<?php

namespace Rikkei\Api\Helper;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Project\Model\AssumptionConstrain;
use Rikkei\Project\Model\Communication;
use Rikkei\Project\Model\CriticalDependencie;
use Rikkei\Project\Model\DashboardLog;
use Rikkei\Project\Model\ExternalInterface;
use Rikkei\Project\Model\ProjDeliverable;
use Rikkei\Project\Model\ProjPointReport;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Project\Model\Project as ProjectModel;
use Rikkei\Project\Model\ProjectApprovedProductionCost;
use Rikkei\Project\Model\ProjectBillableCost;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\ProjectProgramLang;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\Project\Model\ProjectWONote;
use Rikkei\Project\Model\QualityPlan;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\SourceServer;
use Rikkei\Project\Model\StageAndMilestone;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Project\Model\ToolAndInfrastructure;
use Rikkei\Project\Model\Training;
use Rikkei\Project\View\ProjDbHelp;
use Rikkei\Project\View\View;
use Rikkei\Resource\Model\Programs;
use Rikkei\Sales\Model\Company;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Sales\Model\Customer;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\ManageTime\View\ManageTimeCommon;

class Project extends BaseHelper
{
    private $projectId;
    const MIN_DATE = '0000-01-01';
    const MAX_DATE = '9999-12-31';

    /*
     * get projects list
     * @param array $filter
     * @return collection $projects
     */
    public function getList($filter = [])
    {
        $defaultStateFilter = [
            ProjectModel::STATE_NEW,
            ProjectModel::STATE_PROCESSING,
            ProjectModel::STATE_PENDING,
            ProjectModel::STATE_OPPORTUNITY,
        ];
        $defaultTypeFilter = [
            ProjectModel::TYPE_OSDC,
            ProjectModel::TYPE_BASE,
            ProjectModel::TYPE_ONSITE,
        ];
        $defaultIsGetTeam = true; // get team of project or no
        $stateFilter = isset($filter['state']) ? $filter['state'] : $defaultStateFilter;
        $typeFilter = isset($filter['type']) ? $filter['type'] : $defaultTypeFilter;
        $isGetTeam = isset($filter['is_get_team']) ? $filter['is_get_team'] : $defaultIsGetTeam;
        $tblProject = ProjectModel::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmpAsManager = "employee_as_manager";
        $tblTeamProject = TeamProject::getTableName();
        $tblProjectProgram = ProjectProgramLang::getTableName();
        $tblProgram = Programs::getTableName();
        $tblCompany = Company::getTableName();

        $collection = ProjectModel::leftJoin("{$tblEmployee} AS {$tblEmpAsManager}", "{$tblEmpAsManager}.id", '=', "{$tblProject}.manager_id")
            ->leftJoin($tblTeamProject, "{$tblTeamProject}.project_id", '=', "{$tblProject}.id");
        if (isset($filter['get_crm_account_id']) && $filter['get_crm_account_id'] === '1' || isset($filter['crm_account_id'])) {
            $collection->leftJoin($tblCompany, "{$tblCompany}.id", '=', "{$tblProject}.company_id");
        }
        if (!isset($filter['state']) && !empty($filter['get_state_closed'])) {
            $collection->where(function ($query) use ($tblProject, $stateFilter) {
                $query->whereIn("{$tblProject}.state", $stateFilter)
                    ->orWhere(function ($subQuery) use ($tblProject) {
                        $subQuery->where("{$tblProject}.state", '=', ProjectModel::STATE_CLOSED)
                            ->whereDate("{$tblProject}.end_at", '>=', date('Y-01-01'));
                    });
            });
        } else {
            $collection->whereIn("{$tblProject}.state", $stateFilter);
        }
        $collection->whereIn("{$tblProject}.type", $typeFilter)
            ->where("{$tblProject}.status", ProjectModel::STATUS_APPROVED)
            ->groupBy("{$tblProject}.id")
            ->select([
                "{$tblProject}.id",
                "{$tblProject}.name",
                "{$tblProject}.state",
                "{$tblEmpAsManager}.name AS pm"
            ]);
        if (isset($filter['name'])) {
            $collection->where("{$tblProject}.name", $filter['name']);
        }
        if (isset($filter['pm'])) {
            $collection->where("{$tblEmpAsManager}.name", $filter['pm']);
        }
        if (isset($filter['id'])) {
            $collection->whereIn("{$tblProject}.id", $filter['id']);
        }
        if (isset($filter['month_from'])) {
            $collection->whereRaw(DB::raw("date_format({$tblProject}.end_at, '%Y-%m') >= '{$filter['month_from']}'"));
        }
        if (isset($filter['month_to'])) {
            $collection->whereRaw(DB::raw("date_format({$tblProject}.start_at, '%Y-%m') <= '{$filter['month_to']}'"));
        }
        if (isset($filter['crm_account_id'])) {
            $collection->whereIn("{$tblCompany}.crm_account_id", $filter['crm_account_id']);
        }

        /* add select more */
        if (isset($filter['team_id'])) {
            $collection->whereIn("{$tblTeamProject}.team_id", $filter['team_id'])
                ->addSelect(
                    DB::raw("(SELECT GROUP_CONCAT({$tblTeamProject}.team_id SEPARATOR ';')
                        FROM {$tblTeamProject}
                        WHERE {$tblTeamProject}.project_id = {$tblProject}.id
                        ) AS teams")
                );
        } else {
            $collection->addSelect(DB::raw("GROUP_CONCAT({$tblTeamProject}.team_id SEPARATOR ';') AS teams"));
        }
        if (!empty($filter['get_period'])) {
            $collection->addSelect([
                DB::raw("DATE_FORMAT({$tblProject}.start_at, '%Y-%m-%d') AS start_date"),
                DB::raw("DATE_FORMAT({$tblProject}.end_at, '%Y-%m-%d') AS end_date"),
            ]);
        }
        if (!empty($filter['get_project_code'])) {
            $collection->addSelect("{$tblProject}.project_code_auto AS project_code");
        }
        if (!empty($filter['project_lang'])) {
            $collection->leftJoin($tblProjectProgram, "{$tblProjectProgram}.project_id", '=', "{$tblProject}.id")
                ->leftJoin($tblProgram, "{$tblProjectProgram}.prog_lang_id", '=', "{$tblProgram}.id");
            $collection->addSelect(DB::raw("GROUP_CONCAT(DISTINCT {$tblProgram}.name SEPARATOR ';')as project_lang"));
        }
        if (isset($filter['get_crm_account_id']) && $filter['get_crm_account_id'] === '1') {
            $collection->addSelect("{$tblCompany}.crm_account_id");
        }
        $projects = $collection->get();

        if ($isGetTeam) {
            $teams = Team::pluck('name', 'id')->toArray();
            foreach ($projects as $project) {
                $teamProjects = [];
                foreach (explode(';', $project->teams) as $id) {
                    if (isset($teams[$id])) {
                        $teamProjects[] = ['id' => $id, 'name' => $teams[$id]];
                    }
                }
                $project->teams = $teamProjects;
            }
        }
        return $projects;
    }
    /**
     * Get effort employee in months 
     */
    public static function getMember($time)
    {
        
        $month = date('m',strtotime($time));
        $year = date('Y',strtotime($time));
        $projects = ProjectMember::effortInMonth($month, $year, null);
        return $projects;
    }

    public function getListInMonth($time)
    {
        
        $tblProject = ProjectModel::getTableName();
        $tblTeamProject = TeamProject::getTableName();
        $date = date('Y-m',strtotime($time));
        $collection = ProjectModel::whereRaw(DB::raw("date_format({$tblProject}.start_at, '%Y-%m') <= '{$date}'"))
                        ->whereRaw(DB::raw("date_format({$tblProject}.end_at, '%Y-%m') >= '{$date}'"))
                        ->where("{$tblProject}.status",ProjectModel::STATUS_APPROVED)
                        ->whereIn("{$tblProject}.state",[ProjectModel::STATE_NEW,  ProjectModel::STATE_PROCESSING, ProjectModel::STATE_CLOSED])
                        ->whereNotIn("{$tblProject}.type",[ProjectModel::TYPE_TRAINING,  ProjectModel::TYPE_RD])
                        ->whereNull("{$tblProject}.deleted_at")
                        ->whereNull("{$tblProject}.parent_id")
                        ->select([
                            "projs.id AS project_id",
                            "projs.name AS project_name",
                            "projs.manager_id",
                            "projs.start_at",
                            "projs.end_at",
                            "projs.project_code",
        ]);            
        $collection
        ->addSelect(
            DB::raw("(SELECT GROUP_CONCAT({$tblTeamProject}.team_id SEPARATOR ';')
                FROM {$tblTeamProject}
                WHERE {$tblTeamProject}.project_id = {$tblProject}.id
                ) AS teams")
        );
    $carbon = Carbon::parse($time);
    $firstDay = $carbon->firstOfMonth()->toDateString();  
    $lastDay = $carbon->endOfMonth()->toDateString();
    $worksInTimekeeping = ManageTimeCommon::countWorkingDayWithoutHoliday($firstDay, $lastDay);

     $projects = $collection->get();
     $teams = Team::orderBy('id','ASC')->pluck('name', 'id')->toArray();
            foreach ($projects as $project) {
                $teamProjects = [];
                foreach (explode(';', $project->teams) as $id) {
                    if (isset($teams[$id])) {
                        $teamProjects[] = ['id' => $id, 'name' => $teams[$id]];
                    }
                }
                $project->teams = $teamProjects;
                $project->working_time = $worksInTimekeeping;
            }
    return $projects;
    }
    /**
     * get project information in WO and Report
     *
     * @param int $projectId
     * @return array|null
     * @throws \Exception
     */
    public function getInfo($projectId)
    {
        if (empty($projectId)) {
            return null;
        }

        $tblProject = ProjectModel::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmpAsManager = "employee_as_manager";
        $tblEmpAsLeader = "employee_as_leader";
        $tblEmpAsAssignee = "employee_as_assignee";
        $tblCompany = Company::getTableName();
        $tblCustomer = Customer::getTableName();
        $tblProjQuality = ProjQuality::getTableName();

        $project = ProjectModel::with('projectMeta')
            ->leftJoin("{$tblEmployee} AS {$tblEmpAsManager}", "{$tblEmpAsManager}.id", '=', "{$tblProject}.manager_id")
            ->leftJoin("{$tblEmployee} AS {$tblEmpAsLeader}", "{$tblEmpAsLeader}.id", '=', "{$tblProject}.leader_id")
            ->leftJoin("{$tblEmployee} AS {$tblEmpAsAssignee}", "{$tblEmpAsAssignee}.id", '=', "{$tblProject}.tag_assignee")
            ->leftJoin($tblCompany, "{$tblCompany}.id", '=', "{$tblProject}.company_id")
            ->leftJoin($tblCustomer, "{$tblCustomer}.id", '=', "{$tblProject}.cust_contact_id")
            ->leftJoin($tblProjQuality, function ($query) use ($tblProjQuality, $tblProject) {
                $query->on("{$tblProjQuality}.project_id", '=', "{$tblProject}.id")
                    ->where("{$tblProjQuality}.status", '=', ProjQuality::STATUS_APPROVED);
            })
            ->where("{$tblProject}.id", $projectId)
            ->select([
                "{$tblProject}.*",
                "{$tblEmpAsManager}.name AS manager_name",
                "{$tblEmpAsLeader}.name AS leader_name",
                "{$tblEmpAsAssignee}.email AS tag_assignee_email",
                "{$tblCompany}.company AS company_name",
                "{$tblCustomer}.name AS customer_name",
                "{$tblProjQuality}.billable_effort",
                "{$tblProjQuality}.plan_effort",
                "{$tblProjQuality}.cost_approved_production",
            ])
            ->first();

        // project not exist or status is not approve
        if ($project === null || (int)$project->status !== ProjectModel::STATUS_APPROVED) {
            return null;
        }

        $this->projectId = $project->id;
        $projectNote = collect(ProjectWONote::getProjectWoNote($project->id)->toArray());
        $projectPoint = ProjectPoint::findFromProject($project->id);
        $projectPointInformation = collect(View::getProjectPointInfo($project, $projectPoint));
        $stateAll = ProjectModel::lablelState();
        $project->state_label = isset($stateAll[$project->state]) ? $stateAll[$project->state] : '';
        $typeAll = ProjectModel::labelTypeProjectFull();
        $project->type_label = isset($typeAll[$project->type]) ? $typeAll[$project->type] : '';
        $project->sales = $project->company_id ? $this->getSaleByCompany($project->company_id) : [];
        $project->teams = $project->getTeams();
        $project->program_languages = $this->getProgramLanguages();

        $projectWO = [
            'basic' => $project,
            'scope' => $this->getScope($project),
            'stages' => $this->getStages(),
            'deliverable' => $this->getDeliverable(),
            'team_allocation' => $this->getTeamAllocation($project),
            'performance' => $this->getPerformance($project, $projectNote),
            'quality' => $this->getQuality($projectPointInformation, $projectNote),
            'training_plan' => $this->getTrainingPlan(),
            'cm_plan' => $this->getCMPlan($project),
            'risk' => $this->getRisk(),
            'other' => [
                'critical_dependencies' => $this->getCriticalDependencies(),
                'assumption_constrains' => $this->getAssumptionConstrains(),
                'external_interface' => ExternalInterface::getAllExternalInterface($project->id),
                'communication' => Communication::getAllCommunication($project->id),
                'tools_and_infrastructure' => ToolAndInfrastructure::getAllToolAndInfrastructures($project->id),
            ]
        ];
        $project->makeHidden(['projectMeta']);

        $allTasks = $this->getAllTasks()->groupBy('type')->toArray();
        $taskCost = $this->splitTasksByType($allTasks, Task::TYPE_ISSUE_COST);
        $taskQuality = $this->splitTasksByType($allTasks, Task::TYPE_ISSUE_QUA);
        $taskTimeliness = $this->splitTasksByType($allTasks, Task::TYPE_ISSUE_TL);
        $taskProcess = $this->splitTasksByType($allTasks, Task::TYPE_ISSUE_PROC);
        $taskCss = $this->splitTasksByType($allTasks, Task::TYPE_ISSUE_CSS);
        $taskRisk = $this->splitTasksByType($allTasks, Task::TYPE_RISK);
        $projectReport = [
            'summary' => $projectPointInformation,
            'tasks' => [
                'summary' => array_merge($taskCost, $taskQuality, $taskTimeliness, $taskProcess, $taskCss, $taskRisk),
                'cost' => $taskCost,
                'quality' => $taskQuality,
                'timeliness' => $taskTimeliness,
                'process' => $taskProcess,
                'css' => $taskCss,
            ],
            'customer_feedback' => $this->splitTasksByType($allTasks, [Task::TYPE_COMMENDED, Task::TYPE_CRITICIZED]),
            'reports' => ProjPointReport::getList($project->id, true),
            'css' => (new CssResult())->cssResultsOfProject($project->id),
            'dashboard_log' => DashboardLog::getAllLogs($project->id, true),
        ];

        return [
            'work_order' => $projectWO,
            'project_report' => $projectReport,
        ];
    }

    /*
     * get sales by company
     */
    public function getSaleByCompany($companyId)
    {
        $company = Company::select('manager_id', 'sale_support_id')
            ->where('id', $companyId)
            ->first();
        $employeeIds = [];
        if ($company->manager_id) {
            $employeeIds[] = $company->manager_id;
        }
        if ($company->sale_support_id) {
            $employeeIds[] = $company->sale_support_id;
        }
        if (count($employeeIds) === 0) {
            return [];
        }
        return Employee::select('id', DB::raw("SUBSTRING_INDEX(email, '@', 1) as nickname"), 'email')
            ->whereIn('id', $employeeIds)
            ->get();
    }

    /*
     * get all tasks of project
     */
    public function getAllTasks()
    {
        $tblTask = Task::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTaskAssigns = TaskAssign::getTableName();
        return Task::leftJoin($tblTaskAssigns, "{$tblTaskAssigns}.task_id", '=', "{$tblTask}.id")
            ->leftJoin($tblEmployee, "{$tblEmployee}.id", '=', "{$tblTaskAssigns}.employee_id")
            ->where("{$tblTask}.project_id", $this->projectId)
            ->groupBy("{$tblTask}.id")
            ->select([
                "{$tblTask}.id",
                "{$tblTask}.title",
                "{$tblTask}.status",
                "{$tblTask}.priority",
                "{$tblTask}.duedate",
                "{$tblTask}.type",
                "{$tblTask}.project_id",
                "{$tblTask}.created_by",
                "{$tblTask}.created_at",
                DB::raw("GROUP_CONCAT(SUBSTRING_INDEX({$tblEmployee}.email, '@', 1) SEPARATOR ', ') AS email"),
                DB::raw("(SELECT COUNT(id) FROM tasks AS tasks_child WHERE parent_id = tasks.id) AS count_issues")
            ])
            ->get();
    }

    /*
     * list tasks by type
     */
    public function splitTasksByType($allTasks, $type)
    {
        $type = (array) $type;
        $statusLabel = Task::statusLabel();
        $priorityLabel = Task::priorityLabel();
        $typeLabel = Task::typeLabel();

        $tasks = [];
        foreach ($type as $itemType) {
            if (isset($allTasks[$itemType])) {
                // $tasks = array_merge($tasks, $allTasks[$itemType]);
                foreach ($allTasks[$itemType] as $task) {
                    $task['status_label'] = isset($statusLabel[$task['status']]) ? $statusLabel[$task['status']] : '';
                    $task['priority_label'] = isset($priorityLabel[$task['priority']]) ? $priorityLabel[$task['priority']] : '';
                    $task['type_label'] = isset($typeLabel[$task['type']]) ? $typeLabel[$task['type']] : '';
                    $tasks[] = $task;
                }
            }
        }
        return $tasks;
    }

    /*
     * get all program languages of project
     */
    public function getProgramLanguages()
    {
        $tblProjectProgram = ProjectProgramLang::getTableName();
        $tblProgram = Programs::getTableName();
        return ProjectProgramLang::join($tblProgram, "{$tblProgram}.id", '=', "{$tblProjectProgram}.prog_lang_id")
            ->where("{$tblProjectProgram}.project_id", $this->projectId)
            ->select(["{$tblProgram}.id", "{$tblProgram}.name"])
            ->get();
    }

    /*
     * get data in tab Scope and Object in WO
     */
    public function getScope($project)
    {
        $projectMeta = collect($project->projectMeta->toArray());
        return $projectMeta->only(['scope_desc', 'scope_customer_provide', 'scope_scope', 'scope_customer_require']);
    }

    /*
     * get data in tab Stages in WO
     */
    public function getStages()
    {
        return StageAndMilestone::where('project_id', $this->projectId)
            ->where('status', ProjectWOBase::STATUS_APPROVED)
            ->select(['id', 'stage', 'description', 'milestone', 'qua_gate_plan'])
            ->get();
    }

    /*
     * get data in tab Deliverable in WO
     */
    public function getDeliverable()
    {
        return ProjDeliverable::where('project_id', $this->projectId)
            ->where('status', ProjectWOBase::STATUS_APPROVED)
            ->select(['id', 'title', 'committed_date', 'actual_date', 'note', 'stage_id'])
            ->get();
    }

    /*
     * get data in tab Team Allocation in WO
     */
    public function getTeamAllocation($project)
    {
        $members = ProjectMember::getAllMemberAvai($project);
        $langAll = Programs::getListOption();
        $typeAll = ProjectMember::getTypeMember();
        $tblTeams = Team::getTableName();
        $memberIds = $members->pluck('employee_id')->toArray();
        $teamMembers = ProjDbHelp::getTeamOfEmployees($memberIds, ["{$tblTeams}.name"])
            ->groupBy('employee_id')->toArray();
        foreach ($members as $member) {
            $member->type_label = isset($typeAll[$member->type]) ? $typeAll[$member->type] : '';
            $langIds = explode('-', $member->prog_lang_ids);
            $languages = [];
            foreach ($langIds as $langId) {
                if (isset($langAll[$langId])) {
                    $languages[] = ['id' => $langId, 'name' => $langAll[$langId]];
                }
            }
            $member->languages = $languages;
            $teams = [];
            if (isset($teamMembers[$member->employee_id])) {
                foreach ($teamMembers[$member->employee_id] as $team) {
                    $teams[] = ['id' => $team['team_id'], 'name' => $team['name']];
                }
            }
            $member->teams = $teams;
        }
        return $members;
    }

    /*
     * get data in tab Performance in WO
     */
    public function getPerformance($project, $projectNote)
    {
        return [
            'note' => $projectNote->only(['perf_duration', 'perf_plan_effort', 'perf_effort_usage', 'perf_dev', 'perf_pm', 'perf_qa']),
            'effort' => ProjectMember::getTotalEffortTeamApproved(null, $project->id),
            'duration' => $project->end_at ? Carbon::parse($project->start_at)->diffInDays(Carbon::parse($project->end_at)) : '',
        ];
    }

    /*
    * get data in tab Quality in WO
    */
    public function getQuality($projectPointInformation, $projectNote)
    {
        $qualityPlan = QualityPlan::getQualityPlanOfProject($this->projectId);
        $qualityTasks = Task::getListTaskQuality($this->projectId);
        return [
            'target' => $projectPointInformation->only(['cost_target', 'css_css_target', 'tl_deliver_target', 'qua_leakage_target', 'proc_compliance_target']),
            'note' => $projectNote->only(['qua_effectiveness', 'qua_css', 'qua_timeliness', 'qua_leakage', 'qua_process']),
            'plan' => [
                'strategy' => $qualityPlan->content,
                'tasks' => $qualityTasks
            ],
        ];
    }

    /*
     * get data in tab Training Plan in WO
     */
    public function getTrainingPlan()
    {
        $allTrainings = Training::with('traningMember')
            ->where('project_id', $this->projectId)
            ->select(['id', 'topic', 'description', 'walver_criteria', 'status', 'start_at', 'end_at'])
            ->get();
        foreach ($allTrainings as $training) {
            $participants = [];
            foreach ($training->traningMember as $participant) {
                $participants[] = ['id' => $participant->id, 'email' => $participant->email];
            }
            $training->participants = $participants;
        }
        $allTrainings->makeHidden('traningMember');
        return $allTrainings;
    }

    /*
     * get data in tab CM Plan in WO
     */
    public function getCMPlan($project)
    {
        $sourceCode = SourceServer::getSourceServer($project->id);
        $projectMeta = collect($project->projectMeta->toArray());
        $environments = $projectMeta->only(['schedule_link', 'scope_env_test', 'env_dev', 'env_staging', 'env_production']);

        return [
            'lineofcode_baseline' => $projectMeta['lineofcode_baseline'],
            'lineofcode_current' => $projectMeta['lineofcode_current'],
            'source_code' => $sourceCode,
            'environments' => $environments,
            'others' => $projectMeta['others'],
        ];
    }

    /*
     * get data in tab Risk in WO
     */
    public function getRisk()
    {
        $selectedFields = [
            'proj_op_ricks.id', 'content', 'weakness', 'level_important', 'team_owner', 'owner',
            'employees.email as owner_email', 'teams.name as team_name', 'proj_op_ricks.status', 'proj_op_ricks.due_date'
        ];
        $statusLabel = Risk::statusLabel();
        $allRisks = Risk::getAllRisk($this->projectId, $selectedFields);
        foreach ($allRisks as $risk) {
            $risk->status_label = isset($statusLabel[$risk->status]) ? $statusLabel[$risk->status] : '';
        }
        return $allRisks;
    }

    /*
     * get data in tab Critical dependencies - Others in WO
     */
    public function getCriticalDependencies()
    {
        $collection = CriticalDependencie::with('criticalDependencyMember')
            ->where('project_id', $this->projectId);
        if (config('project.workorder_approved.critical_dependencies')) {
            $collection->whereNull('parent_id');
        }
        $allCriticalDependencies = $collection->select(['id', 'content', 'note', 'status', 'impact', 'action'])
            ->orderBy('updated_at', 'asc')
            ->get();
        foreach ($allCriticalDependencies as $criticalDependency) {
            $criticalDependencies = [];
            foreach ($criticalDependency->criticalDependencyMember as $member) {
                $criticalDependencies[] = ['id' => $member->id, 'email' => $member->email];
            }
            $criticalDependency->assignees = $criticalDependencies;
        }
        $allCriticalDependencies->makeHidden('criticalDependencyMember');
        return $allCriticalDependencies;
    }

    /*
     * get data in tab Assumption and constrains - Others in WO
     */
    public function getAssumptionConstrains()
    {
        $collection = AssumptionConstrain::with('assumptionContrainMember')
            ->where('project_id', $this->projectId);
        if (config('project.workorder_approved.assumption_constrain')) {
            $collection->whereNull('parent_id');
        }
        $allAssumptionConstrains = $collection->select(['id', 'content', 'note', 'status', 'impact', 'action'])
            ->orderBy('updated_at', 'asc')
            ->get();
        foreach ($allAssumptionConstrains as $item) {
            $assumptionConstrains = [];
            foreach ($item->assumptionContrainMember as $member) {
                $assumptionConstrains[] = ['id' => $member->id, 'email' => $member->email];
            }
            $item->assignees = $assumptionConstrains;
        }
        $allAssumptionConstrains->makeHidden('assumptionContrainMember');
        return $allAssumptionConstrains;
    }

    /**
     * Get billable effort of list project by time
     *
     * @param array $params
     * @return array
     */
    public function getBillableEffortByProjectIds($params = [])
    {
        $tblProject = ProjectModel::getTableName();
        $tblProjQuality = ProjQuality::getTableName();
        $tblTeamProject = TeamProject::getTableName();
        $tblTeam = Team::getTableName();
        // get project with teams
        $collectionProjectTeam = ProjectModel::leftJoin($tblTeamProject, "{$tblTeamProject}.project_id", '=', "{$tblProject}.id")
            ->leftJoin($tblTeam, "{$tblTeam}.id", '=', "{$tblTeamProject}.team_id")
            ->where("{$tblProject}.status", ProjectModel::STATUS_APPROVED)
            ->whereNull("{$tblTeam}.deleted_at")
            ->select("{$tblProject}.id", "{$tblProject}.name", "{$tblTeamProject}.team_id", "{$tblTeam}.name as team_name");

        // get project with billable effort and approved cost
        $collectionProjects = ProjectModel::leftJoin($tblProjQuality, function ($query) use ($tblProjQuality, $tblProject) {
                $query->on("{$tblProjQuality}.project_id", '=', "{$tblProject}.id")
                    ->where("{$tblProjQuality}.status", '=', ProjQuality::STATUS_APPROVED);
            })
            ->where("{$tblProject}.status", ProjectModel::STATUS_APPROVED)
            ->select([
                "{$tblProject}.id",
                "{$tblProject}.name",
                "{$tblProject}.kind_id",
                DB::raw("DATE({$tblProject}.start_at) AS start"),
                DB::raw("DATE({$tblProject}.end_at) AS end"),
                "{$tblProjQuality}.billable_effort",
                "{$tblProjQuality}.cost_approved_production",
            ]);

        // filter param project_ids
        if (!empty($params['project_ids'])) {
            $collectionProjects->whereIn("{$tblProject}.id", $params['project_ids']);
            $collectionProjectTeam->whereIn("{$tblProject}.id", $params['project_ids']);
        }

        $response = [];
        $projects = $collectionProjects->get();
        $projectIds = $projects->pluck('id')->toArray();
        $approvedCosts = $this->getApprovedProductionCostByProjectIds($projectIds, $params);
        // init project list
        foreach ($projects as $project) {
            $response[$project->id] = [
                'kind_id' => $project->kind_id,
                'name' => $project->name,
                'start' => $project->start,
                'end' => $project->end,
                'total_billable' => $project->billable_effort,
                'total_approved_cost' => $project->cost_approved_production,
            ];
        }
        // merge approved cost into projects
        foreach ($approvedCosts as $approvedCost) {
            $pId = $approvedCost->project_id;
            $response[$pId]['billable'][] = [
                'month' => $approvedCost->month,
                'team_id' => $approvedCost->team_id,
                'price' => $this->getPrice($approvedCost->price, $approvedCost->unapproved_price),
                'unit_price' => $approvedCost->unit_price,
                'approved_cost' => $approvedCost->cost,
            ];
        }
        // merge division into projects
        foreach ($collectionProjectTeam->get() as $project) {
            $response[$project->id]['team'][$project->team_id] = $project->team_id;
        }
        return $response;
    }

    /**
     * Lấy Đơn giá của mỗi line item trong bảng project_approved_production_cost
     * Ưu tiên lấy giá chưa duyệt => Giá đã duyệt => giá mặc định
     *
     * @param int $price   Giá đã duyệt ở cột price
     * @param int $unapproved_price    Giá chưa duyệt ở cột unapproved_price
     * @return int
     */
    public function getPrice($price, $unapproved_price)
    {
        // Ưu tiên lấy giá chưa nhập
        if (!is_null($unapproved_price)) {
            return $unapproved_price;
        }
        return is_null($price) ? ProjectApprovedProductionCost::UNIT_PRICE_DEFAULT : $price;
    }

    /**
     * get approved production cost by project ids
     * @param array $projectIds
     * @param array $params - contains date_start and date_end
     * @return array
     */
    public function getApprovedProductionCostByProjectIds($projectIds, $params)
    {
        $collectionApprovedCost = ProjectApprovedProductionCost::whereIn('project_id', $projectIds)
            ->select([
                'project_id',
                'team_id',
                'approved_production_cost AS cost',
                'price',
                'unapproved_price',
                'unit_price',
                DB::raw("CONCAT(LPAD(year, 4, '0'), '-', LPAD(month, 2, '0')) AS month"),
            ]);
        if (!empty($params['date_start'])) {
            $startMonth = substr($params['date_start'], 0, 7);
            $collectionApprovedCost->where(DB::raw("CONCAT(LPAD(year, 4, '0'), '-', LPAD(month, 2, '0'))"), '>=', $startMonth);
        }
        if (!empty($params['date_end'])) {
            $endMonth = substr($params['date_end'], 0, 7);
            $collectionApprovedCost->where(DB::raw("CONCAT(LPAD(year, 4, '0'), '-', LPAD(month, 2, '0'))"), '<=', $endMonth);
        }
        return $collectionApprovedCost->get();
    }

    /**
     * list all months between period time
     *
     * @param string $start {format: YYYY-mm.....}
     * @param string $end {format: YYYY-mm.....}
     * @return array
     */
    public function generateMonthBetween($start, $end)
    {
        $start = substr($start, 0 , 7);
        $end = substr($end, 0, 7);
        list ($yStart, $mStart) = explode('-', $start);
        $aryMonths = [];
        while ($start <= $end) {
            if ($mStart < 12) {
                $mStart++;
            } else {
                $mStart = 1;
                $yStart++;
            }
            $aryMonths[] = $start;
            $start = sprintf('%04d', $yStart) . '-' .sprintf('%02d', $mStart);
        }
        return $aryMonths;
    }

    /**
     * get project_billable_costs
     *
     * @param $proIds
     * @param $timeStart
     * @param $timeEnd
     * @return array
     */
    public function getArrayProjBillable($proIds, $timeStart, $timeEnd)
    {
        $collection =  DB::table('project_billable_costs')
        ->select(
            'id',
            'project_id',
            'price',
            'month'
        )
        ->whereIn('project_id', $proIds)
        ->where('month', '>=', $timeStart)
        ->where('month', '<=', $timeEnd)
        ->whereNull('deleted_at')
        ->get();
        $projBillables = [];
        if (!count($collection)) {
            return $projBillables;
        }

        foreach ($collection as $item) {
            $check = false;
            if (isset($projBillables[$item->project_id])) {
                foreach ($projBillables[$item->project_id] as $key => $value) {
                    if ($item->month == $value['month']) {
                        $projBillables[$item->project_id][$key]['price'] += (float) $item->price;
                        $check = true;
                        break;
                    }
                }
            }
            if (!$check) {
                $projBillables[$item->project_id][] = [
                    'month' => $item->month,
                    'price' => (float) $item->price,
                ];
            }
        }
        return $projBillables;
    }


    /**
     * getProjectCssResultByProjIds
     *
     * @param  array $projIds
     * @return collection
     */
    public function getProjectCssResultByProjIds($projIds)
    {
        $tblProj = ProjectModel::getTableName();
        $tblCss = Css::getTableName();
        $tblCssResult = CssResult::getTableName();

        return ProjectModel::select(
            "{$tblProj}.id",
            "{$tblProj}.name",
            'tblCss.id as css_id',
            'tblCss.employee_id as css_employee_id',
            'tblCssResult.id as css_result_id',
            'tblCssResult.css_id as css_result_css_id',
            'tblCssResult.name as css_result_name',
            'tblCssResult.email as css_result_email',
            'tblCssResult.avg_point as css_result_point',
            'tblCssResult.status as css_result_status',
            'tblCssResult.code as css_result_code',
            'tblCssResult.created_at as css_result_created_at'
        )
        ->leftJoin("{$tblCss} as tblCss", 'tblCss.projs_id', '=', "{$tblProj}.id")
        ->leftJoin("{$tblCssResult} as tblCssResult", 'tblCssResult.css_id', '=', "tblCss.id")
        ->join(DB::raw("(select max(id) as last_id from {$tblCssResult} group by css_id, code) result_max"),
            "result_max.last_id", "=", "tblCssResult.id")
        ->whereIn("{$tblProj}.id", $projIds)
        ->whereNull('tblCss.deleted_at')
        ->whereNull('tblCssResult.deleted_at')
        ->get();
    }
}
