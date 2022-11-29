<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Lang;
use Rikkei\Project\Model\ProjectChangeWorkOrder;
use Rikkei\Project\Model\Task;
use Rikkei\Project\View\View;
use Illuminate\Support\Facades\DB;

class ProjectWOBase extends CoreModel
{
    const STATUS_APPROVED = 1;
    const STATUS_DRAFT = 2; // add new item
    const STATUS_SUBMITTED = 3; // submited new item of STATUS_DRAFT
    const STATUS_REVIEWED = 4; // reivewed new item of STATUS_SUBMITTED
    const STATUS_FEEDBACK = 5; // feedback new item of STATUS_REVIEWED, STATUS_SUBMITTED
    const STATUS_DRAFT_EDIT = 6; //edit item approved of STATUS_APPROVED
    const STATUS_DRAFT_DELETE = 7;  //delete item approved of STATUS_APPROVED
    const STATUS_SUBMIITED_EDIT = 8; //submit item approved STATUS_APPROVED
    const STATUS_SUBMMITED_DELETE = 9; // submit item approved STATUS_APPROVED
    const STATUS_REVIEWED_EDIT = 10; // review of item approved - STATUS_SUBMIITED_EDIT
    const STATUS_REVIEWED_DELETE = 11; // review of item approve - STATUS_SUBMMITED_DELETE
    const STATUS_FEEDBACK_EDIT = 12; // feedback of item approved
    const STATUS_FEEDBACK_DELETE = 13; // feedback of item approved
    const STATUS_OLD = 20; // feedback of item approved
    const STATUS_OPPORTUNITY = 30; //opportunity

    const STATUS_DISABLED = 1;// status disaled

    const KEY_CACHE_WO = 'pp_wo';

    const CLASS_COLOR_STATUS_APPROVED = 'approved';
    const CLASS_COLOR_STATUS_DRAFT = 'draft';
    const CLASS_COLOR_STATUS_SUBMITTED = 'submitted';
    const CLASS_COLOR_STATUS_REVIEWED = 'reviewed';
    const CLASS_COLOR_STATUS_FEEDBACK = 'feedback';
    const CLASS_COLOR_STATUS_DRAFT_EDIT = 'draft-edit';
    const CLASS_COLOR_STATUS_DRAFT_DELETE = 'draft-delete';
    const CLASS_COLOR_STATUS_SUBMIITED_EDIT = 'submitted-edit';
    const CLASS_COLOR_STATUS_SUBMMITED_DELETE = 'submitted-delete';
    const CLASS_COLOR_STATUS_REVIEWED_EDIT = 'reviewed-edit';
    const CLASS_COLOR_STATUS_REVIEWED_DELETE = 'reviewed-delete';
    const CLASS_COLOR_STATUS_FEEDBACK_EDIT = 'feedback-edit';
    const CLASS_COLOR_STATUS_FEEDBACK_DELETE = 'feedback-delete';
    const CLASS_COLOR_STATUS_DELETE_APPROVED = 'delete-approved';

    const TYPE_ADD = 1;
    const TYPE_EDIT = 2;
    const TYPE_DELETE = 3;

    /**
     * status for project log
     */
    const STATUS_ADD = 14;
    const STATUS_EDIT = 15;
    const STATUS_DELETE = 16;
    const STATUS_EDIT_APPROVED = 17;
    const STATUS_DELETE_APPROVED = 18;
    const STATUS_DELETE_DRAFT_EDIT = 19;
    const STATUS_DELETE_DRAFT = 20;
    const STATUS_DELETE_FEEDBACK_EDIT = 21;
    const STATUS_DELETE_FEEDBACK = 22;
    const STATUS_UPDATED_DRAFT = 23;
    const STATUS_CREATE_QUALITY_PLAN = 24;

    const STATUS_RESULT_PASS = 1;
    const STATUS_RESULT_FAIL = 2;

    /**
     * number element workorder have not added
     */
    const NUMBER_ELEMENT_WORKORDER_HAVE_NOT_ADDED = 5;

    /**
     * type element in workorder
     */
    /**
     * get label of type task
     *
     * @return array
     */
    public static function statusLabel()
    {
        return [
            self::STATUS_APPROVED => Lang::get('project::view.Status approved'),
            self::STATUS_DRAFT => Lang::get('project::view.Status draft'),
            self::STATUS_SUBMITTED => Lang::get('project::view.Status submitted'),
            self::STATUS_REVIEWED => Lang::get('project::view.Status reviewed'),
            self::STATUS_FEEDBACK => Lang::get('project::view.Status feedback'),
            self::STATUS_DRAFT_EDIT => Lang::get('project::view.Status draft edit'),
            self::STATUS_DRAFT_DELETE => Lang::get('project::view.Status draft delete'),
            self::STATUS_SUBMIITED_EDIT => Lang::get('project::view.Status submitted edit'),
            self::STATUS_SUBMMITED_DELETE => Lang::get('project::view.Status submitted delete'),
            self::STATUS_REVIEWED_EDIT => Lang::get('project::view.Status reviewed edit'),
            self::STATUS_REVIEWED_DELETE => Lang::get('project::view.Status reviewed delete'),
            self::STATUS_FEEDBACK_EDIT => Lang::get('project::view.Status feedback edit'),
            self::STATUS_FEEDBACK_DELETE => Lang::get('project::view.Status feedback delete'),
            self::STATUS_DELETE_APPROVED => Lang::get('project::view.Status delete approved'),
        ];
    }

    /**
     * get array status delete
     * @return array
     */
    public static function getArrayStatusDelete()
    {
        return [
            self::STATUS_DRAFT_DELETE,
            self::STATUS_SUBMMITED_DELETE,
            self::STATUS_REVIEWED_DELETE,
            self::STATUS_FEEDBACK_DELETE,
        ];
    }
    /**
     * process submit workorder
     *
     * @param array $input
     * @param object $project
     * @return boolean
     */
    public static function submitWorkorder($input, $project = null)
    {
        DB::beginTransaction();
        try {
            $task = Task::insertOrUpdateTaskWo($input, $project);
            if ($task) {
                Project::updateStatusWhenSubmitWorkorder($task, $input);
                CriticalDependencie::updateStatusWhenSubmitWorkorder($task, $input);
                AssumptionConstrain::updateStatusWhenSubmitWorkorder($task, $input);
//                Risk::updateStatusWhenSubmitWorkorder($task, $input);
                ExternalInterface::updateStatusWhenSubmitWorkorder($task, $input);
                Communication::updateStatusWhenSubmitWorkorder($task, $input);
                ToolAndInfrastructure::updateStatusWhenSubmitWorkorder($task, $input);

                StageAndMilestone::updateStatusWhenSubmitWorkorder($task, $input);
                ProjDeliverable::updateStatusWhenSubmitWorkorder($task, $input);
                ProjectMember::updateStatusWhenSubmitWorkorder($task, $input);
                Training::updateStatusWhenSubmitWorkorder($task, $input);
                ProjQuality::updateStatusWhenSubmitWorkorder($task, $input);
            }
            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * slove workorder
     * @param int
     * @param int
     */
    public static function sloveWorkorder($statusTask, $projectId)
    {
        DB::beginTransaction();
        try {
            Project::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            CriticalDependencie::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            ProjDeliverable::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            AssumptionConstrain::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            Risk::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            StageAndMilestone::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            Training::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            ExternalInterface::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            Communication::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            ToolAndInfrastructure::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            Performance::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            ProjQuality::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            ProjectMember::updateStatusWhenSloveWorkorder($statusTask, $projectId);
            View::insertProjectLogChangeWO($statusTask, $projectId);
            if ($statusTask == Task::STATUS_APPROVED) {
                ProjectChangeWorkOrder::insertVersionWorkorder($projectId);
            }
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();
        }
    }

    /**
     * get label status for project log
     * @return array
     */
    public static function getLabelStatusForProjectLog()
    {
        return [
            self::STATUS_DRAFT => Lang::get('project::view.Added object draft'),
            self::STATUS_EDIT_APPROVED => Lang::get('project::view.Added object draft for edit approved'),
            self::STATUS_FEEDBACK_EDIT => Lang::get('project::view.Updated object feedback edit'),
            self::STATUS_FEEDBACK => Lang::get('project::view.Updated object feedback'),
            self::STATUS_ADD => Lang::get('project::view.Added object'),
            self::STATUS_EDIT => Lang::get('project::view.Updated object'),
            self::STATUS_DELETE => Lang::get('project::view.Deleted object'),
            self::STATUS_DELETE_APPROVED => Lang::get('project::view.Delete object approved'),
            self::STATUS_FEEDBACK_DELETE => Lang::get('project::view.Removed object feedback delete'),
            self::STATUS_DELETE_DRAFT_EDIT => Lang::get('project::view.Removed object draft edit'),
            self::STATUS_DELETE_DRAFT => Lang::get('project::view.Removed object draft'),
            self::STATUS_DELETE_FEEDBACK_EDIT => Lang::get('project::view.Removed object feedback edit'),
            self::STATUS_DELETE_FEEDBACK => Lang::get('project::view.Removed object feedback'),
            self::STATUS_DRAFT_EDIT => Lang::get('project::view.Updated object draft edit'),
            self::STATUS_DRAFT_DELETE => Lang::get('project::view.Removed object draft delete'),
            self::STATUS_UPDATED_DRAFT => Lang::get('project::view.Updated object draft'),
            self::STATUS_CREATE_QUALITY_PLAN => Lang::get('project::view.Created quality plan'),
        ];
    }

    /**
     * get label element for workorder
     * @return array
     */
    public static function getLabelElementForWorkorder()
    {
        return [
            Task::TYPE_WO_CRITICAL_DEPENDENCIES => Lang::get('project::view.for critical dependencies'),
            Task::TYPE_WO_ASSUMPTION_CONSTRAINS => Lang::get('project::view.for assumption constrains'),
            Task::TYPE_WO_RISK => Lang::get('project::view.for risk'),
            Task::TYPE_WO_STAGE_MILESTONE => Lang::get('project::view.for stage and milestones'),
            Task::TYPE_WO_TRANING => Lang::get('project::view.for training'),
            Task::TYPE_WO_EXTERNAL_INTERFACE => Lang::get('project::view.for external interface'),
            Task::TYPE_WO_COMMINUCATION => Lang::get('project::view.for communication'),
            Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE => Lang::get('project::view.for tool and infrastructure'),
            Task::TYPE_WO_DELIVERABLE => Lang::get('project::view.for deliverable'),
            Task::TYPE_WO_PERFORMANCE => Lang::get('project::view.for performance'),
            Task::TYPE_WO_QUALITY => Lang::get('project::view.for quality'),
            Task::TYPE_WO_PROJECT_MEMBER => Lang::get('project::view.for project member'),
            Task::TYPE_WO_QUALITY_PLAN => Lang::get('project::view.for quality plan'),
            Task::TYPE_WO_CM_PLAN => Lang::get('project::view.for cm plan'),
            Task::TYPE_WO_DEVICES_EXPENSE => Lang::get('project::view.for devices expense'),
        ];
    }

    /**
     * check submit workorder
     * @param int project id
     * @return boolean
     */
    public static function checkSubmitWorkOrder($projectId)
    {
        $arrayComponanentsWorkOrder = Task::getAllComponentsWorkorder();
        $status = true;
        $messageError = Lang::get('project::view.Please fill:');
        $countElementError = 0;
        foreach ($arrayComponanentsWorkOrder as $key => $value) {
            $checkElement = self::checkHasElementWorkOrder($projectId, $key);
            if (!$checkElement) {
                $status = false;
                $countElementError++;
                if ($countElementError == 1) {
                    $messageError .= ' ' .$value;
                } else {
                    $messageError .= ', ' .$value;
                }
            }
        }
        if ($countElementError > self::NUMBER_ELEMENT_WORKORDER_HAVE_NOT_ADDED) {
            $messageError = Lang::get('project::view.Please fill out all components of workorder');
        }
        return [
                'status' => $status,
                'message' => $messageError,
            ];
    }

    /**
     * check has element workorder
     * @param int project id
     * @param int type element
     */
    public static function checkHasElementWorkOrder($projectId, $type)
    {
        $element = new ProjectWOBase;
        switch ($type) {
            case Task::TYPE_WO_CRITICAL_DEPENDENCIES:
            $element = new CriticalDependencie;
                break;
            case Task::TYPE_WO_ASSUMPTION_CONSTRAINS:
            $element = new AssumptionConstrain;
                break;
            case Task::TYPE_WO_RISK:
            $element = new Risk;
                break;
            case Task::TYPE_WO_STAGE_MILESTONE:
            $element = new StageAndMilestone;
                break;
            case Task::TYPE_WO_TRANING:
            $element = new Training;
                break;
            case Task::TYPE_WO_EXTERNAL_INTERFACE:
            $element = new ExternalInterface;
                break;
            case Task::TYPE_WO_COMMINUCATION:
            $element = new Communication;
                break;
            case Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE:
            $element = new ToolAndInfrastructure;
                break;
            case Task::TYPE_WO_DELIVERABLE:
            $element = new ProjDeliverable;
                break;
            case Task::TYPE_WO_PERFORMANCE:
            $element = new Performance;
                break;
            case Task::TYPE_WO_QUALITY:
            $element = new ProjQuality;
                break;
            case Task::TYPE_WO_QUALITY_PLAN:
            $element =  Task::where('type', Task::TYPE_QUALITY_PLAN);
                break;
            case Task::TYPE_WO_CM_PLAN:
            $element = new CMPlan;
                break;
            case Task::TYPE_WO_PROJECT_MEMBER:
            $element = new ProjectMember;
                break;
            default:
                break;
        }
        return $element->where('project_id', $projectId)->count();
    }

    /**
     * update status to submit
     */
    public static function updateStatusSubmit($project)
    {
        $class = self::classNeedApprove();
        foreach ($class as $item) {
            call_user_func([$item ,'updateStatusSubmitItem'], $project);
        }
    }

    /**
     * update status to submit item
     */
    public static function updateStatusSubmitItem($project)
    {
        // update submit status
        self::where('project_id', $project->id)
            ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
            ->whereNotNull('task_id')
            ->update([
                'status' => self::STATUS_SUBMITTED
            ]);

        // update submit edit status
        self::where('project_id', $project->id)
            ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
            ->whereNotNull('parent_id')
            ->whereNotNull('task_id')
            ->update([
                'status' => self::STATUS_SUBMIITED_EDIT
            ]);

        //update delete status
        self::where('project_id', $project->id)
            ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
            ->whereNotNull('task_id')
            ->update([
                'status' => self::STATUS_SUBMMITED_DELETE
            ]);
    }

    /**
     * get all object need approve
     *
     * @return array
     */
    public static function classNeedApprove()
    {
        return [
            ProjDeliverable::class,
            ProjectMember::class,
            Performance::class,
            ProjQuality::class,
        ];
    }

    /**
     * get content change after submit with only check exists change
     *  object multi item change
     *
     * @param $projectId int
     * @param $type type of object change
     * @return array
     */
    public static function getChangesAfterSubmitWithExists($projectId, $type = null)
    {
        $result = [
            $type => null
        ];
        // find new item
        $item = self::where('project_id', $projectId)
            ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
            ->select(DB::raw('COUNT(*) as count'))
            ->first();
        if ($item && $item->count) {
            $result[$type][TaskWoChange::FLAG_STATUS_ADD] = 1;
        }

        // find edit item
        $item = self::where('project_id', $projectId)
            ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
            ->select(DB::raw('COUNT(*) as count'))
            ->first();
        if ($item && $item->count) {
            $result[$type][TaskWoChange::FLAG_STATUS_EDIT] = 1;
        }

        // find delete item
        $item = self::where('project_id', $projectId)
            ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
            ->select(DB::raw('COUNT(*) as count'))
            ->first();
        if ($item && $item->count) {
            $result[$type][TaskWoChange::FLAG_STATUS_DELETE] = 1;
        }
        $result[$type][TaskWoChange::FLAG_TYPE_TEXT] = TaskWoChange::FLAG_TYPE_MULTI;
        return $result;
    }

    /**
     * get content change after submit
     *  object multi item change
     *
     * @param $projectId int
     * @param $type type of object change
     * @return array
     */
    public static function getChangesAfterSubmit($projectId, $type = null)
    {
        $result = [];
        $classCall = get_called_class();
        $columnsChange = array_keys($classCall::getColumnChanges());
        // add items
        $collection = $classCall::select($columnsChange)
            ->whereIn('status',
            [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
            ->where('project_id', $projectId)
            ->get();
        if (count($collection)) {
            $result[$type][TaskWoChange::FLAG_STATUS_ADD] = $collection->toArray();
        }

        // delete item
        $collection = $classCall::select($columnsChange)
            ->whereIn('status',
            [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
            ->where('project_id', $projectId)
            ->get();
        if (count($collection)) {
            $result[$type][TaskWoChange::FLAG_STATUS_DELETE]
                = $collection->toArray();
        }

        // edit item
        $collection = $classCall::select($columnsChange)
            ->whereIn('status',
            [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
            ->where('project_id', $projectId)
            ->get();
        if (count($collection)) {
            foreach ($collection as $item) {
                $itemParent = $classCall::select($columnsChange)
                    ->whereIn('status', [self::STATUS_APPROVED])
                    ->where('project_id', $projectId)
                    ->where('id', $item->parent_id)
                    ->first();
                if ($itemParent) {
                    $result[$type][TaskWoChange::FLAG_STATUS_EDIT][] = [
                        TaskWoChange::FLAG_STATUS_EDIT_OLD => $itemParent->toArray(),
                        TaskWoChange::FLAG_STATUS_EDIT_NEW => $item->toArray(),
                    ];
                }
            }
        }
        $result[$type][TaskWoChange::FLAG_TYPE_TEXT]
            = TaskWoChange::FLAG_TYPE_MULTI;
        return $result;
    }

    /**
     * get column name to compare changes
     *
     * @return array
     */
    public static function getColumnChanges()
    {
        return [];
    }
}
