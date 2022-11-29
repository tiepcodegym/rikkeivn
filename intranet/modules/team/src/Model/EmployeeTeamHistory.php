<?php
namespace Rikkei\Team\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Resource\View\View;
use Rikkei\Team\Model\Employee;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;

class EmployeeTeamHistory extends CoreModel
{
    use SoftDeletes;

    const IS_WORKING = 1;
    const END_WORK = 0;
    const KEY_CACHE_API_HRM_PROFILE_EMPLOYEE_IDS = 'team_history_api_hrm_profile_employee_ids';
    const KEY_CACHE_API_HRM_PROFILE_EMPLOYEES = 'team_history_api_hrm_profile_employees';

    protected $table = 'employee_team_history';

    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'team_id', 'employee_id', 'start_at', 'end_at', 'created_at', 'updated_at', 'role_id', 'is_working',
    ];

    /**
     * Get list current team of employee
     *
     * @param int $employeeId
     * @return EmployeeTeamHistory
     */
    public static function getCurrentTeams ($employeeId)
    {
        return self::where('employee_id',$employeeId)
                ->whereNull('end_at')
                ->get();
    }
    public static function getCurrentTeamsHistory($employeeId)
    {
        return self::where('employee_id', $employeeId)
                ->whereNull('deleted_at')
                ->select('id', 'team_id', 'employee_id', 'role_id', 'start_at', 'end_at', 'is_working')
                ->get();
    }

    public static function getCurrentByTeamEmployee($teamId, $employeeId)
    {
        return self::where('team_id', $teamId)
                ->where('employee_id', $employeeId)
                ->whereNull('end_at')
                ->first();
    }
    public static function getCurrentHistoryById($id)
    {
        return self::where('id', $id)
                ->whereNull('deleted_at')
                ->first();
    }

    /**
     * Get count team's employee in month
     *
     * @param int $month
     * @param int $year
     * @param int|null $teamId
     * @param boolean $isTeamSoftDev
     * @return int
     */
    public static function getCountEmployeeOfMonth($month, $year, $teamId = null, $isTeamSoftDev = false)
    {
        $firstLastMonth = View::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $firstDay = $firstLastMonth[0];
        $lastDay = $firstLastMonth[1];
        $list = self::whereRaw("(DATE(start_at) <= DATE(?) or start_at is null) and (DATE(end_at) >= DATE(?) or end_at is null)", [$lastDay, $firstDay])
                    ->selectRaw('count(distinct employee_id) as count_emp')
                    ->leftJoin("employees", "employees.id", "=", "employee_team_history.employee_id")
                    ->whereRaw("DATE(employees.join_date) <= DATE(?)", [$lastDay]);
        if (!empty($teamId)) {
            $list->where('team_id', $teamId);
        }
        if ($isTeamSoftDev) {
            $teamTable = Team::getTableName();
            $employeeTeamHistoryTbl = self::getTableName();
            $list->leftJoin("{$teamTable}", "{$employeeTeamHistoryTbl}.team_id" , "=", "{$teamTable}.id")
                ->where("{$teamTable}.is_soft_dev", Team::IS_SOFT_DEVELOPMENT);
        }
        return $list->first()->count_emp;
    }

    /**
     * Get employees start or end in month
     *
     * @param int $month
     * @param int $year
     * @param int $teamId
     * @return EmployeeTeamHistory collection
     */
    public static function getEmpStartOrEndInMonth($month, $year, $teamId = null)
    {
        $emps = self::where(function ($query) use ($month, $year) {
            $query->whereRaw("MONTH(end_at) = ? AND YEAR(end_at) = ?", [$month, $year])
                    ->orWhereRaw("MONTH(start_at) = ? AND YEAR(start_at) = ?", [$month, $year]);
        });
        if ($teamId) {
            $emps->where('team_id', $teamId);
        }
        return $emps->get();
    }

    /**
     * Set end date work in team
     *
     * @param int $employeeId
     * @param date $date
     * @return void
     */
    public static function updateEndAt($employeeId, $date = null)
    {
        DB::beginTransaction();
        try {
            $empHistoryWithNullEndAt = self::where('employee_id', $employeeId)
                                            ->whereNull('end_at')->get();
            //if employee is working else employee leave job
            if (count($empHistoryWithNullEndAt)) {
                if ($date) {
                    self::where('employee_id', $employeeId)
                        ->whereNull('end_at')
                        ->update(['end_at' => $date]);
                }
            } else {
                $empHistoryWithMaxEndAt = DB::select("select * from employee_team_history join
                                    (select max(end_at) max_end_at from employee_team_history where employee_id = ?) as  emp_max_end on employee_team_history.end_at = emp_max_end.max_end_at
                                    where employee_id = ?", [$employeeId, $employeeId]
                                    );
                if (count($empHistoryWithMaxEndAt)) {
                    foreach ($empHistoryWithMaxEndAt as $empMaxEndAt) {
                        self::where('id', $empMaxEndAt->id)
                                ->update(['end_at'=> $date]);
                    }
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
        }
    }

    /**
     * Get teams of employee by month, year
     *
     * @param int $month
     * @param int $year
     * @param int $empId
     * @return EmployeeTeamHistory collection
     */
    public static function getTeamOfEmp($month, $year, $empId, $teamId = null)
    {
        $firstLastMonth = View::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $firstDay = $firstLastMonth[0];
        $lastDay = $firstLastMonth[1];
        $result = self::whereRaw("(DATE(start_at) <= DATE(?) or start_at is null) and (DATE(end_at) >= DATE(?) or end_at is null)", [$lastDay, $firstDay])
                    ->where('employee_id', $empId)
                    ->select('*');
        if ($teamId) {
            $result->where('team_id', $teamId);
            return $result->first();
        }
        return $result->get();
    }

    /**
     * Set end_at for employees leave date in today
     */
    public static function cronUpdate()
    {
        $empsUpdatedToday = Employee::getEmpUpdatedToday();
        foreach ($empsUpdatedToday as $emp) {
            static::updateEndAt($emp->id, $emp->leave_date);
        }
    }

    /**
     * Get employee has team start at $startAt
     *
     * @param date $startAt
     * @param int $employeeId
     * @return EmployeeTeamHistory collection
     */
    public static function getTeamByStartAt($startAt, $employeeId)
    {
        return self::whereRaw("DATE(start_at) = DATE(?)", [$startAt])
                ->where('employee_id', $employeeId)
                ->get();
    }

    /**
     * Get team positions of employee
     *
     * @param [int] $employeeId
     *
     * @return EmployeeTeamHistory collection
     */
    public static function getTeamPositons($employeeId)
    {
        $selfTbl = self::getTableName();
        return self::select([
                "{$selfTbl}.team_id",
                "{$selfTbl}.role_id",
                DB::raw("DATE({$selfTbl}.start_at) as start_at"),
                DB::raw("DATE({$selfTbl}.end_at) as end_at"),
                "{$selfTbl}.is_working"
            ])
            ->join(DB::raw("(select max(id) as id from employee_team_history where employee_id = {$employeeId} group by team_id) as tbl_max_id"), function ($join) use ($selfTbl) {
                $join->on("{$selfTbl}.id", "=", "tbl_max_id.id");
            })
            ->where($selfTbl.'.employee_id', $employeeId)
            ->orderBy("{$selfTbl}.id", "desc")
            ->get();
    }

    /**
     * Get team history of employee
     *
     * @param [int] $employeeId
     *
     * @return Team history collection
     */
    public static function getTeamHistory($employeeId)
    {
        $selfTbl = self::getTableName();
        return self::select([
                "{$selfTbl}.id",
                "{$selfTbl}.team_id",
                "{$selfTbl}.role_id",
                "{$selfTbl}.is_working",
                DB::raw("DATE({$selfTbl}.start_at) as start_at"),
                DB::raw("DATE({$selfTbl}.end_at) as end_at"),
            ])
            ->whereNull("{$selfTbl}.deleted_at")
            ->where($selfTbl.'.employee_id', $employeeId)
            ->orderByRaw("end_at IS NULL DESC, end_at DESC, start_at DESC")
            ->get();
    }

    /**
     * Get team history of employee
     *
     * @param [int] $employeeId
     *
     * @return Team history collection
     */
    public static function getTeamHistoryWithTrash()
    {
        $selfTbl = self::getTableName();
        $teamTbl = Team::getTableName();
        $roleTbl = Role::getTableName();
        $empTbl = Employee::getTableName();

        return DB::table($empTbl)
            ->select([
                "{$selfTbl}.id as team_history_id",
                "{$empTbl}.id as employee_id",
                "{$selfTbl}.team_id",
                "{$teamTbl}.name as team_name",
                "{$selfTbl}.role_id",
                "{$roleTbl}.role as role_name",
                "{$selfTbl}.is_working",
                DB::raw("DATE({$selfTbl}.deleted_at) as deleted_at"),
                DB::raw("DATE({$selfTbl}.start_at) as start_at"),
                DB::raw("DATE({$selfTbl}.end_at) as end_at"),
            ])
            ->join("{$selfTbl}", "{$selfTbl}.employee_id", '=', "{$empTbl}.id")
            ->join("{$teamTbl}", "{$selfTbl}.team_id", '=', "{$teamTbl}.id")
            ->join("{$roleTbl}", "{$selfTbl}.role_id", '=', "{$roleTbl}.id")
            ->where("{$roleTbl}.special_flg", Role::FLAG_POSITION)
            ->orderByRaw("end_at IS NULL DESC, end_at DESC, start_at DESC")
            ->get();
    }

    /**
     * @param int $teamId
     * @param int $employeeId
     * @param int $roleId
     * @return mixed
     */
    public static function getEmpTeamHisByTeamIdAndRoleId($teamId, $roleId, $employeeId)
    {
        return self::where('team_id', $teamId)
            ->where('employee_id', $employeeId)
            ->where('role_id', $roleId)
            ->whereNull('end_at')
            ->first();
    }

    /**
     * get employee team history by id
     * @param int $id
     * @return mixed
     */
    public static function getEmpTeamHistoryById($id)
    {
        return self::where('id', $id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * get employee team history by team_id
     * @param int $empId
     * @param int $teamId
     * @return mixed
     */
    public static function getEmpTeamHistoryByTeam($empId, $teamId)
    {
        return self::where('team_id', $teamId)
            ->where('employee_id', $empId)
            ->whereNull('deleted_at')
            ->first();
    }

    public static function updateTeamIsWorking()
    {
        $now = Carbon::now()->toDateString();
        $tmp = EmployeeTeamHistory::join('teams', 'employee_team_history.team_id', '=', 'teams.id')
            ->where('teams.branch_code', Team::CODE_PREFIX_JP)
            ->where(function ($query) use ($now) {
                $query->whereDate('employee_team_history.end_at', '>=', $now)
                    ->orWhereNull('employee_team_history.end_at');
            })
            ->whereNull('employee_team_history.deleted_at')
            ->groupBy('employee_team_history.employee_id')
            ->select('teams.id', 'teams.branch_code', 'employee_team_history.*')->get();
        $employees = [];
        if (count($tmp) > 0) {
            foreach ($tmp as $item) {
                array_push($employees, $item->employee_id);
                $item->is_working = self::IS_WORKING;
                $item->save();
            }
        }

        $result = EmployeeTeamHistory::select([
            "employee_team_history.id",
            "employee_team_history.team_id",
            "employee_team_history.employee_id",
            "employee_team_history.is_working",
            "employee_team_history.end_at",
            "employee_team_history.deleted_at"
        ])
            ->join('team_members', function ($join) {
                $join->on("team_members.employee_id", "=", "employee_team_history.employee_id");
                $join->on("team_members.team_id", "=", "employee_team_history.team_id");
            })
            ->whereNotIn('employee_team_history.employee_id', $employees)
            ->where(function ($query) use ($now) {
                $query->whereDate('employee_team_history.end_at', '>=', $now)
                    ->orWhereNull('employee_team_history.end_at');
            })
            ->whereNull('employee_team_history.deleted_at')
            ->orderBy('employee_id', 'asc')
            ->get()
            ->groupBy('employee_id')
            ->map(function ($abc) {
                return $abc[0];
            });
        if (count($result) > 0) {
            foreach ($result as $value) {
                $value->is_working = self::IS_WORKING;
                $value->save();
            }
        }
    }


    public static function getCurrentTeamsWorking($employeeId)
    {
        return self::where('employee_id', $employeeId)
                ->whereNull('deleted_at')
                ->where('is_working', '=', static::IS_WORKING)
                ->select('id', 'team_id', 'employee_id', 'role_id', 'start_at', 'end_at', 'is_working')
                ->get();
    }

    /**
     * get current team is working of list employees
     * @param array $employeeIds
     * @return array
     */
    public static function getTeamWorkingIdOfEmployees($employeeIds)
    {
        return self::whereIn('employee_id', $employeeIds)
            ->where('is_working', '=', static::IS_WORKING)
            ->whereNull('deleted_at')
            ->groupBy('employee_id')
            ->pluck('team_id', 'employee_id')->toArray();
    }
}
