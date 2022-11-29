<?php

namespace Rikkei\Project\Model;

use DateTime;
use Gitlab\Model\Issue;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Illuminate\Support\Facades\Lang;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Core\View\Form;
use Exception;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\Model\TaskComment;
use Rikkei\Team\View\Permission;
use Carbon\Carbon;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Facades\URL;
use Rikkei\Team\Model\Team;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Project\Model\TaskTeam;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Project\Model\TaskRisk;
use Rikkei\Team\Model\PqaResponsibleTeam;

/**
 * Class Task
 * @package Rikkei\Project\Model
 */
class Task extends CoreModel
{
    use SoftDeletes;

    const TYPE_ISSUE = 1;
    const TYPE_WO = 2;
    const TYPE_COMMENDED = 3;
    const TYPE_CRITICIZED = 4;
    const TYPE_QUALITY_PLAN = 5;
    const TYPE_SOURCE_SERVER = 6;
    const TYPE_COMPLIANCE = 7;
    const TYPE_ISSUE_COST = 10;
    const TYPE_ISSUE_QUA = 11;
    const TYPE_ISSUE_TL = 12;
    const TYPE_ISSUE_PROC = 13;
    const TYPE_ISSUE_CSS = 14;
    const TYPE_REWARD = 15;
    const TYPE_GROUP_POINT = 16;
    const TYPE_GENERAL = 17;
    const TYPE_NC = 18;
    const TYPE_ISSUE_SUMMARY = 30;
    const TYPE_OUT_REPORT = 50;
    const TYPE_OUT_DELIVER = 51;
    const TYPE_CONTRACT_CONFIRM = 60;
    const TYPE_RISK = 70;
    const TYPE_OPPORTUNITY = 20;

    const STATUS_NEW = 1;
    const STATUS_PROCESS = 2;
    const STATUS_RESOLVE = 2;
    const STATUS_RESOLVE2 = 10;
    const STATUS_FEEDBACK = 4;
    const STATUS_SUBMITTED = 7;
    const STATUS_REVIEWED = 8;
    const STATUS_APPROVED = 9;
    const STATUS_CLOSED = 40;
    const STATUS_REJECT = 50;
    const STATUS_REOPEN = 51;
    const STATUS_COMPLETED = 11;

    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_SERIOUS = 4;
//    const PRIORITY_URGENT = 4;
//    const PRIORITY_IMMEDIATE = 5;

    const KEY_CACHE_ALL = 'task_all';
    const KEY_CACHE_CR = 'task_cr';
    const KEY_CACHE_COMPLIANCE = 'task_comp';

    const TYPE_WO_CRITICAL_DEPENDENCIES = 1;
    const TYPE_WO_ASSUMPTION_CONSTRAINS = 2;
    const TYPE_WO_RISK = 3;
    const TYPE_WO_STAGE_MILESTONE = 4;
    const TYPE_WO_TRANING = 5;
    const TYPE_WO_EXTERNAL_INTERFACE = 6;
    const TYPE_WO_COMMINUCATION = 7;
    const TYPE_WO_TOOL_AND_INFRASTRUCTURE = 8;
    const TYPE_WO_DELIVERABLE = 9;
    const TYPE_WO_PERFORMANCE = 10;
    const TYPE_WO_QUALITY = 11;
    const TYPE_WO_PROJECT_MEMBER = 12;
    const TYPE_WO_QUALITY_PLAN = 13;
    const TYPE_WO_CM_PLAN = 14;
    const TYPE_WO_CHANGE_WO = 15;
    const TYPE_WO_PROJECT_LOG = 16;
    const TYPE_WO_OVER_PLAN = 17;
    const TYPE_WO_PROJECT = 18;
    const TYPE_WO_BASIC_INFO_PROJECT = 19;
    const TYPE_WO_DEVICES_EXPENSE = 20;
    const TYPE_WO_ISSUE = 21;
    const TYPE_WO_ASSUMPTIONS = 22;
    const TYPE_WO_CONSTRAINTS = 23;
    const TYPE_WO_SECURITY = 24;
    const TYPE_WO_SKILL_REQUEST = 25;
    const TYPE_WO_MEMBER_COMMUNICATION = 26;
    const TYPE_WO_MEETING_COMMUNICATION = 27;
    const TYPE_WO_REPORT_COMMUNICATION = 28;
    const TYPE_WO_OTHER_COMMUNICATION = 29;
    const TYPE_WO_CUSTOMER_COMMUNICATION = 30;
    const TYPE_WO_NC = 31;
    const TYPE_WO_OPPORTUNITY = 32;

    const TYPE_FEEDBACK = 1;

    /*
     * using for task TYPE_REWARD
     * save to table tasks field bonus_money
     */
    const REWARD_IS_PAID = 1;
    const REWARD_IS_UNPAID = 0;

    const ACTION_CORRECTIVE = 1;
    const ACTION_PREVENTIVE = 2;

    const REPORT_WEEKLY = 1;
    const REPORT_DAILY = 2;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'type', 'status', 'action_type', 'parent_id',
                            'priority', 'project_id', 'duedate', 'actual_date', 'cause', 'pqa_suggestion', 'impact',
                            'content', 'report_content', 'freequency_report', 'solution', 'type', 'project_code', 'bonus_money',
                            'process', 'label', 'correction', 'corrective_action',
                            'opportunity_source', 'cost', 'expected_benefit', 'action_plan', 'action_status'
                        ];
    protected $dates = ['duedate'];

    /*
     * get all components workorder
     */
    public static function getAllNameTabWorkorder()
    {
        return [
            self::TYPE_WO_BASIC_INFO_PROJECT => 'summary',
            self::TYPE_WO_OVER_PLAN => Lang::get('project::view.orthers'),
            self::TYPE_WO_STAGE_MILESTONE => Lang::get('project::view.stage'),
            self::TYPE_WO_DELIVERABLE => Lang::get('project::view.deliverable'),
            self::TYPE_WO_PROJECT_MEMBER => Lang::get('project::view.team-allocation'),
            self::TYPE_WO_PERFORMANCE => Lang::get('project::view.performance'),
            self::TYPE_WO_QUALITY => Lang::get('project::view.quality'),
            self::TYPE_WO_TRANING => Lang::get('project::view.training'),
            self::TYPE_WO_QUALITY_PLAN => Lang::get('project::view.quality-plan'),
            self::TYPE_WO_CM_PLAN => Lang::get('project::view.cm-plan'),
            self::TYPE_WO_CRITICAL_DEPENDENCIES => Lang::get('project::view.critical-dependencies'),
            self::TYPE_WO_ASSUMPTION_CONSTRAINS => Lang::get('project::view.assumption-constrains'),
            self::TYPE_WO_RISK => Lang::get('project::view.risk'),
            self::TYPE_WO_EXTERNAL_INTERFACE => Lang::get('project::view.external-interface'),
            self::TYPE_WO_COMMINUCATION => Lang::get('project::view.communication'),
            self::TYPE_WO_TOOL_AND_INFRASTRUCTURE => Lang::get('project::view.tool-infrastructure'),
            self::TYPE_WO_CHANGE_WO => Lang::get('project::view.change-wo'),
            self::TYPE_WO_PROJECT_LOG => Lang::get('project::view.project-log'),
            self::TYPE_WO_DEVICES_EXPENSE => Lang::get('project::view.devices-expenses'),
            self::TYPE_WO_ISSUE => Lang::get('project::view.issue'),
            self::TYPE_WO_ASSUMPTIONS => Lang::get('project::view.assumptions'),
            self::TYPE_WO_CONSTRAINTS => Lang::get('project::view.constraints'),
            self::TYPE_WO_SECURITY => Lang::get('project::view.security'),
            self::TYPE_WO_SKILL_REQUEST => Lang::get('project::view.skill_request'),
            self::TYPE_WO_MEMBER_COMMUNICATION => Lang::get('project::view.member_communication'),
            self::TYPE_WO_MEETING_COMMUNICATION => Lang::get('project::view.communication_meeting'),
            self::TYPE_WO_REPORT_COMMUNICATION => Lang::get('project::view.communication_report'),
            self::TYPE_WO_OTHER_COMMUNICATION => Lang::get('project::view.communication_other'),
            self::TYPE_WO_CUSTOMER_COMMUNICATION => Lang::get('project::view.customer_communication'),
            self::TYPE_WO_NC => 'NC',
            self::TYPE_WO_OPPORTUNITY => 'Opportunity',
        ];
    }

    /**
     * get name of tab in workorder
     *
     * @param int $id
     * @return string
     */
    public static function getNameTabWOItem($id, array $names = [])
    {
        if (!$names) {
            $names = self::getAllNameTabWorkorder();
        }
        if (isset($names[$id])) {
            return $names[$id];
        }
        return null;
    }

    /*
     * get all components workorder
     */
    public static function getAllComponentsWorkorder()
    {
        return [
            self::TYPE_WO_CRITICAL_DEPENDENCIES => Lang::get('project::view.critical dependencies'),
            self::TYPE_WO_ASSUMPTION_CONSTRAINS => Lang::get('project::view.assumption and constrains'),
            self::TYPE_WO_RISK => Lang::get('project::view.risk'),
            self::TYPE_WO_STAGE_MILESTONE => Lang::get('project::view.project stages and milestones'),
            self::TYPE_WO_TRANING => Lang::get('project::view.traning plan'),
            self::TYPE_WO_EXTERNAL_INTERFACE => Lang::get('project::view.external interface'),
            self::TYPE_WO_COMMINUCATION => Lang::get('project::view.project communication'),
            self::TYPE_WO_TOOL_AND_INFRASTRUCTURE => Lang::get('project::view.tool and infrastructure'),
            self::TYPE_WO_DELIVERABLE => Lang::get('project::view.deliverable'),
            self::TYPE_WO_QUALITY => Lang::get('project::view.quality'),
            self::TYPE_WO_PROJECT_MEMBER => Lang::get('project::view.team allocation'),
            self::TYPE_WO_QUALITY_PLAN => Lang::get('project::view.quality plan'),
            self::TYPE_WO_CM_PLAN => Lang::get('project::view.cm plan'),
            self::TYPE_WO_ISSUE => Lang::get('project::view.issues'),
            self::TYPE_WO_NC => 'NC',
        ];
    }

    public static function getAllProcessNC()
    {
        return [
            1 => 'Coding',
            2 => 'Configuration management',
            3 => 'Contract management',
            4 => 'Correction',
            5 => 'Customer support',
            6 => 'Deployment',
            7 => 'Design',
            8 => 'Internal audit',
            9 => 'Others',
            10 => 'Prevention',
            11 => 'Project management',
            12 => 'Quality control',
            13 => 'Requirement',
            14 => 'Test',
            15 => 'Training',
        ];
    }

    /**
     * get task type commended and criticized and spit them
     *
     * @param int $projectId
     * @return array
     */
    public static function getTypeCommendedCriticizedSplit($projectId) {
        if ($collection = CacheHelper::get(self::KEY_CACHE_CR, $projectId)) {
            return $collection;
        }
        $collection = self::select('type', DB::raw('count(*) as count'))
            ->where('project_id', $projectId)
            ->whereIn('type', [self::TYPE_COMMENDED, self::TYPE_CRITICIZED])
            ->groupBy('type')
            ->get();
        $result = [
            self::TYPE_COMMENDED => 0,
            self::TYPE_CRITICIZED => 0
        ];
        if (!$collection) {
            return $result;
        }
        foreach ($collection as $item) {
            if ($item->type == self::TYPE_COMMENDED) {
                $result[self::TYPE_COMMENDED] = $item->count;
            } else {
                $result[self::TYPE_CRITICIZED] = $item->count;
            }
        }
        CacheHelper::put(self::KEY_CACHE_CR, $result, $projectId);
        return $result;
    }

    /**
     * insert task wo
     *
     * @param array $input
     * @param object $project
     * @return boolean|\Rikkei\Project\Model\Task|\self
     * @throws Exception
     */
    public static function insertOrUpdateTaskWo($input, $project)
    {
        $typeTask = self::TYPE_WO;
        $task = self::where('project_id', $input['project_id'])
            ->where('type', $typeTask)
            ->whereIn('status', [
                self::STATUS_FEEDBACK,
                self::STATUS_NEW,
                self::STATUS_SUBMITTED,
           ])
           ->first();
        $idAssign = [];
        $contentCurrChange = self::getContentApproveChange($project);
        if (!$task) {
            $task = new self();
            $versionWorkorder = ProjectChangeWorkOrder::getVersionLastest($input['project_id'], true);
            $task->title = 'Workorder v' . $versionWorkorder;
            $task->type = $typeTask;
            $task->project_id = $input['project_id'];
            $task->priority = self::PRIORITY_NORMAL;
            $task->content = '';
            $oldOldContent = null;
        } else {
            $oldOldContent = TaskWoChange::getContentChangedLast($task);
        }
        $task->status = self::STATUS_SUBMITTED;
        // insert PQA
        $pqa = ProjectMember::getPQAOfProjectLifetime($input['project_id'])->pluck('employee_id')->toArray();
        if ($pqa) {
            $idPqa = $pqa;
        } else {
            $pqaResponsible = PqaResponsibleTeam::getPqaResponsibleTeamOfProjs($input['project_id']);
            if (count($pqaResponsible)) {
                $idPqa = $pqaResponsible->lists('employee_id')->toArray();
            } else {
                $emailPqa = CoreConfigData::getQAAccount();
                $idPqa = (array)Employee::getIdEmpByEmail($emailPqa);
            }
        }

        if ($idPqa) {
            foreach ($idPqa as $value) {
                $idAssign[] = [
                    'employee_id' => $value,
                    'role' => TaskAssign::ROLE_REVIEWER,
                    'status' => TaskAssign::STATUS_NO
                ];
            }
        }

        //insert SQA
        $emailSqa = CoreConfigData::getSQA();
        $idSqa = Employee::getIdEmpByEmail($emailSqa);
        if ($idSqa) {
            $idAssign[] = [
                'employee_id' => $idSqa,
                'role' => TaskAssign::ROLE_REVIEWER,
                'status' => TaskAssign::STATUS_NO
            ];
        }
        $emailCoo = CoreConfigData::getCOOAccount();
        $idCoo = Employee::getIdEmpByEmail($emailCoo);
        DB::beginTransaction();
        try {
            $task->save([], $project, [
                'history' => false
            ]);
            if ($contentCurrChange !== $oldOldContent) {
                // new task wo changes
                $taskWoChanges = new TaskWoChange();
                $taskWoChanges->content = $contentCurrChange;
                $taskWoChanges->created_by = Permission::getInstance()->getEmployee()->id;
                $taskWoChanges->created_at = Carbon::now()->format('Y-m-d H:i:s');
                $taskWoChanges->task_id = $task->id;
                $taskWoChanges->save();
            }
            $approverId = $project->leader_id ? $project->leader_id : $idCoo;
            if ($approverId) {
                $approver = TaskAssign::findAssignee($task->id, $approverId, TaskAssign::ROLE_APPROVER);
                if (!$approver) {
                    $approver = new TaskAssign();
                    $approver->task_id = $task->id;
                    $approver->employee_id  = $approverId;
                    $approver->role = TaskAssign::ROLE_APPROVER;
                }
                if ($approver) {
                    $approver->status = TaskAssign::STATUS_NO;
                    $approver->save();
                }
            }
            TaskAssign::insertOrUpdateAssigneeWOReviewer($task, $idAssign, [
                'type' => TaskAssign::TYPE_STATUS_CHANGE,
                'status_default' => TaskAssign::STATUS_NO,
                'project' => $project
            ]);
            if (isset($input['sb_note']) && $input['sb_note']) {
                $user = Permission::getInstance()->getEmployee();
                $taskComment = new TaskComment();
                $taskComment->setData([
                    'task_id' => $task->id,
                    'content' => $input['sb_note'],
                    'created_by' => $user ? $user->id : null,
                    'type' => TaskComment::TYPE_COMMENT_WO
                ])
                ->save();
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        return $task;
    }

    /**
     * get content change after submit wo
     *
     * @param object $project
     * @return string
     */
    protected static function getContentApproveChange($project)
    {
        $contentMember = ProjectMember::getChangesAfterSubmit($project->id);
        $contentBasic = Project::getChangesAfterSubmit($project);
        $contentDeliver = ProjDeliverable::getChangesAfterSubmit($project->id);
        $contentStages = StageAndMilestone::getChangesAfterSubmit($project->id);
        $content = array_merge($contentMember, $contentBasic, $contentDeliver, $contentStages);
        return json_encode($content);
    }
    /**
     * get content changed of wo
     *
     * @param boolean $oldTask
     * @return string
     */
    protected static function getContentApproveChangeFormatHtml($input, $oldTask = false)
    {
        $content = '';
        $content = Project::getContentTaskApproved(
            self::TYPE_WO_PROJECT,
            $input,
            $content,
            $oldTask
        );
        $content = ProjQuality::getContentTaskApproved(
                self::TYPE_WO_QUALITY,
                $input,
                $content,
                $oldTask
            );
        if ($content) {
            $content = '<p>' . Lang::get('project::view.Edit basic info for project')
                . '</p><ul>' . $content . '</ul>';
        }
        $content = ProjDeliverable::getContentTaskApproved(
                self::TYPE_WO_DELIVERABLE,
                $input,
                $content,
                $oldTask
            );
        $content = CriticalDependencie::getContentTaskApproved(
                self::TYPE_WO_CRITICAL_DEPENDENCIES,
                $input,
                $content,
                $oldTask
            );
        $content = AssumptionConstrain::getContentTaskApproved(
                self::TYPE_WO_ASSUMPTION_CONSTRAINS,
                $input,
                $content,
                $oldTask
            );
        $content = Risk::getContentTaskApproved(
                self::TYPE_WO_RISK,
                $input,
                $content,
                $oldTask
            );
        $content = StageAndMilestone::getContentTaskApproved(
                self::TYPE_WO_STAGE_MILESTONE,
                $input,
                $content,
                $oldTask
            );
        $content = Training::getContentTaskApproved(
                self::TYPE_WO_TRANING,
                $input,
                $content,
                $oldTask
            );
        $content = ExternalInterface::getContentTaskApproved(
                self::TYPE_WO_EXTERNAL_INTERFACE,
                $input,
                $content,
                $oldTask
            );
        $content = Communication::getContentTaskApproved(
                self::TYPE_WO_COMMINUCATION,
                $input,
                $content,
                $oldTask
            );
        $content = ToolAndInfrastructure::getContentTaskApproved(
                self::TYPE_WO_TOOL_AND_INFRASTRUCTURE,
                $input,
                $content,
                $oldTask
            );
        $content = ProjectMember::getContentTaskApproved(
                self::TYPE_WO_PROJECT_MEMBER,
                $input,
                $content,
                $oldTask
            );
        $content = Risk::getContentTaskApproved(
                self::TYPE_WO_ISSUE,
                $input,
                $content,
                $oldTask
            );
        $content = trim(preg_replace('/\s+/', ' ', $content));
        return $content;
    }

    /**
     * get label of type task
     *
     * @return array
     */
    public static function typeFeedbackLabel()
    {
        return [
            self::TYPE_COMMENDED => 'Customer feedback - Positive',
            self::TYPE_CRITICIZED => 'Customer feedback - Negative',
        ];
    }

    public static function countOfIssueByProjectId($projectId)
    {
        return self::where('project_id', $projectId)
            ->whereNull('deleted_at')
            ->where('type', self::TYPE_CRITICIZED)
            ->count();
    }

    /**
     * get label of type task
     *
     * @return array
     */
    public static function typeLabel()
    {
        return [
            self::TYPE_ISSUE => Lang::get('project::view.Task'),
            self::TYPE_WO => Lang::get('project::view.Workorder'),
            self::TYPE_COMMENDED => Lang::get('project::view.Customer feedback - Positive'),
            self::TYPE_CRITICIZED => Lang::get('project::view.Customer feedback - Negative'),
            self::TYPE_QUALITY_PLAN => Lang::get('project::view.Quality Plan'),
            self::TYPE_SOURCE_SERVER => Lang::get('project::view.Source server'),
            self::TYPE_COMPLIANCE => Lang::get('project::view.None Compliance'),
            self::TYPE_ISSUE_COST => Lang::get('project::view.Task cost'),
            self::TYPE_ISSUE_QUA => Lang::get('project::view.Task quality'),
            self::TYPE_ISSUE_TL => Lang::get('project::view.Task timeliness'),
            self::TYPE_ISSUE_PROC => Lang::get('project::view.Task process'),
            self::TYPE_ISSUE_CSS => Lang::get('project::view.Task css'),
            self::TYPE_RISK => Lang::get('project::view.Task risk'),
        ];
    }

    public static function typeLabelForIssue()
    {
        return [
            self::TYPE_ISSUE_QUA => Lang::get('project::view.Task quality'),
            self::TYPE_ISSUE_COST => Lang::get('project::view.Task cost'),
            self::TYPE_ISSUE_TL => Lang::get('project::view.Task timeliness'),
            self::TYPE_ISSUE_PROC => Lang::get('project::view.Task process'),
            self::TYPE_CRITICIZED => Lang::get('project::view.Task css'),
        ];
    }

    /**
     * get some label of type task
     *
     * @return array
     */
    public static function typeLabelMyTask()
    {
        return [
            self::TYPE_WO,
            self::TYPE_ISSUE_COST,
            self::TYPE_ISSUE_QUA,
            self::TYPE_ISSUE_TL,
            self::TYPE_ISSUE_PROC,
            self::TYPE_ISSUE_CSS,
            self::TYPE_WO_TRANING
        ];
    }

    /**
     * get status label of task
     *
     * @return array
     */
    public static function statusLabel()
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_PROCESS => 'Processing',
            self::STATUS_RESOLVE => 'Resolved',
            self::STATUS_RESOLVE2 => 'Resolved',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REOPEN => 'Reopen',
            self::STATUS_FEEDBACK => 'Feedback',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_REVIEWED => 'Reviewed',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECT => 'Reject'
        ];
    }

    public static function statusNewLabel()
    {
        return [
            self::STATUS_NEW => 'Open',
            self::STATUS_PROCESS => 'In-Progress',
            self::STATUS_REOPEN => 'Re-Open',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    public static function statusNCLabel()
    {
        return [
            self::STATUS_NEW => 'Open',
            self::STATUS_PROCESS => 'In-Progress',
            self::STATUS_RESOLVE2 => 'Resolved',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_REJECT => 'Cancelled',
        ];
    }

    /**
     * get status of work order
     *
     * @return array
     */
    public static function getStatusTypeWO($returnKey = false)
    {
        return self::getStatusFollowKey([
            self::STATUS_SUBMITTED,
            self::STATUS_REVIEWED,
            self::STATUS_APPROVED,
            self::STATUS_FEEDBACK
        ], null, $returnKey);
    }

    /**
     * get status of work order
     *
     * @return array
     */
    public static function getStatusTypeNormal($returnKey = false)
    {
        return self::getStatusFollowKey([
            self::STATUS_NEW,
            self::STATUS_PROCESS,
            self::STATUS_RESOLVE,
            self::STATUS_FEEDBACK,
            self::STATUS_CLOSED,
            self::STATUS_REJECT
        ], null, $returnKey);
    }

    public static function getStatusWithoutClosed($returnKey = false)
    {
        return self::getStatusFollowKey([
            self::STATUS_NEW,
            self::STATUS_PROCESS,
            self::STATUS_REOPEN,
        ], null, $returnKey);
    }

    /**
     * get status of work order
     *
     * @return array
     */
    public static function getStatusTypeMySelf($returnKey = false)
    {
        return self::getStatusFollowKey([
            self::STATUS_NEW,
            self::STATUS_PROCESS,
            self::STATUS_RESOLVE,
            self::STATUS_REVIEWED,
            self::STATUS_SUBMITTED,
            self::STATUS_FEEDBACK,
            self::STATUS_CLOSED,
            self::STATUS_REJECT,
        ], null, $returnKey);
    }

    /**
     * get status with keys
     *
     * @param array $keys
     * @param array $status
     * @return array
     */
    protected static function getStatusFollowKey($keys, $status = null, $returnKey = false)
    {
        if (!$status) {
            $status = self::statusLabel();
        }
        $keyStatus = array_keys($status);
        $result = [];
        $resultKey = [];
        foreach ($keys as $key) {
            if (in_array($key, $keyStatus)) {
                $result[$key] = $status[$key];
                $resultKey[] = $key;
            }
        }
        if ($returnKey) {
            return $resultKey;
        }
        return $result;
    }

    /**
     * get priority label of task
     *
     * @return array
     */
    public static function priorityLabel()
    {
        return [
            self::PRIORITY_LOW => Lang::get('project::view.Level Low'),
            self::PRIORITY_NORMAL => Lang::get('project::view.Level Normal'),
            self::PRIORITY_HIGH => Lang::get('project::view.Level High'),
            self::PRIORITY_SERIOUS => Lang::get('project::view.Level Urgent'),
//            self::PRIORITY_URGENT => 'Urgent',
//            self::PRIORITY_IMMEDIATE => 'Immediate',
        ];
    }

    /**
     * get priority label of task
     *
     * @return array
     */
    public static function actionLabel()
    {
        return [
            self::ACTION_CORRECTIVE => Lang::get('project::view.Corrective'),
            self::ACTION_PREVENTIVE => Lang::get('project::view.Preventive'),
        ];
    }

    /**
     * get freequency of report label of task
     *
     * @return array
     */
    public static function getLableFrequencyOfReport()
    {
        return [
            self::REPORT_WEEKLY => Lang::get('project::view.Weekly'),
            self::REPORT_DAILY => Lang::get('project::view.Daily'),
        ];
    }

    /**
     * get task type compliance
     *
     * @param int $projectId
     * @param boolean $getReject
     * @return object
     */
    public static function countTypeCompliance($projectId)
    {
        if ($collection = CacheHelper::get(self::KEY_CACHE_COMPLIANCE, $projectId)) {
            return $collection;
        }
        $collection = self::select(DB::raw('count(*) as count'))
            ->where('project_id', $projectId)
            ->where('type', self::TYPE_COMPLIANCE)
            ->where('status', '!=', self::STATUS_REJECT)
            ->first();
        $collection = $collection->count;
        CacheHelper::put(self::KEY_CACHE_COMPLIANCE, $collection, $projectId);
        return $collection;
    }

    public static function getAllTask($teamIdsAvailable)
    {
        $urlFilter = trim(URL::route('project::report.issue'), '/') . '/';
        $tableProject = Project::getTableName();
        $tableTask = self::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tableTeam = Team::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();
        $tableTaskAction = TaskAction::getTableName();

        $collection = self::select("{$tableTask}.id as id", 'title', 
            'priority', "{$tableTask}.duedate", "{$tableTask}.type as type", "{$tableTask}.project_id", "{$tableTask}.created_by",
            "{$tableTask}.created_at as created_at", "{$tableTask}.updated_at as updated_at",
            DB::raw("SUBSTRING_INDEX({$tableEmployee}.email, "
                . "'@', 1) as email"), "{$tableTeam}.name as team_leader",
            DB::raw("GROUP_CONCAT($tableTaskAction.status) as status"),
            "{$tableTask}.status as status_backup")
            ->leftJoin($tableTaskAssigns, function ($join) use ($tableTaskAssigns, $tableTask) {
                $join->on("{$tableTaskAssigns}.task_id", '=', "{$tableTask}.id")
                    ->where("{$tableTaskAssigns}.role", '=', TaskAssign::ROLE_OWNER);
            })
            ->leftJoin($tableProject, "{$tableProject}.id", '=', "{$tableTask}.project_id")
            ->leftJoin($tableTeamMember, "{$tableTeamMember}.employee_id", '=', "{$tableProject}.leader_id")
            ->leftJoin($tableTeam, "{$tableTeamMember}.team_id", '=', "{$tableTeam}.id")
            ->leftJoin($tableTaskAction, "{$tableTaskAction}.issue_id", '=', "{$tableTask}.id")
            ->leftJoin('project_members', "project_members.project_id", "=", "projs.id")
            ->whereIn("{$tableTask}.status", [self::STATUS_NEW, self::STATUS_PROCESS, self::STATUS_REOPEN, self::STATUS_CLOSED]);
        $collection->addSelect(DB::raw("GROUP_CONCAT($tableTaskAction.duedate) as task_duedate"));
        $collection->addSelect("{$tableTask}.duedate as task_duedate_backup");
        $collection->addSelect(
            DB::raw("(SELECT COUNT(id) FROM tasks as tasks_child WHERE parent_id = tasks.id) AS count_issues")
        );
        $collection->addSelect(
            "{$tableProject}.id as project_id", "{$tableProject}.name as project_name"
        );
        $collection->leftJoin($tableEmployee, "{$tableEmployee}.id", '=', "{$tableTaskAssigns}.employee_id");
        $collection->groupBy("{$tableTask}.id")
            ->orderBy("{$tableTask}.status")
            ->orderBy('priority', 'desc')
            ->orderBy("{$tableTask}.created_at", 'desc');
        $employeeFilter = Form::getFilterData('except', "{$tableEmployee}.email", $urlFilter);
        if ($employeeFilter) {
            $collection->where("{$tableEmployee}.email", 'LIKE', "%".addslashes(trim($employeeFilter)) . "%");
        }
        $projectFilter = Form::getFilterData('except', "{$tableProject}.name", $urlFilter);
        if ($projectFilter) {
            $collection->where("{$tableProject}.name", 'LIKE', "%".addslashes(trim($projectFilter)) . "%");
        }
        $priorityFilter = Form::getFilterData('except', "{$tableTask}.priority", $urlFilter);
        if ($priorityFilter) {
            $collection->where("{$tableTask}.priority", $priorityFilter);
        }
        $titleFilter = Form::getFilterData('except', "{$tableTask}.title", $urlFilter);
        if ($titleFilter) {
            $collection->where("{$tableTask}.title", 'LIKE', "%".addslashes(trim($titleFilter)) . "%");
        }
        $typeFilter = Form::getFilterData('except', "{$tableTask}.type", $urlFilter);
        if ($typeFilter) {
            $collection->where("{$tableTask}.type", $typeFilter);
        }
        $stateFilter = Form::getFilterData('except', "{$tableTask}.status", $urlFilter);
        if ($stateFilter) {
            $collection->where(function ($query) use ($stateFilter, $tableTaskAction, $tableTask) {
                $query->where(function ($q) use ($stateFilter, $tableTaskAction, $tableTask) {
                    $q->whereNull("{$tableTaskAction}.status")
                        ->where("{$tableTask}.status", $stateFilter);
                })
                    ->orWhere(function ($q) use ($stateFilter, $tableTaskAction, $tableTask) {
                        $q->where("{$tableTaskAction}.status", $stateFilter);
                    });
            });
        }
        $dateFromFilter = Form::getFilterData('except', 'created_at', $urlFilter);
        if ($dateFromFilter) {
            $collection->whereDate("{$tableTask}.updated_at", '>=', $dateFromFilter);
        }
        $dateToFilter = Form::getFilterData('except', 'updated_at', $urlFilter);
        if ($dateToFilter) {
            $collection->whereDate("{$tableTask}.updated_at", '<=', $dateToFilter);
        }
        $divisionFilter = Form::getFilterData('except', 'teams.id', $urlFilter);
        if ($divisionFilter) {
            $collection->whereIn("team_members.team_id", $divisionFilter);
        }
        $emp = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, 'project::report.issue')) {
            return $collection;
        } else {
            if (!empty($teamIdsAvailable)) {
                $collection->where(function ($p) use ($teamIdsAvailable, $emp) {
                    $p->orWhereIn('teams.id', $teamIdsAvailable)
                        ->orWhere(function ($p) use ($emp) {
                            $p->where('project_members.employee_id', $emp->id)
                                ->whereDate('project_members.start_at', '<=', Carbon::now()->format('Y-m-d'))
                                ->whereDate('project_members.end_at', '>=', Carbon::now()->format('Y-m-d'))
                                ->where('project_members.status', ProjectMember::STATUS_APPROVED);
                        });
                });
            } else {
                $collection->where('project_members.employee_id', $emp->id)
                    ->whereDate('project_members.start_at', '<=', Carbon::now()->format('Y-m-d'))
                    ->whereDate('project_members.end_at', '>=', Carbon::now()->format('Y-m-d'))
                    ->where('project_members.status', ProjectMember::STATUS_APPROVED);
            }
        }
        return $collection;
    }

    public static function getDuedateOfIssue($duedateOfActions, $oldDuedate = null)
    {
        if (empty($duedateOfActions)) {
            return static::getDuedateOfOldTask($oldDuedate);
        }

        $arrayStatus = explode(',', $duedateOfActions);
        return  max($arrayStatus);
    }

    public static function getDuedateOfOldTask($oldDuedate)
    {
        return empty($oldDuedate) ? '' : date('Y-m-d', strtotime($oldDuedate));
    }

    /**
     * Get status of issue from multi value status of actions
     * @param type $statusOfActions actions 's status of issue
     * @param $statusBackup status of old tasks, use if $statusOfActions empty
     *
     * @return string
     */
    public static function getStatusOfIssue($statusOfActions, $oldStatus = null)
    {
        if (empty($statusOfActions)) {
            return static::getStatusOfOldTask($oldStatus);
        }

        $arrayStatus = explode(',', $statusOfActions);
        if (!is_array($arrayStatus)) {
            return '';
        }
        if (in_array(self::STATUS_PROCESS, $arrayStatus)) {
            return 'In-Progress'; 
        }
        if (in_array(self::STATUS_NEW, $arrayStatus)) {
            return 'Open';
        }
        if (in_array(self::STATUS_REOPEN, $arrayStatus)) {
            return 'Re-Open';
        }
        return 'Closed';
    }

    public static function getStatusOfOldTask($oldStatus)
    {
        switch ($oldStatus) {
            case self::STATUS_NEW:
                return 'Open';
            case self::STATUS_PROCESS:
            case self::STATUS_RESOLVE:
                return 'In-Progress'; 
            case self::STATUS_CLOSED:
                return 'Closed';
            default:
                return 'N/A';
        }
    }

    /**
     * get list task
     *
     * @param int $projectId
     * @param array $type
     * @param array $option
     * @param array $statusClose
     * @return collection
     */
    public static function getList($projectId, $type = null, $option = [], $listTaskRisk = null, $statusClose = null, $listStatusIssue = null)
    {
        if (!$type) {
            $type = self::getTypeIssues(true);
            if ($listTaskRisk) {
                $type[] = self::TYPE_RISK;
            }
        }
        $type = (array) $type;
        $pager = Config::getPagerDataQuery();
        $tableTask = self::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();
        $tableTaskAction = TaskAction::getTableName();
        $collection = self::select("{$tableTask}.id as id", 'title', 
                'priority', "{$tableTask}.duedate", "{$tableTask}.type as type", "{$tableTask}.project_id", "{$tableTask}.created_by",
                "{$tableTask}.created_at as created_at", "{$tableTask}.updated_at as updated_at",
                DB::raw("GROUP_CONCAT(DISTINCT  SUBSTRING_INDEX({$tableEmployee}.email, "
                . "'@', 1) SEPARATOR ', ') as email"),
                DB::raw("GROUP_CONCAT($tableTaskAction.status) as status"),
                "{$tableTask}.status as status_backup")
            
            ->leftJoin($tableTaskAssigns, "{$tableTaskAssigns}.task_id", '=', "{$tableTask}.id")
            ->leftJoin($tableTaskAction, "{$tableTaskAction}.issue_id", '=', "{$tableTask}.id");
        $collection->addSelect(DB::raw("GROUP_CONCAT($tableTaskAction.duedate) as task_duedate"));
        $collection->addSelect("{$tableTask}.duedate as task_duedate_backup");
        $collection->addSelect(
            DB::raw("(SELECT COUNT(id) FROM tasks as tasks_child WHERE parent_id = tasks.id) AS count_issues")
        );
            $collection->leftJoin($tableEmployee, "{$tableEmployee}.id", '=',
                "{$tableTaskAssigns}.employee_id")
            ->where('project_id', $projectId);
        if (isset($statusClose)) {
            $collection->where(function ($query) use ($tableTask, $statusClose, $type) {
                $query->orWhere(function ($qR) use ($tableTask)  {
                    $qR->where("{$tableTask}.type", self::TYPE_CONTRACT_CONFIRM)
                        ->whereNotIn("{$tableTask}.status", [self::STATUS_APPROVED, self::STATUS_CLOSED, self::STATUS_REJECT]);
                })
                ->orWhere(function ($qR1) use ($tableTask, $statusClose, $type) {
                    $qR1->whereIn("{$tableTask}.type", $type)
                        ->whereNotIn("{$tableTask}.status", [self::STATUS_CLOSED, self::STATUS_REJECT, self::STATUS_RESOLVE]);
                });
            });
        } else  {
            $collection->whereIn("{$tableTask}.status", [self::STATUS_NEW, self::STATUS_PROCESS, self::STATUS_REOPEN, self::STATUS_CLOSED])
                ->where($tableTaskAssigns.'.role', TaskAssign::ROLE_OWNER)
                ->whereIn($tableTask.'.type', $type);
        }

        $collection->groupBy("{$tableTask}.id")
                    ->orderBy("{$tableTask}.status")
                    ->orderBy('priority', 'desc')
                    ->orderBy("{$tableTaskAction}.duedate", 'ASC')
                    ->orderBy("{$tableTask}.created_at", 'desc')
                    ->orderBy("{$tableTask}.type", 'asc');

        $empFilter = Form::getFilterData("{$tableEmployee}.email", null);
        if ($empFilter) {
            $collection->where("{$tableEmployee}.email", 'LIKE', "%".addslashes(trim($empFilter)) . "%");
        }

        if (isset($listStatusIssue)) {
            $collection->whereIn("{$tableTaskAction}.status", $listStatusIssue);
        }

        $stateFilter = Form::getFilterData('exception', "{$tableTask}.status");
        if ($stateFilter) {
            $collection->where(function ($query) use ($stateFilter, $tableTaskAction, $tableTask) {
                $query->where(function ($q) use ($stateFilter, $tableTaskAction, $tableTask) {
                    $q->whereNull("{$tableTaskAction}.status")
                        ->where("{$tableTask}.status", $stateFilter);
                })
                    ->orWhere(function ($q) use ($stateFilter, $tableTaskAction, $tableTask) {
                        $q->where("{$tableTaskAction}.status", $stateFilter);
                    });
            });
        }

        $priorityFilter = Form::getFilterData("{$tableTask}.priority", null);
        if ($priorityFilter) {
            $collection->where("{$tableTask}.priority", $priorityFilter);
        }

        if (isset($option['get_all']) && $option['get_all']) {
            return $collection->get();
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    public static function getTaskChild($taskId)
    {
        $tableTask = self::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();
        return self::leftJoin($tableTaskAssigns, "{$tableTaskAssigns}.task_id", '=', "{$tableTask}.id")
            ->leftJoin($tableEmployee, "{$tableEmployee}.id", '=', "{$tableTaskAssigns}.employee_id")
            ->where('tasks.parent_id', $taskId)
            ->select("tasks.id as id", 'title', "tasks.status",
                'priority', 'duedate', "tasks.type as type",
                "tasks.created_at as created_at",
                "tasks.action_type",
                DB::raw("GROUP_CONCAT(SUBSTRING_INDEX(employees.email, "
                . "'@', 1) SEPARATOR ', ') as email"))
            ->orderBy("tasks.created_at", 'desc')
            ->groupBy('tasks.id')
            ->get();
    }

    public static function getById($issueId)
    {
        $tableTask = self::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tableTeam = Team::getTableName();
        $tableTaskTeam = TaskTeam::getTableName();
        $tblTaskComment = TaskComment::getTableName();
        return self::where("{$tableTask}.id", $issueId)
            ->leftJoin("$tableTaskAssigns as assign", function ($join) use ($tableTask) {
                $join->on('assign.task_id', '=', "{$tableTask}.id")
                    ->where('assign.role', '=', TaskAssign::ROLE_OWNER);
            })
            ->leftJoin("{$tableEmployee} as employee_assign", "employee_assign.id", '=', "assign.employee_id")
            ->leftJoin("$tableTaskAssigns as assign2", function ($join) use ($tableTask) {
                $join->on('assign2.task_id', '=', "{$tableTask}.id")
                    ->where('assign2.role', '=', TaskAssign::ROLE_ASSIGNEE);
            })
            ->leftJoin("{$tableEmployee} as employee_assign_2", "employee_assign_2.id", '=', "assign2.employee_id")
            ->leftJoin("$tableTaskAssigns as approver", function ($join) use ($tableTask) {
                $join->on('approver.task_id', '=', "{$tableTask}.id")
                    ->where('approver.role', '=', TaskAssign::ROLE_APPROVER);
            })
            ->leftJoin("{$tableEmployee} as employee_approver", "employee_approver.id", '=', "approver.employee_id")
            ->leftJoin($tableTeamMember, "{$tableTeamMember}.employee_id", '=', "employee_assign.id")
            ->leftJoin($tableTaskTeam, "{$tableTaskTeam}.task_id", '=', "{$tableTask}.id")
            ->leftJoin($tableTeam, "{$tableTaskTeam}.team_id", '=', "{$tableTeam}.id")
            ->leftJoin('projs', "projs.id", "=", "{$tableTask}.project_id")
            ->leftJoin('project_members', "project_members.project_id", "=", "projs.id")
            ->leftJoin("{$tblTaskComment} as task_cmt", "task_cmt.task_id", '=', "{$tableTask}.id")
            ->select("{$tableTask}.id as id", 'title', "tasks.status", "tasks.content",
                'priority', 'duedate', "tasks.type as type", "tasks.project_id", "{$tableTaskTeam}.team_id as task_team",
                "tasks.impact", "tasks.pqa_suggestion", "tasks.solution", "employee_assign.id as employee_id",
                "tasks.created_at as created_at", "tasks.updated_at", "{$tableTeam}.name as team_name", "{$tableTask}.cause",
                DB::raw("SUBSTRING_INDEX(employee_assign.email, ". "'@', 1) as email_assign"),
                "employee_assign_2.id as assign_2_id", DB::raw("SUBSTRING_INDEX(employee_assign_2.email, ". "'@', 1) as assign_2_email"),
                "employee_approver.id as approver_id", DB::raw("SUBSTRING_INDEX(employee_approver.email, ". "'@', 1) as approver_email"),
                "tasks.process", "tasks.label", "tasks.correction", "tasks.corrective_action",
                "task_cmt.content as comment",
                "tasks.opportunity_source", "tasks.cost", "tasks.expected_benefit", "tasks.action_plan", "tasks.actual_date", "tasks.action_status"
            )
            ->leftJoin("$tableTaskAssigns as reporter", function ($join) use ($tableTask) {
                $join->on('reporter.task_id', '=', "{$tableTask}.id")
                    ->where('reporter.role', '=', TaskAssign::ROLE_REPORTER);
            })
            ->leftJoin("{$tableEmployee} as employee_reporter", "employee_reporter.id", '=', "reporter.employee_id")
            ->addSelect("employee_reporter.id as employee_reporter", DB::raw("SUBSTRING_INDEX(employee_reporter.email, "
                . "'@', 1) as email_reporter"), "employee_reporter.name as name_reporter")
            ->first();
    }

    /**
     * List tasks of risk
     *
     * @param int $riskId
     * @return Task collection
     */
    public static function getTaskRisk($riskId)
    {
        $tableTask = self::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();
        $tableTaskRisk = TaskRisk::getTableName();

        return self::leftJoin($tableTaskAssigns, "{$tableTaskAssigns}.task_id", '=', "{$tableTask}.id")
            ->leftJoin($tableEmployee, "{$tableEmployee}.id", '=', "{$tableTaskAssigns}.employee_id")
            ->leftJoin($tableTaskRisk, "{$tableTaskRisk}.task_id", '=', "{$tableTask}.id")
            ->where("{$tableTaskRisk}.risk_id", $riskId)
            ->select("tasks.id as id", 'title', "tasks.status",
                'priority', 'duedate', "tasks.type as type",
                "tasks.created_at as created_at",
                "tasks.action_type",
                DB::raw("GROUP_CONCAT(SUBSTRING_INDEX(employees.email, "
                . "'@', 1) SEPARATOR ', ') as email"))
            ->orderBy("tasks.created_at", 'desc')
            ->groupBy('tasks.id')
            ->get();
    }

    /**
     * get status of task item
     *
     * @return string
     */
    public function getStatus()
    {
        $status = $this->status;
        $statusLabel = self::statusLabel();
        if (isset($statusLabel[$status])) {
            return $statusLabel[$status];
        }
        return $statusLabel[self::STATUS_NEW];
    }

    /**
     * get priority of task item
     *
     * @return string
     */
    public function getPriority()
    {
        $priority = $this->priority;
        $priorityLabel = self::priorityLabel();
        if (isset($priorityLabel[$priority])) {
            return $priorityLabel[$priority];
        }
        return $priorityLabel[self::PRIORITY_LOW];
    }

    /**
     * get freequency ò report of task item
     *
     * @return string
     */
    public function getFreequencyOfReport()
    {
        $freequencyOfReport = $this->freequency_report;
        $freequeencyLabel = self::getLableFrequencyOfReport();
        if (isset($freequeencyLabel[$freequencyOfReport])) {
            return $freequeencyLabel[$freequencyOfReport];
        }
        return $freequeencyLabel[self::REPORT_WEEKLY];
    }

    /**
     * get action type of task item
     *
     * @return string
     */
    public function getAction()
    {
        $action = $this->action_type;
        $actionLabel = self::actionLabel();
        if (isset($actionLabel[$action])) {
            return $actionLabel[$action];
        }
        return $actionLabel[self::ACTION_CORRECTIVE];
    }

    /**
     * get type of task item
     *
     * @return string
     */
    public function getType()
    {
        $type = $this->type;
        $typeLabel = self::typeLabel();
        if (isset($typeLabel[$type])) {
            return $typeLabel[$type];
        }
        return $typeLabel[self::TYPE_ISSUE];
    }

    /**
     * get grid data of task
     *
     * @return collection
     */
    public static function getGridData($projectId, $type = null)
    {
        $pager = Config::getPagerData();
        $tableTask = self::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();

        $collection = self::select("{$tableTask}.id as id", 'title', "{$tableTask}.status",
                'priority', 'duedate', "{$tableTask}.type as type",
                "{$tableTask}.created_at as created_at",
                DB::raw("GROUP_CONCAT(SUBSTRING_INDEX({$tableEmployee}.email, "
                . "'@', 1) SEPARATOR ', ') as email"))
            ->leftJoin($tableTaskAssigns, "{$tableTaskAssigns}.task_id", '=',
                "{$tableTask}.id")
            ->leftJoin($tableEmployee, "{$tableEmployee}.id", '=',
                "{$tableTaskAssigns}.employee_id")
            ->where('project_id', $projectId)
            ->groupBy("{$tableTask}.id");
        if ($type != null) {
            if (is_array($type)) {
                $collection->whereIn("{$tableTask}.type", $type);
            } else {
                $collection->where("{$tableTask}.type", $type);
            }
        }
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy("{$tableTask}.status")
                ->orderBy('priority', 'desc')
                ->orderBy("{$tableTask}.created_at", 'desc')
                ->orderBy("{$tableTask}.type", 'asc');
        }
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get grid data of task
     *
     * @return collection
     */
    public static function getGridDataGeneral($urlGeneralTask = null, $countTask = false)
    {
        $pager = Config::getPagerData($urlGeneralTask);
        $tableTask = self::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();
        $collection = self::select($tableTask.'.id', $tableTask.'.title',
                $tableTask.'.status', $tableTask.'.priority',
                $tableTask.'.duedate', $tableTask.'.created_at',
                DB::raw("GROUP_CONCAT(SUBSTRING_INDEX({$tableEmployee}.email, "
                . "'@', 1) SEPARATOR ', ') as email"))
            ->leftJoin($tableTaskAssigns, "{$tableTaskAssigns}.task_id", '=',
                $tableTask.'.id')
            ->leftJoin($tableEmployee, $tableEmployee.'.id', '=',
                $tableTaskAssigns.'.employee_id')
            ->where($tableTask.'.type', self::TYPE_GENERAL)
            ->groupBy($tableTask.'.id');
        // get filter email
        $permission = Permission::getInstance();
        $userCurrent = $permission->getEmployee();
        $filterEmail = Form::getFilterData('email', null, $urlGeneralTask);
        // check permission
        if ($permission->isScopeCompany(null, 'project::task.general.view')) {
            // view all task general of company
            if ($filterEmail) {
                $collection->whereIn($tableTask.'.id', function($query) use
                    ($tableTask, $userCurrent, $tableTaskAssigns, $filterEmail,
                        $tableEmployee
                ) {
                    $query->select('tmp_tasks.id')
                        ->from($tableTask . ' as tmp_tasks')
                        ->leftJoin($tableTaskAssigns . ' as tmp_tasks_assign',
                            'tmp_tasks_assign.task_id', '=', 'tmp_tasks.id')
                        ->leftJoin($tableEmployee . ' as tmp_employee',
                            'tmp_employee.id', '=', 'tmp_tasks_assign.employee_id')
                        ->where('tmp_employee.email', 'REGEXP', addslashes($filterEmail));
                });
            }
        } else {
            // only view task self assign of created by
            $collection->where(function ($query) use
                ($tableTask, $userCurrent, $tableTaskAssigns, $filterEmail, $tableEmployee) {
                $query->orWhere($tableTask.'.created_by', $userCurrent->id)
                    ->orWhereIn($tableTask.'.id', function($query) use
                        ($tableTask, $userCurrent, $tableTaskAssigns,
                        $filterEmail, $tableEmployee
                    ){
                        $query->select('tmp_tasks.id')
                            ->from($tableTask . ' as tmp_tasks')
                            ->leftJoin($tableTaskAssigns . ' as tmp_tasks_assign',
                                'tmp_tasks_assign.task_id', '=', 'tmp_tasks.id')
                            ->where('tmp_tasks_assign.employee_id', $userCurrent->id);
                        if ($filterEmail) {
                            $query->leftJoin($tableEmployee . ' as tmp_employee',
                                'tmp_employee.id', '=', 'tmp_tasks_assign.employee_id')
                                ->where('tmp_employee.email', 'REGEXP', addslashes($filterEmail));
                        }
                    });
            });
        }
        if ($countTask) {
            return $collection->whereNotIn("{$tableTask}.status", Task::getStatusCloseOrReject())
                                ->where("{$tableTaskAssigns}.employee_id", $userCurrent->id)
                                ->where("{$tableTaskAssigns}.role", "=", TaskAssign::ROLE_OWNER)
                                ->get()
                                ->count();
        }

        if (Form::getFilterPagerData('order', $urlGeneralTask)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy($tableTask.'.status', 'asc')
                ->orderBy($tableTask.'.priority', 'desc')
                ->orderBy($tableTask.'.created_at');
        }
        self::filterGrid($collection, ['email'], $urlGeneralTask);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get grid data of task
     *
     * @return collection
     */
    public static function getSelfRelateTask($employeeId, $options = [])
    {
        $pager = Config::getPagerData();
        $tableTask = self::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableProject = Project::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();
        $tableTeamProject = TeamProject::getTableName();
        $tableTeam = Team::getTableName();
        $collection = self::select("{$tableTask}.id as id", 'title', "{$tableTask}.status",
                $tableTask.'.priority', $tableTask.'.duedate', "{$tableTask}.type as type",
                "{$tableTask}.created_at as created_at", "{$tableEmployee}.email as assignee",
                "{$tableProject}.name as project_name", "{$tableProject}.manager_id as manager_id",
                        "ProjEmployee.email as email",
                        DB::raw("GROUP_CONCAT(DISTINCT({$tableTeam}.name) SEPARATOR ',') as team_name"),
                        $tableTask.'.project_id')
            ->leftJoin($tableTaskAssigns, "{$tableTaskAssigns}.task_id", '=',
                "{$tableTask}.id")
            ->leftJoin($tableEmployee, "{$tableEmployee}.id", '=',
                "{$tableTaskAssigns}.employee_id")
            ->leftJoin($tableProject, "{$tableProject}.id", '=', "{$tableTask}.project_id")
            ->leftJoin($tableEmployee.' as ProjEmployee', 'ProjEmployee.id', '=', "{$tableProject}.manager_id")
            ->leftJoin($tableTeamProject, "{$tableTeamProject}.project_id", '=', "{$tableTask}.project_id")
            ->leftJoin($tableTeam, "{$tableTeam}.id", '=', "{$tableTeamProject}.team_id")
            ->whereIn("{$tableTask}.status", self::getStatusTypeMySelf(true))
            ->where(function ($query) use ($tableTaskAssigns, $employeeId, $tableTask) {
                $query->where("{$tableTaskAssigns}.employee_id", $employeeId)
                    ->orwhere("{$tableTask}.created_by", $employeeId);
            })
            ->groupBy("{$tableTask}.id");

        if (Project::isUseSoftDelete()) {
            $collection->whereNull("{$tableProject}.deleted_at");
        }

        if (!empty($options['status'])) {
            $collection->where("{$tableTask}.status", $options['status']);
        }
        if (!empty($options['title'])) {
            $collection->where("{$tableTask}.title", 'Like', '%'.$options['title'].'%');
        }
        if (!empty($options['priority'])) {
            $collection->where("{$tableTask}.priority", $options['priority']);
        }
        if (!empty($options['created_at'])) {
            $collection->where("{$tableTask}.created_at", $options['created_at']);
        }
        if (!empty($options['duedate'])) {
            $collection->where("{$tableTask}.duedate", $options['duedate']);
        }
        if (!empty($options['project_name'])) {
            $collection->where("{$tableProject}.name", 'Like', '%'.$options['project_name'].'%');
        }
        if (!empty($options['assignee'])) {
            $collection->where("{$tableEmployee}.email", 'Like', '%'.$options['assignee'].'%');
        }
        if (!empty($options['pm'])) {
            $collection->where("ProjEmployee.email", 'Like', '%'.$options['pm'].'%');
        }
        return $collection;
    }

    /**
     * get grid data of task
     *
     * @return collection
     */
    public static function getGridDataSelfTask($employeeId, $urlFilter = null, $countSelfTask = false)
    {
        $pager = Config::getPagerData($urlFilter);
        $tableTask = self::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableProject = Project::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();
        $tableTeamProject = TeamProject::getTableName();
        $tableTeam = Team::getTableName();
        $userCurrent = Permission::getInstance()->getEmployee();
        $collection = self::select("{$tableTask}.id as id", 'title', "{$tableTask}.status",
                $tableTask.'.priority', $tableTask.'.duedate', "{$tableTask}.type as type",
                "{$tableTask}.created_at as created_at",
                "{$tableProject}.name as project_name", "{$tableProject}.manager_id as manager_id",
                        "ProjEmployee.email as email",
                        DB::raw("GROUP_CONCAT(DISTINCT({$tableTeam}.name) SEPARATOR ',') as team_name"),
                        $tableTask.'.project_id')
            ->leftJoin($tableTaskAssigns, "{$tableTaskAssigns}.task_id", '=',
                "{$tableTask}.id")
            ->leftJoin($tableEmployee, "{$tableEmployee}.id", '=',
                "{$tableTaskAssigns}.employee_id")
            ->join($tableProject, "{$tableProject}.id", '=', "{$tableTask}.project_id")
            ->leftJoin($tableEmployee.' as ProjEmployee', 'ProjEmployee.id', '=', "{$tableProject}.manager_id")
            ->leftJoin($tableTeamProject, "{$tableTeamProject}.project_id", '=', "{$tableTask}.project_id")
            ->leftJoin($tableTeam, "{$tableTeam}.id", '=', "{$tableTeamProject}.team_id")
            ->whereIn("{$tableTask}.status", self::getStatusTypeMySelf(true))
            ->where("{$tableTaskAssigns}.employee_id", $employeeId)
            ->groupBy("{$tableTask}.id");
        if ($countSelfTask) {
            return $collection->whereNotIn("{$tableTask}.status", Task::getStatusCloseOrReject())
                                ->get()
                                ->count();
        }
        if (Project::isUseSoftDelete()) {
            $collection->whereNull("{$tableProject}.deleted_at");
        }

        if (Form::getFilterPagerData('order', $urlFilter)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy("{$tableTask}.priority", 'desc')
                ->orderBy($tableTask.'.created_at', 'desc')
                ->orderBy("{$tableTask}.status")
                ->orderBy("{$tableTask}.type", 'asc');
        }
        $filter = Form::getFilterData(null, null, $urlFilter);
        if (!$filter) {
            $collection->whereNotIn("{$tableTask}.status", Task::getStatusCloseOrReject());
        }
        self::filterGrid($collection, [], $urlFilter);

        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * get list task quality
     * @param int
     * @return collection
     */
    public static function getListTaskQuality($projectId)
    {
        if ($collection = CacheHelper::get(self::KEY_CACHE_ALL, $projectId)) {
            return $collection;
        }
        $tableTask = self::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();

        $collection =  self::select("{$tableTask}.id as id", 'title', "{$tableTask}.status",
                'priority', 'duedate', 'content', 'actual_date',
                "{$tableTask}.created_at as created_at",
                DB::raw("GROUP_CONCAT(SUBSTRING_INDEX({$tableEmployee}.email, "
                . "'@', 1) SEPARATOR ', ') as email"))
            ->leftJoin($tableTaskAssigns, "{$tableTaskAssigns}.task_id", '=',
                "{$tableTask}.id")
            ->leftJoin($tableEmployee, "{$tableEmployee}.id", '=',
                "{$tableTaskAssigns}.employee_id")
            ->where('project_id', $projectId)
            ->where($tableTask.'.type', self::TYPE_QUALITY_PLAN)
            ->orderBy("{$tableTask}.status")
            ->orderBy('priority', 'desc')
            ->orderBy("{$tableTask}.created_at", 'desc')
            ->groupBy("{$tableTask}.id")
            ->get();
        CacheHelper::put(self::KEY_CACHE_ALL, $collection, $projectId);
        return $collection;
    }
    /**
     * overwrite save model
     *
     * @param array $options
     * @param object $project
     */
    public function save(
        array $options = array(),
        $project = null,
        array $config = []
    ) {
        DB::beginTransaction();
        try {
            CacheHelper::forget(Project::KEY_CACHE_TASK, $this->project_id);
            $this->content = trim($this->content);
            $this->title = trim($this->title);
            if (!$this->actual_date) {
                if (in_array($this->status,
                    [self::STATUS_APPROVED,
                    self::STATUS_CLOSED,
                    self::STATUS_REJECT])
                ) {
                    $this->actual_date = Carbon::now()->format('Y-m-d H:i:s');
                } else {
                    $this->actual_date = null;
                }
            }
            $originData = $this->id ? $this->getOriginal() : false;
            //check has ncm
            if (isset($config['ncm'])) {
                $ncmData = $this->ncmRequest;
                if ($ncmData) {
                    $ncmData = $ncmData->getAttributes();
                    $originNcmData = [];
                    foreach ($ncmData as $key => $value) {
                        $originNcmData['ncm_' . $key] = $value;
                    }
                    $originData = array_merge($originData, $originNcmData);
                }
            }
            //check has task_assgin
            if (isset($config['task_assign'])) {
                $taskAssiginData = TaskAssign::where('task_id', $this->id)->get();
                if (!$taskAssiginData->isEmpty()) {
                    $ncmData = $this->ncmRequest;
                    $originTaskAssign = [];
                    if ($ncmData) {
                        foreach ($taskAssiginData as $taskAssign) {
                            if ($taskAssign->role == $ncmData::ASSIGN_DEPART_REPRESENT) {
                                $originTaskAssign['task_assign_depart_represent'] = $taskAssign->employee_id;
                            }
                            if ($taskAssign->role == $ncmData::ASSIGN_TESTER) {
                                $originTaskAssign['task_assign_tester'] = $taskAssign->employee_id;
                            }
                            if ($taskAssign->role == $ncmData::ASSIGN_EVALUATOR) {
                                $originTaskAssign['task_assign_evaluater'] = $taskAssign->employee_id;
                            }
                        }
                        $originData = array_merge($originData, $originTaskAssign);
                    }
                }
            }
            //check has teams
            if (isset($config['teams'])) {
                $teamsOldIds = TaskTeam::where('task_id', $this->id)->lists('team_id')->toArray();
                $teamsOlds = Team::whereIn('id', $teamsOldIds)->lists('name')->toArray();
                $teamNews = Team::whereIn('id', $config['teams'])->lists('name')->toArray();
                if ($teamsOldIds) {
                    $originData['task_team'] = implode(', ', $teamsOlds);
                }
                if ($teamNews) {
                    $config['task_team'] = implode(', ', $teamNews);
                }
            }
            //check create task
            if (isset($config['is_create']) && $config['is_create']) {
                $originData['create_new'] = 'create';
            }
            $newTask = $this->id ? false : true;
            $result = parent::save($options);
            if (!isset($config['history']) || $config['history']) {
                $config['historyResult'] =
                    TaskHistory::storeHistory($this, $project, $originData, null, $config);
            }
            if ($project) {
                $this->afterSaveTask($project, $newTask);
            }
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public static function store($dataIssue)
    {
        if (isset($dataIssue['id'])) {
            $issueId = $dataIssue['id'];
            $issue = Task::find($issueId);
        } else {
            $issue = new Task();
        }
        DB::beginTransaction();
        try {
            $issue->fill($dataIssue);
            $arrCanNull = ["type", "duedate", "actual_date",
                "deleted_at", "report_content", "freequency_report", "process",
                "created_by", "parent_id", "action_type", "impact", "pqa_suggestion", 'bonus_money'];
            foreach ($arrCanNull as $field) {
                if (!isset($dataIssue[$field]) || empty($dataIssue[$field]) || !$dataIssue[$field]) {
                    $issue->$field = null;
                }
            }
            $issue->save();
            DB::commit();
            return $issue;
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    /**
     * process after save task
     */
    protected function afterSaveTask($project = null, $newTask = false)
    {
        if (!$project) {
            $project = $this->getProject();
        }
        if ($this->isTaskIssues()) {
            CacheHelper::forget(Project::KEY_CACHE_TASK, $project->id);
            return true;
        }
        if ($this->isTaskCustomerIdea()) {
            CacheHelper::forget(self::KEY_CACHE_CR, $this->project_id);
            $project->refreshFlatDataPoint();
            return true;
        }
        $statusTask = $this->status;
        switch ($this->type) {
            case self::TYPE_WO:
                $project->refreshDataWorkOrder($statusTask);
                if ($statusTask == self::STATUS_APPROVED) {
                    SourceServer::saveFromRequest($project);
                    CacheHelper::forget(ProjectPoint::KEY_CACHE, $project->id);
                    CacheHelper::forget(ProjectPoint::KEY_CACHE_WO, $project->id);
                    CacheHelper::forget(Project::KEY_CACHE_MEMBER, $project->id);
                    CacheHelper::forget(ProjectWOBase::KEY_CACHE_WO, $project->id);
                    CacheHelper::forget(ProjectMember::KEY_CACHE_MEMBER_APPROVED, $project->id);
                    CacheHelper::forget(CssResult::KEY_CACHE, $project->id);
                    $project->refreshFlatDataPoint(false);//set is change color = false
                    $project = Project::find($project->id);
                    ProjPointBaseline::baselineItem($project);
                    // create reward default
                    ProjRewardBudget::createRewardBudgetsDefault($project);
                    if ($project->state == Project::STATE_NEW) {
                        $project->state = Project::STATE_PROCESSING;
                        $project->save();
                    } elseif ($project->type == Project::TYPE_BASE &&
                        $project->state == Project::STATE_CLOSED
                    ) {
                        self::createReward($project);
                        break;
                    }
                    if ($project->isLong()) {
                        self::createRewardLong($project);
                    }
                }
                break;
            case self::TYPE_QUALITY_PLAN:
                if ($newTask) {
                    ProjectLog::insertLogTaskQualityPlan($this);
                }
                CacheHelper::forget(self::KEY_CACHE_ALL, $project->id);
                break;
            case self::TYPE_SOURCE_SERVER:
                $project->refreshDataSourceServer($statusTask);
                if ($statusTask == self::STATUS_APPROVED) {
                    CacheHelper::forget(Project::KEY_CACHE, $project->id);
                    CacheHelper::forget(SourceServer::KEY_CACHE, $project->id);
                }
                break;
            case self::TYPE_COMPLIANCE:
                CacheHelper::forget(self::KEY_CACHE_COMPLIANCE, $this->project_id);
                $project->refreshFlatDataPoint();
                break;
        }
        return true;
    }

    /**
     * get project of task
     *
     * @return object
     */
    public function getProject()
    {
        return Project::find($this->project_id);
    }

    /**
     * check edit workorder
     * @param int
     * @return boolean
     */
    public static function checkEditWorkOrder($projectId)
    {
        $tableTask = self::getTableName();
        $tableTaskAssngin = TaskAssign::getTableName();

        $item = DB::table($tableTask . ' AS t_task')
            ->join($tableTaskAssngin . ' AS t_task_ass', 't_task_ass.task_id',
                '=', 't_task.id')
            ->where('t_task.project_id', $projectId)
            ->whereIn('t_task.status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED])
            ->where('t_task.type', self::TYPE_WO)
            ->where('t_task_ass.status', TaskAssign::STATUS_REVIEWED)
            ->count();
        if($item > 0) {
            return false;
        }
        return true;
    }

    /**
     * get type of approved
     *
     * @return array
     */
    public static function getTypeApproved($returnKey = false)
    {
        return self::getStatusFollowKey([
            self::TYPE_WO,
            self::TYPE_SOURCE_SERVER
        ], self::typeLabel(), $returnKey);
    }

    /**
     * check task type approve
     *
     * @return boolean
     */
    public function isTaskApproved()
    {
        if (in_array($this->type, self::getTypeApproved(true))) {
            return true;
        }
        return false;
    }

    /**
     * check task type customer idea
     *
     * @return boolean
     */
    public function isTaskCustomerIdea()
    {
        if ($this->type == self::TYPE_COMMENDED ||
            $this->type == self::TYPE_CRITICIZED
        ) {
            return true;
        }
        return false;
    }

    /**
     * get type of customer idea
     *
     * @return array
     */
    public static function getTypeCustomerIdea($returnKey = false)
    {
        return self::getStatusFollowKey([
            self::TYPE_COMMENDED,
            self::TYPE_CRITICIZED
        ], self::typeLabel(), $returnKey);
    }

    /**
     * get type of task normal
     *
     * @return array
     */
    public static function getTypeNormal($returnKey = false)
    {
        return self::getStatusFollowKey([
            self::TYPE_ISSUE,
            self::TYPE_COMMENDED,
            self::TYPE_CRITICIZED,
            self::TYPE_QUALITY_PLAN,
            self::TYPE_SOURCE_SERVER,
            self::TYPE_COMPLIANCE,
            self::TYPE_ISSUE_COST,
            self::TYPE_ISSUE_QUA,
            self::TYPE_ISSUE_TL,
            self::TYPE_ISSUE_PROC,
            self::TYPE_ISSUE_CSS,
            self::TYPE_RISK,
        ], self::typeLabel(), $returnKey);
    }

    /**
     * check type task is wo, issue.., not reward, group point
     *
     * @return boolean
     */
    public function isTypeTask()
    {
        if (in_array($this->type, [
            self::TYPE_REWARD,
            self::TYPE_GROUP_POINT
        ])) {
            return false;
        }
        return true;
    }

    /**
     * get task type issue
     *
     * @param boolean $returnKey
     * @return array
     */
    public static function getTypeIssues($returnKey = false)
    {
        return self::getStatusFollowKey([
            self::TYPE_ISSUE,
            self::TYPE_COMMENDED,
            self::TYPE_CRITICIZED,
            self::TYPE_ISSUE_COST,
            self::TYPE_ISSUE_QUA,
            self::TYPE_ISSUE_TL,
            self::TYPE_ISSUE_PROC,
            self::TYPE_ISSUE_CSS
        ], self::typeLabel(), $returnKey);
    }

    /**
     * get task type issue
     *
     * @param boolean $returnKey
     * @return array
     */
    public static function getTypeIssuesCreator($returnKey = false)
    {
        return self::getStatusFollowKey([
            self::TYPE_ISSUE_COST,
            self::TYPE_ISSUE_QUA,
            self::TYPE_ISSUE_TL,
            self::TYPE_ISSUE_PROC,
            self::TYPE_ISSUE_CSS
        ], self::typeLabel(), $returnKey);
    }

    /**
     * check task is type issues
     *
     * @return boolean
     */
    public function isTaskIssues()
    {
        if (in_array($this->type, self::getTypeIssues(true))) {
            return true;
        }
        return false;
    }

    /**
     * get content change wo by project id
     * @param int
     * @return int
     */
    public static function getIdTaskChangeWoByProjectId($projectId)
    {
        $task = self::where('project_id', $projectId)
                    ->where('type', self::TYPE_WO)
                    ->where('status', self::STATUS_APPROVED)
                    ->orderBy('created_at', 'desc')
                    ->first();
        return $task->id;
    }

    /**
     * insert task source server
     * @param int
     * @param array
     * @param array
     * @param array
     * @param array
     * @param int
     * @param array
     * @param int
     */
    public static function insertTaskSourceServer($projectId, $arrayField, $arrayAttributes, $arrayOrigin, $sourceServer, $checkAddNewForEditApproved, $sourceServerApproved, $checkHasDraftEdit)
    {
        $task = self::where('project_id', $projectId)
                    ->where('type', self::TYPE_SOURCE_SERVER)
                    ->where('status', self::STATUS_FEEDBACK)
                    ->first();
        if ($task) {
            $task->status = self::STATUS_SUBMITTED;
            $pqa = ProjectMember::getQaListOfProject($projectId);
            if ($pqa) {
                $idAssign = $pqa->employee_id;
            } else {
                $emailPqa = ViewProject::getQAAccount();
                $idPqa = Employee::getIdEmpByEmail($emailPqa);
                $idAssign = $idPqa;
            }
            $task->save();
            TaskAssign::insertMember($task, $idAssign, $idAssign, [
                'type' => TaskAssign::TYPE_STATUS_CHANGE
            ]);
            $taskComment = new TaskComment;
            $taskComment->task_id = $task->id;
            $content =  '';
            $content = self::getContentTaskSourceServerRedmine($content, $arrayField, $arrayOrigin, $arrayAttributes, $sourceServer, $checkAddNewForEditApproved, $sourceServerApproved, $checkHasDraftEdit);

            $content = self::getContentTaskSourceServerGit($content, $arrayField, $arrayOrigin, $arrayAttributes, $sourceServer, $checkAddNewForEditApproved, $sourceServerApproved, $checkHasDraftEdit);
            // $content = self::getContentTaskSourceServerSvn($content, $arrayField, $arrayOrigin, $arrayAttributes, $sourceServer, $checkAddNewForEditApproved, $sourceServerApproved, $checkHasDraftEdit);
            $taskComment->content = $content;
            $taskComment->type = TaskComment::TYPE_COMMENT_SOURCE_SERVER;
            $taskComment->created_by = Permission::getInstance()->getEmployee()->id;
            $taskComment->save();
            return $task;
        } else {
            $task = new Task();
            $task->title = Lang::get('project::view.Change source server');
            $task->type = self::TYPE_SOURCE_SERVER;
            $task->status = self::STATUS_SUBMITTED;
            $task->project_id = $projectId;
            $pqa = ProjectMember::getQaListOfProject($projectId);
            if ($pqa) {
                $idAssign = $pqa->employee_id;
            } else {
                $emailPqa = ViewProject::getQAAccount();
                $idPqa = Employee::getIdEmpByEmail($emailPqa);
                $idAssign = $idPqa;
            }
            $task->priority = self::PRIORITY_NORMAL;
            $content = '';
            $content = self::getContentTaskSourceServerRedmine($content, $arrayField, $arrayOrigin, $arrayAttributes, $sourceServer, $checkAddNewForEditApproved, $sourceServerApproved, $checkHasDraftEdit);
            $content = self::getContentTaskSourceServerGit($content, $arrayField, $arrayOrigin, $arrayAttributes, $sourceServer, $checkAddNewForEditApproved, $sourceServerApproved, $checkHasDraftEdit);
            // $content = self::getContentTaskSourceServerSvn($content, $arrayField, $arrayOrigin, $arrayAttributes, $sourceServer, $checkAddNewForEditApproved, $sourceServerApproved, $checkHasDraftEdit);
            if ($content) {
                $task->content = $content;
                if($task->save()) {
                    TaskAssign::insertMember($task, $idAssign, $idAssign, [
                        'type' => TaskAssign::TYPE_STATUS_NEW
                    ]);
                    return $task;
                }
            }
            return false;
        }
    }

    /**
     * get content task source server redmine
     * @param string
     * @param array
     * @param array
     * @param array
     * @param array
     * @param int
     * @param array
     * @param int
     * @return string
     */
    public static function getContentTaskSourceServerRedmine($content, $arrayField, $arrayOrigin, $arrayAttributes, $sourceServer, $checkAddNewForEditApproved, $sourceServerApproved, $checkHasDraftEdit)
    {
        if ($arrayField['is_check_redmine']) {
            if ($arrayOrigin && $arrayOrigin['is_check_redmine']) {
                if ($arrayOrigin['is_check_redmine'] != $arrayAttributes['is_check_redmine']|| !$checkAddNewForEditApproved || $checkHasDraftEdit) {
                    $isChangeRedmine = true;
                }
                if (!$checkAddNewForEditApproved) {
                    $isChangeRedmine = true;
                }
            } else {
                $isChangeRedmine = true;
            }
            $isCheckHasContentChange = false;
            if (isset($isChangeRedmine)) {
                if ($checkAddNewForEditApproved) {
                    if ($sourceServerApproved->is_check_redmine != $sourceServer->is_check_redmine) {
                        $isCheckHasContentChange = true;
                    } else {
                        if ($arrayOrigin && isset($arrayOrigin['is_check_redmine'])) {
                            if ($arrayOrigin['is_check_redmine'] != $sourceServer->is_check_redmine) {
                                $isCheckHasContentChange = true;
                            }
                        }
                    }
                } else {
                    $isCheckHasContentChange = true;
                }
                if ($isCheckHasContentChange) {
                    if (isset($arrayAttributes['is_check_redmine'])) {
                        if ($sourceServer->is_check_redmine == Project::CHECKED_PROJECT_INDETIFY) {
                            $content .= Lang::get('project::view.Allow sync project redmine in server'). '<br>';
                        } else {
                            $content .= Lang::get('project::view.Do not use sync project redmine in server'). '<br>';
                        }
                    }
                }
            }
            if ($arrayField['id_redmine'] && isset($arrayAttributes['id_redmine'])) {
                $idRemineNew = $arrayAttributes['id_redmine'];
                if($checkAddNewForEditApproved) {
                    $idRemineOld = $sourceServerApproved->id_redmine;
                    if (!$idRemineOld) {
                        $content .= Lang::get('project::view.Create redmine identifier:').' ' .$idRemineNew. '<br>';
                    } else if (trim($idRemineOld) != trim($idRemineNew)) {
                        $content .= Lang::get('project::view.Change redmine identifier from:').' ' .$idRemineOld. ' '. Lang::get('project::view.to').' ' . $idRemineNew . '<br>';
                    }
                } else if(!$checkAddNewForEditApproved) {
                    $content .= Lang::get('project::view.Create redmine identifier:').' ' .$idRemineNew. '<br>';
                } else if ($arrayOrigin && $arrayOrigin['id_redmine']) {
                    $idRemineOld = $arrayOrigin['id_redmine'];
                    if (trim($idRemineOld) != trim($idRemineNew)) {
                        $content .= Lang::get('project::view.Change redmine identifier from:').' ' .$idRemineOld. ' '. Lang::get('project::view.to').' ' . $idRemineNew . '<br>';
                    }
                } else {
                    $content .= Lang::get('project::view.Create redmine identifier:').' ' .$idRemineNew. '<br>';
                }
            }
        }
        return $content;
    }

    /**
     * get content task source server git
     * @param string
     * @param array
     * @param array
     * @param array
     * @param array
     * @param int
     * @param array
     * @param int
     * @return string
     */
    public static function getContentTaskSourceServerGit($content, $arrayField, $arrayOrigin, $arrayAttributes, $sourceServer, $checkAddNewForEditApproved, $sourceServerApproved, $checkHasDraftEdit)
    {
        if ($arrayField['is_check_git']) {
            if ($arrayOrigin && $arrayOrigin['is_check_git']) {
                if ($arrayOrigin['is_check_git'] != $arrayAttributes['is_check_git'] || !$checkAddNewForEditApproved
                    || $checkHasDraftEdit) {
                    $isChangeGit = true;
                }
            } else {
                $isChangeGit = true;
            }

            $isCheckHasContentChange = false;
            if (isset($isChangeGit)) {
                if ($checkAddNewForEditApproved) {
                    if ($sourceServerApproved->is_check_git != $sourceServer->is_check_git) {
                        $isCheckHasContentChange = true;
                    } else {
                        if ($arrayOrigin && isset($arrayOrigin['is_check_git'])) {
                            if ($arrayOrigin['is_check_git'] != $sourceServer->is_check_git) {
                                $isCheckHasContentChange = true;
                            }
                        }
                    }
                } else {
                    $isCheckHasContentChange = true;
                }
                if ($isCheckHasContentChange) {
                    if (isset($arrayAttributes['is_check_git'])) {
                        if ($sourceServer->is_check_git == Project::CHECKED_PROJECT_INDETIFY) {
                            $content .= Lang::get('project::view.Allow sync project git in server'). '<br>';
                        } else {
                            $content .= Lang::get('project::view.Do not use sync project git in server'). '<br>';
                        }
                    }
                }
            }
            if ($arrayField['id_git'] && isset($arrayAttributes['id_git'])) {
                $idGitNew = $arrayAttributes['id_git'];
                if($checkAddNewForEditApproved) {
                    $idGitOld = $sourceServerApproved->id_git;
                    if (!$idGitOld) {
                        $content .= Lang::get('project::view.Create gitlab repo:').' ' .$idGitNew. '<br>';
                    } else if (trim($idGitOld) != trim($idGitNew)) {
                        $content .= Lang::get('project::view.Change gitlab repo from:').' ' .$idGitOld.' ' .Lang::get('project::view.to').' ' . $idGitNew . '<br>';
                    }
                } else if(!$checkAddNewForEditApproved) {
                    $content .= Lang::get('project::view.Create gitlab repo:').' ' .$idGitNew. '<br>';
                } elseif ($arrayOrigin && $arrayOrigin['id_git']) {
                    $idGitOld = $arrayOrigin['id_git'];
                    if (trim($idGitOld) != trim($idGitNew)) {
                        $content .= Lang::get('project::view.Change gitlab repo from:').' ' .$idGitOld.' ' .Lang::get('project::view.to').' ' . $idGitNew . '<br>';
                    }
                } else {
                    $content .= Lang::get('project::view.Create gitlab repo:').' ' .$idGitOld. '<br>';
                }
            }
        }
        return $content;
    }

    /**
     * get content task source server svn
     * @param string
     * @param array
     * @param array
     * @param array
     * @param array
     * @param int
     * @param array
     * @param int
     * @return string
     */
    public static function getContentTaskSourceServerSvn($content, $arrayField, $arrayOrigin, $arrayAttributes, $sourceServer, $checkAddNewForEditApproved, $sourceServerApproved, $checkHasDraftEdit)
    {
        if ($arrayField['is_check_svn']) {
            if ($arrayOrigin && $arrayOrigin['is_check_svn']) {
                if ($arrayOrigin['is_check_svn'] != $arrayAttributes['is_check_svn']|| !$checkAddNewForEditApproved || $checkHasDraftEdit) {
                    $isChangeSvn = true;
                }
                if (!$checkAddNewForEditApproved) {
                    $isChangeSvn = true;
                }
            } else {
                $isChangeSvn = true;
            }
            $isCheckHasContentChange = false;
            if (isset($isChangeSvn)) {
                if ($checkAddNewForEditApproved) {
                    if ($sourceServerApproved->is_check_svn != $sourceServer->is_check_svn) {
                        $isCheckHasContentChange = true;
                    } else {
                        if ($arrayOrigin && isset($arrayOrigin['is_check_svn'])) {
                            if ($arrayOrigin['is_check_svn'] != $sourceServer->is_check_svn) {
                                $isCheckHasContentChange = true;
                            }
                        }
                    }
                } else {
                    $isCheckHasContentChange = true;
                }
                if ($isCheckHasContentChange) {
                    if (isset($arrayAttributes['is_check_svn'])) {
                        if ($sourceServer->is_check_svn == Project::CHECKED_PROJECT_INDETIFY) {
                            $content .= Lang::get('project::view.Allow sync project svn in server'). '<br>';
                        } else {
                            $content .= Lang::get('project::view.Do not use sync project svn in server'). '<br>';
                        }
                    }
                }
            }

            if ($arrayField['id_svn'] && isset($arrayAttributes['id_svn'])) {
                $idSvnNew = $arrayAttributes['id_svn'];
                if($checkAddNewForEditApproved) {
                    $idSvnOld = $sourceServerApproved->id_svn;
                    if (!$idSvnOld) {
                        $content .= Lang::get('project::view.Create svn indentify:').' ' .$idSvnNew. '<br>';
                    } else if (trim($idSvnOld) != trim($idSvnNew)) {
                        $content .= Lang::get('project::view.Change svn indentify form:').' ' .$idSvnNew.' ' .Lang::get('project::view.to').' ' . $idSvnNew . '<br>';
                    }
                } else if(!$checkAddNewForEditApproved) {
                    $content .= Lang::get('project::view.Create svn indentify:').' ' .$idSvnNew. '<br>';
                } elseif ($arrayOrigin && $arrayOrigin['id_svn']) {
                    $idSvnOld = $arrayOrigin['id_svn'];
                    if (trim($idSvnOld) != trim($idSvnNew)) {
                        $content .= Lang::get('project::view.Change svn indentify form:').' ' .$idSvnOld.' ' .Lang::get('project::view.to').' ' . $idSvnNew . '<br>';
                    }
                } else {
                    $content .= Lang::get('project::view.Create svn indentify:').' ' .$idSvnNew. '<br>';
                }
            }
        }
        return $content;
    }

    /**
     * get task waiting approved by type
     * @param int
     * @param int
     */
    public static function getTaskWaitingApproveByType($projectId, $typeTask) {
        return self::where('project_id', $projectId)
                         ->where('type', $typeTask)
                         ->where('status', '!=', self::STATUS_APPROVED)
                         ->first();
    }

    /**
     * check hask task workorder approved
     * @param int
     * @return int
     */
    public static function checkHasTaskWorkorderApproved($projectId)
    {
        return self::where('project_id', $projectId)
                    ->where('type', self::TYPE_WO)
                    ->where('status', self::STATUS_APPROVED)
                    ->count();
    }

    /**
     * check hask task workorder
     * @param int
     * @return int
     */
    public static function checkHasTaskWorkorder($projectId)
    {
        return self::where('project_id', $projectId)
                    ->where('type', self::TYPE_WO)
                    ->count();
    }

    /**
     * get task reward
     *
     * @param model $project
     * @return model
     */
    public static function findReward($project)
    {
        if(!$project->isLong()) {
            $rewardMetaTable = ProjRewardMeta::getTableName();
            $taskTable = self::getTableName();
            return self::join($rewardMetaTable, "{$taskTable}.id", '=', "{$rewardMetaTable}.task_id")
                    ->where($taskTable.'.type', self::TYPE_REWARD)
                    ->where($taskTable.'.project_id', $project->id)
                    ->whereNull($rewardMetaTable.'.month_reward')
                    ->select($taskTable.'.*')
                    ->first();
        }
        return self::where('type', self::TYPE_REWARD)->where('project_id', $project->id)
            ->first();
    }

    /**
    * get all task of long project
    *
    */
    public static function getAllTaskofLong($project) {
        $rewardMetaTable = ProjRewardMeta::getTableName();
        $taskTable = self::getTableName();
        return self::where('type', self::TYPE_REWARD)->where('project_id', $project->id)
            ->join($rewardMetaTable, "{$taskTable}.id", '=', "{$rewardMetaTable}.task_id")
            ->select("{$rewardMetaTable}.*", "{$taskTable}.id", "{$taskTable}.project_id",
                "{$taskTable}.type", "{$taskTable}.status",  "{$taskTable}.actual_date",
                DB::raw("DATE_FORMAT({$rewardMetaTable}.month_reward, '%m-%Y') as month_format"))
            ->get();
    }

    /**
     * delete all reward
     *
     * @param model $project
     * @throws Exception
     */
    public static function deleteAllReward($project)
    {
        $tasks = self::where('type', self::TYPE_REWARD)
            ->where('project_id', $project->id)
            ->get();
        if (!$tasks || !count($tasks)) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach ($tasks as $task) {
                ProjRewardEmployee::where('task_id', $task->id)->delete();
                ProjRewardMeta::where('task_id', $task->id)->delete();
                $task->delete();
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }


    /**
     * create reward task
     *
     * @param model $project
     * @return \self
     */
    public static function createReward($project, array $option = [])
    {
        if (!self::createRewardAvailable($project)) {
            return false;
        }
        $groupLeader = $project->groupLeader;
        $pmActive = $project->getPmActive();
        $idAssign = [];
        $emailCoo = CoreConfigData::getValueDb('project.account_approver_reward');
        $task = self::findReward($project);
        if ($task) {
            return $task;
        }
        $task = new self();
        $task->setData([
            'project_id' => $project->id,
            'type' => self::TYPE_REWARD,
            'title' => 'Project Reward',
            'status' => self::STATUS_NEW,
            'priority' => self::PRIORITY_LOW,
            'content' => ''
        ]);
        $task->save();
        // add assign
        if ($pmActive) {
            $idAssign[] = [
                'employee_id' => $pmActive->id,
                'role' => TaskAssign::ROLE_PM,
                'status' => TaskAssign::STATUS_NO,
                'task_id' => $task->id
            ];
        }
        if ($groupLeader) {
            $idAssign[] = [
                'employee_id' => $groupLeader->id,
                'role' => TaskAssign::ROLE_REVIEWER,
                'status' => TaskAssign::STATUS_NO,
                'task_id' => $task->id
            ];
        }
        $idCoo = Employee::getIdEmpByEmail($emailCoo);
        if ($idCoo) {
            $idAssign[] = [
                'employee_id' => $idCoo,
                'role' => TaskAssign::ROLE_APPROVER,
                'status' => TaskAssign::STATUS_NO,
                'task_id' => $task->id
            ];
        }
        TaskAssign::insert($idAssign);
        //add member to reward
        ProjRewardEmployee::createRewardEmployee($task);
        // add meta to reward
        ProjRewardMeta::createRewardMeta($task, [
            'project' => $project
        ]);
        //send email to pm to noti
        self::sendEmailReward($project, $pmActive, $option);
        return $task;
    }

    /**
     * create reward for long project
     *
     * @param model $project
     * @return \self
     */
    public static function createRewardLong($project, array $option = [])
    {
        if ($project->type != Project::TYPE_BASE ||
            self::isProjectUseToApproved($project) != 1
        ) {
            return false;
        }
        $groupLeader = $project->groupLeader;
        $pmActive = $project->getPmActive();
        $idAssign = [];
        $emailCoo = CoreConfigData::getValueDb('project.account_approver_reward');
        $task = self::findReward($project);
        if ($task) {
            return $task;
        }
        $monthRewards = $project->getMonthReward();
        for ( $i = 0; $i < count($monthRewards); $i++) {
            $task = new Task();
            $task->setData([
                'project_id' => $project->id,
                'type' => self::TYPE_REWARD,
                'title' => 'Project Reward',
                'status' => self::STATUS_NEW,
                'priority' => self::PRIORITY_LOW,
                'content' => '',
            ]);
            $task->save();
            //add member to reward
            ProjRewardEmployee::createRewardEmployee($task);

            // add assign
            if ($pmActive) {
                $idAssign[] = [
                    'employee_id' => $pmActive->id,
                    'role' => TaskAssign::ROLE_PM,
                    'status' => TaskAssign::STATUS_NO,
                    'task_id' => $task->id
                ];
            }
            if ($groupLeader) {
                $idAssign[] = [
                    'employee_id' => $groupLeader->id,
                    'role' => TaskAssign::ROLE_REVIEWER,
                    'status' => TaskAssign::STATUS_NO,
                    'task_id' => $task->id
                ];
            }

            $idCoo = Employee::getIdEmpByEmail($emailCoo);
            if ($idCoo) {
                $idAssign[] = [
                    'employee_id' => $idCoo,
                    'role' => TaskAssign::ROLE_APPROVER,
                    'status' => TaskAssign::STATUS_NO,
                    'task_id' => $task->id
                ];
            }
            // add meta to reward
            ProjRewardMeta::createRewardMeta($task, [
                'project' => $project
            ], $monthRewards[$i]);
        }
        TaskAssign::insert($idAssign);
        //send email to pm to noti
        self::sendEmailReward($project, $pmActive, $option);
        return $task;
    }

    public static function sendEmailReward($project, $pmActive, $option)
    {
        if ($pmActive &&
            (!isset($option['send_email']) || $option['send_email']) &&
            $project->isClosed()
        ) {
            $subject = Lang::get('project::email.[Project reward] Project '
                . 'Reward of :project', [
                'project' => $project->name
            ]);
            $emailQueue = new EmailQueue();
            $rewardLink = URL::route('project::reward', ['id' => $project->id]);
            $emailQueue->setTo($pmActive->email, $pmActive->name)
                ->setSubject($subject)
                ->setTemplate('project::emails.reward_create', [
                    'dear_name' => $pmActive->name,
                    'project_name' => $project->name,
                    'reward_link' => $rewardLink
                ])
                ->setNotify($pmActive->id, null, $rewardLink, ['category_id' => RkNotify::CATEGORY_PROJECT])
                ->save();
        }
    }

    /**
     * get url of task
     *
     * @return string
     */
    public function getUrl()
    {
        switch ($this->type) {
            case self::TYPE_REWARD:
                return URL::route('project::reward', ['id' => $this->project_id ]);
            case self::TYPE_COMPLIANCE:
                return (URL::route('project::point.edit', ['id' => $this->project_id]) . '#process');
        }
        return URL::route('project::task.edit', ['id' => $this->id ]);
    }

    /**
     * check user is created by or assign of task
     *
     * @return boolean
     */
    public function isAssignOrCreatedBy($allowParticipant = true)
    {
        $userId = Permission::getInstance()->getEmployee()->id;
        // check created by
        if ($this->created_by == $userId) {
            return true;
        }
        // check assignee
        $isAssignee = TaskAssign::select(DB::raw('count(*) as count'))
            ->where('task_id', $this->id)
            ->where('employee_id', $userId);
        if (!$allowParticipant) {
            $isAssignee->where('role', '<>', TaskAssign::ROLE_PARTICIPANT);
        }
        $isAssignee = $isAssignee->first();
        if ($isAssignee && $isAssignee->count) {
            return true;
        }
        return false;
    }

    /**
     * allow create reward of project
     *
     * @param model $project
     * @return boolean
     */
    public static function createRewardAvailable($project)
    {
        $rewardDisableProjectIds = (array)CoreConfigData::get(
            'project.reward_disable.ids');
        if (in_array($project->id, $rewardDisableProjectIds)) {
            return false;
        }
        $rewardDisableTeam = CoreConfigData::get('project.reward_disable.team_code');
        $rewardDisableStart = CoreConfigData::get('project.reward_disable.project_start_at');
        if (!$rewardDisableTeam || !$rewardDisableStart) {
            return true;
        }
        if (!$project->start_at) {
            return true;
        }
        if (!$project->start_at instanceof Carbon) {
            $startDateProject = Carbon::parse($project->start_at);
        } else {
            $startDateProject = $project->start_at;
        }
        $rewardDisableStart = Carbon::parse($rewardDisableStart);
        // if project kick of after flag date => avai
        if ($startDateProject >= $rewardDisableStart) {
            return true;
        }
        $tableTeamProject = TeamProject::getTableName();
        $tableTeam = Team::getTableName();

        $projectTeamDisable = TeamProject::select(DB::raw('count(*) as count'))
            ->join($tableTeam, $tableTeam.'.id', '=', $tableTeamProject.'.team_id')
            ->where($tableTeam.'.code', $rewardDisableTeam)
            ->where($tableTeamProject.'.project_id', $project->id)
            ->first();
        // if code of team project not eq flag team => avai
        if (!$projectTeamDisable->count) {
            return true;
        }
        return false;
    }

    /**
     * rewrite delete model
     *
     * @param array $config
     * @return type
     * @throws Exception
     */
    public function delete(array $config = []) {
        try {
            $result = parent::delete();
            if (isset($config['project']) && $config['project']) {
                $project = $config['project'];
            } else {
                $project = $this->getProject();
            }
            CacheHelper::forget(Project::KEY_CACHE_TASK, $this->project_id);
            switch ($this->type) {
                case self::TYPE_QUALITY_PLAN:
                    CacheHelper::forget(self::KEY_CACHE_ALL, $project->id);
                    break;
                case self::TYPE_COMPLIANCE:
                    CacheHelper::forget(self::KEY_CACHE_COMPLIANCE, $this->project_id);
                    $project->refreshFlatDataPoint();
                    break;
            }
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function isNotComfirmContract($projectId)
    {
        $task = self::where('project_id', $projectId)
            ->where('type', Task::TYPE_CONTRACT_CONFIRM)
            ->where('status', Task::STATUS_NEW)
            ->get();
        if (count($task)) {
            return true;
        }
        return false;
    }

    /**
     * check project is use to approved
     *
     * @param type $project
     * @return boolean
     */
    public static function isProjectUseToApproved($project)
    {
        return self::where('project_id', $project->id)
            ->where('type', self::TYPE_WO)
            ->where('status', self::STATUS_APPROVED)
            ->count();
    }

    public static function getTasksInMonth($month, $year, $project_id)
    {
        $teamProjTable = TeamProject::getTableName();
        $tasksTable = self::getTableName();
        return self::whereRaw("MONTH({$tasksTable}.updated_at) = {$month}")
                ->whereRaw("YEAR({$tasksTable}.updated_at) = {$year}")
                ->where('content', '<>', '')
                ->whereIn('type', [self::TYPE_COMMENDED, self::TYPE_CRITICIZED])
                //->join("{$teamProjTable}", "{$teamProjTable}.project_id", "=", "{$tasksTable}.project_id")
                ->where("{$tasksTable}.project_id", $project_id)
                ->select('content')
                ->get();
    }

    /*
     * get ncm request
    */
    public function ncmRequest()
    {
        return $this->hasOne('\Rikkei\Project\Model\TaskNcmRequest', 'task_id', 'id');
    }
    public static function getTasksComment($options = [])
    {
        if (!Permission::getInstance()->isAllow(null, 'project::dashboard')) {
            return null;
        }

        $tableTask = self::getTableName();
        $tableProject = Project::getTableName();
        $tableSaleProject = SaleProject::getTableName();
        $tableTeamProject = TeamProject::getTableName();
        $curEmp = Permission::getInstance()->getEmployee();
        $tableTeamMember = \Rikkei\Team\Model\TeamMember::getTableName();
        $tableEmp = Employee::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();
        $tasks = self::join('task_assigns', 'task_assigns.task_id', '=', 'tasks.id')
            ->join('employees', 'employees.id', '=', 'task_assigns.employee_id')
            ->join("$tableProject", "{$tableProject}.id", "=", "{$tableTask}.project_id")
            ->whereNull('tasks.parent_id')
            ->whereIn('tasks.type', [self::TYPE_COMMENDED, self::TYPE_CRITICIZED])
            ->select('tasks.*', 'employees.email', "{$tableProject}.name as project_name");
        $tasks->addSelect(DB::raw("(SELECT COUNT(id) FROM tasks as tasks_child WHERE parent_id = tasks.id) AS count_issues"));

        //Filter
        if (!empty($options['status'])) {
            $tasks->where("{$tableTask}.status", $options['status']);
        }
        if (!empty($options['title'])) {
            $tasks->where("{$tableTask}.title", 'Like', '%'.$options['title'].'%');
        }
        if (!empty($options['priority'])) {
            $tasks->where("{$tableTask}.priority", $options['priority']);
        }
        if (!empty($options['type'])) {
            $tasks->where("{$tableTask}.type", $options['type']);
        }
        if (!empty($options['created_at'])) {
            $tasks->where("{$tableTask}.created_at", $options['created_at']);
        }
        if (!empty($options['duedate'])) {
            $tasks->where("{$tableTask}.duedate", $options['duedate']);
        }
        if (!empty($options['assignee'])) {
            $tasks->where("employees.email", 'Like', '%'.$options['assignee'].'%');
        }
        if (!empty($options['project_name'])) {
            $tasks->where("{$tableProject}.name", 'Like', '%'.$options['project_name'].'%');
        }

        /* Check Permission
         * Company: view all
         * Team: view only project's tasks of team in projects
         * Self: view only project's tasks of saler
         */
        if (Permission::getInstance()->isScopeCompany(null, 'project::dashboard')) {

        } elseif (Permission::getInstance()->isScopeTeam(null, 'project::dashboard')) {
            $teamsEmployee = Permission::getInstance()->getTeams();
            $tasks->join("$tableTeamProject", "{$tableProject}.id", "=", "{$tableTeamProject}.project_id")
                ->join("$tableTeamMember", "{$tableTeamProject}.team_id", "=", "{$tableTeamMember}.team_id")
                ->where("{$tableTeamMember}.employee_id", $curEmp->id);
        } else {
            $tblCustomer = \Rikkei\Sales\Model\Customer::getTableName();
            $tblCompany = \Rikkei\Sales\Model\Company::getTableName();
            $tasks->join("$tableSaleProject", "{$tableProject}.id", "=", "{$tableSaleProject}.project_id")
                ->join("$tblCustomer", "{$tableProject}.cust_contact_id", "=", "{$tblCustomer}.id")
                ->join("$tblCompany", "{$tblCustomer}.company_id", "=", "{$tblCompany}.id")
                ->where(function ($query) use ($tableSaleProject, $tblCustomer, $tblCompany, $tableTask, $curEmp, $tableTaskAssigns) {
                    $query->where("{$tableSaleProject}.employee_id", $curEmp->id)
                          ->orWhere("{$tblCompany}.manager_id", $curEmp->id)
                          ->orWhere("{$tblCompany}.sale_support_id", $curEmp->id)
                          ->orWhere($tableTask.'.created_by', $curEmp->id)
                          ->orWhere($tableTaskAssigns.'.employee_id', $curEmp->id);
                });
        }

        $tasks->groupBy("{$tableTask}.id");
        return $tasks->get();
    }

    /**
     * Check project that all the task are closed
     * @param $projectId
     * @return boolean
     */
    public static function isProjectClose($projectId)
    {
        $tableTask = self::getTableName();
        $tblTaskAction = TaskAction::getTableName();
        $tableProject = Project::getTableName();
        $typeCheck = self::getTypeTaskCheckCloseProject();

        //Check is issue or task
        $isIssueOrTask = self::isIssueOrTaskByProjsId($projectId);
        if ($isIssueOrTask == 'tasks') {
            $item = self::join($tableProject, "{$tableTask}.project_id", '=', "{$tableProject}.id")
                    ->whereNull('tasks.deleted_at')
                    ->where("{$tableTask}.project_id", '=', $projectId)
                    ->where(function ($query) use ($tableTask, $typeCheck) {
                        $query->where(function ($qR1) use ($tableTask, $typeCheck) {
                            $qR1->whereIn("{$tableTask}.type", $typeCheck)
                                ->whereNotIn("{$tableTask}.status", [self::STATUS_CLOSED, self::STATUS_REJECT, self::STATUS_RESOLVE]);
                        });
                    })
                    ->count();
        } else {
            $item = TaskAction::join($tableTask, "{$tblTaskAction}.issue_id", '=', "{$tableTask}.id")
                    ->join($tableProject, "{$tableTask}.project_id", '=', "{$tableProject}.id")
                    ->where("{$tableTask}.project_id", '=', $projectId)
                    ->where("{$tblTaskAction}.type", TaskAction::TYPE_ISSUE_MITIGATION)
                    ->whereNotIn("{$tblTaskAction}.status", [self::STATUS_CLOSED, self::STATUS_REJECT, self::STATUS_RESOLVE])
                    ->count();
        }

        return !($item > 0);
    }

    public static function isIssueOrTaskByProjsId($projectId)
    {
        $tblTask = self::getTableName();
        $tblTaskAction = TaskAction::getTableName();
        $tblProjs = Project::getTableName();

        $taskAcs = Task::join($tblProjs, "{$tblProjs}.id", '=', "{$tblTask}.project_id")
            ->join($tblTaskAction, "{$tblTaskAction}.issue_id", '=', "{$tblTask}.id")
            ->where("{$tblTask}.project_id", '=', $projectId)
            ->where("{$tblTaskAction}.type", TaskAction::TYPE_ISSUE_MITIGATION)
            ->count();
        $typeCheck = 'tasks';
        if ($taskAcs) {
            $typeCheck = 'task_actions';
        }
        return $typeCheck;
    }

    public static function getIssueHasTaskActinoUnCloseByProjsId($projsId)
    {
        $tblTask = self::getTableName();
        $tblTaskAction = TaskAction::getTableName();
        $tableProject = Project::getTableName();

        return self::select("{$tblTask}.*")
            ->join($tableProject, "{$tblTask}.project_id", '=', "{$tableProject}.id")
            ->join($tblTaskAction, "{$tblTaskAction}.issue_id", '=', "{$tblTask}.id")
            ->where("{$tblTask}.project_id", '=', $projsId)
            ->where("{$tblTaskAction}.type", TaskAction::TYPE_ISSUE_MITIGATION)
            ->whereNotIn("{$tblTaskAction}.status", [Task::STATUS_CLOSED, Task::STATUS_REJECT, Task::STATUS_RESOLVE])
            ->groupBy("{$tblTask}.id")
            ->get();
    }

    /**
     * Check permission change status tasks.
     *
     * @param $projectId
     * @param $taskId
     * @return boolean
     */
    public static function hasEditStatusTasks($task, $project)
    {
        $curEmp = Permission::getInstance()->getEmployee();
        $accessEditTask = ViewProject::isAccessEditTask($project, $task->type, $task);
        if (($project->isOpen() && $accessEditTask) || ($curEmp->id == $task->created_by)) {
            return true;
        } else {
            return Permission::getInstance()->isScopeCompany(null, 'project::task.save');
        }

    }

    /**
     * get type of task check close project.
     *
     * @return array.
     */
    public static function getTypeTaskCheckCloseProject()
    {
        return [
            self::TYPE_COMMENDED,
            self::TYPE_CRITICIZED,
            self::TYPE_QUALITY_PLAN,
            self::TYPE_COMPLIANCE,
            self::TYPE_ISSUE_COST,
            self::TYPE_ISSUE_QUA,
            self::TYPE_ISSUE_TL,
            self::TYPE_ISSUE_PROC,
            self::TYPE_ISSUE_CSS,
            self::TYPE_GENERAL  ,
            self::TYPE_RISK,
        ];
    }

    public static function getTypeIssueProject()
    {
        return [
            self::TYPE_CRITICIZED,
            self::TYPE_ISSUE_QUA,
            self::TYPE_ISSUE_TL,
            self::TYPE_ISSUE_PROC,
            self::TYPE_ISSUE_COST
        ];
    }

    /**
     * get status of task close or reject.
     *
     * @return array.
     */
    public static function getStatusCloseOrReject()
    {
        return [
            self::STATUS_CLOSED,
            self::STATUS_REJECT,
        ];
    }

    public static function checkTypeOfIssue($type)
    {
        return in_array($type, self::getTypeIssueProject());
    }


    /*
     * get url of task follow type.
     *
     * @param collection $task
     * @return url.
     */
    public static function getUrlTaskFollowType($task)
    {
        switch ($task->type) {
            case self::TYPE_COMPLIANCE:
                $urlTask = route('project::point.edit', ['id' => $task->project_id]) . '#process';
                break;
                case self::TYPE_CONTRACT_CONFIRM:
                $urlTask = route('project::project.edit', ['id' => $task->project_id]) . "#scope";
                break;
            default:
                $urlTask = route('project::task.edit', ['id' => $task->id]);
                break;
        }
        return $urlTask;
    }

    public static function deleteIssue($request)
    {
        if (isset($request->issueId)) {
            $issue = self::where('id', $request->issueId)->first();
            $issue->deleted_at = Carbon::now()->format('Y-m-d');
            $issue->save();
            return $issue->id;
        }
        return false;
    }

    public static function notiToPersonMentioned($arrEmp, $type, $objId, $content)
    {
        $employees = Employee::getEmployeesById($arrEmp);
        $userCurrent = Permission::getInstance()->getEmployee();

        if ($type == RiskComment::TYPE_ISSUE) {
            $link = route('project::issue.detail', ['id' => $objId]);
            $subject = '【Issue】'.$userCurrent->name.' đã nhắc đến bạn trong một bình luận.';
        } else {
            $link = route('project::risk.detail', ['id' => $objId]);
            $subject = '【Risk】'.$userCurrent->name.' đã nhắc đến bạn trong một bình luận.';
        }
        
        if (!empty($employees)) {
            foreach ($employees as $emp) {
                $dataEmail = [
                    'name' => $emp->name,
                    'cmt_content' => $content,
                    'link' => $link
                ];
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($emp->email, $emp->name)
                    ->setSubject($subject)
                    ->setTemplate("project::emails.mention_noti", $dataEmail)
                    ->setNotify($emp->id, $subject, $link)
                    ->save();
            }
        }        
    }

    public static function priorityLabelV2()
    {
        return [
            self::PRIORITY_LOW => Lang::get('project::view.Level Low'),
            self::PRIORITY_NORMAL => Lang::get('project::view.Level Normal'),
            self::PRIORITY_HIGH => Lang::get('project::view.Level High'),
        ];
    }

    public function getCostOpportunity()
    {
        $cost = $this->cost;
        $priorityLabel = self::priorityLabelV2();
        return isset($priorityLabel[$cost]) ? $priorityLabel[$cost] : '';
    }

    public function getBenefitOpportunity()
    {
        $expected_benefit = $this->expected_benefit;
        $priorityLabel = self::priorityLabelV2();
        return isset($priorityLabel[$expected_benefit]) ? $priorityLabel[$expected_benefit] : '';
    }
    
    public static function statusOpportunityLabel()
    {
        return [
            self::STATUS_NEW => 'Open',
            self::STATUS_PROCESS => 'In-Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_REOPEN => 'Reopened',
            self::STATUS_REJECT => 'Cancelled',
        ];
    }

    public function getStatusOpportunity()
    {
        $status = $this->status;
        $statusLabel = self::statusOpportunityLabel();
        return isset($statusLabel[$status]) ? $statusLabel[$status] : '';
    }

    public static function statusActionOpportunityLabel()
    {
        return [
            self::STATUS_NEW => 'Open',
            self::STATUS_PROCESS => 'In-Progress',
            self::STATUS_REOPEN => 'Reopened',
            self::STATUS_RESOLVE2 => 'Resolved',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_REJECT => 'Cancelled',
        ];
    }

    public function getStatusActionOpportunity()
    {
        $status = $this->action_status;
        $statusLabel = self::statusActionOpportunityLabel();
        return isset($statusLabel[$status]) ? $statusLabel[$status] : '';
    }
    
    public static function getAllOpportunitySource()
    {
        return [
            1 => 'Opportunity Category',
            2 => 'Business Processes Complexity',
            3 => 'Critical Dependencies',
            4 => 'Data Migration Required',
            5 => 'Design Difficulty',
            6 => 'Existing System Documentation',
            7 => 'External driven decisions forced on the project',
            8 => 'Hardware Constraints',
            9 => 'Implementation Difficulty',
            10 => 'Inexperience Customer/End User',
            11 => 'Inexperience with Project Process',
            12 => 'Inexperience with Project Technology',
            13 => 'Insufficient PM Experience',
            14 => 'Insufficient business knowledge',
            15 => 'Integration Complexity',
            16 => 'Lack of Customer Support',
            17 => 'Link failure or slow performance',
            18 => 'New Technology',
            19 => 'Not meeting performance requirements',
            20 => 'Parallel development',
            21 => 'Physical Facilities',
            22 => 'Project Complexity',
            23 => 'Opportunity Dependencies',
            24 => 'Shortage of Human resource',
            25 => 'Team Spirit and Attitude',
            26 => 'Test Ability',
            27 => 'Too many requirement changes',
            28 => 'Tools Availability',
            29 => 'Unclear Acceptance Criteria',
            30 => 'Unclear Acceptance criteria and process',
            31 => 'Unclear Decision Making Process',
            32 => 'Unclear requirements',
            33 => 'Unrealistic schedules',
            34 => 'Unstable Customer Organization',
            35 => 'Vendor/External Support',
            36 => 'Weak Customer Involvement',
        ];
    }

    public function getOpportunitySource()
    {
        $opportunity_source = $this->opportunity_source;
        $oppSources = self::getAllOpportunitySource();
        return isset($oppSources[$opportunity_source]) ? $oppSources[$opportunity_source] : '';
    }
    
    public static function getOpportunity($projectId = null)
    {
        $type = Task::TYPE_OPPORTUNITY;
        $pager = Config::getPagerData();
        $tableTask = Task::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableProject = Project::getTableName();
        $tblTaskAssigns = TaskAssign::getTableName();
        
        $collection = Task::select($tableTask.'.id', $tableTask.'.opportunity_source',
                $tableTask.'.content',
                $tableTask.'.duedate', $tableTask.'.actual_date',
                $tableTask.'.cost', $tableTask.'.expected_benefit', $tableTask.'.priority',
                $tableTask.'.status', $tableTask.'.action_plan', $tableTask.'.action_status',
                $tableTask.'.created_at',
                $tableTask.'.updated_at',
                $tableProject.'.name',                
                "tblEmpAssign.id as assign_empId",
                DB::raw("SUBSTRING_INDEX(tblEmpAssign.email, ". "'@', 1) as assign_email")
            )
            ->leftJoin($tableProject, $tableProject.'.id', '=', $tableTask.'.project_id')
            ->leftJoin("$tblTaskAssigns as tblAssign", function ($join) use ($tableTask) {
                $join->on('tblAssign.task_id', '=', "{$tableTask}.id")
                ->where('tblAssign.role', '=', TaskAssign::ROLE_ASSIGNEE);
            })
            ->leftJoin("{$tableEmployee} as tblEmpAssign", "tblEmpAssign.id", '=', "tblAssign.employee_id")
            ->groupBy($tableTask.'.id')
            ->where($tableTask.'.type', $type);
        if ($projectId) {
            $collection->where('project_id', $projectId);
        }
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy($tableTask.'.created_at', 'desc');
        }
        self::filterGridOpp($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    public static function filterGridOpp(&$collection, $urlSubmitFilter = null, $compare = 'REGEXP')
    {
        if (is_array($urlSubmitFilter)) {
            $filter = $urlSubmitFilter;
        } else {
            $filter = Form::getFilterData(null, null, $urlSubmitFilter);
        }
        // dd($filter);
        if ($filter && count($filter)) {
            foreach ($filter as $key => $value) {
                $keys = explode("-", $key);
                if (empty($keys[0]) || $keys[0] != "oop") {
                    continue;
                }
                if (is_array($value)) {
                    if (!empty($keys[1])) {
                        if ($keys[1] == 'number' && $value) {
                            foreach ($value as $col => $filterValue) {
                                if ($filterValue === '') {
                                    continue;
                                }
                                if ($filterValue == 'NULL') {
                                    $collection = $collection->whereNull($col);
                                } else {
                                    $collection = $collection->where($col, $filterValue);
                                }
                            }
                        } elseif ($keys[1] == 'in' && $value) {
                            foreach ($value as $col => $filterValue) {
                                $collection = $collection->whereIn($col, $filterValue);
                            }
                        } elseif ($keys[1] == 'date' && $value) {
                            foreach ($value as $col => $filterValue) {
                                if ($filterValue == 'NULL') {
                                    $collection = $collection->whereNull($col);
                                } elseif (preg_match('/^[0-9\-\:\s]+$/', $filterValue)) {
                                    $collection = $collection->where($col, $filterValue);
                                }
                            }
                        } else {
                            if (isset($value['from']) && $value['from']) {
                                $collection = $collection->where($keys[1], '>=', $value['from']);
                            }
                            if (isset($value['to']) && $value['to']) {
                                $collection = $collection->where($keys[1], '<=', $value['to']);
                            }
                        }
                    } else {
                        foreach ($value as $col => $filterValue) {
                            $value = trim($filterValue);
                            if ($value == '') {
                                continue;
                            }
                            switch ($compare) {
                                case 'LIKE':
                                    $collection = $collection->where($col, $compare, addslashes("%$filterValue%"));
                                    break;
                                default:
                                    $collection = $collection->where($col, $compare, addslashes("$filterValue"));
                            }
                        }
                    }
                }
            }
        }
        return $collection;
    }

    public static function fillPriority($cost, $benefit) {
        $typeLow = self::PRIORITY_LOW;
        $typeMedium = self::PRIORITY_NORMAL;
        $typeHigh = self::PRIORITY_HIGH;
        if ($cost == $typeHigh) {
            switch($benefit) {
                case $typeLow:
                    return $typeLow;
                    break;
                case $typeMedium:
                    return $typeMedium;
                    break;
                case $typeHigh:
                    return $typeMedium;
                    break;
                default:
                    return '';
            }
        }
        if ($cost == $typeMedium) {
            switch($benefit) {
                case $typeLow:
                    return $typeLow;
                    break;
                case $typeMedium:
                    return $typeMedium;
                    break;
                case $typeHigh:
                    return $typeHigh;
                    break;
                default:
                    return '';
            }
        }
        if ($cost == $typeLow) {
            switch($benefit) {
                case $typeLow:
                    return $typeMedium;
                    break;
                case $typeMedium:
                    return $typeHigh;
                    break;
                case $typeHigh:
                    return $typeHigh;
                    break;
                default:
                    return '';
            }
        }
    }
}
