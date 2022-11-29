<?php

namespace Rikkei\ManageTime\View;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\ManageTime\Http\Controllers\TimekeepingController;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\Ot\Model\OtBreakTime;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\Project\View\TimesheetHelper;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\View\TeamList;

class ViewTimeKeeping
{
    const FOLDER_UPLOAD = 'timekeeping_upload';
    const FOLDER_UPLOAD_RELATED = 'timekeeping_upload_related';
    const FOLDER_APP = 'app';
    const TIME_IN_OUT_EMPLOYEE = 'time_in_out';
    const FOLDER_UPLOAD_RELATED_PERSON = 'timekeeping_upload_related_person';
    const FOLDER_WKT_UPLOAD_RELATED = 'workingtime_upload_related';
    private $logFile = '/logs/timekeeping.log';

    public function getLogFile() {
        return storage_path() . $this->logFile;
    }

    /**
     * tính toán thời gian ra vào làm việc của nhân viên
     * @param  [collection] $tblKeepings [table manage_time_timekeepings]
     * @param  [array] $teams            [key: id table manage_time_timekeeping_tables, value: team]
     * @param  [array] $typeTable        [loại bảng, key: value]
     * @param  [date] $dateTableKeeping [ngày chạy cron]
     * @param  [array] $dataNotLate [nhân viên không đi muộn]
     * @return [type]
     */
    public function calculateTimeWork($tblKeepings, $teams, $typeTable, $dateTableKeeping, $dataNotLate = [])
    {
        $empIds = $tblKeepings->lists('employee_id')->toArray();
        $tKTableIds = array_unique($tblKeepings->lists('timekeeping_table_id')->toArray());
        $emps = Employee::select('id', 'email')->whereIn('id', $empIds)->get();
        $emails = $emps->lists('email')->toArray();
        $empList = $this->getTimeWokringEmpByEmails($emails, $dateTableKeeping);

        $datasInsertTimeKeeping = [];
        //======= compensationDays =====
        foreach ($teams as $key => $temcode) {
            $arrSpecialHoliday[$key] = CoreConfigData::getSpecialHolidays(2, $temcode);
            $arrCompensation[$key] = CoreConfigData::getCompensatoryDays($temcode);
        }
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        foreach ($tblKeepings as $tblKeeping) {
            $dataInsert = [];
            $date = $tblKeeping->timekeeping_date;
            $type = $typeTable[$tblKeeping->timekeeping_table_id];
            $specialHolidays = $arrSpecialHoliday[$tblKeeping->timekeeping_table_id];
            $compensationDays = $arrCompensation[$tblKeeping->timekeeping_table_id];
            $teamCodePre = $teams[$tblKeeping->timekeeping_table_id];
            $carbonDate = Carbon::createFromFormat('Y-m-d', $date);
            $isWeekend = ManageTimeCommon::isWeekend($carbonDate, $compensationDays);
            $isHoliday = ManageTimeCommon::isHoliday($carbonDate, $annualHolidays, $specialHolidays, $teamCodePre);
            $isWeekendOrHoliday = $isWeekend || $isHoliday;
            if ($isWeekendOrHoliday) {
                continue;
            }
            if (!array_key_exists($tblKeeping->employee_id, $empList)) {
                continue;
            }
            $workingTime = ManageTimeView::findTimeSetting($empList[$tblKeeping->employee_id], $teamCodePre);
            $times = [
                [
                    'vao_luc' => empty($tblKeeping->start_time_morning_shift) ? '' : Carbon::parse($tblKeeping->start_time_morning_shift),
                    'ra_luc' => empty($tblKeeping->end_time_morning_shift) ? '' : Carbon::parse($tblKeeping->end_time_morning_shift),
                    'di_muon' => null,
                    've_som' => null,
                    'ca_lam_viec' => ManageTimeConst::MORNING_SHIFT,
                ], [
                    'vao_luc' => empty($tblKeeping->start_time_afternoon_shift) ? '' : Carbon::parse($tblKeeping->start_time_afternoon_shift),
                    'ra_luc' => empty($tblKeeping->end_time_afternoon_shift) ? '' : Carbon::parse($tblKeeping->end_time_afternoon_shift),
                    'di_muon' => null,
                    've_som' => null,
                    'ca_lam_viec' => ManageTimeConst::AFTERNOON_SHIFT,
                ]
            ];
            $times = $this->calculationLateEary($times, $workingTime, $teamCodePre);
            // check di muon cua bom, bod
            if (isset($tblKeeping->email) && !empty($times[0]["di_muon"]) &&
                    isset($dataNotLate[$tblKeeping->email]) && isset($dataNotLate[$tblKeeping->email][$date])) {
                $time = $times[0]["di_muon"]->hour * 60 + $times[0]["di_muon"]->minute;
                if ($time <= ManageTimeConst::MAX_TIME_LATE_IN_EARLY_OUT) {
                    $times[0]['di_muon'] = 0;
                }
            }

            $value = $empList[$tblKeeping->employee_id];
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
            $working = $this->calculateTime($times, $tblKeeping->employee_id, $date, $isWeekendOrHoliday, $value, $type);
            $dataInsert = array_merge($working, $dataInsert);

            $timekeepingOfEmployee = $tblKeeping;
            $timekeepingOfEmployee->setData($dataInsert);

            $manageTimeView = new ManageTimeView();
            $timekeepingResult = $manageTimeView->timekeepingResult($timekeepingOfEmployee, $isWeekend, $isHoliday, $value['offcial_date'], $value['trial_date'], $value['contract_type'], $workingTime, $teamCodePre, $type);
            $dataInsert['timekeeping'] = $timekeepingResult[0];
            $dataInsert['timekeeping_number'] = $timekeepingResult[1];
            
            $key = $tblKeeping->employee_id . '-' . $date;
            $datasInsertTimeKeeping[$tblKeeping->timekeeping_table_id][$key] = $dataInsert;
        }
        if (count($datasInsertTimeKeeping)) {
            foreach ($datasInsertTimeKeeping as $key => $datas) {
                $this->updateDataCron($datasInsertTimeKeeping[$key], $key);
            }
        }
        // tinh lại time đi muộn về xớm, time làm việc khi có đơn phép 1/4
        $this->handLingLeaveday($tKTableIds, $empIds, $dateTableKeeping, $teams, $typeTable);
    }

    /**
     * lấy thông tin nhiều nhân viên theo emails
     * @param  [array] $emails
     * @return [type]
     */
    public function getTimeWokringEmpByEmails($emails, $dateTableKeeping)
    {
        $arrTeamList = [];
        $date = Carbon::parse($dateTableKeeping);
        $dateStart = $date->format('Y-m-d');

        $teamLists =  Employee::getEmpByEmailsWithContracts($emails, $dateStart, $dateStart, [
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
        if ($teamLists) {
            foreach($teamLists as $empIds => $item) {
                $arrTeamList[$empIds] = $item[0];
            }
        }
        return $arrTeamList;
    }

    /**
     * lấy số đi muộn về xớm dựa vào calEarlyOutLateIn - Rikkei\ManageTime\View\View
     * @return [type] [description]
     */
    public function calculationLateEary($times, $timeSettingReclone, $teamCodePre)
    {
        if (!empty($times[0]['vao_luc'])) {
            $morningIn = $times[0]['vao_luc'];
            if ($morningIn->gt($timeSettingReclone['morningInSetting'])) {
                $times[0]['di_muon'] = ManageTimeView::timeDiff($morningIn, $timeSettingReclone['morningInSetting'], $teamCodePre);
            }
        } else {
            if (!empty($times[1]['vao_luc'])) {
                $afternoonIn = $times[1]['vao_luc'];
                if ($afternoonIn->gt($timeSettingReclone['afternoonInSetting']) && $afternoonIn->lt($timeSettingReclone['afternoonOutSetting'])) {
                    $times[1]['di_muon'] = ManageTimeView::timeDiff($afternoonIn, $timeSettingReclone['afternoonInSetting'], $teamCodePre);
                }
            }
        }

        // Early out
        if (!empty($times[1]['ra_luc'])) {
            $afternoonOut = $times[1]['ra_luc'];
            if ($afternoonOut->lt($timeSettingReclone['afternoonOutSetting'])) {
                if ($afternoonOut->hour > ManageTimeConst::TIME_END_OT) {
                    $times[1]['ve_som'] = ManageTimeView::timeDiff($afternoonOut, $timeSettingReclone['afternoonOutSetting'], $teamCodePre);
                }
            }
        } else {
            if (!empty($times[0]['ra_luc'])) {
                $morningOut = $times[0]['ra_luc'];
                if ($morningOut->lt($timeSettingReclone['morningOutSetting']) && $morningOut->gt($timeSettingReclone['morningInSetting'])) {
                    $times[0]['ve_som'] = ManageTimeView::timeDiff($morningOut, $timeSettingReclone['morningOutSetting'], $teamCodePre);
                }
            }
        }
        return $times;
    }

    /**
     * tinh toan thoi gian đi xớm về muộn - Rikkei\ManageTime\View\View
     * @param  [array] $times [time ra vao]
     * @param  [int] $employeeId
     * @param  [date] $date               [Y-m-d]
     * @param  [boolean] $isWeekendOrHoliday
     * @param  [type] $value
     * @param  [int] $type [loại bảng công]
     * @return [type]
     */
    public function calculateTime($times, $employeeId, $date, $isWeekendOrHoliday, $value, $type)
    {
        $typesOffcial = getOptions::typeEmployeeOfficial();
        $dataInsert = [];
        foreach ($times as $infoItem) {
            $dataInsert['employee_id'] = $employeeId;
            $dataInsert['timekeeping_date'] = $date;
            $dataInsert['updated_at'] = date('Y-m-d H:i:s');
            $timeStartAt = $infoItem['vao_luc'];
            $timeEndAt = $infoItem['ra_luc'];
            $timeEarlyOut = $infoItem['ve_som'];
            $timeLateIn = $infoItem['di_muon'];
            // Check late in early out
            switch ($infoItem['ca_lam_viec']) {
                case ManageTimeConst::MORNING_SHIFT:
                    if ($timeStartAt) {
                        if ($timeLateIn && !$isWeekendOrHoliday
                                && Carbon::parse($date)->format('Y-m-d') > Carbon::parse($value['join_date'])->format('Y-m-d')) {
                            if (in_array($type, $typesOffcial)) {
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
                        $dataInsert['late_start_shift'] = 0;
                    }
                    if ($timeEndAt) {
                        if ($timeEarlyOut && !$isWeekendOrHoliday
                                 && Carbon::parse($date)->format('Y-m-d') > Carbon::parse($value['join_date'])->format('Y-m-d')) {
                            if (in_array($type, $typesOffcial)) {
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
                        $dataInsert['early_mid_shift'] = 0;
                    }
                    break;
                case ManageTimeConst::AFTERNOON_SHIFT:
                    if ($timeStartAt) {
                        if ($timeLateIn && !$isWeekendOrHoliday
                                 && Carbon::parse($date)->format('Y-m-d') > Carbon::parse($value['join_date'])->format('Y-m-d')) {
                            if (in_array($type, $typesOffcial)) {
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
                        $dataInsert['late_mid_shift'] = 0;
                    }
                    if ($timeEndAt) {
                        if ($timeEarlyOut && !$isWeekendOrHoliday
                                 && Carbon::parse($date)->format('Y-m-d') > Carbon::parse($value['join_date'])->format('Y-m-d')) {
                            if (in_array($type, $typesOffcial)) {
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
                        $dataInsert['early_end_shift'] = 0;
                    }
                    break;
                default:
            }
        }
        return $dataInsert;
    }

    /**
     * tinh lai thoi gian di muon ve xom, time làm việc với tường hợp có đơn nghỉ 1/4
     * truong hop nghỉ 1 hoặc 0.5 dựa vào ký hiệu
     * @param  [type] $arrLateEary
     * @param  [type] $arrEmpIds
     * @param  [type] $dateTableKeeping
     * @param  [type] $teams
     * @param  [type] $typeTable
     * @return [type]
     */
    public function handLingLeaveday($arrLateEary, $arrEmpIds, $dateTableKeeping, $teams, $typeTable)
    {
        $now = Carbon::parse($dateTableKeeping);
        $dateTimekeeping = clone $now;
        $startDate = $now->firstOfMonth()->format("Y-m-d");
        $endDate = $now->endOfMonth()->format("Y-m-d");
        $monthOfTimeKeeping = $now->year . '-' . $now->month . '-01';

        $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);
        $leaveDayRegister = LeaveDayRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, $arrEmpIds, $startDate, $endDate);
        $businessTripRegister = BusinessTripRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, $arrEmpIds, $startDate, $endDate);

        $arrLeaveDay = [];
        if (count($leaveDayRegister)) {
            foreach ($arrLateEary as $keytable) {
                $dataLeaveDay = [];
                $teamCodePrefix = $teams[$keytable];
                $type = $typeTable[$keytable];
                $timekeepingTableId = $keytable;
                //separate branch when have busineess trip register
                if (count($leaveDayRegister) && count($businessTripRegister)) {
                    $leaveDayRegister = ManageTimeView::separateBranchBusiness($leaveDayRegister, $businessTripRegister, $teamCodePrefix);
                }
                foreach ($leaveDayRegister as $item) {
                    if ($item->leave_date && Carbon::parse($item->leave_date)->lt($dateTimekeeping)) {
                        continue;
                    }
                    $workingTime = ManageTimeView::findTimeSetting($item, $teamCodePrefix, $rangeTimes);
                    $leaveDayDateStart = Carbon::parse($item->date_start);
                    $leaveDayDateEnd = Carbon::parse($item->date_end);
                    $dateTimeStartWorking = clone $dateTimekeeping;
                    $dateTimeStartWorking->setTime($workingTime['morningInSetting']->hour, $workingTime['morningInSetting']->minute);
                    $dateTimeEndWorking = clone $dateTimekeeping;
                    $dateTimeEndWorking->setTime($workingTime['afternoonOutSetting']->hour, $workingTime['afternoonOutSetting']->minute);
                    $getDate = ManageTimeView::setDateApplicationByTableType(
                        $type,
                        $leaveDayDateStart,
                        $leaveDayDateEnd,
                        $item->trial_date,
                        $item->offcial_date
                    );
                    if ($getDate['continue']) {
                        continue;
                    }
                    $leaveDayDateStart = $getDate['start'];
                    $leaveDayDateEnd = $getDate['end'];

                    if ($leaveDayDateStart < $dateTimeEndWorking && $leaveDayDateEnd > $dateTimeStartWorking) {
                        $dataInsertLeaveDay = [];
                        $leaveDayCreator = $item->creator_id;
                        $keyLeaveDay = $leaveDayCreator . '-' . $dateTimekeeping->format('Y-m-d');

                        if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                            $diffAndSession = TimekeepingController::getDiffTimesOfTimeKeepingResiger($leaveDayDateStart, $leaveDayDateEnd, $dateTimekeeping, $workingTime, $timekeepingTableId, $item->creator_id);
                        } else {
                            $diffAndSession = TimekeepingController::getDiffTimesOfTimeKeeping($leaveDayDateStart, $leaveDayDateEnd, $dateTimekeeping, $teamCodePrefix, $workingTime);
                        }
                        if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                            if (isset($diffAndSession['timeLateStart'])) {
                                if ($diffAndSession['timeLateStart'] === ManageTimeConst::RESET) {
                                    $dataInsertLeaveDay['late_start_shift'] = 0;
                                } elseif ($diffAndSession['timeLateStart'] != 0) {
                                    $dataInsertLeaveDay['late_start_shift'] = $diffAndSession['timeLateStart'];
                                }
                            }
                            if (isset($diffAndSession['timeLateMid'])) {
                                if ($diffAndSession['timeLateMid'] === ManageTimeConst::RESET) {
                                    $dataInsertLeaveDay['late_mid_shift'] = 0;
                                } elseif ($diffAndSession['timeLateMid'] !== 0) {
                                    $dataInsertLeaveDay['late_mid_shift'] = $diffAndSession['timeLateMid'];
                                }
                            }
                            if (isset($diffAndSession['timeEarlyMid'])) {
                                if ($diffAndSession['timeEarlyMid'] === ManageTimeConst::RESET) {
                                    $dataInsertLeaveDay['early_mid_shift'] = 0;
                                } elseif ($diffAndSession['timeEarlyMid'] != 0) {
                                    $dataInsertLeaveDay['early_mid_shift'] = $diffAndSession['timeEarlyMid'];
                                }
                            }
                            if (isset($diffAndSession['timeEarlyEnd'])) {
                                if ($diffAndSession['timeEarlyEnd'] === ManageTimeConst::RESET) {
                                    $dataInsertLeaveDay['early_end_shift'] = 0;
                                } elseif ($diffAndSession['timeEarlyEnd'] != 0) {
                                    $dataInsertLeaveDay['early_end_shift'] = $diffAndSession['timeEarlyEnd'];
                                }
                            }

                            if (isset($dataLeaveDay[$keyLeaveDay]['timekeeping_number_register'])) {
                                $dataLeaveDay[$keyLeaveDay]['timekeeping_number_register'] = $dataLeaveDay[$keyLeaveDay]['timekeeping_number_register'] + $diffAndSession['timekeeping_number_register'];
                            } else {
                                $dataInsertLeaveDay['timekeeping_number_register'] = $diffAndSession['timekeeping_number_register'];
                            }
                        }
                        if (count($dataInsertLeaveDay)) {
                            $dataLeaveDay[$keyLeaveDay]['employee_id'] = $leaveDayCreator;
                            $dataLeaveDay[$keyLeaveDay]['timekeeping_date'] = $dateTimekeeping->format('Y-m-d');
                            $dataLeaveDay[$keyLeaveDay]['timekeeping_table_id'] = $timekeepingTableId;

                            if (isset($dataInsertLeaveDay['late_start_shift'])) {
                                $dataLeaveDay[$keyLeaveDay]['late_start_shift'] = $dataInsertLeaveDay['late_start_shift'];
                            }
                            if (isset($dataInsertLeaveDay['late_mid_shift'])) {
                                $dataLeaveDay[$keyLeaveDay]['late_mid_shift'] = $dataInsertLeaveDay['late_mid_shift'];
                            }
                            if (isset($dataInsertLeaveDay['early_mid_shift'])) {
                                $dataLeaveDay[$keyLeaveDay]['early_mid_shift'] = $dataInsertLeaveDay['early_mid_shift'];
                            }
                            if (isset($dataInsertLeaveDay['early_end_shift'])) {
                                $dataLeaveDay[$keyLeaveDay]['early_end_shift'] = $dataInsertLeaveDay['early_end_shift'];
                            }
                            if (isset($dataLeaveDay[$keyLeaveDay]['timekeeping_number_register'])) {
                            } else {
                                $dataLeaveDay[$keyLeaveDay]['timekeeping_number_register'] = $dataInsertLeaveDay['timekeeping_number_register'];
                            }
                        }
                    }
                }
                if (count($dataLeaveDay)) {
                    $arrLeaveDay[$keytable] = $dataLeaveDay;
                }
            }
            if (count($arrLeaveDay)) {
                foreach ($arrLeaveDay as $key => $value) {
                    $this->updateDataCron($arrLeaveDay[$key], $key);
                }
            }
        }
    }

    /**
     * Tính time ot
     * @param  [type] $tblKeepings        [table manage_time_timekeepings]
     * @param  [type] $arrEmpIds          [description]
     * @param  [type] $teams              [description]
     * @param  [type] $typeTable          [description]
     * @return [type]                     [description]
     */
    public function calculationOT($dateTableKeeping, $tblKeepings, $arrEmpIds, $teams, $typeTable, $overtimeRegister = [])
    {
        $now = Carbon::parse($dateTableKeeping);
        $cpNow = clone $now;
        $startDate = $cpNow->firstOfMonth()->format("Y-m-d");
        $endDate = $cpNow->endOfMonth()->format("Y-m-d");
        $monthOfTimeKeeping = $now->year . '-' . $now->month . '-01';
        $idKeepingTable = array_unique($tblKeepings->lists('timekeeping_table_id')->toArray());
        $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);

        // Get OT
        if (!count($overtimeRegister)) {
            $overtimeRegister = OtRegister::getRegisterOfTimeKeepingCron($arrEmpIds, $now->format("Y-m-d"));
        }
        //Find Supplement for OT
        $supplementRegisterOT = SupplementRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, $arrEmpIds, $startDate, $endDate, true);
        $businessTripRegister = BusinessTripRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, $arrEmpIds, $startDate, $endDate);

        $supplementOTGroup = [];
        if (count($supplementRegisterOT)) {
            $supplementOTGroup = $supplementRegisterOT->groupBy('employee_id');
        }

        //======= compensationDays =====
        foreach ($teams as $key => $temcode) {
            $arrSpecialHoliday[$key] = CoreConfigData::getSpecialHolidays(2, $temcode);
            $arrCompensation[$key] = CoreConfigData::getCompensatoryDays($temcode);
        }
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);

        if (count($overtimeRegister)) {
            foreach ($idKeepingTable as $idTable) {
                $teamCodePrefix = $teams[$idTable];
                $type = $typeTable[$idTable];
                $timekeepingTableId = $idTable;
                $teamCodePre = $teams[$idTable];
                $specialHolidays = $arrSpecialHoliday[$idTable];
                $compensationDays = $arrCompensation[$idTable];

                $dataOT = [];
                if (count($overtimeRegister) && count($businessTripRegister)) {
                    $overtimeRegister = ManageTimeView::separateBranchBusiness($overtimeRegister, $businessTripRegister, $teamCodePrefix);
                }
                if (count($supplementRegisterOT) && count($businessTripRegister)) {
                    $supplementRegisterOT = ManageTimeView::separateBranchBusiness($supplementRegisterOT, $businessTripRegister, $teamCodePrefix);
                }
                // start_at = end_at, ot chỉ đăng ký trong 1 ngày
                foreach ($overtimeRegister as $item) {
                    $overtimeDateStart = Carbon::parse($item->start_at);
                    $overtimeDateEnd = Carbon::parse($item->end_at);
                    if ($item->leave_date && Carbon::parse($item->leave_date)->lt($overtimeDateStart)) {
                        continue;
                    }
                    $isWeekend = ManageTimeCommon::isWeekend($overtimeDateStart, $compensationDays);
                    $isHoliday = ManageTimeCommon::isHoliday($overtimeDateStart, $annualHolidays, $specialHolidays, $teamCodePre);
                    $isWeekendOrHoliday = $isWeekend || $isHoliday;

                    $getDate = ManageTimeView::setDateApplicationByTableType(
                        $type,
                        $overtimeDateStart,
                        $overtimeDateEnd,
                        $item->trial_date,
                        $item->offcial_date
                    );
                    if ($getDate['continue']) {
                        continue;
                    }
                    $overtimeDateStart = $getDate['start'];
                    $overtimeDateEnd = $getDate['end'];

                    // 1 đơn OT chỉ đăng ký được 1 ngày nên
                    $dateTimekeeping = clone $overtimeDateStart;
                        $overtimeEmployee = $item->employee_id;
                        $keyOvertime = $overtimeEmployee . '-' . $dateTimekeeping->format('Y-m-d');

                        // Get timekeeping of employee in table timekeeping
                        $month = $dateTimekeeping->format('Y-m') . '-01';
                        $timekeepingOfEmployee = Timekeeping::select(
                            'manage_time_timekeepings.id as timekeeping_id',
                            'start_time_morning_shift',
                            'end_time_morning_shift',
                            'start_time_afternoon_shift',
                            'end_time_afternoon_shift',
                            'start_time1',
                            'end_time1',
                            'start_time2',
                            'end_time2'
                        )
                        ->leftJoin('working_times', function ($join) use ($month) {
                            $join->on('working_times.employee_id', '=', 'manage_time_timekeepings.employee_id');
                            $join->where('working_times.from_month', '<=', $month);
                            $join->where('working_times.to_month', '>=', $month);
                            $join->where('working_times.status', '=', ManageTimeConst::STT_WK_TIME_APPROVED);
                        })
                        ->where('timekeeping_table_id', $timekeepingTableId)
                        ->where('manage_time_timekeepings.employee_id', $overtimeEmployee)
                        ->where('timekeeping_date', $dateTimekeeping->format('Y-m-d'))
                        ->first();
                        if (!$timekeepingOfEmployee) {
                            continue;
                        }

                        $timeSettingOfEmp = ManageTimeView::findTimeSetting($timekeepingOfEmployee, $teamCodePrefix, $rangeTimes);

                        $dataInsertOT = [];
                        $dataInsertOT['timekeeping_table_id'] = $timekeepingTableId;
                        $dataInsertOT['employee_id'] = $overtimeEmployee;
                        $dataInsertOT['timekeeping_date'] = $dateTimekeeping->format('Y-m-d');
                        $timeOvertime = 0;
                        $registerOvertime = 0;
                        $timeAddLeaveDay = 0;
                        // Check Supplement for OT
                        if (!empty($supplementOTGroup[$overtimeEmployee])) {
                            $dateCompare = Carbon::parse($dateTimekeeping->format('Y-m-d') . ' ' . $timeSettingOfEmp['morningOutSetting']->format('H:i:s'));
                            foreach ($supplementOTGroup[$overtimeEmployee] as $itemSupOt) {
                                $dateSupOtStart = Carbon::parse($itemSupOt->start_at);
                                $dateSupOtEnd = Carbon::parse($itemSupOt->end_at);

                                $getDate = ManageTimeView::setDateApplicationByTableType(
                                    $type,
                                    $dateSupOtStart,
                                    $dateSupOtEnd,
                                    $item->trial_date,
                                    $item->offcial_date
                                );
                                if ($getDate['continue']) {
                                    continue;
                                }
                                $dateSupOtStart = $getDate['start'];
                                $dateSupOtEnd = $getDate['end'];

                                if ($dateSupOtStart->format('Y-m-d') <= $dateTimekeeping->format('Y-m-d') && $dateSupOtEnd->format('Y-m-d') >= $dateTimekeeping->format('Y-m-d')) {
                                    if ($dateSupOtStart->format('Y-m-d') < $dateTimekeeping->format('Y-m-d')) {
                                        $timekeepingOfEmployee->start_time_morning_shift = $timeSettingOfEmp['morningInSetting']->format('H:i');
                                    } else {
                                        if ($dateSupOtStart->format('Y-m-d H:i:s') < $dateCompare->format('Y-m-d H:i:s')) {
                                            if (empty($timekeepingOfEmployee->start_time_morning_shift) || $timekeepingOfEmployee->start_time_morning_shift > $dateSupOtStart->format('H:i')) {
                                                $timekeepingOfEmployee->start_time_morning_shift = $dateSupOtStart->format('H:i');
                                            }
                                        } else {
                                            if (empty($timekeepingOfEmployee->start_time_afternoon_shift) || $timekeepingOfEmployee->start_time_afternoon_shift > $dateSupOtStart->format('H:i')) {
                                                $timekeepingOfEmployee->start_time_afternoon_shift = $dateSupOtStart->format('H:i');
                                            }
                                        }
                                    }
                                    if ($dateSupOtEnd->format('Y-m-d') > $dateTimekeeping->format('Y-m-d')) {
                                        if (empty($timekeepingOfEmployee->end_time_afternoon_shift) || $timekeepingOfEmployee->end_time_afternoon_shift < $timeSettingOfEmp['afternoonOutSetting']->format('H:i')) {
                                            $timekeepingOfEmployee->end_time_afternoon_shift = $timeSettingOfEmp['afternoonOutSetting']->format('H:i');
                                        }
                                    } else {
                                        if ($dateSupOtEnd->format('Y-m-d H:i:s') <= $dateCompare->format('Y-m-d H:i:s')) {
                                            if (empty($timekeepingOfEmployee->end_time_morning_shift) || $timekeepingOfEmployee->end_time_morning_shift < $dateSupOtEnd->format('H:i')) {
                                                $timekeepingOfEmployee->end_time_morning_shift = $dateSupOtEnd->format('H:i');
                                            }
                                        } else {
                                            if (empty($timekeepingOfEmployee->end_time_afternoon_shift) || $timekeepingOfEmployee->end_time_afternoon_shift < $dateSupOtEnd->format('H:i')) {
                                                $timekeepingOfEmployee->end_time_afternoon_shift = $dateSupOtEnd->format('H:i');
                                            }
                                        }
                                    }
                                    Timekeeping::where('id', $timekeepingOfEmployee->timekeeping_id)
                                        ->update([
                                            'start_time_morning_shift' => $timekeepingOfEmployee->start_time_morning_shift,
                                            'end_time_morning_shift' => $timekeepingOfEmployee->end_time_morning_shift,
                                            'start_time_afternoon_shift' => $timekeepingOfEmployee->start_time_afternoon_shift,
                                            'end_time_afternoon_shift' => $timekeepingOfEmployee->end_time_afternoon_shift,
                                        ]);
                                }
                            }
                        }

                        if ($item->is_onsite) {
                            $startTimeMorningShift = $overtimeDateStart->format('H:i');
                            $endTimeMorningShift = null;
                            $startTimeAfternoonShift = null;
                            $endTimeAfternoonShift = $overtimeDateEnd->format('H:i');
                        } else {
                            $startTimeMorningShift = $timekeepingOfEmployee->start_time_morning_shift;
                            $endTimeMorningShift = $timekeepingOfEmployee->end_time_morning_shift;
                            $startTimeAfternoonShift = $timekeepingOfEmployee->start_time_afternoon_shift;
                            $endTimeAfternoonShift = $timekeepingOfEmployee->end_time_afternoon_shift;
                        }

                        $overtimeStartAt = $overtimeDateStart->toTimeString();
                        if ($dateTimekeeping->format('Y-m-d') !== $overtimeDateStart->format('Y-m-d')) {
                            if ($isWeekendOrHoliday) {
                                $overtimeStartAt = "08:00:00";
                            } else {
                                $overtimeStartAt = "18:30:00";
                            }
                        }
                        $overtimeEndAt = $overtimeDateEnd->toTimeString();
                        if ($dateTimekeeping->format('Y-m-d') !== $overtimeDateEnd->format('Y-m-d')) {
                            $overtimeEndAt = "22:00:00";
                        }

                        $overtimeTimeBreak = $item->time_break;
                        $overtimeIsPaid = $item->is_paid;
                        $overtimeStartAtStrtotime = strtotime($overtimeStartAt);
                        $overtimeEndAtStrtotime = strtotime($overtimeEndAt);
                        $startTimeMorningShiftStrtotime = 0;
                        $endTimeMorningShiftStrtotime = 0;
                        $startTimeAfternoonShiftStrtotime = 0;
                        $endTimeAfternoonShiftStrtotime = 0;

                        if ($startTimeMorningShift) {
                            $startTimeMorningShiftStrtotime = strtotime($startTimeMorningShift);
                        }
                        if ($endTimeMorningShift) {
                            $endTimeMorningShiftStrtotime = strtotime($endTimeMorningShift);
                        }
                        if ($startTimeAfternoonShift) {
                            $startTimeAfternoonShiftStrtotime = strtotime($startTimeAfternoonShift);
                        }
                        if ($endTimeAfternoonShift) {
                            $endTimeAfternoonShiftStrtotime = strtotime($endTimeAfternoonShift);
                        }

                        if ($isWeekendOrHoliday) {
                            if ((!$startTimeMorningShift && !$startTimeAfternoonShift) || (!$endTimeMorningShift && !$endTimeAfternoonShift && !$startTimeAfternoonShift)) {
                                continue;
                            } else {
                                if (($endTimeAfternoonShiftStrtotime > 0) && ($endTimeAfternoonShiftStrtotime < $overtimeStartAtStrtotime)) {
                                    continue;
                                } else {
                                    if ((!$startTimeMorningShift) && (!$startTimeAfternoonShift)) {
                                        continue;
                                    } elseif ($startTimeMorningShift) {
                                        if ($overtimeStartAtStrtotime < $startTimeMorningShiftStrtotime) {
                                            $overtimeStartAtStrtotime = $startTimeMorningShiftStrtotime;
                                        }
                                        if ($endTimeAfternoonShift) {
                                            if ($overtimeEndAtStrtotime > $endTimeAfternoonShiftStrtotime) {
                                                $overtimeEndAtStrtotime = $endTimeAfternoonShiftStrtotime;
                                            }
                                        } elseif ($startTimeAfternoonShift) {
                                            if ($overtimeEndAtStrtotime > $startTimeAfternoonShiftStrtotime) {
                                                $overtimeEndAtStrtotime = $startTimeAfternoonShiftStrtotime;
                                            }
                                        } elseif ($endTimeMorningShift) {
                                            if ($overtimeEndAtStrtotime > $endTimeMorningShiftStrtotime) {
                                                $overtimeEndAtStrtotime = $endTimeMorningShiftStrtotime;
                                            }
                                        } else {
                                            //do not something
                                        }
                                        $timeOvertime = ($overtimeEndAtStrtotime - $overtimeStartAtStrtotime) / 3600;
                                    } elseif ((!$startTimeMorningShift) && $startTimeAfternoonShift) {
                                        if ($startTimeAfternoonShiftStrtotime > $overtimeStartAtStrtotime) {
                                            $overtimeStartAtStrtotime = $startTimeAfternoonShiftStrtotime;
                                        }
                                        if ($overtimeEndAtStrtotime > $endTimeAfternoonShiftStrtotime) {
                                            $overtimeEndAtStrtotime = $endTimeAfternoonShiftStrtotime;
                                        }
                                        $timeOvertime = ($overtimeEndAtStrtotime - $overtimeStartAtStrtotime) / 3600;
                                    }
                                    if ($timeOvertime) {
                                        if ($isHoliday) {
                                            $registerOvertime = ManageTimeConst::IS_OT_ANNUAL_SPECIAL_HOLIDAY;
                                        } else {
                                            $registerOvertime = ManageTimeConst::IS_OT_WEEKEND;
                                        }
                                    }
                                }
                            }
                        } else {
                            if (!$endTimeAfternoonShift) {
                                continue;
                            } else {
                                if ($endTimeAfternoonShiftStrtotime < $overtimeStartAtStrtotime) {
                                    continue;
                                } else {
                                    if ((!$startTimeMorningShift) && (!$startTimeAfternoonShift)) {
                                        continue;
                                    } elseif ($startTimeMorningShift) {
                                        if ($overtimeEndAtStrtotime > $endTimeAfternoonShiftStrtotime) {
                                            $overtimeEndAtStrtotime = $endTimeAfternoonShiftStrtotime;
                                        }
                                        $timeOvertime = ($overtimeEndAtStrtotime - $overtimeStartAtStrtotime) / 3600;
                                        if ($timeOvertime) {
                                            $registerOvertime = ManageTimeConst::IS_OT;
                                        }
                                    } elseif ((!$startTimeMorningShift) && $startTimeAfternoonShift) {
                                        if ($startTimeAfternoonShiftStrtotime > $overtimeStartAtStrtotime) {
                                            $overtimeStartAtStrtotime = $startTimeAfternoonShiftStrtotime;
                                        }
                                        if ($overtimeEndAtStrtotime > $endTimeAfternoonShiftStrtotime) {
                                            $overtimeEndAtStrtotime = $endTimeAfternoonShiftStrtotime;
                                        }
                                        $timeOvertime = ($overtimeEndAtStrtotime - $overtimeStartAtStrtotime) / 3600;
                                        if ($timeOvertime) {
                                            $registerOvertime = ManageTimeConst::IS_OT;
                                        }
                                    }
                                }
                            }
                        }

                        if ($isWeekendOrHoliday && $overtimeTimeBreak) {
                            $breakTime = OtBreakTime::select('break_time')
                                ->where('ot_register_id', $item->id)
                                ->where('employee_id', $item->employee_id)
                                ->whereDate('ot_date', "=", $dateTimekeeping->format('Y-m-d'))
                                ->first();

                            if ($breakTime) {
                                $timeOvertime = $timeOvertime - $breakTime->break_time;
                            }
                        }
                        $timeOvertime = round($timeOvertime, 1);
                        if ($timeOvertime <= 0) {
                            $timeOvertime = 0;
                            $registerOvertime = ManageTimeConst::IS_NOT_OT;
                        } else {
                            // Check if is weekend then add 50% or holiday then add 150% time OT to leave day ot
                            if ($isWeekendOrHoliday && $overtimeIsPaid) {
                                if ($isHoliday) {
                                    $timeAddLeaveDay = ($timeOvertime * 150) / (100 * 8);
                                } else {
                                    $timeAddLeaveDay = ($timeOvertime * 50) / (100 * 8);
                                }
                                $timeAddLeaveDay = round($timeAddLeaveDay, 1);
                                if ($timeAddLeaveDay < 0) {
                                    $timeAddLeaveDay = 0;
                                }
                            }
                        }

                        if (isset($dataOT[$keyOvertime])) {
                            if ($overtimeIsPaid) {
                                if (isset($dataOT[$keyOvertime]['register_ot_has_salary'])) {
                                    $dataOT[$keyOvertime]['register_ot_has_salary'] += $timeOvertime;
                                } else {
                                    $dataOT[$keyOvertime]['register_ot_has_salary'] = $timeOvertime;
                                }
                                if (isset($dataOT[$keyOvertime]['leave_day_added'])) {
                                    $dataOT[$keyOvertime]['leave_day_added'] += $timeAddLeaveDay;
                                } else {
                                    $dataOT[$keyOvertime]['leave_day_added'] = $timeAddLeaveDay;
                                }
                            } else {
                                if (isset($dataOT[$keyOvertime]['register_ot_no_salary'])) {
                                    $dataOT[$keyOvertime]['register_ot_no_salary'] += $timeOvertime;
                                } else {
                                    $dataOT[$keyOvertime]['register_ot_no_salary'] = $timeOvertime;
                                }
                            }
                        } else {
                            $dataInsertOT['register_ot'] = $registerOvertime;
                            if ($overtimeIsPaid) {
                                $dataInsertOT['register_ot_has_salary'] = $timeOvertime;
                                $dataInsertOT['leave_day_added'] = $timeAddLeaveDay;
                            } else {
                                $dataInsertOT['register_ot_no_salary'] = $timeOvertime;
                            }
                            $dataOT[$keyOvertime] = $dataInsertOT;
                        }
                }
                if (count($dataOT)) {
                    $this->updateDataCron($dataOT, $idTable);
                }
            }
        }
    }

    //================ duyệt các đơn ================

    /**
     * approved leave day
     * @param  [object] $leaveDay
     */
    public static function insertLeaveDayTimekeeping($leaveDay)
    {
        $carbonStart = Carbon::parse($leaveDay->date_start);
        $carbonEnd = Carbon::parse($leaveDay->date_end);
        $monthOfTimeKeeping = $carbonStart->year . '-' . $carbonStart->month . '-01';

        $timeKeepingTables = self::getArrTKTableManageTK($carbonStart->toDateString(), $carbonEnd->toDateString(), [$leaveDay->creator_id]);

        if (!$timeKeepingTables) {
            return;
        }

        // Get business trip
        $businessTripRegister = BusinessTripRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, [$leaveDay->creator_id], $carbonStart->toDateString(), $carbonEnd->toDateString());
        // Get leave day
        $leaveDayRegister = LeaveDayRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, [$leaveDay->creator_id], $carbonStart->toDateString(), $carbonEnd->toDateString());
        foreach ($timeKeepingTables as $timeKeepingTable) {
            $dataLeaveDay = [];
            //Get holidays of time keeping table
            $team = Team::getTeamById($timeKeepingTable->team_id);
            $teamCodePrefix = Team::getTeamCodePrefix($team->code);
            $teamCodePrefix = Team::changeTeam($teamCodePrefix);
            $annualHolidays = CoreConfigData::getAnnualHolidays(2);
            $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);
            $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
            $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);
            $timekeepingTableId = $timeKeepingTable->id;

            //separate branch when have busineess trip register
            if (count($leaveDayRegister) && count($businessTripRegister)) {
                $leaveDayRegister = ManageTimeView::separateBranchBusiness($leaveDayRegister, $businessTripRegister, $teamCodePrefix);
            }

            if (count($leaveDayRegister)) {
                $timekeepingTableStartDate = $leaveDay->date_start;
                $timekeepingTableEndDate = $leaveDay->date_end;
                while (strtotime($timekeepingTableStartDate) <= strtotime($timekeepingTableEndDate)) {
                    $dateTimekeeping = Carbon::parse($timekeepingTableStartDate);
                    $isHoliday = ManageTimeCommon::isHoliday($dateTimekeeping, $annualHolidays, $specialHolidays, $teamCodePrefix);
                    $isWeekend = ManageTimeCommon::isWeekend($dateTimekeeping, $compensationDays);
                    $isWeekendOrHoliday = $isWeekend || $isHoliday;
                    foreach ($leaveDayRegister as $item) {
                        if ($item->leave_date && Carbon::parse($item->leave_date)->lt($dateTimekeeping)) {
                            continue;
                        }
                        $workingTime = ManageTimeView::findTimeSetting($item, $teamCodePrefix, $rangeTimes);
                        $leaveDayDateStart = Carbon::parse($item->date_start);
                        $leaveDayDateEnd = Carbon::parse($item->date_end);
                        $dateTimeStartWorking = clone $dateTimekeeping;
                        $dateTimeStartWorking->setTime($workingTime['morningInSetting']->hour, $workingTime['morningInSetting']->minute);
                        $dateTimeEndWorking = clone $dateTimekeeping;
                        $dateTimeEndWorking->setTime($workingTime['afternoonOutSetting']->hour, $workingTime['afternoonOutSetting']->minute);
                        $getDate = ManageTimeView::setDateApplicationByTableType(
                            $timeKeepingTable->type,
                            $leaveDayDateStart,
                            $leaveDayDateEnd,
                            $item->trial_date,
                            $item->offcial_date
                        );
                        if ($getDate['continue']) {
                            continue;
                        }
                        $leaveDayDateStart = $getDate['start'];
                        $leaveDayDateEnd = $getDate['end'];

                        if ($leaveDayDateStart < $dateTimeEndWorking && $leaveDayDateEnd > $dateTimeStartWorking) {
                            $dataInsertLeaveDay = [];
                            $dataInsertLeaveDay['timekeeping_table_id'] = $timekeepingTableId;
                            if ($isWeekendOrHoliday) {
                                continue;
                            }
                            $leaveDayCreator = $item->creator_id;
                            $keyLeaveDay = $leaveDayCreator . '-' . $dateTimekeeping->format('Y-m-d');
                            $dataInsertLeaveDay['employee_id'] = $leaveDayCreator;
                            $dataInsertLeaveDay['timekeeping_date'] = $dateTimekeeping->format('Y-m-d');

                            if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                if ($teamCodePrefix == Team::CODE_PREFIX_HCM || $teamCodePrefix == Team::CODE_PREFIX_RS) {
                                    // lấy time 1/4. Không có đăng ký thay doi time lam viec
                                    $objView = new ManageTimeView();
                                    $timeQuater = $objView->getTimeWorkingQuater([], $teamCodePrefix, $dateTimekeeping->format('Y-m-d'));
                                    $diffAndSession = with(new TimekeepingController())->getDiffTimesOfTimeKeepingResigerHCM($leaveDayDateStart, $leaveDayDateEnd, $dateTimekeeping->format('Y-m-d'), $workingTime, $timekeepingTableId, $item->creator_id, $timeQuater);
                                } else {
                                    $diffAndSession = TimekeepingController::getDiffTimesOfTimeKeepingResiger($leaveDayDateStart, $leaveDayDateEnd, $dateTimekeeping, $workingTime, $timekeepingTableId, $item->creator_id);
                                }
                            } else {
                                $diffAndSession = TimekeepingController::getDiffTimesOfTimeKeeping($leaveDayDateStart, $leaveDayDateEnd, $dateTimekeeping, $teamCodePrefix, $workingTime);
                            }
                            $timeLeaveDay = $diffAndSession['diff'];
                            if ($teamCodePrefix == Team::CODE_PREFIX_JP) {
                                if ($timeLeaveDay < 1 && $timeLeaveDay > 0) {
                                    $timeLeaveDay = 0.5;
                                } elseif ($timeLeaveDay >= 1) {
                                    $timeLeaveDay = 1;
                                }
                            }

                            if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                if (isset($diffAndSession['timeLateStart'])) {
                                    if ($diffAndSession['timeLateStart'] === ManageTimeConst::RESET) {
                                        $dataInsertLeaveDay['late_start_shift'] = 0;
                                    } elseif ($diffAndSession['timeLateStart'] != 0) {
                                        $dataInsertLeaveDay['late_start_shift'] = $diffAndSession['timeLateStart'];
                                    }
                                }
                                if (isset($diffAndSession['timeLateMid'])) {
                                    if ($diffAndSession['timeLateMid'] === ManageTimeConst::RESET) {
                                        $dataInsertLeaveDay['late_mid_shift'] = 0;
                                    } elseif ($diffAndSession['timeLateMid'] !== 0) {
                                        $dataInsertLeaveDay['late_mid_shift'] = $diffAndSession['timeLateMid'];
                                    }
                                }
                                if (isset($diffAndSession['timeEarlyMid'])) {
                                    if ($diffAndSession['timeEarlyMid'] === ManageTimeConst::RESET) {
                                        $dataInsertLeaveDay['early_mid_shift'] = 0;
                                    } elseif ($diffAndSession['timeEarlyMid'] != 0) {
                                        $dataInsertLeaveDay['early_mid_shift'] = $diffAndSession['timeEarlyMid'];
                                    }
                                }
                                if (isset($diffAndSession['timeEarlyEnd'])) {
                                    if ($diffAndSession['timeEarlyEnd'] === ManageTimeConst::RESET) {
                                        $dataInsertLeaveDay['early_end_shift'] = 0;
                                    } elseif ($diffAndSession['timeEarlyEnd'] != 0) {
                                        $dataInsertLeaveDay['early_end_shift'] = $diffAndSession['timeEarlyEnd'];
                                    }
                                }
                            }
                            if (isset($dataLeaveDay[$keyLeaveDay])) {
                                if ($diffAndSession['session'] < 0.5 && $leaveDayDateEnd->hour <= $workingTime['morningOutSetting']->hour) {
                                    $sessions = ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF;
                                } elseif ($diffAndSession['session'] < 0.5 && $leaveDayDateEnd->hour > $workingTime['morningOutSetting']->hour) {
                                    $sessions = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF;
                                } else {
                                   $sessions = $diffAndSession['session'];
                                }
                                // if ($item->used_leave_day == ManageTimeConst::USED_LEAVE_DAY) {
                                if ($item->salary_rate != ManageTimeConst::NOT_SALARY) {
                                    if (isset($dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'])) {
                                        $dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] = $dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] + $timeLeaveDay;
                                        $dataLeaveDay[$keyLeaveDay]['has_leave_day'] = $diffAndSession['session'];
                                        if ($dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] >= 1) {
                                            $dataLeaveDay[$keyLeaveDay]['has_leave_day'] = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
                                        }
                                    } else {
                                        $dataLeaveDay[$keyLeaveDay]['has_leave_day'] = $sessions;
                                        $dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] = $timeLeaveDay;
                                    }
                                } else {
                                    if (isset($dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'])) {
                                        $dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] = $dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] + $timeLeaveDay;
                                        $dataLeaveDay[$keyLeaveDay]['has_leave_day_no_salary'] = $diffAndSession['session'];
                                        if ($dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] >= 1) {
                                            $dataLeaveDay[$keyLeaveDay]['has_leave_day_no_salary'] = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
                                        }
                                    } else {
                                        $dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] = $timeLeaveDay;
                                        $dataLeaveDay[$keyLeaveDay]['has_leave_day_no_salary'] = $sessions;
                                    }
                                }
                                if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                    if (isset($dataLeaveDay[$keyLeaveDay]['timekeeping_number_register'])) {
                                        $dataLeaveDay[$keyLeaveDay]['timekeeping_number_register'] = $dataLeaveDay[$keyLeaveDay]['timekeeping_number_register'] + $diffAndSession['timekeeping_number_register'];
                                    } else {
                                        $dataInsertLeaveDay['timekeeping_number_register'] = $diffAndSession['timekeeping_number_register'];
                                    }
                                }
                                if (isset($dataInsertLeaveDay['late_start_shift'])) {
                                    $dataLeaveDay[$keyLeaveDay]['late_start_shift'] = $dataInsertLeaveDay['late_start_shift'];
                                }
                                if (isset($dataInsertLeaveDay['late_mid_shift'])) {
                                    $dataLeaveDay[$keyLeaveDay]['late_mid_shift'] = $dataInsertLeaveDay['late_mid_shift'];
                                }
                                if (isset($dataInsertLeaveDay['early_mid_shift'])) {
                                    $dataLeaveDay[$keyLeaveDay]['early_mid_shift'] = $dataInsertLeaveDay['early_mid_shift'];
                                }
                                if (isset($dataInsertLeaveDay['early_end_shift'])) {
                                    $dataLeaveDay[$keyLeaveDay]['early_end_shift'] = $dataInsertLeaveDay['early_end_shift'];
                                }

                            } else {
                                if ($diffAndSession['session'] < 0.5 && $leaveDayDateEnd->hour <= $workingTime['morningOutSetting']->hour) {
                                    $sessions = ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF;
                                } elseif ($diffAndSession['session'] < 0.5 && $leaveDayDateEnd->hour > $workingTime['morningOutSetting']->hour) {
                                    $sessions = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF;
                                 } else {
                                    $sessions = $diffAndSession['session'];
                                 }
                                // if ($item->used_leave_day == ManageTimeConst::USED_LEAVE_DAY) {
                                if ($item->salary_rate != ManageTimeConst::NOT_SALARY) {
                                    $dataInsertLeaveDay['has_leave_day'] = $sessions;
                                    $dataInsertLeaveDay['register_leave_has_salary'] = $timeLeaveDay;
                                } else {
                                    $dataInsertLeaveDay['has_leave_day_no_salary'] = $sessions;
                                    $dataInsertLeaveDay['register_leave_no_salary'] = $timeLeaveDay;
                                }
                                if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                    if (isset($diffAndSession['timekeeping_number_register'])) {
                                        $dataInsertLeaveDay['timekeeping_number_register'] = $diffAndSession['timekeeping_number_register'];
                                    }
                                }
                                $dataLeaveDay[$keyLeaveDay] = $dataInsertLeaveDay;
                            }
                            if (isset($dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'])) {
                                if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                    $dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] = round($dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'], 2);
                                } else {
                                    $dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] = round($dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'], 1);
                                }
                            }
                            if (isset($dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'])) {
                                if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                    $dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] = round($dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'], 2);
                                } else {
                                    $dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] = round($dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'], 1);
                                }
                            }
                        }
                    }
                    $timekeepingTableStartDate = date ("Y-m-d", strtotime("+1 day", strtotime($timekeepingTableStartDate)));
                }
                if (count($dataLeaveDay)) {
                    $viewTimeKeeping = new ViewTimeKeeping();
                    $viewTimeKeeping->updateDataCron($dataLeaveDay, $timekeepingTableId);
                }
            }
            // cập nhật bảng công tổng hợp
            static::updateTimekeepingAggregate($timekeepingTableId, $teamCodePrefix, [$leaveDay->creator_id]);
        }
    }

    /**
     * approve supplemental - bsc
     * @param  [type] $supp [description]
     * @return [type]       [description]
     */
    public static function insertSupplementTimeKeeping($supp)
    {
        $carbonStart = Carbon::parse($supp->date_start);
        $carbonEnd = Carbon::parse($supp->date_end);
        $monthOfTimeKeeping = $carbonStart->year . '-' . $carbonStart->month . '-01';

        $timeKeepingTables = self::getArrTKTableManageTK($carbonStart->toDateString(), $carbonEnd->toDateString(), [$supp->creator_id]);
        if (!$timeKeepingTables) {
            return;
        }
        // insert bổ sung công OT
        if ($supp->is_ot) {
            foreach ($timeKeepingTables as $timeKeepingTable) {
                static::insertSupplementTimeKeepingOT($timeKeepingTable, $carbonStart->toDateString(), $supp->creator_id);
            }
            return;
        }
        // Get supplement
        $supplementRegister = SupplementRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, [$supp->creator_id], $carbonStart->toDateString(), $carbonEnd->toDateString());
        // Get business trip
        $businessTripRegister = BusinessTripRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, [$supp->creator_id], $carbonStart->toDateString(), $carbonEnd->toDateString());

        foreach ($timeKeepingTables as $timeKeepingTable) {
            //Get holidays of time keeping table
            $team = Team::getTeamById($timeKeepingTable->team_id);
            $teamCodePrefix = Team::getTeamCodePrefix($team->code);
            $teamCodePrefix = Team::changeTeam($teamCodePrefix);
            $annualHolidays = CoreConfigData::getAnnualHolidays(2);
            $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);
            $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
            $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);
            $timekeepingTableId = $timeKeepingTable->id;

            //separate branch when have busineess trip register
            if (count($supplementRegister) && count($businessTripRegister)) {
                $supplementRegister = ManageTimeView::separateBranchBusiness($supplementRegister, $businessTripRegister, $teamCodePrefix);
            }
            if (count($supplementRegister)) {
                $timekeepingTableStartDate = $supp->date_start;
                $timekeepingTableEndDate = $supp->date_end;
                $dataSupplement = [];
                while (strtotime($timekeepingTableStartDate) <= strtotime($timekeepingTableEndDate)) {
                    $dateTimekeeping = Carbon::parse($timekeepingTableStartDate);
                    $isHoliday = ManageTimeCommon::isHoliday($dateTimekeeping, $annualHolidays, $specialHolidays, $teamCodePrefix);
                    $isWeekend = ManageTimeCommon::isWeekend($dateTimekeeping, $compensationDays);
                    $isWeekendOrHoliday = $isWeekend || $isHoliday;
                    foreach ($supplementRegister as $item) {
                        if ($item->leave_date && Carbon::parse($item->leave_date)->lt($dateTimekeeping)) {
                            continue;
                        }
                        $workingTime = ManageTimeView::findTimeSetting($item, $teamCodePrefix, $rangeTimes);
                        $dateTimeStartWorking = clone $dateTimekeeping;
                        $dateTimeStartWorking->setTime($workingTime['morningInSetting']->hour, $workingTime['morningInSetting']->minute);
                        $dateTimeEndWorking = clone $dateTimekeeping;
                        $dateTimeEndWorking->setTime($workingTime['afternoonOutSetting']->hour, $workingTime['afternoonOutSetting']->minute);
                        $supplementDateStart = Carbon::parse($item->start_at);
                        $supplementDateEnd = Carbon::parse($item->end_at);

                        $getDate = ManageTimeView::setDateApplicationByTableType(
                            $timeKeepingTable->type,
                            $supplementDateStart,
                            $supplementDateEnd,
                            $item->trial_date,
                            $item->offcial_date
                        );
                        if ($getDate['continue']) {
                            continue;
                        }
                        $supplementDateStart = $getDate['start'];
                        $supplementDateEnd = $getDate['end'];

                        if ($supplementDateStart < $dateTimeEndWorking && $supplementDateEnd > $dateTimeStartWorking) {
                            $dataInsertSupplement = [];
                            $dataInsertSupplement['timekeeping_table_id'] = $timekeepingTableId;
                            if ($isWeekendOrHoliday) {
                                continue;
                            }
                            $supplementCreator = $item->employee_id;
                            $keySupplement = $supplementCreator . '-' . $dateTimekeeping->format('Y-m-d');

                            $dataInsertSupplement['employee_id'] = $supplementCreator;
                            $dataInsertSupplement['timekeeping_date'] = $dateTimekeeping->format('Y-m-d');

                            $diffAndSession = TimekeepingController::getDiffTimesOfTimeKeeping($supplementDateStart, $supplementDateEnd, $dateTimekeeping, $teamCodePrefix, $workingTime);
                            $timeSupplement = $diffAndSession['diff'];
                            $dataInsertSupplement['has_supplement'] = $diffAndSession['session'];

                            if (isset($dataSupplement[$keySupplement])) {
                                $dataSupplement[$keySupplement]['register_supplement_number'] = $dataSupplement[$keySupplement]['register_supplement_number'] + $timeSupplement;
                                if ($dataSupplement[$keySupplement]['register_supplement_number'] >= 1) {
                                    $dataSupplement[$keySupplement]['has_supplement'] = ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY;
                                }
                            } else {
                                $dataInsertSupplement['register_supplement_number'] = $timeSupplement;
                                $dataSupplement[$keySupplement] = $dataInsertSupplement;
                            }
                            if (isset($dataSupplement[$keySupplement]['register_supplement_number'])) {
                                $dataSupplement[$keySupplement]['register_supplement_number'] = round($dataSupplement[$keySupplement]['register_supplement_number'], 2);
                            }
                        }
                    }
                    $timekeepingTableStartDate = date ("Y-m-d", strtotime("+1 day", strtotime($timekeepingTableStartDate)));
                }
                if (count($dataSupplement)) {
                    $viewTimeKeeping = new ViewTimeKeeping();
                    $viewTimeKeeping->updateDataCron($dataSupplement, $timekeepingTableId);
                }
            }

            // cập nhật bảng công tổng hợp
            static::updateTimekeepingAggregate($timekeepingTableId, $teamCodePrefix, [$supp->creator_id]);
        }
    }

    /**
     * BSC OT chỉ được 1 ngày nên startDate = endDate
     */
    public static function insertSupplementTimeKeepingOT($timeKeepingTable, $dateTableKeeping, $empId)
    {
        $timekeepings = self::getManageTimeKeeping($dateTableKeeping, $timeKeepingTable->id);
        if (!$timekeepings) {
            Log::info('Table manage_time_timekeepings do not exist');
            return false;
        }
        $teams[$timeKeepingTable->id] = static::getTeamByTKTable($timeKeepingTable);
        $type[$timeKeepingTable->id] = $timeKeepingTable->type;
        $viewTimeKeeping = new ViewTimeKeeping;
        $viewTimeKeeping->calculationOT($dateTableKeeping, $timekeepings, [$empId], $teams, $type);

        // cập nhật bảng công tổng hợp
        $team = Team::getTeamById($timeKeepingTable->team_id);
        $teamCodePrefix = Team::getTeamCodePrefix($team->code);
        $teamCodePrefix = Team::changeTeam($teamCodePrefix);
        static::updateTimekeepingAggregate($timeKeepingTable->id, $teamCodePrefix, [$empId]);
    }

    /**
     * insert mission to timekeeping
     * Đơn công tác có nhiều người - mỗi người có 1 time CT khác nhau
     * @param  [object] $busineess
     * @return [type]
     */
    public static function insertMissionTimeKeeping($busineess)
    {
        $arrEmpBusiness = BusinessTripEmployee::getEmployees($busineess->id);
        $empIds = [];
        $dateStart = '';
        $dateEnd = '';
        foreach ($arrEmpBusiness as $item) {
            $empIds[] = $item->employee_id;
            if ($dateStart == '') {
                $dateStart = $item->start_at;
                $dateEnd = $item->end_at;
            } else {
                $dateStart = strtotime($dateStart) < strtotime($item->start_at) ? $dateStart : $item->start_at;
                $dateEnd = strtotime($dateEnd) > strtotime($item->end_at) ? $dateEnd : $item->end_at;
            }
        }

        $carbonStart = Carbon::parse($dateStart);
        $carbonEnd = Carbon::parse($dateEnd);
        $monthOfTimeKeeping = $carbonStart->year . '-' . $carbonStart->month . '-01';

        $timeKeepingTables = self::getArrTKTableManageTK($carbonStart->toDateString(), $carbonEnd->toDateString(), $empIds);

        $businessTripRegister = BusinessTripRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, $empIds, $carbonStart->toDateString(), $carbonEnd->toDateString());
        
        if (!$timeKeepingTables) {
            return;
        }
        if (count($businessTripRegister)) {
            foreach ($timeKeepingTables as $timeKeepingTable) {
                $dataBussinessTrip = [];
                $timekeepingTableStartDate = $dateStart;
                $timekeepingTableEndDate = $dateEnd;

                //Get holidays of time keeping table
                $team = Team::getTeamById($timeKeepingTable->team_id);
                $teamCodePrefix = Team::getTeamCodePrefix($team->code);
                $teamCodePrefix = Team::changeTeam($teamCodePrefix);
                $annualHolidays = CoreConfigData::getAnnualHolidays(2);
                $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);
                $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
                $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);
                $timekeepingTableId = $timeKeepingTable->id;

                while (strtotime($timekeepingTableStartDate) <= strtotime($timekeepingTableEndDate)) {
                    $dateTimekeeping = Carbon::parse($timekeepingTableStartDate);
                    $isHoliday = ManageTimeCommon::isHoliday($dateTimekeeping, $annualHolidays, $specialHolidays, $teamCodePrefix);
                    $isWeekend = ManageTimeCommon::isWeekend($dateTimekeeping, $compensationDays);
                    $isWeekendOrHoliday = $isWeekend || $isHoliday;
                    foreach ($businessTripRegister as $item) {
                        if ($item->leave_date && Carbon::parse($item->leave_date)->lt($dateTimekeeping)) {
                            continue;
                        }
                        $workingTime = ManageTimeView::findTimeSetting($item, $teamCodePrefix, $rangeTimes);
                        $dateTimeStartWorking = clone $dateTimekeeping;
                        $dateTimeStartWorking->setTime($workingTime['morningInSetting']->hour, $workingTime['morningInSetting']->minute);
                        $dateTimeEndWorking = clone $dateTimekeeping;
                        $dateTimeEndWorking->setTime($workingTime['afternoonOutSetting']->hour, $workingTime['afternoonOutSetting']->minute);
                        $businessTripDateStart = Carbon::parse($item->start_at);
                        $businessTripDateEnd = Carbon::parse($item->end_at);
                        $getDate = ManageTimeView::setDateApplicationByTableType(
                            $timeKeepingTable->type,
                            $businessTripDateStart,
                            $businessTripDateEnd,
                            $item->trial_date,
                            $item->offcial_date
                        );
                        if ($getDate['continue']) {
                            continue;
                        }
                        $businessTripDateStart = $getDate['start'];
                        $businessTripDateEnd = $getDate['end'];

                        if ($businessTripDateStart < $dateTimeEndWorking && $businessTripDateEnd > $dateTimeStartWorking) {
                            $dataInsertBusinessTrip = [];
                            $dataInsertBusinessTrip['timekeeping_table_id'] = $timekeepingTableId;
                            if ($isWeekendOrHoliday) {
                                continue;
                            }
                            $businessTripCreator = $item->employee_id;
                            $keyBusinessTrip = $businessTripCreator . '-' . $dateTimekeeping->format('Y-m-d');

                            $dataInsertBusinessTrip['employee_id'] = $businessTripCreator;
                            $dataInsertBusinessTrip['timekeeping_date'] = $dateTimekeeping->format('Y-m-d');

                            $diffAndSession = TimekeepingController::getDiffTimesOfTimeKeeping($businessTripDateStart, $businessTripDateEnd, $dateTimekeeping, $teamCodePrefix, $workingTime);
                            $timeBusinessTrip = $diffAndSession['diff'];
                            $dataInsertBusinessTrip['has_business_trip'] = $diffAndSession['session'];
                            
                            if (isset($dataBussinessTrip[$keyBusinessTrip])) {
                                $dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'] = $dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'] + $timeBusinessTrip ;
                                if ($dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'] >= 1) {
                                    $dataBussinessTrip[$keyBusinessTrip]['has_business_trip'] = ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY;
                                }
                            } else {
                                $dataInsertBusinessTrip['register_business_trip_number'] = $timeBusinessTrip;
                                $dataBussinessTrip[$keyBusinessTrip] = $dataInsertBusinessTrip;
                            }
                            if (isset($dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'])) {
                                $dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'] = round($dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'], 2);
                            }
                        }
                    }
                    $timekeepingTableStartDate = date ("Y-m-d", strtotime("+1 day", strtotime($timekeepingTableStartDate)));
                }
                if (count($dataBussinessTrip)) {
                    $viewTimeKeeping = new ViewTimeKeeping();
                    $viewTimeKeeping->updateDataCron($dataBussinessTrip, $timekeepingTableId);
                }
                // cập nhật bảng công tổng hợp
                static::updateTimekeepingAggregate($timekeepingTableId, $teamCodePrefix, $empIds);
            }
        }
    }

    //============ hủy các đơn ============

    /**
     * disapproved leave day 
     * @param  [object] $leaveDay
     */
    public static function removeLeaveDayTimeKeeping($leaveDay)
    {
        $carbonStart = Carbon::parse($leaveDay->date_start);
        $carbonEnd = Carbon::parse($leaveDay->date_end);

        // ==== get manage_time_timekeepings ====
        $dataResetTimekeeping = [
            'has_leave_day' => ManageTimeConst::HAS_NOT_LEAVE_DAY,
            'has_leave_day_no_salary' => ManageTimeConst::HAS_NOT_LEAVE_DAY,
            'register_leave_has_salary' => 0,
            'register_leave_no_salary' => 0,
            'timekeeping_number_register' => 0,
        ];
        //update reset manage timekeepings
        self::resetManageTimeKeeping($leaveDay->creator_id, $carbonStart->toDateString(), $carbonEnd->toDateString(), $dataResetTimekeeping);
        
        //tính lại phép nếu ngày đó có nhiều đơn phép
        self::insertLeaveDayTimekeeping($leaveDay);

        // ==== tính lại time đi muộn về xơm khi nghỉ 1/4
        $numberDayOff = explode(".", $leaveDay->number_days_off);
        if (isset($numberDayOff[1]) && $numberDayOff[1] != '0' && $numberDayOff[1] != '5') {
            self::calculationAgainLateEarly($carbonStart, $leaveDay->creator_id);
            if ((float)$leaveDay->number_days_off > 1) {
                self::calculationAgainLateEarly($carbonEnd, $leaveDay->creator_id);
            }
        }

        // cập nhật bảng công tổng hợp
        // self::updateTimekeepingAggregate($timekeepingTableId, $teamCodePrefix, [$leaveDay->creator_id]);
    }

    /**
     * disapprove supplement
     * @param  [object] $supp
     */
    public static function removeSupplementTimeKeeping($supp)
    {
        $carbonStart = Carbon::parse($supp->date_start);
        $carbonEnd = Carbon::parse($supp->date_end);

        // remove supplement
        if ($supp->is_ot) {
            static::removeSupplementOTTimeKeeping($supp, $carbonStart, $carbonEnd);
            return;
        }

        // ==== get manage_time_timekeepings ====
        $dataResetTimekeeping = [
            'has_supplement' => ManageTimeConst::HAS_NOT_SUPPLEMENT,
            'register_supplement_number' => 0,
        ];
        //update reset manage timekeepings
        self::resetManageTimeKeeping($supp->creator_id, $carbonStart->toDateString(), $carbonEnd->toDateString(), $dataResetTimekeeping);
        
        //tính lại phép nếu ngày đó có nhiều bổ sung công
        self::insertSupplementTimeKeeping($supp);
    }

    /**
     * reset BSC OT
     * @param  [object] $supp
     * @param  [carbon] $carbonStart
     * @param  [carbon] $carbonEnd
     */
    public static function removeSupplementOTTimeKeeping($supp, $carbonStart, $carbonEnd)
    {
        $dataResetTimekeeping = [
            'register_ot' => 0,
            'register_ot_has_salary' => 0,
            'register_ot_no_salary' => 0
        ];
        //update reset manage timekeepings
        self::resetManageTimeKeeping($supp->creator_id, $carbonStart->toDateString(), $carbonEnd->toDateString(), $dataResetTimekeeping);

        // cập nhật bảng công tổng hợp
        $tkTables = self::getArrTKTableManageTK($carbonStart->toDateString(), $carbonEnd->toDateString(), [$supp->creator_id]);
        if (count($tkTables)) {
            foreach ($tkTables as $item) {
                $team = Team::getTeamById($item->team_id);
                $teamCodePrefix = Team::getTeamCodePrefix($team->code);
                $teamCodePrefix = Team::changeTeam($teamCodePrefix);
                static::updateTimekeepingAggregate($item->id, $teamCodePrefix, [$supp->creator_id]);
            }
        }
    }

    /**
     * disapprove supplement
     * @param  [object] $supp
     */
    public static function removeMissionTimeKeeping($busineess)
    {
        $arrEmpBusiness = BusinessTripEmployee::getEmployees($busineess->id);
        $dataResetTimekeeping = [
            'has_business_trip' => ManageTimeConst::HAS_NOT_BUSINESS_TRIP,
            'register_business_trip_number' => 0,
        ];
        $tables = [];
        $tableTK = [];
        foreach ($arrEmpBusiness as $item) {
            $carbonStart = Carbon::parse($item->start_at);
            $carbonEnd = Carbon::parse($item->end_at);
            self::resetManageTimeKeeping($item->employee_id, $carbonStart->toDateString(), $carbonEnd->toDateString(), $dataResetTimekeeping);

            // cập nhật bảng công tổng hợp
            $tkTables = self::getArrTKTableManageTK($carbonStart->toDateString(), $carbonEnd->toDateString(), [$item->employee_id]);
            if (count($tkTables)) {
                foreach ($tkTables as $itemTbl) {
                    if (array_key_exists($itemTbl->id, $tables)) {
                        $tables[$itemTbl->id]['emps'] = array_merge($tables[$itemTbl->id]['emps'], [$item->employee_id]);
                        continue;
                    } else {
                        $team = Team::getTeamById($itemTbl->team_id);
                        $teamCodePrefix = Team::getTeamCodePrefix($team->code);
                        $teamCodePrefix = Team::changeTeam($teamCodePrefix);
                        $tables[$itemTbl->id] = [
                            'id' => $itemTbl->id,
                            'team' => $teamCodePrefix,
                            'emps' => [$item->employee_id],
                        ];
                    }
                    $tableTK[$itemTbl->id] = $itemTbl;
                }
            }
        }
        if (count($tables)) {
            foreach ($tables as $tKTableId => $value) {
                $dataRelate['emp_ids'] = $value['emps'];
                self::updateTimekeepingAggregate($tKTableId, $tables[$tKTableId]['team'], $dataRelate);
            }
        }
    }

    /**
     * remove time OT in timekeeping
     * @return []
     */
    public function removeRegisterOT($registerIds)
    {
        $objOTReg = new OtRegister();
        $regs = $objOTReg->getListRegisterById($registerIds);
        if (!count($regs)) {
            return;
        }
        $dataResetTimekeeping = [
            'register_ot' => ManageTimeConst::IS_NOT_OT,
            'register_ot_no_salary' => 0,
            'register_ot_has_salary' => 0,
        ];

        //=== bảng công tháng này ===
        foreach ($regs as $item) {
            $carbonStart = Carbon::parse($item->start_at);
            $carbonEnd = Carbon::parse($item->end_at);
            self::resetManageTimeKeeping($item->employee_id, $carbonStart->toDateString(), $carbonEnd->toDateString(), $dataResetTimekeeping);

            // tính lại Ot nếu ngày đó có 2 đơn ot - code tù
            $regsOT = $objOTReg->getListRegisterByNotId($item->id, $item->employee_id, $carbonStart->toDateString(), $carbonEnd->toDateString());
            if (count($regsOT)) {
                $tables = TimekeepingTable::where('month', $carbonStart->month)->where('year', $carbonStart->year)->whereNull('deleted_at')->get();
                $ids = $tables->lists('id')->toArray();
                $teams = [];
                $typeTable = [];
                foreach ($tables as $table) {
                    $team = Team::getTeamById($table->team_id);
                    $teamCodePre = Team::getTeamCodePrefix($team->code);
                    $teamCodePre = Team::changeTeam($teamCodePre);
                    $teams[$table->id] = empty($teamCodePre) ? "hanoi" : $teamCodePre;
                    $typeTable[$table->id] = $table->type;
                }
                $timekeepings =  Timekeeping::whereIn('timekeeping_table_id', $ids)
                ->where('timekeeping_date', $carbonStart->format("Y-m-d"))
                ->where('employee_id', $item->employee_id)
                ->orderBy('employee_id')
                ->get();
                $this->calculationOT($carbonStart->toDateString(), $timekeepings, [$item->employee_id], $teams, $typeTable, $regsOT);
            }
        }
    }
    //=======================
    /**
     * merege mảng 2 chiều timekeeping table
     * @param  [array] $timeKeepingTableStart
     * @param  [array] $timeKeepingTableEnd
     * @return [array]
     */
    public static function timeKeepingMerge($timeKeepingTableStart, $timeKeepingTableEnd)
    {
        return array_map("unserialize", array_unique(array_map("serialize", array_merge($timeKeepingTableStart, $timeKeepingTableEnd))));
    }

    /**
     * get timekeeping table with manage_time_timekeepings
     * mục đích lấy ra thông tin timekeeping table
     * @param  [date] $date  [Y-m-d]
     * @param  [int] $empId
     * @return [type]
     */
    public static function getArrTKTableManageTK($dateStart, $dateEnd, $empIds)
    {
        return DB::table('manage_time_timekeeping_tables')->select(
            'manage_time_timekeeping_tables.id',
            'manage_time_timekeeping_tables.creator_id',
            'timekeeping_table_name',
            'team_id',
            'start_date',
            'end_date',
            'year',
            'month',
            'type'
        )
        ->leftJoin('manage_time_timekeepings', 'manage_time_timekeepings.timekeeping_table_id', '=', 'manage_time_timekeeping_tables.id')
        ->whereDate('timekeeping_date', '>=', $dateStart)
        ->whereDate('timekeeping_date', '<=', $dateEnd)
        ->whereIn('employee_id', $empIds)
        ->whereNull('manage_time_timekeeping_tables.deleted_at')
        ->groupBy('manage_time_timekeeping_tables.id')
        ->get();
    }

    /**
     * get timekeeping table with manage_time_timekeepings
     * mục đích lấy ra thông tin timekeeping table
     * get vì có nhiều bảng công trong tháng - nhân viên có 2 bảng công chính thức
     * @param  [date] $date  [Y-m-d]
     * @param  [int] $empId
     * @return [type]
     */
    public static function getTKTableManageTK($date, $empId)
    {
        return DB::table('manage_time_timekeeping_tables')->select(
            'manage_time_timekeeping_tables.id',
            'manage_time_timekeeping_tables.creator_id',
            'timekeeping_table_name',
            'team_id',
            'start_date',
            'end_date',
            'year',
            'month',
            'type'
        )
        ->leftJoin('manage_time_timekeepings', 'manage_time_timekeepings.timekeeping_table_id', '=', 'manage_time_timekeeping_tables.id')
        ->where('timekeeping_date', $date)
        ->where('employee_id', $empId)
        ->whereNull('manage_time_timekeeping_tables.deleted_at')
        ->get();
    }

    /**
     * get manage timekeeping
     * @param  [date] $dateTableKeeping [Y-m-d]
     * @param  [int] $tableId
     * @return [type]
     */
    public static function getManageTimeKeeping($dateTableKeeping, $tableId)
    {
        return Timekeeping::select(
        'manage_time_timekeepings.*',
        'emp.email'
        )
        ->where('timekeeping_table_id', $tableId)
        ->leftJoin('employees as emp', 'emp.id', '=', 'manage_time_timekeepings.employee_id')
        ->where('timekeeping_date', $dateTableKeeping)
        ->where(function($sql) {
            $sql->whereNotNull('start_time_morning_shift')
                ->orwhereNotNull('start_time_afternoon_shift');
        })
        ->orderBy('timekeeping_table_id')
        ->get();
    }

    /**
     * update reset manage timekeepings
     * @param  [int] $empId
     * @param  [date] $dateStart [Y-m-d]
     * @param  [date] $dateEnd   [Y-m-d]
     * @param  [array] $data
     */
    public static function resetManageTimeKeeping($empId, $dateStart, $dateEnd, $data)
    {
        Timekeeping::whereDate('timekeeping_date', '>=', $dateStart)
            ->whereDate('timekeeping_date','<=', $dateEnd)
            ->where('employee_id', $empId)
            ->update($data);
    }

    /**
     * tính toán lại time đi xớm về muộn khi nghỉ phép có 1/4
     * @param  [carbon] $date
     * @param  [int] $empId
     * @return [type]
     */
    public static function calculationAgainLateEarly($dateCarbon, $empId)
    {
        $tables = self::getTKTableManageTK($dateCarbon->toDateString(), $empId);
        if (!count($tables)) {
            Log::info('Table timekeeping month now do not exist');
            return false;
        }
        foreach ($tables as $table) {
            $timekeepings = self::getManageTimeKeeping($dateCarbon->format("Y-m-d"), $table->id);
            if (!$timekeepings) {
                continue;
            }
            //=== get team of table ===
            $teams = [];
            $typeTable = [];
            $teams[$table->id] = static::getTeamByTKTable($table);
            $typeTable[$table->id] = $table->type;

            $viewTimeKeeping = new ViewTimeKeeping;
            $viewTimeKeeping->calculateTimeWork($timekeepings, $teams, $typeTable, $dateCarbon->format("Y-m-d"));
        }
    }

    /**
     * Lấy team theo bảng công
     * @param  [type] $table
     * @return [type]
     */
    public static function getTeamByTKTable($table)
    {
        $team = Team::getTeamById($table->team_id);
        $teamCodePre = Team::getTeamCodePrefix($team->code);
        $teamCodePre = Team::changeTeam($teamCodePre);
        return empty($teamCodePre) ? "hanoi" : $teamCodePre;
    }

    /**
     * [deleteDateTimeKeeping description]
     * @param  [date] $dateStart [Y-m-d]
     * @param  [date] $endDate
     * @param  [int] $tKTableId
     */
    public function deleteDateTimeKeeping($dateStart, $endDate, $tKTableId)
    {
        Timekeeping::where('timekeeping_date', '>', $dateStart)
            ->where('timekeeping_date', '<=', $endDate)
            ->where('timekeeping_table_id', $tKTableId)
            ->delete();
        return;
    }

    /**
     * [insertDateTimeKeeping description]
     * @param  [object] $timekeepingTable [description]
     * @param  [date] $startDate [Y-m-d]
     * @param  [date] $endDate 
     */
    public function insertDateTimeKeeping($timekeepingTable, $startDate, $endDate)
    {
        $teamOfTimekeeping = Team::find($timekeepingTable->team_id);
        $teamCodePrefix = Team::getTeamCodePrefix($teamOfTimekeeping->code);
        $teamCodePrefix = Team::changeTeam($teamCodePrefix);
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);
        $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
        $teamIds = $this->getTeamTimekeeping($timekeepingTable->team_id);

        $dataInsertTimekeeping = [];
        if ($timekeepingTable->type == getOptions::WORKING_OFFICIAL) {
            $employee = new Employee();
            $employeeTimekeeping = $employee->getEmpTrialOrOffcial($timekeepingTable->end_date, $teamIds);
        } else {
            $contractType = [getOptions::WORKING_PARTTIME];
            if ($teamCodePrefix === TeamConst::CODE_DANANG) {
                $contractType[] = getOptions::WORKING_INTERNSHIP;
            }
            $contact = new ContractModel();
            $employeeTimekeeping = $contact->getEmpByContractType($timekeepingTable, $contractType);
        }
        $dataTimekeeping = new Timekeeping();
        if (count($employeeTimekeeping)) {
            $dates = [];
            $now = Carbon::now();
            while (strtotime($startDate) <= strtotime($endDate)) {
                $dates[] = $startDate->toDateString();
                $startDate->addDay();
            }
            $dataTimekeeping->timekeeping_table_id = $timekeepingTable->id;
            $manageTimeView = new ManageTimeView();
            foreach ($employeeTimekeeping as $emp) {
                $dataTimekeeping->employee_id = $emp->employee_id;
                $dataTimekeeping->created_at = $now;
                $dataTimekeeping->updated_at = $now;

                $empOffcialDate = $emp->offcial_date;
                $empTrialDate = $emp->trial_date;
                $empOffcialDateCarbon = Carbon::parse($empOffcialDate)->format('Y-m-d');
                foreach ($dates as $date) {
                    $dateCarbon = Carbon::createFromFormat('Y-m-d', $date);
                    $isWeekend = ManageTimeCommon::isWeekend($dateCarbon, $compensationDays);
                    $isHoliday = ManageTimeCommon::isHolidays($dateCarbon, [$annualHolidays, $specialHolidays]);
                    $dataTimekeeping->timekeeping_date = $date;

                    $dataTimekeeping->is_official =  0;
                    if ($empOffcialDate && strtotime($dateCarbon->format('Y-m-d')) >= strtotime($empOffcialDateCarbon)) {
                        $dataTimekeeping->is_official =  1;
                    }

                    if (empty($emp->leave_date) || Carbon::parse($emp->leave_date)->gte(Carbon::parse($date))) {
                        $timekeepingResult = $manageTimeView->timekeepingResult($dataTimekeeping, $isWeekend, $isHoliday, $empOffcialDate, $empTrialDate, $timekeepingTable->contract_type, null, null, $timekeepingTable->type);
                        $dataTimekeeping->timekeeping = $timekeepingResult[0];
                        $dataTimekeeping->timekeeping_number = $timekeepingResult[1];
                    } else {
                        $dataTimekeeping->timekeeping = 0;
                        $dataTimekeeping->timekeeping_number = 0;
                    }
                    $dataInsertTimekeeping[] = $dataTimekeeping->toArray();
                }
            }
            unset($employeeTimekeeping);
            foreach (collect($dataInsertTimekeeping)->chunk(1000) as $chunk) {
                Timekeeping::insert($chunk->toArray());
            }
            unset($dataInsertTimekeeping);
        }
        return;
    }

     /**
     * get list team
     * @param  [collection] $timekeepingTable
     * @return [type]
     */
    public function getTeamTimekeeping($teamId)
    {
        $teamIds = [];
        $teamIds[] = (int) $teamId;
        ManageTimeCommon::getTeamChildRecursive($teamIds, $teamId);
        $teamHN = Team::select('id')->where('code', TeamConst::CODE_HANOI)->first();

        if ($teamHN && $teamHN->id == $teamId) {
            // Add team BOD and PQA
            $team = new Team();
            $teamIds = array_unique(array_merge($teamIds, $team->getTeamBODPQA()));
        }
        return array_values($teamIds);
    }

    /**
     * [updateTimekeepingAggregate description]
     * @param  [type] $idTKtbl   [description]
     * @param  [type] $teamTKtbl [description]
     * @param  [type] $arrEmpId     [description]
     * @return [type]            [description]
     */
    public static function updateTimekeepingAggregate($idTKtbl, $teamCodePre, $arrEmpId)
    {
        $data = [
            'timekeeping_table_id' => $idTKtbl,
            'team_code' => $teamCodePre,
        ];
        $request = new Request($data);
        TimekeepingController::updateTimekeepingAggregate($request, $arrEmpId);
    }

    /**
     * Update data into table timekeeping - customer lai Timekeeping::updateData 
     *
     * @param array $data
     * @param int $timekeepingTableId
     * @param boolean $update
     * @return boolean
     */
    public function updateDataCron($data, $timekeepingTableId)
    {
        $table = Timekeeping::getTableName();
        if (!count($data)) {
            return false;
        }
        $final = [];
        $employeeIds = [];
        foreach ($data as $key => $val) {
            $employeeId = $val['employee_id'];
            $timekeepingDate = $val['timekeeping_date'];
            $employeeIds[$val['employee_id']]= $val['employee_id'];
            foreach (array_keys($val) as $field) {
                if ($field == 'employee_id' || $field == 'timekeeping_date' || $field == 'updated_at') {
                    continue;
                }
                $value = (is_null($val[$field]) ? 'NULL' : '"' . $val[$field] . '"');
                $final[$field][] = 'WHEN `timekeeping_table_id` = "' . $timekeepingTableId . '" AND `employee_id` = "' . $employeeId . '" AND `timekeeping_date` = "' . $timekeepingDate . '" THEN ' . $value . ' ';
            }
        }

        $cases = '';
        $strEmployeeId = implode(',', $employeeIds);
        foreach ($final as $k => $v) {
            if ($k == 'employee_id' || $k == 'timekeeping_date') {
                continue;
            }
            $cases .=  '`'. $k.'` = (CASE '. implode("\n", $v) . "\n" . 'ELSE `'.$k.'` END), ';
        }
        $query = 'UPDATE ' . $table . ' SET '. substr($cases, 0, -2) . ' WHERE `timekeeping_table_id` = "' . $timekeepingTableId . '" AND employee_id IN (' . $strEmployeeId . ')';
        DB::statement($query);
        return true;
    }

    //============= start lock =============
    /**
     * build manage time keeping table
     * @return build
     */
    public function buildEmpAfterLock()
    {
        return DB::table('manage_time_timekeeping_tables AS tkTable')
        ->select(
            'tkl.id AS lockId',
            'tkl.timekeeping_table_id AS tkTableId',
            'tkl.time_close_lock',
            'tkl.time_open_lock',
            'employees.join_date',
            'employees.trial_date',
            'employees.trial_end_date',
            'employees.offcial_date'
        )
        ->leftJoin('manage_time_timekeepings AS tk', 'tkTable.id', '=', 'tk.timekeeping_table_id')
        ->leftJoin('timekeeping_locks as tkl', 'tkl.timekeeping_table_id', '=', 'tkTable.id');
    }

    /**
     * get leave days approved after lock
     * @param int $idTable
     * @param int $idLock
     * @return object
     */
    public function getLeaveDayAfterLock($idTable, $idLock)
    {
        return $this->buildEmpAfterLock()->addSelect(
            'leaveDay.id AS registerId',
            'leaveDay.creator_id AS employee_id',
            'leaveDay.updated_at AS registerUpdate',
            'leaveDay.date_start as date_start',
            'leaveDay.date_end as date_end'
        )
        ->join('leave_day_registers AS leaveDay', function ($join) {
            $join->on('leaveDay.creator_id', '=', 'tk.employee_id')
                ->on('leaveDay.updated_at', '>=', 'tkl.time_close_lock')
                ->on(function ($query) {
                    $query->on('leaveDay.updated_at', '<', 'tkl.time_open_lock')
                        ->orWhereNull('tkl.time_open_lock');
                })
                ->on(DB::raw('Date(leaveDay.date_end)'), '<=', 'tkTable.end_date')
                ->on(DB::raw("Date(leaveDay.date_end)"), '>=', 'tkTable.start_date')
                ->where('leaveDay.status',  '=', LeaveDayRegister::STATUS_APPROVED);
        })
        ->leftJoin('employees', 'employees.id', '=', 'leaveDay.creator_id')
        ->where('tkTable.id', $idTable)
        ->where('tkl.id', $idLock)
        ->whereNull('tkTable.deleted_at')
        ->groupBy('leaveDay.id', 'leaveDay.creator_id')
        ->get();
    }

    /**
     * get supplement approved after lock
     * @param int $idTable
     * @param int $idLock
     * @return object
     */
    public function getSuppAfterLock($idTable, $idLock)
    {
        return $this->buildEmpAfterLock()->addSelect(
            'supplement.id AS registerId',
            'supplement.updated_at AS registerUpdate',
            'supplement.employee_id AS employee_id',
            'supplement.is_ot',
            'supplement.start_at as date_start',
            'supplement.end_at as date_end'
        )
        ->join(DB::raw("(SELECT supEmp.employee_id, sup.id, sup.status, sup.updated_at, supEmp.start_at, supEmp.end_at, sup.is_ot 
                FROM supplement_registers as sup
                LEFT JOIN supplement_employees AS supEmp ON supEmp.supplement_registers_id = sup.id
                WHERE sup.deleted_at IS NULL AND sup.status = " . SupplementRegister::STATUS_APPROVED . "
            ) AS supplement"),  function ($join) {
            $join->on('supplement.employee_id', '=', 'tk.employee_id')
                ->on('supplement.updated_at', '>=', 'tkl.time_close_lock')
                ->on(function ($query) {
                    $query->on('supplement.updated_at', '<=', 'tkl.time_open_lock')
                        ->orWhereNull('tkl.time_open_lock');
                })
                ->on(DB::raw('Date(supplement.start_at)'), '<=', 'tkTable.end_date')
                ->on(DB::raw("Date(supplement.end_at)"), '>=', 'tkTable.start_date');
        })
        ->leftJoin('employees', 'employees.id', '=', 'supplement.employee_id')
        ->where('tkTable.id', $idTable)
        ->where('tkl.id', $idLock)
        ->whereNull('tkTable.deleted_at')
        ->groupBy('supplement.id', 'supplement.employee_id')
        ->get();
    }

    /**
     * get business approved after lock
     * @param int $idTable
     * @param int $idLock
     * @return object
     */
    public function getBusinessAfterLock($idTable, $idLock)
    {
        return  $this->buildEmpAfterLock()->addSelect(
            'business.id AS registerId',
            'business.updated_at AS registerUpdate',
            'business.employee_id AS employee_id',
            'business.start_at as date_start',
            'business.end_at as date_end'
        )
        ->join(DB::raw("(SELECT busEmp.employee_id, bus.id, bus.status, bus.updated_at, busEmp.start_at, busEmp.end_at
                FROM business_trip_registers as bus
                LEFT JOIN business_trip_employees AS busEmp ON busEmp.register_id = bus.id
                WHERE bus.deleted_at IS NULL AND bus.status = " . BusinessTripRegister::STATUS_APPROVED . "
            ) AS business"),  function ($join) {
            $join->on('business.employee_id', '=', 'tk.employee_id')
                ->on('business.updated_at', '>=', 'tkl.time_close_lock')
                ->on(function ($query) {
                    $query->on('business.updated_at', '<', 'tkl.time_open_lock')
                        ->orWhereNull('tkl.time_open_lock');
                })
                ->on(DB::raw('Date(business.start_at)'), '<=', 'tkTable.end_date')
                ->on(DB::raw("Date(business.end_at)"), '>=', 'tkTable.start_date');
        })
        ->leftJoin('employees', 'employees.id', '=', 'business.employee_id')
        ->where('tkTable.id', $idTable)
        ->where('tkl.id', $idLock)
        ->whereNull('tkTable.deleted_at')
        ->groupBy('business.id', 'business.employee_id')
        ->get();
    }

    /**
     * get register OT approved after lock
     * @param int $idTable
     * @param int $idLock
     * @return object
     */
    public function getRegisterOTAfterLock($idTable, $idLock)
    {
        return  $this->buildEmpAfterLock()->addSelect(
            'registerOT.id AS registerId',
            'registerOT.updated_at AS registerUpdate',
            'registerOT.employee_id AS employee_id',
            'registerOT.start_at as date_start',
            'registerOT.end_at as date_end'
        )
        ->join(DB::raw("(SELECT OTEmp.employee_id, rOT.id, rOT.status, rOT.updated_at, OTEmp.start_at, OTEmp.end_at
                FROM ot_registers as rOT
                LEFT JOIN ot_employees AS OTEmp ON OTEmp.ot_register_id = rOT.id
                WHERE rOT.deleted_at IS NULL AND rOT.status = " . BusinessTripRegister::STATUS_APPROVED . "
            ) AS registerOT"),  function ($join) {
            $join->on('registerOT.employee_id', '=', 'tk.employee_id')
                ->on('registerOT.updated_at', '>=', 'tkl.time_close_lock')
                ->on(function ($query) {
                    $query->on('registerOT.updated_at', '<', 'tkl.time_open_lock')
                        ->orWhereNull('tkl.time_open_lock');
                })
                ->on(DB::raw('Date(registerOT.start_at)'), '<=', 'tkTable.end_date')
                ->on(DB::raw("Date(registerOT.end_at)"), '>=', 'tkTable.start_date');
        })
        ->leftJoin('employees', 'employees.id', '=', 'registerOT.employee_id')
        ->where('tkTable.id', $idTable)
        ->where('tkl.id', $idLock)
        ->whereNull('tkTable.deleted_at')
        ->groupBy('registerOT.id', 'registerOT.employee_id')
        ->get();
    }
    //============= end lock =============

    /**
     * get label status all application: leave days, supplement, business, ot
     *
     * @return array
     */
    public function getLabelApp()
    {
        return [
            SupplementRegister::STATUS_UNAPPROVE => Lang::get('manage_time::view.Unapprove'),
            SupplementRegister::STATUS_APPROVED => Lang::get('manage_time::view.Approved'),
            SupplementRegister::STATUS_DISAPPROVE => Lang::get('manage_time::view.Disapprove'),
            SupplementRegister::STATUS_CANCEL => Lang::get('manage_time::view.Cancelled'),
        ];
    }

    // ================ chấm công ================

    /**
     * Lấy ra bảng công của nhân viên
     * @param string $dateStart
     * @param string $dateEnd
     * @param array $empIds
     * @return Collection
     */
    public function getTimekeepingByEmpId($dateStart, $dateEnd, $empIds)
    {
        $objTk = new Timekeeping();
        return $objTk->getTimekeepingByEmpId($dateStart, $dateEnd, $empIds);
    }

    /**
     * Lấy ra  bảng công của nhân viên tương ứng với từng nhân viên
     * @param string $dateStart
     * @param string $dateEnd
     * @param array $empIds
     * @return array
     */
    public function getArrTimekeepingByEmpId($dateStart, $dateEnd, $empIds)
    {
        $data = [];
        $collection = $this->getTimekeepingByEmpId($dateStart, $dateEnd, $empIds);
        if (count($collection)) {
            foreach ($collection as $item) {
                $data[$item->employee_id][] = $item;
            }
        }
        return $data;
    }

    /**
     * Lấy ra thời gian làm việc của nhân viên
     *
     * @param $dateStart
     * @param $dateEnd
     * @param $empIds
     * @param null $checkinStandard
     * @param null $checkoutStandard
     * @return array
     */
    public function getTimeWorkByEmpId($dateStart, $dateEnd, $empIds, $checkinStandard = null, $checkoutStandard = null)
    {
        $objTeam = new TeamList();
        $teamEmp = $objTeam->getTeamPrefixOfEmpIds($empIds);
        $holidayWeekday = with(new Team())->getHolidaysCompensate();
        $arrTkEmp = $this->getArrTimekeepingByEmpId($dateStart, $dateEnd, $empIds);

        $infoEmp = [];
        $employees = Employee::whereIn('id', $empIds)->get();
        foreach ($employees as $employee) {
            $infoEmp[$employee->id] = $employee;
        }

        $objLeaveDay = new LeaveDayRegister();
        $regLeaveDay = $objLeaveDay->getArrLeaveDayApprovedByEmpId($empIds, $dateStart, $dateEnd);
        $data = [];

        foreach ($arrTkEmp as $empId => $timekeepings) {
            if (!isset($regLeaveDay[$empId])) {
                $leaveDay = [];
            } else {
                $leaveDay = $regLeaveDay[$empId];
            }
            $data[$empId] = $this->getTimeWorking($timekeepings, $leaveDay, $infoEmp[$empId], $teamEmp[$empId], $holidayWeekday[$teamEmp[$empId]], $checkinStandard, $checkoutStandard);
        }
        return $data;
    }

    /**
     * từ bảng chấm công lấy ra thời gian làm việc
     *
     * @param $timekeepings
     * @param $regLeaveDay
     * @param $employee
     * @param $teamCodePrefix
     * @param $holidayWeek
     * @param null $checkinStandard
     * @param null $checkoutStandard
     * @return array
     */
    public function getTimeWorking($timekeepings, $regLeaveDay, $employee, $teamCodePrefix, $holidayWeek, $checkinStandard = null, $checkoutStandard = null)
    {
        $empId = $employee->id;
        $collect = $timekeepings;

        if (!count($collect)) {
            return [];
        }
        $compensationDays = $holidayWeek['compensationDays'];
        $annualHolidays = $holidayWeek['annualHolidays'];
        $specialHolidays = $holidayWeek['specialHolidays'];

        $results = [];
        $checkEmp = [];
        $k = -1;

        foreach ($collect as $item) {
            // check get two table timekeeping new
            if (!in_array($item->idTable, $checkEmp)) {
                $checkEmp[] = $item->idTable;
                $k++;
                // giới hạn 2 bảng công
//                if ($k == 2) {
//                    break;
//                }
            }

            $date = $item->timekeeping_date;
            $isWeekend = ManageTimeCommon::isWeekend(Carbon::createFromFormat('Y-m-d', $date), $compensationDays);
            $isHoliday = ManageTimeCommon::isHoliday(Carbon::createFromFormat('Y-m-d', $date), $annualHolidays, $specialHolidays, $teamCodePrefix);
            $isWeekendOrHoliday = $isWeekend || $isHoliday;

            $timeWork = Timekeeping::getTimeWorking($employee, $date);
            $timeAfterOut = $timeWork['afternoonOutSetting']->format('H:i');
            $timeAfterIn = $timeWork['afternoonInSetting']->format('H:i');
            $timeMorIn = $timeWork['morningInSetting']->format('H:i');
            $timeMorOut = $timeWork['morningOutSetting']->format('H:i');
            $breakTime = gmdate('H:i', $timeWork['afternoonInSetting']->diffInSeconds($timeWork['morningOutSetting']));

            if (!empty($checkinStandard) && !empty($checkoutStandard)) {
                $breakTime = TimesheetHelper::instance()->calculateBreakTime($checkinStandard, $checkoutStandard);
            }

            $data = [
                "timeIn" => '',
                "timeOut" => '',
                "timeWork" => 0,
                "timeOT" => $item->register_ot_has_salary,
                'holiday' => 0,
                'break_time' => $breakTime,
                'ct' => '',
                'p' => '',
            ];

            if ($isHoliday) {
                $data['holiday'] = 1;
            }

            if (!$isWeekendOrHoliday) {
                $hasSupp = $item->has_supplement;
                $regSupp = (float)$item->register_supplement_number;
                $hasLeaveday = $item->has_leave_day;
                $regLeave = (float)$item->register_leave_has_salary;

                $check = true;
                if ($hasLeaveday == 1) {
                    $check = false;
                }

                if ($check) {
                    $timeInOut = $this->getTimeInOut($item);
                    $timeInWork = Timekeeping::getTimeInOutWork($timeInOut, $timeWork);

                    $data['timeIn'] = $timeInOut["timeIn"];
                    $data['timeOut'] = $timeInOut["timeOut"];
                    if ($hasSupp == 1) {
                        if ($data['timeIn'] == '' || $data['timeIn'] < $timeMorIn) {
                            $data['timeIn'] = $timeMorIn;
                        }
                        if ($data['timeOut'] == '' || $data['timeOut'] < $timeAfterOut) {
                            $data['timeOut'] = $timeAfterOut;
                        }

                        $data['timeWork'] = 8.0 - (float)$regLeave * 8;
                        $check = false;
                    }
                }

                if ($check && (!empty($timeInWork['timeIn']) && !empty($timeInWork['timeOut']))) {
                    $data = Timekeeping::calculateTimeWokingTwo($data, $item, $timeInWork, $timeWork);
                } elseif ($hasSupp && $check && $hasSupp != $hasLeaveday) {
                    $data['timeWork'] = $regSupp * 8 + (float)$data['timeWork'];
                    if ($hasSupp == ManageTimeConst::HAS_BUSINESS_TRIP_MORNING) {
                        $data['timeIn'] = $timeMorIn;
                        $data['timeOut'] = $timeMorOut;
                        $timeInWork['timeIn'] = $timeMorIn;
                        $timeInWork['timeOut'] = $timeMorOut;
                    } else {
                        $data['timeIn'] = $timeAfterIn;
                        $data['timeOut'] = $timeAfterOut;
                        $timeInWork['timeIn'] = $timeAfterIn;
                        $timeInWork['timeOut'] = $timeAfterOut;
                    }
                } else {
                    // do not some thing
                }

                if ($check && $hasLeaveday && $hasSupp
                && (($hasLeaveday == ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF && $hasSupp == ManageTimeConst::HAS_SUPPLEMENT_AFTERNOON)
                    || ($hasLeaveday == ManageTimeConst:: HAS_LEAVE_DAY_MORNING_HALF && $hasSupp == ManageTimeConst::HAS_SUPPLEMENT_MORNING)
                    || ($regLeave > 0.5 && $regLeave < 1))) {
                    $leaves = Timekeeping::scheckLeaveDay($date, $regLeaveDay, $timeWork, $item->timekeeping_table_id, $empId);
                    $timeLeave = Timekeeping::timeWorkSuppLeave($leaves[$empId], $timeInWork['timeIn'], $timeInWork['timeOut'], $timeWork);
                    $data['timeWork'] = ($timeLeave['timeLeave'] / 60) + (float)$data['timeWork'];
                }

                if (strpos($item->sign_fines, 'CT') !== false) {
                    if(strpos($item->sign_fines, 'P') === false && strpos($item->sign_fines, 'KL') === false) {
                        $data['timeIn'] = $checkinStandard;
                        $data['timeOut'] = $checkoutStandard;
                        $data['timeWork'] = TimesheetHelper::instance()->calculateWorkingHour($checkinStandard, $checkoutStandard);
                    }
                    else {
                        $data['timeIn'] = '';
                        $data['timeOut'] = '';
                        $data['timeWork'] = '';
                    }
                }
            }

            //==== update api if có công tác
            if (strpos($item->sign_fines, 'CT') !== false) {
                $data['ct'] = 1;
            }

            if (strpos($item->sign_fines, 'P/2') !== false || strpos($item->sign_fines, 'KL/2') !== false) {
                $data['p'] = 0.5;
            } elseif (strpos($item->sign_fines, 'P:') !== false || strpos($item->sign_fines, 'KL:') !== false) {
                $data['p'] = 0.25;
            } elseif (strpos($item->sign_fines, 'P') !== false || strpos($item->sign_fines, 'KL') !== false) {
                $data['p'] = 1 ;
            }

            if ($data['timeWork'] == 0 && $data['timeOT'] == 0) {
                $data['timeWork'] = '';
                $data['timeOT'] = '';
            }

            if ($timeWork['afternoonInSetting']->format('H:m') > $data['timeOut']) {
                $data['break_time'] = '';
            }

            if (isset($results[$date])) {
                if (empty($results[$date]['timeWork']) && !empty($data['timeWork'])) {
                    $results[$date] = $data;
                }
            } else {
                $results[$date] = $data;
            }
        }

        return $results;
    }

    /**
     * get time In Out working of employee
     *
     * @param $item
     * @return array
     */
    public function getTimeInOut($item)
    {
        if (!empty($item->start_time_morning_shift)) {
            $timeIn = $item->start_time_morning_shift;
        } elseif (!empty($item->end_time_morning_shift)) {
            $timeIn = $item->end_time_morning_shift;
        } elseif (!empty($item->start_time_afternoon_shift)) {
            $timeIn = $item->start_time_afternoon_shift;
        } else {
            $timeIn = '';
        }

        if (!empty($item->end_time_afternoon_shift)) {
            $timeOut = $item->end_time_afternoon_shift;
        } else {
            $timeOut = $item->end_time_morning_shift;
        }
        return [
            'timeIn' => $timeIn,
            'timeOut' => $timeOut,
        ];
    }
    // ================ end chấm công ================


    //========= start export time in out ==================
    /**
     * run crontab export time in out of employee with date
     * @param date format y
     *
     * @return void
     */
    public function cronExportTimeInOut($date = null)
    {
        Log::useFiles($this->getLogFile());
        try {
            Log::info('Start run crontab export time in out');
            $data = $this->getDataTimeInOut($date);
            if ($data) {
                $this->exportTimeInOut($data);
            }
            Log::info('End run crontab export time in out');
            return;
        } catch (Exception $ex) {
            Log::info($ex);
            Log::info('End run crontab export time in out');
        }
    }
    
    /**
     * get time in out by table manage_time_timekeepings
     * áp dụng cho chi nhánh hà nôi
     * 
     * @param  date|null $date ('d/m/Y')
     * @return array
     */
    public function getDataTimeInOut($date = null)
    {
        $arrBranch = [
            Team::CODE_PREFIX_HN,
            Team::CODE_PREFIX_DN,
        ];

        try {
            $objViewTK = new Timekeeping();
            if (!$date) {
                $cbDate = Carbon::now()->subDay();
            } else {
                $cbDate = Carbon::parse($date);
            }
            $listTk = $objViewTK->getTimeInOutByBranch($arrBranch, $cbDate->toDateString());

            if (!$listTk) {
                return;
            }
            $data = [];
            foreach ($listTk as $item) {
                $account = str_replace('@rikkeisoft.com', '', $item->email);
                $value =  $item->timekeeping_table_id . ',' . $account  . ',' . $item->employee_id . ',' . $cbDate->format('d/m/Y') . ',';
                $valSang = $value . 'Sáng,' . $item->start_time_morning_shift_real . ',' . $item->end_time_morning_shift . ',,';
                $valChieu = $value . 'Chiều,' . $item->start_time_afternoon_shift . ',' . $item->end_time_afternoon_shift . ',,';
            
                $dataValue = [$valSang,$valChieu];
                if (isset($data[$item->timekeeping_table_id])) {
                    $data[$item->timekeeping_table_id] = array_merge($data[$item->timekeeping_table_id], $dataValue);
                } else {
                    $data[$item->timekeeping_table_id] = $dataValue;
                }
            }
            return $data;
        } catch (Exception $ex) {
            Log::info($ex);
        }
    }

    /**
     * api get time in out of employee
     * 
     * @param date $date
     */
    public function getApiTimeInOutEmployee($date)
    {
        $client = new Client();
        $token =  'Bearer uyc9MPAhqxnpNlcyPrgqngypq3qDwBl9AkavDKO7FHjMpwE5EC1rdNrpscouwJKA';
        $url = 'https://test.rikkei.vn/api/timekeeping/get-time-in-out';
        $options['headers'] = [
            'Content-Type' => 'application/json',
            'Authorization' => $token
        ];
        $options['json'] = [
            'date' => $date,
        ];

        $response = $client->get($url, $options);
        $response = $response->getBody()->getContents();
        return json_decode($response, true);
    }


    /**
     * get time in out of employee
     *
     * @param  string $fileName
     * @return array
     */
    public function getTimeInOutEmployee($fileName)
    {
        $dataNull =  [
            'success' => 1,
            'data' => [],
        ];
        $files = Storage::files(self::TIME_IN_OUT_EMPLOYEE);

        if (!$files) {
            return $dataNull;
        }
        try {
            foreach ($files as $file) {
                $str = self::TIME_IN_OUT_EMPLOYEE . '/' . $fileName . '.csv';
                if ($str == $file) {
                    return $this->handlingFileTimeInOut($file);
                }
            }
            return $dataNull;
        } catch (Exception $ex) {
            Log::info($ex);
            return [
                'success' => 1,
                'message' => $ex->getMessage(),
            ];
        }
    }

       
    /**
     * xử lý file thời gian vào ra của nhân viên
     *
     * @param  mixed $file
     * @return void
     */
    public static function handlingFileTimeInOut($file)
    {
        $fileFullPath = storage_path('app/' . $file);
        $dataTime = [];
        $status = 1;
        Excel::selectSheetsByIndex(0)
        ->load($fileFullPath, function ($data) use (&$dataTime, &$status) {
            $data = $data->toArray();
            foreach ($data as $item) {
                if (isset($item['message'])) {
                    $dataTime = $item['message'];
                    $status= 0;
                } else {
                    $dataTime[$item['emp_ids']] = explode('/', $item['time_in_out']);
                }
            }
        });
        if ($status) {
            return [
                'success' => 1,
                'data' => $dataTime,
            ];
        } else {
            return [
                'success' => 0,
                'message' => $dataTime,
            ];
        }
    }

    /**
     * getTimekeeping
     *
     * @param  date $dateStart Y-m-d
     * @param  date $dateEnd Y-m-d
     * @param  array $empIds
     * @param  string $teamCodePrefix
     * @param  boolean $isEmpIds
     * @return array
     */
    public function getTimekeeping($dateStart, $dateEnd, $empIds, $teamCodePrefix, $isEmpIds = false)
    {
        $objTimekeeping = new Timekeeping();
        $arrResult = [];

        $timekeeping = $objTimekeeping->getTimekeepingByDate($dateStart, $dateEnd, $empIds);
        if (!count($timekeeping)) {
           return $arrResult;
        }
        $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
        $arrHolidays[$teamCodePrefix] = CoreConfigData::getHolidayTeam($teamCodePrefix);

        foreach ($timekeeping as $item) {
            $timekeepingSign = ManageTimeCommon::getTimekeepingSign($item, $teamCodePrefix, $compensationDays, $arrHolidays[$teamCodePrefix]);
            $item->sign_fines = trim($timekeepingSign[0]);
            $arrResult[$item->employee_id][$item->timekeeping_date] = $item;
        }
        if (!$isEmpIds) {
            return $arrResult[$empIds[0]];
        }
        return $arrResult;
    }

    /**
     * create file related employee
     *
     * @param  array $dataRecord
     * @return void
     */
    public static function createFileTk($dataRecord)
    {
        if (!count($dataRecord)) {
            return;
        }
        $folder = static::FOLDER_UPLOAD_RELATED_PERSON;
        if (!Storage::exists($folder)) {
            @chmod(storage_path('app/' . $folder), ManageTimeView::ACCESS_FOLDER);
        }

        $fileName = 'related_person';
        $folderPath = storage_path('app/' . $folder);
        $files = Storage::files($folder);
        $dataFile = [];
        try {
            ini_set('memory_limit', '1024M');
            if ($files) {
                foreach ($files as $file) {
                    $excel = Excel::selectSheetsByIndex(0)->load(storage_path('app/' . $file), function ($reader) {
                    })->get()->toArray();
                    foreach($excel as $v) {
                        $v['emp_id'] = (int)$v['emp_id'];
                        $dataFile[$v['emp_id']] = [
                            'emp_id' => $v['emp_id'],
                            'start_date' => $v['start_date'],
                            'end_date' => $v['end_date'],
                        ];
                    }
                }
            }
            // Move file to folder
            Excel::create($fileName, function ($excel) use ($dataRecord, $dataFile) {
                    $excel->sheet('Sheet 1', function ($sheet) use ($dataRecord, $dataFile) {
                        $data = [];
                        $data[0] = ['emp_id', 'start_date', 'end_date'];
                        foreach ($dataRecord as $key => $item) {
                            $start = $item['start_date'];
                            $end = $item['end_date'];
                            if (isset($dataFile[$key])) {
                                $temp = $dataFile[$key];
                                if ($start > $temp['start_date']) $start = $temp['start_date'];
                                if ($end < $temp['end_date']) $end = $temp['end_date'];
                                unset($dataFile[$key]);
                            }
                            $data[] = [
                                $key,
                                $start,
                                $end,
                            ];
                        }
                        if ($dataFile) {
                            foreach ($dataFile as $item) {
                                $data[] = $item;
                            }
                        }
                        $sheet->fromArray($data, null, 'A1', true, false);
                    });
                })->store('csv', storage_path('app/' . $folder));

            @chmod($folderPath . '/'. $fileName . '.csv', ManageTimeView::ACCESS_FOLDER);
        } catch (Exception $ex) {
            Log::info($ex);
        }
        return;
    }

    /**
     * cron related person
     * cap nhat lieu quan khi nhan vien co don duyet
     * 
     * @param  int|null $empId
     * @return void
     */
    public static function cronRelatedPerson($empId = null)
    {
        $files = Storage::files(static::FOLDER_UPLOAD_RELATED_PERSON);
        if (!$files) {
            return true;
        }
        try {
            ini_set('memory_limit', '1024M');
            $dataRelate = [];
            foreach ($files as $file) {
                $path = storage_path('app/' . $file);
                if ($empId) {
                    $arrFile = file($path);
                    foreach ($arrFile as $key => $value) {
                        $temp = explode(",", $value);
                        if ($empId == trim($temp[0], '"')) {
                            $dataRelate = [
                                'empids' => [$empId],
                                'start_date' => trim($temp[1],'"'),
                                'end_date' => trim(preg_replace('/\\n|\\r|\s/', '', $temp[2]), '"')
                            ];
                            unset($arrFile[$key]);
                            break;
                        }
                    }
                    $fp = fopen($path, 'w+');
                    foreach($arrFile as $line)
                        fwrite($fp, $line); 
    
                    fclose($fp);
                } else {
                    $dataFile = Excel::selectSheetsByIndex(0)->load(storage_path('app/' . $file), function ($reader) {
                    })->get()->toArray();
                    
                    foreach ($dataFile as $item) {
                        $dataRelate['empids'][] = (int)$item['emp_id'];
                        if (isset($dataRelate['start_date'])) {
                            if ($dataRelate['start_date'] > $item['start_date'])
                                $dataRelate['start_date'] = $item['start_date'];
                        } else {
                            $dataRelate['start_date'] = $item['start_date'];
                        }
                        
                        if (isset($dataRelate['end_date'])) {
                            if ($dataRelate['end_date'] < $item['end_date'])
                                $dataRelate['end_date'] = $item['end_date'];
                        } else {
                            $dataRelate['end_date'] = $item['end_date'];
                        }
                    }
                    Storage::delete($file);
                }
            }
            if ($dataRelate) {
                $oldBreadcrumb = Breadcrumb::get();
                $branchCodes = EmployeeTeamHistory::select('team_id', 'branch_code')
                ->leftJoin('teams', 'teams.id', '=','employee_team_history.team_id')
                ->whereIn('employee_id', $dataRelate['empids'])
                ->where('is_working', EmployeeTeamHistory::IS_WORKING)
                ->where(function ($query) {
                    $query->WhereDate("end_at", '>=', Carbon::now()->format('Y-m-d'))
                        ->orWhereNull("end_at");
                })
                ->get()->pluck('branch_code')->toArray();

                $timeKeepingTables = TimekeepingTable::select(
                    'manage_time_timekeeping_tables.id',
                    'manage_time_timekeeping_tables.creator_id',
                    'manage_time_timekeeping_tables.timekeeping_table_name',
                    'manage_time_timekeeping_tables.team_id',
                    'manage_time_timekeeping_tables.start_date',
                    'manage_time_timekeeping_tables.end_date',
                    'manage_time_timekeeping_tables.year',
                    'manage_time_timekeeping_tables.month',
                    'manage_time_timekeeping_tables.type'
                )
                ->leftJoin('teams', 'teams.id', '=','manage_time_timekeeping_tables.team_id')
                ->where('lock_up', TimekeepingTable::OPEN_LOCK_UP)
                ->where('start_date', '<=', $dataRelate['end_date'])
                ->where('end_date', '>=', $dataRelate['start_date'])
                ->whereIn('branch_code', $branchCodes)
                ->get();
                if (!count($timeKeepingTables)) {
                    return;
                }
                $dataRelate['start_date'] = Carbon::parse($dataRelate['start_date'])->format('d-m-Y');
                $dataRelate['end_date'] = Carbon::parse($dataRelate['end_date'])->format('d-m-Y');
                foreach ($timeKeepingTables as $item) {
                    $dataRelate['timekeeping_table_id'] = $item->id;
                    $dataRelateTemp = array_merge(array_splice($dataRelate, -1), $dataRelate);
                    $dataTmp = array_chunk($dataRelateTemp['empids'], Timekeeping::CHUNK_NUMBER);
                    foreach ($dataTmp as $empIdsTk) {
                        static::createFileRelateTk($dataRelateTemp, $empIdsTk);
                    }
                }
                Breadcrumb::reset();
                foreach($oldBreadcrumb as $bc) {
                    Breadcrumb::add($bc['text'], $bc['url'], $bc['pre_text']);
                }
            }
        } catch (Exception $ex) {
            Log::info($ex);
        }
    }

    /**
     * Tạo file để từ đó lấy thông tin cập nhật dữ liệu liên quan
     * Tạo từ 2 nơi:
     * - Ấn nút cập nhật ở bảng công
     * - Từ file trong thư mục `timekeeping_upload_related_person` là nơi chứa file đc tạo khi có đơn đc duyệt/ko duyệt
     *
     * @param array $dataRequest    Mảng data truyền vào gồm các thông tin về timekeeping_id, employee_id, start date, end date
     * @param array $empIdsTk   Mảng các employee_id sẽ đc tạo trong file
     *
     * @return void
     */
    public static function createFileRelateTk($dataRequest, $empIdsTk)
    {
        $fileName = ViewTimeKeeping::setTableName($dataRequest['timekeeping_table_id']);
        $folderPath = storage_path('app/' . ManageTimeView::FOLDER_UPLOAD_RELATED);
        Excel::create($fileName, function ($excel) use ($dataRequest, $empIdsTk) {
                $excel->sheet('Sheet 1', function ($sheet) use ($dataRequest, $empIdsTk) {
                    $data = [];
                    $data[0] = ['timekeeping_table_id', 'emp_ids', 'start_date', 'end_date'];
                    $request = $dataRequest;
                    $request['empids'] = $empIdsTk;
                    $data[1] = array_values($request);
                    if (isset($data[1][1])) {
                        $data[1][1] = implode('_', $data[1][1]);
                    }
                    $sheet->fromArray($data, null, 'A1', true, false);
                });
            })->store('csv', storage_path('app/' . ManageTimeView::FOLDER_UPLOAD_RELATED));

        @chmod($folderPath . '/'. $fileName . '.csv', ManageTimeView::ACCESS_FOLDER);
    }

    /**
     * run create file timekeeping csv relate
     *
     * @return void
     */
    public function cronTimekeepingRelated()
    {
        try {
            Log::info('Start create file timekeeping csv relate');
            $data = $this->getDataTimeInOut();
            $this->createFileCSV($data, ManageTimeView::FOLDER_UPLOAD_RELATED);
            Log::info('End create file timekeeping csv relate');
            return;
        } catch (Exception $ex) {
            Log::info($ex);
            Log::info('End create file timekeeping csv relate');
        }
    }
        
    /**
     * tạo file csv theo mẫu sẵn
     *
     * @param  array $data
     * @param  string $folder
     * @return void
     */    
    public function createFileCSV($data, $folder)
    {
        if ($data) {
            $folderPath = storage_path('app/' . $folder);
            $cbDate = Carbon::now()->subDay();
            foreach ($data as $tableId => $arr) {
                $empIds = Timekeeping::getEmployeesIdOfTimekeeping($tableId);
                $dataTmp = array_chunk($empIds, Timekeeping::CHUNK_NUMBER);
                foreach ($dataTmp as $empIdsTk) {
                    $columns = '"timekeeping_table_id","emp_ids","start_date","end_date"' . "\n";
                    $columns .= '"' . $tableId . '","' . implode('_', $empIdsTk) . '","'. $cbDate->format('d-m-Y') . '","' . $cbDate->format('d-m-Y')  . '"';
                    $nameFile = ViewTimeKeeping::setTableName($tableId);
                    $this->createCSV($nameFile, $folder);
                    $csv_handler = fopen($folderPath . '/' . $nameFile . '.csv', 'w');
                    fwrite($csv_handler, $columns);
                    fclose($csv_handler);
                }
            }
        }
        return;
    }
    
    public static function setTableName($tableId)
    {
        return $tableId . '_' . static::generateRandomString() . time();
    }

    public static function generateRandomString($length = 20)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    //========= start export time in out ==================

    /**
     * run cron update related timekeeping when update working time
     *
     * @return void
     */
    public static function wKTUpdateRelated()
    {
        $files = Storage::files(static::FOLDER_WKT_UPLOAD_RELATED);
        if (!$files) {
            return true;
        }
        Log::info('====== start update timekeeping when have working time ======');
        try {
            ini_set('memory_limit', '1024M');
            foreach ($files as $file) {
                TimekeepingController::setDataRelatedCron($file, TimekeepingTable::OPEN_LOCK_UP);
                Storage::delete($file);
            }
            Log::info('====== end update timekeeping when have working time ======');
        } catch (Exception $ex) {
            Storage::delete($file);
            Log::info($ex);
            Log::info('====== end update error timekeeping when have working time ======');
        }
    }

    /**
     * export file timeekeeing for datase
     * @param array $dataEmp [empid =>[[startDate, endDate]]]
     * @return void
     */
    public function exportTimeInOutByListEmp($dataEmp)
    {
        try {
            if (!count($dataEmp)) {
                return;
            }
            foreach($dataEmp as $key => $arrDate) {
                if (!count($arrDate)) {
                    unset($dataEmp[$key]);
                }
            }
            $dataWKT = $this->getDataTimeKeepingByListEmp($dataEmp);
            if (!count($dataWKT) || !count($dataWKT['data_in_out'])) {
                return;
            }
            $dataInOut = $dataWKT['data_in_out'];
            $dataRelated = $dataWKT['data_relate'];
            $this->exportTimeInOut($dataInOut);
            $this->createFileWKTRelated($dataRelated);
            return;
        } catch (Exception $ex) {
            Log::info($ex);
        }
    }

    /**
     * get time in out of table manage_time_timekeepings
     *
     * @param  array $dataEmp [empid =>[[startDate, endDate]]]
     * @return array
     */
    public function getDataTimeKeepingByListEmp($dataEmp)
    {
        if (!$dataEmp) {
            return [];
        }
        $objViewTK = new Timekeeping();
        $listTkEmp = $objViewTK->getDataTimeInOutByListEmp($dataEmp);
        if (!count($listTkEmp)) {
            return [];
        }
        $dataInOut = [];
        $dataRelated = [];
        foreach ($listTkEmp as $item) {
            $account = str_replace('@rikkeisoft.com', '', $item->email);
            $value =  $item->timekeeping_table_id . ',' . $account  . ',' . $item->employee_id . ',' . $item->timekeeping_date_format . ',';
            $valSang = $value . 'Sáng,' . $item->start_time_morning_shift_real . ',' . $item->end_time_morning_shift . ',,';
            $valChieu = $value . 'Chiều,' . $item->start_time_afternoon_shift . ',' . $item->end_time_afternoon_shift . ',,';
        
            $dataValue = [$valSang,$valChieu];
            if (isset($dataInOut[$item->timekeeping_table_id])) {
                $dataInOut[$item->timekeeping_table_id] = array_merge($dataInOut[$item->timekeeping_table_id], $dataValue);
                if ($dataRelated[$item->timekeeping_table_id]['start_date'] > $item->timekeeping_date)
                    $dataRelated[$item->timekeeping_table_id]['start_date'] = $item->timekeeping_date;
                if ($dataRelated[$item->timekeeping_table_id]['end_date'] < $item->timekeeping_date)
                    $dataRelated[$item->timekeeping_table_id]['end_date'] = $item->timekeeping_date;
            } else {
                $dataInOut[$item->timekeeping_table_id] = $dataValue;
                $dataRelated[$item->timekeeping_table_id]['start_date'] = $item->timekeeping_date;
                $dataRelated[$item->timekeeping_table_id]['end_date'] = $item->timekeeping_date;
                $dataRelated[$item->timekeeping_table_id]['emp_ids'][] = $item->employee_id;
            }
            if (!in_array($item->employee_id, $dataRelated[$item->timekeeping_table_id]['emp_ids'])) {
                $dataRelated[$item->timekeeping_table_id]['emp_ids'][] = $item->employee_id;
            }
        }
        return [
            'data_in_out' => $dataInOut,
            'data_relate' => $dataRelated,
        ];
    }

     /**
     * create file csv time in out of list employee
     * storage/app/timekeeping_upload
     * 
     * @param  array $data
     * @return void
     */
    public function exportTimeInOut($data)
    {
        if (!$data) {
            return;
        }
        $folderPath = storage_path('app/' . ManageTimeView::FOLDER_UPLOAD);
        $dataFileOld = $this->getDataFileWKT();
        foreach($data as $tableId => $arr) {
            $columns = "ID chấm công,Mã N.Viên,Họ tên,Ngày,Ca làm việc,Vào lúc,Ra lúc,đi muộn,Về sớm \n";
            foreach($arr as $val) {
                $columns .= $val . "\n";
            }
            if (isset($dataFileOld[$tableId])) {
                foreach($dataFileOld[$tableId] as $old) {
                    $columns .= $old . "\n";
                }
            }
            $this->createCSV($tableId, ManageTimeView::FOLDER_UPLOAD);
            $csv_handler = fopen($folderPath . '/' . $tableId . '.csv','w');
            fwrite($csv_handler, $columns);
            fclose($csv_handler);
        }
        return;
    }

    /**
     * run create file timekeeping csv relate salary rate
     *
     * @return void
     */
    public function cronTimekeepingRelatedSalaryRate()
    {
        try {
            Log::info('Start create file timekeeping csv salary rate');
            $data = $this->getDataTimeInOut();
            $this->createFileCSV($data, ManageTimeView::FOLDER_SALARY_RATE_RELATED);
            Log::info('End create file timekeeping csv salary rate');
            return;
        } catch (Exception $ex) {
            Log::info($ex);
            Log::info('End create file timekeeping csv salary rate');
        }
    }
    
    
    /**
     * run create file timekeeping csv aggregate salary rate
     *
     * @return void
     */
    public function cronTimekeepingAggregateSalaryRate()
    {
        try {
            Log::info('Start create file timekeeping csv aggregate salary rate');
            $data = $this->getDataTimeInOut();
            if ($data) {
                $folderPath = storage_path('app/' . ManageTimeView::FOLDER_SALARY_RATE_AGGREGATE);
                foreach ($data as $tableId => $arr) {
                    $columns = '"timekeeping_table_id","emp_ids","start_date","end_date"' . "\n";
                    $columns .= '"' . $tableId . '",""';
                    $this->createCSV($tableId, ManageTimeView::FOLDER_SALARY_RATE_AGGREGATE);
                    $csv_handler = fopen($folderPath . '/' . $tableId . '.csv', 'w');
                    fwrite($csv_handler, $columns);
                    fclose($csv_handler);
                }
            }
            Log::info('End create file timekeeping csv aggregate salary rate');
            return;
        } catch (Exception $ex) {
            Log::info($ex);
            Log::info('End create file timekeeping csv aggregate salary rate');
        }
    }

     /**
     * get data in folder timekeeping_upload
     *
     * @return array
     */
    public function getDataFileWKT()
    {
        $files = Storage::files(ManageTimeView::FOLDER_UPLOAD);
        $dataFileOld = [];
        if ($files) {
            foreach ($files as $file) {
                $resultMore = '';
                $result = preg_match('/\d+/', $file, $resultMore);
                if (!$result || !$resultMore)
                    continue;
                $idTable = $resultMore[0];
                $excel = Excel::selectSheetsByIndex(0)->load(storage_path('app/' . $file), function ($reader) {
                })->get()->toArray();
                if (!count($excel))
                    continue;
                foreach ($excel as $item) {
                    $account = trim($item['ma_nvien']);
                    $strInfo = (int)$item['id_cham_cong'] . ',' . $account  . ',' . $item['ho_ten'] . ',' . $item['ngay'] . ',';
                    $strTime = $item['vao_luc'] . ',' . $item['ra_luc'] . ',,';
                    if ($item['ca_lam_viec'] == 'Sáng') {
                        $strTime = $strInfo . 'Sáng,' . $strTime;
                    } else {
                        $strTime = $strInfo . 'Chiều,' . $strTime;
                    }
                    $dataFileOld[$idTable][] = $strTime;
                }
            }
        }
        return $dataFileOld;
    }
            
    /**
     * create file csv, use run cron update related
     *
     * @param  array $dataRelated
     * @return void
     */
    public function createFileWKTRelated($dataRelated)
    {
        $files = Storage::files(static::FOLDER_WKT_UPLOAD_RELATED);
        $dataOld = [];
        if ($files) {
            foreach ($files as $file) {
                $excel = Excel::selectSheetsByIndex(0)->load(storage_path('app/' . $file), function ($reader) {
                })->get()->toArray();
                if ($excel) {
                    $excel = $excel[0];
                    $dataOld[$excel['timekeeping_table_id']] = [
                        'emp_ids' => $excel['emp_ids'],
                        'start_date' => $excel['start_date'],
                        'end_date' => $excel['end_date'],
                    ];
                }
            }
        }
        $folderPath = storage_path('app/' . static::FOLDER_WKT_UPLOAD_RELATED);
        foreach($dataRelated as $tableId => $data) {
            $sttEmpId = implode('_', $data['emp_ids']);
            if (isset($dataOld[$tableId])) {
                $old = $dataOld[$tableId];
                $sttEmpId .= '_' . $old['emp_ids'];
                if ($old['start_date'] < $data['start_date'])
                    $data['start_date'] = $old['start_date'];
                if ($old['end_date'] > $data['end_date'])
                    $data['end_date'] = $old['end_date'];
            }
            $rowFile = $tableId . ',';
            $rowFile .= $sttEmpId . ',';
            $rowFile .= $data['start_date'] . ','. $data['end_date'];
            $columns = "timekeeping_table_id,emp_ids,start_date,end_date \n";
            $columns .= $rowFile;
            $this->createCSV($tableId, static::FOLDER_WKT_UPLOAD_RELATED);
            $csv_handler = fopen($folderPath . '/' . $tableId . '.csv','w');
            fwrite($csv_handler, $columns);
            fclose($csv_handler);
        }
        return;
    }

    /**
     * createFileCsv
     *
     * @param  string $nameFile
     * @param  string $folder
     * @return void
     */
    public function createCSV($nameFile, $folder)
    {
        if (!Storage::exists($folder)) {
            Storage::makeDirectory($folder, ManageTimeView::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . $folder), ManageTimeView::ACCESS_FOLDER);

        $fileName = $nameFile . '.csv';
        $folderPath = storage_path('app/' . $folder);
        if (Storage::exists($folder . '/' . $fileName)) {
            return false;
        }
        @chmod($folderPath . '/' . $fileName, ManageTimeView::ACCESS_FOLDER);
        Excel::create($nameFile, function($excel) {
            $excel->sheet('Sheetname', function($sheet) {
            });
        })->store('csv', $folderPath);
        return true;
    }
    //========= end export time in out ====================
}
