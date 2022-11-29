<?php

namespace Rikkei\ManageTime\Model;

use DB;
use Exception;
use Carbon\Carbon;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\CoreModel;
use Rikkei\ManageTime\Model\TimekeepingAggregate;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salary extends CoreModel
{
    use SoftDeletes;

    protected $table = 'manage_time_salaries';

    /**
     * [getSalaryBySalaryTableId: get salary list by salary id]
     * @param  [int] $salaryTableId
     * @return [collection]
     */
    public static function getSalaryBySalaryTableId($salaryTableId)
    {
        $tblSalary = self::getTableName();
        $tblSalaryTable = SalaryTable::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblRole = Role::getTableName();

        $collection = self::select(
            "{$tblSalaryTable}.id as salary_table_id",
            "{$tblSalaryTable}.salary_table_name",
            "{$tblEmployee}.employee_code",
            "{$tblEmployee}.name as employee_name",
            "{$tblSalary}.basic_salary",
            "{$tblSalary}.official_salary",
            "{$tblSalary}.trial_salary",
            "{$tblSalary}.overtime_salary",
            "{$tblSalary}.gasoline_allowance",
            "{$tblSalary}.telephone_allowance",
            "{$tblSalary}.certificate_allowance",
            "{$tblSalary}.bonus_and_other_allowance",
            "{$tblSalary}.other_income",
            DB::raw('('. $tblSalary . '.official_salary + ' . $tblSalary . '.trial_salary + ' . $tblSalary . '.overtime_salary + ' . $tblSalary . '.gasoline_allowance + ' . $tblSalary . '.telephone_allowance + ' . $tblSalary . '.certificate_allowance + ' . $tblSalary . '.bonus_and_other_allowance + ' . $tblSalary . '.other_income) AS total_income'),
            "{$tblSalary}.premium_and_union",
            "{$tblSalary}.advance_payment",
            "{$tblSalary}.personal_income_tax",
            DB::raw('('. $tblSalary . '.premium_and_union + ' . $tblSalary . '.advance_payment + ' . $tblSalary . '.personal_income_tax) AS total_deduction'),
            DB::raw('(('. $tblSalary . '.official_salary + ' . $tblSalary . '.trial_salary + ' . $tblSalary . '.overtime_salary + ' . $tblSalary . '.gasoline_allowance + ' . $tblSalary . '.telephone_allowance + ' . $tblSalary . '.certificate_allowance + ' . $tblSalary . '.bonus_and_other_allowance + ' . $tblSalary . '.other_income) - ('. $tblSalary . '.premium_and_union + ' . $tblSalary . '.advance_payment + ' . $tblSalary . '.personal_income_tax)) AS money_received'),
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$tblRole}.role, ' - ', {$tblTeam}.name) ORDER BY {$tblRole}.role DESC SEPARATOR '; ') as role_name")
        );

        // join employee
        $collection->join("{$tblEmployee}", 
            function ($join) use ($tblEmployee, $tblSalary)
            {
                $join->on("{$tblEmployee}.id", '=', "{$tblSalary}.employee_id");
            }
        );

        // join team member
        $collection->join(
            "{$tblTeamMember}", 
            function ($join) use ($tblTeamMember, $tblEmployee) 
            {
                $join->on("{$tblTeamMember}.employee_id", '=', "{$tblEmployee}.id");
            }
        );

        // join team
        $collection->join(
            "{$tblTeam}", 
            function ($join) use ($tblTeam, $tblTeamMember) 
            {
                $join->on("{$tblTeam}.id", '=', "{$tblTeamMember}.team_id");
            }
        );

        // join role
        $collection->join(
            "{$tblRole}", 
            function ($join) use ($tblRole, $tblTeamMember) 
            {
                $join->on("{$tblRole}.id", '=', "{$tblTeamMember}.role_id");
                $join->on("{$tblRole}.special_flg", '=', DB::raw(Role::FLAG_POSITION));
            }
        );

        // join salary table
        $collection->join("{$tblSalaryTable}", 
            function ($join) use ($tblSalaryTable, $tblSalary)
            {
                $join->on("{$tblSalaryTable}.id", '=', "{$tblSalary}.salary_table_id");
            }
        );
        $collection->where("{$tblSalary}.salary_table_id", $salaryTableId)
            ->whereNull("{$tblSalaryTable}.deleted_at")
            ->groupBy("{$tblEmployee}.id");

        $pager = Config::getPagerData(null, ['order' => "{$tblEmployee}.id", 'dir' => 'ASC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * [getSalaryCollection: get salary list by employee id and year]
     * @param  [int] $employeeId
     * @param  [int] $year
     * @return [collection]
     */
    public static function getSalaryCollection($employeeId, $year = null)
    {
        if (!$year) {
            $year = Carbon::now()->year;
        }
        $tblSalary = self::getTableName();
        $tblSalaryTable = SalaryTable::getTableName();
        $salaryThisPeriod = self::select("{$tblSalaryTable}.id as salary_table_id")
            ->join("{$tblSalaryTable}", "{$tblSalaryTable}.id", "=", "{$tblSalary}.salary_table_id")
            ->where("{$tblSalary}.employee_id", $employeeId)
            ->where("{$tblSalaryTable}.year", Carbon::now()->year)
            ->orderBy("{$tblSalaryTable}.month", "DESC")
            ->orderBy("{$tblSalaryTable}.id", "DESC")
            ->first();

        $collection = self::select(
                "{$tblSalaryTable}.id as salary_table_id",
                "{$tblSalaryTable}.month",
                DB::raw('('. $tblSalary . '.official_salary + ' . $tblSalary . '.trial_salary + ' . $tblSalary . '.overtime_salary + ' . $tblSalary . '.gasoline_allowance + ' . $tblSalary . '.telephone_allowance + ' . $tblSalary . '.certificate_allowance + ' . $tblSalary . '.bonus_and_other_allowance + ' . $tblSalary . '.other_income) AS total_income'),
                DB::raw('('. $tblSalary . '.premium_and_union + ' . $tblSalary . '.advance_payment + ' . $tblSalary . '.personal_income_tax) AS total_deduction'),
                DB::raw('(('. $tblSalary . '.official_salary + ' . $tblSalary . '.trial_salary + ' . $tblSalary . '.overtime_salary + ' . $tblSalary . '.gasoline_allowance + ' . $tblSalary . '.telephone_allowance + ' . $tblSalary . '.certificate_allowance + ' . $tblSalary . '.bonus_and_other_allowance + ' . $tblSalary . '.other_income) - ('. $tblSalary . '.premium_and_union + ' . $tblSalary . '.advance_payment + ' . $tblSalary . '.personal_income_tax)) AS money_received')
            )
            ->join("{$tblSalaryTable}", "{$tblSalaryTable}.id", "=", "{$tblSalary}.salary_table_id")
            ->where("{$tblSalary}.employee_id", $employeeId)
            ->where("{$tblSalaryTable}.year", $year)
            ->whereNotNull("{$tblSalaryTable}.timekeeping_table_id");
        if ($salaryThisPeriod) {
            $collection->where("{$tblSalaryTable}.id", "!=", $salaryThisPeriod->salary_table_id);
        }

        $pager = Config::getPagerData(null, ['order' => "{$tblSalaryTable}.month", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy("{$tblSalaryTable}.id", 'DESC');
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * [getSalaryThisPeriod: get salary detail this period of current year]
     * @param  [int] $employeeId
     * @return [object]
     */
    public static function getSalaryThisPeriod($employeeId)
    {
        $yearCurrent = Carbon::now()->year;
        $tblSalary = Salary::getTableName();
        $tblSalaryTable = SalaryTable::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTimekeepingAggregate = TimekeepingAggregate::getTableName();
        $salaryThisPeriod = self::select(
                "{$tblSalaryTable}.timekeeping_table_id",
                "{$tblSalaryTable}.number_working_days",
                "{$tblEmployee}.employee_code",
                "{$tblEmployee}.name as employee_name",
                "{$tblSalaryTable}.id as salary_table_id",
                "{$tblSalaryTable}.salary_table_name",
                "{$tblSalaryTable}.month",
                "{$tblSalaryTable}.year",
                "{$tblSalaryTable}.start_date",
                "{$tblSalaryTable}.end_date",
                "{$tblSalary}.basic_salary",
                "{$tblSalary}.official_salary",
                "{$tblSalary}.trial_salary",
                "{$tblSalary}.overtime_salary",
                "{$tblSalary}.gasoline_allowance",
                "{$tblSalary}.telephone_allowance",
                "{$tblSalary}.certificate_allowance",
                "{$tblSalary}.bonus_and_other_allowance",
                "{$tblSalary}.other_income",
                DB::raw('('. $tblSalary . '.official_salary + ' . $tblSalary . '.trial_salary + ' . $tblSalary . '.overtime_salary + ' . $tblSalary . '.gasoline_allowance + ' . $tblSalary . '.telephone_allowance + ' . $tblSalary . '.certificate_allowance + ' . $tblSalary . '.bonus_and_other_allowance + ' . $tblSalary . '.other_income) AS total_income'),
                "{$tblSalary}.premium_and_union",
                "{$tblSalary}.advance_payment",
                "{$tblSalary}.personal_income_tax",
                DB::raw('('. $tblSalary . '.premium_and_union + ' . $tblSalary . '.advance_payment + ' . $tblSalary . '.personal_income_tax) AS total_deduction'),
                DB::raw('(('. $tblSalary . '.official_salary + ' . $tblSalary . '.trial_salary + ' . $tblSalary . '.overtime_salary + ' . $tblSalary . '.gasoline_allowance + ' . $tblSalary . '.telephone_allowance + ' . $tblSalary . '.certificate_allowance + ' . $tblSalary . '.bonus_and_other_allowance + ' . $tblSalary . '.other_income) - ('. $tblSalary . '.premium_and_union + ' . $tblSalary . '.advance_payment + ' . $tblSalary . '.personal_income_tax)) AS money_received')
            )
            ->join("{$tblSalaryTable}", "{$tblSalaryTable}.id", "=", "{$tblSalary}.salary_table_id")
            ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblSalary}.employee_id")
            ->join("{$tblTimekeepingAggregate}", "{$tblTimekeepingAggregate}.timekeeping_table_id", "=", "{$tblSalaryTable}.timekeeping_table_id")
            ->where("{$tblSalary}.employee_id", $employeeId)
            ->where("{$tblSalaryTable}.year", $yearCurrent)
            ->orderBy("{$tblSalaryTable}.month", "DESC")
            ->orderBy("{$tblSalaryTable}.id", "DESC")
            ->first();

        return $salaryThisPeriod;
    }

    /**
     * [getSalaryByEmployee: get salary detail by salary table id and employee id]
     * @param  [int] $salaryTableId
     * @param  [int] $employeeId
     * @return [object]
     */
    public static function getSalaryByEmployee($salaryTableId, $employeeId)
    {
        $yearCurrent = Carbon::now()->year;
        $tblSalary = self::getTableName();
        $tblSalaryTable = SalaryTable::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTimekeepingAggregate = TimekeepingAggregate::getTableName();
        $salary = self::select(
                "{$tblSalaryTable}.timekeeping_table_id",
                "{$tblSalaryTable}.number_working_days",
                "{$tblEmployee}.employee_code",
                "{$tblEmployee}.name as employee_name",
                "{$tblSalaryTable}.id as salary_table_id",
                "{$tblSalaryTable}.salary_table_name",
                "{$tblSalaryTable}.month",
                "{$tblSalaryTable}.year",
                "{$tblSalaryTable}.start_date",
                "{$tblSalaryTable}.end_date",
                "{$tblSalary}.basic_salary",
                "{$tblSalary}.official_salary",
                "{$tblSalary}.trial_salary",
                "{$tblSalary}.overtime_salary",
                "{$tblSalary}.gasoline_allowance",
                "{$tblSalary}.telephone_allowance",
                "{$tblSalary}.certificate_allowance",
                "{$tblSalary}.bonus_and_other_allowance",
                "{$tblSalary}.other_income",
                DB::raw('('. $tblSalary . '.official_salary + ' . $tblSalary . '.trial_salary + ' . $tblSalary . '.overtime_salary + ' . $tblSalary . '.gasoline_allowance + ' . $tblSalary . '.telephone_allowance + ' . $tblSalary . '.certificate_allowance + ' . $tblSalary . '.bonus_and_other_allowance + ' . $tblSalary . '.other_income) AS total_income'),
                "{$tblSalary}.premium_and_union",
                "{$tblSalary}.advance_payment",
                "{$tblSalary}.personal_income_tax",
                DB::raw('('. $tblSalary . '.premium_and_union + ' . $tblSalary . '.advance_payment + ' . $tblSalary . '.personal_income_tax) AS total_deduction'),
                DB::raw('(('. $tblSalary . '.official_salary + ' . $tblSalary . '.trial_salary + ' . $tblSalary . '.overtime_salary + ' . $tblSalary . '.gasoline_allowance + ' . $tblSalary . '.telephone_allowance + ' . $tblSalary . '.certificate_allowance + ' . $tblSalary . '.bonus_and_other_allowance + ' . $tblSalary . '.other_income) - ('. $tblSalary . '.premium_and_union + ' . $tblSalary . '.advance_payment + ' . $tblSalary . '.personal_income_tax)) AS money_received')
            )
            ->join("{$tblSalaryTable}", "{$tblSalaryTable}.id", "=", "{$tblSalary}.salary_table_id")
            ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblSalary}.employee_id")
            ->join("{$tblTimekeepingAggregate}", "{$tblTimekeepingAggregate}.timekeeping_table_id", "=", "{$tblSalaryTable}.timekeeping_table_id")
            ->where("{$tblSalaryTable}.id", $salaryTableId)
            ->where("{$tblSalary}.employee_id", $employeeId)
            ->first();

        return $salary;
    }
}