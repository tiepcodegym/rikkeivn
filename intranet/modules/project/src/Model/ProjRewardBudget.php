<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Project\Model\ProjRewardMeta;
use Illuminate\Support\Facades\URL;

class ProjRewardBudget extends CoreModel
{
    const KEY_CACHE = 'proj_reward_budgets';
    
    protected $table = 'proj_reward_budgets';
    public $timestamps = false;

    /**
     * get all reward budget availabel of project
     * 
     * @param int $projectId
     * @return array
     */
    public static function getRewardBudgets($projectId, $monthReward = null)
    {
        if ($result = CacheHelper::get(self::KEY_CACHE, $projectId)) {
            return $result;
        }
        $collection = self::select('level', 'reward', 'month_reward')
            ->where('project_id', $projectId);
        $project = Project::find($projectId);
        if ($monthReward != null) {
            $collection = $collection ->where('month_reward', '=', $monthReward);
        } elseif (!$project->isLong()){
            $collection = $collection->whereNull('month_reward');
        }
        $collection = $collection->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        if (!$project->isLong()) {
            foreach ($collection as $item) {
                $result[$item->level] = $item->reward;
            }
        } else {
            foreach ($collection as $item) {
                $result['sum'][$item->level] = 0;
            }
            foreach ($collection as $item) {
                $result[$item->month_reward][$item->level] = $item->reward;
                $result['sum'][$item->level] = $result['sum'][$item->level] + $item->reward;
            }
        }
        
        CacheHelper::put(self::KEY_CACHE, $result, $projectId);
        return $result;
    }

    /**
    * get reward budget for long project
    */
    public static function getRewardBudgetsForLongProj($projectId, $monthReward) {
        return self::select('level', 'reward', 'month_reward')
                ->where('project_id', $projectId)
                ->where('month_reward', $monthReward)
                ->get();
    }
    
    /**
     * insert or update reward budget data when submit
     * 
     * @param object $project
     * @param array $data
     * @return boolean
     * @throws \Rikkei\Project\Model\Exception
     */
    public static function insertRewardBudgets($project, array $data = [])
    {
        if (!$data) {
            return true;
        }
        $evaluationKey = array_keys(ProjectPoint::evaluationLabel());
        DB::beginTransaction();
        try {
            if (!$project->isLong()) {
                foreach ($data as $key => $value) {
                    if (!in_array($key, $evaluationKey)) {
                        continue;
                    }
                    $countReward = self::where('project_id', $project->id)
                        ->where('level', $key)
                        ->count();
                    if ($countReward) { //update
                        self::where('project_id', $project->id)
                            ->where('level', $key)
                            ->update([
                                'reward' => $value
                            ]);
                    } else { // insert
                        self::insert([
                            'project_id' => $project->id,
                            'level' => $key,
                            'reward' => $value
                        ]);
                    }
                }
            }else {
                self::where('project_id', $project->id)->whereIn('month_reward', array_keys($data))->delete();
                $dataBudget = [];
                foreach ($data as $month => $budget_data) {
                    foreach ($budget_data as $key => $value) {
                        if (!in_array($key, $evaluationKey)) {
                            continue;
                        }
                        $dataBudget[] = [
                            'project_id' => $project->id,
                            'level' => $key,
                            'reward' => $value,
                            'month_reward' => $month,
                        ];
                    }
                }
                
                self::insert($dataBudget);
                $tasks = Task::getAllTaskofLong($project);
                ProjRewardMeta::updateRewardMetaAfterBudget($tasks, $project->id);
            }
            CacheHelper::forget(self::KEY_CACHE, $project->id);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * create data default reward budget data when submit
     * 
     * @param object $project
     * @param array $data
     * @return boolean
     * @throws \Rikkei\Project\Model\Exception
     */
    public static function createRewardBudgetsDefault(
        $project, 
        array $data = [],
        array $config = []
    ) {
        if ($project->type != Project::TYPE_BASE) {
            return true;
        }
        // approve wo first time
        if (self::isExistsBudget($project)) {
            return true;
        }
        $evaluationKey = array_keys(ProjectPoint::evaluationLabel());
        $rewardMetaConfig = CoreConfigData::get('project.reward');
        if (!isset($data['projectPoint'])) {
            $data['projectPoint'] = ProjectPoint::findFromProject($project->id);
        }
        $projectQuality = ProjQuality::getFollowProject($project->id);
        $billableEffort = $data['projectPoint']->getCostBillableEffort($projectQuality);
        if ($project->type_mm == Project::MD_TYPE) {
            $billableEffort = round(
                $billableEffort / 
                (float) CoreConfigData::get('project.mm'),
            2);
        }
        if($project->isLong()) {
            $monthReward = $project->getMonthReward();
        }
        $rewardEvalUnitConfig = $project->getRewardEvalUnitConfig($rewardMetaConfig);
        DB::beginTransaction();
        try {
            $dataBudget = [];
            foreach ($evaluationKey as $key) {
                $rewardBudget = $rewardEvalUnitConfig[$key] *
                        $billableEffort;
                $countReward = self::where('project_id', $project->id)
                            ->where('level', $key)
                            ->count();
                if (!$countReward) {
                    if (isset($monthReward) && count($monthReward)) {
                        foreach ($monthReward as $month) {
                            $dataBudget[] = [
                                'project_id' => $project->id,
                                'level' => $key,
                                'reward' => $rewardBudget / count($monthReward),
                                'month_reward' => $month,
                            ];
                        }
                    } else {
                        $dataBudget[] = [
                            'project_id' => $project->id,
                            'level' => $key,
                            'reward' => $rewardBudget,
                        ];
                    }
                }
            }
            if(count($dataBudget)) {
                self::insert($dataBudget);
            }
            if ((!isset($config['send_email']) || $config['send_email'])) {
                // send email budget for first approve
                ProjectMeta::firstApprove($project);
            }
            CacheHelper::forget(self::KEY_CACHE, $project->id);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    
    /**
     * send email for COO review and change reward budget
     * 
     * @param object $project
     */
    public static function sendEmailToChangeBudget($project)
    {
        $emaiCoo = CoreConfigData::getValueDb('project.account_approver_reward');
        $coo = Employee::getNameByEmail($emaiCoo);
        if (!$coo) {
            return true;
        }
        $pm = $project->getPmActive();
        if (!$pm) {
            $pm = new Employee();
        }
        $emaiQueue = new EmailQueue();
        $rewardLink = URL::route('project::point.edit', ['id' => $project->id]) . '#reward';
        $template = 'project::emails.reward_budget';
        $data = [
            'dear_name' => $coo->name,
            'project_name' => $project->name,
            'project_pm' => $pm->name,
            'project_group' => $project->getTeamsString(),
            'reward_link' => $rewardLink
        ];
        $emaiQueue->setTo($emaiCoo, $coo->name)
            ->setSubject('[Project reward] Project Reward Budget of ' .
                $project->name . ' available, please review it and change it')
            ->setTemplate($template, $data)
            ->setNotify($coo->id, null, $rewardLink, [
                'category_id' => RkNotify::CATEGORY_PROJECT,
                'content_detail' => RkNotify::renderSections($template, $data)
            ])
            ->save();
    }
    
    /**
     * check exits budget reward of project
     *
     * @param model $project
     * @return int
     */
    public static function isExistsBudget($project)
    {
        return self::where('project_id', $project->id)->count();
    }
}