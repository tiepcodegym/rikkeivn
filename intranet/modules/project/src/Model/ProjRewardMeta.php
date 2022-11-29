<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjRewardMeta extends CoreModel
{
    use SoftDeletes;

    protected $fillable = [
        'reward_budget', 'count_defect', 'count_defect_pqa', 'count_leakage'
    ];
    protected $table = 'proj_reward_metas';
    
    /**
     * create project reward meta
     *      if exists, not create
     * 
     * @param model $task
     * @param array $data
     * @return \self
     */
    public static function createRewardMeta($task, array $data = [], $monthReward = null)
    {
        $rewardMeta = self::where('task_id', $task->id)->first();
        if ($rewardMeta) {
            return $rewardMeta;
        }
        if (!isset($data['project'])) {
            $data['project'] = $task->getProject();
        }
        if (!isset($data['project'])) {
            return null;
        }
        if (!isset($data['projectPoint'])) {
            $data['projectPoint'] = ProjectPoint::findFromProject($task->project_id);
        }
        if (!isset($data['projectPointInformation'])) {
            $data['projectPointInformation'] = ViewProject::getProjectPointInfo(
                $data['project'], 
                $data['projectPoint']
            );
        }
        $billableEffort = $data['projectPointInformation']['cost_billable_effort'];
        if ($data['project']->type_mm == Project::MD_TYPE) {
            $billableEffort = round($billableEffort / 
                (float) CoreConfigData::get('project.mm'), 2);
        }
        $rewardBudgets = ProjRewardBudget::getRewardBudgets($data['project']->id, $monthReward);
        $rewardMetaConfig = CoreConfigData::get('project.reward');
        $rewardMeta = new self();
        if (isset($rewardBudgets[$data['projectPointInformation']['project_evaluation']])) {
            $rewardBudget = $rewardBudgets[$data['projectPointInformation']['project_evaluation']];
        } elseif (isset($rewardBudgets[$monthReward][$data['projectPointInformation']['project_evaluation']])) {
            $rewardBudget = $rewardBudgets[$monthReward][$data['projectPointInformation']['project_evaluation']];
        } else {
            $rewardBudget = $data['project']->getRewardEvalUnitConfig($rewardMetaConfig)
                    [$data['projectPointInformation']['project_evaluation']] *
                    $billableEffort;
        }
        $metaData = [
            'task_id' => $task->id,
            'evaluation' => $data['projectPointInformation']['project_evaluation'],
            'billable' => $billableEffort,
            'reward_budget' => $rewardBudget,
            'count_defect' => $data['projectPoint']->qua_defect_reward_errors,
            'count_defect_pqa' => 0,
            'count_leakage' => $data['projectPointInformation']['qua_leakage_errors'],
            'unit_reward_leakage_actual' => $rewardMetaConfig['unit_reward_leakage_actual'],
            'unit_reward_leakage_qa' => $rewardMetaConfig['unit_reward_leakage_qa'],
            'unit_reward_defect' => $rewardMetaConfig['unit_reward_defect'],
            'unit_reward_defect_pqa' => $rewardMetaConfig['unit_reward_defect_pqa'],
            'factor_reward_pm' => $rewardMetaConfig['factor_reward_pm'],
            'factor_reward_dev' => $rewardMetaConfig['factor_reward_dev'],
            'factor_reward_brse' => $rewardMetaConfig['factor_reward_brse'],
        ];
        if($monthReward != null) {
            $metaData['month_reward'] = $monthReward;
        }
        $rewardMeta->setData($metaData)->save();
        return $rewardMeta;
    }
    
    /**
     * update reward meta after update budget
     */
    public static function updateRewardMetaAfterBudget($tasks = [], $projectId) 
    {
        foreach ($tasks as $key => $task) {
            $proMeta = self::getRewardMeta($task);
            $rewardBudgets = ProjRewardBudget::getRewardBudgets($projectId, $proMeta->month_reward);
            if (isset($rewardBudgets[$proMeta->month_reward][$proMeta->evaluation]) && $proMeta->month_reward != null) {
                $proMeta->reward_budget = $rewardBudgets[$proMeta->month_reward][$proMeta->evaluation];
                $proMeta->save();
            }
        }
    }
    
    /**
     * get reward meta
     * 
     * @param model $task
     * @return model
     */
    public static function getRewardMeta($task)
    {
        return self::where('task_id', $task->id)->first();
    }
    
    /**
     * get last approved date
     * @return datetime
     */
    public static function getLastApproveDate() {
        $tableRewardMeta = ProjRewardMeta::getTableName();
        $lastMonth = ProjRewardMeta::select(
            DB::raw('MAX('.$tableRewardMeta.'.approve_date) AS approve_date')
        )->first();
        if ($lastMonth) {
            return Carbon::parse($lastMonth->approve_date);
        }
        return null;
    }
}