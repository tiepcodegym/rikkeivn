<?php

namespace Rikkei\Api\Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Contract\Model\ContractModel as ContractModel;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\Jobs\QueueJobUpdateAllocation;
use Rikkei\Project\Model\CronjobProjectAllocations;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\View\OperationOverview;
use Rikkei\Resource\Model\Dashboard;
use Rikkei\Resource\View\UtilizationView;
use Rikkei\Resource\View\View;
use Rikkei\Team\Model\Employee as EmployeeModel;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team as TeamModel;


/**
 * Description of Contact
 *
 * @author duydv
 */
class HrmFo extends HrmBase
{
    /**
     *
     */
    const STATUS_LEFT_COMPANY = 1;
    /**
     *
     */
    const STATUS_PENDING_LEFT_COMPANY = 0;

    /**
     * Get week number of time
     * @param string|datetime $time
     * @return int week number
     */
    protected function _getWeek($time)
    {
        $w = (int)date('W', strtotime($time));
        $m = (int)date('n', strtotime($time));
        $w = $w == 1 ? ($m == 12 ? 53 : 1) : ($w >= 51 ? ($m == 1 ? 0 : $w) : $w);
        return $w;
    }

    /**
     * Get Fo Allocation by SQL
     * @param $filter
     * @return
     */
    protected function _getFoAllocationFromSql($filter)
    {
        $projId = $filter['projId'];
        $projStatus = $filter['projStatus'];
        $programs = isset($filter['programs']) ? $filter['programs'] : null;
        $teamId = $filter['teamId'];
        $empFilter = $filter['empId'];
        $startDate = $filter['startDate'];
        $endDate = $filter['endDate'];
        //get data
        $result = Dashboard::getInstance()->empDashboard($startDate, $endDate, $projId, $projStatus, $teamId, null, null, $empFilter, $programs, $filter['viewMode']);
        $result = $result->get();
        $dashboard = [];
        foreach ($result as $item) {
            if (!empty($item->cols)) {
                $rows = explode(Dashboard::GROUP_CONCAT, $item->cols);
                if (count($rows)) {
                    foreach ($rows as $row) {
                        $proj = explode(Dashboard::CONCAT, $row);
                        if (!isset($proj[0]) || !$proj[0] ||
                            !isset($proj[1]) || !$proj[1]) {
                            continue;
                        }
                        $start = $this->_getWeek($proj[0]);
                        $end = $this->_getWeek($proj[1]);
                        $dashboard[$item->email . CoreModel::GROUP_CONCAT . $item->id . CoreModel::GROUP_CONCAT . $item->leave_date . CoreModel::GROUP_CONCAT . $item->join_date . CoreModel::GROUP_CONCAT . $item->team][] =
                            [
                                'proj_id' => isset($proj[4]) ? $proj[4] : '',
                                'proj_name' => isset($proj[3]) ? $proj[3] : '',
                                'start' => $start,
                                'end' => $end,
                                'start_date' => date('Y-m-d', strtotime($proj[0])),
                                'end_date' => date('Y-m-d', strtotime($proj[1])),
                                'start_year' => date('Y', strtotime($proj[0])),
                                'end_year' => date('Y', strtotime($proj[1])),
                                'effort' => isset($proj[2]) ? $proj[2] : 0
                            ];
                    }
                }
            } else {
                $dashboard[$item->email . CoreModel::GROUP_CONCAT . $item->id . CoreModel::GROUP_CONCAT . $item->leave_date . CoreModel::GROUP_CONCAT . $item->join_date . CoreModel::GROUP_CONCAT . $item->team][] =
                    [
                        'proj_id' => null,
                        'proj_name' => null,
                        'start' => null,
                        'end' => null,
                        'start_date' => null,
                        'end_date' => null,
                        'start_year' => null,
                        'end_year' => null,
                        'effort' => null
                    ];
            }
        }

        return $dashboard;
    }

    /**
     * Get data by view mode is month
     *
     * @param array $data
     * @param string|date $startDate
     * @param string|date $endDate
     * @return array
     */
    protected function _viewFoAllocationMonth($data, $startDate, $endDate)
    {
        //Tham khảo ở file modules/resource/src/View/UtilizationView.php, function viewMonth
        $utilizationView = new UtilizationView();
        $months = $utilizationView->getMonths($startDate, $endDate);

        $dashboard = [];
        foreach ($months as $month) {
            $dashboard[$month['number']] = [
                'unallocated' => 0,
                'warning' => 0,
                'normal' => 0,
                'overloaded' => 0,
            ];
        }

        foreach ($data as $nicknameId => $value) {
            $userInfo = explode(CoreModel::GROUP_CONCAT, $nicknameId);

            $effortMonthJoinUser = $userInfo[3];
            $effortMonthLeaveUser = $userInfo[2];
            $effortMonthJoinUserTypeYM = $effortMonthJoinUser ? Carbon::parse($effortMonthJoinUser)->format('Y-m-d') : null;
            $effortMonthLeaveUserTypeMM = $effortMonthLeaveUser ? Carbon::parse($effortMonthLeaveUser)->format('Y-m-d') : null;
            foreach ($months as $month) {
                $totalEffort = 0;
                foreach ($value as $projInfo) {
                    $effort = View::getInstance()->getEffortOfMonth($month['month'], $month['year'], $projInfo['effort'], $projInfo['start_date'], $projInfo['end_date'], $effortMonthJoinUser);
                    $totalEffort += $effort;
                }
                $monthStartDate = Carbon::createFromFormat('m/Y', $month['number'])->firstOfMonth()->format('Y-m-d');
                $monthEndDate = Carbon::createFromFormat('m/Y', $month['number'])->endOfMonth()->format('Y-m-d');

                $conditionIsWorking = (is_null($effortMonthJoinUserTypeYM) || $effortMonthJoinUserTypeYM < $monthEndDate)
                    &&
                    (is_null($effortMonthLeaveUserTypeMM) || $effortMonthLeaveUserTypeMM > $monthStartDate);

                if ($conditionIsWorking) {
                    if ($totalEffort <= 0) {
                        $dashboard[$month['number']]['unallocated'] += 1;
                    } else if ($totalEffort > 0 && $totalEffort <= 70) {

                        $dashboard[$month['number']]['warning'] += 1;
                    } else if ($totalEffort > 70 && $totalEffort <= 120) {

                        $dashboard[$month['number']]['normal'] += 1;
                    } else {
                        $dashboard[$month['number']]['overloaded'] += 1;
                    }
                }
            }
        }

        return $dashboard;
    }

    /**
     * @param $request
     * @return array
     */
    protected function _initFoEffortProjectFilter($request)
    {
        $firstMonthOfYear = Carbon::createFromFormat('Y', $request->year)->firstOfYear();
        $lastMonthOfYear = Carbon::createFromFormat('Y', $request->year)->lastOfYear();

        return [
            'firstMonthOfYear' => $firstMonthOfYear,
            'lastMonthOfYear' => $lastMonthOfYear,
            'branch_code' => $request->branch_code,
            'team_id' => $request->team_id,
        ];
    }

    /**
     * @param $request
     * @return array
     */
    protected function _setFilterBusyRate($request)
    {
        $firstOfYear = Carbon::createFromFormat('Y', $request->year)->firstOfYear()->format('Y-m');
        $lastOfYear = Carbon::createFromFormat('Y', $request->year)->lastOfYear()->format('Y-m');

        $teamIds = [];
        if ($request->branch_code) {
            if ($request->team_id) {
                $teamIds[] = $request->team_id;
            } else {
                $teamIds = $this->getTeamFo($request);
                $teamIds = $teamIds->pluck('id')->toArray();
            }
        } else {
            $teamIds = TeamModel::select(['id'])->where([
                ['is_soft_dev', '=', 1],
                ['is_branch', '!=', 1]
            ])->get()->pluck('id')->toArray();
        }

        return [
            'monthFrom' => $firstOfYear,
            'monthTo' => $lastOfYear,
            'team_id' => $teamIds,
        ];
    }

    /**
     * @param $items
     *
     * @return \ArrayObject
     */
    protected function _collapseBusyRate($items)
    {
        $result = [];

        foreach ($items as $item) {
            $monthInteger = Carbon::createFromFormat('Y-m', $item['month'])->month - 1;
            if (!array_key_exists($monthInteger, $result)) {
                $result[$monthInteger] = [
                    'member' => 0,
                    'project' => 0,
                ];
            }

            $result[$monthInteger]['member'] += $item['members'];
            $result[$monthInteger]['project'] += $item['project'];
        }

        return $result;
    }

    /**
     * Get filter for Fo Allocation
     * @param string|datetime $time
     * @return array
     */
    protected function _initFilterFoAllocation($request)
    {
        $firstDateOfYear = Carbon::createFromFormat('Y', $request->year)->firstOfYear()->format('Y-m-d');
        $lastDateOfYear = Carbon::createFromFormat('Y', $request->year)->lastOfYear()->format('Y-m-d');

        $teamId = $request->team_id ? [$request->team_id] : null;

        if ($request->branch_code && !$teamId) {
            $teamId = $this->getTeamFo($request)->toArray();
            $teamId = array_map(function ($item) {
                return $item['id'];
            }, $teamId);
        }

        return [
            'projId' => '',
            'projStatus' => '',
            'programs' => '',
            'teamId' => $teamId,
            'empId' => '',
            'startDate' => $firstDateOfYear,
            'endDate' => $lastDateOfYear,
            'effort' => 0,
            'viewMode' => 'month'
        ];
    }

    /**
     * @param $filter
     * @return array
     */
    protected function _initSelectedMonthFoEffortProjectSql($filter)
    {
        $selectedMonthSql = [];
        $firstOfYearClone = clone $filter['firstMonthOfYear'];

        while ($firstOfYearClone <= $filter['lastMonthOfYear']) {
            $currentSelectedMonth = clone $firstOfYearClone;
            $currentSelectedMonthFormatYearMonth = $currentSelectedMonth->format('Y-m');

            $endOfCurrentSelectedMonth = $currentSelectedMonth->lastOfMonth()->format('Y-m-d');
            $firstOfCurrentSelectedMonth = $currentSelectedMonth->firstOfMonth()->format('Y-m-d');

            $selectedMonthSql[] = DB::raw("SUM(CASE
                WHEN
                    (effort_start_at <= '{$endOfCurrentSelectedMonth}'
                        && CASE
                        WHEN
                            leave_date IS NULL
                                OR effort_end_at < leave_date
                        THEN
                            effort_end_at
                        ELSE leave_date
                    END >= '{$firstOfCurrentSelectedMonth}')
                THEN
                    effort
                ELSE 0
            END) AS '{$currentSelectedMonthFormatYearMonth}'");

            $firstOfYearClone = $firstOfYearClone->addMonth(1);
        }

        return $selectedMonthSql;
    }

    /**
     * @return array
     */
    protected function _initSelectedTypeFoEffortProjectSql()
    {
        $arrTypes = \Rikkei\Project\Model\Project::labelTypeProject();
        $sql = '(CASE ';
        foreach ($arrTypes as $key => $value) {
            $sql .= " WHEN type = {$key} THEN '{$value}' ";
        }
        $sql .= " ELSE 'Opportunity' END) AS type";

        return [DB::raw($sql)];
    }

    /**
     * @param $filter
     * @return mixed
     */
    protected function _initFoEffortProjectSql($filter)
    {
        $selectedType = $this->_initSelectedTypeFoEffortProjectSql();
        $selectedMonth = $this->_initSelectedMonthFoEffortProjectSql($filter);

        $projTbl = \Rikkei\Project\Model\Project::getTableName();
        $projMemberTbl = ProjectMember::getTableName();
        $employeeTbl = \Rikkei\Team\Model\Employee::getTableName();

        $foEffortProjectSelectedFields = [
            "{$projTbl}.type",
            "{$projMemberTbl}.employee_id",
            "{$projMemberTbl}.project_id",
            "{$projMemberTbl}.id",
            DB::raw("ROUND({$projMemberTbl}.effort / 100, 2) as effort"),
            "{$projMemberTbl}.start_at AS effort_start_at",
            "{$projMemberTbl}.end_at AS effort_end_at",
            DB::raw("DATE_FORMAT({$employeeTbl}.leave_date, '%Y-%m-%d') AS leave_date")
        ];

        $collections = Project::select($foEffortProjectSelectedFields);

        $collections = $this->_getFoSqlEffort($collections, $filter);

        $result = DB::table(DB::raw("({$collections->toSql()}) as temp"))
            ->mergeBindings($collections->getQuery())
            ->select(array_merge($selectedType, $selectedMonth))
            ->groupBy('type')
            ->get();

        return $result;
    }

    /**
     * @param $collections
     * @param $filter
     * @return mixed
     */
    protected function _getFoSqlEffort($collections, $filter)
    {
        $firstOfYear = $filter['firstMonthOfYear']->format('Y-m-d');
        $lastOfYear = $filter['lastMonthOfYear']->format('Y-m-d');

        $projTbl = \Rikkei\Project\Model\Project::getTableName();
        $projMemberTbl = ProjectMember::getTableName();
        $employeeTbl = \Rikkei\Team\Model\Employee::getTableName();
        $teamTbl = \Rikkei\Team\Model\Team::getTableName();
        $employeeTeamHistoryTbl = EmployeeTeamHistory::getTableName();

        $collections = $collections
            ->join($projMemberTbl, "{$projMemberTbl}.project_id", '=', "{$projTbl}.id")
            ->join($employeeTbl, "{$projMemberTbl}.employee_id", '=', "{$employeeTbl}.id")
            ->join($employeeTeamHistoryTbl, "{$employeeTeamHistoryTbl}.employee_id", '=', "{$employeeTbl}.id")
            ->join($teamTbl, "{$employeeTeamHistoryTbl}.team_id", '=', "{$teamTbl}.id")
            ->whereNull("{$employeeTbl}.deleted_at")
            ->where("{$projMemberTbl}.status", 1)
            ->where("{$teamTbl}.is_soft_dev", 1)
            ->whereDate("{$projMemberTbl}.end_at", '>=', $firstOfYear)
            ->whereDate("{$projMemberTbl}.start_at", '<=', $lastOfYear)
            ->where(function ($q) use ($employeeTeamHistoryTbl, $lastOfYear, $projMemberTbl) {
                $q->whereNull("{$employeeTeamHistoryTbl}.start_at")
                    ->orWhere(function ($q) use ($employeeTeamHistoryTbl, $lastOfYear, $projMemberTbl) {
                        $q->whereDate("{$employeeTeamHistoryTbl}.start_at", '<=', $lastOfYear)
                            ->whereRaw("{$employeeTeamHistoryTbl}.start_at <= {$projMemberTbl}.end_at");
                    });
            })
            ->where(function ($q) use ($employeeTeamHistoryTbl, $firstOfYear, $projMemberTbl) {
                $q->whereNull("{$employeeTeamHistoryTbl}.end_at")
                    ->orWhere(function ($q) use ($employeeTeamHistoryTbl, $firstOfYear, $projMemberTbl) {
                        $q->whereDate("{$employeeTeamHistoryTbl}.end_at", '>=', $firstOfYear)
                            ->whereRaw("{$employeeTeamHistoryTbl}.end_at >= {$projMemberTbl}.start_at");
                    });
            })
            ->whereIn("{$projTbl}.state", [Project::STATE_OPPORTUNITY, Project::STATE_PROCESSING, Project::STATE_PENDING, Project::STATE_CLOSED]);

        if ($filter['branch_code']) {
            $collections = $collections->where("{$teamTbl}.branch_code", $filter['branch_code']);
        }
        if ($filter['team_id']) {
            $collections = $collections->where("{$employeeTeamHistoryTbl}.team_id", $filter['team_id']);
        }

        return $collections->groupBy("{$projMemberTbl}.id");
    }

    /**
     * @param $collections
     * @param $filter
     * @return mixed
     */
    protected function _getFoSqlEffortForDay($collections, $filter)
    {
        $date= $filter['date']->format('Y-m-d');

        $projTbl = \Rikkei\Project\Model\Project::getTableName();
        $projMemberTbl = ProjectMember::getTableName();
        $employeeTbl = \Rikkei\Team\Model\Employee::getTableName();
        $teamTbl = \Rikkei\Team\Model\Team::getTableName();
        $employeeTeamHistoryTbl = EmployeeTeamHistory::getTableName();

        $collections = $collections
            ->join($projMemberTbl, "{$projMemberTbl}.project_id", '=', "{$projTbl}.id")
            ->join($employeeTbl, "{$projMemberTbl}.employee_id", '=', "{$employeeTbl}.id")
            ->join($employeeTeamHistoryTbl, "{$employeeTeamHistoryTbl}.employee_id", '=', "{$employeeTbl}.id")
            ->join($teamTbl, "{$employeeTeamHistoryTbl}.team_id", '=', "{$teamTbl}.id")
            ->whereNull("{$employeeTbl}.deleted_at")
            ->where("{$projMemberTbl}.status", 1)
            ->where("{$teamTbl}.is_soft_dev", 1)
            ->whereDate("{$projMemberTbl}.end_at", '>=', $date)
            ->whereDate("{$projMemberTbl}.start_at", '<=', $date)
            ->where(function ($q) use ($employeeTeamHistoryTbl, $date, $projMemberTbl) {
                $q->whereNull("{$employeeTeamHistoryTbl}.start_at")
                    ->orWhere(function ($q) use ($employeeTeamHistoryTbl, $date, $projMemberTbl) {
                        $q->whereDate("{$employeeTeamHistoryTbl}.start_at", '<=', $date)
                            ->whereRaw("{$employeeTeamHistoryTbl}.start_at <= {$projMemberTbl}.end_at");
                    });
            })
            ->where(function ($q) use ($employeeTeamHistoryTbl, $date, $projMemberTbl) {
                $q->whereNull("{$employeeTeamHistoryTbl}.end_at")
                    ->orWhere(function ($q) use ($employeeTeamHistoryTbl, $date, $projMemberTbl) {
                        $q->whereDate("{$employeeTeamHistoryTbl}.end_at", '>=', $date)
                            ->whereRaw("{$employeeTeamHistoryTbl}.end_at >= {$projMemberTbl}.start_at");
                    });
            })
            ->whereIn("{$projTbl}.state", [Project::STATE_OPPORTUNITY, Project::STATE_PROCESSING, Project::STATE_PENDING, Project::STATE_CLOSED]);

        if ($filter['branch_code']) {
            $collections = $collections->where("{$teamTbl}.branch_code", $filter['branch_code']);
        }
        if ($filter['team_id']) {
            $collections = $collections->where("{$employeeTeamHistoryTbl}.team_id", $filter['team_id']);
        }
//        dd($collections->toSql());
        return $collections->groupBy("{$projMemberTbl}.id");
    }

    /**
     * @param $collections
     * @param $filter
     * @return mixed
     */
    protected function _getFoSqlEffortEmployee($collections, $filter)
    {
        $date= $filter['date']->format('Y-m-d');

        $projTbl = \Rikkei\Project\Model\Project::getTableName();
        $projMemberTbl = ProjectMember::getTableName();
        $employeeTbl = \Rikkei\Team\Model\Employee::getTableName();
        $teamTbl = \Rikkei\Team\Model\Team::getTableName();
        $employeeTeamHistoryTbl = EmployeeTeamHistory::getTableName();

        $collections = $collections
            ->join($projMemberTbl, "{$projMemberTbl}.project_id", '=', "{$projTbl}.id")
            ->join($employeeTbl, "{$projMemberTbl}.employee_id", '=', "{$employeeTbl}.id")
            ->join($employeeTeamHistoryTbl, "{$employeeTeamHistoryTbl}.employee_id", '=', "{$employeeTbl}.id")
            ->join($teamTbl, "{$employeeTeamHistoryTbl}.team_id", '=', "{$teamTbl}.id")
            ->whereNull("{$employeeTbl}.deleted_at")
            ->where("{$projMemberTbl}.status", 1)
            ->where("{$teamTbl}.is_soft_dev", 1)
            ->whereDate("{$projMemberTbl}.end_at", '>=', $date)
            ->whereDate("{$projMemberTbl}.start_at", '<=', $date)
            ->where(function ($q) use ($employeeTeamHistoryTbl, $date, $projMemberTbl) {
                $q->whereNull("{$employeeTeamHistoryTbl}.start_at")
                    ->orWhere(function ($q) use ($employeeTeamHistoryTbl, $date, $projMemberTbl) {
                        $q->whereDate("{$employeeTeamHistoryTbl}.start_at", '<=', $date)
                            ->whereRaw("{$employeeTeamHistoryTbl}.start_at <= {$projMemberTbl}.end_at");
                    });
            })
            ->where(function ($q) use ($employeeTeamHistoryTbl, $date, $projMemberTbl) {
                $q->whereNull("{$employeeTeamHistoryTbl}.end_at")
                    ->orWhere(function ($q) use ($employeeTeamHistoryTbl, $date, $projMemberTbl) {
                        $q->whereDate("{$employeeTeamHistoryTbl}.end_at", '>=', $date)
                            ->whereRaw("{$employeeTeamHistoryTbl}.end_at >= {$projMemberTbl}.start_at");
                    });
            })
            ->whereIn("{$projTbl}.state", [Project::STATE_OPPORTUNITY, Project::STATE_PROCESSING, Project::STATE_PENDING, Project::STATE_CLOSED]);

        if ($filter['branch_code']) {
            $collections = $collections->where("{$teamTbl}.branch_code", $filter['branch_code']);
        }
        if ($filter['team_id']) {
            $collections = $collections->where("{$employeeTeamHistoryTbl}.team_id", $filter['team_id']);
        }

        return $collections->groupBy("{$projMemberTbl}.employee_id");
    }

    /**
     * @param $request
     * @return array
     */
    protected function _initFoEffortRoleFilter($request)
    {
        $dateFrom = Carbon::createFromFormat('Y-m', $request->month_from)->firstOfMonth();
        $dateTo = $request->month_to ? Carbon::createFromFormat('Y-m', $request->month_to)->lastOfMonth() : Carbon::now()->lastOfMonth();

        return [
            'firstMonthOfYear' => $dateFrom,
            'lastMonthOfYear' => $dateTo,
            'branch_code' => $request->branch_code,
            'team_id' => $request->team_id,
        ];
    }

    /**
     * @param $request
     * @return array
     */
    protected function _initFoEffortRoleForDayFilter($request)
    {
        $date = Carbon::createFromFormat('d/m/Y', $request->date);
        return [
            'date' => $date,
            'branch_code' => $request->branch_code,
            'team_id' => $request->team_id,
        ];
    }

    /**
     * @param $filter
     * @return array
     */
    protected function _initTotalFieldFoEffortRoleSql($filter)
    {
        $selectedMonthSql = [];
        $dateFrom = clone $filter['firstMonthOfYear'];
        $dateFrom = $dateFrom->format('Y-m-d');
        $dateTo = clone $filter['lastMonthOfYear'];
        $dateTo = $dateTo->format('Y-m-d');

        $selectedMonthSql[] = DB::raw("SUM(CASE
                WHEN
                    (effort_start_at <= '{$dateTo}'
                        && CASE
                        WHEN
                            leave_date IS NULL
                                OR effort_end_at < leave_date
                        THEN
                            effort_end_at
                        ELSE leave_date
                    END >= '{$dateFrom}')
                THEN
                    effort
                ELSE 0
            END) AS 'effort'");

        return $selectedMonthSql;
    }

    /**
     * @param $filter
     * @return array
     */
    protected function _initTotalFieldFoEffortRoleForDaySql($filter)
    {
        $selectedDaySql = [];
        $date = clone $filter['date'];
        $dateFrom = $date->format('Y-m-d');
        $selectedDaySql[] = DB::raw("SUM(CASE
                WHEN
                    (effort_start_at <= '{$dateFrom}'
                        && CASE
                        WHEN
                            leave_date IS NULL
                                OR effort_end_at < leave_date
                        THEN
                            effort_end_at
                        ELSE leave_date
                    END >= '{$dateFrom}')
                THEN
                    effort
                ELSE 0
            END) AS 'effort'");
        return $selectedDaySql;
    }

    /**
     * @return array
     */
    protected function _initSelectedRoleFoEffortRoleSql()
    {
        $arrTypes = ProjectMember::getTypeMember();
        $sql = '(CASE ';
        foreach ($arrTypes as $key => $value) {
            $sql .= " WHEN type = {$key} THEN '{$value}' ";
        }
        $sql .= " ELSE 'Other' END) AS role";

        return [DB::raw($sql)];
    }

    /**
     * @param $filter
     * @return mixed
     */
    protected function _initFoEffortRoleSql($filter)
    {
        $selectedType = $this->_initSelectedRoleFoEffortRoleSql();
        $selectedMonth = $this->_initTotalFieldFoEffortRoleSql($filter);

        $projMemberTbl = ProjectMember::getTableName();
        $employeeTbl = \Rikkei\Team\Model\Employee::getTableName();

        $foEffortProjectSelectedFields = [
            "{$projMemberTbl}.type",
            "{$projMemberTbl}.employee_id",
            "{$projMemberTbl}.project_id",
            "{$projMemberTbl}.id",
            "{$projMemberTbl}.effort",
            "{$projMemberTbl}.start_at AS effort_start_at",
            "{$projMemberTbl}.end_at AS effort_end_at",
            DB::raw("DATE_FORMAT({$employeeTbl}.leave_date, '%Y-%m-%d') AS leave_date")
        ];

        $collections = Project::select($foEffortProjectSelectedFields);

        $collections = $this->_getFoSqlEffort($collections, $filter);

        $result = DB::table(DB::raw("({$collections->toSql()}) as temp"))
            ->mergeBindings($collections->getQuery())
            ->select(array_merge($selectedType, $selectedMonth))
            ->groupBy('role')
            ->get();

        return $result;
    }

    /**
     * @param $filter
     * @return mixed
     */
    protected function _initFoEffortRoleForDaySql($filter)
    {
        $selectedType = $this->_initSelectedRoleFoEffortRoleSql();
        $selectedDate = $this->_initTotalFieldFoEffortRoleForDaySql($filter);
        $projMemberTbl = ProjectMember::getTableName();
        $employeeTbl = \Rikkei\Team\Model\Employee::getTableName();

        $foEffortProjectSelectedFields = [
            "{$projMemberTbl}.type",
            "{$projMemberTbl}.employee_id",
            "{$projMemberTbl}.project_id",
            "{$projMemberTbl}.id",
            "{$projMemberTbl}.effort",
            "{$projMemberTbl}.start_at AS effort_start_at",
            "{$projMemberTbl}.end_at AS effort_end_at",
            DB::raw("DATE_FORMAT({$employeeTbl}.leave_date, '%Y-%m-%d') AS leave_date")
        ];

        $collections = Project::select($foEffortProjectSelectedFields);

        $collections = $this->_getFoSqlEffortForDay($collections, $filter);

        $result = DB::table(DB::raw("({$collections->toSql()}) as temp"))
            ->mergeBindings($collections->getQuery())
            ->select(array_merge($selectedType, $selectedDate))
            ->groupBy('role')
            ->get();

        return $result;
    }

    /**
     * @param $filter
     * @return mixed
     */
    protected function _initFoEffortEmployee($filter)
    {
        $projMemberTbl = ProjectMember::getTableName();
        $employeeTbl = \Rikkei\Team\Model\Employee::getTableName();

        $foEffortProjectSelectedFields = [
            "{$employeeTbl}.email",
            DB::raw("SUM({$projMemberTbl}.effort) AS effort_emp")
        ];

        $collections = Project::select($foEffortProjectSelectedFields);
        $collections = $this->_getFoSqlEffortEmployee($collections, $filter);
        $result = $collections->get()->toArray();

        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    protected function _getFoTotalEmployees($request)
    {
        $func = "_foTotalEmployeesSql";

        $filter = $this->_initFoTotalEmployeesFilter($request, $func);

        return $this->_initFoOverAllSql($filter);

    }

    /**
     * @param $request
     * @return mixed
     */
    protected function _getFoTotalEmployeesHide($request)
    {
        $func = "_foTotalEmployeesHideSql";

        $filter = $this->_initFoTotalEmployeesFilter($request,  $func);

        return $this->_initFoOverAllSql($filter);

    }

    /**
     * @param $request
     * @return array
     */
    protected function _getFoTotalEmployeesBorrow($request)
    {
        if ($request->branch_code || $request->team_id) {

            $func = "_foTotalEmployeesBorrowSql";

            $filter = $this->_initFoTotalEmployeesFilter($request, $func);

            return $this->_initFoOverAllSql($filter);
        } else {

            $response = [];

            for ($i=0; $i < 12; $i++) {

                $response[$i]['month'] = $i + 1;

                $response[$i]['total_employee_borrow'] = 0;

            }

            return $response;
        }

    }

    /**
     * @param $request
     * @param $func
     * @return array
     */
    protected function _initFoTotalEmployeesFilter($request, $func)
    {
        $firstMonthOfYear = Carbon::createFromFormat('Y', $request->year)->firstOfYear();
        $lastMonthOfYear = Carbon::createFromFormat('Y', $request->year)->lastOfYear();

        return [
            'firstMonthOfYear' => $firstMonthOfYear,
            'lastMonthOfYear' => $lastMonthOfYear,
            'branch_code' => $request->branch_code,
            'team_id' => $request->team_id,
            'func' => $func
        ];
    }

    /**
     * @param $filter
     * @return mixed
     */
    protected function _initFoOverAllSql($filter)
    {
        $firstOfYearClone = clone $filter['firstMonthOfYear'];
        $hrmTotal = (object) array();

        while ($firstOfYearClone->lte($filter['lastMonthOfYear'])) {
            $currentSelectedMonth = clone $firstOfYearClone;

            $endOfCurrentSelectedMonth = $currentSelectedMonth->lastOfMonth()->format('Y-m-d');
            $selectedMonth = $currentSelectedMonth->month;

            $sql = $this->{$filter['func']}($selectedMonth, $endOfCurrentSelectedMonth, $filter);

            if ($firstOfYearClone->eq($filter['firstMonthOfYear'])) {
                $hrmTotal = $sql;
            } else {
                $collections = $hrmTotal->union($sql);
            }

            $firstOfYearClone = $firstOfYearClone->addMonth();
        }

        return $collections->get();
    }

    /**
     * Tổng nhân viên của Division
     *
     * @param $selectedMonth
     * @param $endOfCurrentSelectedMonth
     * @param $filter
     * @return mixed
     */
    protected function _foTotalEmployeesSql($selectedMonth, $endOfCurrentSelectedMonth, $filter)
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $contractTable = ContractModel::getTableName();

        $sql  = DB::table($employeeTable)->select(DB::raw("'{$selectedMonth}' as 'month'"),
            DB::raw("COUNT(DISTINCT {$employeeTable}.id) as total_employee_full")
        )
            ->leftjoin($contractTable,
                "{$contractTable}.employee_id", '=', "{$employeeTable}.id"
            )
            ->join($teamHistory,
                "{$teamHistory}.employee_id", '=', "{$employeeTable}.id"
            )
            ->join($teamTable,
                "{$teamTable}.id", '=', "{$teamHistory}.team_id"
            )
            ->whereNull("{$employeeTable}.deleted_at")
            ->whereNull("{$contractTable}.deleted_at")
            ->whereNull("{$teamHistory}.deleted_at")
            ->where(function ($q) use ($contractTable) {
                $q->where("{$contractTable}.type", '!=', \Rikkei\Resource\View\getOptions::WORKING_BORROW)
                    ->orwhereNull("{$contractTable}.type");
            })
            ->where(function ($q) use ($teamHistory, $endOfCurrentSelectedMonth) {
                $q->whereNull("{$teamHistory}.start_at")
                    ->orWhereDate("{$teamHistory}.start_at", '<=', $endOfCurrentSelectedMonth);
            })
            ->where(function ($q) use ($endOfCurrentSelectedMonth) {
                $q->whereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $endOfCurrentSelectedMonth);
            })
            ->where(function ($q) use ($teamHistory, $endOfCurrentSelectedMonth) {
                $q->whereNull("{$teamHistory}.end_at")
                    ->orWhereDate("{$teamHistory}.end_at", '>=', $endOfCurrentSelectedMonth);
            })
            ->whereNotNull("{$teamTable}.branch_code")
            ->where("{$teamTable}.branch_code", '!=', '')
            ->where("{$teamTable}.is_soft_dev", 1);

        if ($filter['branch_code']) {
            $sql->where("{$teamTable}.branch_code", $filter['branch_code']);
        }

        if ($filter['team_id']) {
            $sql->where("{$teamTable}.id", $filter['team_id']);
        }

        return $sql;

    }

    /**
     * Get Nhân viên thuê ngoài
     *
     * @param $selectedMonth
     * @param $endOfCurrentSelectedMonth
     * @param $filter
     * @return mixed
     */
    protected function _foTotalEmployeesHideSql($selectedMonth, $endOfCurrentSelectedMonth, $filter)
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $contractTable = ContractModel::getTableName();


        $sql  = DB::table($employeeTable)->select(DB::raw("'{$selectedMonth}' as 'month'"),
            DB::raw("COUNT(DISTINCT {$employeeTable}.id) as total_employee_hide")
        )
            ->join($contractTable,
                "{$contractTable}.employee_id", '=', "{$employeeTable}.id"
            )
            ->join($teamHistory,
                "{$teamHistory}.employee_id", '=', "{$employeeTable}.id"
            )
            ->join($teamTable,
                "{$teamTable}.id", '=', "{$teamHistory}.team_id"
            )
            ->whereNull("{$employeeTable}.deleted_at")
            ->whereNull("{$contractTable}.deleted_at")
            ->where(function ($q) use ($teamHistory, $endOfCurrentSelectedMonth) {
                $q->whereNull("{$teamHistory}.start_at")
                    ->orWhereDate("{$teamHistory}.start_at", '<=', $endOfCurrentSelectedMonth);
            })
            ->where(function ($q) use ($endOfCurrentSelectedMonth) {
                $q->whereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $endOfCurrentSelectedMonth);
            })
            ->where(function ($q) use ($teamHistory, $endOfCurrentSelectedMonth) {
                $q->whereNull("{$teamHistory}.end_at")
                    ->orWhereDate("{$teamHistory}.end_at", '>=', $endOfCurrentSelectedMonth);
            })
            ->where("{$contractTable}.type", \Rikkei\Resource\View\getOptions::WORKING_BORROW)
            ->whereNull("{$teamHistory}.deleted_at")
            ->whereNotNull("{$teamTable}.branch_code")
            ->where("{$teamTable}.branch_code", '!=', '')
            ->where("{$teamTable}.is_soft_dev", 1);

        if ($filter['branch_code']) {
            $sql->where("{$teamTable}.branch_code", $filter['branch_code']);
        }

        if ($filter['team_id']) {
            $sql->where("{$teamTable}.id", $filter['team_id']);
        }

        return $sql;
    }

    /**
     * Get Nhân viên mượn từ Division khác
     *
     * @param $selectedMonth
     * @param $endOfCurrentSelectedMonth
     * @param $filter
     * @return mixed
     */
    protected function _foTotalEmployeesBorrowSql($selectedMonth, $endOfCurrentSelectedMonth, $filter)
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $contractTable = ContractModel::getTableName();
        $projTbl = \Rikkei\Project\Model\Project::getTableName();
        $projMemberTbl = ProjectMember::getTableName();

        $sql  = EmployeeModel::select(DB::raw("'{$selectedMonth}' as 'month'"),
            DB::raw("COUNT(DISTINCT {$employeeTable}.id) as total_employee_borrow")
        )
            ->join($contractTable,
                "{$contractTable}.employee_id", '=', "{$employeeTable}.id"
            )
            ->join($teamHistory,
                "{$teamHistory}.employee_id", '=', "{$employeeTable}.id"
            )
            ->join("{$teamTable} as team_employee",
                'team_employee.id', '=', "{$teamHistory}.team_id"
            )
            ->join($projMemberTbl,
                "{$projMemberTbl}.employee_id", '=', "{$employeeTable}.id"
            )
            ->join($projTbl,
                "{$projTbl}.id", '=', "{$projMemberTbl}.project_id"
            )
            ->join("{$teamHistory} as employee_team_history_leader",
                'employee_team_history_leader.employee_id', '=', "{$projTbl}.leader_id"
            )
            ->join("{$teamTable} as team_leader",
                'team_leader.id', '=', 'employee_team_history_leader.team_id'
            )
            ->whereNull("{$employeeTable}.deleted_at")
            ->whereNull("{$contractTable}.deleted_at")
            ->where(function ($q) use ($teamHistory, $endOfCurrentSelectedMonth) {
                $q->whereNull("{$teamHistory}.start_at")
                    ->orWhereDate("{$teamHistory}.start_at", '<=', $endOfCurrentSelectedMonth);
            })
            ->where(function ($q) use ($endOfCurrentSelectedMonth) {
                $q->whereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $endOfCurrentSelectedMonth);
            })
            ->where(function ($q) use ($teamHistory, $endOfCurrentSelectedMonth) {
                $q->whereNull("{$teamHistory}.end_at")
                    ->orWhereDate("{$teamHistory}.end_at", '>=', $endOfCurrentSelectedMonth);
            })
            ->where("{$contractTable}.type", '!=', \Rikkei\Resource\View\getOptions::WORKING_BORROW)
            ->whereNull("{$teamHistory}.deleted_at")
            ->whereNotNull('team_employee.branch_code')
            ->where('team_employee.branch_code', '!=', '')
            ->where('team_employee.is_soft_dev', 1)
            ->whereNotNull('team_leader.branch_code')
            ->where('team_leader.branch_code', '!=', '')
            ->where('team_leader.is_soft_dev', 1);

        if ($filter['team_id']) {
            $sql->where('team_employee.id', '!=', $filter['team_id'])
                ->where('team_leader.id', $filter['team_id']);
        }

        if ($filter['branch_code']) {
            $sql->where('team_employee.branch_code', '!=', $filter['branch_code'])
                ->where('team_leader.branch_code', $filter['branch_code']);
        }

        return $sql;
    }

    /**
     * Get team FO
     *
     * @param $request
     * @return mixed
     */
    public function getTeamFo($request)
    {
        return TeamModel::select(['id', 'name'])->where([
            ['branch_code', '=', $request->branch_code],
            ['is_soft_dev', '=', 1],
        ])->orderBy('name', 'ASC')->get();
    }

    /**
     * Overall
     *
     * @param $request
     * @return array
     */
    public function getFoOverall($request)
    {
        $response = [];
        $foTotalEmpResponse = $this->_getFoTotalEmployees($request);
        $foTotalEmpHideResponse = $this->_getFoTotalEmployeesHide($request);
        $foTotalEmpBorrowResponse = $this->_getFoTotalEmployeesBorrow($request);
        $foAllocationResponse = $this->getFoAllocationOptimized($request);
        for ($month = 0; $month < 12; $month++) {
            $response[$month]['month'] = $month + 1;

            //
            $response[$month]['total_employees'] = $foTotalEmpResponse[$month]->total_employee_full;

            $response[$month]['total_include_borrowing'] = $foTotalEmpBorrowResponse[$month]['total_employee_borrow'] +
                $foTotalEmpHideResponse[$month]->total_employee_hide +
                $foTotalEmpResponse[$month]->total_employee_full;

            $response[$month]['total_allocated'] =  isset($foAllocationResponse[$month])
                ? (($foAllocationResponse[$month]['warning'] + $foAllocationResponse[$month]['normal'] + $foAllocationResponse[$month]['overloaded'])) : 0;

            $response[$month]['busy_rate'] = ($response[$month]['total_employees'] > 0) ? ($response[$month]['total_allocated'] * 100 / $response[$month]['total_employees']) : 0;
        }

        return $response;
       
    }

    /**
     * Get Fo Allocation Data By Queues
     * @param string|datetime $time
     * @return array
     */
    public function getFoAllocation($request)
    {
        $filter = $this->_initFilterFoAllocation($request);
        $dashboard = $this->_getFoAllocationFromSql($filter);

        $dataView = $this->_viewFoAllocationMonth($dashboard, $filter['startDate'], $filter['endDate']);

        $result = [];

        foreach ($dataView as $key => $value) {
            $result[] = array_merge([
                'month' => (int)Carbon::createFromFormat('m/Y', $key)->format('m')
            ], $value);
        }

        return $result;
    }

    /**
     * Thống kê Allocations
     * @param string|datetime $time
     * @return array
     */
    public function getFoAllocationOptimized($request)
    {
        $data = CronjobProjectAllocations::where([
            ['year', '=', $request->year],
            ['branch_code', '=', $request->branch_code ? $request->branch_code : null],
            ['team_id', '=', $request->team_id ? $request->team_id : null],
        ])->first();

        return $data ? unserialize($data->allocation_serialize) : [];
    }

    /**
     * Thống kê Effort Project
     * 
     * @param $request
     * @return mixed
     */
    public function getFoEffortProject($request)
    {
        $filter = $this->_initFoEffortProjectFilter($request);

        return $this->_initFoEffortProjectSql($filter);
    }

    /**
     * Thống kê Effort Role
     * 
     * @param $request
     * @return array
     */
    public function getFoEffortRole($request)
    {
        $filter = $this->_initFoEffortRoleFilter($request);

        $items = $this->_initFoEffortRoleSql($filter);

        $total = array_sum(array_map(function ($item) {
            return $item->effort;
        }, $items));

        $result = [];
        $totalWithOutLastItem = 0;
        foreach ($items as $item) {
            $result[$item->role] = 0;
            if ($item->role != end($items)->role) {
                if ($total > 0) {
                    $result[$item->role] = round($item->effort * 100.0 / $total, 2);
                    $totalWithOutLastItem += $result[$item->role];
                }
            } else {
                $result[$item->role] = round(100 - $totalWithOutLastItem, 2);
            }
        }

        return $result;
    }

    /**
     * Thống kê Effort Role For Day
     *
     * @param $request
     * @return array
     */
    public function getFoEffortRoleForDay($request)
    {
        $filter = $this->_initFoEffortRoleForDayFilter($request);

        $items = $this->_initFoEffortRoleForDaySql($filter);
        $result = [];
        foreach ($items as $item) {
            $result[$item->role] = $item->effort / 100;
        }

        return $result;
    }

    /**
     * Thống kê Effort Role For Day
     *
     * @param $request
     * @return array
     */
    public function getFoEffortRoleEmployee($request)
    {
        $filter = $this->_initFoEffortRoleForDayFilter($request);
        $items = $this->_initFoEffortEmployee($filter);

        return $items;
    }


    /**
     * Cronjob function to store Allocation
     */
    public static function cronjobHrmAllocation()
    {
        $currentYear = Carbon::now()->year;
        $startYear = 2012;

        DB::beginTransaction();

        //Delete all Current Year
        CronjobProjectAllocations::where('year', $currentYear)->delete();

        $hrmFoInstance = self::getInstance();
        $hrmCommonInstance = new HrmCommon;
        $listBranches = $hrmCommonInstance->getBranches();

        while ($startYear <= $currentYear) {
            Log::debug("==========================[{$startYear}]");
            $hrmFoInstance->_insertDataCertainYear($startYear, $listBranches);
            $startYear++;
        }

        DB::commit();
    }

    private function _createInsertData(&$request, $year, $branchCode = null, $teamId = null)
    {
        $request = (object)[
            'year' => $year,
            'branch_code' => $branchCode,
            'team_id' => $teamId
        ];
        $isExist = CronjobProjectAllocations::where([
            ['year', '=', $year],
            ['branch_code', '=', $branchCode],
            ['team_id', '=', $teamId],
        ])->first();

        if (!$isExist) {
            dispatch(new QueueJobUpdateAllocation($request, $year, $branchCode, $teamId));
        }
    }

    private function _insertDataCertainYear($year, $listBranches)
    {
        Log::debug("==========================[{$year}] [][] ");
        //Case All Company
        $this->_createInsertData($request, $year);
        //Case Loop each Branch
        foreach ($listBranches as $branch) {
            //Case all team in branch
            Log::debug("==========================[{$year}] [{$branch->branch_code}] * ");
            $this->_createInsertData($request, $year, $branch->branch_code);

            //case Each team in branch
            $teams = $this->getTeamFo($request);
            foreach ($teams as $team) {
                Log::debug("==========================[{$year}] [{$branch->branch_code}] [$team->id] ");
                $this->_createInsertData($request, $year, $request->branch_code, $team->id);
            }
        }
    }
}
