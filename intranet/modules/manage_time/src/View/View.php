<?php

namespace Rikkei\ManageTime\View;

use Auth;
use Carbon\Carbon;
use DB;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Lang;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\FinesMoney\Model\FinesMoney;
use Rikkei\ManageTime\Http\Controllers\ProjectController;
use Rikkei\ManageTime\Http\Controllers\TimekeepingController;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\Model\ComeLateRegister;
use Rikkei\ManageTime\Model\LeaveDay;
use Rikkei\ManageTime\Model\LeaveDayBaseline;
use Rikkei\ManageTime\Model\LeaveDayHistories;
use Rikkei\ManageTime\Model\LeaveDayHistory;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\SupplementReasons;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\ManageTime\Model\TimekeepingAggregate;
use Rikkei\ManageTime\Model\TimekeepingNotLate;
use Rikkei\ManageTime\Model\TimekeepingNotLateTime;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\Model\WorkingTime as WKTModel;
use Rikkei\ManageTime\Model\WorkingTimeRegister;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\ManageTime\View\WorkingTime as WorkingTimeView;
use Rikkei\Project\Model\Project;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;

class View
{
    const FOLDER_UPLOAD = 'timekeeping_upload';
    const FOLDER_UPLOAD_RELATED = 'timekeeping_upload_related';
    const FOLDER_APP = 'app';

    const ACCESS_FOLDER = 0777;
    const ACCESS_FILE = 'public';
    const STR_SUPPLEMENT = 'supp';
    const STR_LEAVE_DAYS = 'leave_days';
    const STR_MISSION = 'misson';
    const STR_OT = 'ot';
    const FOLDER_EXP_SYSTENA = 'systena_export';
    const FOLDER_SALARY_RATE_AGGREGATE = 'salary_rate_aggregate';

    //business trip onsite
    const NUMBER_YEAR = 20;
    const DAY_YEAR = 365;

    private static $emplSpecInOut = null;

    /**
     * list of branches applying timekeeping
     * @return array
     */
    public static function getListBranch() {
        return [
            'hanoi',
            'hcm',
            'danang'
        ];
    }
    
    /**
     * [timekeepingResult]
     * @param  object $timekeepingOfEmployee
     * @param  boolean $isWeekend
     * @param  boolean $isHoliday
     * @return array
     */
    public function timekeepingResult($timekeepingOfEmployee, $isWeekend = false, $isHoliday = false, $offcialDate = null, $trialDate = null, $contractType = null, $workingTime = null, $teamCodePre = null, $typeOfTable = null)
    {
        $startTimeMorningShift = $timekeepingOfEmployee->start_time_morning_shift;
        $endTimeMorningShift = $timekeepingOfEmployee->end_time_morning_shift;
        $startTimeAfternoonShift = $timekeepingOfEmployee->start_time_afternoon_shift;
        $endTimeAfternoonShift = $timekeepingOfEmployee->end_time_afternoon_shift;
        $lateStartShift = $timekeepingOfEmployee->late_start_shift;
        $earlyMidShift = $timekeepingOfEmployee->early_mid_shift;
        $lateMidShift = $timekeepingOfEmployee->late_mid_shift;
        $earlyEndShift = $timekeepingOfEmployee->early_end_shift;
        $maxTimeLateInEarlyOut = ManageTimeConst::MAX_TIME_LATE_IN_EARLY_OUT;
        $date = $timekeepingOfEmployee->timekeeping_date;

        $timekeepingNumber = 0;
        $timekeeping = ManageTimeConst::NOT_WORKING;

        //Calculate time morning, afternoon works
        if (!$workingTime) {
            $morningTime = 0.5;
            $afternoonTime = 0.5;
        } else {
            $morningTime = $workingTime['morningInSetting']->diff($workingTime['morningOutSetting']);
            $afternoonTime = $workingTime['afternoonInSetting']->diff($workingTime['afternoonOutSetting']);
            $morningTime = round(($morningTime->h * 60 + $morningTime->i) / static::getHoursWork($workingTime), 2, PHP_ROUND_HALF_DOWN);
            $afternoonTime =round(($afternoonTime->h * 60 + $afternoonTime->i) / static::getHoursWork($workingTime), 2, PHP_ROUND_HALF_DOWN);

        }

        $typesOffcial = getOptions::typeEmployeeOfficial();
        if (((empty($trialDate) && (empty($offcialDate) || strtotime($date) < strtotime($offcialDate)))
                || (!empty($trialDate) && strtotime($date) < strtotime($trialDate)))
            && in_array($typeOfTable, $typesOffcial)) {
            return [$timekeeping, $timekeepingNumber];
        }

        if (((empty($trialDate) && (empty($offcialDate) || strtotime($date) >= strtotime($offcialDate)))
                || (!empty($trialDate) && strtotime($date) >= strtotime($trialDate)))
            && !in_array($typeOfTable, $typesOffcial) && !(empty($trialDate) && empty($offcialDate))) {
            return [$timekeeping, $timekeepingNumber];
        }

        if (!$isWeekend && $isHoliday) {
            if ((empty($trialDate) && (empty($offcialDate) || strtotime($date) < strtotime($offcialDate)))
                || (!empty($trialDate) && strtotime($date) < strtotime($trialDate))
                || !in_array($typeOfTable, $typesOffcial)) {
                return [$timekeeping, $timekeepingNumber];
            }
        }

        // Check if is weekend or holiday then set not working
        if ($isWeekend || $isHoliday) {
            $timekeepingNumber = 0;
            $timekeeping = ManageTimeConst::NOT_WORKING;
            if ($isHoliday && !$isWeekend && in_array($typeOfTable, $typesOffcial)) {
                $timekeeping = ManageTimeConst::HOLIDAY_TIME;
            }
        } else {
            //timekeeping code not japan
            if (((!$startTimeMorningShift) && (!$endTimeMorningShift) && (!$startTimeAfternoonShift) && (!$endTimeAfternoonShift))
                || (($startTimeMorningShift) && (!$endTimeMorningShift) && (!$startTimeAfternoonShift) && (!$endTimeAfternoonShift))
                || ((!$startTimeMorningShift) && ($endTimeMorningShift) && (!$startTimeAfternoonShift) && (!$endTimeAfternoonShift))
                || ((!$startTimeMorningShift) && (!$endTimeMorningShift) && ($startTimeAfternoonShift) && (!$endTimeAfternoonShift))
                || ((!$startTimeMorningShift) && ($endTimeMorningShift) && ($startTimeAfternoonShift) && (!$endTimeAfternoonShift))
            ) {
                return [$timekeeping, $timekeepingNumber];
            } elseif ($startTimeMorningShift) {
                if ($endTimeAfternoonShift
                    && $lateStartShift <= $maxTimeLateInEarlyOut
                    && $earlyMidShift <= $maxTimeLateInEarlyOut
                    && $lateMidShift <= $maxTimeLateInEarlyOut
                    && $earlyEndShift <= $maxTimeLateInEarlyOut) {
                    $timekeeping = ManageTimeConst::FULL_TIME;
                    $timekeepingNumber = 1;
                } elseif (($endTimeAfternoonShift
                        && $lateStartShift > $maxTimeLateInEarlyOut
                        && $lateMidShift <= $maxTimeLateInEarlyOut && $earlyEndShift <= $maxTimeLateInEarlyOut)
                    || ($endTimeAfternoonShift && $startTimeAfternoonShift
                        && $lateMidShift <= $maxTimeLateInEarlyOut && $earlyEndShift <= $maxTimeLateInEarlyOut)) {
                    $timekeeping = ManageTimeConst::PART_TIME_AFTERNOON;
                    $timekeepingNumber = $afternoonTime;
                } elseif (($endTimeMorningShift || $endTimeAfternoonShift || $startTimeAfternoonShift)
                    && $earlyMidShift <= $maxTimeLateInEarlyOut && $lateStartShift <= $maxTimeLateInEarlyOut) {
                    $timekeeping = ManageTimeConst::PART_TIME_MORNING;
                    $timekeepingNumber = $morningTime;
                } else {
                    return [$timekeeping, $timekeepingNumber];
                }
            } elseif ($startTimeAfternoonShift) {
                if ($startTimeAfternoonShift >= $workingTime['afternoonOutSetting']->format('H:i')) {
                    $timekeeping = 0;
                    $timekeepingNumber = 0;
                } elseif ($earlyEndShift <= $maxTimeLateInEarlyOut && $lateMidShift <= $maxTimeLateInEarlyOut) {
                    $timekeeping = ManageTimeConst::PART_TIME_AFTERNOON;
                    $timekeepingNumber = $afternoonTime;
                }
            } elseif ((!$startTimeMorningShift) && ($endTimeMorningShift)
                && (!$startTimeAfternoonShift) && ($endTimeAfternoonShift)) {
                if ($earlyEndShift <= $maxTimeLateInEarlyOut && $lateMidShift <= $maxTimeLateInEarlyOut) {
                    $timekeeping = ManageTimeConst::PART_TIME_AFTERNOON;
                    $timekeepingNumber = $afternoonTime;
                }
            } else {
                //do not something
            }
        }
        return [$timekeeping, $timekeepingNumber];
    }

    /*
     * Update leave day OT when upload timekeeping file
     * @param [int] $timekeepingTableId
     */
    public static function updateLeaveDayOT($timekeepingTableId, $arrayDates = [])
    {
        if (count($arrayDates)) {
            foreach ($arrayDates as $empId => $value) {
                $dataResetLeaveDay = [];
                $employeesAddedLeaveDayOT = Timekeeping::select('employee_id', DB::raw('SUM(leave_day_added) as leave_day_added'))
                    ->where('timekeeping_table_id', $timekeepingTableId)
                    ->where('leave_day_added', '>', 0)
                    ->where('employee_id', $empId)
                    ->whereIn('timekeeping_date', $value)
                    ->groupBy('employee_id')
                    ->get();
                if (count($employeesAddedLeaveDayOT)) {
                    foreach ($employeesAddedLeaveDayOT as $emp) {
                        $leaveDay = LeaveDay::select('day_ot')->where('employee_id', $emp->employee_id)->first();
                        if (!$leaveDay) {
                            continue;
                        }
                        $leaveDayOT = $leaveDay->day_ot - $emp->leave_day_added;
                        if ($leaveDayOT < 0) {
                            $leaveDayOT = 0;
                        }
                        $dataResetLeaveDay['update'][$emp->employee_id] = [
                            'employee_id' => $emp->employee_id,
                            'day_ot' => (float)$leaveDayOT
                        ];
                    }
                    ManageLeaveDay::insertUpdateData($dataResetLeaveDay);
                }
            }
        } else {
            $dataResetLeaveDay = [];
            $employeesAddedLeaveDayOT = Timekeeping::select('employee_id', DB::raw('SUM(leave_day_added) as leave_day_added'))
                ->where('timekeeping_table_id', $timekeepingTableId)
                ->where('leave_day_added', '>', 0)
                ->groupBy('employee_id')
                ->get();
            if (count($employeesAddedLeaveDayOT)) {
                foreach ($employeesAddedLeaveDayOT as $emp) {
                    $leaveDay = LeaveDay::select('day_ot')->where('employee_id', $emp->employee_id)->first();
                    if (!$leaveDay) {
                        continue;
                    }
                    $leaveDayOT = $leaveDay->day_ot - $emp->leave_day_added;
                    if ($leaveDayOT < 0) {
                        $leaveDayOT = 0;
                    }
                    $dataResetLeaveDay['update'][$emp->employee_id] = [
                        'employee_id' => $emp->employee_id,
                        'day_ot' => (float)$leaveDayOT
                    ];
                }
                ManageLeaveDay::insertUpdateData($dataResetLeaveDay);
            }
        }
    }

    /**
     * Storage timekeeping file
     * @return boolean
     */
    public static function storageTimekeepingFile($file, $timekeepingId)
    {
        self::createFiles();
        $fileName = $timekeepingId . '.' . $file->getClientOriginalExtension();
        // Move file to folder
        $folderPath = storage_path('app/' . self::FOLDER_UPLOAD);
        if (Storage::exists(self::FOLDER_UPLOAD . '/' . $fileName)) {
            return false;
        }
        $file->move($folderPath, $fileName);
        @chmod($folderPath . '/' . $fileName, self::ACCESS_FOLDER);
        return true;
    }

    /**
     * Import timekeeping file
     *
     * @return void
     */
    public static function doUpdateTimekeeping()
    {
        if (static::isProcess()) {
            return true;
        }
        $files = Storage::files(self::FOLDER_UPLOAD);
        if (!$files) {
            return true;
        }
        static::createProcess();
        try {
            ini_set('memory_limit', '1024M');
            Log::info('inprogress');
            foreach ($files as $file) {
                static::execProcessFile($file);
                Storage::delete($file);
            }
        } catch (Exception $ex) {
            // nothing
        }
        static::deleteProcess();
    }

    public static function doUpdateRelated()
    {
        if (static::isProcess()) {
            return true;
        }
        $files = Storage::files(self::FOLDER_UPLOAD_RELATED);
        if (!$files) {
            return true;
        }
        static::createProcess();
        try {
            ini_set('memory_limit', '1024M');
            $i = 0;
            foreach ($files as $file) {
                TimekeepingController::setDataRelatedCron($file);
                Storage::delete($file);
                if ($i++ % 10 == 0) {
                    sleep(3);
                }
            }
        } catch (Exception $ex) {
            // nothing
        }
        static::deleteProcess();
    }

    /**
     * Recalculate time in of morning
     *
     * @param array $record a row import from csv
     *
     * return array
     */
    public static function reCalTime($record)
    {
        if ((strtolower($record['ca_lam_viec']) === 'sáng' ||
            strtolower($record['ca_lam_viec']) === 'Sáng') &&
            is_null($record['vao_luc']) &&
            !is_null($record['ra_luc']) &&
            $record['ra_luc'] < '12:00'
        ) {
            $record['vao_luc'] = $record['ra_luc'];
            $record['ra_luc'] = null;
        }
        return $record;
    }

    /*
     * Execute process file
     */
    public static function execProcessFile($file)
    {
        $resultMore = '';
        $result = preg_match('/[0-9]+.*$/', $file, $resultMore);
        if (!$result || !$resultMore) {
            return true;
        }
        $resultMore = $resultMore[0];
        $timekeepingTableId = substr($resultMore, 0, strrpos($resultMore, '.'));
        if (!is_numeric($timekeepingTableId)) {
            Storage::delete($file);
            return true;
        }
        $timekeepingTable = TimekeepingTable::find($timekeepingTableId);

        if (!$timekeepingTable) {
            return false;
        }
        DB::beginTransaction();
        try {
            $fileFullPath = storage_path('app/' . $file);
            $timekeepingStartDate = Carbon::parse($timekeepingTable->start_date);
            $timekeepingEndDate = Carbon::parse($timekeepingTable->end_date);
            $tableNameTimekeeping = Timekeeping::getTableName();
            $tableNameTimekeepingTable = TimekeepingTable::getTableName();
            $annualHolidays = CoreConfigData::getAnnualHolidays(2);
            $team = Team::getTeamById($timekeepingTable->team_id);
            $teamCodePre = Team::getTeamCodePrefix($team->code);
            $teamCodePre = Team::changeTeam($teamCodePre);
            $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePre);
            $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePre);
            $typesOffcial = getOptions::typeEmployeeOfficial();
            $arrHolidays = [$annualHolidays, $specialHolidays];

            $employees = [];
            $unsetDate = [];
            Excel::selectSheetsByIndex(0)
                ->filter('chunk')
                ->load($fileFullPath)
                ->chunk(10000, function ($data) use (
                    $tableNameTimekeeping,
                    $tableNameTimekeepingTable,
                    $annualHolidays,
                    $specialHolidays,
                    $employees,
                    $unsetDate,
                    $timekeepingTableId,
                    $timekeepingStartDate,
                    $timekeepingEndDate,
                    $teamCodePre,
                    $timekeepingTable,
                    $compensationDays,
                    $arrHolidays,
                    $typesOffcial
                ) {
                    $data = $data->toArray();
                    $timekeepingEmployees = [];
                    $datasInsertTimeKeeping = [];
                    $getTimekeepingEmployees = [];

                    $employeesEmail = [];
                    $dataWithEmpEmailKey = [];
                    foreach ($data as $itemRow) {
                        if (!array_key_exists('id_cham_cong', $itemRow)
                            || !array_key_exists('ma_nvien', $itemRow)
                            || !array_key_exists('ngay', $itemRow)
                            || !array_key_exists('vao_luc', $itemRow)
                            || !array_key_exists('ra_luc', $itemRow)
                            || !array_key_exists('ca_lam_viec', $itemRow)
                        ) {
                            CoreConfigData::delByKey('hr.timekeeping.timesheet.temp');
                            Log::info(Lang::get('manage_time::view.Invalid file'));
                            throw new Exception(Lang::get('manage_time::view.Invalid file'), 652);
                        }

                        // Check if has not employee code or date timekeeping then set error message and continue
                        if (!$itemRow['ma_nvien'] || !$itemRow['ngay']) {
                            continue;
                        }

                        // Get timekeeping information
                        if ($itemRow['ngay'] instanceof Carbon) {
                            $timekeepingDate = $itemRow['ngay'];
                        } else {
                            try {
                                $timekeepingDate = Carbon::createFromFormat('d/m/Y', $itemRow['ngay']);
                            } catch (Exception $ex) {
                                $_SESSION['timekeeping_upload'][] = 'Định dạng ngày tháng sai. Nơi sai - Mã NV: ' . $itemRow['ma_nvien'] . ', date: ' . $itemRow['ngay'];
                            }
                        }
                        // Check if upload date has not in timkeeping date
                        if (isset($unsetDate[$timekeepingDate->format('Y-m-d')]) && $unsetDate[$timekeepingDate->format('Y-m-d')]) {
                            continue;
                        }
                        if (strtotime($timekeepingDate->format('Y-m-d')) < strtotime($timekeepingStartDate->format('Y-m-d')) || strtotime($timekeepingDate->format('Y-m-d')) > strtotime($timekeepingEndDate->format('Y-m-d'))) {
                            $unsetDate[$timekeepingDate->format('Y-m-d')] = true;
                            continue;
                        }

                        // Check if not exist employee then continue
                        $employeeEmail = trim(strtolower($itemRow['ma_nvien'])) . CoreConfigData::get('project.suffix_email');

                        //store unique emails
                        if (!in_array($employeeEmail, $employeesEmail)) {
                            $employeesEmail[] = $employeeEmail;
                        }

                        try {
                            $itemRow = View::reCalTime($itemRow);
                            //Store timekeeping information
                            if ($itemRow['ca_lam_viec'] === 'Sáng' || $itemRow['ca_lam_viec'] === 'sáng') {
                                $key = 0;
                            } else {
                                $key = 1;
                            }
                            $strDate = $timekeepingDate->format('Y-m-d') . ' ';
                            $dataWithEmpEmailKey[$employeeEmail][$timekeepingDate->format('Y-m-d')][$key] = [
                                'vao_luc' => $itemRow['vao_luc'] instanceof Carbon || !$itemRow['vao_luc']
                                    ? $itemRow['vao_luc'] : Carbon::createFromFormat('Y-m-d H:i', $strDate .  $itemRow['vao_luc']),
                                'thuc_te_vao_luc' => $itemRow['vao_luc'] instanceof Carbon || !$itemRow['vao_luc']
                                    ? $itemRow['vao_luc'] : Carbon::createFromFormat('Y-m-d H:i', $strDate .  $itemRow['vao_luc']),

                                'ra_luc' => $itemRow['ra_luc'] instanceof Carbon || !$itemRow['ra_luc']
                                    ? $itemRow['ra_luc'] : Carbon::createFromFormat('Y-m-d H:i', $strDate . $itemRow['ra_luc']),

                                'di_muon' => null,

                                've_som' => null,

                                'ca_lam_viec' => $itemRow['ca_lam_viec'],
                            ];
                        } catch (Exception $ex) {
                            $_SESSION['timekeeping_upload'][] = 'Định dạng giờ sai. Nơi sai - Mã NV: ' . $itemRow['ma_nvien'] . ', date: ' . $itemRow['ngay'];
                        }
                    }
                    //get employee_id from email
                    $empList = Employee::getEmpByEmailsWithContracts($employeesEmail, $timekeepingStartDate->format('Y-m-d'), $timekeepingEndDate->format('Y-m-d'), [
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
                    $empIds = [];
                    foreach  ($empList as $key => $emp) {
                        $empIds[] = $key;
                    }
                    $dataNotLateTime = with(new View())->getDataNotLateTime($empIds, $timekeepingTable->start_date, $timekeepingTable->end_date);
                    $dataNotLate = with(new View())->getDataNotLate($empIds, $timekeepingStartDate, $timekeepingEndDate);
                    $timeSettingOfEmp = [];
                    $workingTime = [];
                    foreach ($empList as $empId => $emps) {
                        $emp = $emps[0];
                        $dataWithEmpEmailKey[strtolower($emp->email)]['employee_id'] = $emp->id;
                        $dataWithEmpEmailKey[strtolower($emp->email)]['offcial_date'] = $emp->offcial_date;
                        $dataWithEmpEmailKey[strtolower($emp->email)]['trial_date'] = $emp->trial_date;
                        $dataWithEmpEmailKey[strtolower($emp->email)]['join_date'] = $emp->join_date;
                        $dataWithEmpEmailKey[strtolower($emp->email)]['leave_date'] = $emp->leave_date;
                        $dataWithEmpEmailKey[strtolower($emp->email)]['contract_type'] = $emp->contract_type;
                        
                        if (isset($dataWithEmpEmailKey[strtolower($emp->email)])) {
                            $dataDateTime = $dataWithEmpEmailKey[strtolower($emp->email)];
                            foreach ($dataDateTime as $date => $wt) {
                                if (!strstr($date, "-")) {
                                    continue;
                                }
                                foreach ($emps as $itemEmp) {
                                    if ($itemEmp->wtk_from_date <= $date && $itemEmp->wtk_to_date >= $date) {
                                        $timeSettingDate = [
                                            'morningInSetting' => Carbon::createFromFormat('Y-m-d H:i', $date . ' '. $itemEmp->start_time1),
                                            'morningOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $date . ' '. $itemEmp->end_time1),
                                            'afternoonInSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $date . ' '. $itemEmp->start_time2),
                                            'afternoonOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $date . ' '. $itemEmp->end_time2),
                                        ];
                                        $timeSettingOfEmp[$emp->email][$date] = $timeSettingDate;
                                        $workingTime[$emp->email][$date] = $timeSettingDate;
                                    }
                                }
                                if (!isset($workingTime[$emp->email][$date])) {
                                    $timeSettingDate = View::findTimeSetting(null, $teamCodePre, null, $date);
                                    $timeSettingOfEmp[$emp->email][$date] = $timeSettingDate;
                                    $workingTime[$emp->email][$date] = $timeSettingDate;
                                }
                            }
                        }
                        $dataWithEmpEmailKey = View::calEarlyOutLateIn($dataWithEmpEmailKey, $emp, $timeSettingOfEmp, $teamCodePre, $dataNotLateTime, $dataNotLate);
                    }

                    //unset data
                    unset($empList);
                    unset($data);
                    $arrDate = [];
                    $dateMax = '';
                    foreach ($dataWithEmpEmailKey as $email => $value) {
                        //Check email has not exist store error and ignore
                        if (!isset($value['employee_id'])) {
                            continue;
                        }
                        $employeeId = $value['employee_id'];

                        foreach ($value as $date => $timekeepingInfo) {
                            if (in_array($date, ['employee_id', 'offcial_date', 'trial_date', 'join_date', 'contract_type', 'leave_date'])) {
                                continue;
                            }
                            if (empty($value['join_date']) || Carbon::parse($value['join_date'])->gt(Carbon::parse($date))) {
                                continue;
                            }
                            if (!empty($value['leave_date']) && Carbon::parse($value['leave_date'])->lt(Carbon::parse($date))) {
                                continue;
                            }
                            if ($dateMax == '' || $dateMax < $date) {
                                $dateMax = $date;
                            }
                            $keyUnsetDate = $employeeId . '-' . $date;
                            if (isset($unsetDate[$keyUnsetDate]) && $unsetDate[$keyUnsetDate]) {
                                continue;
                            }

                            if (!isset($timekeepingEmployees[$employeeId])) {
                                $timekeepingEmployees[$employeeId] = Timekeeping::join("{$tableNameTimekeepingTable}", "{$tableNameTimekeepingTable}.id", "=", "{$tableNameTimekeeping}.timekeeping_table_id")
                                    ->where("{$tableNameTimekeeping}.timekeeping_table_id", $timekeepingTableId)
                                    ->where("{$tableNameTimekeeping}.employee_id", $employeeId)
                                    ->where("{$tableNameTimekeeping}.timekeeping_date", $date)
                                    ->first();
                                if (!$timekeepingEmployees[$employeeId]) {
                                    $unsetDate[$keyUnsetDate] = true;
                                    unset($timekeepingEmployees[$employeeId]);
                                    continue;
                                }
                            }
                            if (!$timekeepingEmployees[$employeeId]) {
                                continue;
                            }
                            if (!isset($arrDate[0]) || (isset($arrDate[0]) && $arrDate[0] > $date)) {
                                $arrDate[0] = $date;
                            }
                            if (!isset($arrDate[1]) || (isset($arrDate[1]) && $arrDate[1] < $date)) {
                                $arrDate[1] = $date;
                            }

                            $dataInsert['is_official'] = 0;
                            if ($value['offcial_date'] && strtotime(Carbon::parse($date)->format('Y-m-d')) >= strtotime(Carbon::parse($value['offcial_date'])->format('Y-m-d'))) {
                                $dataInsert['is_official'] = 1;

                                foreach ($compensationDays["com"] as $key => $compenstaion) {
                                    if (strtotime(Carbon::parse($compenstaion)->format('Y-m-d')) === strtotime(Carbon::parse($date)->format('Y-m-d'))) {
                                        if (strtotime(Carbon::parse($compensationDays["lea"][$key])->format('Y-m-d')) < strtotime(Carbon::parse($value['offcial_date'])->format('Y-m-d'))
                                            && strtotime(Carbon::parse($compensationDays["lea"][$key])->format('Y-m-d')) >= strtotime(Carbon::parse($value['join_date'])->format('Y-m-d'))
                                            && $value['trial_date'] && strtotime(Carbon::parse($compensationDays["lea"][$key])->format('Y-m-d')) >= strtotime(Carbon::parse($value['trial_date'])->format('Y-m-d'))) {
                                            $dataInsert['is_official'] = 0;
                                        }
                                    }
                                }
                            }
                            // Check timekeeping date is weekend or holiday
                            $isWeekend = ManageTimeCommon::isWeekend(Carbon::createFromFormat('Y-m-d', $date), $compensationDays);
                            $isHoliday = ManageTimeCommon::isHoliday(Carbon::createFromFormat('Y-m-d', $date), $annualHolidays, $specialHolidays, $teamCodePre);
                            $isWeekendOrHoliday = $isWeekend || $isHoliday;
                            foreach ($timekeepingInfo as $infoItem) {
                                // Check late in early out
                                $dataInsert['employee_id'] = $employeeId;
                                $dataInsert['timekeeping_date'] = $date;
                                $dataInsert['created_at'] = date('Y-m-d H:i:s');
                                $dataInsert['updated_at'] = date('Y-m-d H:i:s');
                                if ($teamCodePre == Team::CODE_PREFIX_HN || $teamCodePre == Team::CODE_PREFIX_AI) {
                                    $dataCheck = static::checkLateOutTKNew($infoItem, $date, $value, $timekeepingTable, $typesOffcial, $isWeekendOrHoliday);
                                } else {
                                    $dataCheck = static::checkLateOutTKOld($infoItem, $date, $value, $timekeepingTable, $typesOffcial, $isWeekendOrHoliday);
                                }
                                if (count($dataCheck)) {
                                    foreach ($dataCheck as $keyCheck => $valueCheck) {
                                        $dataInsert[$keyCheck] = $valueCheck;
                                    }
                                }
                                $keyTimeEmployee = $employeeId . '-' . $date;
                                if (isset($getTimekeepingEmployees[$keyTimeEmployee])) {
                                    $timekeepingOfEmployee = $getTimekeepingEmployees[$keyTimeEmployee];
                                } else {
                                    $timekeepingOfEmployee = $timekeepingEmployees[$employeeId];
                                    $datasInsertTimeKeeping[$keyTimeEmployee] = $dataInsert;
                                }
                                $timekeepingOfEmployee->setData($dataInsert);
                                // Get timekeeping result
                                $manageTimeView = new View();
                                $timekeepingResult = $manageTimeView->timekeepingResult($timekeepingOfEmployee, $isWeekend, $isHoliday, $value['offcial_date'], $value['trial_date'], $value['contract_type'], $workingTime[$email][$date], $teamCodePre, $timekeepingTable->type);
                                if ($teamCodePre != Team::CODE_PREFIX_JP) {
                                    $dataInsert['timekeeping'] = $timekeepingResult[0];
                                    $dataInsert['timekeeping_number'] = $timekeepingResult[1];
                                } else {
                                    if ($timekeepingResult[0] == ManageTimeConst::PART_TIME_MORNING) {
                                        $timeEndMor = $timekeepingOfEmployee->end_time_morning_shift instanceof Carbon || !$timekeepingOfEmployee->end_time_morning_shift
                                            ? $timekeepingOfEmployee->end_time_morning_shift : Carbon::createFromFormat('H:i', $timekeepingOfEmployee->end_time_morning_shift);
                                        if ($timeEndMor->gt($workingTime[$email][$date]["morningInSetting"])) {
                                            $dataInsert['timekeeping'] = $timekeepingResult[0];
                                            $dataInsert['timekeeping_number'] = $timekeepingResult[1];
                                        } else {
                                            $dataInsert['timekeeping'] = 0;
                                            $dataInsert['timekeeping_number'] = 0;
                                        }
                                    } elseif ($timekeepingResult[0] == ManageTimeConst::PART_TIME_AFTERNOON) {
                                        $timeStartAfter = $timekeepingOfEmployee->start_time_afternoon_shift instanceof Carbon || !$timekeepingOfEmployee->start_time_afternoon_shift
                                            ? $timekeepingOfEmployee->start_time_afternoon_shift : Carbon::createFromFormat('H:i', $timekeepingOfEmployee->start_time_afternoon_shift);

                                        if ($timeStartAfter && $timeStartAfter->lt($workingTime[$email][$date]["afternoonOutSetting"])) {
                                            $dataInsert['timekeeping'] = $timekeepingResult[0];
                                            $dataInsert['timekeeping_number'] = $timekeepingResult[1];
                                        } else {
                                            $dataInsert['timekeeping'] = 0;
                                            $dataInsert['timekeeping_number'] = 0;
                                        }
                                    } else {
                                        $dataInsert['timekeeping'] = $timekeepingResult[0];
                                        $dataInsert['timekeeping_number'] = $timekeepingResult[1];
                                    }
                                    // rouding 0.5
                                    if ($dataInsert['timekeeping_number'] < 1 && $dataInsert['timekeeping_number'] > 0) {
                                        $dataInsert['timekeeping_number'] = 0.5;
                                    }
                                }

                                $datasInsertTimeKeeping[$keyTimeEmployee] = array_merge($datasInsertTimeKeeping[$keyTimeEmployee], $dataInsert);
                                $getTimekeepingEmployees[$keyTimeEmployee] = $timekeepingOfEmployee;
                            }

                            // set time OT japan
                            if ($teamCodePre == Team::CODE_PREFIX_JP) {
                                $timeOT = 0;
                                $dataInsertOT['register_ot_has_salary'] = 0;
                                $dataInsertOT['register_ot'] = 0;

                                $timeOutAfter = clone $workingTime[$email][$date]["afternoonOutSetting"];
                                $timeOutAfter->addMinutes(ManageTimeConst::JP_TIME_START_OT);
                                if ($isHoliday || $isWeekend) {
                                    $timeLunch = 0;
                                    $timeRestedAfternoon = 0;
                                    if (!empty($timekeepingInfo[0]['vao_luc'])) {
                                        $overtimeStartAtStrtotime = strtotime($timekeepingInfo[0]['vao_luc']);

                                        if (!empty($timekeepingInfo[1]['ra_luc'])) {
                                            $morningIn = Carbon::parse($timekeepingInfo[0]['vao_luc']);
                                            $afterOut = Carbon::parse($timekeepingInfo[1]['ra_luc']);
                                            //set time end afternoon
                                            if ($afterOut < $workingTime[$email][$date]["afternoonOutSetting"]) {
                                                $overtimeEndAtStrtotime = strtotime($timekeepingInfo[1]['ra_luc']);
                                            } elseif ($afterOut >= $workingTime[$email][$date]["afternoonOutSetting"] && $afterOut < $timeOutAfter) {
                                                $overtimeEndAtStrtotime = strtotime($workingTime[$email][$date]["afternoonOutSetting"]);
                                            } else {
                                                $overtimeEndAtStrtotime = strtotime($timekeepingInfo[1]['ra_luc']);
                                                $timeRestedAfternoon = ManageTimeConst::JP_TIME_START_OT;
                                            }

                                            $timeLunch = View::getLunchBreak($morningIn->hour, $afterOut->hour, $workingTime[$email][$date]);
                                        } else {
                                            if (!empty($timekeepingInfo[0]['ra_luc'])) {
                                                $overtimeEndAtStrtotime = strtotime($timekeepingInfo[0]['ra_luc']);
                                            } else {
                                                $overtimeEndAtStrtotime = 0;
                                            }
                                        }
                                    } elseif (!empty($timekeepingInfo[1]['vao_luc'])) {
                                        $overtimeStartAtStrtotime = strtotime($timekeepingInfo[1]['vao_luc']);

                                        if (!empty($timekeepingInfo[1]['ra_luc'])) {
                                            $afterOut = Carbon::parse($timekeepingInfo[1]['ra_luc']);
                                            //set time end afternoon
                                            if ($afterOut < $workingTime[$email][$date]["afternoonOutSetting"]) {
                                                $overtimeEndAtStrtotime = strtotime($timekeepingInfo[1]['ra_luc']);
                                            } elseif ($afterOut >= $workingTime[$email]["afternoonOutSetting"] && $afterOut < $timeOutAfter) {
                                                $overtimeEndAtStrtotime = strtotime($workingTime[$email][$date]["afternoonOutSetting"]);
                                            } else {
                                                $overtimeEndAtStrtotime = strtotime($timekeepingInfo[1]['ra_luc']);
                                                $timeRestedAfternoon = ManageTimeConst::JP_TIME_START_OT;
                                            }
                                        } else {
                                            $overtimeEndAtStrtotime = 0;
                                        }
                                    } else {
                                        $overtimeEndAtStrtotime = 0;
                                        $overtimeStartAtStrtotime = 0;
                                    }
                                    $timeOT = (($overtimeEndAtStrtotime - $overtimeStartAtStrtotime) / 3600) - ($timeLunch / 60) - ($timeRestedAfternoon / 60);
                                    if ($isHoliday) {
                                        $registerOt = ManageTimeConst::IS_OT_ANNUAL_SPECIAL_HOLIDAY;
                                    } else {
                                        $registerOt = ManageTimeConst::IS_OT_WEEKEND;
                                    }
                                } else {
                                    $timeOtAfter = 0;
                                    $intAfterIn = 0;
                                    if (!empty($timekeepingInfo[1]['ra_luc'])) {
                                        $intTimeOutAfter = strtotime($timeOutAfter);

                                        if (!empty($timekeepingInfo[1]['vao_luc'])) {
                                            $intAfterIn = strtotime($timekeepingInfo[1]['vao_luc']);
                                        }
                                        if ($intAfterIn > $intTimeOutAfter) {
                                            $intTimeOutAfter = $intAfterIn;
                                        }
                                        $intAfterOut = strtotime($timekeepingInfo[1]['ra_luc']);

                                        if ($intAfterOut - $intTimeOutAfter > 0) {
                                            $timeOtAfter = $intAfterOut - $intTimeOutAfter;
                                        }
                                    }

                                    $timeInMor = $workingTime[$email][$date]["morningInSetting"];
                                    $intTimeInMor = strtotime($timeInMor->toTimeString());

                                    $timeMorInOt = 0;
                                    $timeMorOutOt = 0;
                                    if (!empty($timekeepingInfo[0]['vao_luc'])) {
                                        $intMorningIn = strtotime($timekeepingInfo[0]['vao_luc']);

                                        if ($intTimeInMor > $intMorningIn) {
                                            $timeMorInOt = $intMorningIn;
                                            $timeMorOutOt = $intTimeInMor;
                                        } else {
                                            $timeMorInOt = 0;
                                        }
                                    }

                                    if (!empty($timekeepingInfo[0]['ra_luc']) && !empty($timekeepingInfo[0]['vao_luc'])) {
                                        $intMorningOut = strtotime($timekeepingInfo[0]['ra_luc']);
                                        if ($intMorningOut < $intTimeInMor) {
                                            $timeMorOutOt = $intMorningOut;
                                        }
                                    }
                                    $timeOT = ($timeOtAfter + ($timeMorOutOt - $timeMorInOt)) / 3600;
                                    $registerOt = ManageTimeConst::IS_OT;
                                }
                                if ($timeOT > 0) {
                                    $dataInsertOT['register_ot_has_salary'] = round($timeOT, 1);
                                    $dataInsertOT['register_ot'] = $registerOt;
                                }
                                $datasInsertTimeKeeping[$keyTimeEmployee] = array_merge($datasInsertTimeKeeping[$keyTimeEmployee], $dataInsertOT);
                            }
                        }
                    }
                    //unset data
                    unset($dataWithEmpEmailKey);
                    unset($getTimekeepingEmployees);

                if (count($datasInsertTimeKeeping) && empty($_SESSION['timekeeping_upload'])) {
                    Timekeeping::updateData($datasInsertTimeKeeping, $timekeepingTableId, false);
                    // with(new Timekeeping())->getDataSingTimeKeeping($empIds, $arrDate, $timekeepingTableId, $compensationDays, $arrHolidays);
                    $tkTable = TimekeepingTable::find($timekeepingTableId);
                    if ($tkTable->date_max_import < $dateMax) {
                        $tkTable = $tkTable->update(['date_max_import' => $dateMax]);
                    }
                    unset($datasInsertTimeKeeping);
                }
            }, false);

            if ($creator = $timekeepingTable->getCreatorInfo()) {
                $dataInsertEmail = [];
                $templateEmail = 'manage_time::template.timekeeping.mail_update_timekeping';
                $dataInsertEmail['mail_to'] = $creator->email;
                $dataInsertEmail['receiver_name'] = $creator->name;
                $dataInsertEmail['timekeeping_table_name'] = $timekeepingTable->timekeeping_table_name;
                $dataInsertEmail['month'] = $timekeepingTable->month;
                $dataInsertEmail['year'] = $timekeepingTable->year;
                $dataInsertEmail['link'] = route('manage_time::timekeeping.manage-timekeeping-table');

                if (empty($_SESSION['timekeeping_upload'])) {
                    $dataInsertEmail['mail_title'] = Lang::get('manage_time::message.[Notification][Timekeeping][Success] :subject', ['subject' => $timekeepingTable->timekeeping_table_name]);
                    $dataInsertEmail['content'] = Lang::get('manage_time::message.Process update timekeeping file successful. Please get data related module and update timekeeping aggregate');
                } else {
                    $dataInsertEmail['mail_title'] = Lang::get('manage_time::message.[Notification][Timekeeping][Error] :subject', ['subject' => $timekeepingTable->timekeeping_table_name]);
                    $dataInsertEmail['content'] = Lang::get('manage_time::message.Process update timekeeping file fail');
                    $dataInsertEmail['errors'] = $_SESSION['timekeeping_upload'];
                }
                ManageTimeCommon::pushEmailToQueue($dataInsertEmail, $templateEmail);
            }

            if (!empty($_SESSION['timekeeping_upload'])) {
                unset($_SESSION['timekeeping_upload']);
            }

            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            if ($timekeepingTable && $creator = $timekeepingTable->getCreatorInfo()) {
                $dataInsertEmail = [];
                $templateEmail = 'manage_time::template.timekeeping.mail_update_timekeping';
                $dataInsertEmail['mail_to'] = $creator->email;
                $dataInsertEmail['mail_title'] = Lang::get('manage_time::message.[Notification][Timekeeping][Error] :subject', ['subject' => $timekeepingTable->timekeeping_table_name]);
                $dataInsertEmail['receiver_name'] = $creator->name;
                $dataInsertEmail['timekeeping_table_name'] = $timekeepingTable->timekeeping_table_name;
                $dataInsertEmail['month'] = $timekeepingTable->month;
                $dataInsertEmail['year'] = $timekeepingTable->year;
                $dataInsertEmail['link'] = route('manage_time::timekeeping.manage-timekeeping-table');
                $dataInsertEmail['content'] = Lang::get('manage_time::message.Process update timekeeping file fail') . $ex->getMessage();
                ManageTimeCommon::pushEmailToQueue($dataInsertEmail, $templateEmail);
            }
            Storage::delete($file);
            return false;
        }
    }

    /**
     * Calculate early out, late in of employee
     *
     * @param  arrar $dataWithEmpEmailKey
     * @param  collection $emp
     * @param  array $timeSettingOfEmp
     * @param  string $teamCodePre
     * @param  array $dataNotLateTime [nhân viên không đi muộn 1 khoảng time < 10:30]
     * @param  array $dataNotLate [nhân viên không đi muộn]
     * @return array
     */
    public static function calEarlyOutLateIn($dataWithEmpEmailKey, $emp, $timeSettingOfEmp, $teamCodePre, $dataNotLateTime = [], $dataNotLate = [])
    {
        foreach ($dataWithEmpEmailKey[strtolower($emp->email)] as $date => &$times) {
            if (!strstr($date, "-")) {
                continue;
            }
            $notLate = [];
            if (isset($dataNotLateTime[$emp->email])) {
                $notLate = $dataNotLateTime[$emp->email];
            }
            $timeSettingReclone = self::reCalSpecialEmplTime(strtolower($emp->email), $date, $timeSettingOfEmp[$emp->email][$date]);
            if (strstr($date,"-") && isset($notLate[$date]) &&
                !empty($times[0]['vao_luc']) && $times[0]['vao_luc']->format('H:i') < '10:30') {
                $times[0]['vao_luc'] = $times[0]['vao_luc']->subMinutes($notLate[$date]);
            }
            // Note: $times[0] is morning time, $time[1] is afternoon time
            // Late in
            if (!empty($times[0]['vao_luc']) &&
                (!empty($times[0]['ra_luc']) || !empty($times[1]['vao_luc']) || !empty($times[1]['ra_luc']))) {
                $morningIn = $times[0]['vao_luc'];
                if ($morningIn->gt($timeSettingReclone['morningInSetting'])) {
                    $times[0]['di_muon'] = static::timeDiff($morningIn, $timeSettingReclone['morningInSetting'], $teamCodePre);
                }
            } else {
                if (!empty($times[1]['vao_luc']) && !empty($times[1]['ra_luc']) && empty($times[0]['ra_luc'])) {
                    $afternoonIn = $times[1]['vao_luc'];
                    if ($afternoonIn->gt($timeSettingReclone['afternoonInSetting']) && $afternoonIn->lt($timeSettingReclone['afternoonOutSetting'])) {
                        $times[1]['di_muon'] = static::timeDiff($afternoonIn, $timeSettingReclone['afternoonInSetting'], $teamCodePre);
                    }
                }
            }
            if (strstr($date, "-") && isset($dataNotLate[$emp->email]) && isset($dataNotLate[$emp->email][$date])) {
                if (!empty($times[0]['di_muon']) &&
                    ($times[0]['di_muon']->hour * 60 + $times[0]['di_muon']->minute) <= ManageTimeConst::MAX_TIME_LATE_IN_EARLY_OUT) {
                    $times[0]['di_muon'] = 0;
                }
            }
            // fix TH xin nghỉ cuối giờ chiều nhưng về sớm hơn 15:30 máy chấm công hiểu là giờ ra chiều
            // >= 15:30 đề tránh sinh ra bug ko mong muốn
            if (!empty($times[1]['vao_luc']) && empty($times[1]['ra_luc']) && $times[1]['vao_luc']->format('H:i') >= '15:00') {
                $times[1]['ra_luc'] = $times[1]['vao_luc'];
                $times[1]['vao_luc'] = null;
            }
             // Early out
            if (!empty($times[1]['ra_luc']) &&
                (!empty($times[0]['vao_luc']) || !empty($times[1]['vao_luc']) || !empty($times[0]['ra_luc']))) {
                $afternoonOut = $times[1]['ra_luc'];
                if ($afternoonOut->lt($timeSettingReclone['afternoonOutSetting'])) {
                    if ($afternoonOut->hour > ManageTimeConst::TIME_END_OT) {
                        $times[1]['ve_som'] = static::timeDiff($afternoonOut, $timeSettingReclone['afternoonOutSetting'], $teamCodePre);
                    }
                }
            } else {
                
                if (!empty($times[0]['ra_luc']) && !empty($times[0]['vao_luc']) && empty($times[1]['vao_luc'])) {
                    $morningOut = $times[0]['ra_luc'];
                    if ($morningOut->lt($timeSettingReclone['morningOutSetting'])) {
                        $times[0]['ve_som'] = static::timeDiff($morningOut, $timeSettingReclone['morningOutSetting'], $teamCodePre);
                    }
                }
            }
        }
        return $dataWithEmpEmailKey;
    }

    /**
     * Ceiling late/early minutes of employee works in Japan
     *
     * @param int $lateOrEarly
     *
     * @return int
     */
    public static function ceilLateEarlyMinuteJapan($lateOrEarly)
    {
        $minutesPerBlock = ManageTimeConst::TIME_LATE_IN_PER_BLOCK;
        return ceil($lateOrEarly / $minutesPerBlock) * $minutesPerBlock;
    }

    /**
     * Get working time setting of employee
     * Default from system data config
     *
     * @param WorkingTime $workingTimeOfEmployee
     *
     * @return array
     */
    public static function findTimeSetting($workingTimeOfEmployee, $teamCode, $rangeTimes = null, $date = null)
    {
        $defaultWorkingTime = ManageTimeCommon::defaultWorkingTime($teamCode, $rangeTimes);
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d') . ' ';
        } else {
            $date .= ' ';
        }
        $morningInSetting = empty($workingTimeOfEmployee->start_time1) ?
            Carbon::createFromFormat('Y-m-d H:i', $date . $defaultWorkingTime['start_time1']) : Carbon::createFromFormat('Y-m-d H:i', $date . $workingTimeOfEmployee->start_time1);
        $morningOutSetting = empty($workingTimeOfEmployee->end_time1) ?
            Carbon::createFromFormat('Y-m-d H:i', $date . $defaultWorkingTime['end_time1']) : Carbon::createFromFormat('Y-m-d H:i', $date . $workingTimeOfEmployee->end_time1);
        $afternoonInSetting = empty($workingTimeOfEmployee->start_time2) ?
            Carbon::createFromFormat('Y-m-d H:i', $date . $defaultWorkingTime['start_time2']) : Carbon::createFromFormat('Y-m-d H:i', $date . $workingTimeOfEmployee->start_time2);
        $afternoonOutSetting = empty($workingTimeOfEmployee->end_time2) ?
            Carbon::createFromFormat('Y-m-d H:i', $date . $defaultWorkingTime['end_time2']) : Carbon::createFromFormat('Y-m-d H:i', $date . $workingTimeOfEmployee->end_time2);

        return [
            'morningInSetting' => $morningInSetting,
            'morningOutSetting' => $morningOutSetting,
            'afternoonInSetting' => $afternoonInSetting,
            'afternoonOutSetting' => $afternoonOutSetting,
        ];
    }

    /**
     * Get time diff between 2 time
     *
     * @param Carbon $time1
     * @param Carbon $time2
     *
     * @return Carbon Format H:i
     */
    public static function timeDiff($time1, $time2, $code = null)
    {
        $diff = $time1->diffInSeconds($time2);
        if ($code && $code === Team::CODE_PREFIX_JP) {
            $diff = static::ceilLateEarlyMinuteJapan($diff / 60) * 60;
        }
        return Carbon::createFromFormat('H:i', gmdate('H:i', $diff));
    }

    public static function getHoursWork($timeSetting)
    {
        $startTime = new DateTime();
        $endTime = new DateTime();
        $startTime->setTime($timeSetting['morningInSetting']->hour, $timeSetting['morningInSetting']->minute);
        $endTime->setTime($timeSetting['afternoonOutSetting']->hour, $timeSetting['afternoonOutSetting']->minute);
        $diffTime = $startTime->diff($endTime);
        $lunchBreak = static::getLunchBreak($startTime->format('H'), $endTime->format('H'), $timeSetting);
        return $diffTime->h * 60 + $diffTime->i - $lunchBreak;
    }

    public static function getLunchBreak($hourStart, $hourEnd, $timeSetting)
    {
        if ((static::isMorningTime($hourStart) && static::isMorningTime($hourEnd, false))
            || (!static::isMorningTime($hourStart) && !static::isMorningTime($hourEnd, false))) {
            return 0;
        }
        $startDate = new DateTime();
        $startDate->setTime($timeSetting['morningOutSetting']->hour, $timeSetting['morningOutSetting']->minute);
        $endDate = new DateTime();
        $endDate->setTime($timeSetting['afternoonInSetting']->hour, $timeSetting['afternoonInSetting']->minute);
        $diffTime = $startDate->diff($endDate);
        return $diffTime->h * 60 + $diffTime->i;
    }

    public static function isMorningTime($hour, $timeIn = true)
    {
        $morningTime = $timeIn ? [7, 8, 9, 10] : [11, 12, 13];
        return in_array($hour, $morningTime);
    }

    public function timeSettingEmployee($employeeId, $date, $teamCodePrefix = 'hanoi')
    {
        $workingTime = new WKTModel();
        $workingTimeSettingOfEmp = $workingTime->getWorkingTimeInfo($employeeId, $date);

        return $this->buildTimeSettingEmployee($workingTimeSettingOfEmp, $teamCodePrefix);
    }

    public function buildTimeSettingEmployee($workingTimeSettingOfEmp, $teamCodePrefix = 'hanoi', $rangeTimes = null)
    {
        $workingTimeReload = static::findTimeSetting($workingTimeSettingOfEmp, $teamCodePrefix, $rangeTimes);
        return [
            'morningInSetting' => [
                'hour' => (int)$workingTimeReload['morningInSetting']->format('H'),
                'minute' => (int)$workingTimeReload['morningInSetting']->format('i'),
            ],
            'morningOutSetting' => [
                'hour' => (int)$workingTimeReload['morningOutSetting']->format('H'),
                'minute' => (int)$workingTimeReload['morningOutSetting']->format('i'),
            ],
            'afternoonInSetting' => [
                'hour' => (int)$workingTimeReload['afternoonInSetting']->format('H'),
                'minute' => (int)$workingTimeReload['afternoonInSetting']->format('i'),
            ],
            'afternoonOutSetting' => [
                'hour' => (int)$workingTimeReload['afternoonOutSetting']->format('H'),
                'minute' => (int)$workingTimeReload['afternoonOutSetting']->format('i'),
            ],
        ];
    }

    /**
     * Create and chmod files use timekeeping
     */
    public static function createFiles()
    {
        if (!Storage::exists(self::FOLDER_UPLOAD)) {
            Storage::makeDirectory(self::FOLDER_UPLOAD, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . self::FOLDER_UPLOAD), self::ACCESS_FOLDER);
        if (!Storage::exists('process')) {
            Storage::makeDirectory('process');
        }
        @chmod(storage_path('app/process'), self::ACCESS_FOLDER);
    }

    /*
     * Check exist process running
     */
    public static function isProcess()
    {
//        if (Storage::exists('process/' . self::FOLDER_UPLOAD)) {
//            return true;
//        }
        return false;
    }

    /*
     * Create process update timekeeping
     */
    public static function createProcess()
    {
        if (static::isProcess()) {
            return true;
        }
        Storage::put('process/' . self::FOLDER_UPLOAD, 1);
    }

    /*
     * Delete process running
     */
    public static function deleteProcess()
    {
        if (!static::isProcess()) {
            return true;
        }
        Storage::delete('process/' . self::FOLDER_UPLOAD);
    }

    /**
     * Update day off from OT in weekend
     * Save to table leave_day_history      check leave day ot has updated from timekeeping table
     * Save to table leave_day_histories    Save changes histories
     *
     * @param int $timekeepingTableId
     * @param array $dataInsertLeave store data save into table leave_days
     *
     * @return void
     */
    public static function updateDayOff($timekeepingTableId, $dataInsertLeave)
    {
        $empDelete = [];
        $dataInsertHistory = [];
        $leaveDayHistory = LeaveDayHistory::getLeaveAdded($timekeepingTableId);
        $dataInsertHistories = [];

        if (isset($dataInsertLeave['update'])) {
            if (count($leaveDayHistory)) {
                foreach ($leaveDayHistory as $history) {
                    if (isset($dataInsertLeave['update'][$history->employee_id])) {
                        $empDelete[] = $history->employee_id;
                        $dataInsertHistory[] = [
                            'timekeeping_table_id' => $timekeepingTableId,
                            'employee_id' => $history->employee_id,
                            'day_added' => $dataInsertLeave['update'][$history->employee_id]['day_ot'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                        $dayOt = $dataInsertLeave['update'][$history->employee_id]['day_ot'] - $history->day_added + $dataInsertLeave['update'][$history->employee_id]['leave_day_ot'];
                        $dataInsertLeave['update'][$history->employee_id]['day_ot'] = $dayOt;
                        $fields = [
                            'day_ot' => [
                                'old' => $dataInsertLeave['update'][$history->employee_id]['leave_day_ot'],
                                'new' => $dayOt,
                            ],
                        ];
                        $change = LeaveDayPermission::getFieldsChanged($history->employee_id, $fields);
                        if ($change) {
                            $dataInsertHistories[] = static::getRecordInsertHistory($history->employee_id, $change);
                        }
                    }
                }
            }

            foreach ($dataInsertLeave['update'] as $update) {
                if (!in_array($update['employee_id'], $empDelete)) {
                    $dataInsertHistory[] = [
                        'timekeeping_table_id' => $timekeepingTableId,
                        'employee_id' => $update['employee_id'],
                        'day_added' => $update['day_ot'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                    $dayOt = round($update['day_ot'] + $update['leave_day_ot'], 1);
                    $dataInsertLeave['update'][$update['employee_id']]['day_ot'] = $dayOt;
                    $fields = [
                        'day_ot' => [
                            'old' => $update['leave_day_ot'],
                            'new' => $dayOt,
                        ],
                    ];
                    $change = LeaveDayPermission::getFieldsChanged($update['employee_id'], $fields);
                    if ($change) {
                        $dataInsertHistories[] = static::getRecordInsertHistory($update['employee_id'], $change);
                    }
                }
            }
        }

        if (isset($dataInsertLeave['insert'])) {
            foreach ($dataInsertLeave['insert'] as $insert) {
                $dataInsertHistory[] = [
                    'timekeeping_table_id' => $timekeepingTableId,
                    'employee_id' => $insert['employee_id'],
                    'day_added' => $insert['day_ot'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                $fields = [
                    'day_ot' => [
                        'old' => 0,
                        'new' => $insert['day_ot'],
                    ],
                ];
                $change = LeaveDayPermission::getFieldsChanged($insert['employee_id'], $fields);
                if ($change) {
                    $dataInsertHistories[] = static::getRecordInsertHistory($insert['employee_id'], $change);
                }
            }
        }

        //save history
        if (count($dataInsertHistories)) {
            LeaveDayHistories::insert($dataInsertHistories);
        }

        ManageLeaveDay::insertUpdateData($dataInsertLeave);

        //insert into tbl leave_day_history
        if (count($empDelete)) {
            LeaveDayHistory::deleteOldData($timekeepingTableId, $empDelete);
        }
        if (count($dataInsertHistory)) {
            LeaveDayHistory::insert($dataInsertHistory);
        }
    }

    /**
     * Get record insert to table leave_day_histories
     *
     * @param int $empId
     * @param array $change
     *
     * @return array
     */
    public static function getRecordInsertHistory($empId, $change)
    {
        $leaveDayHistory = new LeaveDayHistories();
        return [
            'id' => $leaveDayHistory->id,
            'employee_id' => $empId,
            'content' => json_encode($change),
            'type' => LeaveDayHistories::TYPE_OT,
            'created_by' => Auth::id(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    /**
     * Đồng bộ đi muộn về sớm với bảng chấm công
     *
     * @param TimekeepingTable $timeKeepingTable
     * @return void
     */
    public static function updateEarlyLate($timeKeepingTable)
    {
        $timekeepingTableId = $timeKeepingTable->id;
        $timekeepingTableStartDate = Carbon::parse($timeKeepingTable->start_date);
        $timekeepingTableEndDate = Carbon::parse($timeKeepingTable->end_date);

        //Lấy các đơn đăng ký trong khoảng thời gian của bảng chấm công
        $lateEarly = ComeLateRegister::where('status', '=', ComeLateRegister::STATUS_APPROVED)
            ->where(function ($query) use ($timekeepingTableStartDate, $timekeepingTableEndDate) {
                $query->whereBetween('date_start', [$timekeepingTableStartDate, $timekeepingTableEndDate])
                    ->orWhereBetween('date_end', [$timekeepingTableStartDate, $timekeepingTableEndDate]);
            })->get();

        $dataLateEarly = [];
        if (count($lateEarly)) {
            $dataLateEarly['timekeeping_table_id'] = $timekeepingTableId;
            foreach ($lateEarly as $item) {
                $dateStart = $item->date_start;
                $dateEnd = $item->date_end;
                while (strtotime($dateStart) <= strtotime($dateEnd)) {
                    $dataLateEarly['employee'][$item->employee_id][$dateStart] = [
                        'early_end_shift' => $item->early_end_shift,
                        'early_mid_shift' => $item->early_mid_shift,
                    ];
                    $dateStart = date("Y-m-d", strtotime("+1 day", strtotime($dateStart)));
                }
            }
        }

        //Tính toán và update vào bảng chấm công
        if (count($dataLateEarly)) {
            $timeKeepingData = Timekeeping::where('timekeeping_table_id', $timekeepingTableId)->get();
            foreach ($timeKeepingData as $timeKeepingDataItem) {
                if (isset($dataLateEarly['employee'][$timeKeepingDataItem->employee_id][$timeKeepingDataItem->timekeeping_date])) {

                    $timeEndShiftRegister = $dataLateEarly['employee'][$timeKeepingDataItem->employee_id][$timeKeepingDataItem->timekeeping_date]['early_end_shift'];

                    $timeMidShiftRegister = $dataLateEarly['employee'][$timeKeepingDataItem->employee_id][$timeKeepingDataItem->timekeeping_date]['early_mid_shift'];

                    $earlyEndShift = $timeEndShiftRegister - $timeKeepingDataItem->early_end_shift >= 0
                        ? 0 : $timeKeepingDataItem->early_end_shift - $timeEndShiftRegister;

                    $earlyMidShift = $timeMidShiftRegister - $timeKeepingDataItem->early_mid_shift >= 0
                        ? 0 : $timeKeepingDataItem->early_mid_shift - $timeMidShiftRegister;

                    Timekeeping::where('id', $timeKeepingDataItem->id)
                        ->update([
                            'early_end_shift' => $earlyEndShift,
                            'early_mid_shift' => $earlyMidShift,
                        ]);
                }
            }
        }
    }

    /**
     * Tìm những nhân viên đã có trong bảng chấm công
     *
     * @param int $timekeepingTableId
     * @param Employee collection $employees
     *
     * @return array
     */
    public static function findEmpExistInTimekeeping($timekeepingTableId, $employees)
    {
        $empInTimekeeping = Timekeeping::where('timekeeping_table_id', $timekeepingTableId)
            ->groupBy('employee_id')->lists('employee_id')->toArray();

        $dataExist = [];
        foreach ($employees as $emp) {
            if (in_array($emp->id, $empInTimekeeping)) {
                $dataExist[] = Lang::get('manage_time::message.Employee name has exists.', ['name' => $emp->name . ' (' . CoreView::getNickName($emp->email) . ')']);
            }
        }

        if (count($dataExist)) {
            $dataExist[] = Lang::get('manage_time::message.Add employee error. Please try again.');
        }

        return $dataExist;
    }

    /**
     * Check in that month, employee has offcial or not
     *
     * @param date|string $offcialDate
     * @param date|string $endDate
     * @return boolean
     */
    public static function hasOffcialText($offcialDate, $endDate)
    {
        return $offcialDate && Carbon::parse($offcialDate) <= Carbon::parse($endDate);
    }

    /**
     * Check in that month, employee is working trial or not
     *
     * @param date|string $offcialDate
     * @param date|string $startDate
     * @return boolean
     */
    public static function hasTrialText($offcialDate, $startDate)
    {
        return ($offcialDate && Carbon::parse($offcialDate) > Carbon::parse($startDate)) || !$offcialDate;
    }

    /**
     * Check in that month, employee both offcial and trial
     *
     * @param date|string $offcialDate
     * @param date|string $startDate
     * @param date|string $endDate
     * @return boolean
     */
    public static function hasBothOffcialTrialText($offcialDate, $startDate, $endDate)
    {
        return static::hasOffcialText($offcialDate, $endDate) && static::hasTrialText($offcialDate, $startDate);
    }

    /**
     * Get number days off in time business
     *
     * @param collection $timekeepingAggregate
     * @param datetime $startDate
     * @param datetime $endDate
     *
     * @return array
     */
    public static function getDaysOffInTimeBusiness($employeeId, $startDate, $endDate, $teamCode, $tkTableId)
    {
        $teamCodePrefix = Team::getTeamCodePrefix($teamCode);
        $leaveDayRegister = LeaveDayRegister::select('creator_id', 'date_start', 'date_end', 'number_days_off', 'used_leave_day')
            ->join('leave_day_reasons', 'leave_day_reasons.id', '=', 'leave_day_registers.reason_id')
            ->where('status', '=', LeaveDayRegister::STATUS_APPROVED)
            ->where('creator_id', $employeeId);

        $carbonEndDate = Carbon::parse($endDate);
        if ($carbonEndDate->hour === 0) {
            $leaveDayRegister->whereDate('date_start', '<=', $carbonEndDate);
        } else {
            $leaveDayRegister->where('date_start', '<=', $endDate);
        }
        $carbonStartDate = Carbon::parse($startDate);
        if ($carbonStartDate->hour === 0) {
            $leaveDayRegister->whereDate('date_end', '>=', $carbonStartDate);
        } else {
            $leaveDayRegister->where('date_end', '>=', $startDate);
        }

        $leaveDayRegister = $leaveDayRegister->get();

        $startDateBusiness = $carbonStartDate->year . '-' . $carbonStartDate->month . '-01';
        $endDateBusiness = $carbonStartDate->year . '-' . $carbonStartDate->month . '-31';
        $businessTripRegister = BusinessTripRegister::getRegisterOfTimeKeeping($startDateBusiness,[$employeeId], $startDateBusiness, $endDateBusiness);

        $leaveDayRegister = self::separateBranchBusiness($leaveDayRegister, $businessTripRegister, $teamCodePrefix);

        $daysOff = [
            'has_salary' => 0,
            'no_salary' => 0,
        ];

        //=== working time register ===
        $empLists = Employee::getEmpWithContracts([$employeeId], $startDate, $endDate, [
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
        if (count($empLists)) {
            foreach ($empLists[$employeeId] as $itemEmp) {
                $startDateWT = Carbon::parse($itemEmp->wtk_from_date);
                $endDateWT = Carbon::parse($itemEmp->wtk_to_date);
                while (strtotime($startDateWT) <= strtotime($endDateWT)) {
                    $dateKey = $startDateWT->format('Y-m-d') . ' ';
                    if ($itemEmp->start_time1) {
                        $workingTimeDate[$startDateWT->format('Y-m-d')] = [
                        'morningInSetting' => Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->start_time1),
                        'morningOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->end_time1),
                        'afternoonInSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->start_time2),
                        'afternoonOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->end_time2),
                        ];
                    }
                    $startDateWT->addDay();
                }
            }
        }
        if (count($leaveDayRegister)) {
            $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
            foreach ($leaveDayRegister as $itemReg) {
                $timeStart = $itemReg->date_start;
                $timeEnd = $itemReg->date_end;

                $date = date('Y-m-01', strtotime($timeStart));
                $workingTimeModel = new WKTModel();
                $workingTimeSettingOfEmp = $workingTimeModel->getWorkingTimeInfo($itemReg->creator_id, $date);
                $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);
                $workingTimeDefault = static::findTimeSetting($workingTimeSettingOfEmp, $teamCodePrefix, $rangeTimes);

                while (strtotime($timeStart) <= strtotime($timeEnd)) {
                    $isWeekend = ManageTimeCommon::isWeekend(Carbon::parse($timeStart), $compensationDays);
                    $isHoliday = ManageTimeCommon::isHoliday(Carbon::parse($timeStart), null, null, $teamCodePrefix);
                    if (Carbon::parse($timeStart)->format('Y-m-d') >= Carbon::parse($startDate)->format('Y-m-d')
                        && Carbon::parse($timeStart)->format('Y-m-d') <= Carbon::parse($endDate)->format('Y-m-d')
                        && !($isWeekend || $isHoliday)) {
                        $dayAdd = 1;
                        if ($workingTimeDate && isset($workingTimeDate[substr($timeStart, 0, 10)])) {
                            $workingTime = $workingTimeDate[substr($timeStart, 0, 10)];
                        } else {
                            $workingTime = $workingTimeDefault;
                        }
                        if (CoreConfigData::checkBranchRegister(Employee::getEmpById($itemReg->creator_id))) {
                            $dateStart = Carbon::parse($itemReg->date_start);
                            $dateEnd = Carbon::parse($itemReg->date_end);
                            $timeStart = Carbon::parse($timeStart);

                            if ($timeStart->format('Y-m-d') == $dateStart->format('Y-m-d')
                                && $timeStart->format('Y-m-d') < $dateEnd->format('Y-m-d')) {
                                $dateEnd->hour($workingTime['afternoonOutSetting']->hour);
                                $dateEnd->minute($workingTime['afternoonOutSetting']->minute);
                                $dayAdd = TimekeepingController::getDiffTimesRegister($dateStart->hour, $dateEnd->hour, $dateStart->minute, $dateEnd->minute, $workingTime);
                            } elseif ($timeStart->format('Y-m-d') > $dateStart->format('Y-m-d')
                                && $timeStart->format('Y-m-d') < $dateEnd->format('Y-m-d')) {
                                $dayAdd = 1;
                            } elseif ($timeStart->format('Y-m-d') > $dateStart->format('Y-m-d')
                                && $timeStart->format('Y-m-d') == $dateEnd->format('Y-m-d')) {
                                $dateStart->hour($workingTime['morningInSetting']->hour);
                                $dateStart->minute($workingTime['morningInSetting']->minute);
                                $dayAdd = TimekeepingController::getDiffTimesRegister($dateStart->hour, $dateEnd->hour, $dateStart->minute, $dateEnd->minute, $workingTime);
                            } else {
                                // if (ManageTimeView::isMorningTime($dateStart->hour)) {
                                //     $dateStart = TimekeepingController::getTimeStartMor($dateStart, $workingTime);
                                // } else {
                                //     $dateStart = TimekeepingController::getTimeStartAfter($dateStart, $workingTime);
                                // }

                                // if ($dateEnd->hour == $workingTime['morningOutSetting']->hour ||
                                //     $dateEnd->hour == $workingTime['morningOutSetting']->hour - 2) {
                                //     $dateEnd = TimekeepingController::getTimeEndMor($dateEnd, $workingTime);
                                // } else {
                                //     $dateEnd = TimekeepingController::getTimeEndAfter($dateEnd, $workingTime);
                                // }
                                $dayAdd = TimekeepingController::getDiffTimesRegister($dateStart->hour, $dateEnd->hour, $dateStart->minute, $dateEnd->minute, $workingTime);
                            }
                            if ($dayAdd >= ManageTimeConst::TIME_MORE_HALF && $dayAdd < 1) {
                                $timekeeping = Timekeeping::getTimekeepingByEmployee($tkTableId, $employeeId, $dateStart->format('Y-m-d'));
                                $objView = new View();
                                $workingTime['morningOutSetting'] = Carbon::createFromFormat('Y-m-d H:i', $dateStart->format('Y-m-d') . ' ' . $workingTime['morningOutSetting']->format('H:i'));
                                $workingTime['afternoonInSetting'] = Carbon::createFromFormat('Y-m-d H:i', $dateStart->format('Y-m-d') . ' ' . $workingTime['afternoonInSetting']->format('H:i'));
                                if ($dateStart->hour == $workingTime['morningInSetting']->hour) {
                                    if ($timekeeping->has_business_trip == ManageTimeConst::HAS_BUSINESS_TRIP_MORNING) {
                                        $time = $timekeeping->register_business_trip_number;
                                    } elseif ($timekeeping->has_business_trip == ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON) {
                                        $time = $objView->getTimeMinuteDiff($workingTime['afternoonInSetting'], $dateEnd);
                                    } else {
                                        $time = 0;
                                    }
                                } elseif ($dateEnd->hour == $workingTime['afternoonOutSetting']->hour) {
                                    if ($timekeeping->has_business_trip == ManageTimeConst::HAS_BUSINESS_TRIP_MORNING) {
                                        $time = $objView->getTimeMinuteDiff($dateStart, $workingTime['morningOutSetting']);
                                    } elseif ($timekeeping->has_business_trip == ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON) {
                                        $time = $timekeeping->register_business_trip_number;
                                    } else {
                                        $time = 0;
                                    }
                                }
                                if ($time) {
                                    $dayAdd = round($time / 480, 2);
                                }
                            }
                            if ($itemReg->used_leave_day == 0) {
                                $daysOff['no_salary'] += $dayAdd;
                            } else {
                                $daysOff['has_salary'] += $dayAdd;
                            }
                        } else {
                            if (Carbon::parse($timeStart)->format('Y-m-d') == Carbon::parse($itemReg->date_start)->format('Y-m-d')
                                && Carbon::parse($itemReg->date_start)->hour > 12) {
                                $duration = TimekeepingController::getDiffTimes($workingTime['afternoonInSetting']->hour, $workingTime['afternoonOutSetting']->hour, $workingTime['afternoonInSetting']->minute, $workingTime['afternoonOutSetting']->minute, $teamCodePrefix, $workingTime);
                                $dayAdd = $duration;
                            }
                            if (Carbon::parse($timeStart)->format('Y-m-d') == Carbon::parse($itemReg->date_end)->format('Y-m-d')
                                && Carbon::parse($itemReg->date_end)->hour < 14) {
                                $duration = TimekeepingController::getDiffTimes($workingTime['morningInSetting']->hour, $workingTime['morningOutSetting']->hour, $workingTime['morningInSetting']->minute, $workingTime['morningOutSetting']->minute, $teamCodePrefix, $workingTime);
                                $dayAdd = $duration;
                            }
                            if ($itemReg->used_leave_day == 0) {
                                $daysOff['no_salary'] += $dayAdd;
                            } else {
                                $daysOff['has_salary'] += $dayAdd;
                            }
                        }
                    }
                    $timeStart = date("Y-m-d", strtotime("+1 day", strtotime($timeStart)));
                }
            }
        }
        return $daysOff;
    }

    /**
     * Compare two dates
     *
     * @param string $date1
     * @param string $date2
     *
     * @return boolean true if date1 > date2
     */
    public static function compareDates($date1, $date2)
    {
        return Carbon::parse($date1)->format('Y-m-d') > Carbon::parse($date2)->format('Y-m-d');
    }

    /**
     * Get days off unapprove of employee
     *
     * @param int $employeeId
     *
     * @return int
     */
    public static function dayOffUnapprove($employeeId)
    {
        $regsUnapporve = LeaveDayRegister::listRegs($employeeId, ['number_days_off'], LeaveDayRegister::STATUS_UNAPPROVE);
        $regsUnapporve = $regsUnapporve->lists('number_days_off')->toArray();
        return array_sum($regsUnapporve);
    }

    /**
     * get days off in time business of employee
     *
     * @param TimekeepingAggregate $timekeepingAggregate
     * @param Timekeeping $timekeepingTable
     *
     * @return array
     */
    public static function daysOffInTimeBusiness($timekeepingAggregate, $timekeepingTable, $teamCodePre = '', $startDate = null, $endDate = null)
    {
        $daysOff = [
            'office_has_salary' => 0,
            'office_no_salary' => 0,
            'trial_has_salary' => 0,
            'trial_no_salary' => 0,
        ];
        if ($teamCodePre == '') {
            $teamCodePre = $timekeepingTable->team_code;
        }
        if (!empty($timekeepingTable->id)) {
            $tkTableId = $timekeepingTable->id;
        } else {
            $tkTableId = $timekeepingTable->timekeeping_table_id;
        }
        $totalRegisterBusinessTrip = $timekeepingAggregate->total_official_business_trip + $timekeepingAggregate->total_trial_business_trip;
        if ($totalRegisterBusinessTrip > 0) {
            $offcialDate = $timekeepingAggregate->offcial_date;
            if (!$offcialDate
                || static::compareDates($offcialDate, $timekeepingTable->end_date)
                || static::compareDates($timekeepingTable->start_date, $offcialDate)) {
                $businessRegisters = BusinessTripEmployee::getRegistersOfEmployeeJoinDate($timekeepingAggregate->employee_id, $timekeepingTable->start_date, $timekeepingTable->end_date);

                foreach ($businessRegisters as $itemBusi) {
                    if (!$startDate) {
                        $startDate = static::compareDates($timekeepingTable->start_date, $itemBusi->start_at) ? $timekeepingTable->start_date : $itemBusi->start_at;
                    }
                    if (!$endDate) {
                        $endDate = static::compareDates($itemBusi->end_at, $timekeepingTable->end_date) ? $timekeepingTable->end_date : $itemBusi->end_at;
                    }
                    $daysOffInTimeBusiness = static::getDaysOffInTimeBusiness($timekeepingAggregate->employee_id, $startDate, $endDate, $teamCodePre, $tkTableId);
                    if (!$offcialDate
                        || static::compareDates($offcialDate, $timekeepingTable->end_date)) {
                        $daysOff['trial_no_salary'] += $daysOffInTimeBusiness['no_salary'];
                        $daysOff['trial_has_salary'] += $daysOffInTimeBusiness['has_salary'];
                    } else {
                        $daysOff['office_no_salary'] += $daysOffInTimeBusiness['no_salary'];
                        $daysOff['office_has_salary'] += $daysOffInTimeBusiness['has_salary'];
                    }
                    $startDate = null;
                    $endDate = null;
                }
            } else {
                // Business in offcial time
                $businessOffcialRegisters = BusinessTripEmployee::getRegistersOfEmployee($timekeepingAggregate->employee_id, $offcialDate, $timekeepingTable->end_date);
                foreach ($businessOffcialRegisters as $itemOffcialBusi) {
                    if (!$startDate) {
                        $startDate = static::compareDates($timekeepingTable->start_date, $itemOffcialBusi->start_at) ? $timekeepingTable->start_date : $itemOffcialBusi->start_at;
                        $startDate = static::compareDates($startDate, $offcialDate) ? $startDate : $offcialDate;
                    }
                    if (!$endDate) {
                        $endDate = static::compareDates($itemOffcialBusi->end_at, $timekeepingTable->end_date) ? $timekeepingTable->end_date : $itemOffcialBusi->end_at;
                    }
                    $daysOffInTimeBusiness = static::getDaysOffInTimeBusiness($timekeepingAggregate->employee_id, $startDate, $endDate, $teamCodePre, $tkTableId);
                    $daysOff['office_no_salary'] += $daysOffInTimeBusiness['no_salary'];
                    $daysOff['office_has_salary'] += $daysOffInTimeBusiness['has_salary'];
                    $startDate = null;
                    $endDate = null;
                }

                // Business in trial time
                $lastTrialDay = Carbon::parse($offcialDate)->subDay()->format('Y-m-d');
                $businessTrialRegisters = BusinessTripEmployee::getRegistersOfEmployee($timekeepingAggregate->employee_id, $timekeepingTable->start_date, $lastTrialDay);
                foreach ($businessTrialRegisters as $itemTrialBusi) {
                    if (!$startDate) {
                        $startDate = static::compareDates($timekeepingTable->start_date, $itemTrialBusi->start_at) ? $timekeepingTable->start_date : $itemTrialBusi->start_at;
                    }
                    if (!$endDate) {
                        $endDate = static::compareDates($itemTrialBusi->end_at, $timekeepingTable->end_date) ? $timekeepingTable->end_date : $itemTrialBusi->end_at;
                        $endDate = static::compareDates($endDate, $offcialDate) ? $offcialDate : $endDate;
                    }
                    $daysOffInTimeBusiness = static::getDaysOffInTimeBusiness($timekeepingAggregate->employee_id, $startDate, $endDate, $teamCodePre, $tkTableId);
                    $daysOff['trial_no_salary'] += $daysOffInTimeBusiness['no_salary'];
                    $daysOff['trial_has_salary'] += $daysOffInTimeBusiness['has_salary'];
                    $startDate = null;
                    $endDate = null;
                }
            }
        }

        return $daysOff;
    }

    /**
     * total working day to salary
     *
     * @param type $timekeepingAggregate
     * @param type $daysOffInTimeBusiness
     * @return type
     */
    public static function totalWorkingDays($timekeepingAggregate, $daysOffInTimeBusiness)
    {
        $totalWorkingOfficialToSalary = $timekeepingAggregate->total_official_working_days
            + $timekeepingAggregate->total_official_business_trip
            + $timekeepingAggregate->total_official_leave_day_has_salary
            + $timekeepingAggregate->total_official_supplement
            + $timekeepingAggregate->total_official_holiay
            - $daysOffInTimeBusiness['office_has_salary']
            - $daysOffInTimeBusiness['office_no_salary']
            - $timekeepingAggregate['number_com_off'];

        $totalWorkingTrialToSalary = $timekeepingAggregate->total_trial_working_days
            + $timekeepingAggregate->total_trial_business_trip
            + $timekeepingAggregate->total_trial_leave_day_has_salary
            + $timekeepingAggregate->total_trial_supplement
            + $timekeepingAggregate->total_trial_holiay
            - $daysOffInTimeBusiness['trial_has_salary']
            - $daysOffInTimeBusiness['trial_no_salary']
            - $timekeepingAggregate['number_com_tri'];

        return [
            'offcial' => $totalWorkingOfficialToSalary,
            'trial' => $totalWorkingTrialToSalary,
        ];
    }

    /**
     * Display time in/out from time keeping
     *
     * @param Timekeeping $timeKeeping
     *
     * @return array
     */
    public static function displayTimeInOut($timeKeeping)
    {
        if (!empty($timeKeeping->start_time_morning_shift_real)) {
            $timeInMor = $timeKeeping->start_time_morning_shift_real;
        } else {
            $timeInMor = '';
        }
        if (!empty($timeKeeping->start_time_afternoon_shift)) {
            $timeInAfter = $timeKeeping->start_time_afternoon_shift;
        } else {
            $timeInAfter = '';
        }
        if (!empty($timeKeeping->end_time_morning_shift)) {
            $timeOutMor = $timeKeeping->end_time_morning_shift;
        } else {
            $timeOutMor = '';
        }
        if (!empty($timeKeeping->end_time_afternoon_shift)) {
            $timeOutAfter = $timeKeeping->end_time_afternoon_shift;
        } else {
            $timeOutAfter = '';
        }
        return [
            'timeInMor' => $timeInMor,
            'timeInAfter' => $timeInAfter,
            'timeOutMor' => $timeOutMor,
            'timeOutAfter' => $timeOutAfter,
            'timeOver' => !empty($timeKeeping->time_over) ? $timeKeeping->time_over : ''
        ];
    }

    /**
     * Get late/early minutes from timekeeping
     *
     * @param Timekeeping $timekeeping
     *
     * @return array
     */
    public static function getLateEarly($timekeeping)
    {
        if (!empty($timekeeping->start_time_morning_shift)) {
            if (!static::hasApplication($timekeeping, ManageTimeConst::PART_TIME_MORNING)) {
                $late = $timekeeping->late_start_shift;
            } else {
                $late = 0;
            }
        } elseif (!empty($timekeeping->start_time_afternoon_shift)) {
            if (!static::hasApplication($timekeeping, ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON)) {
                $late = $timekeeping->late_mid_shift;
            } else {
                $late = 0;
            }
        } else {
            $late = 0;
        }
        if (!empty($timekeeping->end_time_afternoon_shift)) {
            if (!static::hasApplication($timekeeping, ManageTimeConst::HAS_BUSINESS_TRIP_AFTERNOON)) {
                $early = $timekeeping->early_end_shift;
            } else {
                $early = 0;
            }
        } elseif (!empty($timekeeping->end_time_morning_shift)) {
            if (!static::hasApplication($timekeeping, ManageTimeConst::PART_TIME_MORNING)) {
                $early = $timekeeping->early_mid_shift;
            } else {
                $early = 0;
            }
        } else {
            $early = 0;
        }
        return [
            'late' => $late,
            'early' => $early,
        ];
    }

    public static function hasApplication($timekeeping, $type = ManageTimeConst::FULL_TIME)
    {
        $applicationTypes = [
            $timekeeping->has_business_trip,
            $timekeeping->has_leave_day,
            $timekeeping->has_supplement,
            $timekeeping->has_leave_day_no_salary,
        ];
        return in_array($type, $applicationTypes)
            || in_array(ManageTimeConst::FULL_TIME, $applicationTypes);
    }

    /**
     * Case: employee become official in mid month
     * In that month employee has 2 time keeping table: part-time type and offcial type
     * This function check time of application belongs to 1 of 2 tables
     *
     * @param int $type type of time keeping table, part-time or offcial
     * @param Carbon $start start time of application
     * @param Carbon $end end time of application
     * @param string|date $trialDate trial date of employee who created application
     * @param string|date $offcialDate offcial date of employee who created application
     *
     * @return array
     */
    public static function setDateApplicationByTableType($type, $start, $end, $trialDate, $offcialDate)
    {
        $startHour = $start->hour;
        $startMinute = $start->minute;
        $endHour = $end->hour;
        $endMinute = $end->minute;
        $typesOffcial = getOptions::typeEmployeeOfficial();
        $continue = false;

        if (!empty($trialDate)) {
            $dateCompare = $trialDate;
        } elseif (!empty($offcialDate)) {
            $dateCompare = $offcialDate;
        } else {
            $dateCompare = null;
        }
        if (in_array($type, $typesOffcial)) {
            if ($dateCompare) {
                if ($end->lt(Carbon::parse($dateCompare))) {
                    $continue = true;
                }
                if ($start->lt(Carbon::parse($dateCompare))) {
                    $start = Carbon::parse($dateCompare);
                    $start->hour($startHour);
                    $start->minute($startMinute);
                }
            }
        } else {
            if ($dateCompare) {
                if ($start->gte(Carbon::parse($dateCompare))) {
                    $continue = true;
                }
                if ($end->gte(Carbon::parse($dateCompare))) {
                    $end = Carbon::parse($dateCompare)->subDay();
                    $end->hour($endHour);
                    $end->minute($endMinute);
                }
            }
        }
        return [
            'continue' => $continue,
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * re calculator in out time with special employee
     *
     * @param string $emlpEmail
     * @param Carbon $date
     * @param Carbon $timeSettingOfEmp
     */
    private static function reCalSpecialEmplTime($emlpEmail, $date, $timeSettingOfEmp)
    {
        $empSpecInOut = self::getEmplSpecInOutFile();
        if (!$empSpecInOut) {
            return $timeSettingOfEmp;
        }
        if (!isset($empSpecInOut[$emlpEmail]) ||
            !$empSpecInOut[$emlpEmail] ||
            !isset($empSpecInOut[$emlpEmail][$date]) ||
            !$empSpecInOut[$emlpEmail][$date]
        ) {
            return $timeSettingOfEmp;
        }
        $timeSpecInOut = $empSpecInOut[$emlpEmail][$date];
        foreach ($timeSpecInOut as $key => $hour) {
            if (isset($timeSettingOfEmp[$key]) && $timeSettingOfEmp[$key]) {
                $timeClone = clone $timeSettingOfEmp[$key];
                $timeSettingOfEmp[$key] = $timeClone->setTimeFromTimeString($hour);
            }
        }
        return $timeSettingOfEmp;
    }

    /**
     * get employee special in out file
     *
     * @return array
     */
    private static function getEmplSpecInOutFile()
    {
        if (!self::$emplSpecInOut) {
            try {
                if (File::exists(storage_path('app/process/empl_in_out_spec.php'))) {
                    self::$emplSpecInOut = require_once storage_path('app/process/empl_in_out_spec.php');
                }
            } catch (Exception $ex) {
            }
        }
        return self::$emplSpecInOut;
    }

    /**
     * get timekeeping time on timekeeping date
     * @param  [int] $hourStart
     * @param  [int] $hourEnd
     * @param  [int] $minuteStar
     * @param  [int] $minuteEnd
     * @param  [collection] $workingTime
     * @return [float]
     */
    public static function getDiffTimesRegister($hourStart, $hourEnd, $minuteStart, $minuteEnd, $workingTime)
    {
        return TimekeepingController::getDiffTimesRegister($hourStart, $hourEnd, $minuteStart, $minuteEnd, $workingTime);
    }

    /**
     * Check employee is working in Japan
     *
     * @param string $teamCodePre
     *
     * @return boolean
     */
    public static function isWorkingInJapan($teamCodePre)
    {
        return $teamCodePre === Team::CODE_PREFIX_JP;
    }

    /**
     * Get supplement reason for supplement list page
     *
     * @param type $supplement
     *
     * @return string
     */
    public static function getSupplementReason($supplement)
    {
        if (!$supplement->reason_name || $supplement->is_type_other === SupplementReasons::TYPE_OTHER) {
            return $supplement->reason;
        }

        return $supplement->reason_name;
    }

    /**
     * Calculate element of employee (leaveDayRegister, supplementRegister) when business trip register
     * @param [colletion] $element
     * @param [colletion] $businessTripRegister
     * @param [string] $teamCodePrefix
     * @return [colletion]
     */
    public static function separateBranchBusiness($element, $businessTripRegister, $teamCodePrefix)
    {
        $dateStart = 0;
        $dateEnd = 0;

        foreach ($element as $key => $value) {
            if (isset($value->date_start)) {
                $dateStart = $value->date_start;
                $dateEnd = $value->date_end;
            } elseif (isset($value->start_at)) {
                $dateStart = $value->start_at;
                $dateEnd = $value->end_at;
            } else {
                // not something
            }
            $start = Carbon::parse($dateStart)->format('Y-m-d');
            $end = Carbon::parse($dateEnd)->format('Y-m-d');
            $check = true;

            foreach ($businessTripRegister as $keyBus => $business) {
                $startBus = Carbon::parse($business->start_at)->format('Y-m-d');
                $endBus = Carbon::parse($business->end_at)->format('Y-m-d');

                if (((isset($value->creator_id) && $value->creator_id == $business->employee_id)
                        || (isset($value->employee_id) && $value->employee_id == $business->employee_id))
                    && $start >= $startBus
                    && $end <= $endBus
                    && $business->is_long == BusinessTripRegister::IS_LONG) {
                    $check = false;
                }
            }

            if ((($teamCodePrefix == Team::CODE_PREFIX_JP && $check)
                    || ($teamCodePrefix != Team::CODE_PREFIX_JP && !$check))
                && isset($element[$key])) {
                unset($element[$key]);
            }
        }
        return $element;
    }

    /**
     * Get working time setting of employee
     * Default from system data config
     *
     * @param WorkingTime $workingTimeOfEmployee
     *
     * @return array
     */
    public static function findTimeSettingDate($workingTimeOfEmployee, $teamCode, $date, $rangeTimes = null)
    {
        $defaultWorkingTime = ManageTimeCommon::defaultWorkingTime($teamCode, $rangeTimes);
        $morningInSetting = empty($workingTimeOfEmployee->start_time1) ?
            Carbon::createFromFormat('Y-m-d H:i', $date . $defaultWorkingTime['start_time1']) : Carbon::createFromFormat('Y-m-d H:i', $date . $workingTimeOfEmployee->start_time1);
        $morningOutSetting = empty($workingTimeOfEmployee->end_time1) ?
            Carbon::createFromFormat('Y-m-d H:i', $date . $defaultWorkingTime['end_time1']) : Carbon::createFromFormat('Y-m-d H:i', $date . $workingTimeOfEmployee->end_time1);
        $afternoonInSetting = empty($workingTimeOfEmployee->start_time2) ?
            Carbon::createFromFormat('Y-m-d H:i', $date . $defaultWorkingTime['start_time2']) : Carbon::createFromFormat('Y-m-d H:i', $date . $workingTimeOfEmployee->start_time2);
        $afternoonOutSetting = empty($workingTimeOfEmployee->end_time2) ?
            Carbon::createFromFormat('Y-m-d H:i', $date . $defaultWorkingTime['end_time2']) : Carbon::createFromFormat('Y-m-d H:i', $date . $workingTimeOfEmployee->end_time2);
        return [
            'morningInSetting' => $morningInSetting,
            'morningOutSetting' => $morningOutSetting,
            'afternoonInSetting' => $afternoonInSetting,
            'afternoonOutSetting' => $afternoonOutSetting,
        ];
    }

    /**
     * total working day to salary
     *
     * @param type $timekeepingAggregate
     * @param type $daysOffInTimeBusiness
     * @return type
     */
    public static function totalWorkingDayObject($timekeepingAggregate, $daysOffInTimeBusiness)
    {
        $totalWorkingOfficialToSalary = $timekeepingAggregate->total_official_working_days
            + $timekeepingAggregate->total_official_business_trip
            + $timekeepingAggregate->total_official_leave_day_has_salary
            + $timekeepingAggregate->total_official_supplement
            + $timekeepingAggregate->total_official_holiay
            - $daysOffInTimeBusiness['office_has_salary']
            - $daysOffInTimeBusiness['office_no_salary']
            - $timekeepingAggregate->number_com_off;

        $totalWorkingTrialToSalary = $timekeepingAggregate->total_trial_working_days
            + $timekeepingAggregate->total_trial_business_trip
            + $timekeepingAggregate->total_trial_leave_day_has_salary
            + $timekeepingAggregate->total_trial_supplement
            + $timekeepingAggregate->total_trial_holiay
            - $daysOffInTimeBusiness['trial_has_salary']
            - $daysOffInTimeBusiness['trial_no_salary']
            - $timekeepingAggregate->number_com_tri;

        return [
            'offcial' => $totalWorkingOfficialToSalary,
            'trial' => $totalWorkingTrialToSalary,
        ];
    }

    public static function checkLateOutTKOld($infoItem, $date, $value, $timekeepingTable, $typesOffcial, $isWeekendOrHoliday)
    {
        $timeStartAt = $infoItem['vao_luc'];
        $timeStartAtReal = $infoItem['thuc_te_vao_luc'];
        $timeEndAt = $infoItem['ra_luc'];
        $timeEarlyOut = $infoItem['ve_som'];
        $timeLateIn = $infoItem['di_muon'];
        switch ($infoItem['ca_lam_viec']) {
            case ManageTimeConst::MORNING_SHIFT:
                if ($timeStartAt) {
                    $dataInsert['start_time_morning_shift'] = $timeStartAt->format('H:i');
                    $dataInsert['start_time_morning_shift_real'] = $timeStartAtReal->format('H:i');
                    if ($timeLateIn && !$isWeekendOrHoliday
                            && Carbon::parse($date)->format('Y-m-d') > Carbon::parse($value['join_date'])->format('Y-m-d')) {
                        if (in_array($timekeepingTable->type, $typesOffcial)) {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['start_time_morning_shift'] = null;
                                    $dataInsert['start_time_morning_shift_real'] = null;
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_start_shift'] = $lateStartShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['start_time_morning_shift'] = null;
                                    $dataInsert['start_time_morning_shift_real'] = null;
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_start_shift'] = $lateStartShift;
                                }
                            } else {
                                $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                $dataInsert['late_start_shift'] = $lateStartShift;
                            }
                        } else {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['start_time_morning_shift'] = null;
                                    $dataInsert['start_time_morning_shift_real'] = null;
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_start_shift'] = $lateStartShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['start_time_morning_shift'] = null;
                                    $dataInsert['start_time_morning_shift_real'] = null;
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_start_shift'] = $lateStartShift;
                                }
                            } else {
                                $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                $dataInsert['late_start_shift'] = $lateStartShift;
                            }
                        }
                    } else {
                        $dataInsert['late_start_shift'] = 0;
                    }
                } else {
                    $dataInsert['start_time_morning_shift'] = null;
                    $dataInsert['start_time_morning_shift_real'] = null;
                    $dataInsert['late_start_shift'] = 0;
                }
                if ($timeEndAt) {
                    $dataInsert['end_time_morning_shift'] = $timeEndAt->format('H:i');
                    if ($timeEarlyOut && !$isWeekendOrHoliday
                             && Carbon::parse($date)->format('Y-m-d') > Carbon::parse($value['join_date'])->format('Y-m-d')) {
                        if (in_array($timekeepingTable->type, $typesOffcial)) {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['end_time_morning_shift'] = null;
                                    $dataInsert['early_mid_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_mid_shift'] = $earlyMidShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['end_time_morning_shift'] = null;
                                    $dataInsert['early_mid_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_mid_shift'] = $earlyMidShift;
                                }
                            } else {
                                $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                $dataInsert['early_mid_shift'] = $earlyMidShift;
                            }
                        } else {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['end_time_morning_shift'] = null;
                                    $dataInsert['early_mid_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_mid_shift'] = $earlyMidShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['end_time_morning_shift'] = null;
                                    $dataInsert['early_mid_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_mid_shift'] = $earlyMidShift;
                                }
                            } else {
                                $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                $dataInsert['early_mid_shift'] = $earlyMidShift;
                            }
                        }
                    } else {
                        $dataInsert['early_mid_shift'] = 0;
                    }
                } else {
                    $dataInsert['end_time_morning_shift'] = null;
                    $dataInsert['early_mid_shift'] = 0;
                }
                break;
            case ManageTimeConst::AFTERNOON_SHIFT:
                if ($timeStartAt) {
                    $dataInsert['start_time_afternoon_shift'] = $timeStartAt->format('H:i');
                    if ($timeLateIn && !$isWeekendOrHoliday
                             && Carbon::parse($date)->format('Y-m-d') > Carbon::parse($value['join_date'])->format('Y-m-d')) {
                        if (in_array($timekeepingTable->type, $typesOffcial)) {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['start_time_afternoon_shift'] = null;
                                    $dataInsert['late_mid_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_mid_shift'] = $lateStartShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['start_time_afternoon_shift'] = null;
                                    $dataInsert['late_mid_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_mid_shift'] = $lateStartShift;
                                }
                            } else {
                                $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                $dataInsert['late_mid_shift'] = $lateStartShift;
                            }
                        } else {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['start_time_afternoon_shift'] = null;
                                    $dataInsert['late_mid_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_mid_shift'] = $lateStartShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['start_time_afternoon_shift'] = null;
                                    $dataInsert['late_mid_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_mid_shift'] = $lateStartShift;
                                }
                            } else {
                                $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                $dataInsert['late_mid_shift'] = $lateStartShift;
                            }
                        }
                    } else {
                        $dataInsert['late_mid_shift'] = 0;
                    }
                } else {
                    $dataInsert['start_time_afternoon_shift'] = null;
                    $dataInsert['late_mid_shift'] = 0;
                }
                if ($timeEndAt) {
                    $dataInsert['end_time_afternoon_shift'] = $timeEndAt->format('H:i');
                    if ($timeEarlyOut && !$isWeekendOrHoliday
                             && Carbon::parse($date)->format('Y-m-d') > Carbon::parse($value['join_date'])->format('Y-m-d')) {
                        if (in_array($timekeepingTable->type, $typesOffcial)) {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['end_time_afternoon_shift'] = null;
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['end_time_afternoon_shift'] = null;
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } else {
                                $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                $dataInsert['early_end_shift'] = $earlyMidShift;
                            }
                        } else {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['end_time_afternoon_shift'] = null;
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['end_time_afternoon_shift'] = null;
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } else {
                                $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                $dataInsert['early_end_shift'] = $earlyMidShift;
                            }
                        }
                    } else {
                        $dataInsert['early_end_shift'] = 0;
                    }
                } else {
                    $dataInsert['end_time_afternoon_shift'] = null;
                    $dataInsert['early_end_shift'] = 0;
                }
                break;
            default:
                if ($timeStartAt) {
                    $dataInsert['start_time_morning_shift'] = $timeStartAt->format('H:i');
                    $dataInsert['start_time_morning_shift_real'] = $timeStartAtReal->format('H:i');
                    if ($timeLateIn && !$isWeekendOrHoliday
                            && Carbon::parse($date)->format('Y-m-d') > Carbon::parse($value['join_date'])->format('Y-m-d')) {
                        if (in_array($timekeepingTable->type, $typesOffcial)) {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['start_time_morning_shift'] = null;
                                    $dataInsert['start_time_morning_shift_real'] = null;
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_start_shift'] = $lateStartShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['start_time_morning_shift'] = null;
                                    $dataInsert['start_time_morning_shift_real'] = null;
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_start_shift'] = $lateStartShift;
                                }
                            } else {
                                $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                $dataInsert['late_start_shift'] = $lateStartShift;
                            }
                        } else {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['start_time_morning_shift'] = null;
                                    $dataInsert['start_time_morning_shift_real'] = null;
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_start_shift'] = $lateStartShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['start_time_morning_shift'] = null;
                                    $dataInsert['start_time_morning_shift_real'] = null;
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_start_shift'] = $lateStartShift;
                                }
                            } else {
                                $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                $dataInsert['late_start_shift'] = $lateStartShift;
                            }
                        }
                    } else {
                        $dataInsert['late_start_shift'] = 0;
                    }
                } else {
                    $dataInsert['start_time_morning_shift'] = null;
                    $dataInsert['start_time_morning_shift_real'] = null;
                    $dataInsert['late_start_shift'] = 0;
                }
                if ($timeEndAt) {
                    $dataInsert['end_time_afternoon_shift'] = $timeEndAt->format('H:i');
                    if ($timeEarlyOut && !$isWeekendOrHoliday
                             && Carbon::parse($date)->format('Y-m-d') > Carbon::parse($value['join_date'])->format('Y-m-d')) {
                        if (in_array($timekeepingTable->type, $typesOffcial)) {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['end_time_morning_shift'] = null;
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['end_time_morning_shift'] = null;
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } else {
                                $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                $dataInsert['early_end_shift'] = $earlyMidShift;
                            }
                        } else {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['end_time_morning_shift'] = null;
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['end_time_morning_shift'] = null;
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } else {
                                $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                $dataInsert['early_end_shift'] = $earlyMidShift;
                            }
                        }
                    } else {
                        $dataInsert['early_end_shift'] = 0;
                    }
                } else {
                    $dataInsert['end_time_afternoon_shift'] = null;
                    $dataInsert['early_end_shift'] = 0;
                }
                break;
        }
        return $dataInsert;
    }

    public static function checkLateOutTKNew($infoItem, $date, $value, $timekeepingTable, $typesOffcial, $isWeekendOrHoliday)
    {
        $timeStartAt = $infoItem['vao_luc'];
        $timeStartAtReal = $infoItem['thuc_te_vao_luc'];
        $timeEndAt = $infoItem['ra_luc'];
        $timeEarlyOut = $infoItem['ve_som'];
        $timeLateIn = $infoItem['di_muon'];
        switch ($infoItem['ca_lam_viec']) {
            case ManageTimeConst::MORNING_SHIFT:
                if ($timeStartAt) {
                    $dataInsert['start_time_morning_shift'] = $timeStartAt->format('H:i');
                    $dataInsert['start_time_morning_shift_real'] = $timeStartAtReal->format('H:i');
                    if ($timeLateIn && !$isWeekendOrHoliday
                            && Carbon::parse($date)->format('Y-m-d') >= Carbon::parse($value['join_date'])->format('Y-m-d')) {
                        if (in_array($timekeepingTable->type, $typesOffcial)) {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_start_shift'] = $lateStartShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_start_shift'] = $lateStartShift;
                                }
                            } else {
                                $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                $dataInsert['late_start_shift'] = $lateStartShift;
                            }
                        } else {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_start_shift'] = $lateStartShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['late_start_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                }
                            } else {
                                $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                $dataInsert['late_start_shift'] = $lateStartShift;
                            }
                        }
                    } else {
                        $dataInsert['late_start_shift'] = 0;
                    }
                } else {
                    $dataInsert['start_time_morning_shift'] = 0;
                    $dataInsert['start_time_morning_shift_real'] = 0;
                    $dataInsert['late_start_shift'] = 0;
                }
                if ($timeEndAt) {
                    $dataInsert['end_time_morning_shift'] = $timeEndAt->format('H:i');
                    if ($timeEarlyOut && !$isWeekendOrHoliday
                             && Carbon::parse($date)->format('Y-m-d') >= Carbon::parse($value['join_date'])->format('Y-m-d')) {
                        if (in_array($timekeepingTable->type, $typesOffcial)) {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['early_mid_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_mid_shift'] = $earlyMidShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['early_mid_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_mid_shift'] = $earlyMidShift;
                                }
                            } else {
                                $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                $dataInsert['early_mid_shift'] = $earlyMidShift;
                            }
                        } else {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['early_mid_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_mid_shift'] = $earlyMidShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['early_mid_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_mid_shift'] = $earlyMidShift;
                                }
                            } else {
                                $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                $dataInsert['early_mid_shift'] = $earlyMidShift;
                            }
                        }
                    } else {
                        $dataInsert['early_mid_shift'] = 0;
                    }
                } else {
                    $dataInsert['end_time_morning_shift'] = 0;
                    $dataInsert['early_mid_shift'] = 0;
                }
                break;
            default:
                if ($timeStartAt) {
                    $dataInsert['start_time_afternoon_shift'] = $timeStartAt->format('H:i');
                    if ($timeLateIn && !$isWeekendOrHoliday
                             && Carbon::parse($date)->format('Y-m-d') >= Carbon::parse($value['join_date'])->format('Y-m-d')) {
                        if (in_array($timekeepingTable->type, $typesOffcial)) {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['late_mid_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_mid_shift'] = $lateStartShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['late_mid_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_mid_shift'] = $lateStartShift;
                                }
                            } else {
                                $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                $dataInsert['late_mid_shift'] = $lateStartShift;
                            }
                        } else {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['late_mid_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_mid_shift'] = $lateStartShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['late_mid_shift'] = 0;
                                } else {
                                    $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                    $dataInsert['late_mid_shift'] = $lateStartShift;
                                }
                            } else {
                                $lateStartShift = $timeLateIn->hour * 60 + $timeLateIn->minute;
                                $dataInsert['late_mid_shift'] = $lateStartShift;
                            }
                        }
                    } else {
                        $dataInsert['late_mid_shift'] = 0;
                    }
                } else {
                    $dataInsert['start_time_afternoon_shift'] = 0;
                    $dataInsert['late_mid_shift'] = 0;
                }
                if ($timeEndAt) {
                    $dataInsert['end_time_afternoon_shift'] = $timeEndAt->format('H:i');
                    if ($timeEarlyOut && !$isWeekendOrHoliday
                             && Carbon::parse($date)->format('Y-m-d') > Carbon::parse($value['join_date'])->format('Y-m-d')) {
                        if (in_array($timekeepingTable->type, $typesOffcial)) {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->lt(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } else {
                                $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                $dataInsert['early_end_shift'] = $earlyMidShift;
                            }
                        } else {
                            if (!empty($value['trial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['trial_date']))) {
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } elseif (!empty($value['offcial_date'])) {
                                if (Carbon::parse($date)->gte(Carbon::parse($value['offcial_date']))) {
                                    $dataInsert['early_end_shift'] = 0;
                                } else {
                                    $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                    $dataInsert['early_end_shift'] = $earlyMidShift;
                                }
                            } else {
                                $earlyMidShift = $timeEarlyOut->hour * 60 + $timeEarlyOut->minute;
                                $dataInsert['early_end_shift'] = $earlyMidShift;
                            }
                        }
                    } else {
                        $dataInsert['early_end_shift'] = 0;
                    }
                } else {
                    $dataInsert['end_time_afternoon_shift'] = 0;
                    $dataInsert['early_end_shift'] = 0;
                }
                break;
        }
        return $dataInsert;
    }

    /**
     * get table filter in cookie
     */
    public static function getFilterLeaveDayTable()
    {
        $url = route('manage_time::admin.manage-day-of-leave.index') . '/';
        $tblLeaveDay = LeaveDay::getTableName();
        $tblLeaveDayBaseline = LeaveDayBaseline::getTableName();
        $tblFilter = null;
        $dataFilter = Form::getFilterData(null, null, $url);
        if (isset($dataFilter['number']) && is_array($dataFilter['number'])) {
            list($tblFilter) = explode('.', key($dataFilter['number']));
        } else {
            if (isset($dataFilter["{$tblLeaveDay}.note"])) {
                $tblFilter = $tblLeaveDay;
            } elseif (isset($dataFilter["{$tblLeaveDayBaseline}.note"])) {
                $tblFilter = $tblLeaveDayBaseline;
            } else {
                // nothing
            }
        }

        return $tblFilter;
    }

    /**
     * get time working quater of team (time 1/4)
     * Chú Ý: phần này kết hợp với đăng ký time làm việc chỉ đúng với team không đăng ky 1/4
     *
     * @param  [object] $workingTimeOfEmployee
     * @param  string $teamCodePrefix
     * @param  string $date
     * @return [type]
     */
    public function getTimeWorkingQuater($workingTimeOfEmployee, $teamCodePrefix = 'hanoi', $date = '')
    {
        if (!empty($workingTimeOfEmployee->start_time1)) {
            if ($teamCodePrefix == Team::CODE_PREFIX_JP) {
                $quater = $this->getTimeRegisterWorkingNot14($workingTimeOfEmployee);
            } else {
                $quater = with(new ManageTimeCommon())->getTimeSettingQuarterByWTRegister($workingTimeOfEmployee);
            }
        } else {
            $quater = with(new ManageTimeCommon())->getTimeSettingQuarter($teamCodePrefix);
        }

        if ($date == '') {
            $date = Carbon::now()->format('Y-m-d');
        }
        if (count($quater['timeIn']) > 2) {
            return [
                'timeInMor' => [
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeInMor'][0] . $date),
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeInMor'][1] . $date),
                ],
                'timeOutMor' => [
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeOutMor'][0] . $date),
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeOutMor'][1] . $date),
                ],
                'timeInAfter' => [
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeInAfter'][0] . $date),
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeInAfter'][1] . $date),
                ],
                'timeOutAfter' => [
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeOutAfter'][0] . $date),
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeOutAfter'][1] . $date),
                ],
                'timeIn' => [
                    $quater['timeInMor'][0],
                    $quater['timeInMor'][1],
                    $quater['timeInAfter'][0],
                    $quater['timeInAfter'][1],
                ],
                'timeOut' => [
                    $quater['timeOutMor'][0],
                    $quater['timeOutMor'][1],
                    $quater['timeOutAfter'][0],
                    $quater['timeOutAfter'][1],
                ]
            ];
        } else {
            return [
                'timeInMor' => [
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeInMor'][0] . $date),
                ],
                'timeOutMor' => [
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeOutMor'][0] . $date),
                ],
                'timeInAfter' => [
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeInAfter'][0] . $date),
                ],
                'timeOutAfter' => [
                    Carbon::createFromFormat('H:i Y-m-d', $quater['timeOutAfter'][0] . $date),
                ],
                'timeIn' => [
                    $quater['timeInMor'][0],
                    $quater['timeInAfter'][0],
                ],
                'timeOut' => [
                    $quater['timeOutMor'][0],
                    $quater['timeOutAfter'][0],
                ]
            ];
        }
    }

    /**
     * get time register working of employee
     * chỉ áp dụng cho nhật bản vì ko có đăng ký 1/4
     */
    public function getTimeRegisterWorkingNot14($workingTimeOfEmployee)
    {
        return [
            'timeInMor' => [
                $workingTimeOfEmployee->start_time1,
            ],
            'timeOutMor' => [
                $workingTimeOfEmployee->end_time1,
            ],
            'timeInAfter' => [
                $workingTimeOfEmployee->start_time2,
            ],
            'timeOutAfter' => [
                $workingTimeOfEmployee->end_time2,
            ],
            'timeIn' => [
                $workingTimeOfEmployee->start_time1,
                $workingTimeOfEmployee->start_time2,
            ],
            'timeOut' => [
                $workingTimeOfEmployee->end_time1,
                $workingTimeOfEmployee->end_time2,
            ]
        ];
    }

    /**
     * kiểm tra trùng lặp khi duyệt đơn đã hủy
     * trùng các đơn đã duyệt
     * trùng các đơn trong danh sách phê duyệt
     * @param  [array] $registerIds [id: disaparoved]
     * @param  [string] $category
     * @return [type]
     */
    public function checkOverlap($registerIds, $category)
    {
        switch ($category) {
            case self::STR_SUPPLEMENT:
                $obj = new SupplementRegister();
                $infoApproved = $obj->getExistDeleteWithOtherSupp($registerIds);
                $infoDelete = $obj->getExistDelete($registerIds);
                break;
            case self::STR_LEAVE_DAYS:
                $obj = new LeaveDayRegister();
                $infoApproved = $obj->getExistDeleteWithOtherLeaveDays($registerIds);
                $infoDelete = $obj->getExistDelete($registerIds);
                break;
            case self::STR_MISSION:
                $obj = new BusinessTripRegister();
                $infoApproved = $obj->getExistDeleteWithOtherMission($registerIds);
                $infoDelete = $obj->getExistDelete($registerIds);
                break;
            default:
                break;
        }
        if (count($infoApproved)) {
            return [
                'errors' => [Lang::get('manage_time::message.The registration period overlaps in the approved list')],
            ];
        } elseif (count($infoDelete)) {
            return [
                'errors' => [Lang::get('manage_time::message.Registration time has been identical')],
            ];
        } else {
            return [];
        }
    }

    public function getTimeMinuteDiff($time1, $time2)
    {
        $diff = $time1->diffInSeconds($time2);
        $diff = Carbon::createFromFormat('H:i', gmdate('H:i', $diff));
        return $diff->hour * 60 + $diff->minute;
    }

    /**
     * [sendEmailSystena description]
     * @return
     */
    public function sendEmailSystena()
    {
        $files = Storage::files(self::FOLDER_EXP_SYSTENA);
        if (!$files) {
            return true;
        }
        try {
            ini_set('memory_limit', '1024M');
            foreach ($files as $file) {
                ProjectController::exportProjectSystenaCron($file);
                Storage::delete($file);
            }
        } catch (Exception $ex) {
            Log::info($ex);
        }
    }

    /**
     * @param $file
     * @param $fileName
     * @param $folder
     * @param int $access
     * @return bool
     */
    public function storageFile($file, $fileName, $folder, $access = self::ACCESS_FOLDER)
    {
        if (!Storage::exists($folder)) {
            Storage::makeDirectory($folder, $access);
        }
        @chmod(storage_path('app/' . $folder), $access);
        $fileName = $fileName . '.' . $file->getClientOriginalExtension();
        // Move file to folder
        $folderPath = storage_path('app/' . $folder);
        if (Storage::exists($folder . '/' . $fileName)) {
            return false;
        }
        $file->move($folderPath, $fileName);
        @chmod($folderPath . '/' . $fileName, $access);
        return true;
    }

    /**
     * @param $employee
     * @param $route
     * @return bool
     */
    public function getScopeTeamUser($employee, $route)
    {
        $objPermission = new Permission($employee);
        return $objPermission->isScopeTeam(null, $route);
    }

    /**
     * @param $employee
     * @param $route
     * @return bool
     */
    public function isCompanyUser($employee, $route)
    {
        $objPermission = new Permission($employee);
        return $objPermission->isScopeCompany(null, $route);
    }

    /**
     * check dates is holiday
     * @param $dates
     * @param $branch
     * @return bool
     */
    public function isDateHoliday($dates, $branch)
    {
        $arrHolidays = CoreConfigData::getHolidayTeam($branch);
        foreach ($dates as $date) {
            $cbDate = Carbon::parse($date);
            $check = ManageTimeCommon::isHolidays($cbDate, $arrHolidays);
            if ($check) {
                return $check;
            }
        }
        return false;
    }

    /**
     * check full day cate leave day no salary holiday
     * @param Request $request
     * @return bool
     */
    public function isFullDayHoliday(Request $request)
    {
        $objTK = new Timekeeping();
        $idLeaveReasonNoSalaries = $objTK->getIdLeaveReasonNoSalaryHolidays();

        if (!in_array($request->reason, $idLeaveReasonNoSalaries)) {
            return false;
        }
        if (!empty($request->employee_id)) {
            $employeeId = $request->employee_id;
        } else {
            $userCurrent = Permission::getInstance()->getEmployee();
            $employeeId = $userCurrent->id;
        }
        $objTeam = new Team();
        $branch = $objTeam->getBranchPrefixByEmpId($employeeId);
        $dates = [
            $request->start_date,
            $request->end_date,
        ];
        if ($this->isDateHoliday($dates, $branch)) {
            $arr = explode('.', $request->number_validate);
            if (isset($arr[1]) && !empty((int)$arr[1])) {
                return true;
            }
        }
        return false;
    }

    /**
     * nhân viên không đi làm muộn hoặc đi muộn 1 thời gian
     * @param $empIds
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function getDataNotLateTime($empIds, $startDate, $endDate)
    {
        $data = [];
        $timekeepingStartDate = Carbon::parse($startDate);
        $timekeepingEndDate = Carbon::parse($endDate);
        $objNotLateTime = new TimekeepingNotLateTime();
        $notLateTime = $objNotLateTime->getNotLateTimeByEmpDate($empIds, $startDate, $endDate);
        if (!count($notLateTime)) {
            return $data;
        }
        while ($timekeepingStartDate <= $timekeepingEndDate) {
            $date = $timekeepingStartDate->format('Y-m-d');
            foreach ($notLateTime as $item) {
                if ($date >= $item->start_date &&
                    $date <= $item->end_date) {
                    $data[$item->emp_email][$date] = $item->minute;
                }
            }
            $timekeepingStartDate->addDay();
        }
        return $data;
    }


    /**
     * get array date note late of employee
     *
     * @param  array $empIds
     * @param  carbon $startDate
     * @param  carbon $endDate
     * @return array
     */
    public function getDataNotLate($empIds, $startDate, $endDate)
    {
        $data = [];
        $objNotLate = new TimekeepingNotLate();
        $notLate = $objNotLate->getNotLateByEmpDate($empIds);
        if (!count($notLate)) {
            return $data;
        }
        foreach ($notLate as $item) {
            $start = clone $startDate;
            $arrDays = explode(',', $item->weekdays);
            while ($start <= $endDate) {
                if (in_array($start->dayOfWeek, $arrDays)) {
                    $data[$item->emp_email][$start->format('Y-m-d')] = 240;
                }
                $start->addDay();
            }
        }
        return $data;
    }
        
    /**
     * business trip onsite
     * get array year with key day
     *
     * @param  mixed $diffday
     * @return array
     */
    public function getDayYear($diffday)
    {
        $numberYear = static::NUMBER_YEAR;
        $dayYear = static::DAY_YEAR;
        $i = 1;
        $arrYear = [];
        do {
            $key = $dayYear + $diffday;
            $arrYear[$key] = $i;
            $dayYear += $dayYear;
            $i++;
        } while($i <= $numberYear);
        
        return $arrYear;
    }
        
    /**
     * get year by day
     *
     * @param  int $numberDay
     * @param  array $dayYear [day => year]
     * @return int
     */
    public function getYearByDay($numberDay, $dayYear)
    {
        if (!is_array($dayYear)) {
            return 0;
        }
        foreach ($dayYear as $day => $year) {
            if ($numberDay <= $day) {
                return $year;
            }
        }
        return 0;
    }
        
    /**
     * insertTimekeeping
     *
     * @param  collection $userCurrent
     * @param  array $dataInsertTK
     * @return void
     */
    public function insertTimekeeping($userCurrent, $dataInsertTK)
    {
        if (!count($dataInsertTK)) {
            return;
        }
        $teamCodePre = Team::getOnlyOneTeamCodePrefix($userCurrent);
        if ($teamCodePre != Team::CODE_PREFIX_JP) {
            $objView = new ManageTimeView();
            $arrLeaveDayTK = $objView->getArrayByRegisterObject($dataInsertTK);
            if ($arrLeaveDayTK) {
                ViewTimeKeeping::createFileTk($arrLeaveDayTK);
            }
        }
        return;
    }
    
    /**
     * doi cac don dang ky thanh mang thoi gian
     *
     * @param  collection $collections
     * @return array
     */
    public function getArrayByRegisterObject($collections)
    {
        if (!count($collections)) {
            return [];
        }
        $data = [];
        foreach ($collections as $item) {
            $empId = '';
            $startDate = '';
            $endDate = '';
            if (!empty($item->creator_id)) {
                $empId = $item->creator_id;
                $startDate = explode(" ", $item->date_start)[0];
                $endDate = explode(" ", $item->date_end)[0];
            } elseif (!empty($item->employee_id)) {
                $empId = $item->employee_id;
                $startDate = explode(" ", $item->start_at)[0];
                $endDate = explode(" ", $item->end_at)[0];
            } else {
                // no thing
            }
            if ($empId) {
                if (isset($data[$empId])) {
                    if ($data[$empId]['start_date'] > $startDate) {
                        $data[$empId]['start_date'] = $startDate;
                    }
                    if ($data[$empId]['start_date'] < $endDate) {
                        $data[$empId]['end_date'] = $endDate;
                    }
                } else {
                    $data[$empId] = [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * statistic number leave days of employee onsite in year
     * @param integer $year
     * @return array
     */
    public function statisticLeaveDaysEmployeeOnsite($year)
    {
        $query = "SELECT 
            employees.id AS employee_id,
            employees.employee_code,
            business_trip_registers.id AS business_trip_register_id,
            leave_day_registers.id AS leave_day_register_id,
            business_trip_employees.start_at AS business_trip_start_at,
            business_trip_employees.end_at AS business_trip_end_at,
            leave_day_registers.date_start AS leave_day_start_at,
            leave_day_registers.date_end AS leave_day_end_at,
            leave_day_registers.number_days_off
        FROM business_trip_registers
        INNER JOIN business_trip_employees ON business_trip_registers.id = business_trip_employees.register_id
        INNER JOIN employees ON employees.id = business_trip_employees.employee_id
            AND employees.deleted_at IS NULL
        INNER JOIN leave_day_registers ON leave_day_registers.creator_id = employees.id
            AND leave_day_registers.status = '4'
            AND leave_day_registers.deleted_at IS NULL
            AND (leave_day_registers.date_start <= business_trip_employees.end_at
            AND leave_day_registers.date_end >= business_trip_employees.start_at)
        INNER JOIN leave_day_reasons ON leave_day_reasons.id = leave_day_registers.reason_id
            AND leave_day_reasons.deleted_at IS NULL
            AND leave_day_reasons.used_leave_day = '1'
        WHERE business_trip_registers.deleted_at IS NULL
            AND business_trip_registers.status = '4'
            AND (DATE(business_trip_employees.start_at) <= '{$year}-12-31'
            AND DATE(business_trip_employees.end_at) >= '{$year}-01-01')
        ORDER BY employees.id";
        $dataLeaveDaysInYear = [];
        /* init original data leave day in year */
        $dataOriginalLeaveDayInYear = [];
        for ($i = 1; $i <= 12; $i++) {
            $dataOriginalLeaveDayInYear[sprintf("{$year}-%02d", $i)] = 0;
        }
        $employees = [];
        foreach (DB::select($query) as $item) {
            $item = (array) $item;
            $item['start_at'] = max($item['leave_day_start_at'], $item['business_trip_start_at']);
            $item['end_at'] = min($item['leave_day_end_at'], $item['business_trip_end_at']);
            $employeeId = $item['employee_id'];
            if (!isset($dataLeaveDaysInYear[$employeeId])) {
                $dataLeaveDaysInYear[$employeeId] = [
                    'employee_code' => $item['employee_code'],
                    'leave_days' => $dataOriginalLeaveDayInYear,
                    'total_leave_days' => 0,
                ];
            }
            $month = substr($item['start_at'], 0, 7);
            $monthEndAt = substr($item['end_at'], 0, 7);
            /* the most common cases */
            if ($item['start_at'] === $item['leave_day_start_at'] && $item['end_at'] === $item['leave_day_end_at'] && $month === $monthEndAt) {
                if (isset($dataLeaveDaysInYear[$employeeId]['leave_days'][$month])) {
                    $dataLeaveDaysInYear[$employeeId]['leave_days'][$month] += $item['number_days_off'];
                    $dataLeaveDaysInYear[$employeeId]['total_leave_days'] += $item['number_days_off'];
                }
                continue;
            }

            if (!isset($employees[$employeeId])) {
                $employees[$employeeId]['info'] = Employee::find($employeeId);
                $employees[$employeeId]['team_code'] = Team::getOnlyOneTeamCodePrefix($employees[$employeeId]['info']);
            }
            $employee = $employees[$employeeId]['info'];
            $teamCode = $employees[$employeeId]['team_code'];
            if ($month === $monthEndAt) {
                if (isset($dataLeaveDaysInYear[$employeeId]['leave_days'][$month])) {
                    $numDayOffs = ManageLeaveDay::getTimeLeaveDay($item['start_at'], $item['end_at'], $employee, $teamCode);
                    $dataLeaveDaysInYear[$employeeId]['leave_days'][$month] += $numDayOffs;
                    $dataLeaveDaysInYear[$employeeId]['total_leave_days'] += $numDayOffs;
                }
                continue;
            }

            /* cases rarely occur: leave day register in two months */
            $monthArray = $this->splitMonthInPeriodTime($item['start_at'], $item['end_at']);
            $dateArray = [];
            foreach ($monthArray as $monthItem) {
                $dateArray[] = $monthItem['start'];
                $dateArray[] = $monthItem['end'];
            }
            unset($dateArray[count($dateArray) - 1], $dateArray[0]);
            $timeSetting = $this->getEmpTimeSettings($employeeId, $teamCode, $dateArray);
            foreach ($monthArray as $monthItem) {
                $month = substr($monthItem['start'], 0 ,7);
                if (isset($dataLeaveDaysInYear[$employeeId]['leave_days'][$month])) {
                    $startAt = $monthItem['start'];
                    if (isset($timeSetting[$startAt])) {
                        $tsMorning = $timeSetting[$startAt]['morningInSetting'];
                        $startAt .= sprintf(" %02d:%02d:00", $tsMorning['hour'], $tsMorning['minute']);
                    }
                    $endAt = $monthItem['end'];
                    if (isset($timeSetting[$endAt])) {
                        $tsAfternoon = $timeSetting[$endAt]['afternoonOutSetting'];
                        $endAt .= sprintf(" %02d:%02d:00", $tsAfternoon['hour'], $tsAfternoon['minute']);
                    }
                    $numDayOffs = ManageLeaveDay::getTimeLeaveDay($startAt, $endAt, $employee);
                    $dataLeaveDaysInYear[$employeeId]['leave_days'][$month] += $numDayOffs;
                    $dataLeaveDaysInYear[$employeeId]['total_leave_days'] += $numDayOffs;
                }
            }
        }
        return $dataLeaveDaysInYear;
    }

    /**
     * get list time setting in list date
     * @param number $empId
     * @param string $teamCode
     * @param array $dateArray
     * @return array
     */
    public function getEmpTimeSettings($empId, $teamCode, $dateArray) {
        $timeSetting = [];
        $manageTimeView = new ManageTimeView();
        $workingTimeView = new WorkingTimeView();
        $empWorkingTimes = (new WorkingTimeRegister())->getWorkingTimeList($empId);
        foreach ($dateArray as $date) {
            $empWorkingTime = $workingTimeView->getTimeWorkingOfDate($empWorkingTimes, $date);
            $timeSetting[$date] = $manageTimeView->buildTimeSettingEmployee($empWorkingTime, $teamCode);
        }
        return $timeSetting;
    }

    /**
     * split month (first day and last date in month) in period time
     * @param string $start {format: 'Y-m-d'}
     * @param string $end {format: 'Y-m-d'}
     * @return array
     */
    function splitMonthInPeriodTime($start, $end)
    {
        $monthArray = [];
        $originStart = $start;
        $start = substr($start, 0, 7);
        $i = 0;
        while ($start <= $end) {
            $monthArray[$i]['start'] = $i === 0 ? $originStart : "{$start}-01";
            $lastDayInMonth = date('Y-m-t', strtotime($start));
            $start = $this->addMonths($start, 1);
            if ($start <= $end) {
                $monthArray[$i]['end'] = $lastDayInMonth;
            } else {
                $monthArray[$i]['end'] = $end;
            }
            $i++;
        }
        return $monthArray;
    }

    /**
     * add month with N months
     *
     * @param string $month {format: Y-m}
     * @param integer $n
     * @return string {format: 'Y-m'}
     */
    function addMonths($month, $n)
    {
        $yyyy = (int)substr($month, 0, 4);
        $mm = (int)substr($month, 5, 2);
        $totalMonths = ($yyyy * 12) + ($mm - 1) + $n;
        return sprintf("%04d-%02d", (int) $totalMonths / 12, $totalMonths % 12 + 1);
    }

    /**
     * get complement of two period times
     * @param array $periodOne [start, end]
     * @param array $periodTwo [start, end]
     * @return array
     */
    public function getComplementTwoPeriods($periodOne, $periodTwo) {
        if ($periodOne[0] > $periodTwo[1] || $periodTwo[0] > $periodOne[1]) {
            return [$periodOne, $periodTwo];
        }
        $periods = [];
        /* left complement */
        $startMin = min($periodOne[0], $periodTwo[0]);
        $startMax = max($periodOne[0], $periodTwo[0]);
        if ($startMin !== $startMax) {
            $periods[] = [$startMin, date('Y-m-d', strtotime($startMax) - 86400)];
        }
        /* right complement */
        $finishMin = min($periodOne[1], $periodTwo[1]);
        $finishMax = max($periodOne[1], $periodTwo[1]);
        if ($finishMin !== $finishMax) {
            $periods[] = [date('Y-m-d', strtotime($finishMin) + 86400), $finishMax];
        }
        return $periods;
    }
    
    // =================== start update rate agregate ==============================
    /**
     * cập nhât bảng công tong hop
     */
    public function updateSalaryRateAgregateCron()
    {
        $files = Storage::files(self::FOLDER_SALARY_RATE_AGGREGATE);
        if (!$files) {
            return;
        }
        try {
            Log::info('Start cron job salary rate aggregate');
            ini_set('memory_limit', '1024M');
            foreach ($files as $file) {
                $this->setDataSalaryRateAgregate($file);
                Storage::delete($file);
            }
            Log::info('End cron job salary rate aggregate');
        } catch (Exception $ex) {
            Log::info($ex);
        }
        return;
    }
    
    /**
     * @param $file
     * @return bool|void
     * @throws Exception
     */
    public function setDataSalaryRateAgregate($file)
    {
        set_time_limit(360);
        $resultMore = '';
        $result = preg_match('/[0-9]+.*$/', $file, $resultMore);
        if (!$result || !$resultMore) {
            return true;
        }
        $resultMore = $resultMore[0];
        $dataRequest['timekeeping_table_id'] = substr($resultMore, 0, strrpos($resultMore, '.'));
        $excel = Excel::selectSheetsByIndex(0)->load(storage_path(self::FOLDER_APP . '/'. $file), function ($reader) {
        })->get()->toArray();
        if (count($excel)) {
            $dataRelate = $excel[0];
            if ($dataRelate['emp_ids'] != '') {
                $dataRelate['emp_ids'] = explode('_', $dataRelate['emp_ids']);
            } else {
                $dataRelate['emp_ids'] = [];
            }
            $dataRelate['timekeeping_table_id'] = $dataRequest['timekeeping_table_id'];
        } else {
            Log::info('Thiếu thông tin cập nhật tổng hợp công');
            return true;
        }
        $timeKeepingTable = TimekeepingTable::find($dataRequest['timekeeping_table_id']);
        if (!$timeKeepingTable) {
            Log::info('cập nhật tổng hợp công không tìm thấy bảng công');
            return true;
        }

        DB::beginTransaction();
        try {
            $this->updateSalaryRateAgregate($timeKeepingTable, $dataRelate);
            if ($creator = $timeKeepingTable->getCreatorInfo()) {
                $templateEmail = 'manage_time::template.timekeeping.mail_update_related';
                $dataInsertEmail['mail_to'] = $creator->email;
                $dataInsertEmail['receiver_name'] = $creator->name;
                $dataInsertEmail['timekeeping_table_name'] = $timeKeepingTable->timekeeping_table_name;
                $dataInsertEmail['month'] = $timeKeepingTable->month;
                $dataInsertEmail['year'] = $timeKeepingTable->year;
                $dataInsertEmail['link'] = route('manage_time::timekeeping.timekeeping-aggregate', ['timekeepingTableId' => $timeKeepingTable->id]);

                $dataInsertEmail['mail_title'] = Lang::get('manage_time::message.[Notification][Timekeeping][Update aggregate status success] :subject', ['subject' => $timeKeepingTable->timekeeping_table_name]);
                $dataInsertEmail['content'] = Lang::get('manage_time::message.Process update aggregate timekeeping successfuly.');
                ManageTimeCommon::pushEmailToQueue($dataInsertEmail, $templateEmail);
            }
            DB::commit();
            return;
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            if ($creator = $timeKeepingTable->getCreatorInfo()) {
                $dataInsertEmail = [];
                $templateEmail = 'manage_time::template.timekeeping.mail_update_related';
                $dataInsertEmail['mail_to'] = $creator->email;
                $dataInsertEmail['receiver_name'] = $creator->name;
                $dataInsertEmail['timekeeping_table_name'] = $timeKeepingTable->timekeeping_table_name;
                $dataInsertEmail['month'] = $timeKeepingTable->month;
                $dataInsertEmail['year'] = $timeKeepingTable->year;
                $dataInsertEmail['link'] = route('manage_time::timekeeping.timekeeping-aggregate', ['timekeepingTableId' => $timeKeepingTable->id]);

                $dataInsertEmail['mail_title'] = Lang::get('manage_time::message.[Notification][Timekeeping][Update aggregate fail] :subject', ['subject' => $timeKeepingTable->timekeeping_table_name]);
                $dataInsertEmail['content'] = Lang::get('manage_time::message.Process update aggregatetimekeeping fail.');
                ManageTimeCommon::pushEmailToQueue($dataInsertEmail, $templateEmail);
            }
        }
    }
    
    /**
     * @param $timeKeepingTable
     * @param $dataRelate
     * @return bool|void
     */
    public function updateSalaryRateAgregate($timeKeepingTable, $dataRelate)
    {
        //Get holidays of time keeping table
        $team = Team::getTeamById($timeKeepingTable->team_id);
        $teamCodePre = Team::getTeamCodePrefix($team->code);
        $teamCodePre = Team::changeTeam($teamCodePre);
        $timekeepingTableId = $timeKeepingTable->id;
        $empIds = $dataRelate['emp_ids'];

        //reset col salary rate
        $timekeepingAggregate = Timekeeping::getTimekeepingAggregate($timeKeepingTable->id, $teamCodePre, $empIds);
        if (count($timekeepingAggregate)) {
            $now = Carbon::now();
            $employeeIds = [];
            $dataUpdate = [];
            if ($timeKeepingTable->start_date && $timeKeepingTable->end_date) {
                $timeKeepingTable->start_date = Carbon::parse($timeKeepingTable->start_date);
                $timeKeepingTable->end_date = Carbon::parse($timeKeepingTable->end_date);
            } else {
                $timeKeepingTable->start_date = null;
                $timeKeepingTable->end_date = null;
            }
            $compensation = CoreConfigData::getComAndLeaveDays($teamCodePre);
            $compInTime = ManageTimeCommon::getCompInTime(
                $timeKeepingTable,
                $compensation,
                'com'
            );
            $leavComInTime = ManageTimeCommon::getCompInTime(
                $timeKeepingTable,
                $compensation,
                'lea'
            );

            $empIds = [];
            foreach ($timekeepingAggregate as $item) {
                $arrayWork = [];
                $cpCompenstaion = $compensation;
                $cpCompInTime = $compInTime;
                $cpleavComInTime = $leavComInTime;
                $empIds[] = $item['employee_id'];

                $item["join_date"] = Carbon::parse($item["join_date"])->format("Y-m-d");
                if ($timeKeepingTable->type == TimekeepingTable::OFFICIAL) {
                    foreach ($cpCompenstaion['com'] as $key => $compen) {
                        if (strtotime($cpCompenstaion['lea'][$key]) < strtotime($item["join_date"])
                            || ((($item["trial_date"] && strtotime($compen) <= strtotime($item["trial_date"]))
                            || (!$item["trial_date"] && strtotime($compen) <= strtotime($item["offcial_date"]))
                            || (($item["trial_date"] && strtotime($compen) >= strtotime($item["trial_date"]) && strtotime($cpCompenstaion['lea'][$key]) < strtotime($item["trial_date"]))
                                || (!$item["trial_date"] && strtotime($compen) >= strtotime($item["offcial_date"]) && strtotime($cpCompenstaion['lea'][$key]) < strtotime($item["offcial_date"]))
                                && strtotime($cpCompenstaion['lea'][$key]) >= strtotime($item["join_date"])))
                            && strtotime($compen) >= strtotime($timeKeepingTable->start_date)
                            && strtotime($compen) <= strtotime($timeKeepingTable->end_date))) {
                            unset($cpCompenstaion['com'][$key]);
                            unset($cpCompenstaion['lea'][$key]);
                            $cpCompInTime = ManageTimeCommon::getCompInTime($timeKeepingTable, $cpCompenstaion, 'com');
                            $cpleavComInTime = ManageTimeCommon::getCompInTime($timeKeepingTable, $cpCompenstaion, 'lea');
                        }
                    }
                } elseif ($timeKeepingTable->type == TimekeepingTable::TRIAL) {
                    $cpCompInTime = ['check' => [], 'big' => []];
                    $cpleavComInTime = ['check' => [], 'big' => []];
                } else {
                    //do not some thing
                }

                $itemCom = ManageTimeCommon::calComDayEmpInTime($timeKeepingTable, $item['offcial_date'], $item['join_date'], $item['leave_date'], $cpCompInTime, true);
                $itemLea = ManageTimeCommon::calComDayEmpInTime($timeKeepingTable, $item['offcial_date'], $item['join_date'], $item['leave_date'], $cpleavComInTime);
                $itemCom['number_com_tri'] = $itemCom['number_com_tri'] - $itemLea['number_com_tri'];
                $itemCom['number_com_off'] = $itemCom['number_com_off'] - $itemLea['number_com_off'];
                $item['updated_at'] = $now;
                $item = array_merge($itemCom, $item);

                $item = (object) $item;
                $daysOffInTimeBusiness = ManageTimeView::daysOffInTimeBusiness($item, $timeKeepingTable, $teamCodePre);
                $totalWorkingToSalary = ManageTimeView::totalWorkingDayObject($item, $daysOffInTimeBusiness);
                $item = (array) $item;

                $arrayWork= [
                    'total_working_officail' => $totalWorkingToSalary['offcial'],
                    'total_working_trial' => $totalWorkingToSalary['trial'],
                ];
                $item = array_merge($arrayWork, $item);
                // nv không có công thì không được hưởng ngày lễ
                $total = (float)($item['total_working_officail'] + $item['total_working_trial']) - ($item['total_official_holiay'] + $item['total_trial_holiay']);
                if (!$total) {
                    $item['total_official_holiay'] = 0;
                    $item['total_trial_holiay'] = 0;
                    $item['total_working_officail'] = 0;
                    $item['total_working_trial'] = 0;
                }
                unset($item['offcial_date']);
                unset($item['join_date']);
                unset($item['leave_date']);
                unset($item['trial_date']);
                TimekeepingAggregate::where('timekeeping_table_id', $timeKeepingTable->id)
                    ->where('employee_id', $item['employee_id'])
                    ->update($item);
            }
            // fined to work late
            FinesMoney::insertFinesWorkLate($timeKeepingTable->start_date, $empIds);
        }
        return;
    }
    // =================== end update rate agregate ==============================
        
    /**
     * get employee id and category permission by permission
     *
     * @param  string $route
     * @param  int $empId
     * @return array
     */
    public function getEmployeeByPermission($route, $empId = null)
    {
        $scope = Permission::getInstance();
        if (!$scope->isAllow($route)) {
            CoreView::viewErrorPermission();
        }
        if (!$empId) {
            $empId = Permission::getInstance()->getEmployee()->id;
        }
        $now = Carbon::now();

        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            return [
                'cate' => 'company',
                'emp_ids' => []
            ];
        } elseif ($teamIds = $scope->isScopeTeam(null, $route)) {
            return [
                'cate' => 'team',
                'emp_ids' => Employee::getEmpByTeams($teamIds)->pluck('id')->toArray(),
                'team_ids' => $teamIds
            ];
        } else {
            $objProject = new Project();
            $empProj = $objProject->getEmployeeByPM($empId, $now->format('Y-m-d'));
            return [
                'cate' => 'x',
                'emp_ids' => $empProj->pluck('employee_id')->toArray()
            ];
        }
    }
        
    /**
     * get employee by project ids or team ids
     *
     * @param  array $proIds
     * @param  array $teamIds
     * @return collection
     */
    public function getEmpByProjectTeam($proIds = [], $teamIds = [])
    {
        $collection = Employee::select(
            'employees.id',
            'employees.name',
            'tm.team_id'
        )
        ->rightJoin('team_members as tm', 'tm.employee_id', '=', 'employees.id');
        if ($teamIds) {
            $collection->whereIn('tm.team_id', $teamIds);
        }
        if ($proIds) {
            $collection->rightJoin('project_members as proj_m', 'proj_m.employee_id', '=', 'employees.id')
                ->join('projs as projs', 'projs.id', '=', 'proj_m.project_id')
                ->whereIn('proj_m.project_id', $proIds)
                ->where('projs.status', Project::STATUS_APPROVED) // STATUS_APPROVED = 1
                ->where('proj_m.status', Project::STATUS_APPROVED) // STATUS_APPROVED = 1
                ->whereNull('projs.deleted_at')
                ->whereNull('proj_m.deleted_at');
        }
        return $collection->groupBy('employees.id')->get();
    }

    /**
     * tính toán thời gian nhân viên BSC
     * @param  date $start_at
     * @param  date $created_at 
     * @param  [array] $holidays 
     */

    public static function isDueDate($start_at, $created_at, $holidays) 
    {
        $weekends = ['Sat', 'Sun'];
        $start = new \DateTime($start_at);
        $end  = new \DateTime($created_at);
        $end->modify('+1 day');
        $total_days = $end->diff($start)->days;
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
            foreach($period as $dt) {
                if (in_array($dt->format('D'),  $weekends) || in_array($dt->format('Y-m-d'), $holidays)){
                    $total_days--;
                }
            }
        return $total_days > 3;
    }

}
