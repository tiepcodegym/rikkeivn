<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\CacheHelper;
use Exception;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Facades\URL;

class ProjectMeta extends CoreModel
{

    use SoftDeletes;
    
    /*
     * show hide reward budget in project report
     */
    const REWARD_BUGGET_SHOW = 3;
    const REWARD_BUGGET_HIDE = 4;
    const REWARD_BUGGET_SUBMIT = 1;
    const REWARD_BUGGET_REVIEWED = 2;
    
    /*
     * scope_customer_require is a field in table project_metas
     * if change this field in database then you have to change here
     */
    const CUSTOMER_REQUIRE_FIELD = 'scope_customer_require';
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'project_metas';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['size', 'price', 'incurred_cost',
            'total_cost', 'source_url', 'documents_url',
            'issues_url', 'homepage_url', 'schedule_link',
            'level', 'lineofcode_baseline', 'lineofcode_current',
        'scope_desc','scope_require', 'scope_customer_provide',
        'scope_scope', 'scope_products', 'scope_env_test'];
    
    public static function labelFiledProjectMeta()
    {
        return [
            'size' => Lang::get('project::view.size of project'),
            'price' => Lang::get('project::view.price of project'),
            'incurred_cost' => Lang::get('project::view.incurred cost of project'),
            'total_cost' => Lang::get('project::view.total cost of project'),
            'source_url' => Lang::get('project::view.source url of project'),
            'documents_url' => Lang::get('project::view.documents url of project'),
            'issues_url' => Lang::get('project::view.issues url of project'),
            'homepage_url' => Lang::get('project::view.homepage url of project'),
            'schedule_link' => Lang::get('project::view.plan - schedule link of project'),
            'level' => Lang::get('project::view.level of project'),
            'lineofcode_baseline' => Lang::get('project::view.line of code baseline of project'),
            'lineofcode_current' => Lang::get('project::view.line of code current of project'),
            'id_redmine' => Lang::get('project::view.id redmine of project'),
            'id_git' => Lang::get('project::view.id git of project'),
            'id_svn' => Lang::get('project::view.id svn of project'),
            'scope_desc' => Lang::get('project::view.scope description of project'),
            'scope_require' => Lang::get('project::view.scope require of project'),
            'scope_customer_provide' => Lang::get('project::view.scope customer provide of project'),
            'scope_scope' => Lang::get('project::view.scope of project'),
            'scope_products' => Lang::get('project::view.scope products of project'),
            'scope_env_test' => Lang::get('project::view.scope environment test of project'),
        ];
    }
    
    /**
     * rewrite save model
     * 
     * @param array $options
     * @return type
     * @throws \Rikkei\Project\Model\Exception
     */
    public function save(array $options = array()) {
        try {
            $result = parent::save($options);
            CacheHelper::forget(Project::KEY_CACHE, $this->project_id);
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    /*
     * get project meta
     * 
     * @param type $projectId
     * @return \self
     * @throws Exception
     */
    public static function findFromProject($projectId)
    {
        CacheHelper::flush();
        $item = self::where('project_id', $projectId)
            ->first();
        if ($item) {
            return $item;
        }
        $item = new self();
        $item->setData([
            'project_id' => $projectId,
        ]);
        try {
            $item->save();
            CacheHelper::forget(Project::KEY_CACHE, $projectId);
            return $item;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * edit project meta basic information
     * @param array
     * @param array
     * @return array
     */
    public static function editBasicInfo($data, $project)
    {
        $result = array();
        $result['status'] = false;
        $projectMeta = $project->projectMeta;
        if ($data['name'] == 'scope_scope') {
            $scopes = ProjectMetaScope::where('project_metas_id', $projectMeta->id)
                ->whereNull('deleted_at')
                ->select(DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(scope_scope)) SEPARATOR ",") as scope'))
                ->first();
            if (isset($data['value']) && $data['value'] != null) {
                foreach ($data['value'] as $key => $value) {
                    if (in_array($value, explode(',', $scopes->scope))) {
                        continue;
                    } else {
                        $newScope = [
                            'project_metas_id' => $projectMeta->id,
                            'scope_scope' => $value,
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        ];
                        ProjectMetaScope::insert($newScope);
                    }
                }
            }

            if (isset($scopes->scope)) {
                foreach(explode(',', $scopes->scope) as $items => $val) {
                    if ($data['value'] && in_array($val, $data['value'])) {
                        continue;
                    } else {
                        $peojectMetaScope = ProjectMetaScope::where('project_metas_id', $projectMeta->id)
                            ->where('scope_scope', $val)
                            ->whereNull('deleted_at')
                            ->first();
                        $peojectMetaScope->deleted_at = Carbon::now()->format('Y-m-d H:i:s');
                        $peojectMetaScope->save();
                    }
                }
            }
            $result['status'] = true;
        } else {
            $projectMeta->{$data['name']} = $data['value'];
            if($projectMeta->save()) {
                $result['status'] = true;
            }
        }
        CacheHelper::forget(Project::KEY_CACHE, $project->id);
        return $result;
    }

    /**
     * check project reward budget is approved and showed
     *
     * @return boolean
     */
    public function isShowRewardBudget()
    {
        return $this->is_show_reward_budget == self::REWARD_BUGGET_SHOW;
    }

    /**
     * check project reward budget is approved and hidden
     *
     * @return boolean
     */
    public function isHideRewardBudget()
    {
        return $this->is_show_reward_budget == self::REWARD_BUGGET_HIDE;
    }

    /**
     * Project reward budget is summitted
     *
     * @return boolean
     */
    public function isSubmittedRewardBudget()
    {
        return $this->is_show_reward_budget == self::REWARD_BUGGET_SUBMIT
                || is_null($this->is_show_reward_budget);
    }

    /**
     * Project reward budget is reviewed
     *
     * @return boolean
     */
    public function isReviewedRewardBudget()
    {
        return $this->is_show_reward_budget == self::REWARD_BUGGET_REVIEWED;
    }

    /**
     * sent email to leader for first approved
     * @param object $project
     */
    public static function firstApprove($project)
    {
        $projectMeta = self::findFromProject($project->id);
        if (!$projectMeta->is_show_reward_budget) {
            $projectMeta->is_show_reward_budget = self::REWARD_BUGGET_SUBMIT;
            if ($projectMeta->save()) {
                $emailLeader = Employee::getEmailEmpById($project->leader_id);
                $name = Employee::getNameEmpById($project->leader_id);
                $pm = $project->getPmActive();
                if ($pm) {
                    $pm = $pm->name . ' (' . $pm->email . ')';
                }
                $emailQueue = new EmailQueue();
                $poinLink = URL::route('project::point.edit', ['id' => $project->id]);
                $emailQueue->setTo($emailLeader)
                    ->setSubject(trans('project::email.Subject email report reward for first approve project', ['name' => $project->name]))
                    ->setTemplate('project::emails.reward_notifiReviewBudget', [
                        'dear_name' => $name,
                        'project_name' => $project->name,
                        'project_pm' => $pm,
                        'project_group' => $project->getTeamsString(),
                        'point_link' => $poinLink
                    ])
                    ->setNotify($project->leader_id, null, $poinLink, ['category_id' => RkNotify::CATEGORY_PROJECT])
                    ->save();
            }
        }
    }

    /**
     * save data meta from project input
     *
     * @param object $project
     * @param array $data
     * @return type
     */
    public static function saveFromProject($project, $data)
    {
        if (!is_array($data) || !$data) {
            return false;
        }
        $meta = self::findFromProject($project->id);
        return $meta->setData($data)->save();
    }

    /**
     * @param int $cloneId
     * @param int $projectId
     * @return null|boolean
     */
    public static function cloneProjectMeta($cloneId, $projectId)
    {
        $item = self::where('project_id', $cloneId)
            ->whereNull('deleted_at')
            ->first();
        if ($item) {
            unset($item->id);
            unset($item->created_at);
            unset($item->updated_at);
            $item->setData([
                'project_id' => $projectId,
            ]);
            return self::insert($item->toArray());
        }
        return null;
    }
}
