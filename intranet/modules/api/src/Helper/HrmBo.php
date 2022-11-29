<?php

namespace Rikkei\Api\Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Contract\Model\ContractModel as ContractModel;
use Rikkei\Resource\Model\Candidate as CandidateModel;
use Rikkei\Team\Model\Employee as EmployeeModel;
use Rikkei\Team\Model\EmployeeRole as EmployeeRoleModel;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Role as RoleModel;
use Rikkei\Team\Model\Team as TeamModel;

/**
 * Description of Contact
 *
 * @author duydv
 */
class HrmBo extends HrmBase
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
     * Tính tổng nhân viên ở mỗi bộ phận hoặc chi nhánh
     *
     * @param $selectedFields
     * @param $groupByArray
     * @param array $conditions
     * @param bool $isGetBranchName
     * @return mixed
     */
    protected function _getEmployeesForBO($selectedFields, $groupByArray, $conditions = [], $isGetBranchName = false)
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $teamBranchCodeName = 'team_branch_code_name_tbl';

        $today = Carbon::now()->format('Y-m-d');


        $collections = DB::table($employeeTable)->select($selectedFields)->where(
            array_merge([
                ["{$teamTable}.is_bo", '=', 1],
                ["{$teamHistory}.is_working", '=', 1],
            ], $conditions)
        )->join($teamHistory, "{$teamHistory}.employee_id", '=', "{$employeeTable}.id")
            ->join($teamTable, "{$teamHistory}.team_id", '=', "{$teamTable}.id");

        if ($isGetBranchName) {
            $sqlGetBranchName = DB::table($teamTable)->select('name', 'branch_code')->whereRaw('is_branch = 1')->toSql();
            $collections = $collections->join(DB::raw("({$sqlGetBranchName}) as {$teamBranchCodeName}"), "{$teamTable}.branch_code", "=", "{$teamBranchCodeName}.branch_code")
                ->addSelect(DB::raw("{$teamBranchCodeName}.name as team_name"));
        }

        return $collections->whereNull("{$employeeTable}.deleted_at")
            ->where(function ($q) use ($employeeTable, $today) {
                $q->whereDate("{$employeeTable}.leave_date", '>=', $today)
                    ->orWhereNull("{$employeeTable}.leave_date");
            })
            ->where(function ($q) use ($teamHistory, $today) {
                $q->whereDate("{$teamHistory}.start_at", '<=', $today)
                    ->orWhereNull("{$teamHistory}.start_at");
            })
            ->where(function ($q) use ($teamHistory, $today) {
                $q->whereDate("{$teamHistory}.end_at", '>=', $today)
                    ->orWhereNull("{$teamHistory}.end_at");
            })
            ->where(function ($q) use ($teamTable, $today) {
                $q->where("{$teamTable}.branch_code", '!=', '')
                    ->whereNotNull("{$teamTable}.branch_code");
            })
            ->groupBy($groupByArray)
            ->get();
    }


    /**
     * Tổng collections tính tới thời điểm bất kì
     *
     * @param $collections
     * @param $dayOfMonth
     * @return mixed
     */
    protected function _getTotalUpTo($collections, $dayOfMonth)
    {
        $filterd = $collections->filter(function ($value) use ($dayOfMonth) {
            return ($value->join_date == null || $value->join_date <= $dayOfMonth)
                &&
                ($value->leave_date == null || $value->leave_date >= $dayOfMonth);
        });

        return $filterd->count();
    }

    /**
     * Tổng nhân viên join trong 1 tháng
     *
     * @param $collections
     * @param $firstDayOfMonth
     * @param $lastDayOfMonth
     * @return mixed
     */
    protected function _getTotalJoin($collections, $firstDayOfMonth, $lastDayOfMonth)
    {
        $filterd = $collections->filter(function ($value) use ($firstDayOfMonth, $lastDayOfMonth) {
            return $value->join_date >= $firstDayOfMonth && $value->join_date <= $lastDayOfMonth;

        });

        return $filterd->count();
    }

    /**
     * Tổng nhân viên leave trong 1 tháng
     *
     * @param $collections
     * @param $firstDayOfMonth
     * @param $lastDayOfMonth
     * @return mixed
     */
    protected function _getTotalLeave($collections, $firstDayOfMonth, $lastDayOfMonth)
    {
        $filterd = $collections->filter(function ($value) use ($firstDayOfMonth, $lastDayOfMonth) {
            return $value->leave_date >= $firstDayOfMonth && $value->leave_date <= $lastDayOfMonth;
        });

        return $filterd->count();
    }

    /**
     * Tỉ lệ leave
     *
     * @param $totalLeave
     * @param $avgTotalEmployees
     * @return float|int
     */
    protected function _getRatioLeave($totalLeave, $avgTotalEmployees)
    {
        if ($avgTotalEmployees <= 0) return 0;

        return ($totalLeave / $avgTotalEmployees) * 100;
    }

    /**
     * Filter collections theo branch code hoặc id của bộ phận
     *
     * @param $collection
     * @param $request
     */
    protected function _filterBOTableBranchTeam(&$collection, $request)
    {
        if ($request->branch_code) {
            $collection = $collection->where('teams.branch_code', $request->branch_code);
        }
        if ($request->team_id) {
            $collection = $collection->where('teams.id', $request->team_id);
        }
    }

    /**
     * Get team BO
     * @param $request
     * @return mixed
     */
    public function getTeamBo($request)
    {
        return TeamModel::select(['id', 'name'])->where([
            ['branch_code', '=', $request->branch_code],
            ['is_bo', '=', 1],
        ])->orderBy('name', 'ASC')->get();
    }

    /**
     * Tổng employees mỗi Branch
     * @return mixed
     */
    public function getBoEachBranch()
    {
        $getBranchName = true;
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();

        $selectedFields = ["{$teamTable}.branch_code", DB::raw("COUNT(DISTINCT({$employeeTable}.id)) as total")];
        $groupByArray = ["{$teamTable}.branch_code"];

        return $this->_getEmployeesForBO($selectedFields, $groupByArray, [], $getBranchName);
    }

    /**
     * Tổng employees mỗi Division ở từng Branch
     * @param $request
     * @return mixed
     */
    public function getBoDivisionEachBranch($request)
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();

        $selectedFields = ["{$teamTable}.id", "{$teamTable}.name", "{$teamTable}.branch_code", DB::raw("COUNT(DISTINCT({$employeeTable}.id)) as total")];
        $conditions = [
            ["{$teamTable}.branch_code", '=', $request->branch_code]
        ];
        $groupByArray = ["{$teamTable}.id"];

        return $this->_getEmployeesForBO($selectedFields, $groupByArray, $conditions);
    }

    /**
     * Tỉ lệ nhân viên vào/ra
     *
     * @param $request
     * @return array
     */
    public function getStatisticalEmployeeInOut($request)
    {
        $firstDateOfYear = $request->year . '-01-01';
        $lastDateOfYear = $request->year . '-12-31';
        $currentDate = Carbon::now();

        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();

        $collections = \Rikkei\Team\Model\Employee::select([
            DB::raw("{$employeeTable}.id as employee_id"),
            DB::raw("date_format({$employeeTable}.join_date, '%Y-%m-%d') as join_date"),
            DB::raw("date_format({$employeeTable}.leave_date, '%Y-%m-%d') as leave_date"),
        ])->join($teamHistory, "{$teamHistory}.employee_id", '=', "{$employeeTable}.id")
            ->join($teamTable, "{$teamHistory}.team_id", '=', "{$teamTable}.id")
            ->whereNull("{$employeeTable}.deleted_at")
            ->where(function ($q) use ($teamHistory, $lastDateOfYear) {
                $q->whereDate("{$teamHistory}.start_at", '<=', $lastDateOfYear)
                    ->orWhereNull("{$teamHistory}.start_at");
            })
            ->where(function ($q) use ($teamHistory, $firstDateOfYear) {
                $q->whereDate("{$teamHistory}.end_at", '>=', $firstDateOfYear)
                    ->orWhereNull("{$teamHistory}.end_at");
            });

        if ($request->branch_code) {
            $collections = $collections->where("{$teamTable}.branch_code", $request->branch_code);
            $collections->where(function ($q) use ($teamTable) {
                $q->where("{$teamTable}.branch_code", '!=', '')
                    ->whereNotNull("{$teamTable}.branch_code");
            });
        }
        if ($request->team_id) {
            $collections = $collections->where("{$teamTable}.id", $request->team_id);
        }

        $collections = $collections->groupBy(['employee_id'])->get();

        $results = [];


        $firstDateOfYear = Carbon::parse($firstDateOfYear);
        $lastDateOfYear = Carbon::parse($lastDateOfYear);
        $lastDateOfYear = $currentDate->min($lastDateOfYear);

        while ($firstDateOfYear->lte($lastDateOfYear)) {
            $currentMonth = clone $firstDateOfYear;
            $firstDateOfMonth = $currentMonth->firstOfMonth()->format('Y-m-d');
            $lastDateOfMonth = $currentMonth->endOfMonth()->format('Y-m-d');

            $totalEmployeesUptoFirstOfMonth = $this->_getTotalUpTo($collections, $firstDateOfMonth);
            $totalEmployeesUptoLastOfMonth = $this->_getTotalUpTo($collections, $lastDateOfMonth);
            $totalLeave = $this->_getTotalLeave($collections, $firstDateOfMonth, $lastDateOfMonth);
            $totalJoin = $this->_getTotalJoin($collections, $firstDateOfMonth, $lastDateOfMonth);
            $ratioLeave = $this->_getRatioLeave($totalLeave, ($totalEmployeesUptoFirstOfMonth + $totalEmployeesUptoLastOfMonth) / 2);

            $results[] = [
                'month' => $currentMonth->format('Y-m'),
                'total_employees' => $totalEmployeesUptoLastOfMonth,
                'total_join' => $totalJoin,
                'total_leave' => $totalLeave,
                'ratio_leave' => $ratioLeave,
            ];

            $firstDateOfYear->addMonth(1);
        }

        return $results;
    }

    /**
     * Nhân viên Rời công ty trong tháng
     *
     * @param $request is the param
     * @return list employees
     */
    public function getListLeaveCompanyInMonth($request)
    {
        $employeeTable = EmployeeModel::getTableName();
        $reqDate = $request->month;

        $collections = DB::table($employeeTable)
            ->select(
                'employees.id',
                'employees.name',
                DB::raw("GROUP_CONCAT(DISTINCT (roles.role) SEPARATOR ', ') AS role"),
                DB::raw('GROUP_CONCAT( teams.name SEPARATOR \', \' ) AS team_name'),
                DB::raw('DATE_FORMAT( employees.leave_date, \'%Y-%m-%d\' ) AS leave_date'),
                DB::raw('IF ( DATE_FORMAT( employees.leave_date, \'%Y-%m-%d\' ) < CURDATE(), ' . self::STATUS_LEFT_COMPANY . ', ' . self::STATUS_PENDING_LEFT_COMPANY . ' ) AS status')
            )
            ->join('employee_team_history', 'employees.id', '=', 'employee_team_history.employee_id')
            ->leftJoin('roles', 'roles.id', '=', 'employee_team_history.role_id')
            ->join('teams', 'teams.id', '=', 'employee_team_history.team_id')
            ->whereNull('employees.deleted_at')
            ->where(DB::raw('DATE_FORMAT( employees.leave_date, \'%Y-%m\' )'), $reqDate)
            ->where(function ($query) use ($reqDate) {
                $query->whereNull('employee_team_history.start_at')
                    ->orWhere(DB::raw('DATE_FORMAT( employee_team_history.start_at, \'%Y-%m\' )'), '<=', $reqDate);
            })
            ->where(function ($query) use ($reqDate) {
                $query->whereNull('employee_team_history.end_at')
                    ->orWhere(DB::raw('DATE_FORMAT( employee_team_history.end_at, \'%Y-%m\' )'), '>=', $reqDate);
            })
            ->groupBy("{$employeeTable}.id")
            ->orderBy('leave_date', 'ASC');

        $this->_filterBOTableBranchTeam($collections, $request);

        return $collections->get();

    }

    /**
     * Nhân viên sinh nhật trong tháng
     *
     * @param $request
     * @return Illuminate\Support\Facades\DB
     */
    public function getListBirthdayInMonth($request)
    {
        $employeeTable = EmployeeModel::getTableName();
        $rolesTable = RoleModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $teamTable = TeamModel::getTableName();

        $yearMonth = $request->month;
        $monthRequest = Carbon::parse($yearMonth)->month;
        $dayRequest = Carbon::now()->format('Y-m-d');
        if (Carbon::now()->format('Y-m') !== Carbon::parse($request->year_month)->format('Y-m')) {
            $dayRequest = Carbon::parse($request->year_month)->endOfMonth()->format('Y-m-d');
        }

        $selectedFields = [
            "{$employeeTable}.id",
            "{$employeeTable}.name",
            DB::raw("GROUP_CONCAT(DISTINCT (roles.role) SEPARATOR ', ') AS role"),
            "{$teamTable}.name AS team_name",
            "{$employeeTable}.birthday"
        ];

        $collections = DB::table($employeeTable)->select($selectedFields)
            ->join($teamHistory, "{$employeeTable}.id", '=', "{$teamHistory}.employee_id")
            ->leftJoin($rolesTable, "{$rolesTable}.id", '=', "{$teamHistory}.role_id")
            ->join($teamTable, "{$teamTable}.id", '=', "{$teamHistory}.team_id")
            ->whereNull("{$employeeTable}.deleted_at")
            ->where(function ($q) use ($employeeTable, $dayRequest) {
                $q->whereNull("{$employeeTable}.leave_date")
                    ->orWhereDate("{$employeeTable}.leave_date", ">=", $dayRequest);
            })
            ->whereMonth("{$employeeTable}.birthday", "=", $monthRequest)
            ->where(function ($q) use ($teamHistory, $yearMonth) {
                $q->whereNull("{$teamHistory}.start_at")
                    ->orWhere(DB::raw("DATE_FORMAT({$teamHistory}.start_at, '%Y-%m')"), "<=", $yearMonth);
            })
            ->where(function ($q) use ($teamHistory, $yearMonth) {
                $q->whereNull("{$teamHistory}.end_at")
                    ->orWhere(DB::raw("DATE_FORMAT({$teamHistory}.end_at, '%Y-%m')"), ">=", $yearMonth);
            })
            ->groupBy("{$employeeTable}.id")
            ->orderBy(DB::raw("date_format(birthday, '%m-%d')"), 'ASC')
            ->orderBy(DB::raw("date_format(birthday, '%Y')"), 'ASC');

        $this->_filterBOTableBranchTeam($collections, $request);

        return $collections->get();
    }

    /**
     * Nhân viên hết hợp đồng trong tháng
     *
     */
    public function getExpiredContractInMonth($request)
    {
        $teamTable = TeamModel::getTableName();
        $employeeTable = EmployeeModel::getTableName();
        $teamHistory = EmployeeTeamHistory::getTableName();
        $rolesTable = RoleModel::getTableName();
        $contractTable = ContractModel::getTableName();

        $yearMonth = $request->month;
        $dayRequest = Carbon::now()->format('Y-m-d');
        if (Carbon::now()->format('Y-m') !== Carbon::parse($request->month)->format('Y-m')) {
            $dayRequest = Carbon::parse($request->month)->endOfMonth()->format('Y-m-d');
        }

        $collections = DB::table($employeeTable)->select("{$employeeTable}.id",
            "{$employeeTable}.name",
            DB::raw("GROUP_CONCAT(DISTINCT (roles.role) SEPARATOR ', ') AS role"),
            DB::raw("{$teamTable}.name as team_name"),
            DB::raw("DATE_FORMAT({$contractTable}.end_at, '%Y-%m-%d') as contract_end_at"),
            DB::raw("{$contractTable}.type as contract_type")
        )
            ->join($teamHistory,
                "{$employeeTable}.id", "=", "{$teamHistory}.employee_id"
            )
            ->leftjoin($rolesTable,
                "{$rolesTable}.id", "=", "{$teamHistory}.role_id"
            )

            ->join($teamTable,
                "{$teamTable}.id", "=", "{$teamHistory}.team_id"
            )
            ->join("{$contractTable}",
                "{$contractTable}.employee_id", "=", "{$employeeTable}.id"
            )
            ->whereNull("{$employeeTable}.deleted_at")
            ->where(function ($q) use ($employeeTable, $dayRequest) {
                $q->whereNull("{$employeeTable}.leave_date")
                    ->orWhereDate("{$employeeTable}.leave_date", ">=", $dayRequest);
            })
            ->where(DB::raw("DATE_FORMAT({$contractTable}.end_at, '%Y-%m')"), "=", $yearMonth)
            ->where(function ($q) use ($teamHistory, $yearMonth) {
                $q->whereNull("{$teamHistory}.start_at")
                    ->orWhere(DB::raw("DATE_FORMAT({$teamHistory}.start_at, '%Y-%m')"), '<=', $yearMonth);
            })
            ->where(function ($q) use ($teamHistory, $yearMonth) {
                $q->whereNull("{$teamHistory}.end_at")
                    ->orWhere(DB::raw("DATE_FORMAT({$teamHistory}.end_at, '%Y-%m')"), '>=', $yearMonth);
            })
            ->groupBy("{$employeeTable}.id")
            ->orderBy('contract_end_at', 'ASC');

        $this->_filterBOTableBranchTeam($collections, $request);

        return $collections->get();
    }

    /**
     * Nhân viên mới trong tháng
     *
     * @param $request is the param
     * @return list employees
     */
    public function getNewEmployeesInMonth($request)
    {
        $employeeTable = EmployeeModel::getTableName();
        $roleTable = RoleModel::getTableName();
        $employeeTeamHistoryTable = EmployeeTeamHistory::getTableName();
        $teamTable = TeamModel::getTableName();
        $candidatesTable = CandidateModel::getTableName();

        $reqDate = $request->month;
        $reqCandidateStatus = $request->status;

        $selectedFields = [
            "{$employeeTable}.id",
            "{$employeeTable}.name",
            DB::raw("GROUP_CONCAT(DISTINCT (roles.role) SEPARATOR ', ') AS role"),
            DB::raw("GROUP_CONCAT( {$teamTable}.name SEPARATOR ', ' ) AS team_name"),
            DB::raw("DATE_FORMAT( {$employeeTable}.join_date, '%Y-%m-%d' ) AS join_date"),
            "{$candidatesTable}.status"
        ];

        $collections = DB::table($employeeTable)
            ->select($selectedFields)
            ->join("{$employeeTeamHistoryTable}", "{$employeeTable}.id", '=', "{$employeeTeamHistoryTable}.employee_id")
            ->leftJoin("{$roleTable}", "{$roleTable}.id", '=', "{$employeeTeamHistoryTable}.role_id")
            ->join("{$teamTable}", "{$teamTable}.id", '=', "{$employeeTeamHistoryTable}.team_id")
            ->join("{$candidatesTable}", "{$candidatesTable}.employee_id", '=', "{$employeeTable}.id")
            ->whereNull("{$employeeTable}.deleted_at")
            ->whereNull("{$candidatesTable}.deleted_at")
            ->where(DB::raw("DATE_FORMAT( {$employeeTable}.join_date, '%Y-%m' )"), '=', $reqDate)
            ->where(function ($query) use ($reqDate, $employeeTeamHistoryTable) {
                $query->whereNull("{$employeeTeamHistoryTable}.start_at")
                    ->orWhere(DB::raw("DATE_FORMAT( {$employeeTeamHistoryTable}.start_at, '%Y-%m' )"), '<=', $reqDate);
            })
            ->where(function ($query) use ($reqDate, $employeeTeamHistoryTable) {
                $query->whereNull("{$employeeTeamHistoryTable}.end_at")
                    ->orWhere(DB::raw("DATE_FORMAT( {$employeeTeamHistoryTable}.end_at, '%Y-%m' )"), '>=', $reqDate);
            })
            ->whereIn("{$candidatesTable}.status", $reqCandidateStatus)
            ->groupBy("{$employeeTable}.id")
            ->orderBy('join_date', 'ASC');

        $this->_filterBOTableBranchTeam($collections, $request);

        return $collections->get();
    }

}
