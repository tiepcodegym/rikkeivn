<?php

namespace Rikkei\Api\Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Contract\Model\ContractModel as ContractModel;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\Team\Model\Certificate as CertificateModel;
use Rikkei\Team\Model\Employee as EmployeeModel;
use Rikkei\Team\Model\EmployeeCertificate as EmployeeCertificateModel;
use Rikkei\Team\Model\EmployeeEducation as EmployeeEducationModel;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\QualityEducation as QualityEducationModel;
use Rikkei\Team\Model\Team as TeamModel;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Resource\Model\RequestTeam;
use Rikkei\Resource\Model\RequestProgramming;
use Rikkei\Team\Model\Employee;

/**
 * Description of Contact
 *
 * @author duydv
 */
class HrmTotal extends HrmBase
{
    /**
     * @param $options
     * @param $teamList
     * @param null $parentId
     * @param string $char
     * @param bool $hasPrefix
     */
    protected function _genDataTeam(&$options, $teamList, $parentId = null, $char = '', $hasPrefix = true)
    {
        if (empty($teamList)) {
            return;
        }

        foreach ($teamList as $key => $team) {
            if ($team['parent_id'] == $parentId) {
                $optionItem = [
                    'label' => $char . $team['name'],
                    'value' => $team['id'],
                    'leader_id' => $team['leader_id'],
                    'is_soft_dev' => $team['is_soft_dev'],
                    'code' => $team['code'],
                    'parent_id' => $team['parent_id'],
                    'branch_code' => $team['branch_code']
                ];

                if (!$hasPrefix) {
                    $optionItem['label'] = $team->name;
                    $optionItem['prefix'] = $char;
                }
                $options[] = $optionItem;
                unset($teamList[$key]);
                $this->_genDataTeam($options, $teamList, $team['id'], $char . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $hasPrefix);
            }
        }
    }

    /**
     * @param $ownerTeamIds
     * @param $request
     * @return array
     */
    protected function _getListOption($ownerTeamIds, $request)
    {
        $options = [];
        $team = false;
        if ($request->branch_code) {
            $team = $ownerTeamIds->where('branch_code', $request->branch_code)->first();
            if (!$team) return [];
        }

        if ($team) {
            $options[] = [
                'label' => $team['name'],
                'value' => $team['id'],
                'leader_id' => $team['leader_id'],
                'is_soft_dev' => $team['is_soft_dev'],
                'code' => $team['code'],
                'parent_id' => $team['parent_id'],
                'branch_code' => $team['branch_code']
            ];
            $this->_genDataTeam($options, $ownerTeamIds, $team['id'], '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', true);
            array_shift($options);

            return $options;
        }
        $this->_genDataTeam($options, $ownerTeamIds, null, '', true);

        return $options;
    }
    
    /**
     * @param $filter
     * @return mixed
     */
    protected function _initHRByBranchSql($filter)
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $businessTripTable = BusinessTripEmployee::getTableName();
        $businessRegisterTable = BusinessTripRegister::getTableName();
        $firstOfYearClone = clone $filter['firstMonthOfYear'];
        while ($firstOfYearClone->lte($filter['lastMonthOfYear'])) {
            $currentSelectedMonth = clone $firstOfYearClone;

            $endOfCurrentSelectedMonth = $currentSelectedMonth->lastOfMonth()->min(Carbon::now())->format('Y-m-d');

            $isCurrent = $endOfCurrentSelectedMonth === Carbon::now()->format('Y-m-d');

            $selectedMonth = $currentSelectedMonth->month;

            $subqueryEmployeeOnsites = DB::table($businessTripTable)->select('employee_id',
                DB::raw("1 AS 'onsite'")
            )
                ->join($businessRegisterTable,
                    "{$businessTripTable}.register_id", '=', "{$businessRegisterTable}.id"
                )
                ->whereNull('deleted_at')
                ->whereRaw(DB::raw("DATE(date_start) <= '{$endOfCurrentSelectedMonth}'"))
                ->whereRaw(DB::raw("DATE(date_end) >= '{$endOfCurrentSelectedMonth}'"))
                ->whereNull('parent_id')
                ->whereRaw('status = ' . LeaveDayRegister::STATUS_APPROVED)
                ->groupBy('employee_id');

            $collections = DB::table($employeeTable)->select(
                DB::raw("{$selectedMonth} as 'month'"),
                DB::raw("COUNT(distinct {$employeeTable}.id) as total_employee_full"),
                DB::raw("(COUNT(distinct {$employeeTable}.id) - SUM(COALESCE(employee_onsites.onsite, 0))) AS total_employees_without_onsite"),
                DB::raw('SUM(COALESCE(employee_onsites.onsite, 0)) AS total_onsite')
            )
                ->join($teamHistory,
                    "{$teamHistory}.employee_id", '=', "{$employeeTable}.id"
                )
                ->join($teamTable,
                    "{$teamTable}.id", '=', "{$teamHistory}.team_id"
                )
                ->join(DB::raw("(select {$teamTable}.branch_code, {$teamTable}.name from $teamTable where is_branch = 1 ) as branches"),
                    function ($join) use ($teamTable) {
                        $join->on('branches.branch_code', '=', "{$teamTable}.branch_code");
                    }
                )
                ->leftjoin(DB::raw('(' . $subqueryEmployeeOnsites->toSql() . ') AS employee_onsites'
                ),
                    function ($join) use ($employeeTable) {
                        $join->on('employee_onsites.employee_id', '=', "{$employeeTable}.id");
                    }
                )
                ->whereNull("{$employeeTable}.deleted_at")
                ->whereNull("{$teamHistory}.deleted_at")
                ->whereNotNull("{$teamTable}.branch_code")
                ->where(function ($query1) use ($teamHistory, $endOfCurrentSelectedMonth) {
                    $query1->where("{$teamHistory}.end_at", '>=', $endOfCurrentSelectedMonth)
                        ->orWhereNull("{$teamHistory}.end_at");
                })
                ->where(function ($query1) use ($employeeTable, $endOfCurrentSelectedMonth) {
                    $query1->where("{$employeeTable}.leave_date", '>=', $endOfCurrentSelectedMonth)
                        ->orWhereNull("{$employeeTable}.leave_date");
                })
                ->where(function ($query1) use ($teamHistory, $endOfCurrentSelectedMonth) {
                    $query1->where("{$teamHistory}.start_at", '<=', $endOfCurrentSelectedMonth)
                        ->orWhereNull("{$teamHistory}.start_at");
                })
                ->where(function ($query1) use ($employeeTable, $endOfCurrentSelectedMonth) {
                    $query1->where("{$employeeTable}.join_date", '<=', $endOfCurrentSelectedMonth)
                        ->orWhereNull("{$employeeTable}.join_date");
                })
                ->where("{$teamTable}.branch_code", '!=', '');

            if ($isCurrent) $collections->where("{$teamHistory}.is_working", 1);

            if ($filter['branch_code'] && $filter['branch_code'] != '') {
                $collections->where("{$teamTable}.branch_code", $filter['branch_code']);
            }

            // set hrmTotal la subquery dau tien
            if (!isset($query)) {
                $query = $collections;
            } else if (!$firstOfYearClone->eq($filter['firstMonthOfYear'])) {
                $query = $query->union($collections);
            }
            // tang thang len 1
            $firstOfYearClone = $firstOfYearClone->addMonth();
        }
        if (isset($query)) return $query->get();
        return null;
    }

    /**
     * @param $filter
     * @return mixed
     */
    protected function _initDivisionPopupSql($filter)
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $businessTripTable = BusinessTripEmployee::getTableName();
        $businessRegisterTable = BusinessTripRegister::getTableName();
        $firstOfYearClone = clone $filter['firstMonthOfYear'];
        while ($firstOfYearClone->lte( $filter['lastMonthOfYear'])) {
            $currentSelectedMonth = clone $firstOfYearClone;

            $endOfCurrentSelectedMonth = $currentSelectedMonth->lastOfMonth()->min(Carbon::now())->format('Y-m-d');
            $selectedMonth = $currentSelectedMonth->month;
            $isCurrent = $endOfCurrentSelectedMonth === Carbon::now()->format('Y-m-d');

            $subqueryEmployeeOnsites = DB::table($businessTripTable)->select('employee_id',
                DB::raw("1 AS 'onsite'")
            )
                ->join($businessRegisterTable,
                    "{$businessTripTable}.register_id", '=', "{$businessRegisterTable}.id"
                )
                ->whereNull('deleted_at')
                ->whereRaw(DB::raw("DATE(start_at) <= '{$endOfCurrentSelectedMonth}'"))
                ->whereRaw(DB::raw("DATE(end_at) >= '{$endOfCurrentSelectedMonth}'"))
                ->whereNull('parent_id')
                ->whereRaw('status = ' . LeaveDayRegister::STATUS_APPROVED)
                ->groupBy('employee_id');

            $sql  = DB::table($employeeTable)->select(DB::raw("'{$selectedMonth}' as 'month'"),
                DB::raw("COUNT(distinct {$employeeTable}.id) as total_employee_full"),
                DB::raw("(COUNT(distinct {$employeeTable}.id) - SUM(COALESCE(employee_onsites.onsite, 0))) 
                                                                        AS total_employees_without_onsite"),
                DB::raw("SUM(COALESCE(employee_onsites.onsite, 0)) AS total_onsite"))
                ->join($teamHistory,
                    "{$employeeTable}.id", '=', "{$teamHistory}.employee_id"
                )
                ->join($teamTable,
                    "{$teamTable}.id", '=', "{$teamHistory}.team_id"
                )
                ->join(DB::raw("(select {$teamTable}.branch_code, {$teamTable}.name from $teamTable where is_branch = 1 ) as branches"),
                    function ($join) use ($teamTable) {
                        $join->on('branches.branch_code', '=', "{$teamTable}.branch_code");
                    })
                ->leftjoin(DB::raw('(' . $subqueryEmployeeOnsites->toSql() . ') AS employee_onsites'),
                    function ($join) use ($employeeTable) {
                        $join->on('employee_onsites.employee_id', '=', "{$employeeTable}.id");
                    })
                ->whereNull("{$employeeTable}.deleted_at")
                ->whereNull("{$teamHistory}.deleted_at")
                ->whereNotNull("{$teamTable}.branch_code")
                ->where(function ($query1) use ($teamHistory, $endOfCurrentSelectedMonth) {
                    $query1->where("{$teamHistory}.end_at", '>=', $endOfCurrentSelectedMonth)
                        ->orWhereNull("{$teamHistory}.end_at");
                })
                ->where(function ($query1) use ($employeeTable, $endOfCurrentSelectedMonth) {
                    $query1->where("{$employeeTable}.leave_date", '>=', $endOfCurrentSelectedMonth)
                        ->orWhereNull("{$employeeTable}.leave_date");
                })
                ->where(function ($query1) use ($teamHistory, $endOfCurrentSelectedMonth) {
                    $query1->where("{$teamHistory}.start_at", '<=', $endOfCurrentSelectedMonth)
                        ->orWhereNull("{$teamHistory}.start_at");
                })
                ->where(function ($query1) use ($employeeTable, $endOfCurrentSelectedMonth) {
                    $query1->where("{$employeeTable}.join_date", '<=', $endOfCurrentSelectedMonth)
                        ->orWhereNull("{$employeeTable}.join_date");
                });

            $sql = $isCurrent ? $sql->where("{$teamHistory}.is_working", 1) : $sql;

            if ($filter['team_id']) {
                $sql->where("{$teamTable}.id", $filter['team_id']);
            }

            if (!isset($collections)) {
                $collections = $sql;
            } else if (!$firstOfYearClone->eq($filter['firstMonthOfYear'])) {
                $collections = $collections->union($sql);
            }
            $firstOfYearClone = $firstOfYearClone->addMonth();
        }
        if (isset($collections)) return $collections->get();
        return null;
    }

    /**
     * @param $date
     * @return mixed
     */
    protected function _initSelectedRangeAgeSql($date)
    {

        $sql = '(CASE
        WHEN
            TIMESTAMPDIFF(YEAR, DATE(birthday), "'.$date.'") < 23 THEN "1"
        WHEN
            TIMESTAMPDIFF(YEAR, DATE(birthday), "'.$date.'") <= 25 THEN "2"
        WHEN
            TIMESTAMPDIFF(YEAR, DATE(birthday), "'.$date.'") <= 30 THEN "3"
        WHEN
            TIMESTAMPDIFF(YEAR, DATE(birthday), "'.$date.'") <= 35 THEN "4"';
        $sql .= " ELSE '5' END) AS 'order_tuoi', ";

        $sql .= '(CASE
        WHEN
           TIMESTAMPDIFF(YEAR, DATE(birthday), "'.$date.'") < 23 THEN "<23"
        WHEN
           TIMESTAMPDIFF(YEAR, DATE(birthday), "'.$date.'") <= 25 THEN "23-25"
        WHEN
           TIMESTAMPDIFF(YEAR, DATE(birthday), "'.$date.'") <= 30 THEN "26-30"
        WHEN
           TIMESTAMPDIFF(YEAR, DATE(birthday), "'.$date.'") <= 35 THEN "31-35"';
        $sql .= " ELSE '>35' END) AS 'tuoi'";

        return DB::raw($sql);
    }

    /**
     * @param $collections
     * @param $request
     * @return mixed
     */
    protected function _getAgeGendersFrom($collections, $request) {
        $date = $request->date;
        $employeeTbl = EmployeeModel::getTableName();
        $teamTbl = TeamModel::getTableName();
        $employeeTeamHistoryTbl = EmployeeTeamHistory::getTableName();

        $collections = $collections
            ->join($employeeTeamHistoryTbl, "{$employeeTbl}.id", '=', "{$employeeTeamHistoryTbl}.employee_id")
            ->join($teamTbl, "{$teamTbl}.id", '=', "{$employeeTeamHistoryTbl}.team_id")
            ->whereNull("{$employeeTbl}.deleted_at")
            ->whereNotNull("{$employeeTbl}.birthday")
            ->whereDate("{$employeeTbl}.birthday", '<=', $date)
            ->where(function ($query) use ($employeeTbl, $date) {
                $query->whereNull("{$employeeTbl}.leave_date")
                    ->orWhereRaw(DB::raw("DATE({$employeeTbl}.leave_date) >= '{$date}'") );
            })
            ->where(function ($query) use ($employeeTeamHistoryTbl, $date) {
                $query->whereNull("{$employeeTeamHistoryTbl}.start_at")
                    ->orWhereRaw(DB::raw("DATE({$employeeTeamHistoryTbl}.start_at) <= '{$date}'") );
            })
            ->where(function ($query) use ($employeeTeamHistoryTbl, $date) {
                $query->whereNull("{$employeeTeamHistoryTbl}.end_at")
                    ->orWhereRaw(DB::raw("DATE({$employeeTeamHistoryTbl}.end_at) >= '{$date}'") );
            });
        if ($request->branch_code) {
            $collections->where("{$teamTbl}.branch_code", "=", $request->branch_code);
        }
        if ($request->team_id) {
            $teamIds = TeamModel::teamChildIds($request->team_id);
            $collections->whereIn("{$teamTbl}.id", $teamIds);
        }

        return $collections->groupBy("{$employeeTbl}.id");
    }

    /**
     * @param $request
     * @return array
     */
    protected function _initDivisionPopupFilter($request)
    {
        $firstMonthOfYear = Carbon::createFromFormat('Y', $request->date)->firstOfYear();
        $lastMonthOfYear = Carbon::createFromFormat('Y', $request->date)->lastOfYear()->min(Carbon::now());

        return [
            'firstMonthOfYear' => $firstMonthOfYear,
            'lastMonthOfYear' => $lastMonthOfYear,
            'team_id' => $request->team_id,
        ];
    }

    /**
     * @param $request
     * @return array
     */
    protected function _initHRByBranchFilter($request)
    {
        $firstMonthOfYear = Carbon::createFromFormat('Y', $request->year)->firstOfYear();
        $lastMonthOfYear = Carbon::createFromFormat('Y', $request->year)->lastOfYear()->min(Carbon::now());

        return [
            'firstMonthOfYear' => $firstMonthOfYear,
            'lastMonthOfYear' => $lastMonthOfYear,
            'branch_code' => $request->branch_code,
            'team_id' => $request->team_id,
        ];
    }

    /**
     * Get toàn bộ team
     *
     * @param $request
     * @return array
     */
    public function getAllTeams($request)
    {
        $selectedFields = ['name', 'id', 'leader_id', 'is_soft_dev', 'code', 'parent_id', 'is_function', 'branch_code', 'is_branch'];
        $collections = TeamModel::select($selectedFields)->orderBy('is_branch', 'desc')->orderBy('sort_order', 'asc');
        if ($request->branch_code) {
            $collections = $collections->where('branch_code', $request->branch_code);
        }

        $ownerTeamIds = $collections->get();

        return $this->_getListOption($ownerTeamIds, $request);
    }

    /**
     * Danh sách requests tuyển dụng
     * 
     * @param $request
     * @return mixed
     */
    public function getTotalRequest($request)
    {
        $updated_from = $request->updated_from;
        $updated_to = $request->updated_to;
        $collections = DB::table('requests')
        ->select('requests.id', 
            'requests.title as name',
            'requests.skill',
            'requests.request_date',
            'request_team.team_id',
            'request_team.position_apply',
            'request_team.number_resource', 
            'teams.branch_code as branch', 
            DB::raw("GROUP_CONCAT(DISTINCT request_type.type SEPARATOR ',') AS experience"),
            'requests.start_working',
            'requests.deadline',
            DB::raw("DATE(requests.created_at) as created_at"),
            DB::raw("DATE(requests.updated_at) as updated_at"),
            DB::raw("SUBSTRING_INDEX(requests.recruiter, '@', 1) as recruiter"),
            'interviewer as interviewer',
            'requests.status', 'requests.description', 'requests.benefits', 'requests.job_qualifi as job_qualify', 'requests.note',
            DB::raw("GROUP_CONCAT(DISTINCT request_programming.programming_id SEPARATOR ',') AS programming_languages"),
            'request_priority.id as priority_id',
            'request_priority.name as priority_text'
        );
        if ($updated_from) {
            $collections = $collections->whereDate('requests.updated_at', '>=', $updated_from);
        }
        if ($updated_to) {
            $collections = $collections->whereDate('requests.updated_at', '<=', $updated_to);
        }
        $collections = $collections->leftJoin('request_team', 'request_team.request_id', '=', "requests.id")
        ->leftJoin('teams', 'request_team.team_id', '=', "teams.id")
        ->leftJoin('request_programming', 'request_programming.request_id', '=', "requests.id")
        ->leftJoin('request_priority', 'request_priority.id', '=', "requests.priority_id")
        ->leftJoin('request_type', 'request_type.request_id', '=', "requests.id")
        ->groupBy("requests.id")
        ->get();

        foreach ($collections as $key => $value) {
            $value->programming_languages = explode(',', $value->programming_languages);
            $interviewers = explode(',', $value->interviewer);
            if (count($interviewers)) {
                $arrEmail = [];
                foreach ($interviewers as $key => $interviewer) {
                    $employee = Employee::where('id', $interviewer)->first(['id', 'email']);
                    if ($employee) {
                        $arrEmail[] = $employee->email ? explode("@", $employee->email)[0] : '';
                    }
                }
            }
            $value->interviewer = $arrEmail;
        }

        return $collections;
    }

    /**
     * Tổng nhân viên mỗi chi nhánh
     * 
     * @return mixed
     */
    public function getTotalEmployeesEachBranch()
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $businessTripTable = BusinessTripEmployee::getTableName();
        $businessRegisterTable = BusinessTripRegister::getTableName();

        $dateFormat = '%Y-%m';
        $date = Carbon::now()->format('Y-m-d');

        $subqueryEmployeeOnsites = DB::table($businessTripTable)->select('employee_id',
            DB::raw("1 AS 'onsite'")
        )
            ->join($businessRegisterTable,
                "{$businessTripTable}.register_id", '=', "{$businessRegisterTable}.id"
            )
            ->whereNull('deleted_at')
            ->whereRaw(DB::raw("DATE(date_start) <= '{$date}'"))
            ->whereRaw(DB::raw("DATE(date_end) >= '{$date}'"))
            ->whereNull('parent_id')
            ->whereRaw('status = ' . LeaveDayRegister::STATUS_APPROVED)
            ->groupBy('employee_id');

        $collections = DB::table($employeeTable)->select("{$teamTable}.branch_code",
            'branches.name',
            DB::raw("COUNT(distinct {$employeeTable}.id) as total_employee_full"),
            DB::raw("(COUNT(distinct {$employeeTable}.id) - SUM(COALESCE(employee_onsites.onsite, 0))) AS total_employees_without_onsite"),
            DB::raw('SUM(COALESCE(employee_onsites.onsite, 0)) AS total_onsite')
        )
            ->join($teamHistory,
                "{$teamHistory}.employee_id", '=', "{$employeeTable}.id"
            )
            ->join($teamTable,
                "{$teamTable}.id", '=', "{$teamHistory}.team_id"
            )
            ->join(DB::raw("(select {$teamTable}.branch_code, {$teamTable}.name from $teamTable where is_branch = 1 ) as branches"),
                function ($join) use ($teamTable) {
                    $join->on('branches.branch_code', '=', "{$teamTable}.branch_code");
                }
            )
            ->leftjoin(DB::raw('(' . $subqueryEmployeeOnsites->toSql() . ') AS employee_onsites'
            ),
                function ($join) use ($employeeTable) {
                    $join->on('employee_onsites.employee_id', '=', "{$employeeTable}.id");
                }
            )
            ->whereNull("{$employeeTable}.deleted_at")
            ->whereNull("{$teamHistory}.deleted_at")
            ->whereNotNull("{$teamTable}.branch_code")
            ->where(function ($query1) use ($teamHistory, $date) {
                $query1->where("{$teamHistory}.end_at", '>=', $date)
                    ->orWhereNull("{$teamHistory}.end_at");
            })
            ->where(function ($query1) use ($employeeTable, $date) {
                $query1->where("{$employeeTable}.leave_date", '>=', $date)
                    ->orWhereNull("{$employeeTable}.leave_date");
            })
            ->where(function ($query1) use ($teamHistory, $date) {
                $query1->where("{$teamHistory}.start_at", '<=', $date)
                    ->orWhereNull("{$teamHistory}.start_at");
            })
            ->where(function ($query1) use ($employeeTable, $date) {
                $query1->where("{$employeeTable}.join_date", '<=', $date)
                    ->orWhereNull("{$employeeTable}.join_date");
            })
            ->where("{$teamHistory}.is_working", '=', '1')

            ->where("{$teamTable}.branch_code", '!=', '')
            ->groupBy("{$teamTable}.branch_code");

        return $collections->get();
    }
    
    /**
     * Tổng nhân viên Rikkei FO
     *
     * @return mixed
     */
    public function getTotalEmployees()
    {
        $employeeTable = EmployeeModel::getTableName();
        $date = Carbon::now()->format('Y-m-d');

        $collections = DB::table($employeeTable)->select(DB::raw('COUNT(id) AS total_employees'))
                                                ->whereNull('deleted_at')
                                                ->where(function ($q) use ($date) {
                                                    $q->whereNull('leave_date')
                                                        ->orWhereDate('leave_date', '>=', "{$date}");
                                                });

        return $collections->get();
    }

    /**
     * Tổng nhân viên Rikkei BO
     *
     * @return mixed
     */
    public function getTotalEmpFOAndBO()
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();

        $date = Carbon::now()->format('Y-m-d');

        $collections = DB::table($employeeTable)->select(DB::raw("(CASE WHEN {$teamTable}.is_bo = 1 THEN 'BO' 
                                                                    WHEN {$teamTable}.is_soft_dev = 1 THEN 'FO' 
                                                                    END) AS type_employee"),
                                                        DB::raw('COUNT(distinct(employees.id)) AS total'))
                                                ->join($teamHistory,
                                                        "{$teamHistory}.employee_id", '=', "{$employeeTable}.id"
                                                    )
                                                ->join($teamTable,
                                                        "{$teamTable}.id", '=', "{$teamHistory}.team_id"
                                                    )
                                                ->where(function ($q) use ($date) {
                                                    $q->whereNull('leave_date')
                                                        ->orWhereDate('leave_date', '>=', $date);
                                                    })
                                                ->where("{$teamHistory}.is_working", '=', 1)
                                                ->where(function ($q) {
                                                    $q->where('is_bo', '=', 1)
                                                        ->orWhere('is_soft_dev', '=', 1);
                                                    })
                                                ->whereNull("{$employeeTable}.deleted_at")
                                                ->whereNull("{$teamHistory}.deleted_at")
                                                ->groupBy('type_employee');

         return $collections->get();
    }

    /**
     * Tổng nhân viên môi division
     * 
     * @param $request
     * @return mixed
     */
    public function getTotalEmployeesDivision($request)
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();

        $date = Carbon::now()->format('Y-m-d');

        $collections = DB::table($employeeTable)->select(
            "{$teamTable}.id",
            "{$teamTable}.name",
            DB::raw("(COUNT(distinct {$employeeTable}.id)) AS total_employees"))
            ->join($teamHistory, "{$teamHistory}.employee_id", '=', "{$employeeTable}.id")
            ->join($teamTable, "{$teamTable}.id", '=', "{$teamHistory}.team_id")
            ->join(DB::raw("(select {$teamTable}.branch_code, {$teamTable}.name from $teamTable where is_branch = 1 ) as branches"),
                function ($join) use ($teamTable) {
                    $join->on('branches.branch_code', '=', "{$teamTable}.branch_code");
                })
            ->whereNull("{$employeeTable}.deleted_at")
            ->where("{$teamHistory}.is_working", '=', '1')
            ->whereNull("{$teamHistory}.deleted_at")
            ->where(function ($query1) use ($teamHistory, $date) {
                $query1->where("{$teamHistory}.end_at", '>=', $date)
                    ->orWhereNull("{$teamHistory}.end_at");
            })
            ->where(function ($query1) use ($employeeTable, $date) {
                $query1->where("{$employeeTable}.leave_date", '>=', $date)
                    ->orWhereNull("{$employeeTable}.leave_date");
            })
            ->where(function ($query1) use ($teamHistory, $date) {
                $query1->where("{$teamHistory}.start_at", '<=', $date)
                    ->orWhereNull("{$teamHistory}.start_at");
            })
            ->where(function ($query1) use ($employeeTable, $date) {
                $query1->where("{$employeeTable}.join_date", '<=', $date)
                    ->orWhereNull("{$employeeTable}.join_date");
            })
            ->where("{$teamTable}.branch_code", $request->branch_code)
            ->groupBy("{$teamTable}.id");

        return $collections->get();
    }
    
    /**
     * Thống kê hợp đồng
     *
     * @param $request
     * @return mixed
     */
    public function getContractType($request)
    {

        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $contractTable = ContractModel::getTableName();

        $date = $request->date;

        $collections = DB::table($employeeTable)->select(DB::raw('count(*) as total'),
                                                         DB::raw("(
                                                            CASE 
                                                                WHEN {$contractTable}.type = 1 THEN 'Thử việc'
                                                                WHEN {$contractTable}.type = 4 THEN 'Xác định thời hạn'
                                                                WHEN {$contractTable}.type = 5 THEN 'Không xác định thời hạn'
                                                                WHEN {$contractTable}.type = 2 THEN 'Học việc'
                                                                WHEN {$contractTable}.type = 3 THEN 'Mùa vụ'
                                                                WHEN {$contractTable}.type = 6 THEN 'Thuê ngoài'
                                                                ELSE 'Chưa có thông tin HĐ'
                                                            END
                                                            ) as contract_type"),
                                                        DB::raw("(
                                                            CASE 
                                                                WHEN {$contractTable}.type = 1 THEN 1
                                                                WHEN {$contractTable}.type = 4 THEN 2
                                                                WHEN {$contractTable}.type = 5 THEN 3
                                                                WHEN {$contractTable}.type = 2 THEN 4
                                                                WHEN {$contractTable}.type = 3 THEN 5
                                                                WHEN {$contractTable}.type = 6 THEN 6
                                                                ELSE 7
                                                            END
                                                            ) as contract_type_order")
                                                    )
                                                ->leftJoin($contractTable,
                                                    "{$contractTable}.employee_id", '=', "{$employeeTable}.id"
                                                )
                                                ->join($teamHistory,
                                                        "{$employeeTable}.id", '=', "{$teamHistory}.employee_id"
                                                    )
                                                ->join($teamTable,
                                                        "{$teamTable}.id", '=', "{$teamHistory}.team_id"
                                                    )

                                                ->whereNull("{$employeeTable}.deleted_at")
                                                ->where(function ($q) use ($employeeTable, $date) {
                                                    $q->whereNull("{$employeeTable}.leave_date")
                                                        ->orWhereDate("{$employeeTable}.leave_date", '>=', $date);
                                                    })
                                                ->where(function ($q) use ($contractTable, $date) {
                                                    $q->whereNull("{$contractTable}.end_at")
                                                        ->orWhereDate("{$contractTable}.end_at", '>=', $date);
                                                    })
                                                ->where(function ($q) use ($contractTable, $date) {
                                                    $q->whereNull("{$contractTable}.start_at")
                                                        ->orWhereDate("{$contractTable}.start_at", '<=', $date);
                                                })
                                                ->where(function ($q) use ($teamHistory, $date) {
                                                    $q->whereNull("{$teamHistory}.start_at")
                                                        ->orWhereDate("{$teamHistory}.start_at", '<=', $date);
                                                    })
                                                ->where(function ($q) use ($teamHistory, $date) {
                                                    $q->whereNull("{$teamHistory}.end_at")
                                                        ->orWhereDate("{$teamHistory}.end_at", '>=', $date);
                                                    });

        if ($request->branch_code) {
            $collections->where("{$teamTable}.branch_code", '=', $request->branch_code);
        }

        if ($request->team_id) {
            $teamIds = TeamModel::teamChildIds($request->team_id);
            $collections->whereIn("{$teamTable}.id", $teamIds);
        }

        return $collections->groupBy('contract_type')->orderBy('contract_type_order', 'ASC')->get();
    }

    /**
     * Thống kê tuổi
     * 
     * @param $request
     * @return mixed
     */
    public function getAgeGenders($request)
    {
        $date = $request->date;
        $employeeTbl = EmployeeModel::getTableName();

        $selectedType = $this->_initSelectedRangeAgeSql($date);
        $selectedCount = DB::raw("(COUNT(*) - SUM(gender)) AS 'female',
                                SUM(gender) AS 'male'");

        $totalAgeGendersSQLFields = [
            "{$employeeTbl}.id",
            "{$employeeTbl}.gender",
            "{$employeeTbl}.birthday"
        ];

        $collections = EmployeeModel::select($totalAgeGendersSQLFields);

        $collections = $this->_getAgeGendersFrom($collections, $request);

        $result = DB::table(DB::raw("({$collections->toSql()}) as temp"))
            ->mergeBindings($collections->getQuery())
            ->select($selectedType,
                    $selectedCount)
            ->groupBy('tuoi')
            ->orderBy('order_tuoi', 'desc')
            ->get();

        return $result;
    }
    
    /**
     * Thống kê thâm niên
     *
     * @param $request
     * @return mixed
     */
    public function getSeniorities($request)
    {
        $date = $request->date;
        $employeeTbl = EmployeeModel::getTableName();
        $teamTbl = TeamModel::getTableName();
        $employeeTeamHistoryTbl = EmployeeTeamHistory::getTableName();

        $collections =
            EmployeeModel::join($employeeTeamHistoryTbl, "{$employeeTbl}.id", '=', "{$employeeTeamHistoryTbl}.employee_id")
                ->join($teamTbl, "{$teamTbl}.id", '=', "{$employeeTeamHistoryTbl}.team_id")
                ->whereNull("{$employeeTbl}.deleted_at")
                ->whereNotNull("{$employeeTbl}.join_date")
                ->whereDate("{$employeeTbl}.join_date", '<=', $date)
                ->where(function ($query) use ($employeeTbl, $date) {
                    $query->whereNull("{$employeeTbl}.leave_date")
                        ->orWhereRaw(DB::raw("DATE({$employeeTbl}.leave_date) >= '{$date}'") );
                })
                ->where(function ($query) use ($employeeTeamHistoryTbl, $date) {
                    $query->whereNull("{$employeeTeamHistoryTbl}.start_at")
                        ->orWhereRaw(DB::raw("DATE({$employeeTeamHistoryTbl}.start_at) <= '{$date}'") );
                })
                ->where(function ($query) use ($employeeTeamHistoryTbl, $date) {
                    $query->whereNull("{$employeeTeamHistoryTbl}.end_at")
                        ->orWhereRaw(DB::raw("DATE({$employeeTeamHistoryTbl}.end_at) >= '{$date}'") );
                });
        if ($request->branch_code) {
            $collections->where("{$teamTbl}.branch_code", "=", $request->branch_code);
        }
        if ($request->team_id) {
            $teamIds = TeamModel::teamChildIds($request->team_id);
            $collections->whereIn("{$teamTbl}.id", $teamIds);
        }

        $sql = 'CASE WHEN TIMESTAMPDIFF(month, date(employees.join_date), "'.$date.'") < 6 then "0" 
                        WHEN TIMESTAMPDIFF(year, date(employees.join_date), "'.$date.'") <= 1 then "1" 
                        WHEN TIMESTAMPDIFF(year, date(employees.join_date), "'.$date.'") <= 3 then "2"
                        WHEN TIMESTAMPDIFF(year, date(employees.join_date), "'.$date.'") <= 5 then "3"
                        WHEN TIMESTAMPDIFF(year, date(employees.join_date), "'.$date.'") <= 10 then "4"';
        $sql .= " ELSE '5' END AS tham_nien";

        $collections->select(DB::raw($sql)
            , DB::raw('count(distinct(employees.id)) as total') );
        $collections->groupBy('tham_nien');
        $result = $collections->get();
        return $result;
    }

    /**
     * Thống kê trình độ học vấn
     *
     * @param $request
     * @return mixed
     */
    public function getEducations($request)
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $qualityEducationTable = QualityEducationModel::getTableName();
        $employeeEducationTable = EmployeeEducationModel::getTableName();

        $date = $request->date;

        $collections = DB::table($employeeTable)
            ->select(DB::raw("(CASE
                                WHEN {$qualityEducationTable}.id in (4, 5) THEN 'Trên đại học'
                                WHEN {$qualityEducationTable}.id = 1 THEN 'Đại học'
                                WHEN {$qualityEducationTable}.id in (2, 10) THEN 'Cao đẳng'
                                WHEN {$qualityEducationTable}.id = 3 THEN 'Trung cấp'
                                ELSE 'Khác'
                               END
                            ) as 'education_name'"),
                DB::raw("(CASE
                                WHEN {$qualityEducationTable}.id in (4, 5) THEN 1
                                WHEN {$qualityEducationTable}.id = 1 THEN 2
                                WHEN {$qualityEducationTable}.id in (2, 10) THEN 3
                                WHEN {$qualityEducationTable}.id = 3 THEN 4
                                ELSE 5
                               END
                            ) as 'education_name_order'"),
                DB::raw("count(distinct {$employeeTable}.id) as total")
            )
            ->join($teamHistory,
                "{$employeeTable}.id", '=', "{$teamHistory}.employee_id"
            )
            ->join($teamTable,
                "{$teamTable}.id", '=', "{$teamHistory}.team_id"
            )
            ->join($employeeEducationTable,
                "{$employeeEducationTable}.employee_id", '=', "{$employeeTable}.id"
            )
            ->join($qualityEducationTable,
                "{$employeeEducationTable}.quality", '=', "{$qualityEducationTable}.id"
            )
            ->whereNull("{$employeeTable}.deleted_at")
            ->whereNotNull("{$employeeEducationTable}.quality")
            ->where(function ($q) use ($employeeTable, $date) {
                $q->whereNull("{$employeeTable}.leave_date")
                    ->orWhereDate("{$employeeTable}.leave_date", '>=', $date);
            })
            ->where(function ($q) use ($teamHistory, $date) {
                $q->whereNull("{$teamHistory}.start_at")
                    ->orWhereDate("{$teamHistory}.start_at", '<=', $date);
            })
            ->where(function ($q) use ($teamHistory, $date) {
                $q->whereNull("{$teamHistory}.end_at")
                    ->orWhereDate("{$teamHistory}.end_at", '>=', $date);
            })
            ->orderBy('education_name_order', 'ASC');

        if ($request->branch_code) {
            $collections->where("{$teamTable}.branch_code", '=', $request->branch_code);
        }

        //TODO: Generate Child teams later
        if ($request->team_id) {
            $teamIds = TeamModel::teamChildIds($request->team_id);
            $collections->whereIn("{$teamTable}.id", $teamIds);
        }

        return $collections->groupBy('education_name')->get();
    }

    /**
     * Thống kê chứng chỉ
     *
     * @param $request
     * @return mixed
     */
    public function getCertificates($request)
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $employeeCertificateTable = EmployeeCertificateModel::getTableName();
        $certificateTable = CertificateModel::getTableName();

        $date = $request->date;

        $collections = DB::table($employeeTable)
            ->select("{$certificateTable}.name",
                DB::raw("count(distinct {$employeeTable}.id) as total")
            )
            ->join($teamHistory,
                "{$employeeTable}.id", '=', "{$teamHistory}.employee_id"
            )
            ->join($teamTable,
                "{$teamTable}.id", '=', "{$teamHistory}.team_id"
            )
            ->join($employeeCertificateTable,
                "{$employeeCertificateTable}.employee_id", '=', "{$employeeTable}.id"
            )
            ->join($certificateTable,
                "{$certificateTable}.id", '=', "{$employeeCertificateTable}.certificate_id"
            )
            ->whereNull("{$employeeTable}.deleted_at")
            ->where(function ($q) use ($employeeTable, $date) {
                $q->whereNull("{$employeeTable}.leave_date")
                    ->orWhereDate("{$employeeTable}.leave_date", '>=', $date);
            })
            ->where(function ($q) use ($teamHistory, $date) {
                $q->whereNull("{$teamHistory}.start_at")
                    ->orWhereDate("{$teamHistory}.start_at", '<=', $date);
            })
            ->where(function ($q) use ($teamHistory, $date) {
                $q->whereNull("{$teamHistory}.end_at")
                    ->orWhereDate("{$teamHistory}.end_at", '>=', $date);
            });

        if ($request->branch_code) {
            $collections->where("{$teamTable}.branch_code", '=', $request->branch_code);
        }

        //TODO: Generate Child teams later
        if ($request->team_id) {
            $teamIds = TeamModel::teamChildIds($request->team_id);
            $collections->whereIn("{$teamTable}.id", $teamIds);
        }

        return $collections->groupBy("{$certificateTable}.id")->get();
    }

    /**
     * Tổng nhân viên môi division POPUP
     *
     * @param $request
     * @return mixed
     */
    public function getTotalDivisionPopup($request)
    {
        $filter = $this->_initDivisionPopupFilter($request);

        return $this->_initDivisionPopupSql($filter);

    }

    /**
     * Tổng nhân viên mỗi chi nhánh POPUP
     *
     * @param $request
     * @return mixed
     */
    public function getHRByBranch($request)
    {
        $filter = $this->_initHRByBranchFilter($request);

        return $this->_initHRByBranchSql($filter);
    }

    /**
     * Tổng nhân viên mỗi chi nhánh
     *
     * @return mixed
     */
    public function getTotalCandidates($request)
    {
        $updated_from = $request->updated_from;
        $updated_to = $request->updated_to;
        $plan = 'interview_plan';
        $plan2 = 'interview2_plan';
        $collections = DB::table('candidates')
            ->select([
                'candidates.id',
                'candidates.fullname as name',
                'candidates.email',
                'candidates.mobile',
                'candidates.birthday',
                'candidates.gender',
                'candidates.skype',
                'candidates.other_contact',
                'candidates.experience',
                'candidates.university',
                'candidates.certificate',
                'candidates.old_company as old_companies',
                'candidates.contact_result as is_pass_cv',
                'candidates.interview_result as is_pass_interview',
                'candidates.offer_result as is_pass_offer',
                'candidates.working_type as contract_type',
                'employees.email as email_rikkei',
                DB::raw("(CASE WHEN candidates.working_type = 2 THEN DATE(candidates.trainee_start_date) ELSE null END) as trainee_date"),
                DB::raw("DATE(candidates.trial_work_start_date) as trial_date"),
                DB::raw("(CASE WHEN candidates.working_type = 1 THEN DATE(candidates.official_date) WHEN candidates.working_type in (4,5) THEN DATE(candidates.start_working_date) ELSE null END) as offical_date"),
                DB::raw("GROUP_CONCAT(DISTINCT candidate_lang.lang_id SEPARATOR ',') AS arr_lang_id"),
                DB::raw("GROUP_CONCAT(DISTINCT candidate_lang.lang_level_id SEPARATOR ',') AS arr_level_id"),
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT(candidate_comments.content, '^', emp_comment.email, '^', candidate_comments.created_at) separator '^^^') as arr_comment"),
                DB::raw("GROUP_CONCAT(DISTINCT candidate_programming.programming_id SEPARATOR ',') AS arr_programming_id"),
                DB::raw('(case when `'.$plan2.'` is not null and `'.$plan2.'` != \'0000-00-00 00:00:00\' then DATE(`'.$plan2.'`) else DATE(`'.$plan.'`) end) interview_pass_date'),
                DB::raw('DATE(`offer_date`) as offer_pass_date'),
                DB::raw('DATE(`received_cv_date`) as cv_pass_date'),
                'candidates.created_at',
                'candidates.updated_at',
                'candidates.deleted_at',
                'candidates.request_id as request_id_offer',
                DB::raw("GROUP_CONCAT(DISTINCT candidate_request.request_id SEPARATOR ',') AS request_id"),
                'candidates.received_cv_date',
                'candidates.type',
                'candidates.interested',
                'emp_found.email as found_by',
                'candidates.channel_id',
                'recruit_channel.name as channel_text',
                'candidates.screening as note',
                'candidates.status_update_date',
                'candidates.recruiter',
                'candidates.status',
            ]);
        $collections = $collections->whereNull("candidates.deleted_at");
        if ($updated_from) {
            $collections = $collections->whereDate('candidates.updated_at', '>=', $updated_from);
        }
        if ($updated_to) {
            $collections = $collections->whereDate('candidates.updated_at', '<=', $updated_to);
        }
        $collections = $collections->leftjoin('employees', 'employees.id', '=', "candidates.employee_id")
            ->leftjoin('candidate_lang', 'candidate_lang.candidate_id', '=', "candidates.id")
            ->leftjoin('candidate_programming', 'candidate_programming.candidate_id', '=', "candidates.id")
            ->leftjoin('candidate_request', 'candidate_request.candidate_id', '=', "candidates.id")
            ->leftjoin('candidate_comments', 'candidate_comments.candidate_id', '=', "candidates.id")
            ->leftjoin('employees as emp_found', 'emp_found.id', '=', "candidates.found_by")
            ->leftjoin('employees as emp_comment', 'emp_comment.id', '=', "candidate_comments.created_by")
            ->leftjoin("employees as emp_interview", function($join) {
                $join->on(DB::raw("find_in_set(emp_interview.id, candidates.interviewer)"), ">", DB::raw("0"));
            })
            ->leftjoin('recruit_channel', 'recruit_channel.id', '=', "candidates.channel_id")
            ->addSelect(DB::raw("GROUP_CONCAT(DISTINCT LEFT(emp_interview.email, locate('@', emp_interview.email) - 1) SEPARATOR ',') AS interviewers"))
            ->groupBy("candidates.id")
            ->get();

        foreach ($collections as $k => $item) {
            $langIds = explode(',', $item->arr_lang_id);
            $levelIds = explode(',', $item->arr_level_id);
            $foreignLanguage = [];
            if (isset($langIds[0]) && !empty($langIds[0])) {
                foreach ($langIds as $key => $lang) {
                    if (!empty($levelIds[$key])) {
                        $data = $langIds[$key] . '-' . $levelIds[$key];
                    } else {
                        $data = $langIds[$key];
                    }
                    $foreignLanguage[] = $data;
                }
            }
            $item->foreign_languages = !empty($foreignLanguage) ? $foreignLanguage : [];
            $item->programming_languages = explode(',', $item->arr_programming_id);
            unset($item->arr_lang_id);
            unset($item->arr_level_id);
            unset($item->arr_programming_id);

            // set comments

            $commentStore = [];
            if (!empty($item->arr_comment)) {
                $comments = explode('^^^', $item->arr_comment);
                if (!empty($comments)) {
                    foreach ($comments as $c) {
                        $cDetails = explode('^', $c);
                        if (isset($cDetails[0]) && isset($cDetails[1]) && isset($cDetails[2])) {
                            $commentStore[] = [
                                'email' => $cDetails[1],
                                'content' => $cDetails[0],
                                'time' => $cDetails[2],
                            ];
                        }
                    }
                }
            }
            $item->comments = $commentStore;
            unset($item->arr_comment);
        }

        return $collections;
    }
}
