<?php

namespace Rikkei\ManageTime\Model;

use DB;
use Carbon\Carbon;
use Exception;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\Core\View\PaginatorHelp;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\Model\TimekeepingTable;

class TimekeepingAggregate extends CoreModel
{
    use SoftDeletes;

    protected $table = 'manage_time_timekeeping_aggregates';

    /**
     * [getTimekeepingAggregateCol: get timekeeping aggregate]
     * @param  [array] $filter
     * @return [collection]
     */
    public static function getTimekeepingAggregateCol($timekeepingTableId, $export = false, $dataFilter = [])
    {
        $tblTimekeepingAggregate = self::getTableName();
        $tblTimekeepingTable = TimekeepingTable::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblRole = Role::getTableName();

        $collection = self::select(
            "{$tblEmployee}.id",
            "{$tblEmployee}.employee_card_id",
            "{$tblEmployee}.employee_code",
            "{$tblEmployee}.name as employee_name",
            "{$tblEmployee}.offcial_date",
            "{$tblTimekeepingAggregate}.employee_id",
            "{$tblTimekeepingAggregate}.total_official_working_days",
            "{$tblTimekeepingAggregate}.total_trial_working_days",
            "{$tblTimekeepingAggregate}.total_official_ot_weekdays",
            "{$tblTimekeepingAggregate}.total_trial_ot_weekdays",
            "{$tblTimekeepingAggregate}.total_official_ot_weekends",
            "{$tblTimekeepingAggregate}.total_trial_ot_weekends",
            "{$tblTimekeepingAggregate}.total_official_ot_holidays",
            "{$tblTimekeepingAggregate}.total_trial_ot_holidays",
            "{$tblTimekeepingAggregate}.total_ot_no_salary",
            "{$tblTimekeepingAggregate}.total_number_late_in",
            "{$tblTimekeepingAggregate}.total_number_early_out",
            "{$tblTimekeepingAggregate}.total_official_business_trip",
            "{$tblTimekeepingAggregate}.total_trial_business_trip",
            "{$tblTimekeepingAggregate}.total_official_leave_day_has_salary",
            "{$tblTimekeepingAggregate}.total_trial_leave_day_has_salary",
            "{$tblTimekeepingAggregate}.total_leave_day_no_salary",
            "{$tblTimekeepingAggregate}.total_official_supplement",
            "{$tblTimekeepingAggregate}.total_trial_supplement",
            "{$tblTimekeepingAggregate}.total_official_holiay",
            "{$tblTimekeepingAggregate}.total_trial_holiay",
            "{$tblTimekeepingAggregate}.total_late_start_shift",
            "{$tblTimekeepingAggregate}.total_late_mid_shift",
            "{$tblTimekeepingAggregate}.total_early_mid_shift",
            "{$tblTimekeepingAggregate}.total_early_end_shift",
            "{$tblTimekeepingAggregate}.number_com_off",
            "{$tblTimekeepingAggregate}.number_com_tri",
            "{$tblTimekeepingAggregate}.total_working_officail",
            "{$tblTimekeepingAggregate}.total_working_trial",
            "{$tblTimekeepingAggregate}.total_official_leave_basic_salary",
            "{$tblTimekeepingAggregate}.total_trial_leave_basic_salary",
            DB::raw("({$tblTimekeepingAggregate}.total_early_mid_shift + {$tblTimekeepingAggregate}.total_early_end_shift) AS total_early_shift"),
            DB::raw("({$tblTimekeepingAggregate}.total_late_start_shift + {$tblTimekeepingAggregate}.total_late_mid_shift) AS total_late_shift"),
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$tblTeam}.name) ORDER BY {$tblRole}.role DESC SEPARATOR '; ') as role_name")
        );

        // join employee
        $collection->join(
            "{$tblEmployee}",
            function ($join) use ($tblTimekeepingAggregate, $tblEmployee)
            {
                $join->on("{$tblTimekeepingAggregate}.employee_id", '=', "{$tblEmployee}.id");
            }
        );

        // join timekeeping table
        $collection->join(
            "{$tblTimekeepingTable}",
            function ($join) use ($tblTimekeepingTable, $tblTimekeepingAggregate)
            {
                $join->on("{$tblTimekeepingTable}.id", '=', "{$tblTimekeepingAggregate}.timekeeping_table_id");
            }
        );

        // join team member
        $collection->leftJoin(
            "{$tblTeamMember}",
            function ($join) use ($tblTeamMember, $tblEmployee)
            {
                $join->on("{$tblTeamMember}.employee_id", '=', "{$tblEmployee}.id");
            }
        );

        // join team
        $collection->leftJoin(
            "{$tblTeam}",
            function ($join) use ($tblTeam, $tblTeamMember)
            {
                $join->on("{$tblTeam}.id", '=', "{$tblTeamMember}.team_id");
            }
        );

        // join role
        $collection->leftJoin(
            "{$tblRole}",
            function ($join) use ($tblRole, $tblTeamMember)
            {
                $join->on("{$tblRole}.id", '=', "{$tblTeamMember}.role_id");
                $join->on("{$tblRole}.special_flg", '=', DB::raw(Role::FLAG_POSITION));
            }
        );
        try {
            if (isset($dataFilter["{$tblTeamMember}.team_id"])) {
                $collection->where("{$tblTeamMember}.team_id", $dataFilter["{$tblTeamMember}.team_id"]);
            }
        } catch (Exception $ex) {
            return null;
        }

        $collection->where("{$tblTimekeepingAggregate}.timekeeping_table_id", $timekeepingTableId)
            ->groupBy("{$tblTimekeepingAggregate}.employee_id");

        $pager = Config::getPagerData();
        $collection = $collection->orderBy($pager['order'], $pager['dir']);

        //Filter
        $route = route('manage_time::timekeeping.timekeeping-aggregate', $timekeepingTableId) . '/';
        $collection = self::filterGrid($collection, [], $route, 'LIKE');

        //Filter except
        $collection = static::filterExcept($collection, $timekeepingTableId, $route);

        //If filter total official salary or total trial salary then return all
        $officialSalaryFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_official_salary");
        $trialSalaryFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_trial_salary");
        if (!empty($officialSalaryFilter) || $officialSalaryFilter === '0'
                || !empty($trialSalaryFilter) || $trialSalaryFilter === '0') {
            return $collection->get();
        }

        if (!$export) {
            $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
            return $collection;
        }

        return $collection->get();
    }

    /**
     * Filter time keeping aggregate with filter[except]
     *
     * @param TimekeepingAggregate collection $collection
     *
     * @return TimekeepingAggregate collection
     */
    public static function filterExcept($collection, $timekeepingTableId, $route)
    {
        $tblTimekeepingAggregate = self::getTableName();

        $dataFilter = isset(Form::getFilterData(null, null, $route)['except2']) ? Form::getFilterData()['except2'] : null;
        $dataCompare = Form::getFilterData(null, null, $route)['compare'];

        if ($dataFilter) {
            foreach ($dataFilter as $field => $value) {
                $collection->where($field, $dataCompare[$field . '_compare'], $value);
            }
        }

        $otWeekdaysFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_ot_weekdays", $route);
        $otWeekdaysFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_ot_weekdays_compare"])
            ? $dataCompare["{$tblTimekeepingAggregate}.total_ot_weekdays_compare"] : '=';
        if (!empty($otWeekdaysFilter) || $otWeekdaysFilter === '0') {
            $collection->whereRaw("(total_official_ot_weekdays + total_trial_ot_weekdays) {$otWeekdaysFilterCompare} ?", [$otWeekdaysFilter]);
        }

        $otWeekendsFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_ot_weekends");
        $otWeekendsFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_ot_weekends_compare"])
            ? $dataCompare["{$tblTimekeepingAggregate}.total_ot_weekends_compare"] : '=';
        if (!empty($otWeekendsFilter) || $otWeekendsFilter === '0') {
            $collection->whereRaw("(total_official_ot_weekends + total_trial_ot_weekends) {$otWeekendsFilterCompare} ?", [$otWeekendsFilter]);
        }

        $otHolidaysFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_ot_holidays");
        $otHolidaysFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_ot_holidays_compare"])
            ? $dataCompare["{$tblTimekeepingAggregate}.total_ot_holidays_compare"] : '=';
        if (!empty($otHolidaysFilter) || $otHolidaysFilter === '0') {
            $collection->whereRaw("(total_official_ot_holidays + total_trial_ot_holidays) {$otHolidaysFilterCompare} ?", [$otHolidaysFilter]);
        }

        $businessWeekdaysFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_business_trip");
        $businessWeekdaysFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_business_trip_compare"])
            ? $dataCompare["{$tblTimekeepingAggregate}.total_business_trip_compare"] : '=';
        if (!empty($businessWeekdaysFilter) || $businessWeekdaysFilter === '0') {
            $collection->whereRaw("(total_official_business_trip + total_trial_business_trip) {$businessWeekdaysFilterCompare} ?", [$businessWeekdaysFilter]);
        }

        $LeaveDayFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_leave_day");
        $LeaveDayFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_leave_day_compare"])
            ? $dataCompare["{$tblTimekeepingAggregate}.total_leave_day_compare"] : '=';
        if (!empty($LeaveDayFilter) || $LeaveDayFilter === '0') {
            $collection->whereRaw("(total_official_leave_day_has_salary + total_trial_leave_day_has_salary) {$LeaveDayFilterCompare} ?", [$LeaveDayFilter]);
        }
        // ==== salary rate ===
        $LeaveDayBasicFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_leave_basic_salary");
        $LeaveDayBasicFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_leave_basic_salary_compare"])
            ? $dataCompare["{$tblTimekeepingAggregate}.total_leave_basic_salary_compare"] : '=';
        if (!empty($LeaveDayBasicFilter) || $LeaveDayBasicFilter === '0') {
            $collection->whereRaw("(total_official_leave_basic_salary + total_trial_leave_basic_salary) {$LeaveDayBasicFilterCompare} ?", [$LeaveDayBasicFilter]);
        }
        // --- search tổng công lương cơ bản ---
        $LeaveDayBasicFilterS = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_leave_basic_salary_s");
        $LeaveDayBasicFilterCompareS = !empty($dataCompare["{$tblTimekeepingAggregate}.total_leave_basic_salary_s_compare"])
            ? $dataCompare["{$tblTimekeepingAggregate}.total_leave_basic_salary_s_compare"] : '=';
        if (!empty($LeaveDayBasicFilterS) || $LeaveDayBasicFilterS === '0') {
            $collection->whereRaw("(total_official_leave_basic_salary + total_trial_leave_basic_salary) {$LeaveDayBasicFilterCompareS} ?", [$LeaveDayBasicFilterS]);
        }
        // ====  end salary rate ===

        $supplementFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_supplement");
        $supplementFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_supplement_compare"])
            ? $dataCompare["{$tblTimekeepingAggregate}.total_supplement_compare"] : '=';
        if (!empty($supplementFilter) || $supplementFilter === '0') {
            $collection->whereRaw("(total_official_supplement + total_trial_supplement) {$supplementFilterCompare} ?", [$supplementFilter]);
        }

        $holidaysFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_holiday");
        $holidaysFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_holiday_compare"]) ?
            $dataCompare["{$tblTimekeepingAggregate}.total_holiday_compare"] : '=';
        if (!empty($holidaysFilter) || $holidaysFilter === '0') {
            $collection->whereRaw("(total_official_holiay + total_trial_holiay) {$holidaysFilterCompare} ?", [$holidaysFilter]);
        }

        $otOfficialFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_official_ot");
        $otOfficialFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_official_ot_compare"]) ?
            $dataCompare["{$tblTimekeepingAggregate}.total_official_ot_compare"] : '=';
        if (!empty($otOfficialFilter) || $otOfficialFilter === '0') {
            $collection->whereRaw("(total_official_ot_weekdays + total_official_ot_weekends + total_official_ot_holidays) {$otOfficialFilterCompare} ?", [$otOfficialFilter]);
        }

        $otTrialFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_trial_ot");
        $otTrialFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_trial_ot_compare"])
            ? $dataCompare["{$tblTimekeepingAggregate}.total_trial_ot_compare"] : '=';
        if (!empty($otTrialFilter) || $otTrialFilter === '0') {
            $collection->whereRaw("(total_trial_ot_weekdays + total_trial_ot_weekends + total_trial_ot_holidays) {$otTrialFilterCompare} ?", [$otTrialFilter]);
        }

        //Filter late shift of time keeping japan
        $otLateFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_late_shift");
        $otLateFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_late_shift_compare"])
            ? $dataCompare["{$tblTimekeepingAggregate}.total_late_shift_compare"] : '=';
        if (!empty($otLateFilter) || $otLateFilter === '0') {
            $collection->whereRaw("(total_late_start_shift + total_late_mid_shift) {$otLateFilterCompare} ?", [$otLateFilter]);
        }

        //Filter early shift of time keeping japan
        $otEarlyFilter = Form::getFilterData('except', "{$tblTimekeepingAggregate}.total_early_shift");
        $otEarlyFilterCompare = !empty($dataCompare["{$tblTimekeepingAggregate}.total_early_shift_compare"])
            ? $dataCompare["{$tblTimekeepingAggregate}.total_early_shift_compare"] : '=';
        if (!empty($otEarlyFilter) || $otEarlyFilter === '0') {
            $collection->whereRaw("(total_early_mid_shift + total_early_end_shift) {$otEarlyFilterCompare} ?", [$otEarlyFilter]);
        }

        return $collection;
    }

    public static function getTimekeepingAggregateByEmp($timekeepingTableId, $employeeId)
    {
        $tblTimekeepingAggregate = self::getTableName();
        $tblTimekeeping = Timekeeping::getTableName();
        $tblTimekeepingTable = TimekeepingTable::getTableName();
        $tblEmployee = Employee::getTableName();

        $timekeepingAggregate = self::select(
            "{$tblEmployee}.offcial_date",
            "{$tblEmployee}.employee_code",
            "{$tblEmployee}.name as employee_name",
            "{$tblTimekeepingAggregate}.employee_id",
            "{$tblTimekeepingAggregate}.total_official_working_days",
            "{$tblTimekeepingAggregate}.total_trial_working_days",
            "{$tblTimekeepingAggregate}.total_official_ot_weekdays",
            "{$tblTimekeepingAggregate}.total_trial_ot_weekdays",
            "{$tblTimekeepingAggregate}.total_official_ot_weekends",
            "{$tblTimekeepingAggregate}.total_trial_ot_weekends",
            "{$tblTimekeepingAggregate}.total_official_ot_holidays",
            "{$tblTimekeepingAggregate}.total_trial_ot_holidays",
            "{$tblTimekeepingAggregate}.total_ot_no_salary",
            "{$tblTimekeepingAggregate}.total_number_late_in",
            "{$tblTimekeepingAggregate}.total_number_early_out",
            "{$tblTimekeepingAggregate}.total_official_business_trip",
            "{$tblTimekeepingAggregate}.total_trial_business_trip",
            "{$tblTimekeepingAggregate}.total_official_leave_day_has_salary",
            "{$tblTimekeepingAggregate}.total_trial_leave_day_has_salary",
            "{$tblTimekeepingAggregate}.total_leave_day_no_salary",
            "{$tblTimekeepingAggregate}.total_official_supplement",
            "{$tblTimekeepingAggregate}.total_trial_supplement",
            "{$tblTimekeepingAggregate}.total_official_holiay",
            "{$tblTimekeepingAggregate}.total_trial_holiay",
            "{$tblTimekeepingAggregate}.total_late_start_shift",
            "{$tblTimekeepingAggregate}.total_early_mid_shift",
            "{$tblTimekeepingAggregate}.total_late_mid_shift",
            "{$tblTimekeepingAggregate}.total_early_end_shift",
            "{$tblTimekeepingAggregate}.number_com_off",
            "{$tblTimekeepingAggregate}.number_com_tri",
            "{$tblTimekeepingAggregate}.total_working_officail",
            "{$tblTimekeepingAggregate}.total_official_leave_basic_salary",
            "{$tblTimekeepingAggregate}.total_trial_leave_basic_salary",
            "{$tblTimekeepingAggregate}.total_working_trial"
        );

        $timekeepingAggregate->join(
            "{$tblTimekeeping}",
            function ($join) use ($tblTimekeepingAggregate, $tblTimekeeping)
            {
                $join->on("{$tblTimekeepingAggregate}.timekeeping_table_id", '=', "{$tblTimekeeping}.timekeeping_table_id");
                $join->on("{$tblTimekeepingAggregate}.employee_id", '=', "{$tblTimekeeping}.employee_id");
            }
        );

        // join timekeeping employee
        $timekeepingAggregate->join(
            "{$tblEmployee}",
            function ($join) use ($tblTimekeepingAggregate, $tblEmployee)
            {
                $join->on("{$tblTimekeepingAggregate}.employee_id", '=', "{$tblEmployee}.id");
            }
        );

        // join timekeeping table
        $timekeepingAggregate->join(
            "{$tblTimekeepingTable}",
            function ($join) use ($tblTimekeepingTable, $tblTimekeepingAggregate)
            {
                $join->on("{$tblTimekeepingTable}.id", '=', "{$tblTimekeepingAggregate}.timekeeping_table_id");
            }
        );

        $timekeepingAggregate = $timekeepingAggregate->where("{$tblTimekeepingAggregate}.timekeeping_table_id", $timekeepingTableId)
            ->where("{$tblTimekeepingAggregate}.employee_id", $employeeId)
            ->first();

        return $timekeepingAggregate;
    }

    public static function filterTotalSalary($route, $collection, $timeKeepingTable, $officialSalaryFilter, $trialSalaryFilter, $isExport =false)
    {
        foreach ($collection as $index => &$item) {
            $item->totalRegisterBusinessTrip = $item->total_official_business_trip + $item->total_trial_business_trip;
            $item->totalLeaveDayHasSalary = $item->total_official_leave_day_has_salary + $item->total_trial_leave_day_has_salary;
            //salary rate
            $item->totalLeaveDayBasic = $item->total_official_leave_basic_salary + $item->total_trial_leave_basic_salary;
            // end salary rate
            $item->totalRegisterSupplement = $item->total_official_supplement + $item->total_trial_supplement;
            $item->totalHoliday = $item->total_official_holiay + $item->total_trial_holiay;
            $item->totalOTWeekdays = $item->total_official_ot_weekdays + $item->total_trial_ot_weekdays;
            $item->totalOTWeekends = $item->total_official_ot_weekends + $item->total_trial_ot_weekends;
            $item->totalOTHolidays = $item->total_official_ot_holidays + $item->total_trial_ot_holidays;
            $item->totalOTOfficial = $item->total_official_ot_weekdays + $item->total_official_ot_weekends + $item->total_official_ot_holidays;
            $item->totalOTTrial = $item->total_trial_ot_weekdays + $item->total_trial_ot_weekends + $item->total_trial_ot_holidays;
            $item->total_num_com = $item->number_com_off + $item->number_com_tri;
            $daysOffInTimeBusiness = ManageTimeView::daysOffInTimeBusiness($item, $timeKeepingTable);
            $totalWorkingToSalary = ManageTimeView::totalWorkingDays($item, $daysOffInTimeBusiness);
            $item->totalWorkingOfficialToSalary = $totalWorkingToSalary['offcial'];
            $item->totalWorkingTrialToSalary = $totalWorkingToSalary['trial'];
            if (!empty($officialSalaryFilter) || $officialSalaryFilter === '0'
                    || !empty($trialSalaryFilter) || $trialSalaryFilter === '0') {
                $officialSalaryFilterCompare = Form::getFilterData('compare', "manage_time_timekeeping_aggregates.total_official_salary_compare", $route);
                $trialSalaryFilterCompare = Form::getFilterData('compare', "manage_time_timekeeping_aggregates.total_trial_salary_compare", $route);

                $officialSalaryFilterCompare = empty($officialSalaryFilterCompare) ? '=' : $officialSalaryFilterCompare;
                $trialSalaryFilterCompare = empty($trialSalaryFilterCompare) ? '=' : $trialSalaryFilterCompare;
                if (!empty($officialSalaryFilter) || $officialSalaryFilter === '0') {
                    if (!CoreView::doComparison($item->totalWorkingOfficialToSalary, $officialSalaryFilterCompare, $officialSalaryFilter)) {
                        $collection->forget($index);
                    }
                }
                if (!empty($trialSalaryFilter) || $trialSalaryFilter === '0') {
                    if (!CoreView::doComparison($item->totalWorkingTrialToSalary, $trialSalaryFilterCompare, $trialSalaryFilter)) {
                        $collection->forget($index);
                    }
                }
            }
        }

        if (!$isExport) {
            if (!empty($officialSalaryFilter) || $officialSalaryFilter === '0'
                    || !empty($trialSalaryFilter) || $trialSalaryFilter === '0') {
                //Pagination result
                $pager = Config::getPagerData();
                $paginatorHelp = new PaginatorHelp();
                $collection = $paginatorHelp->paginate($collection, $pager['limit'], $pager['page']);
            }
        }

        return $collection;
    }

    /**
     * get timekeeping aggregate
     * @param  [date] $date
     * @param  [array] $empIds
     * @return [array]
     */
    public static function getEmpTimekeeping($date, $empIds)
    {
        $date = Carbon::parse($date);
        $year = $date->format("Y");
        $month = $date->format("m");

        $tblTimekeepingTable = TimekeepingTable::getTableName();
        $tblTimekeepingAggregate = self::getTableName();

        return DB::table("{$tblTimekeepingAggregate} as tkAggre")
        ->select(
            "tkAggre.timekeeping_table_id",
            "tkAggre.employee_id",
            "tKTable.year",
            "tKTable.month",
            "tkAggre.total_late_start_shift",
            "tKTable.team_id",
            "tKTable.type"
        )
        ->join("{$tblTimekeepingTable} as tKTable", "tKTable.id", '=', "tkAggre.timekeeping_table_id")
        ->whereNull("tKTable.deleted_at")
        ->where("tKTable.year", '=', $year)
        ->where("tKTable.month", '=', $month)
        ->whereIn("tkAggre.employee_id", $empIds)
        ->groupBy("tkAggre.timekeeping_table_id")
        ->groupBy("tkAggre.employee_id")
        ->get();
    }

    /**
     * get timekeeping aggregate
     * @param  [date] $date
     * @param  boolean $last
     * @return [array]
     */
    public static function getEmpTimekeepingLastFirst($date, $last = true)
    {
        $date = Carbon::parse($date);
        $year = $date->format("Y");
        $month = $date->format("m");
        $dateClone = clone $date;
        $endOfMonth = $dateClone->endOfMonth()->toDateString();

        $tblTimekeepingTable = TimekeepingTable::getTableName();
        $tblTimekeepingAggregate = self::getTableName();

        $collection =  DB::table("{$tblTimekeepingAggregate} as tkAggre")
            ->select(
                "tkAggre.timekeeping_table_id",
                "tkAggre.employee_id",
                "tKTable.year",
                "tKTable.month",
                "tKTable.start_date",
                "tKTable.end_date",
                "tkAggre.total_late_start_shift",
                "tKTable.team_id",
                "tKTable.type"
            )
            ->join("{$tblTimekeepingTable} as tKTable", "tKTable.id", '=', "tkAggre.timekeeping_table_id")
            ->whereNull("tKTable.deleted_at")
            ->where("tKTable.year", '=', $year)
            ->where("tKTable.month", '=', $month);
        if ($last) {
            $collection = $collection->where("tKTable.end_date", '=', $endOfMonth);
        } else {
            $collection = $collection->where("tKTable.start_date", '=', $date->toDateString());
        }
        return $collection->groupBy("tkAggre.timekeeping_table_id")
            ->groupBy("tkAggre.employee_id")
            ->get();
    }

    /**
     * @return
     */
    public function getBuildSelectAggregates()
    {
        $tblTimekeepingAggregate = self::getTableName();
        $tblTimekeepingTable = TimekeepingTable::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblTeam = Team::getTableName();
        $tblRole = Role::getTableName();
        $build =  self::select(
            "{$tblTimekeepingTable}.id",
            "{$tblEmployee}.employee_card_id",
            "{$tblEmployee}.employee_code",
            "{$tblEmployee}.name as employee_name",
            "{$tblEmployee}.offcial_date",
            "{$tblTimekeepingAggregate}.employee_id",
            "{$tblTimekeepingAggregate}.total_official_working_days",
            "{$tblTimekeepingAggregate}.total_trial_working_days",
            "{$tblTimekeepingAggregate}.total_official_ot_weekdays",
            "{$tblTimekeepingAggregate}.total_trial_ot_weekdays",
            "{$tblTimekeepingAggregate}.total_official_ot_weekends",
            "{$tblTimekeepingAggregate}.total_trial_ot_weekends",
            "{$tblTimekeepingAggregate}.total_official_ot_holidays",
            "{$tblTimekeepingAggregate}.total_trial_ot_holidays",
            "{$tblTimekeepingAggregate}.total_ot_no_salary",
            "{$tblTimekeepingAggregate}.total_number_late_in",
            "{$tblTimekeepingAggregate}.total_number_early_out",
            "{$tblTimekeepingAggregate}.total_official_business_trip",
            "{$tblTimekeepingAggregate}.total_trial_business_trip",
            "{$tblTimekeepingAggregate}.total_official_leave_day_has_salary",
            "{$tblTimekeepingAggregate}.total_trial_leave_day_has_salary",
            "{$tblTimekeepingAggregate}.total_leave_day_no_salary",
            "{$tblTimekeepingAggregate}.total_official_supplement",
            "{$tblTimekeepingAggregate}.total_trial_supplement",
            "{$tblTimekeepingAggregate}.total_official_holiay",
            "{$tblTimekeepingAggregate}.total_trial_holiay",
            "{$tblTimekeepingAggregate}.total_late_start_shift",
            "{$tblTimekeepingAggregate}.total_late_mid_shift",
            "{$tblTimekeepingAggregate}.total_early_mid_shift",
            "{$tblTimekeepingAggregate}.total_early_end_shift",
            "{$tblTimekeepingAggregate}.number_com_off",
            "{$tblTimekeepingAggregate}.number_com_tri",
            "{$tblTimekeepingAggregate}.total_working_officail",
            "{$tblTimekeepingAggregate}.total_working_trial",
            "{$tblTimekeepingAggregate}.total_official_leave_basic_salary",
            "{$tblTimekeepingAggregate}.total_trial_leave_basic_salary",
            DB::raw("({$tblTimekeepingAggregate}.total_early_mid_shift + {$tblTimekeepingAggregate}.total_early_end_shift) AS total_early_shift"),
            DB::raw("({$tblTimekeepingAggregate}.total_late_start_shift + {$tblTimekeepingAggregate}.total_late_mid_shift) AS total_late_shift"),
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$tblTeam}.name) ORDER BY {$tblRole}.role DESC SEPARATOR '; ') as role_name")
        )
        ->join("{$tblEmployee}", "{$tblTimekeepingAggregate}.employee_id", '=', "{$tblEmployee}.id")
        ->join("{$tblTimekeepingTable}", "{$tblTimekeepingTable}.id", '=', "{$tblTimekeepingAggregate}.timekeeping_table_id")
        ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", '=', "{$tblEmployee}.id")
        ->join("{$tblTeam}", "{$tblTeam}.id", '=', "{$tblTeamMember}.team_id");

        // join role
        $build->join("{$tblRole}", function ($join) use ($tblRole, $tblTeamMember) {
            $join->on("{$tblRole}.id", '=', "{$tblTeamMember}.role_id");
            $join->on("{$tblRole}.special_flg", '=', DB::raw(Role::FLAG_POSITION));
        });
        return $build;
    }
    /**
     * get timekeeping aggregate
     * @param $timekeepingTableId
     * @param $teamIds
     * @param array $dataFilter
     * @param $route
     * @return \Rikkei\Core\Model\collection|null
     */
    public function getTimekeepingAggregates($timekeepingTableId, $teamIds, $dataFilter = [], $route, $export = false)
    {
        $tblTimekeepingAggregate = self::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $collection = $this->getBuildSelectAggregates();
        try {
            if (isset($dataFilter["{$tblTeamMember}.team_id"])) {
                if (in_array($dataFilter["{$tblTeamMember}.team_id"], $teamIds)) {
                    $teamIds = [$dataFilter["{$tblTeamMember}.team_id"]];
                } else {
                    $teamIds = [];
                }
            }
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            return null;
        }

        $collection->where("{$tblTimekeepingAggregate}.timekeeping_table_id", $timekeepingTableId)
            ->whereIn("{$tblTeamMember}.team_id", $teamIds)
            ->orderBy("{$tblTeamMember}.team_id")
            ->groupBy("{$tblTimekeepingAggregate}.employee_id");
        $pager = Config::getPagerData();
        $collection = self::filterGrid($collection, [], $route, 'LIKE');
        $collection = static::filterExcept($collection, $timekeepingTableId, $route);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        //If filter total official salary or total trial salary then return all
        $officialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_official_salary", $route);
        $trialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_trial_salary", $route);
        if (!empty($officialSalaryFilter) || $officialSalaryFilter === '0'
            || !empty($trialSalaryFilter) || $trialSalaryFilter === '0') {
            return $collection->get();
        }
        if ($export) {
            return $collection->get();
        }
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * @param $timekeepingTableId
     * @param $teamIds
     * @param array $dataFilter
     * @param $projFilter
     * @param $route
     * @param bool $export
     * @return \Rikkei\Core\Model\collection|null
     */
    public function getTimekeepingAggregatesProject($timekeepingTableId, $teamIds, $dataFilter = [], $projFilter, $route, $export = false)
    {
        $tblTimekeepingAggregate = self::getTableName();
        $tblTeamMember = TeamMember::getTableName();

        $collection = $this->getBuildSelectAggregates();
        $collection->join("manage_time_timekeepings as tk", function ($join) use ($timekeepingTableId, $tblTimekeepingAggregate) {
            $join->on("tk.employee_id", '=', "{$tblTimekeepingAggregate}.employee_id")
                ->where("tk.timekeeping_table_id",'=', $timekeepingTableId);
        });
        $collection->join("project_members as proMem", function ($join) use ($projFilter) {
            $join->on("proMem.employee_id", '=', "tk.employee_id")
                ->on("tk.timekeeping_date", '>=', 'proMem.start_at')
                ->on("tk.timekeeping_date", '<=', 'proMem.end_at')
                ->where('proMem.project_id', '=', $projFilter);
        });
        try {
            if (isset($dataFilter["{$tblTeamMember}.team_id"])) {
                if (in_array($dataFilter["{$tblTeamMember}.team_id"], $teamIds)) {
                    $teamIds = [$dataFilter["{$tblTeamMember}.team_id"]];
                } else {
                    $teamIds = [];
                }
            }
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            return null;
        }

        $collection->where("{$tblTimekeepingAggregate}.timekeeping_table_id", $timekeepingTableId)
            ->whereIn("{$tblTeamMember}.team_id", $teamIds)
            ->whereIn("{$tblTeamMember}.team_id", $teamIds)
            ->orderBy("{$tblTeamMember}.team_id")
            ->groupBy("{$tblTimekeepingAggregate}.employee_id");
        $pager = Config::getPagerData();
        $collection = self::filterGrid($collection, [], $route, 'LIKE');
        $collection = static::filterExcept($collection, $timekeepingTableId, $route);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        //If filter total official salary or total trial salary then return all
        $officialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_official_salary", $route);
        $trialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_trial_salary", $route);
        if (!empty($officialSalaryFilter) || $officialSalaryFilter === '0'
            || !empty($trialSalaryFilter) || $trialSalaryFilter === '0') {
            return $collection->get();
        }
        if ($export) {
            return $collection->get();
        }
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * get ot timekeeping aggregate by timekeeping_table_id
     *
     * @param  int $tkTableId
     * @return collection
     */
    public function getOTTimekeepingAggregatesById($tkTableId)
    {
        $tblTimekeepingAggregate = self::getTableName();
        $tblTimekeepingTable = TimekeepingTable::getTableName();

        return static::select(
            "{$tblTimekeepingAggregate}.timekeeping_table_id",
            "{$tblTimekeepingAggregate}.employee_id",
            "{$tblTimekeepingAggregate}.total_official_ot_weekdays",
            "{$tblTimekeepingAggregate}.total_trial_ot_weekdays",
            "{$tblTimekeepingAggregate}.total_official_ot_weekends",
            "{$tblTimekeepingAggregate}.total_trial_ot_weekends",
            "{$tblTimekeepingAggregate}.total_official_ot_holidays",
            "{$tblTimekeepingAggregate}.total_trial_ot_holidays",
            "{$tblTimekeepingAggregate}.total_ot_no_salary"
        )
        ->join("{$tblTimekeepingTable} as tkTable", 'tkTable.id', '=', "{$tblTimekeepingAggregate}.timekeeping_table_id")
        ->where(function($query) use ($tblTimekeepingAggregate) {
            $query->where("{$tblTimekeepingAggregate}.total_official_ot_weekdays", '<>', 0.00)
                ->orwhere("{$tblTimekeepingAggregate}.total_trial_ot_weekdays", '<>', 0.00)
                ->orwhere("{$tblTimekeepingAggregate}.total_official_ot_weekends", '<>', 0.00)
                ->orwhere("{$tblTimekeepingAggregate}.total_trial_ot_weekends", '<>', 0.00)
                ->orwhere("{$tblTimekeepingAggregate}.total_official_ot_holidays", '<>', 0.00)
                ->orwhere("{$tblTimekeepingAggregate}.total_trial_ot_holidays", '<>', 0.00);
        })
        ->where("{$tblTimekeepingAggregate}.timekeeping_table_id", $tkTableId)
        ->whereNull("tkTable.deleted_at")
        ->get();
    }
}
