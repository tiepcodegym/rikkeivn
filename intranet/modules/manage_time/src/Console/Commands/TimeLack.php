<?php

namespace Rikkei\ManageTime\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\Model\Timekeeping;
use DB;
use Rikkei\Team\Model\Team;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\ManageTime\Model\WorkingTimeRegister;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Team\Model\Employee;

class TimeLack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timekeeping:timelack {year=0} {month=0} {datefrom=0} {dateto=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tính thời gian làm thừa thiếu trong ngày của nhân viên';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get param from command
        $year = $this->argument('year');
        $month = $this->argument('month');
        $dateFrom = $this->argument('datefrom');
        $dateTo = $this->argument('dateto');
        if (!$year) {
            $year = Carbon::now()->subDay()->year;
        }
        if (!$month) {
            $month = Carbon::now()->subDay()->month;
        }
        if (!$dateFrom) {
            $dateFrom = Carbon::now()->subDay()->format('Y-m-d');
        }
        if (!$dateTo) {
            $dateTo = Carbon::now()->subDay()->format('Y-m-d');
        }

        $objTable = new TimekeepingTable();
        $tableList = $objTable->getTablesList($year, $month);
        foreach ($tableList as $timeKeepingTable) {
            $timekeepingTableId = $timeKeepingTable->id;
            $startDate = Carbon::parse($timeKeepingTable->start_date);
            $endDate = Carbon::parse($timeKeepingTable->end_date);
            $collectionModel = $this->getEmployeesToTimekeeping($timekeepingTableId, $dateFrom, $dateTo);
            $datesTimekeeping = ManageTimeCommon::getDateRange($startDate, $endDate);
            $empIdInList = $collectionModel->lists('employee_id')->toArray();
            $teamCodePre = Team::getTeamCodePrefix($timeKeepingTable->code);
            $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);
            $workingTimeDefault = ManageTimeView::findTimeSetting([], $teamCodePre, $rangeTimes);
            $wktEmpInDb = $this->getWorkingTime($teamCodePre, $empIdInList, $timeKeepingTable->start_date, $timeKeepingTable->end_date);

            $timeKeepingList = $this->getTimeKeeping($timekeepingTableId, $empIdInList, $dateFrom, $dateTo);
            $dataKeeping = [];
            $arrTeamPrefix = [];
            $arrCompensationDays = [];
            $arrHolidays = [];
            foreach ($timeKeepingList as $keepingItem) {
                
                $dataKeeping[$keepingItem->employee_id][$keepingItem->timekeeping_date] = $keepingItem;
                if (!array_key_exists($keepingItem->employee_id, $arrTeamPrefix)) {
                    $teamCodePrefix = Team::getTeamCodePrefix($keepingItem->code);
                    $arrTeamPrefix[$keepingItem->employee_id] = $teamCodePrefix;
                }
                if (!array_key_exists($teamCodePrefix, $arrCompensationDays)) {
                    $arrCompensationDays[$teamCodePrefix] = CoreConfigData::getCompensatoryDays($teamCodePrefix);
                }
                if (!array_key_exists($teamCodePrefix, $arrHolidays)) {
                    $arrHolidays[$teamCodePrefix] = CoreConfigData::getHolidayTeam($teamCodePrefix);
                }
            }
            foreach ($collectionModel as $item) {
                if (isset($arrTeamPrefix[$item->employee_id])) {
                    $teamCodePrefix = $arrTeamPrefix[$item->employee_id];
                    $compensationDays = $arrCompensationDays[$teamCodePrefix];
                    if (isset($datesTimekeeping) && count($datesTimekeeping)) {
                        foreach ($datesTimekeeping as $column => $date) {
                            if (isset($dataKeeping[$item->employee_id][date('Y-m-d', strtotime($date))])) {
                                $dataItem = $dataKeeping[$item->employee_id][date('Y-m-d', strtotime($date))];
                                $timekeepingSign = ManageTimeCommon::getTimekeepingSign($dataItem, $teamCodePrefix ,$compensationDays, $arrHolidays[$teamCodePrefix]);
                                // get time over/lack
                                $timeOver = $this->analyzeTime($timekeepingSign, $date, $wktEmpInDb, $item->employee_id, $dataItem, $workingTimeDefault);
                                $this->updateDbTimeOver($dataItem->id, $timeOver);
                            }
                        }
                    }
                }
                
            }
        }
    }

    private function analyzeTime($timekeepingSign, $date, $wktEmpInDb, $empId, $dataItem, $workingTimeDefault)
    {
        //if ($date->format('Y-m-d') == '2021-10-04') dd($timekeepingSign);
        $timeLack = 0;
        $arraySign = explode(', ', $timekeepingSign[0]);
        $arraySign = array_map('trim', $arraySign);
        if (is_array($arraySign)) {
            foreach ($arraySign as $sign) {
                if (array_intersect(['P', 'CT', 'V'], $arraySign)) {
                    return $timeLack;
                }
                // Tính thời gian làm thiếu
                $timeLack = $this->calTimeLack($sign, $timeLack);
            }
        }
        // Tính thời gian làm thừa
        $timeLack += $this->calTimeOver($date, $wktEmpInDb, $empId, $dataItem, $workingTimeDefault);

        return $timeLack;
    }

    private function calTimeOver($date, $wktEmpInDb, $empId, $dataItem, $workingTimeDefault)
    {
        $dateEnd = clone $date;
        if (empty($dataItem->end_time_afternoon_shift)) {
            return 0;
        }
        $dateEnd = Carbon::createFromFormat('Y-m-d H:i', $dateEnd->format('Y-m-d') . ' ' . $dataItem->end_time_afternoon_shift);
        $afternoonOutSetting = (!empty($wktEmpInDb[$empId][$date->format('Y-m-d')]['afternoonOutSetting'])) ? 
                $wktEmpInDb[$empId][$date->format('Y-m-d')]['afternoonOutSetting'] : $workingTimeDefault['afternoonOutSetting'];
        // Set lại ngày theo date đang chạy (vì có thể $workingTimeDefault lệch ngày)
        $afternoonOutSetting = Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $this->getNumberWithLeadingZero($afternoonOutSetting->hour) . ':' . $this->getNumberWithLeadingZero($afternoonOutSetting->minute));
        // Nếu về sau giờ hành chính và ko OT
        if ($afternoonOutSetting->lt($dateEnd) && !$dataItem->register_ot) {
            return $afternoonOutSetting->diffInMinutes($dateEnd);
        }
        return 0;
    }

    private function calTimeLack($sign, $timeLack)
    {
        if (strpos($sign, 'M1') !== false) {
            $timeLack -= (int)explode(': ', $sign)[1];
        }
        if (strpos($sign, 'M2') !== false) {
            $timeLack -= (int)explode(': ', $sign)[1];
        }
        if (strpos($sign, 'S1') !== false) {
            $timeLack -= (int)explode(': ', $sign)[1];
        }
        if (strpos($sign, 'S2') !== false) {
            $timeLack -= (int)explode(': ', $sign)[1];
        }
        return $timeLack;
    }

    private function getWorkingTime($teamCodePrefix, $empIds, $timekeepingTableStartDate, $timekeepingTableEndDate)
    {
        $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);
        $empLists = $this->getEmpByIdsWithContracts($empIds, $timekeepingTableStartDate, $timekeepingTableEndDate, [
            'employees.email',
            'employees.id',
            'offcial_date',
            'trial_date',
            'join_date',
            'leave_date',
            'contract_type',
            'start_time1',
            'end_time1',
            'start_time2',
            'end_time2',
            'code',
        ]);
        $workingTimeDate = [];
        $objView = new ManageTimeView();
        $workingTimeDefault = ManageTimeView::findTimeSetting([], $teamCodePrefix, $rangeTimes);
        foreach ($empLists as $empIdKey => $itemEmpList) {
            foreach ($itemEmpList as $itemEmp) {
                $startDateWT = Carbon::parse($itemEmp->wtk_from_date);
                $endDateWT = Carbon::parse($itemEmp->wtk_to_date);
                while (strtotime($startDateWT) <= strtotime($endDateWT)) {
                    $dateKey = $startDateWT->format('Y-m-d') . ' ';
                    if ($itemEmp->start_time1) {
                        $workingTimeDate[$itemEmp->id][$startDateWT->format('Y-m-d')] = [
                           'morningInSetting' => Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->start_time1),
                           'morningOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->end_time1),
                           'afternoonInSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->start_time2),
                           'afternoonOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->end_time2),
                        ];
                    } else {
                        $workingTimeDate[$itemEmp->id][$startDateWT->format('Y-m-d')] = [
                            'morningInSetting' => Carbon::createFromFormat('Y-m-d H:i', $dateKey . $workingTimeDefault['morningInSetting']->format('H:i')),
                            'morningOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $workingTimeDefault['morningOutSetting']->format('H:i')),
                            'afternoonInSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $workingTimeDefault['afternoonInSetting']->format('H:i')),
                            'afternoonOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $workingTimeDefault['afternoonOutSetting']->format('H:i')),
                        ];
                    }
                    $startDateWT->addDay();
                }
            }
        }
        return $workingTimeDate;
    }

    private function getEmpByIdsWithContracts($empIds, $dateStart, $dateEnd)
    {
        $colsSelected = [
            'employees.email',
            'employees.id',
            'offcial_date',
            'trial_date',
            'join_date',
            'leave_date',
            'contract_type',
            'start_time1',
            'end_time1',
            'start_time2',
            'end_time2',
            'code',
        ];
        $statusApprove = WorkingTimeRegister::STATUS_APPROVE;
        return \Rikkei\Team\Model\Employee::join('employee_works', 'employee_works.employee_id', '=', 'employees.id')
            ->leftJoin(DB::raw("(SELECT
                    wktd.working_time_id,
                    wktd.employee_id, 
                    wktd.from_date,
                    wktd.to_date,
                    wktd.half_morning,
                    wktd.half_afternoon,
                    wktd.start_time1,
                    wktd.end_time1, 
                    wktd.start_time2,
                    wktd.end_time2
                FROM working_time_details as wktd
                INNER JOIN working_time_registers AS wkt ON wkt.id = wktd.working_time_id
                WHERE wkt.status = {$statusApprove} 
                    AND wkt.deleted_at IS NULL
                    AND date(wktd.from_date) <= '{$dateEnd}'
                    AND date(wktd.to_date) >= '{$dateStart}') AS working_time
                "), 'working_time.employee_id', '=', 'employees.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'employees.id')
            ->leftJoin('teams', 'teams.id', '=', 'team_members.team_id')
            ->whereIn('employees.id', $empIds)
            ->select($colsSelected)
            ->addSelect(
                'working_time.working_time_id as wtk_id',
                'working_time.from_date as wtk_from_date',
                'working_time.to_date as wtk_to_date',
                'working_time.half_morning as half_morning',
                'working_time.half_afternoon as half_afternoon'
            )
            ->groupBy('working_time.working_time_id', 'employees.id')
            ->get()->groupBy('id');
    }

    private function getTimeKeeping($timekeepingTableId, $empIdInList, $dateFrom, $dateTo)
    {
        return Timekeeping::join('employees', 'employees.id', '=', 'manage_time_timekeepings.employee_id')
            ->join('manage_time_timekeeping_tables', 'manage_time_timekeeping_tables.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
            ->join('teams', 'teams.id', '=', 'manage_time_timekeeping_tables.team_id')
            ->leftJoin('employee_works', 'employee_works.employee_id', '=', 'employees.id')
            ->where('timekeeping_table_id', $timekeepingTableId)
            ->whereIn('manage_time_timekeepings.employee_id', $empIdInList)
            ->whereDate('manage_time_timekeepings.updated_at', '>=', $dateFrom)
            ->whereDate('manage_time_timekeepings.updated_at', '<=', $dateTo)
            ->select(
                'manage_time_timekeepings.*',
                DB::raw('date(employees.join_date) as join_date'),
                DB::raw('date(employees.trial_date) as trial_date'),
                DB::raw('date(employees.offcial_date) as offcial_date'),
                DB::raw('date(employees.leave_date) as leave_date'),
                'manage_time_timekeeping_tables.type as contract_type',
                'manage_time_timekeeping_tables.date_max_import',
                'teams.code'
            )
            ->get();
    }

    private function updateDbTimeOver($id, $timeOver)
    {
        Timekeeping::where('id', $id)->update(['time_over' => $timeOver]);
    }

    /**
     * [getEmployeesToTimekeeping: get employee to timekeeping]
     * @param  [int] $timekeepingTableId
     * @return [collection]
     */
    private function getEmployeesToTimekeeping($timekeepingTableId, $dateFrom, $dateTo)
    {
        $tblTimekeepingTable = Timekeeping::getTableName();
        $tblEmployee = Employee::getTableName();

        $collection = Employee::select(
                "{$tblEmployee}.id as employee_id",
                "{$tblEmployee}.employee_card_id as employee_card_id",
                "{$tblEmployee}.employee_code as employee_code",
                "{$tblEmployee}.name as employee_name"
            )
            ->join("{$tblTimekeepingTable}", "{$tblTimekeepingTable}.employee_id", "=", "{$tblEmployee}.id")
            ->where("{$tblTimekeepingTable}.timekeeping_table_id", $timekeepingTableId)
            ->whereDate("{$tblTimekeepingTable}.updated_at", '>=', $dateFrom)
            ->whereDate("{$tblTimekeepingTable}.updated_at", '<=', $dateTo);

        $collection->groupBy("{$tblEmployee}.id");

        return $collection->get();
    }

    private function getNumberWithLeadingZero($number)
    {
        return (int)$number >= 10 ? $number : '0' . $number;
    }
}
