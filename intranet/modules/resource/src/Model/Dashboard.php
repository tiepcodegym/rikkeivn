<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use DB;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\TeamMember;
use Lang;
use Rikkei\Resource\View\View;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Team\Model\Team;
use Rikkei\Project\Model\ProjectProgramLang;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Project\View\View as pView;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Carbon\Carbon;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Tag\Model\Tag;
use Rikkei\Resource\Model\Programs;
use Rikkei\Project\Model\ProjectMemberProgramLang;

class Dashboard extends CoreModel
{

    const KEY_CACHE = 'dashboard';
    const KEY_CACHE_CHART_MM = 'dashboard_chart_mm';
    const KEY_CACHE_HAS_EFFORT = 'dashboard_chart_has_effort';
    const MONTH_DIFF_DEFAULT = 1;

    const EFFORT_GREEN_MIN = 70;
    const EFFORT_GREEN_MAX = 120;
    const BG_EFFORT_WHITE = "#f7f7f7";
    const BG_EFFORT_YELLOW = "#fff46d";
    const BG_EFFORT_GREEN = "#54d694";
    const BG_EFFORT_RED = "#ff8293";

    public function getTypeOptions()
    {
        return [
            [
                'id' => self::TYPE_WEEK,
                'name' => Lang::get('resource::view.Dashboard.Week')
            ],
            [
                'id' => self::TYPE_MONTH,
                'name' => Lang::get('resource::view.Dashboard.Month')
            ]
        ];
    }

    /**
     * store this object
     * @var object
     */
    protected static $instance;
    
    /**
     * Show employee allocation
     * @param $startMonthFilter
     * @param $endMonthFilter
     * @param null $projId
     * @param null $projStatus
     * @param null $teamId
     * @param null $empId
     * @param null $teamsOfEmp
     * @param null $empFilter
     * @param null $programs
     * @param string $viewMode
     * @return mixed
     */
    public function empDashboard(
            $startMonthFilter,
            $endMonthFilter,
            $projId = null,
            $projStatus = null,
            $teamIds = null,
            $empId = null,
            $teamsOfEmp = null,
            $empFilter = null,
            $programs = null,
            $viewMode = 'week'
    )
    {
        $empTable = Employee::getTableName();
        $projMemTable = ProjectMember::getTableName();
        $projTable = Project::getTableName();
        $programTable = Programs::getTableName();
        $projectProgramLangTable = ProjectProgramLang::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $teamProjTable = TeamProject::getTableName();
        $teamTable = Team::getTableName();
        $employeeTeamHistoryTbl = EmployeeTeamHistory::getTableName();
        $employeeSkill = EmployeeSkill::getTableName();
        $tagTable = Tag::getTableName();
        $projectMemberProgramLangTable = ProjectMemberProgramLang::getTableName();
        $concat = self::CONCAT;
        $groupConcat = self::GROUP_CONCAT;

        $dateFilter = self::getStartEndFilter($startMonthFilter,$endMonthFilter);
        $startAt = $dateFilter[0];
        $endAt = $dateFilter[1];
        //Get actual date filter
        $where = " WHERE $projMemTable.status = ?";
        $data = [ProjectMember::STATUS_APPROVED];
        $whereFilterDate = " AND (($projMemTable.start_at >= ? AND $projMemTable.start_at <= ?) OR ($projMemTable.end_at >= ? AND $projMemTable.end_at <= ?) OR ($projMemTable.start_at <= ? AND $projMemTable.end_at >= ?))";
        $where .= $whereFilterDate;
        $filterDate = [$startAt, $endAt, $startAt, $endAt, $startAt, $endAt];
        $data = array_merge($data, $filterDate);

        if ($projId) {
           $where .= " AND $projMemTable.project_id = ?";
           $data[] = $projId;
        }

        if ($projStatus) {
            $where .= " AND pj.state = ?";
            $data[] = $projStatus;
        }
        /** 
         * End filter table2 
         */

        $result = Employee::select(
                    "{$empTable}.id",
                    "{$empTable}.name",
                    "{$empTable}.email",
                    "{$empTable}.leave_date",
                    "{$empTable}.join_date",
                    DB::raw("(SELECT group_concat(DISTINCT {$teamTable}.name ORDER BY {$teamTable}.name ASC SEPARATOR ', ') FROM {$teamTable} inner join {$teamMemberTable} on {$teamTable}.id =  {$teamMemberTable}.team_id WHERE {$teamMemberTable}.employee_id = {$empTable}.id) AS team"),
                    "table2.cols");
        $result->leftJoin(DB::raw("(SELECT e.email,
                        GROUP_CONCAT(concat($projMemTable.start_at,'$concat', $projMemTable.end_at, '$concat', $projMemTable.effort, '$concat', pj.name, '$concat', pj.id) SEPARATOR '$groupConcat') as cols,
                        $projMemTable.status,
                        GROUP_CONCAT(concat( $projMemTable.project_id ) SEPARATOR ',') as project_ids
                FROM $empTable e INNER JOIN $projMemTable ON e.id = $projMemTable.employee_id
                INNER JOIN $projTable pj ON $projMemTable.project_id = pj.id
                $where group by e.id) AS table2"), "{$empTable}.email", "=", "table2.email");
        $result->leftJoin("{$employeeTeamHistoryTbl}", "{$employeeTeamHistoryTbl}.employee_Id", "=", "{$empTable}.id");
        $result->whereRaw("(DATE({$employeeTeamHistoryTbl}.start_at) <= DATE(?) or {$employeeTeamHistoryTbl}.start_at is null) and (DATE({$employeeTeamHistoryTbl}.end_at) >= DATE(?) or {$employeeTeamHistoryTbl}.end_at is null)");
        $data = array_merge($data, [$endAt, $startAt]);
        /**
         * Filter by project
         * Show all employee of project (contains leave job)
         */
        if ($projId && !empty($projId)) { 
            $result->whereRaw("FIND_IN_SET(?,table2.project_ids)");
            $data[] = $projId;
        }

        /** 
         * Filter by team
         * Show employee of team
         */
        if ($teamIds) {
            $teamNotSysIds = Team::where('type', '!=', Team::TEAM_TYPE_SYSTENA)->find($teamIds)->pluck('id')->toArray();
            

            $result->where(function ($q) use ($employeeTeamHistoryTbl, $teamNotSysIds, $teamProjTable, $whereFilterDate, $filterDate, &$data) {
                
                $q->orWhereRaw("{$employeeTeamHistoryTbl}.team_id IN (" . implode(',', $teamNotSysIds) . ')');
            });
        }

        /**
         * Filter by employee
         */
        if ($empFilter) {
            if (ctype_digit($empFilter)) {
                $result->where("{$empTable}.id", $empFilter);
                $data[] = $empFilter;
            } else { 
                $result->where(function ($query) use ($empFilter, $empTable) {
                    $query->whereRaw("{$empTable}.email like  '%$empFilter%'")
                          ->orWhereRaw("{$empTable}.name like  '%$empFilter%'");
                });
            }
        }

        /** 
         * Filter by permission
         * Scope self
         */
        if ($empId) { 
            $result->where("{$empTable}.id", $empId);
            $data[] = $empId;
        }

        /**
         * Filter by permission
         * Scope team
         * Show employee of team and employee join project of team
         * If scope is team but filter by project then show all employees of project
         */
        if ($teamsOfEmp  && !$projId) {
            $teamSysIds = [];
            foreach ($teamsOfEmp as $teamOfEmpId) {
                $team = Team::getTeamById($teamOfEmpId);
            }
            if (count($teamSysIds)) {
                if (empty($teamIds)) {
                    $result->leftJoin("{$projMemTable}", "{$projMemTable}.employee_Id", "=", "{$empTable}.id");
                    $result->leftJoin("{$teamProjTable}", "{$teamProjTable}.project_id", "=", "{$projMemTable}.project_id");
                    $result->where(function ($query) use ($teamsOfEmp, $teamSysIds, $employeeTeamHistoryTbl, $teamProjTable, $whereFilterDate) {
                        $query->whereIn("{$employeeTeamHistoryTbl}.team_id", $teamsOfEmp)
                              ->orWhereRaw("{$teamProjTable}.team_id IN (?)" . $whereFilterDate);
                    });
                    $data[] = $teamsOfEmp;
                    $data[] = $teamSysIds;
                    $data = array_merge($data, $filterDate);
                }
            } else {
                $result->whereIn("{$employeeTeamHistoryTbl}.team_id", $teamsOfEmp);
                $data[] = $teamsOfEmp;
            }
        }

        // Filter employee leave job. Only show if start date filter <= leave date
        $result->where(function ($query) use ($empTable, $startMonthFilter, $viewMode) {
            $query->whereNull("{$empTable}.leave_date");
            if ($viewMode === 'day') {
                $query->orWhereDate("{$empTable}.leave_date", '>=', $startMonthFilter);
            } elseif ($viewMode === 'month') {
                $query->orWhereRaw("MONTH({$empTable}.leave_date) >= MONTH(?)", [$startMonthFilter]);
            } else {
                $query->orWhereRaw("WEEK({$empTable}.leave_date) >= WEEK(?)", [$startMonthFilter]);
            }
            
        });
        $data[] = $startMonthFilter;

        //Show only team is software development
        $result->leftJoin("{$teamTable}", "{$employeeTeamHistoryTbl}.team_id" , "=", "{$teamTable}.id"); 
        $result->where("{$teamTable}.is_soft_dev", Team::IS_SOFT_DEVELOPMENT);
        $data[] = Team::IS_SOFT_DEVELOPMENT;
        $result->whereRaw("DATE(employees.join_date) <= DATE(?)", [$endAt]);
        $data[] = $endAt;

        //Not show employee has working_type is WORKING_INTERNSHIP
        $result->where("{$empTable}.working_type", "!=", getOptions::WORKING_INTERNSHIP);
        $data[] = getOptions::WORKING_INTERNSHIP;

        $result->setBindings($data);
        $result->groupBy('employees.email', 'cols');
        $result->orderBy('employees.email', 'asc');

        //Filter by programming language follow skillsheet and project.
        if ($programs) {
            $result->leftJoin($employeeSkill, "{$employeeSkill}.employee_id", '=', "{$empTable}.id")
                    ->leftJoin($tagTable, "{$tagTable}.id", '=', "{$employeeSkill}.tag_id")
                    ->leftJoin("{$projMemTable}", "{$projMemTable}.employee_id", '=', "{$empTable}.id")
                    ->leftJoin("{$projectMemberProgramLangTable}", "{$projMemTable}.id", '=', "{$projectMemberProgramLangTable}.proj_member_id")
                    ->leftJoin("{$programTable}", "{$projectMemberProgramLangTable}.prog_lang_id", "=", "{$programTable}.id");

            $result->where(function ($query) use ($programTable, $programs, $tagTable) {
                    $query->whereIn("{$tagTable}.value", $programs)
                        ->orwhereIn("{$programTable}.name", $programs);
            });
        }
        return $result;
    }

    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    /**
     * Get project approved of employee
     *
     * @param int $empId
     * @param string|date $firstDay
     * @param string|date $lastDay
     * @return collection
     */
    public static function getProjectsByEmp($empId, $firstDay, $lastDay)
    {
        $projMemTable = ProjectMember::getTableName();
        $projTable = Project::getTableName();
        return ProjectMember::join("{$projTable}", "{$projTable}.id", "=" , "{$projMemTable}.project_id")
                            ->where("{$projMemTable}.employee_id", $empId)
                            ->where("{$projMemTable}.status", ProjectMember::STATUS_APPROVED)
//                            ->where("{$projMemTable}.start_at", "<=", $lastDay)
//                            ->where("{$projMemTable}.end_at", ">=", $firstDay)
                            ->whereRaw("(({$projMemTable}.start_at between '{$firstDay}' AND '{$lastDay}') 
                                        OR ({$projMemTable}.end_at between '{$firstDay}' AND '{$lastDay}') 
                                        OR ({$projMemTable}.start_at <= '{$firstDay}' AND {$projMemTable}.end_at >= '{$lastDay}'))")
                            ->select("{$projTable}.name", 
                                    "{$projMemTable}.start_at", 
                                    "{$projMemTable}.end_at",
                                    "{$projMemTable}.effort",
                                    "{$projMemTable}.type")
                            ->orderBy("{$projTable}.name", "ASC")
                            ->get();
    }

    /**
     * Get actual start and date filter
     * Because when search by month, 
     * we will get first day of first week of month, 
     * last day of last week of month
     *
     * @param string $startMonthFilter format Y-m
     * @param string $endMonthFilter format Y-m
     * @return array
     */
    private static function getStartEndFilter($startMonthFilter, $endMonthFilter)
    {
        $startMonth = explode('-',$startMonthFilter)[1];
        $startYear = explode('-',$startMonthFilter)[0];
        $endMonth = explode('-',$endMonthFilter)[1];
        $endYear = explode('-',$endMonthFilter)[0];

        $firstDayOfStartMonth = View::getInstance()->getFirstLastDaysOfMonth($startMonth,$startYear)[0];
        $endDayOfEndMonth = View::getInstance()->getFirstLastDaysOfMonth($endMonth,$endYear)[1];

        $startAt = View::getInstance()->getFirstDayOfWeek($firstDayOfStartMonth);
        $endAt = View::getInstance()->getLastDayOfWeek($endDayOfEndMonth);
        return [$startAt,$endAt];
    }

    /**
     * Get all project member in month
     *
     * @param int $month
     * @param int $year
     * @param boolean $isBorrow
     * @return collection 
     */
    public static function getEffortOfMonth($month, $year, $teamIds, $isBorrow = false) 
    {
        $firstLastMonth = View::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $start = $firstLastMonth[0];
        $end = $firstLastMonth[1];
        $projMemTable = ProjectMember::getTableName();
        $empTeamHistoryTable = EmployeeTeamHistory::getTableName();
        $projTable = Project::getTableName();
        $result = ProjectMember::where("{$projMemTable}.status", ProjectMember::STATUS_APPROVED)
                ->whereRaw("(({$projMemTable}.start_at between '{$start}' AND '{$end}') 
                        OR ({$projMemTable}.end_at between '{$start}' AND '{$end}') 
                        OR ({$projMemTable}.start_at <= '{$start}' AND {$projMemTable}.end_at >= '{$end}'))");
        $result->leftJoin("{$projTable}", "{$projTable}.id", "=", "{$projMemTable}.project_id");
        $result->where("{$projTable}.state", "<>", Project::STATE_NEW)
                ->where("{$projTable}.type", "<>", Project::TYPE_TRAINING)
                ->where("{$projTable}.type", "<>", Project::TYPE_RD);
        $select = "{$projMemTable}.id, {$projMemTable}.employee_id, {$projMemTable}.start_at, {$projMemTable}.end_at, effort, {$projMemTable}.type";
        if ($teamIds) {
            $teamProjTable = TeamProject::getTableName();
            $result->leftJoin("{$empTeamHistoryTable}", "{$empTeamHistoryTable}.employee_id", "=", "{$projMemTable}.employee_id");
            $result->leftJoin("{$empTeamHistoryTable} AS emp_leader", "emp_leader.employee_id", "=", "{$projTable}.leader_id");
            $result->where(function ($query) use ($empTeamHistoryTable, $teamProjTable, $teamIds, $projMemTable, $start, $end, $isBorrow, $projTable)  {
                $query->where(function ($subQuery) use ($empTeamHistoryTable, $teamIds, $start, $end) {
                    $subQuery->whereIn("{$empTeamHistoryTable}.team_id", $teamIds);
                    $subQuery->whereRaw("(DATE({$empTeamHistoryTable}.start_at) <= DATE(?) or {$empTeamHistoryTable}.start_at is null) and (DATE({$empTeamHistoryTable}.end_at) >= DATE(?) or {$empTeamHistoryTable}.end_at is null)", [$end, $start]);
                });
                if ($isBorrow) {
                    $query->orWhere(function ($subQuery) use ($teamIds, $projMemTable, $start, $end) {
                        $subQuery->whereIn("emp_leader.team_id", $teamIds)
                                ->whereRaw("(({$projMemTable}.start_at between '{$start}' and '{$end}') OR ($projMemTable.end_at between '{$start}' and '{$end}') OR ($projMemTable.start_at <= '{$start}' AND $projMemTable.end_at >= {$end}))")
                                ->whereRaw("(DATE(emp_leader.start_at) <= DATE(?) or emp_leader.start_at is null) and (DATE(emp_leader.end_at) >= DATE(?) or emp_leader.end_at is null)", [$end, $start]);
                    });
                }
            });
            //if isset team then select employee_team_history.end_at
            $select .= ", {$empTeamHistoryTable}.end_at as team_end_at, {$empTeamHistoryTable}.start_at as team_start_at";
        }
        $result->selectRaw($select);
        $result->groupBy("{$projMemTable}.id");
        return $result->get();
    }

    /**
     * Get total employee has not effort (effort = 0) in month
     *
     * @param int $month
     * @param int $year
     * @return int
     */
    public static function getTotalEmpWithEffort0($month, $year)
    {
        $firstLastMonth = View::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $start = $firstLastMonth[0];
        $end = $firstLastMonth[1];
        $statusApprove = ProjectMember::STATUS_APPROVED;
        $select = "SELECT DISTINCT id ";
        $from = " FROM employees ";
        $where = " WHERE id NOT IN (SELECT employee_id FROM project_members WHERE status = {$statusApprove} AND (start_at BETWEEN '{$start}' AND '{$end}') OR (end_at BETWEEN '{$start}' AND '{$end}') OR (start_at < '{$start}' AND end_at > '{$end}') GROUP BY employee_id HAVING SUM(effort) > 0)";
        $where .= " AND (leave_date IS NULL OR leave_date >= '{$start}')";
        $where .= " AND join_date <= '{$end}'";
        $where .= " AND id NOT IN (SELECT employee_id FROM team_members WHERE team_id IN (SELECT id FROM teams WHERE is_soft_dev <>  " . (int)Team::IS_SOFT_DEVELOPMENT . " OR is_soft_dev IS NULL))";
        $sql = $select . $from . $where; 
        $result = DB::select($sql); 
        return count($result);
    }

    /**
     * Get all employee has effort > 0
     *
     * @param datetime $start
     * @param datetime $end
     * @return collection
     */
    public static function getEmpHasEffort($startAt, $endAt, $teamId)
    {
        $empTable = Employee::getTableName();
        $projMemTable = ProjectMember::getTableName();
        $employeeTeamHistoryTbl = EmployeeTeamHistory::getTableName();
        $concat = self::CONCAT;
        $groupConcat = self::GROUP_CONCAT;

        //Get actual date filter
        $where = " WHERE $projMemTable.status = ?";
        $data = [ProjectMember::STATUS_APPROVED];
        $whereFilterDate = " AND (($projMemTable.start_at >= ? AND $projMemTable.start_at <= ?) OR ($projMemTable.end_at >= ? AND $projMemTable.end_at <= ?) OR ($projMemTable.start_at <= ? AND $projMemTable.end_at >= ?))";
        $where .= $whereFilterDate;
        $filterDate = [$startAt, $endAt, $startAt, $endAt, $startAt, $endAt];
        $data = array_merge($data, $filterDate);
        /** 
         * End filter table2 
         */

        $result = Employee::select("{$empTable}.id", "table2.cols");
        $result->leftJoin(DB::raw("(SELECT e.email,
                        GROUP_CONCAT(concat($projMemTable.start_at,'$concat', $projMemTable.end_at, '$concat', $projMemTable.effort) SEPARATOR '$groupConcat') as cols
                FROM $empTable e INNER JOIN $projMemTable ON e.id = $projMemTable.employee_id
                $where group by e.id) AS table2"), "{$empTable}.email", "=", "table2.email");
        $result->leftJoin("{$employeeTeamHistoryTbl}", "{$employeeTeamHistoryTbl}.employee_Id", "=", "{$empTable}.id");
        $result->whereRaw("(DATE({$employeeTeamHistoryTbl}.start_at) <= DATE(?) or {$employeeTeamHistoryTbl}.start_at is null) and (DATE({$employeeTeamHistoryTbl}.end_at) >= DATE(?) or {$employeeTeamHistoryTbl}.end_at is null)");
        $data = array_merge($data, [$endAt, $startAt]);

        /** 
         * Filter by team
         * Show employee of team and employee join project of team
         */
        if (!empty($teamId)) {
            $result->where("{$employeeTeamHistoryTbl}.team_id", $teamId);
            $data[] = $teamId;
        }

        //Show only team is software development
        $teamTable = Team::getTableName();
        $result->leftJoin("{$teamTable}", "{$employeeTeamHistoryTbl}.team_id" , "=", "{$teamTable}.id"); 
        $result->where("{$teamTable}.is_soft_dev", Team::IS_SOFT_DEVELOPMENT);
        $data[] = Team::IS_SOFT_DEVELOPMENT;
        $result->whereRaw("DATE(employees.join_date) <= DATE(?)", [$endAt]);
        $data[] = $endAt;
        $result->setBindings($data);
        $result->groupBy("{$empTable}.id");
        $result->orderBy("{$empTable}.id");
        return $result->get();
    }

    /**
     * add condition team leader has effort between $startAt and $endAt
     *
     * @param collection $result
     * @param date $startAt
     * @param date $endAt
     */
    public static function whereTeamLeaderWithEffort(&$result, $startAt, $endAt)
    {
        $empTable = Employee::getTableName();
        $projMemTable = ProjectMember::getTableName();
        $teamTable = Team::getTableName();
        $selectLeader = "SELECT DISTINCT(leader_id)";
        $fromLeader = " FROM {$teamTable} inner join {$projMemTable} on {$teamTable}.leader_id = {$projMemTable}.employee_id";
        $whereLeader = " WHERE {$projMemTable}.status = " . ProjectMember::STATUS_APPROVED 
                      . " AND {$projMemTable}.type = " . ProjectMember::TYPE_PM
                      . " AND ((start_at between '{$startAt}' AND '{$endAt}') 
                            OR (end_at between '{$startAt}' AND '{$endAt}') 
                            OR (start_at <= '{$startAt}' AND end_at >= '{$endAt}'))";
        $queryLeader = $selectLeader . $fromLeader . $whereLeader; // Select leader have effort
        $result->whereRaw("{$empTable}.id NOT IN (SELECT leader_id FROM {$teamTable} WHERE leader_id is not NULL AND leader_id NOT IN (SELECT leader_id FROM ({$queryLeader}) as table_leader))");
    }

    public static function cronUpdateDashboardData()
    {
        $twelveMonth = View::getMonthsInYear();
        $teamConditions = ['is_soft_dev' => Team::IS_SOFT_DEVELOPMENT];
        $teamList = Team::getTeamList($teamConditions, ['id']);
        $listKey = ResourceDashboard::listChartKey();
        foreach ($listKey as $key) {
            self::updateData($key, $twelveMonth, (int)date('Y'));
            foreach ($teamList as $team) {
                self::updateData($key, $twelveMonth, (int)date('Y'), $team->id);
            }
        }
    }

    /**
     * Insert or update record type total employee with effort
     *
     * @param array $twelveMonth
     * @param int|null $teamId
     */
    public static function updateData($type, $twelveMonth, $year, $teamId = null)
    {
        switch ($type) {
            case ResourceDashboard::TOTAL_EMPLOYEE_EFFORT:
                $result = self::totalEmpWithEffort($twelveMonth, $teamId);
                break;
            case ResourceDashboard::COUNT_EMPLOYEE_PLAN:
                $result = self::countEmpPlan($twelveMonth, $teamId);
                break;
            case ResourceDashboard::TOTAL_MAN_MONTH:
                $result = self::totalManMonth($twelveMonth, $teamId, true);
                break;
            case ResourceDashboard::COUNT_EMPLOYEE:
                $result = self::countEmpChart($twelveMonth, $teamId);
                break;
            case ResourceDashboard::TOTAL_ROLE:
                $result = self::totalRole($teamId);
                break;
            case ResourceDashboard::COUNT_PROLANG:
                $result = self::countProLang($teamId);
                break;
            case ResourceDashboard::MM_PROJECT:
                $firstLastDay = View::getInstance()->getFirstLastDaysOfMonth(date('m'), date('Y'));
                $result = self::MmLastMOnthByProjType($firstLastDay, $teamId);
                break;
            case ResourceDashboard::TOTAL_MAN_MONTH_NO_BORROW:
                $result = self::totalManMonth($twelveMonth, $teamId);
                break;
        }

        $data = [
            'type' => $type,
            'data' => json_encode($result),
            'year' => $year,
        ];
        if ($teamId) {
            $data['team_id'] = $teamId;
        }
        $dashboardInfo = ResourceDashboard::getByTypeAndTeam($type, $year, $teamId);
        if ($dashboardInfo) {
            $data['id'] = $dashboardInfo->id;
        } 
        ResourceDashboard::updateData($data);
    }

    /**
     * Total employee with effort in 12 months
     *
     * @param int $twelveMonth
     * @param int|null $teamId
     * @return array
     */
    public static function totalEmpWithEffort($twelveMonth, $teamId = null)
    {
        $arrayEffort = [];
        foreach ($twelveMonth as $item) {
            $count0 = $count1 = $count2 = $count3 = 0; //storage count emp by effort in month
            $firstLastMonth = View::getInstance()->getFirstLastDaysOfMonth($item[0], $item[1]);
            $empWithEffort = self::getEmpHasEffort($firstLastMonth[0], $firstLastMonth[1], $teamId);
            foreach ($empWithEffort as $emp) {
                $totalEffort = 0;
                $group = explode(self::GROUP_CONCAT,$emp->cols);
                foreach ($group as $itemChild) {
                    $child = explode(self::CONCAT,$itemChild);
                    if (!isset($child[0]) || !isset($child[1]) || !$child[0] || !$child[1]) {
                        continue;
                    }
                    $child[2] = isset($child[2]) ? $child[2] : 0;
                    try {
                        $totalEffort += View::getInstance()->getEffortOfMonth($item[0], $item[1], $child[2], $child[0], $child[1]);
                    } catch (Exception $ex) {

                    }
                }
                if ($totalEffort > 0 && $totalEffort <= self::EFFORT_GREEN_MIN) {
                    $count1++;
                } elseif ($totalEffort > self::EFFORT_GREEN_MIN && $totalEffort <= self::EFFORT_GREEN_MAX) {
                    $count2++;
                } elseif ($totalEffort > self::EFFORT_GREEN_MAX) {
                    $count3++;
                } else {
                    $count0++;
                }
            }
            $arrayEffort[0][] = $count0;
            $arrayEffort[1][] = $count1;
            $arrayEffort[2][] = $count2;
            $arrayEffort[3][] = $count3;
        }
        return $arrayEffort;
    }

    /**
     * Get count Employee plan last 6 month
     *
     * @param array $sixMonth
     * @param int $teamid
     * @return array
     */
    public static function countEmpPlan($twelveMonth, $teamId = null)
    {
        $teamIds = null;
        if ($teamId) {
            $teamIds = CheckpointPermission::getTeamChild($teamId);
        }
        $arrTotal = [];
        foreach ($twelveMonth as $item) {
            $month = $item[0];
            $year = $item[1];
            $countPlan = RecruitPlan::getCountDevInMonth($year, $month, $teamIds)->number_plan;
            $arrTotal[] = $countPlan ? $countPlan : 0;
        }
        return $arrTotal;
    }

    /**
     * Total man month in 12 months
     *
     * @param array $twelveMonth
     * @param int|null $teamId
     * @param boolean $borrow
     * @return array
     */
    public static function totalManMonth($twelveMonth, $teamId = null, $borrow = false) 
    {
        $teamIds = null;
        if ($teamId) {
            $teamIds = CheckpointPermission::getTeamChild($teamId);
        }
        $arrayEffort = [];
        foreach ($twelveMonth as $item) {
            $month = $item[0];
            $year = $item[1];
            $arrayEffort[] = static::getManMonth($month, $year, $teamId, $teamIds, $borrow);
        }
        return $arrayEffort;
    }

    /**
     * get man month of team
     *
     * @param int $month
     * @param int $year
     * @param array $teamIds
     * @param boolean $borrow
     * @return float
     */
    public static function getManMonth($month, $year, $teamId, $teamIds, $borrow)
    {
        $projMem = Dashboard::getEffortOfMonth($month, $year, $teamIds, $borrow);
        $firstLastMonth = View::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $startMonth = $firstLastMonth[0];
        $endMonth = $firstLastMonth[1];
        $totalEffort = 0;
        foreach ($projMem as $pm) {
            $startAt = $pm->start_at;
            $endAt = $pm->end_at;
            //caculate only effort from date start work to date end work in team
            if (!$borrow) {
                if (isset($pm->team_end_at) && $pm->team_end_at >= $startMonth && $pm->team_end_at <= $endMonth && $pm->team_end_at <= $pm->end_at) {
                    //if change team then end date - 1 day (date team change has effort of new team)
                    $teamEmpStartAt = EmployeeTeamHistory::getTeamByStartAt($pm->team_end_at, $pm->employee_id);
                    if (!empty($teamEmpStartAt)) {
                        $endAt = Carbon::createFromFormat('Y-m-d H:i:s', $pm->team_end_at)->subDays(1)->toDateTimeString();
                    } else {
                        $endAt = $pm->team_end_at;
                    }
                }
                if (isset($pm->team_start_at) && $pm->team_start_at >= $startMonth && $pm->team_start_at <= $endMonth && $pm->team_start_at >= $pm->start_at) {
                    $startAt = $pm->team_start_at;
                }
            } else {
                $teamOfEmp = EmployeeTeamHistory::getTeamOfEmp($month, $year, $pm->employee_id, $teamId);
                if ($teamOfEmp && isset($teamOfEmp->start_at) && $teamOfEmp->start_at >= $startMonth &&  $teamOfEmp->start_at <= $endMonth && $teamOfEmp->start_at >= $pm->start_at) {
                    $startAt = $teamOfEmp->start_at;
                }
                if ($teamOfEmp && isset($teamOfEmp->end_at) && $teamOfEmp->end_at >= $startMonth &&  $teamOfEmp->end_at <= $endMonth && $teamOfEmp->end_at <= $pm->end_at) {
                    //if change team then end date - 1 day (date team change has effort of new team)
                    $teamEmpStartAt = EmployeeTeamHistory::getTeamByStartAt($teamOfEmp->end_at, $pm->employee_id);
                    if (!empty($teamEmpStartAt)) {
                        $endAt = Carbon::createFromFormat('Y-m-d H:i:s', $teamOfEmp->end_at)->subDays(1)->toDateTimeString();
                    } else {
                        $endAt = $teamOfEmp->end_at;
                    }
                }
            }
            $manDayInMonth = pView::getMM($startMonth, $endMonth, 2);
            $manDayActual = View::getInStance()->getRealDaysOfMonth($month, $year, $startAt, $endAt);
            $totalEffort += $manDayActual/$manDayInMonth * $pm->effort / 100;
        }
        return round($totalEffort, 2);
    }

    /**
     * Get count employee in 12 months
     *
     * @param array $twelveMonth
     * @param int|null $teamId
     * @return array
     */
    static function countEmpChart($twelveMonth, $teamId = null)
    {
        $arrTotal = [];
        foreach ($twelveMonth as $item) {
            $month = $item[0];
            $year = $item[1];
            $firstLastDay = View::getInstance()->getFirstLastDaysOfMonth($month, $year);
            $firstDayOfMonth = $firstLastDay[0];
            $lastDayOfMonth = $firstLastDay[1];
            $countEmpInMonth = EmployeeTeamHistory::getCountEmployeeOfMonth($month, $year, $teamId, true);

            //Calculate MM of employees start or end in Month
            $workDaysInMonth = pView::getMM($firstDayOfMonth, $lastDayOfMonth, 2);
            $totalMmInMonthOfEmp = 0;
            if ($teamId) {
                $emps = EmployeeTeamHistory::getEmpStartOrEndInMonth($month, $year, $teamId);
            } else {
                $emps = Employee::getEmpJoinOrLeaveInMonth($month, $year);
            }
            if (!empty($emps)) {
                foreach ($emps as $emp) {
                    if ($teamId) {
                        $startAt = empty($emp->start_at) || date('Y-m-d', strtotime($emp->start_at)) < date('Y-m-d', strtotime($firstDayOfMonth)) 
                                ? $firstDayOfMonth : $emp->start_at;
                        $endAt = empty($emp->end_at) || date('Y-m-d', strtotime($emp->end_at)) > date('Y-m-d', strtotime($lastDayOfMonth)) 
                                ? $lastDayOfMonth : $emp->end_at;
                    } else {
                        $startAt = empty($emp->join_date) || date('Y-m-d', strtotime($emp->join_date)) < date('Y-m-d', strtotime($firstDayOfMonth)) 
                                ? $firstDayOfMonth : $emp->join_date;
                        $endAt = empty($emp->leave_date) || date('Y-m-d', strtotime($emp->leave_date)) > date('Y-m-d', strtotime($lastDayOfMonth)) 
                                ? $lastDayOfMonth : $emp->leave_date;
                    }
                    $workDaysInMonthOfEmp = View::getInStance()->getRealDaysOfMonth($month, $year, $startAt, $endAt);
                    $totalMmInMonthOfEmp += round($workDaysInMonthOfEmp/$workDaysInMonth, 2);
                }
            }
            //Employee MM of $month, $year
            $arrTotal[] = (int)$countEmpInMonth - count($emps) + $totalMmInMonthOfEmp;
        }
        return $arrTotal;
    }

    /**
     * Role report in last 6 month
     * @param array $sixMonth
     * @return array
     */
    public static function totalRole($teamId = null) 
    {
        $teamIds = null;
        if ($teamId) {
            $teamIds = CheckpointPermission::getTeamChild($teamId);
        }
        $types = ProjectMember::getTypeMember();
        $totalRole = [];
        $firstLastDay = View::getInstance()->getFirstLastDaysOfMonth(date('m'), date('Y'));
        $roles = ProjectMember::roleWO($firstLastDay, $teamIds);     
        foreach ($types as $type => $value) {
            $totalRole[$type] = 0;
        }
        foreach ($roles as $item) {
           foreach ($types as $type => $value) {
               if ($item->type == $type) {
                   $totalRole[$type]++;
               }
           }
        }
        return ['count' => $totalRole, 'colors' => ProjectMember::TYPE_COLOR, 'labels' => $types];
    }

    /**
     * Programming language chart data
     *
     * @return array
     */
    public static function countProLang($teamId)
    {
        $teamIds = null;
        if ($teamId) {
            $teamIds = CheckpointPermission::getTeamChild($teamId);
        }
        $progLangs = Programs::getInstance()->getList();
        $labels = [];
        $arrcount = [];
        $colors = [];
        if ($progLangs) {
            foreach ($progLangs as $pro) {
                $count = ProjectMember::countProgLang($teamIds, $pro->id);
                if ($count) {
                    $colors[$pro->id] = $pro->color;
                    $labels[$pro->id] = $pro->name;
                    $arrcount[$pro->id] = $count;
                }
            }
        }
        return ['colors' => $colors, 'labels' => $labels, 'count' => $arrcount];
    }

    /**
     * Chart Man month every project type in last month
     *
     * @param array $firstLastDay
     * @param int $teamId
     * @return array
     */
    public static function MmLastMOnthByProjType($firstLastDay, $teamId)
    {
        $teamIds = null;
        if ($teamId) {
            $teamIds = CheckpointPermission::getTeamChild($teamId);
        }
        $start = $firstLastDay[0];
        $end = $firstLastDay[1];
        $month = date('m');
        $year = date('Y');
        $effort = ProjectMember::effortInMonth($month, $year, $teamIds);
        $result = $countProjectArr = [];
        $types = Project::labelChartTypeProject();
        $countProject = ProjectMember::countProjByProjType($month, $year, $teamIds);
        foreach ($types as $key => $value) {
            $result[$key] = 0;
            $countProjectArr[$value] = 0;
            foreach ($countProject as $proj) {
                if ((int)$key == (int)$proj->type) {
                    $countProjectArr[$value] =  (int)$proj->count_project;
                    break;
                }
            }
        }
        foreach ($effort as $item) {
            $manDayInMonth = pView::getMM($start, $end, 2);
            $manDayActual = View::getInStance()->getRealDaysOfMonth($month, $year, $item->start_at, $item->end_at);
            $manMonth = round($manDayActual/$manDayInMonth * $item->effort / 100, 2);
            foreach ($types as $key => $value) {
                if ((int)$key == (int)$item->type) {
                    $result[$key] += $manMonth;
                }
            }
        }
        return [
            'labels' => $types, 
            'count' => $result, 
            'countProj' => $countProjectArr
        ];
    }

    /**
     * Get year option of resource dashboard
     *
     * @return array
     */
    public static function getDashboardYearOption()
    {
        $listYears = ResourceDashboard::getListYears();
        $years = [];
        if ($listYears) {
            foreach($listYears as $item) {
                $years[] = $item->year;
            }
            $years[] = count($years) ? $years[count($years)-1] + 1 : date('Y');
        }
        return $years;
    }
}
