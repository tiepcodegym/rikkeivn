<?php

namespace Rikkei\ManageTime\Model;

use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\ManageTime\Http\Controllers\TimekeepingController;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\WorkingTime;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;

class Timekeeping extends CoreModel
{
    protected $table = 'manage_time_timekeepings';

    const NOT_SALARY_1 = 'Nghỉ thai sản';
    const NOT_SALARY_2 = 'Nghỉ hưởng lương cơ bản';

    /**
     * Dùng khi ấn nút cập nhật dữ liệu liên quan ở bảng công
     * 
     */
    const CHUNK_NUMBER = 3;

    /**
     * [getTimekeepingAggregate: get timekeeping aggregate]
     * @param  [array] $filter
     * @return [collection]
     */
    public static function getTimekeepingAggregate($timekeepingTableId, $teamCodePre, $empIds = [], $date = null)
    {
        $tblTimekeeping = self::getTableName();
        $tblTimekeepingTable = TimekeepingTable::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTeamMember = TeamMember::getTableName();

        // update timekeeping aggregate của empIds - cron
        if (count($empIds)) {
            $employeeIds = $empIds;
        } else {
            $employeeIds = self::where('timekeeping_table_id', $timekeepingTableId)->distinct('employee_id')->lists('employee_id')->toArray();
        }

        $collection = Employee::select(
            "{$tblEmployee}.offcial_date",
            "{$tblEmployee}.join_date",
            "{$tblEmployee}.leave_date",
            "{$tblEmployee}.trial_date",
            "{$tblEmployee}.id",
            //"employee_contract_histories.contract_type",
            "{$tblTimekeeping}.employee_id",
            "{$tblTimekeeping}.total_official_working_days",
            "{$tblTimekeeping}.total_trial_working_days",
            "{$tblTimekeeping}.total_number_late_in",
            "{$tblTimekeeping}.total_number_early_out",
            "{$tblTimekeeping}.total_official_business_trip",
            "{$tblTimekeeping}.total_trial_business_trip",
            "{$tblTimekeeping}.total_official_leave_day_has_salary",
            "{$tblTimekeeping}.total_trial_leave_day_has_salary",
            "{$tblTimekeeping}.total_leave_day_no_salary",
            "{$tblTimekeeping}.total_official_supplement",
            "{$tblTimekeeping}.total_trial_supplement",
            "{$tblTimekeeping}.total_official_holiay",
            "{$tblTimekeeping}.total_trial_holiay",
            "{$tblTimekeeping}.total_late_start_shift",
            "{$tblTimekeeping}.total_late_mid_shift",
            "{$tblTimekeeping}.total_early_mid_shift",
            "{$tblTimekeeping}.total_early_end_shift",
            "{$tblTimekeeping}.total_official_ot_weekdays",
            "{$tblTimekeeping}.total_trial_ot_weekdays",
            "{$tblTimekeeping}.total_official_ot_weekends",
            "{$tblTimekeeping}.total_trial_ot_weekends",
            "{$tblTimekeeping}.total_official_ot_holidays",
            "{$tblTimekeeping}.total_trial_ot_holidays",
            "{$tblTimekeeping}.total_ot_no_salary",
            "{$tblTimekeeping}.total_official_leave_basic_salary",
            "{$tblTimekeeping}.total_trial_leave_basic_salary"
        );
        if ($date) {
            $collection->addSelect(['employees.email', 'employees.name']);
        }

        // join team member
        $collection->leftJoin(
            "{$tblTeamMember}",
            function ($join) use ($tblTeamMember, $tblEmployee)
            {
                $join->on("{$tblTeamMember}.employee_id", '=', "{$tblEmployee}.id");
            }
        );

        $sqlJoinTimekeepingTbl = "(SELECT employee_id,
                SUM(
                    CASE
                    WHEN (register_business_trip_number = 1 OR register_supplement_number = 1 OR register_leave_has_salary = 1 OR register_leave_no_salary = 1) AND is_official = 1 THEN 0
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day)
                        AND timekeeping = 1 AND register_supplement_number < 1
                        AND ((has_leave_day = 5 AND has_leave_day_no_salary = 7) OR (has_leave_day_no_salary = 5 AND has_leave_day = 7))
                        AND is_official = 1 THEN timekeeping_number - register_supplement_number - register_leave_has_salary
                    WHEN timekeeping = 1
                        AND (((has_leave_day = 5 AND has_supplement = 3) OR (has_leave_day = 7 AND has_supplement = 2)) OR
                            ((has_leave_day_no_salary = 5 AND has_supplement = 3) OR (has_leave_day_no_salary = 7 AND has_supplement = 2)))
                        AND is_official = 1 THEN timekeeping_number - register_supplement_number - register_leave_has_salary
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day)
                        AND has_leave_day + has_leave_day = register_leave_has_salary
                        AND timekeeping = 1 AND has_leave_day > 0
                        AND is_official = 1 THEN timekeeping_number + timekeeping_number_register - has_leave_day
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day)
                        AND has_leave_day + has_leave_day = register_leave_has_salary
                        AND has_leave_day > 0
                        AND is_official = 1 THEN timekeeping_number + timekeeping_number_register
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day_no_salary)
                        AND has_leave_day_no_salary + has_leave_day_no_salary = register_leave_no_salary
                        AND timekeeping = 1 AND has_leave_day_no_salary > 0
                        AND is_official = 1 THEN timekeeping_number + timekeeping_number_register - has_leave_day_no_salary
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day_no_salary)
                        AND has_leave_day_no_salary > 0
                        AND has_leave_day_no_salary + has_leave_day_no_salary = register_leave_no_salary
                        AND is_official = 1 THEN timekeeping_number + timekeeping_number_register
                    WHEN ((has_supplement = 2 and timekeeping = 2) or (has_supplement = 3 and timekeeping = 4)) and timekeeping_number_register > 0 AND is_official = 1 then timekeeping_number_register
                    WHEN ((has_supplement = 2 and timekeeping = 2) or (has_supplement = 3 and timekeeping = 4)) AND is_official = 1 then 0
                    WHEN (has_business_trip != 0 && has_supplement != 0 && has_business_trip != has_supplement) AND is_official = 1 THEN 0
                    WHEN (has_business_trip != 0 && has_leave_day != 0 && has_business_trip != has_leave_day) AND is_official = 1 THEN 0
                    WHEN register_supplement_number > 0 AND timekeeping_number = 1
                        AND (timekeeping = 1 OR (timekeeping = 4 AND has_supplement = 2) OR (timekeeping = 2 AND has_supplement = 3))
                        AND ((has_supplement = 3 AND has_leave_day = 7) OR (has_supplement = 2 AND has_leave_day = 5))
                        AND is_official = 1 THEN timekeeping_number - register_supplement_number
                    WHEN register_supplement_number > 0 AND timekeeping_number = 1
                        AND (timekeeping = 1 OR (timekeeping = 4 AND has_supplement = 2) OR (timekeeping = 2 AND has_supplement = 3))
                        AND ((has_supplement = 3 AND has_leave_day_no_salary = 7) OR (has_supplement = 2 AND has_leave_day_no_salary = 5))
                        AND is_official = 1 THEN timekeeping_number - register_supplement_number
                    WHEN register_supplement_number > 0
                        AND (timekeeping = 1 OR (timekeeping = 4 AND has_supplement = 2) OR (timekeeping = 2 AND has_supplement = 3))
                        AND ((has_supplement = 3 AND has_leave_day = 7) OR (has_supplement = 2 AND has_leave_day = 5))
                        AND is_official = 1 THEN timekeeping_number
                    WHEN register_supplement_number > 0
                        AND (timekeeping = 1 OR (timekeeping = 4 AND has_supplement = 2) OR (timekeeping = 2 AND has_supplement = 3))
                        AND ((has_supplement = 3 AND has_leave_day_no_salary = 7) OR (has_supplement = 2 AND has_leave_day_no_salary = 5))
                        AND is_official = 1 THEN timekeeping_number
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day) AND is_official = 1 
                        AND (register_leave_has_salary + register_supplement_number) > 1 THEN 0
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day) AND is_official = 1 THEN abs(timekeeping_number_register)
                    WHEN (has_business_trip = 2 and timekeeping = 2) or (has_business_trip = 3 and timekeeping = 4) then 0
                    WHEN (has_supplement = 2 and timekeeping = 2) or (has_supplement = 3 and timekeeping = 4) then 0
                    WHEN (has_leave_day = 2 and timekeeping = 2) or (has_leave_day = 3 and timekeeping = 4) then 0
                    WHEN timekeeping = 1 and timekeeping_number = 1 and (has_supplement = 2 or has_supplement = 3)  AND is_official = 1 then timekeeping_number - register_supplement_number + timekeeping_number_register
                    WHEN timekeeping = 1 and timekeeping_number = 1 and (has_business_trip = 2 or has_business_trip = 3)  AND is_official = 1 then timekeeping_number - register_business_trip_number + timekeeping_number_register
                    WHEN timekeeping = 1 and timekeeping_number = 1
                        and ((has_leave_day = 2 or has_leave_day = 3)
                            OR (has_leave_day_no_salary = 2 or has_leave_day_no_salary = 3))
                        AND is_official = 1
                        then timekeeping_number - register_leave_has_salary - register_leave_no_salary + timekeeping_number_register
                    WHEN ((timekeeping = 2 AND has_leave_day = 5) OR (timekeeping = 4 AND has_leave_day = 7)) AND is_official = 1 THEN timekeeping_number - register_leave_has_salary
                    WHEN ((timekeeping = 2 AND has_leave_day_no_salary = 5) OR (timekeeping = 4 AND has_leave_day_no_salary = 7)) AND is_official = 1 THEN timekeeping_number - register_leave_no_salary                    
                    WHEN timekeeping != 3 AND is_official = 1 THEN timekeeping_number + timekeeping_number_register
                    ELSE 0
                    END
                ) AS total_official_working_days,
                SUM(
                  CASE
                    WHEN (register_business_trip_number = 1 OR register_supplement_number = 1 OR register_leave_has_salary = 1 OR register_leave_no_salary = 1) AND is_official = 0 THEN 0
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day)
                        AND timekeeping = 1 AND register_supplement_number < 1
                        AND ((has_leave_day = 5 AND has_leave_day_no_salary = 7) OR (has_leave_day_no_salary = 5 AND has_leave_day = 7))
                        AND is_official = 0 THEN timekeeping_number - register_supplement_number - register_leave_has_salary
                    WHEN timekeeping = 1
                        AND (((has_leave_day = 5 AND has_supplement = 3) OR (has_leave_day = 7 AND has_supplement = 2)) OR
                            ((has_leave_day_no_salary = 5 AND has_supplement = 3) OR (has_leave_day_no_salary = 7 AND has_supplement = 2)))
                        AND is_official = 0 THEN timekeeping_number - register_supplement_number - register_leave_has_salary
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day)
                        AND has_leave_day + has_leave_day = register_leave_has_salary
                        AND timekeeping = 1 AND has_leave_day > 0
                        AND is_official = 0 THEN timekeeping_number + timekeeping_number_register - has_leave_day
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day)
                        AND has_leave_day > 0
                        AND has_leave_day + has_leave_day = register_leave_has_salary
                        AND is_official = 0 THEN timekeeping_number + timekeeping_number_register
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day_no_salary)
                        AND has_leave_day_no_salary + has_leave_day_no_salary = register_leave_no_salary
                        AND timekeeping = 1  AND has_leave_day_no_salary > 0
                        AND is_official = 0 THEN timekeeping_number + timekeeping_number_register - has_leave_day_no_salary
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day_no_salary)
                        AND has_leave_day_no_salary > 0
                        AND has_leave_day_no_salary + has_leave_day_no_salary = register_leave_no_salary
                        AND is_official = 0 THEN timekeeping_number + timekeeping_number_register
                    WHEN ((has_supplement = 2 and timekeeping = 2) or (has_supplement = 3 and timekeeping = 4) and timekeeping_number_register > 0) AND is_official = 0 then timekeeping_number_register
                    WHEN ((has_supplement = 2 and timekeeping = 2) or (has_supplement = 3 and timekeeping = 4)) AND is_official = 0 then 0
                    WHEN (has_business_trip != 0 && has_supplement != 0 && has_business_trip != has_supplement) AND is_official = 0 THEN 0
                    WHEN (has_business_trip != 0 && has_leave_day != 0 && has_business_trip != has_leave_day) AND is_official = 0 THEN 0
                    WHEN register_supplement_number > 0 AND timekeeping_number = 1
                        AND (timekeeping = 1 OR (timekeeping = 4 AND has_supplement = 2) OR (timekeeping = 2 AND has_supplement = 3))
                        AND ((has_supplement = 3 AND has_leave_day = 7) OR (has_supplement = 2 AND has_leave_day = 5))
                        AND is_official = 0 THEN timekeeping_number - register_supplement_number
                    WHEN register_supplement_number > 0 AND timekeeping_number = 1
                        AND (timekeeping = 1 OR (timekeeping = 4 AND has_supplement = 2) OR (timekeeping = 2 AND has_supplement = 3))
                        AND ((has_supplement = 3 AND has_leave_day_no_salary = 7) OR (has_supplement = 2 AND has_leave_day_no_salary = 5))
                        AND is_official = 0 THEN timekeeping_number - register_supplement_number
                    WHEN register_supplement_number > 0
                        AND (timekeeping = 1 OR (timekeeping = 4 AND has_supplement = 2) OR (timekeeping = 2 AND has_supplement = 3))
                        AND ((has_supplement = 3 AND has_leave_day = 7) OR (has_supplement = 2 AND has_leave_day = 5))
                        AND is_official = 0 THEN timekeeping_number
                    WHEN register_supplement_number > 0
                        AND (timekeeping = 1 OR (timekeeping = 4 AND has_supplement = 2) OR (timekeeping = 2 AND has_supplement = 3))
                        AND ((has_supplement = 3 AND has_leave_day_no_salary = 7) OR (has_supplement = 2 AND has_leave_day_no_salary = 5))
                        AND is_official = 0 THEN timekeeping_number
                    WHEN (has_supplement != 0 && has_leave_day != 0 && has_supplement != has_leave_day) AND is_official = 0 THEN abs(timekeeping_number_register)
                    WHEN (has_business_trip = 2 and timekeeping = 2) or (has_business_trip = 3 and timekeeping = 4) then 0
                    WHEN (has_supplement = 2 and timekeeping = 2) or (has_supplement = 3 and timekeeping = 4) then 0
                    WHEN (has_leave_day = 2 and timekeeping = 2) or (has_leave_day = 3 and timekeeping = 4) then 0
                    WHEN timekeeping = 1 and timekeeping_number = 1 and (has_supplement = 2 or has_supplement = 3)  AND is_official = 0 then timekeeping_number - register_supplement_number + timekeeping_number_register
                    WHEN timekeeping = 1 and timekeeping_number = 1 and (has_business_trip = 2 or has_business_trip = 3)  AND is_official = 0 then timekeeping_number - register_business_trip_number + timekeeping_number_register
                    WHEN timekeeping = 1 and timekeeping_number = 1
                        and ((has_leave_day = 2 or has_leave_day = 3)
                            OR (has_leave_day_no_salary = 2 or has_leave_day_no_salary = 3))
                        AND is_official = 0
                        then timekeeping_number - register_leave_has_salary - register_leave_no_salary + timekeeping_number_register
                    WHEN ((timekeeping = 2 AND has_leave_day = 5) OR (timekeeping = 4 AND has_leave_day = 7)) AND is_official = 0 THEN timekeeping_number - register_leave_has_salary
                    WHEN ((timekeeping = 2 AND has_leave_day_no_salary = 5) OR (timekeeping = 4 AND has_leave_day_no_salary = 7)) AND is_official = 0 THEN timekeeping_number - register_leave_no_salary
                    WHEN timekeeping != 3 AND is_official = 0 THEN timekeeping_number + timekeeping_number_register
                    ELSE 0
                    END
                ) AS total_trial_working_days,

                SUM(
                    CASE WHEN (register_business_trip_number > 0 AND is_official = 1) THEN register_business_trip_number
                    ELSE 0
                    END
                ) AS total_official_business_trip,
                SUM(
                    CASE WHEN (register_business_trip_number > 0 AND is_official = 0) THEN register_business_trip_number
                    ELSE 0
                    END
                ) AS total_trial_business_trip,
                SUM(
                    CASE WHEN (register_leave_has_salary > 0  AND is_official = 1) THEN register_leave_has_salary - register_leave_basic_salary
                    ELSE 0
                    END
                ) AS total_official_leave_day_has_salary,
                SUM(
                    CASE WHEN (register_leave_has_salary > 0  AND is_official = 0) THEN register_leave_has_salary - register_leave_basic_salary
                    ELSE 0
                    END
                ) AS total_trial_leave_day_has_salary,
                SUM(
                    CASE WHEN (register_leave_basic_salary > 0  AND is_official = 1) THEN register_leave_basic_salary
                    ELSE 0
                    END
                ) AS total_official_leave_basic_salary,
                SUM(
                    CASE WHEN (register_leave_basic_salary > 0  AND is_official = 0) THEN register_leave_basic_salary
                    ELSE 0
                    END
                ) AS total_trial_leave_basic_salary,
                SUM(
                    CASE WHEN (register_leave_no_salary > 0  AND register_leave_has_salary = 0) THEN register_leave_no_salary
                    WHEN (register_leave_no_salary > 0  AND register_leave_has_salary > 0 AND has_leave_day != has_leave_day_no_salary) THEN register_leave_no_salary
                    WHEN (register_leave_no_salary > 0  AND register_leave_has_salary > 0 AND has_leave_day = has_leave_day_no_salary
                        AND (has_leave_day_no_salary != 2 OR has_leave_day_no_salary != 3)) THEN register_leave_no_salary
                    WHEN no_salary_holiday > 0 THEN no_salary_holiday
                    ELSE 0
                    END
                ) AS total_leave_day_no_salary,
                SUM( ABS(
                    CASE WHEN (register_supplement_number > 0 AND register_business_trip_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0 AND is_official = 1) THEN register_supplement_number
                    WHEN (register_supplement_number > 0 AND (register_business_trip_number = 1 OR (register_business_trip_number > 0 AND has_business_trip = has_supplement)) AND is_official = 1) THEN 0
                    WHEN (register_supplement_number > 0 AND (register_leave_has_salary = 1 
                        OR (register_leave_has_salary > 0 AND has_leave_day = has_supplement)
                        OR (has_leave_day_no_salary > 0 AND has_leave_day_no_salary = has_supplement)
                        ) AND is_official = 1) THEN 0
                    WHEN (register_supplement_number > 0 AND (register_leave_no_salary = 1 OR (register_leave_no_salary > 0 AND has_leave_day = has_supplement)) AND is_official = 1) THEN 0
                    WHEN register_supplement_number = 1 AND register_business_trip_number > 0 AND has_business_trip != has_supplement AND is_official = 1 THEN register_supplement_number - register_business_trip_number
                    WHEN register_supplement_number = 1 AND register_leave_has_salary > 0 AND has_business_trip != has_leave_day AND is_official = 1 THEN register_supplement_number - register_leave_has_salary
                    WHEN register_supplement_number = 1 AND register_leave_no_salary > 0 AND has_business_trip != has_leave_day AND is_official = 1 THEN register_supplement_number - register_leave_no_salary
                    WHEN register_supplement_number > 0 AND register_business_trip_number > 0 AND has_business_trip != has_supplement AND is_official = 1 THEN register_supplement_number
                    WHEN register_supplement_number > 0	
                        AND timekeeping = 0	
                        AND has_business_trip = 0	
                        AND (((has_supplement = 3 AND has_leave_day = 5) OR (has_supplement = 3 AND has_leave_day_no_salary = 5))	
                            OR ((has_supplement = 2 AND has_leave_day = 7) OR (has_supplement = 2 AND has_leave_day_no_salary = 7)))	
                        AND is_official = 1 THEN register_supplement_number
                    WHEN register_supplement_number > 0
                        AND ((has_supplement = 3 AND has_leave_day = 7) OR (has_supplement = 2 AND has_leave_day = 5))
                        AND is_official = 1 THEN register_supplement_number - register_leave_has_salary
                    WHEN register_supplement_number > 0
                        AND ((has_supplement = 3 AND has_leave_day_no_salary = 7) OR (has_supplement = 2 AND has_leave_day_no_salary = 5))
                        AND is_official = 1 THEN register_supplement_number - register_leave_no_salary
                    WHEN register_supplement_number > 0 AND register_leave_has_salary > 0 AND has_business_trip != has_leave_day
                        And has_leave_day > 0
                        AND has_leave_day + has_leave_day = register_leave_has_salary
                        AND is_official = 1 THEN register_supplement_number - has_leave_day
                    WHEN register_supplement_number > 0 AND register_leave_has_salary > 0 AND has_business_trip != has_leave_day
                        AND has_leave_day_no_salary > 0
                        AND has_leave_day_no_salary + has_leave_day_no_salary = register_leave_no_salary
                        AND is_official = 1 THEN register_supplement_number - register_leave_no_salary
                    WHEN register_supplement_number > 0 AND register_leave_has_salary > 0 AND has_business_trip != has_leave_day
                        AND timekeeping_number_register > 0
                        AND ((has_supplement = 3 AND timekeeping = 2) OR (has_supplement = 2 AND timekeeping = 4) OR (has_supplement != 0 AND timekeeping = 0))
                        AND is_official = 1 THEN register_supplement_number - register_leave_has_salary
                    WHEN register_supplement_number > 0 AND register_leave_no_salary > 0 AND has_supplement != has_leave_day_no_salary
                        AND timekeeping_number_register > 0
                        AND ((has_supplement = 3 AND timekeeping = 2) OR (has_supplement = 2 AND timekeeping = 2) OR (has_supplement != 0 AND timekeeping = 0))
                        AND is_official = 1 THEN register_supplement_number - register_leave_no_salary
                    WHEN register_supplement_number = 1 AND register_leave_has_salary > 0 AND has_supplement != has_leave_day
                        AND is_official = 1 THEN register_supplement_number - register_leave_has_salary
                    WHEN register_supplement_number = 1 AND register_leave_no_salary > 0 AND has_supplement != has_leave_day_no_salary
                        AND is_official = 1 THEN register_supplement_number - register_leave_no_salary
                    WHEN register_supplement_number > 0 AND (register_leave_has_salary > 0 OR register_leave_no_salary > 0)
                        AND (register_leave_has_salary + register_leave_no_salary) > register_supplement_number
                        AND (register_leave_has_salary + register_leave_no_salary) > " .  ManageTimeConst::TIME_MORE_HALF . "
                        AND (register_leave_has_salary + register_leave_no_salary) < 1
                        AND register_supplement_number < 1 AND is_official = 1 THEN 1 - (register_leave_has_salary + register_leave_no_salary)
                    WHEN register_supplement_number > 0 AND register_leave_has_salary > 0 AND register_leave_has_salary > register_supplement_number
                        AND (has_leave_day != 2 AND has_leave_day != 3)
                        AND is_official = 1 THEN register_leave_has_salary - register_supplement_number
                    WHEN register_supplement_number > 0 AND register_leave_no_salary > 0 AND register_leave_no_salary > register_supplement_number
                        AND (has_leave_day_no_salary != 2 AND has_leave_day_no_salary != 3)
                        AND is_official = 1 THEN register_leave_no_salary - register_supplement_number
                    WHEN register_supplement_number > 0 AND register_leave_has_salary > 0 AND has_business_trip != has_leave_day AND is_official = 1 THEN register_supplement_number
                    WHEN register_supplement_number > 0 AND register_leave_no_salary > 0 AND has_business_trip != has_leave_day AND is_official = 1 THEN register_supplement_number
                    WHEN register_supplement_number > 0 AND register_leave_no_salary > 0 AND has_leave_day != has_supplement AND is_official = 1 THEN register_supplement_number
                    ELSE 0
                    END)
                ) AS total_official_supplement,
                SUM( ABS(
                  CASE WHEN (register_supplement_number > 0 AND register_business_trip_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0 AND is_official = 0) THEN register_supplement_number
                    WHEN (register_supplement_number > 0 AND (register_business_trip_number = 1 OR (register_business_trip_number > 0 AND has_business_trip = has_supplement)) AND is_official = 0) THEN 0
                    WHEN (register_supplement_number > 0 AND (register_leave_has_salary = 1 
                        OR (register_leave_has_salary > 0 AND has_leave_day = has_supplement)
                        OR (has_leave_day_no_salary > 0 AND has_leave_day_no_salary = has_supplement)
                        ) AND is_official = 0) THEN 0
                    WHEN (register_supplement_number > 0 AND (register_leave_no_salary = 1 OR (register_leave_no_salary > 0 AND has_leave_day = has_supplement)) AND is_official = 0) THEN 0
                    WHEN register_supplement_number = 1 AND register_business_trip_number > 0 AND has_business_trip != has_supplement AND is_official = 0 THEN register_supplement_number - register_business_trip_number
                    WHEN register_supplement_number = 1 AND register_leave_has_salary > 0 AND has_business_trip != has_leave_day AND is_official = 0 THEN register_supplement_number - register_leave_has_salary
                    WHEN register_supplement_number = 1 AND register_leave_no_salary > 0 AND has_business_trip != has_leave_day AND is_official = 0 THEN register_supplement_number - register_leave_no_salary
                    WHEN register_supplement_number > 0 AND register_business_trip_number > 0 AND has_business_trip != has_supplement AND is_official = 0 THEN register_supplement_number
                    WHEN register_supplement_number > 0	
                        AND timekeeping = 0	
                        AND has_business_trip = 0	
                        AND (((has_supplement = 3 AND has_leave_day = 5) OR (has_supplement = 3 AND has_leave_day_no_salary = 5))	
                            OR ((has_supplement = 2 AND has_leave_day = 7) OR (has_supplement = 2 AND has_leave_day_no_salary = 7)))	
                        AND is_official = 0 THEN register_supplement_number
                    WHEN register_supplement_number > 0
                        AND ((has_supplement = 3 AND has_leave_day = 7) OR (has_supplement = 2 AND has_leave_day = 5))
                        AND is_official = 0 THEN register_supplement_number - register_leave_has_salary
                    WHEN register_supplement_number > 0
                        AND ((has_supplement = 3 AND has_leave_day_no_salary = 7) OR (has_supplement = 2 AND has_leave_day_no_salary = 5))
                        AND is_official = 0 THEN register_supplement_number - register_leave_no_salary
                    WHEN register_supplement_number > 0 AND register_leave_has_salary > 0 AND has_business_trip != has_leave_day
                        AND has_leave_day > 0
                        AND has_leave_day + has_leave_day = register_leave_has_salary
                        AND is_official = 0 THEN register_supplement_number - has_leave_day
                    WHEN register_supplement_number > 0 AND register_leave_has_salary > 0 AND has_business_trip != has_leave_day
                        AND has_leave_day_no_salary > 0
                        AND has_leave_day_no_salary + has_leave_day_no_salary = register_leave_no_salary
                        AND is_official = 0 THEN register_supplement_number - register_leave_no_salary
                    WHEN register_supplement_number > 0 AND register_leave_has_salary > 0 AND has_business_trip != has_leave_day
                        AND timekeeping_number_register > 0
                        AND ((has_supplement = 3 AND timekeeping = 2) OR (has_supplement = 2 AND timekeeping = 4) OR (has_supplement != 0 AND timekeeping = 0))
                        AND is_official = 0 THEN register_supplement_number - register_leave_has_salary
                    WHEN register_supplement_number > 0 AND register_leave_no_salary > 0 AND has_supplement != has_leave_day
                        AND timekeeping_number_register > 0
                        AND ((has_supplement = 3 AND timekeeping = 2) OR (has_supplement = 2 AND timekeeping = 2) OR (has_supplement != 0 AND timekeeping = 0))
                        AND is_official = 0 THEN register_supplement_number - register_leave_no_salary
                    WHEN register_supplement_number = 1 AND register_leave_has_salary > 0 AND has_supplement != has_leave_day
                        AND is_official = 0 THEN register_supplement_number - register_leave_has_salary
                    WHEN register_supplement_number = 1 AND register_leave_no_salary > 0 AND has_supplement != has_leave_day_no_salary
                        AND is_official = 0 THEN register_supplement_number - register_leave_no_salary
                    WHEN register_supplement_number > 0 AND (register_leave_has_salary > 0 OR register_leave_no_salary > 0)
                        AND (register_leave_has_salary + register_leave_no_salary) > register_supplement_number
                        AND (register_leave_has_salary + register_leave_no_salary) > " .  ManageTimeConst::TIME_MORE_HALF . "
                        AND (register_leave_has_salary + register_leave_no_salary) < 1
                        AND register_supplement_number < 1 AND is_official = 0 THEN 1 - (register_leave_has_salary + register_leave_no_salary)
                    WHEN register_supplement_number > 0 AND register_leave_has_salary > 0 AND register_leave_has_salary > register_supplement_number
                        AND (has_leave_day != 2 AND has_leave_day != 3)
                        AND is_official = 0 THEN register_leave_has_salary - register_supplement_number
                    WHEN register_supplement_number > 0 AND register_leave_no_salary > 0 AND register_leave_no_salary > register_supplement_number
                        AND (has_leave_day_no_salary != 2 AND has_leave_day_no_salary != 3)
                        AND is_official = 0 THEN register_leave_no_salary - register_supplement_number
                    WHEN register_supplement_number > 0 AND register_leave_has_salary > 0 AND has_business_trip != has_leave_day AND is_official = 0 THEN register_supplement_number
                    WHEN register_supplement_number > 0 AND register_leave_no_salary > 0 AND has_business_trip != has_leave_day AND is_official = 0 THEN register_supplement_number
                    WHEN register_supplement_number > 0 AND register_leave_no_salary > 0 AND has_leave_day != has_supplement AND is_official = 0 THEN register_supplement_number
                    ELSE 0
                    END)
                ) AS total_trial_supplement,
                SUM(
                    CASE WHEN (timekeeping = 3 AND is_official = 1) THEN 1 - no_salary_holiday
                    ElSE 0
                    END
                ) AS total_official_holiay,
                SUM(
                    CASE WHEN (timekeeping = 3 AND is_official = 0) THEN 1 - no_salary_holiday
                    ElSE 0
                    END
                ) AS total_trial_holiay,

                SUM(
                    CASE WHEN (register_ot = 1 AND is_official = 1) THEN register_ot_has_salary
                    ELSE 0
                    END
                ) AS total_official_ot_weekdays,
                SUM(
                    CASE WHEN (register_ot = 1 AND is_official = 0) THEN register_ot_has_salary
                    ELSE 0
                    END
                ) AS total_trial_ot_weekdays,
                SUM(
                    CASE WHEN (register_ot = 2 AND is_official = 1) THEN register_ot_has_salary
                    ELSE 0
                    END
                ) AS total_official_ot_weekends,
                SUM(
                    CASE WHEN (register_ot = 2 AND is_official = 0) THEN register_ot_has_salary
                    ELSE 0
                    END
                ) AS total_trial_ot_weekends,
                SUM(
                    CASE WHEN (register_ot = 3 AND is_official = 1) THEN register_ot_has_salary
                    ELSE 0
                    END
                ) AS total_official_ot_holidays,
                SUM(
                    CASE WHEN (register_ot = 3 AND is_official = 0) THEN register_ot_has_salary
                    ELSE 0
                    END
                ) AS total_trial_ot_holidays,
                SUM(
                    CASE WHEN register_ot_no_salary > 0 THEN register_ot_no_salary
                    ELSE 0
                    END
                ) AS total_ot_no_salary,";


        if (ManageTimeCommon::isTeamCodeJapan($teamCodePre)) {
            $sqlJoinTimekeepingTbl .= "SUM(late_start_shift) AS total_late_start_shift,";
            $sqlJoinTimekeepingTbl .= "SUM(late_mid_shift) AS total_late_mid_shift,";
            $sqlJoinTimekeepingTbl .= "SUM(early_mid_shift) AS total_early_mid_shift,";
            $sqlJoinTimekeepingTbl .= "SUM(early_end_shift) AS total_early_end_shift,";
            $sqlJoinTimekeepingTbl .= "
                    SUM(
                        CASE WHEN late_start_shift > 0 OR late_mid_shift > 0 THEN 1
                        ELSE 0
                        END
                    ) AS total_number_late_in,";
            $sqlJoinTimekeepingTbl .= "
                    SUM(
                        CASE WHEN early_mid_shift > 0 OR early_end_shift > 0 THEN 1
                        ELSE 0
                        END
                    ) AS total_number_early_out";
        } else {
            $minutesPerBlock = ManageTimeConst::TIME_LATE_IN_PER_BLOCK;
            $sqlJoinTimekeepingTbl .= "SUM(
                    CASE WHEN ((
                            late_start_shift != 0 AND
                            late_start_shift < 120 AND
                            register_business_trip_number = 0 AND
                            register_supplement_number = 0 AND
                            register_leave_has_salary = 0 AND
                            register_leave_no_salary = 0 AND
                            (end_time_morning_shift is not null OR
                                end_time_afternoon_shift is not null
                            )
                        ) OR (
                            late_start_shift != 0 AND
                            late_start_shift < 120 AND
                            (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND
                            has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_MORNING . " AND
                            has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_MORNING . " AND
                            has_leave_day_no_salary != " . ManageTimeConst::HAS_LEAVE_DAY_MORNING . " AND
                            has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_MORNING . " AND
                            (end_time_morning_shift is not null OR
                                end_time_afternoon_shift is not null
                            )
                        )
                    THEN (ceiling(late_start_shift/$minutesPerBlock)) * $minutesPerBlock
                    ELSE 0
                    END
                ) AS total_late_start_shift,";
            $sqlJoinTimekeepingTbl .= "SUM(
                    CASE WHEN ((late_mid_shift != 0 AND late_mid_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (late_mid_shift != 0 AND late_mid_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON . ") THEN (ceiling(late_mid_shift/$minutesPerBlock)) * $minutesPerBlock
                    ELSE 0
                    END
                ) AS total_late_mid_shift,";
            $sqlJoinTimekeepingTbl .= "SUM(
                    CASE WHEN ((early_mid_shift != 0 AND early_mid_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (early_mid_shift != 0 AND early_mid_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_MORNING . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_MORNING . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_MORNING . ") THEN (ceiling(early_mid_shift/$minutesPerBlock)) * $minutesPerBlock
                    ELSE 0
                    END
                ) AS total_early_mid_shift,";
            $sqlJoinTimekeepingTbl .= "SUM(
                    CASE WHEN ((early_end_shift != 0 AND early_end_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (early_end_shift != 0 AND early_end_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON . ") THEN (ceiling(early_end_shift/$minutesPerBlock)) * $minutesPerBlock
                    ELSE 0
                    END
                ) AS total_early_end_shift,";
            $sqlJoinTimekeepingTbl .= "(
                    SUM(
                        CASE WHEN ((late_start_shift != 0 AND late_start_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (late_start_shift != 0 AND late_start_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY . " AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_MORNING . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_MORNING . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_MORNING . ") THEN 1
                        ELSE 0
                        END
                    )
                    + SUM(
                        CASE WHEN ((late_mid_shift != 0 AND late_mid_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (late_mid_shift != 0 AND late_mid_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY . " AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON . ") THEN 1
                        ELSE 0
                        END
                    )
                ) AS total_number_late_in,
                (
                    SUM(
                        CASE WHEN ((early_mid_shift != 0 AND early_mid_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (early_mid_shift != 0 AND early_mid_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY . " AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_MORNING . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_MORNING . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_MORNING . ") THEN 1
                        ELSE 0
                        END
                    )
                    + SUM(
                        CASE WHEN ((early_end_shift != 0 AND early_end_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (early_end_shift != 0 AND early_end_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY . " AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON . ") THEN 1
                        ELSE 0
                        END
                    )
                ) AS total_number_early_out";
        }
        $sqlJoinTimekeepingTbl .= " FROM manage_time_timekeepings
                WHERE timekeeping_table_id = " . $timekeepingTableId;
        if ($date) {
            $sqlJoinTimekeepingTbl .= " AND timekeeping_date = '{$date}'";
        }
        $sqlJoinTimekeepingTbl .= " group by employee_id) AS $tblTimekeeping";
        // join timekeeping
        $collection->leftjoin(
            DB::raw($sqlJoinTimekeepingTbl), "{$tblTimekeeping}.employee_id", '=', "{$tblEmployee}.id"
        );

        return $collection->whereIn("{$tblEmployee}.id", $employeeIds)
            ->groupBy("{$tblEmployee}.id")->get()->toArray();
    }

    /**
     * Get late minutes of every employees in time keeping
     *
     * @param int $timekeepingTableId
     *
     * @return TimeKeeping join Employee collection
     */
    public static function getLateMinutes($timekeepingTableId)
    {
        $tblTimekeeping = TimekeepingAggregate::getTableName();
        $tblEmployee = Employee::getTableName();

        return TimekeepingAggregate::join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblTimekeeping}.employee_id")
            ->where('timekeeping_table_id', $timekeepingTableId)
            ->select(
                "{$tblEmployee}.employee_code",
                "{$tblEmployee}.name as employee_name",
                "total_late_start_shift",
                DB::raw("SUM(total_late_start_shift + total_late_mid_shift) AS total_late_shift"),
                DB::raw("SUM(total_early_mid_shift + total_early_end_shift) AS total_early_shift")
            )
            ->groupBy("{$tblEmployee}.id")
            ->get();
    }

    /**
     * Get timekeeping aggregate by employee
     * @param  [int] $timekeepingTableId
     * @param  [int] $employeeId
     * @param  [Carbon] $startDate
     * @param  [Carbon] $endDate
     * @return [type]
     */
    public static function getTimekeepingAggregateByEmp($timekeepingTableId, $employeeId, $startDate, $endDate)
    {
        $tblTimekeeping = self::getTableName();
        $tblTimekeepingTable = TimekeepingTable::getTableName();
        $tblEmployee = Employee::getTableName();

        $timekeepingAggregate = self::select(
            "{$tblEmployee}.employee_code",
            "{$tblEmployee}.name as employee_name",
            "{$tblTimekeeping}.employee_id",
            DB::raw("SUM(
                CASE WHEN (register_business_trip_number = 1 OR register_supplement_number = 1 OR register_leave_has_salary = 1 OR register_leave_no_salary = 1 OR (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) >= 1) THEN 0
                WHEN ((register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) > 0 AND timekeeping_number > 0 AND is_official = 1) THEN 0.5
                WHEN is_official = 1 THEN timekeeping_number
                ELSE 0
                END
            ) AS total_official_working_days"),
            DB::raw("SUM(
                CASE WHEN (register_business_trip_number = 1 OR register_supplement_number = 1 OR register_leave_has_salary = 1 OR register_leave_no_salary = 1 OR (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) >= 1) THEN 0
                WHEN ((register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) > 0 AND timekeeping_number > 0 AND is_official = 0) THEN 0.5
                WHEN is_official = 0 THEN timekeeping_number
                ELSE 0
                END
            ) AS total_trial_working_days"),
            DB::raw("SUM(
                CASE WHEN (register_ot = 1 AND is_official = 1) THEN register_ot_has_salary
                ELSE 0
                END
            ) AS total_official_ot_weekdays"),
            DB::raw("SUM(
                CASE WHEN (register_ot = 1 AND is_official = 0) THEN register_ot_has_salary
                ELSE 0
                END
            ) AS total_trial_ot_weekdays"),
            DB::raw("SUM(
                CASE WHEN (register_ot = 2 AND is_official = 1) THEN register_ot_has_salary
                ELSE 0
                END
            ) AS total_official_ot_weekends"),
            DB::raw("SUM(
                CASE WHEN (register_ot = 2 AND is_official = 0) THEN register_ot_has_salary
                ELSE 0
                END
            ) AS total_trial_ot_weekends"),
            DB::raw("SUM(
                CASE WHEN (register_ot = 3 AND is_official = 1) THEN register_ot_has_salary
                ELSE 0
                END
            ) AS total_official_ot_holidays"),
            DB::raw("SUM(
                CASE WHEN (register_ot = 3 AND is_official = 0) THEN register_ot_has_salary
                ELSE 0
                END
            ) AS total_trial_ot_holidays"),
            DB::raw("SUM(
                CASE WHEN register_ot_no_salary > 0 THEN register_ot_no_salary
                ELSE 0
                END
            ) AS total_ot_no_salary"),
            DB::raw("(SUM(
                    CASE WHEN ((late_start_shift != 0 AND late_start_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (late_start_shift != 0 AND late_start_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY . " AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_MORNING . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_MORNING . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_MORNING . ") THEN 1
                    ELSE 0
                    END
                ) + SUM(
                    CASE WHEN ((late_mid_shift != 0 AND late_mid_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (late_mid_shift != 0 AND late_mid_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY . " AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON . ") THEN 1
                    ELSE 0
                    END
                )
            ) AS total_number_late_in"),
            DB::raw("(SUM(
                    CASE WHEN ((early_mid_shift != 0 AND early_mid_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (early_mid_shift != 0 AND early_mid_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY . " AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_MORNING . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_MORNING . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_MORNING . ") THEN 1
                    ELSE 0
                    END
                ) + SUM(
                    CASE WHEN ((early_end_shift != 0 AND early_end_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (early_end_shift != 0 AND early_end_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY . " AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON . ") THEN 1
                    ELSE 0
                    END
                )
            ) AS total_number_early_out"),
            DB::raw("SUM(
                CASE WHEN (register_business_trip_number > 0 AND is_official = 1) THEN register_business_trip_number
                ELSE 0
                END
            ) AS total_official_business_trip"),
            DB::raw("SUM(
                CASE WHEN (register_business_trip_number > 0 AND is_official = 0) THEN register_business_trip_number
                ELSE 0
                END
            ) AS total_trial_business_trip"),
            DB::raw("SUM(
                CASE WHEN (register_leave_has_salary > 0 AND register_business_trip_number = 0 AND is_official = 1) THEN register_leave_has_salary
                WHEN (register_leave_has_salary > 0 AND register_business_trip_number > 0 AND register_business_trip_number < 1 AND is_official = 1) THEN 0.5
                ELSE 0
                END
            ) AS total_official_leave_day_has_salary"),
            DB::raw("SUM(
                CASE WHEN (register_leave_has_salary > 0 AND register_business_trip_number = 0 AND is_official = 0) THEN register_leave_has_salary
                WHEN (register_leave_has_salary > 0 AND register_business_trip_number > 0 AND register_business_trip_number < 1 AND is_official = 0) THEN 0.5
                ELSE 0
                END
            ) AS total_trial_leave_day_has_salary"),
            DB::raw("SUM(
                CASE WHEN (register_leave_no_salary > 0 AND register_business_trip_number = 0 AND register_leave_has_salary = 0) THEN register_leave_no_salary
                WHEN (register_leave_no_salary > 0 AND (register_business_trip_number + register_leave_has_salary) < 1) THEN 0.5
                ELSE 0
                END
            ) AS total_leave_day_no_salary"),
            DB::raw("SUM(
                CASE WHEN (register_supplement_number > 0 AND register_business_trip_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0 AND is_official = 1) THEN register_supplement_number
                WHEN (register_supplement_number > 0 AND (register_business_trip_number + register_leave_has_salary + register_leave_no_salary) < 1 AND is_official = 1) THEN 0.5
                ELSE 0
                END
            ) AS total_official_supplement"),
            DB::raw("SUM(
                CASE WHEN (register_supplement_number > 0 AND register_business_trip_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0 AND is_official = 0) THEN register_supplement_number
                WHEN (register_supplement_number > 0 AND (register_business_trip_number + register_leave_has_salary + register_leave_no_salary) < 1 AND is_official = 0) THEN 0.5
                ELSE 0
                END
            ) AS total_trial_supplement"),
            DB::raw("SUM(
                CASE WHEN (timekeeping = 3 AND is_official = 1) THEN 1
                ElSE 0
                END
            ) AS total_official_holiay"),
            DB::raw("SUM(
                CASE WHEN (timekeeping = 3 AND is_official = 0) THEN 1
                ElSE 0
                END
            ) AS total_trial_holiay"),
            DB::raw("SUM(
                CASE WHEN ((late_start_shift != 0 AND late_start_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (late_start_shift != 0 AND late_start_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_MORNING . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_MORNING . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_MORNING . ") THEN late_start_shift
                ELSE 0
                END
            ) AS total_late_start_shift"),
            DB::raw("SUM(
                CASE WHEN ((early_mid_shift != 0 AND early_mid_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (early_mid_shift != 0 AND early_mid_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_MORNING . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_MORNING . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_MORNING . ") THEN early_mid_shift
                ELSE 0
                END
            ) AS total_early_mid_shift"),
            DB::raw("SUM(
                CASE WHEN ((late_mid_shift != 0 AND late_mid_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (late_mid_shift != 0 AND late_mid_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON . ") THEN late_mid_shift
                ELSE 0
                END
            ) AS total_late_mid_shift"),
            DB::raw("SUM(
                CASE WHEN ((early_end_shift != 0 AND early_end_shift < 120 AND register_business_trip_number = 0 AND register_supplement_number = 0 AND register_leave_has_salary = 0 AND register_leave_no_salary = 0) OR (early_end_shift != 0 AND early_end_shift < 120 AND (register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1) AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON . " AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON . " AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON . ") THEN early_end_shift
                ELSE 0
                END
            ) AS total_early_end_shift")
        );

        $timekeepingAggregate->join(
            "{$tblTimekeepingTable}",
            function ($join) use ($tblTimekeepingTable, $tblTimekeeping)
            {
                $join->on("{$tblTimekeepingTable}.id", '=', "{$tblTimekeeping}.timekeeping_table_id");
            }
        );
        $timekeepingAggregate->join(
            "{$tblEmployee}",
            function ($join) use ($tblEmployee, $tblTimekeeping)
            {
                $join->on("{$tblEmployee}.id", '=', "{$tblTimekeeping}.employee_id");
            }
        );
        $timekeepingAggregate = $timekeepingAggregate->where("{$tblTimekeeping}.timekeeping_table_id", $timekeepingTableId)
            ->where("{$tblTimekeeping}.employee_id", $employeeId)
            ->whereBetween("{$tblTimekeeping}.timekeeping_date", [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->first();

        return $timekeepingAggregate;
    }

    /**
     * [getTimekeepingByEmployee: get timekeeping by employee and timekeeping date]
     * @param  [int] $employeeId
     * @param  [date] $timekeepingDate
     * @return [type]
     */
    public static function getTimekeepingByEmployee($timekeepingTableId, $employeeId, $timekeepingDate)
    {
        $timekeepingTable = self::getTableName();
        $employeeTable = Employee::getTableName();

        $timekeeping = self::select(
            "{$timekeepingTable}.id as timekeeping_id",
            "{$timekeepingTable}.timekeeping_date",
            "{$timekeepingTable}.timekeeping",
            "{$timekeepingTable}.timekeeping_number",
            "{$timekeepingTable}.has_business_trip",
            "{$timekeepingTable}.register_business_trip_number",
            "{$timekeepingTable}.has_leave_day",
            "{$timekeepingTable}.register_leave_has_salary",
            "{$timekeepingTable}.register_leave_no_salary",
            "{$timekeepingTable}.has_supplement",
            "{$timekeepingTable}.register_supplement_number",
            "{$timekeepingTable}.late_start_shift",
            "{$timekeepingTable}.early_mid_shift",
            "{$timekeepingTable}.late_mid_shift",
            "{$timekeepingTable}.early_end_shift",
            "{$timekeepingTable}.register_ot",
            "{$timekeepingTable}.register_ot_has_salary",
            "{$timekeepingTable}.register_ot_no_salary"
        );

        // join employee
        $timekeeping->join("{$employeeTable}",
            function ($join) use ($employeeTable, $timekeepingTable)
            {
                $join->on("{$employeeTable}.id", '=', "{$timekeepingTable}.employee_id");
            }
        );

        $timekeeping = $timekeeping->where("{$timekeepingTable}.employee_id", $employeeId)
            ->where("{$timekeepingTable}.timekeeping_table_id", $timekeepingTableId)
            ->whereDate("{$timekeepingTable}.timekeeping_date", '=', $timekeepingDate)
            ->first();

        return $timekeeping;
    }

    /**
     * [getEmployeesToTimekeeping: get employee to timekeeping]
     * @param  [int] $timekeepingTableId
     * @return [collection]
     */
    public static function getEmployeesToTimekeeping($timekeepingTableId, $dataFilter = [], $isExport = false)
    {
        $tblTimekeepingTable = self::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblRole = Role::getTableName();

        $collection = Employee::select(
                "{$tblEmployee}.id as employee_id",
                "{$tblEmployee}.employee_card_id as employee_card_id",
                "{$tblEmployee}.employee_code as employee_code",
                "{$tblEmployee}.name as employee_name",
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$tblTeam}.name) ORDER BY {$tblRole}.role DESC SEPARATOR '; ') as role_name")
            )
            ->join("{$tblTimekeepingTable}", "{$tblTimekeepingTable}.employee_id", "=", "{$tblEmployee}.id")
            ->leftJoin("{$tblTeamMember}", "{$tblTeamMember}.employee_id", "=", "{$tblEmployee}.id")
            ->leftJoin("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
            ->leftJoin("{$tblRole}", "{$tblRole}.id", "=", "{$tblTeamMember}.role_id")
            ->where("{$tblTimekeepingTable}.timekeeping_table_id", $timekeepingTableId);
        try {
            if (isset($dataFilter["{$tblTeamMember}.team_id"])) {
                $collection->where("{$tblTeamMember}.team_id", $dataFilter["{$tblTeamMember}.team_id"]);
            }
        } catch (Exception $ex) {
            return null;
        }

        $collection->groupBy("{$tblEmployee}.id");

        $pager = Config::getPagerData(null, ['order' => "{$tblEmployee}.id", 'dir' => 'ASC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $route = route('manage_time::timekeeping.timekeeping-detail', $timekeepingTableId) . '/';
        $collection = self::filterGrid($collection, [], $route, 'LIKE');

        if ($isExport) {
            return $collection->get();
        } else {
            return self::pagerCollection($collection, $pager['limit'], $pager['page']);
        }
    }

    /**
     * Update data into table timekeeping
     *
     * @param array $data
     * @param int $timekeepingTableId
     * @param boolean $update
     * @return boolean
     */
    public static function updateData($data, $timekeepingTableId, $update = true)
    {
        $table = self::getTableName();
        if (!count($data)) {
            return false;
        }
        $dataDelete = [];
        $dataInsert = [];
        $final = [];
        foreach ($data as $key => $val) {
            if ($update) {
                $employeeId = $val['employee_id'];
                $timekeepingDate = $val['timekeeping_date'];
                foreach (array_keys($val) as $field) {
                    $value = (is_null($val[$field]) ? 'NULL' : '"' . $val[$field] . '"');
                    $final[$field][] = 'WHEN `timekeeping_table_id` = "' . $timekeepingTableId . '" AND `employee_id` = "' . $employeeId . '" AND `timekeeping_date` = "' . $timekeepingDate . '" THEN ' . $value . ' ';
                }
            } else {
                $dataDelete[$val['employee_id']][] = $val['timekeeping_date'];
                $tempData = [];
                foreach (array_keys($val) as $field) {
                    $tempData[$field] = $val[$field];
                }
                $tempData['created_at'] = date('Y-m-d H:i:s');
                $tempData['updated_at'] = date('Y-m-d H:i:s');
                $tempData['timekeeping_table_id'] = $timekeepingTableId;
                $dataInsert[] = $tempData;
            }
        }

        if ($update) {
            $cases = '';
            foreach ($final as $k => $v) {
                $cases .=  '`'. $k.'` = (CASE '. implode("\n", $v) . "\n"
                                . 'ELSE `'.$k.'` END), ';
            }
            $query = 'UPDATE ' . $table . ' SET '. substr($cases, 0, -2) . ' WHERE `timekeeping_table_id` = "' . $timekeepingTableId . '"';
            DB::statement($query);
        } else {
            $delete = self::where('timekeeping_table_id', $timekeepingTableId);
            $delete->where(function ($query) use ($dataDelete) {
                foreach ($dataDelete as $employeeId => $arrayDate) {
                    $query->orWhere(function ($childQuery) use ($employeeId, $arrayDate) {
                        $childQuery->where('employee_id', $employeeId)
                                ->whereIn('timekeeping_date', $arrayDate);
                    });
                }
            });
            $delete->delete();
            foreach (array_chunk($dataInsert, 1000) as $chunk) {
                self::insert($chunk);
            }
        }
        return true;
    }

    /**
     * Get all employees of time keeping table
     *
     * @param int $tableId
     *
     * @return array    array of employee_id
     */
    public static function getEmployeesOfTable($tableId)
    {
        return self::where('timekeeping_table_id', $tableId)
                ->selectRaw('distinct(employee_id)')
                ->lists('employee_id')->toArray();
    }

    public static function timeKeepingItem($timekeepingTableId, $employeeId, $timekeepingDate)
    {
        return self::where('timekeeping_table_id', $timekeepingTableId)
            ->where('employee_id', $employeeId)
            ->where('timekeeping_date', $timekeepingDate)
            ->first();
    }

    /**
    * Get all keeping table of employee
    * @param  [int] $timekeepingTableId, $employeeId
    * @param  [date] $timeKeepingDate
    * @param  [array] $column
    * @return [collection]
    */
    public static function getTimekeeping($timekeepingTableId, $employeeId, $timeKeepingDate, $column = ['*'])
    {
        return self::select($column)
            ->where('timekeeping_table_id', $timekeepingTableId)
            ->where('employee_id', $employeeId)
            ->where('timekeeping_date', $timeKeepingDate)
            ->first();
    }

    public static function getTimeWorkOfEmplyee($dateStart, $dateEnd, $empId)
    {
        return self::select(
            "manage_time_timekeeping_tables.id as idTable",
            "manage_time_timekeepings.id",
            "timekeeping_table_id",
            "employee_id",
            "timekeeping_date",
            "start_time_morning_shift",
            "end_time_morning_shift",
            "start_time_afternoon_shift",
            "end_time_afternoon_shift",
            "late_start_shift",
            "early_mid_shift",
            "late_mid_shift",
            "early_end_shift",
            "has_supplement",
            "register_supplement_number",
            "has_leave_day",
            "register_leave_has_salary",
            "has_leave_day_no_salary",
            "register_ot_has_salary",
            "timekeeping",
            "timekeeping_number",
            "timekeeping_number_register",
            "employees.name"
        )
        ->leftJoin('employees', 'employees.id', '=', 'manage_time_timekeepings.employee_id')
        ->leftJoin('manage_time_timekeeping_tables', 'manage_time_timekeeping_tables.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
        ->where('timekeeping_date', '>=', $dateStart)
        ->where('timekeeping_date', '<=', $dateEnd)
        ->where('employee_id', '=', $empId)
        ->whereNull('manage_time_timekeeping_tables.deleted_at')
        ->groupBy('manage_time_timekeepings.id')
        ->orderBy('manage_time_timekeeping_tables.id', 'DESC')
        ->orderBy('timekeeping_date', 'ASC')
        ->get();
    }

     /**
      * [getTimeworkSystena description]
      * @param  [date] $dateStart      [Y-m-d]
      * @param  [date] $dateEnd        [Y-m-d]
      * @param  [object] $employee
      * @param  [boolean] $autoTimeOT
      * @param  [string] $teamCodePrefix
      * @param  [array] $holidayWeek    [time holiday day and week day]
      * @return [array]
      */
    public static function getTimeworkSystena($dateStart, $dateEnd, $employee, $autoTimeOT, $teamCodePrefix, $holidayWeek)
    {
        $empId = $employee->id;
        $collect = static::getTimeWorkOfEmplyee($dateStart, $dateEnd, $empId);

        if ($collect->isEmpty()) {
            return [];
        }
        $compensationDays = $holidayWeek['compensationDays'];
        $annualHolidays = $holidayWeek['annualHolidays'];
        $specialHolidays = $holidayWeek['specialHolidays'];

        $results = [];
        $regLeaveDay = LeaveDayRegister::getLeaveDayApproved($empId, $dateStart, $dateEnd);

        $checkEmp = [];
        $k = -1;
        foreach ($collect as $item) {
            // check get two table timekeeping new
            if (!in_array($item->idTable, $checkEmp)){
                $checkEmp[] = $item->idTable;
                $k++;
                if ($k == 2) {
                    break;
                }
            }
            $date = $item->timekeeping_date;
            $isWeekend = ManageTimeCommon::isWeekend(Carbon::createFromFormat('Y-m-d', $date), $compensationDays);
            $isHoliday = ManageTimeCommon::isHoliday(Carbon::createFromFormat('Y-m-d', $date), $annualHolidays, $specialHolidays, $teamCodePrefix);
            $isWeekendOrHoliday = $isWeekend || $isHoliday;

            $timeWork = static::getTimeWorking($employee, $date);
            $timeAfterOut = $timeWork['afternoonOutSetting']->format('H:i');
            $timeAfterIn = $timeWork['afternoonInSetting']->format('H:i');
            $timeMorIn = $timeWork['morningInSetting']->format('H:i');
            $timeMorOut = $timeWork['morningOutSetting']->format('H:i');

            $days = ManageTimeConst::dayJPs();
            $data = [
                "timeIn" => '',
                "timeOut" => '',
                "timeWork" => '',
                "note" => '',
                'dayOfWeek' => $days[Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek],
                'nameEmp' => $item->name,
            ];

            $isTimeOT = $autoTimeOT;

            if (!empty((float)$item->register_ot_has_salary) || $isWeekendOrHoliday) {
                $isTimeOT = true;
            }
            $timeWorking = static::getTimeInOut($item, $isTimeOT, $timeWork);
            $timeInWork = static::getTimeInOutWork($timeWorking, $timeWork);
            $data['timeIn'] = $timeWorking["timeIn"];
            $data['timeOut'] = $timeWorking["timeOut"];

            $timeResetOt = 0;
            if (!empty((float)$item->register_ot_has_salary)) {
                $data['timeWork']  = (float)$item->register_ot_has_salary;
                if ((strtotime($data['timeOut']) > strtotime('22:00')) || $data['timeOut'] == '00:00') {
                    $timeOut = $data['timeOut'];
                    if ($data['timeOut'] == '00:00') {
                        $timeOut = '23:59';
                    }
                    $time = static::calculateTime('22:00', $timeOut);
                    $data['timeWork'] =  $data['timeWork'] + $time;
                }
                $data['note'] = 'OT: ' . $data['timeWork'];
                $isTimeOT = false;
                $timeResetOt = $data['timeWork'];
            }

            $check = true;
            if ($isWeekendOrHoliday) {
                if (count($compensationDays['lea'])) {
                    foreach ($compensationDays['lea'] as $key => $value) {
                        if ($date == $value && empty((float)$item->register_ot_has_salary)) {
                            $data['timeIn'] = '';
                            $data['timeOut'] = '';
                            break;
                        }
                    }
                }
                $data['note'] = trim($data['note'], ';');
                $results[$k][Carbon::createFromFormat('Y-m-d', $date)->format('d')] = $data;
                $check = false;
            }

            $hasSupp = $item->has_supplement;
            $regSupp = (float)$item->register_supplement_number;
            $hasLeveday = $item->has_leave_day;
            $regLeave = (float)$item->register_leave_has_salary;

            if ($item->timekeeping == 3) {
                if (empty((float)$item->register_ot_has_salary)) {
                    $data['timeIn'] = '';
                    $data['timeOut'] = '';
                } else {
                    $data['note'] = 'L; ' . $data['note'];
                }
                $check = false;
            }

            if ($hasSupp == 1) {
                $data['timeIn'] = $timeMorIn;
                if ($isTimeOT && !empty($item->end_time_afternoon_shift)) {
                    if ($timeInWork['timeOut'] < $timeAfterOut) {
                        $data['timeOut'] = $timeAfterOut;
                    } else {
                        $data['timeOut'] = $timeInWork['timeOut'];
                    }
                } else {
                    $data['timeOut'] = $timeAfterOut;
                }
                if ((float)$regLeave) {
                    $data['note'] = $data['note'] . 'P: ' . $regLeave;
                }
                $data['timeWork'] = 8.0 + (float)$data['timeWork'] - (float)$regLeave * 8;
                $check = false;
            }

            if ($check && (!empty($timeInWork['timeIn']) && !empty($timeInWork['timeOut']))) {
                $data = static::calculateTimeWokingTwo($data, $item, $timeInWork, $timeWork);
            } elseif ($hasSupp && $check && $hasSupp != $hasLeveday) {
                $data['timeWork'] = $regSupp * 8 + (float)$data['timeWork'];
                if ($hasSupp == ManageTimeConst::HAS_BUSINESS_TRIP_MORNING) {
                    $data['timeIn'] = $timeMorIn;
                    $data['timeOut'] = $timeMorOut;
                } else {
                    $data['timeIn'] = $timeAfterIn;
                    $data['timeOut'] = $timeAfterOut;
                }
            } else {
                // do not some thing
            }

            if ($check && $hasLeveday && $hasSupp
                && (($hasLeveday == ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF && $hasSupp == ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON)
                    || ($hasLeveday == ManageTimeConst:: HAS_LEAVE_DAY_MORNING_HALF && $hasSupp == ManageTimeConst::HAS_SUPPLEMENT_MORNING)
                    || ($regLeave > 0.5 && $regLeave < 1))) {
                $leaves = static::scheckLeaveDay($date, $regLeaveDay, $timeWork, $item->timekeeping_table_id, $empId);
                $timeLeave = static::timeWorkSuppLeave($leaves[$empId], $data['timeIn'], $data['timeOut'], $timeWork);
                $data['timeWork'] = ($timeLeave['timeLeave'] / 60) + (float)$data['timeWork'];
                $data['timeIn'] = $timeLeave['timeIn'];
                $data['timeOut'] = $timeLeave['timeOut'];
                $data['note'] = $data['note'] . ';' . $timeLeave['note'];
            }

            // calculation
            if ($autoTimeOT && empty((float)$item->register_ot_has_salary) && !empty((float)$data['timeIn'])) {
                $timeAfterOut = $timeWork['afternoonOutSetting'];
                $timeOT = static::calculateTimeAutoOT($data['timeOut'], $timeAfterOut);
                if ($timeOT) {
                    $data['timeWork'] = $timeOT + (float)$data['timeWork'];
                }
            }
            $data['note'] = trim($data['note'], ';');
            //reset time out when have OT and time out < 18:30 not isWeekendOrHoliday
            if (!empty((float)$item->register_ot_has_salary && $data['timeOut'] == $timeAfterOut && !$isWeekendOrHoliday)) {
                    $minuteOT = (float)$timeResetOt * 60;
                    $tam = Carbon::createFromFormat('H:i', $timeWork['afternoonOutSetting']->hour + 1 . ':' . $timeWork['afternoonOutSetting']->minute);
                    $tam->addMinutes($minuteOT);
                    $data['timeOut'] = $tam->format('H:i');
                    if ($data['timeOut'] < $timeMorIn) {
                        $data['timeOut'] = '23:59';
                    }
            }
            if ($isWeekendOrHoliday && $autoTimeOT && empty((float)$item->register_ot_has_salary)
                && (!empty($timeInWork['timeIn']) && !empty($timeInWork['timeOut']))) {
                $data = static::calculateTimeWokingTwo($data, $item, $timeInWork, $timeWork);
            }

            if (empty((float)$data['timeWork'])) {
                $data['timeIn'] = '';
                $data['timeOut'] = '';
                $data['note'] = '';
            }
            $results[$k][Carbon::createFromFormat('Y-m-d', $date)->format('d')] = $data;
        }

        return $results;
    }

    /**
     * get time working of employee by month
     * @param  [type] $idEmp
     * @param  [type] $date
     * @return [type]
     */
    public static function getTimeWorking($employee, $date)
    {
        $empId = $employee->id;
        $workingTime = new WorkingTime();
        $workingTimeSettingOfEmp = $workingTime->getWorkingTimeInfo($empId, $date);
        $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($employee);
        return ManageTimeView::findTimeSetting($workingTimeSettingOfEmp, $teamCodePrefix);
    }

    /**
     * get time In Out working of employee
     * @param  [object] $item []
     * @param  [type] $timeOT
     * @param  [type] $timeWork [description]
     * @return [type]           [description]
     */
    public static function getTimeInOut($item, $timeOT, $timeWork)
    {
        $timeMorIn =  $timeWork['morningInSetting']->format('H:i');
        $timeMorOut =  $timeWork['morningOutSetting']->format('H:i');
        $timeAfterIn =  $timeWork['afternoonInSetting']->format('H:i');
        $timeAfterOut =  $timeWork['afternoonOutSetting']->format('H:i');

        if (!empty($item->start_time_morning_shift)) {
            if (strtotime($item->start_time_morning_shift) < strtotime($timeMorIn)) {
                $timeIn = $timeMorIn;
            } else {
                $timeIn = $item->start_time_morning_shift;
            }
        } elseif (!empty($item->end_time_morning_shift)) {
            $timeIn = $item->end_time_morning_shift;
        } elseif (!empty($item->start_time_afternoon_shift)) {
            if (strtotime($item->start_time_afternoon_shift) < strtotime($timeAfterIn)) {
                $timeIn = $timeAfterIn;
            } else {
                $timeIn = $item->start_time_afternoon_shift;
            }
        } else {
            $timeIn = '';
        }

        if (strtotime($timeIn) >= strtotime($timeMorOut)
            && strtotime($timeIn) <= strtotime($timeAfterIn)) {
                $timeIn = $timeAfterIn;
        }

        if (!empty($item->end_time_afternoon_shift)) {
            if ($timeOT || (strtotime($item->end_time_afternoon_shift) < strtotime($timeAfterOut))) {
                $timeOut = $item->end_time_afternoon_shift;
            } else {
                $timeOut = $timeAfterOut;
            }
        } elseif (!empty($item->end_time_morning_shift) && !empty($item->start_time_morning_shift)) {
            if (empty($item->end_time_morning_shift)) {
                $timeOut = $timeMorOut;
            } else {
                if (strtotime($item->end_time_morning_shift) > strtotime($timeMorOut)
                    && strtotime($item->end_time_morning_shift) < strtotime($timeAfterIn)) {
                    $timeOut = $timeMorOut;
                } else {
                    $timeOut = $item->end_time_morning_shift;
                }
            }
        } elseif (!empty($item->start_time_morning_shift)
            && empty($item->end_time_morning_shift)
            && !empty($item->start_time_afternoon_shift)) {
            if (strtotime($item->start_time_afternoon_shift) <= strtotime($timeAfterIn)) {
                $timeOut = $timeMorOut;
            } else {
                $timeOut = $item->start_time_afternoon_shift;
            }
        } else {
            $timeOut = '';
        }
        return [
            'timeIn' => $timeIn,
            'timeOut' => $timeOut,
        ];
    }

    /**
     * get time min 8:00 and max 17:30
     * @param  [array] $timeWorking [time In, Out of Emp]
     * @param  [array] $timeWork
     * @return [type]
     */
    public static function getTimeInOutWork($timeWorking, $timeWork)
    {
        $timeAfterOut = $timeWork['afternoonOutSetting']->format('H:i');
        $timeAfterIn = $timeWork['afternoonInSetting']->format('H:i');
        $timeMorIn = $timeWork['morningInSetting']->format('H:i');
        $timeMorOut = $timeWork['morningOutSetting']->format('H:i');

        if (!empty($timeWorking['timeIn'])) {
            if (strtotime($timeWorking['timeIn']) < strtotime($timeMorIn)) {
                $timeIn = $timeMorIn;
            } else {
                if (strtotime($timeWorking['timeIn']) >= strtotime($timeMorOut)
                    && strtotime($timeWorking['timeIn']) <= strtotime($timeAfterIn)) {
                    $timeIn = $timeAfterIn;
                } else {
                    $timeIn = $timeWorking['timeIn'];
                }
            }
        } else {
            $timeIn = '';
        }

        if (!empty($timeWorking['timeOut'])) {
            if (strtotime($timeWorking['timeOut']) > strtotime($timeAfterOut) || $timeWorking['timeOut'] == '00:00') {
                $timeOut = $timeAfterOut;
            } else {
                if (strtotime($timeWorking['timeOut']) >= strtotime($timeMorOut)
                    && strtotime($timeWorking['timeOut']) <= strtotime($timeAfterIn)) {
                    $timeOut = $timeMorOut;
                } else {
                    $timeOut = $timeWorking['timeOut'];
                }
            }
        } else {
            $timeOut = '';
        }

        return [
            'timeIn' => $timeIn,
            'timeOut' => $timeOut,
        ];
    }

    public static function scheckSupplement($date, $supplement, $timeWork, $teamCodePrefix)
    {
        $dateTimekeeping = Carbon::parse($date);

        foreach ($supplement as $key => $sup) {
            $supplementDateStart = Carbon::createFromFormat('Y-m-d H:i:s', $sup->date_start);
            $supplementDateEnd = Carbon::createFromFormat('Y-m-d H:i:s', $sup->date_end);
            if (strtotime($dateTimekeeping->format("Y-m-d")) >= strtotime($supplementDateStart->format('Y-m-d')) &&
                strtotime($dateTimekeeping->format("Y-m-d")) <= strtotime($supplementDateEnd->format('Y-m-d'))) {
                  return TimekeepingController::getDiffTimesOfTimeKeeping($supplementDateStart, $supplementDateEnd, $dateTimekeeping, $teamCodePrefix, $timeWork);
            }
        }
        return [];
    }

    public static function scheckLeaveDay($date, $leaveDay, $timeWork, $timeKeepingTableId, $employeeId)
    {
        $dateTimekeeping = Carbon::parse($date);
        $data[$employeeId] = [];
        foreach ($leaveDay as $key => $leave) {
            $leaveDayDateStart = Carbon::createFromFormat('Y-m-d H:i:s', $leave->date_start);
            $leaveDayDateEnd = Carbon::createFromFormat('Y-m-d H:i:s', $leave->date_end);
            if (strtotime($dateTimekeeping->format("Y-m-d")) >= strtotime($leaveDayDateStart->format('Y-m-d')) &&
                strtotime($dateTimekeeping->format("Y-m-d")) <= strtotime($leaveDayDateEnd->format('Y-m-d'))) {
                $data[$leave->creator_id][] = [
                    'start' => $leaveDayDateStart,
                    'end' => $leaveDayDateEnd,
                    'number_days_off' => $leave->number_days_off,
                    'status' => $leave->status,
                ];
            }
        }
        return $data;
    }

    /**
     * [calculateTimeWokingTwo description]
     * @param  [object] $item       [timekeeping]
     * @param  [array] $timeInWork [8:00 - 17:30, time In, Out]
     * @param  [array] $timeWork   [timeWork of emp]
     * @param  [int] $empId
     * @param  [date] $date
     * @return [type]
     */
    public static function calculateTimeWokingTwo($data, $item, $timeInWork, $timeWork)
    {
        $hasSupp = $item->has_supplement;

        $timeIn = $timeInWork['timeIn'];
        $timeOut = $timeInWork['timeOut'];
        list($hourIn, $minuteIn) = explode(':', $timeIn);
        list($hourOut, $minuteOut) = explode(':', $timeOut);

        $timeAfterOut = $timeWork['afternoonOutSetting']->format('H:i');
        $timeAfterIn = $timeWork['afternoonInSetting']->format('H:i');
        $timeMorIn = $timeWork['morningInSetting']->format('H:i');
        $timeMorOut = $timeWork['morningOutSetting']->format('H:i');

        if ($hasSupp) {
            if ($hasSupp == ManageTimeConst::HAS_BUSINESS_TRIP_MORNING) {
                $timeIn = $timeMorIn;
                if (strtotime($timeOut) <= strtotime($timeAfterIn)) {
                    $timeOut = $timeMorOut;
                }
                $data['timeIn'] = $timeMorIn;
            } elseif ($hasSupp == ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON) {
                if (strtotime($timeIn) >= strtotime($timeMorOut)) {
                    $timeIn = $timeAfterIn;
                }
                $timeOut = $timeAfterOut;
                if (!empty($data['timeIn']) && !empty($data['timeOut']) && strtotime($data['timeOut']) < strtotime($timeAfterOut)) {
                    $data['timeOut']= $timeAfterOut;
                }
            } else {

            }
        }
        list($hourIn, $minuteIn) = explode(':', $timeIn);
        list($hourOut, $minuteOut) = explode(':', $timeOut);
        $time = ($hourOut - $hourIn) * 60 + $minuteOut - $minuteIn;
        $timeLunch = ManageTimeView::getLunchBreak($hourIn, $hourOut, $timeWork);
        $time < 0 ? 0 : $time;
        $data['timeWork'] = ($time - $timeLunch) / 60 + (float)$data['timeWork'];
        return $data;
    }

    /**
     * function called when register leave day 1/4
     * @param  [type] $leaves   [description]
     * @param  [type] $timeIn   [description]
     * @param  [type] $timeOut  [description]
     * @param  [type] $timeWork [description]
     * @return [type]           [description]
     */
    public static function timeWorkSuppLeave($leaves, $timeIn, $timeOut, $timeWork)
    {
        list($hourIn, $minuteIn) = explode(':', $timeIn);
        list($hourOut, $minuteOut) = explode(':', $timeOut);
        $timeLeave = 0;
        $note = '';
        foreach ($leaves as $key => $leave) {
            $timeStart = $leave['start']->format('H:i');
            $timeEnd = $leave['end']->format('H:i');
            if ((strtotime($timeIn) < strtotime($timeStart) && strtotime($timeOut) > strtotime($timeEnd))
                || (strtotime($timeIn) == strtotime($timeStart) && strtotime($timeOut) == strtotime($timeEnd))) {
                $timeLeave = - $leave['number_days_off'] * 8 * 60 + $timeLeave;
                $note = 'P/4';
            } elseif (strtotime($timeIn) >= strtotime($timeStart)
                && strtotime($timeIn) < strtotime($timeEnd)
                && strtotime($timeOut) > strtotime($timeEnd)) {
                list($hourOutLeave, $minuteOutLeave) = explode(':', $timeEnd);
                $timeLeave = - ($hourOutLeave - $hourIn) * 60 - $minuteOutLeave + $minuteIn + $timeLeave;
                $timeIn = $timeEnd;
            } elseif (strtotime($timeIn) < strtotime($timeStart) && strtotime($timeOut) <= strtotime($timeEnd)) {
                list($hourInLeave, $minuteInLeave) = explode(':', $timeStart);
                $timeLeave = - ($hourOut - $hourInLeave) * 60 - $minuteOut + $minuteInLeave + $timeLeave;
                $timeOut = $timeStart;
            } else {
            }
        }
        return [
            'timeLeave' => $timeLeave,
            'timeIn' => $timeIn,
            'timeOut' => $timeOut,
            'note' => $note,
        ];
    }

    /**
     * check exists year-month in timeekeeping
     * @param  [date]  $yearMonth [Y-m]
     * @return boolean
     */
    public static function isTimekeepingDate($yearMonth)
    {
        $tblTimekeeping = self::getTableName();
        $tblTimekeepingTable = TimekeepingTable::getTableName();
        $collection = self::select(
            "{$tblTimekeeping}.id"
        )
        ->join("{$tblTimekeepingTable}", "{$tblTimekeepingTable}.id", '=', "{$tblTimekeeping}.timekeeping_table_id")
        ->whereRaw(DB::raw("DATE_FORMAT(manage_time_timekeepings.timekeeping_date, '%Y-%m') = '" . $yearMonth . "'"))
        ->whereNull("{$tblTimekeepingTable}.deleted_at")
        ->first();
        if ($collection) {
            return true;
        }
        return false;
    }

    /**
     * calculateTimeAutoOT
     * @param  [string] $timeOut       [H:i]
     * @param  [carbon] $timeWorkAfter
     * @return [int]
     */
    public static function calculateTimeAutoOT($timeOut, $timeWorkAfter)
    {
        //$timeWorkAfter = Carbon::createFromFormat('H:i', $timeWorkAfter->hour + 1 . ':' . $timeWorkAfter->minute);
        $timeOutAfter = $timeWorkAfter->format("H:i");
        if ($timeOut == '00:00') {
            $timeOut = '23:59';
        }
        if (strtotime($timeOut) > strtotime($timeOutAfter)) {
            return static::calculateTime($timeOutAfter, $timeOut);
        }
        return 0;
    }

    /**
     * [calculateTime]
     * @param  [string] $timeIn  [H:i]
     * @param  [string] $timeOut [H:i]
     * @return [int]
     */
    public static function calculateTime($timeIn, $timeOut)
    {
        if (empty((float)$timeIn) || empty((float)$timeOut)) {
            return 0;
        }
        $arrIn = explode(':', $timeIn);
        $arrOut = explode(':', $timeOut);
        return round((($arrOut[0] * 60 + $arrOut[1]) - ($arrIn[0] * 60 + $arrIn[1])) / 60, 2);
    }

    /**
     * [getMaxTimekeepingDate description]
     * @param  [array] $empIds
     * @return [type]
     */
    public static function getMaxTimekeepingDate($empIds)
    {
        $tblTimekeepingTable = TimekeepingTable::getTableName();
        $tblTimekeeping = self::getTableName();

        return self::select(
            "{$tblTimekeeping}.id",
            "{$tblTimekeeping}.timekeeping_date"
        )
        ->join("{$tblTimekeepingTable}", "{$tblTimekeepingTable}.id", '=', "{$tblTimekeeping}.timekeeping_table_id")
        ->whereIn("employee_id", $empIds)
        ->whereNull("{$tblTimekeepingTable}.deleted_at")
        ->orderBy("timekeeping_date", "DESC")
        ->first();
    }

    /**
     * @param $tKTableId
     * @return mixed
     */
    public function getTimekeepingByTKTableTd($tKTableId)
    {
        return self::where('timekeeping_table_id', $tKTableId)->get();
    }

    /**
     * @param $tkTableId
     * @param $empIds
     * @param string $dateMin
     * @param string $dateMax
     * @return mixed
     */
    public function getManageTimeKeeing($tkTableId, $empIds, $dateMin = '', $dateMax = '')
    {
        $timeKeepingList = Timekeeping::select(
            'manage_time_timekeepings.*',
            DB::raw('date(employees.join_date) as join_date'),
            DB::raw('date(employees.trial_date) as trial_date'),
            DB::raw('date(employees.offcial_date) as offcial_date'),
            DB::raw('date(employees.leave_date) as leave_date'),
            'manage_time_timekeeping_tables.type as contract_type',
            'manage_time_timekeeping_tables.date_max_import'
        )
            ->join('employees', 'employees.id', '=', 'manage_time_timekeepings.employee_id')
            ->join('manage_time_timekeeping_tables', 'manage_time_timekeeping_tables.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
            ->where('timekeeping_table_id', $tkTableId)
            ->whereIn('manage_time_timekeepings.employee_id', $empIds);
        if ($dateMin) {
            $timeKeepingList->whereDate('manage_time_timekeepings.timekeeping_date', '>=', $dateMin);
        }
        if ($dateMax) {
            $timeKeepingList->whereDate('manage_time_timekeepings.timekeeping_date', '<=', $dateMax);
        }
        return $timeKeepingList->get();
    }

    /**
     * @param $tkTableId
     * @param $empIds
     * @param array $teamAll
     * @return mixed
     */
    public function getTKDetail($tkTableId, $empIds, $teamAll = [])
    {
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblTK = Timekeeping::getTableName();

        $collection = Timekeeping::select(
            "{$tblTK}.*",
            'teams.code',
            'mtkTable.type as contract_type',
            'mtkTable.date_max_import',
            DB::raw('date(employees.join_date) as join_date'),
            DB::raw('date(employees.trial_date) as trial_date'),
            DB::raw('date(employees.leave_date) as leave_date'),
            DB::raw('date(employees.offcial_date) as offcial_date')
        )
        ->join('employees', 'employees.id', '=', "{$tblTK}.employee_id")
        ->join('manage_time_timekeeping_tables as mtkTable', 'mtkTable.id', '=', "{$tblTK}.timekeeping_table_id")
        ->join('teams', 'teams.id', '=', 'mtkTable.team_id')
        ->join('employee_works', 'employee_works.employee_id', '=', 'employees.id')
        ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", '=', "{$tblTK}.employee_id")
        ->where('timekeeping_table_id', $tkTableId)
        ->whereIn("{$tblTK}.employee_id", $empIds);
        if ($teamAll) {
            $collection->whereIn("{$tblTeamMember}.team_id", $teamAll);
        }
        return $collection->get();
    }

    /**
     * get timekeeping of employees by id
     * @param $dateStart
     * @param $dateEnd
     * @param $empIds
     * @return mixed
     */
    public function getTimekeepingByEmpId($dateStart, $dateEnd, $empIds)
    {
        return self::select(
            "manage_time_timekeeping_tables.id as idTable",
            "manage_time_timekeepings.id",
            "timekeeping_table_id",
            "employee_id",
            "timekeeping_date",
            "start_time_morning_shift",
            "end_time_morning_shift",
            "start_time_afternoon_shift",
            "end_time_afternoon_shift",
            "late_start_shift",
            "early_mid_shift",
            "late_mid_shift",
            "early_end_shift",
            "has_supplement",
            "register_supplement_number",
            "has_leave_day",
            "register_leave_has_salary",
            "has_leave_day_no_salary",
            "register_ot_has_salary",
            "timekeeping",
            "timekeeping_number",
            "timekeeping_number_register",
            "employees.name",
            "sign_fines"
        )
            ->leftJoin('employees', 'employees.id', '=', 'manage_time_timekeepings.employee_id')
            ->leftJoin('manage_time_timekeeping_tables', 'manage_time_timekeeping_tables.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
            ->where('timekeeping_date', '>=', $dateStart)
            ->where('timekeeping_date', '<=', $dateEnd)
            ->whereIn('employee_id', $empIds)
            ->whereNull('manage_time_timekeeping_tables.deleted_at')
            ->groupBy('manage_time_timekeepings.id')
            ->orderBy('manage_time_timekeeping_tables.id', 'DESC')
            ->orderBy('timekeeping_date', 'ASC')
            ->get();
    }

    /**
     * get array name leave day no salary when have holiday
     * @return array
     */
    public function getArrHolidayNoSalary()
    {
        return [
            self::NOT_SALARY_1,
        ];
    }

    /**
     * get information leave reason, no salary holiday
     *
     * @return Collection
     */
    public function getLeaveReasonNoSalaryHoliday()
    {
        $arrNoSH = $this->getArrHolidayNoSalary();
        return LeaveDayReason::select(
            'id',
            'name',
            'type'
        )
        ->whereIn('name', $arrNoSH)
        ->get();
    }

    /**
     * get array id leave day reason no salary holiday
     * @return array
     */
    public function getIdLeaveReasonNoSalaryHolidays()
    {
        $noSalaryHoliday = $this->getLeaveReasonNoSalaryHoliday();
        if (!count($noSalaryHoliday)) {
            return [];
        }
        return $noSalaryHoliday->lists('id')->toArray();
    }

    /**
     * get time in out of employee by table id
     *
     * @param  array $arrBranch
     * @param  date $date
     * @param  array $empIds
     * @return collection
     */
    public function getTimeInOutByBranch($arrBranch, $date, $empIds = [])
    {
        $collection = self::select(
            "manage_time_timekeepings.id",
            "timekeeping_table_id",
            "employee_id",
            "employees.email",
            "timekeeping_date",
            "start_time_morning_shift_real",
            "end_time_morning_shift",
            "start_time_afternoon_shift",
            "end_time_afternoon_shift"
        )
        ->leftJoin('employees', 'employees.id', '=', 'manage_time_timekeepings.employee_id')
        ->leftJoin('manage_time_timekeeping_tables as tkTable', 'tkTable.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
        ->leftJoin('teams', 'teams.id', '=', "tkTable.team_id")
        ->whereIn('teams.branch_code', $arrBranch)
        ->whereNull('tkTable.deleted_at')
        ->whereNull('manage_time_timekeepings.deleted_at')
        ->where('timekeeping_date', '=', $date);
        if ($empIds) {
            $collection->whereIn('employee_id', $empIds);
        }
        return $collection->groupBy('manage_time_timekeepings.id')
            ->orderBy('tkTable.id', 'DESC')
            ->get();
    }

    /* get timekeeping of employees by date
     * @param $dateStart
     * @param $dateEnd
     * @param $empIds
     * @return collection
     */
    public function getTimekeepingByDate($dateStart, $dateEnd, $empIds)
    {
        return Timekeeping::select(
            'manage_time_timekeepings.*',
            'teams.code',
            'manage_time_timekeeping_tables.type as contract_type',
            'manage_time_timekeeping_tables.date_max_import',
            DB::raw('date(employees.join_date) as join_date'),
            DB::raw('date(employees.trial_date) as trial_date'),
            DB::raw('date(employees.leave_date) as leave_date'),
            DB::raw('date(employees.offcial_date) as offcial_date')
        )
        ->join('employees', 'employees.id', '=', 'manage_time_timekeepings.employee_id')
        ->join('manage_time_timekeeping_tables', 'manage_time_timekeeping_tables.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
        ->join('teams', 'teams.id', '=', 'manage_time_timekeeping_tables.team_id')
        ->join('timekeeping_rate', 'timekeeping_rate.id', '=', 'manage_time_timekeepings.tk_rate_id')
        ->where('timekeeping_date', '>=', $dateStart)
        ->where('timekeeping_date', '<=', $dateEnd)
        ->whereNull('manage_time_timekeeping_tables.deleted_at')
        ->whereIn('employee_id', $empIds)
        ->orderBy('manage_time_timekeeping_tables.id', 'ASC')
        ->orderBy('timekeeping_date', 'ASC')
        ->get();
    }

    //======== start update timekeeping sign ============
    public function getDataSingTimeKeeping($empIds, $arrDate, $tkTableId, $compensationDays, $arrHolidays)
    {
        $dataInsertSignTK = [];
        $dateMin = $arrDate[0];
        $dateMax = $arrDate[1];

        $tkList = $this->getManageTimeKeeing($tkTableId, $empIds, $dateMin, $dateMax);
        if (!count($tkList)) {
            return $dataInsertSignTK;
        }
        foreach ($tkList as $items) {
            $key = $items->employee_id . '-' . $items->timekeeping_date;
            $dataInsert['employee_id'] = $items->employee_id;
            $dataInsert['timekeeping_date'] = $items->timekeeping_date;
            $signFines = ManageTimeCommon::getTimekeepingSign($items, '', $compensationDays, $arrHolidays);
            if ($items->sign_fines == '-' &&
                ($signFines[0] == ' -' || $signFines[0] == '-')) {
                continue;
            }
            $jsonSF['sign'] = $signFines[0];
            $jsonSF['fines'] = $signFines[1];
            $dataInsert['sign_fines'] = json_encode($jsonSF);
            $dataInsertSignTK[$key] = $dataInsert;
        }
        return $dataInsertSignTK;
    }
    /**
     * @param $empIds
     * @param $arrDate
     * @param $tkTableId
     * @param $compensationDays
     * @param $arrHolidays
     */
    public function updateSignTimekeeping($empIds, $arrDate, $tkTableId, $compensationDays, $arrHolidays)
    {
        $dataInsertSignTK = $this->getDataSingTimeKeeping($empIds, $arrDate, $tkTableId, $compensationDays, $arrHolidays);

        if ($dataInsertSignTK) {
            $table = Timekeeping::getTableName();
            $final = [];
            $column = [];
            foreach ($dataInsertSignTK as $key => $val) {
                $employeeId = $val['employee_id'];
                $timekeepingDate = $val['timekeeping_date'];
                foreach (array_keys($val) as $field) {
                    if ($field == 'employee_id' || $field == 'timekeeping_date' || $field == 'updated_at') {
                        continue;
                    }
                    $column[] = $field;
                    $value = (is_null($val[$field]) ? 'NULL' : "'" . $val[$field] . "'");
                    $final[$field][] = 'WHEN `timekeeping_table_id` = "' . $tkTableId . '" AND `employee_id` = "' . $employeeId . '" AND `timekeeping_date` = "' . $timekeepingDate . '" THEN ' . $value . ' ';
                }
            }
            $cases = '';
            foreach ($final as $k => $v) {
                $cases .=  '`'. $k.'` = (CASE '. implode("\n", $v) . "\n" . 'ELSE `'.$k.'` END), ';
            }
            $query = 'UPDATE ' . $table . ' SET '. substr($cases, 0, -2) . ' WHERE `timekeeping_table_id` = "' . $tkTableId . '"';
            DB::statement($query);
        }
        return;
    }
    //======== end update timekeeping sign ============

    /**
     * get time in out of employee by data employee
     *
     * @param  array $dataEmp [empid =>[startDate, endDate]]
     * @return collection
     */
    public function getDataTimeInOutByListEmp($dataEmp)
    {
        return static::select(
            "manage_time_timekeepings.id",
            "timekeeping_table_id",
            "employee_id",
            "employees.email",
            "timekeeping_date",
            DB::raw("(DATE_FORMAT(timekeeping_date, '%d/%m/%Y')) as timekeeping_date_format"),
            "start_time_morning_shift_real",
            "end_time_morning_shift",
            "start_time_afternoon_shift",
            "end_time_afternoon_shift"
        )
        ->leftJoin('employees', 'employees.id', '=', 'manage_time_timekeepings.employee_id')
        ->leftJoin('manage_time_timekeeping_tables as tkTable', 'tkTable.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
        ->where(function($query) use ($dataEmp) {
            foreach($dataEmp as $empId => $arrDate) {
                foreach($arrDate as $dataDate) {
                    $query->orWhere(function($q) use($empId, $dataDate) {
                        $q->where('employee_id', $empId)
                            ->whereDate('timekeeping_date' , '>=', $dataDate[0])
                            ->whereDate('timekeeping_date' , '<=', $dataDate[1]);
                        });
                }
            }
        })
        ->where('tkTable.lock_up', TimekeepingTable::OPEN_LOCK_UP)
        ->whereNull('tkTable.deleted_at')
        ->groupBy('manage_time_timekeepings.id', 'employee_id', 'timekeeping_date')
        ->orderBy('tkTable.id', 'ASC')
        ->orderBy('employee_id', 'ASC')
        ->orderBy('timekeeping_date', 'ASC')
        ->get();
    }
    
    /**
     * sqlFineMoneyBranch
     *
     * @return string
     */
    public function sqlFinesMoneyBranch()
    {
        $objTeam = new Team();
        $objManageTimeConst= new ManageTimeConst();
        $arrBranch = $objTeam->getAllBranch();
        $sqlFilterLateMinute = $this->sqlFilterLateMinute();
        $minutesPerBlock = ManageTimeConst::TIME_LATE_IN_PER_BLOCK;
        $result = ' SUM(CASE ';
        foreach($arrBranch as $branch_code) {
            $fineMoney = $objManageTimeConst->getFinesBlockBranch($branch_code);
            $result .= " WHEN teams.branch_code = '{$branch_code}' THEN 
                CASE WHEN " . $sqlFilterLateMinute . " THEN CEILING(tk.late_start_shift / {$minutesPerBlock}) * {$fineMoney} ELSE 0 END ";
        }
        $result .= " ELSE CASE 
                WHEN " . $sqlFilterLateMinute ." THEN CEILING(tk.late_start_shift / {$minutesPerBlock}) * ".  ManageTimeConst::FINES_LATE_IN_PER_BLOCK . "
                ELSE 0
                END
            END) as total_fine_money";
        return $result;
    }
        
    /**
     * sqlFilterLateMinute
     *
     * @return void
     */
    public function sqlFilterLateMinute()
    {
        $maxLate = ManageTimeConst::MAX_TIME_LATE_IN_EARLY_OUT;
        return " tk.late_start_shift > 0
            AND tk.late_start_shift <= {$maxLate}
            AND ((register_business_trip_number = 0
            AND register_supplement_number = 0
            AND register_leave_has_salary = 0
            AND register_leave_no_salary = 0)
            OR ((register_business_trip_number + register_supplement_number + register_leave_has_salary + register_leave_no_salary) < 1
            AND has_business_trip != " . ManageTimeConst::HAS_BUSINESS_TRIP_MORNING . "
            AND has_leave_day != " . ManageTimeConst::HAS_LEAVE_DAY_MORNING . "
            AND has_leave_day_no_salary != " . ManageTimeConst::HAS_LEAVE_DAY_MORNING . "
            AND has_supplement != " . ManageTimeConst::HAS_SUPPLEMENT_MORNING . "))";
    }

    /**
     * get late minute start shift (M1)
     * 
     * @param date $dateStart Y-m-d
     * @param date $dateEnd Y-m-d
     * @param array $empIds
     * @param string $route
     * @param  boolean $isExport
     * @return mixed
     */
    public function getTimekeepingMinuteLate($dateStart, $dateEnd, $empIds, $isExport = false, $route = null)
    {
        $minutesPerBlock = ManageTimeConst::TIME_LATE_IN_PER_BLOCK;
        $sqlFilterLateMinute = $this->sqlFilterLateMinute();

        $collection = DB::table(DB::raw("(SELECT 
                tk.employee_id,
                SUM(CASE WHEN " . $sqlFilterLateMinute . " THEN 1 ELSE 0 END) AS 'count_late_minute',
                SUM(CASE WHEN " . $sqlFilterLateMinute . " THEN tk.late_start_shift ELSE 0 END) AS sum_late_minute,
                SUM(time_over) AS time_over,
                timekeeping_table_id,
                SUM(CASE WHEN " . $sqlFilterLateMinute . " THEN CEILING(late_start_shift / {$minutesPerBlock}) ELSE 0 END ) AS total_late_minute_block," 
                . $this->sqlFinesMoneyBranch() . "
            FROM manage_time_timekeepings AS tk
            JOIN manage_time_timekeeping_tables AS tk_table ON tk_table.id = tk.timekeeping_table_id
            JOIN teams ON teams.id = tk_table.team_id
            WHERE tk.deleted_at IS NULL 
                AND tk.timekeeping_table_id IN 
                    (SELECT id
                    FROM manage_time_timekeeping_tables AS tk_table1
                    WHERE tk_table1.deleted_at IS NULL
                        AND tk_table1.start_date <= '{$dateEnd}'
                        AND tk_table1.end_date >= '{$dateStart}'
                    )
            GROUP BY tk.employee_id
        ) as tbl_lm"))
        ->join('employees', 'employees.id', '=', 'tbl_lm.employee_id')
        ->leftJoin('team_members as tm', 'tm.employee_id', '=', 'tbl_lm.employee_id')
        ->leftJoin('teams', 'teams.id', '=', 'tm.team_id')
        ->select(
            'tbl_lm.*',
            'employees.name as employee_name',
            'employees.employee_code',
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(teams.name)) as team_name")
        )
        ->groupBy('tbl_lm.employee_id');
        if ($empIds) {
            $collection->whereIn('tbl_lm.employee_id', $empIds);
        }
        $filter = Form::getFilterData(null, null, $route);
        if (isset($filter['except2']) && isset($filter['compare'])) {
            $arrExce = $filter['except2'];
            foreach($arrExce as $key => $value) {
                $value = trim($value);
                $arrSearh = explode('.', $key);
                if (count($arrSearh) > 1) {
                    $keyCompare = $key . '_compare';
                    $compare = isset($filter['compare'][$keyCompare]) ? $filter['compare'][$keyCompare] : '=';
                    $collection->where("{$arrSearh[1]}", "{$compare}", $value);
                }
            }
        }
        if (!$route) {
            $route = route('manage_time::division.late-minute-report') . '/';
            
        }
        $pager = Config::getPagerData($route, ['order' => "tbl_lm.time_over", 'dir' => 'ASC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], $route, 'LIKE');

        if ($isExport) {
            return $collection->get();
        } else {
            return self::pagerCollection($collection, $pager['limit'], $pager['page']);
        }
    }

    /**
     * Get all employee_id of table timekeeping
     *
     * @param type $tableId     id of table timekeeping
     * @return array
     */
    public static function getEmployeesIdOfTimekeeping($tableId)
    {
        return self::where('timekeeping_table_id', $tableId)
                ->select(DB::raw('DISTINCT employee_id'))
                ->get()
                ->lists('employee_id')
                ->toArray();
    }
}
