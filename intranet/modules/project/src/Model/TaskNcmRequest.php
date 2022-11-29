<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Project\Model\TaskHistory;
use Carbon\Carbon;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;

class TaskNcmRequest extends CoreModel
{
    const TEST_RESULT_PASS = 1;
    const TEST_RESULT_NOT_PASS = 2;

    const ASSIGN_DEPART_REPRESENT = 1;
    const ASSIGN_TESTER = 2;
    const ASSIGN_EVALUATOR = 3;

    public $timestamps = false;
    protected $table = 'task_ncm_requests';
    protected $primaryKey = 'task_id';
    public $incrementing = false;

    /**
     * label of test result ncm
     *
     * @return array
     */
    public static function getTestResultLabels()
    {
        return [
            self::TEST_RESULT_PASS => 'Satisfactory',
            self::TEST_RESULT_NOT_PASS => 'Unsatisfactory'
        ];
    }

    /**
     * find and create ncm follow task
     *
     * @param model $task
     * @return model
     */
    public static function findNcmFollowTask($task, array $config = [])
    {
        $item = self::where('task_id', $task->id)
            ->first();
        if (!$item) {
            $item = new self();
            $item->task_id = $task->id;
            if (isset($config['save']) && $config['save']) {
                $item->save();
            }
        }
        if ($item->requester && isset($config['findRequester']) &&
                $config['findRequester']
        ) {
            $employeeRequester = Employee::select('name', 'email')
                ->where('id', $item->requester)
                ->first();
            if ($employeeRequester) {
                $item->requester_email = $employeeRequester->email;
                $item->requester_name = $employeeRequester->name;
            } else {
                $item->requester = null;
            }
        }
        return $item;
    }

    /**
     * insert and update ncm assigner
     *
     * @param model $task
     * @param array $assignerIds
     * @throws Exception
     */
    public static function insertNcmAssigners($task, array $assignerIds = [])
    {
        $dataAssigns = [];
        if (isset($assignerIds['depart_represent'])) {
            $dataAssigns[] = [
                'task_id' => $task->id,
                'employee_id' => $assignerIds['depart_represent'],
                'role' => self::ASSIGN_DEPART_REPRESENT
            ];
        }
        if (isset($assignerIds['tester'])) {
            $dataAssigns[] = [
                'task_id' => $task->id,
                'employee_id' => $assignerIds['tester'],
                'role' => self::ASSIGN_TESTER
            ];
        }
        if (isset($assignerIds['evaluater'])) {
            $dataAssigns[] = [
                'task_id' => $task->id,
                'employee_id' => $assignerIds['evaluater'],
                'role' => self::ASSIGN_EVALUATOR
            ];
        }

        DB::beginTransaction();
        try {
            foreach ($dataAssigns as $key => $item) {
                $assignExists = TaskAssign::where('task_id', $task->id)
                    ->where('employee_id', $item['employee_id'])
                    ->where('role', $item['role'])
                    ->first();
                if ($assignExists) { // not action if exists
                    unset($dataAssigns[$key]);
                } else { // delete old, to insert new
                    TaskAssign::where('task_id', $task->id)
                        ->where('role', $item['role'])
                        ->delete();
                }
            }
            if ($dataAssigns) {
                TaskAssign::insert($dataAssigns);
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * find ncm assigner
     *
     * @param model $task
     * @return \Rikkei\Project\Model\TaskAssign
     */
    public static function findNcmAssign($task)
    {
        $tableTaskAssign = TaskAssign::getTableName();
        $tableEmployee = Employee::getTableName();

        $collection = Employee::select($tableTaskAssign.'.role',
            $tableTaskAssign.'.employee_id', $tableEmployee.'.email',
            $tableEmployee.'.name')
            ->join($tableTaskAssign, $tableTaskAssign.'.employee_id', '=',
                    $tableEmployee.'.id')
            ->where($tableTaskAssign.'.task_id', $task->id)
            ->get();
        if (!$collection) {
            return new TaskAssign();
        }
        $taskAssignItems = new TaskAssign();
        foreach ($collection as $item) {
            switch ($item->role) {
                case self::ASSIGN_DEPART_REPRESENT:
                    $taskAssignItems->depart_represent = $item->employee_id;
                    $taskAssignItems->depart_represent_email = $item->email;
                    $taskAssignItems->depart_represent_name = $item->name;
                    break;
                case self::ASSIGN_TESTER:
                    $taskAssignItems->tester = $item->employee_id;
                    $taskAssignItems->tester_email = $item->email;
                    $taskAssignItems->tester_name = $item->name;
                    break;
                case self::ASSIGN_EVALUATOR:
                    $taskAssignItems->evaluater = $item->employee_id;
                    $taskAssignItems->evaluater_email = $item->email;
                    $taskAssignItems->evaluater_name = $item->name;
                    break;
                default:
                    break;
            }
        }
        return $taskAssignItems;
    }

    /**
     * get list task ncm
     *
     * @param int $projectId
     * @return collection
     */
    public static function getListTaskNcmAjax($projectId)
    {
        $type = Task::TYPE_COMPLIANCE;
        $pager = TeamConfig::getPagerDataQuery();
        $tableTask = Task::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableNcm = self::getTableName();

        $collection = Task::select($tableTask.'.id', $tableTask.'.title',
                $tableTask.'.duedate', $tableNcm.'.request_date',
                $tableNcm.'.request_standard', $tableNcm.'.requester',
                $tableEmployee.'.email as requester_email',
                $tableTask.'.status')
            ->leftJoin($tableNcm, $tableNcm.'.task_id', '=', $tableTask.'.id')
            ->leftJoin($tableEmployee, $tableEmployee.'.id', '=',
                $tableNcm.'.requester')
            ->where($tableTask.'.project_id', $projectId)
            ->groupBy($tableTask.'.id')
            ->where($tableTask.'.type', $type)
            ->orderBy($tableTask.'.status', 'asc')
            ->orderBy($tableNcm.'.request_date', 'desc')
            ->orderBy($tableTask.'.created_at', 'desc');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get grid data
     *
     * @return collection
     */
    public static function getGridData($projectId = null)
    {
        $type = Task::TYPE_NC;
        $pager = TeamConfig::getPagerData();
        $tableTask = Task::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableNcm = self::getTableName();
        $tableProject = Project::getTableName();
        $tblTaskAssigns = TaskAssign::getTableName();
        $collection = Task::select($tableTask.'.id', $tableTask.'.title',
                $tableTask.'.duedate', $tableNcm.'.request_date',
                $tableNcm.'.request_standard', $tableNcm.'.requester',
                $tableEmployee.'.email as requester_email', $tableProject.'.name',
                "{$tableProject}.id as proj_id",
                $tableTask.'.created_at',
                $tableTask.'.priority',
                "tblEmpAssign.id as assign_emp_id",
                DB::raw("SUBSTRING_INDEX(tblEmpAssign.email, ". "'@', 1) as assign_email"),
                $tableTask.'.status')
            ->leftJoin($tableNcm, $tableNcm.'.task_id', '=', $tableTask.'.id')
            ->leftJoin($tableEmployee, $tableEmployee.'.id', '=',
                $tableNcm.'.requester')
            ->leftJoin($tableProject, $tableProject.'.id', '=',
                $tableTask.'.project_id')
            // ->leftJoin("{$tableEmployee} AS empOwner", 'empOwner.id', '=', $tableTask.'.created_by')
            ->leftJoin("$tblTaskAssigns as tblAssign", function ($join) use ($tableTask) {
                $join->on('tblAssign.task_id', '=', "{$tableTask}.id")
                ->where('tblAssign.role', '=', TaskAssign::ROLE_OWNER);
            })
            ->leftJoin("{$tableEmployee} as tblEmpAssign", "tblEmpAssign.id", '=', "tblAssign.employee_id")
            ->groupBy($tableTask.'.id')
            ->where($tableTask.'.type', $type);
        if ($projectId) {
            $collection->where('project_id', $projectId);
        }
        if (CoreForm::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy($tableTask.'.status', 'asc')
                ->orderBy($tableNcm.'.request_date', 'desc')
                ->orderBy($tableTask.'.created_at', 'desc');
        }

        $filter = Form::getFilterData(null, null, route("project::report.ncm")."/");
        $dataExceptFilter = isset($filter['except']) ? $filter['except'] : [];
        if (isset($dataExceptFilter['tasks.duedate_from'])) {
            $collection->whereDate('tasks.duedate', '>=', $dataExceptFilter['tasks.duedate_from']);
        }
        if (isset($dataExceptFilter['tasks.duedate_to'])) {
            $collection->whereDate('tasks.duedate', '<=', $dataExceptFilter['tasks.duedate_to']);
        }

        if (isset($dataExceptFilter['tasks.created_from'])) {
            $collection->whereDate('tasks.created_at', '>=', $dataExceptFilter['tasks.created_from']);
        }
        if (isset($dataExceptFilter['tasks.created_to'])) {
            $collection->whereDate('tasks.created_at', '<=', $dataExceptFilter['tasks.created_to']);
        }

        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    public static function getNcDuedateInMonthAgo()
    {
        $tableTask = Task::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableTaskAssigns = TaskAssign::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tableTeam = Team::getTableName();
        $tableTaskTeam = TaskTeam::getTableName();
        $tblTaskComment = TaskComment::getTableName();
        $type = Task::TYPE_COMPLIANCE;
        $month1 = Carbon::now()->format('Y-m-d');
        $month2 = Carbon::now()->subMonth(1)->format('Y-m-d');

        return Task::leftJoin("$tableTaskAssigns as assign", function ($join) use ($tableTask) {
                $join->on('assign.task_id', '=', "{$tableTask}.id")
                ->where('assign.role', '=', TaskAssign::ROLE_OWNER);
            })->leftJoin("{$tableEmployee} as employee_assign", "employee_assign.id", '=', "assign.employee_id")

            ->leftJoin("$tableTaskAssigns as assign2", function ($join) use ($tableTask) {
                $join->on('assign2.task_id', '=', "{$tableTask}.id")
                ->where('assign2.role', '=', TaskAssign::ROLE_ASSIGNEE);
            })->leftJoin("{$tableEmployee} as employee_assign_2", "employee_assign_2.id", '=', "assign2.employee_id")

            ->leftJoin("$tableTaskAssigns as approver", function ($join) use ($tableTask) {
                $join->on('approver.task_id', '=', "{$tableTask}.id")
                ->where('approver.role', '=', TaskAssign::ROLE_APPROVER);
            })->leftJoin("{$tableEmployee} as employee_approver", "employee_approver.id", '=', "approver.employee_id")

            ->leftJoin("$tableTaskAssigns as reporter", function ($join) use ($tableTask) {
                $join->on('reporter.task_id', '=', "{$tableTask}.id")
                ->where('reporter.role', '=', TaskAssign::ROLE_REPORTER);
            })->leftJoin("{$tableEmployee} as employee_reporter", "employee_reporter.id", '=', "reporter.employee_id")

            ->leftJoin($tableTeamMember, "{$tableTeamMember}.employee_id", '=', "employee_assign.id")
            ->leftJoin($tableTaskTeam, "{$tableTaskTeam}.task_id", '=', "{$tableTask}.id")
            ->leftJoin($tableTeam, "{$tableTaskTeam}.team_id", '=', "{$tableTeam}.id")
            ->leftJoin("{$tblTaskComment} as task_cmt", "task_cmt.task_id", '=', "{$tableTask}.id")
            ->select("{$tableTask}.id as id", 'title', "tasks.status", "tasks.content",
                'priority', 'duedate', "tasks.type as type", "tasks.project_id", "{$tableTaskTeam}.team_id as task_team",
                "tasks.impact", "tasks.pqa_suggestion", "tasks.solution", "employee_assign.id as employee_id",
                "tasks.created_at as created_at", "tasks.updated_at", "{$tableTeam}.name as team_name", "{$tableTask}.cause",
                DB::raw("SUBSTRING_INDEX(employee_assign.email, ". "'@', 1) as email_assign"),
                "employee_assign_2.id as assign_2_id", DB::raw("SUBSTRING_INDEX(employee_assign_2.email, ". "'@', 1) as assign_2_email"),
                "employee_approver.id as approver_id", DB::raw("SUBSTRING_INDEX(employee_approver.email, ". "'@', 1) as approver_email"),
                "employee_reporter.id as reporter_id", DB::raw("SUBSTRING_INDEX(employee_reporter.email, ". "'@', 1) as email_reporter"),
                "employee_reporter.name as reporter_name",
                "tasks.process", "tasks.label", "tasks.correction", "tasks.corrective_action",
                "task_cmt.content as comment"
            )
            ->whereDate('duedate', '>=', $month2)->whereDate('duedate', '<', $month1)
            ->where($tableTask.'.type', $type)
            ->get();
    }
}
