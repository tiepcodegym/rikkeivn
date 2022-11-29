<?php

namespace Rikkei\ManageTime\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\CoreModel;
use Rikkei\ManageTime\View\ManageLeaveDay;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\View;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\Ot\Model\OtEmployee;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Lang;

class WorkingTimeDetail extends CoreModel
{
    use SoftDeletes;
    
    protected $fillable = [
        'working_time_id',
        'employee_id',
        'team_id',
        'from_date',
        'to_date',
        'start_time1',
        'end_time1',
        'start_time2',
        'end_time2',
        'half_morning',
        'half_afternoon',
        'key_working_time',
        'key_working_time_half',
    ];

    /*
     * get employee that belongs to
     */
    public function employee()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'employee_id');
    }

    /*
     * get approver that belongs to
     */
    public function approver()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'approver_id');
    }

    /*
     * get team that belongs to
     */
    public function team()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Team', 'team_id');
    }


    /**
     * Get the working that owns the details.
     */
    public function workingTime()
    {
        return $this->belongsTo(WorkingTimeRegister::class, 'working_time_id');
    }
    
    /*
     * get from date attribute
     */
    public function getFromDate()
    {
        return Carbon::parse($this->from_date)->format('d-m-Y');
    }

    /*
     * get to date attribute
     */
    public function getToDate()
    {
        return Carbon::parse($this->to_date)->format('d-m-Y');
    }

    /**
     * refresh list employee tags of working time register
     *
     * @param  array $dataWTDetails
     * @param  integer|null $oldWorkingTimeId
     * @return void
     */
    public function insertOrUpdate($dataWTDetails, $oldWorkingTimeId)
    {
        if ($oldWorkingTimeId) {
            static::where('working_time_id', $oldWorkingTimeId)->forceDelete();
        }
        static::insert($dataWTDetails);
    }
        
    /**
     * check employees exists
     *
     * @param  array $data [start_date, end_date: string]
     * @param array|integer|null $registerIds
     * @return array
     */
    public function checkExists($data, $registerIds = null)
    {
        $tblWTRegister = WorkingTimeRegister::getTableName();
        $label = trans('manage_time::view.Change working time');
        $url = route('manage_time::wktime.detail', ['id' => ':id']);
        $collection = static::select(
            'working_time_details.employee_id',
            'employees.employee_code',
            'employees.name as employee_name',
            'working_time_details.working_time_id AS id',
            'working_time_details.from_date AS date_start',
            'working_time_details.to_date AS date_end',
            DB::raw("'{$label}' AS type"),
            DB::raw("'{$url}' AS url")
        )
        ->join('employees', 'employees.id', '=', 'working_time_details.employee_id')
        ->join("{$tblWTRegister} as wtr", 'wtr.id', '=', 'working_time_details.working_time_id')
        ->whereNull('wtr.deleted_at')
        ->whereIn("wtr.status", [WorkingTimeRegister::STATUS_UNAPPROVE, WorkingTimeRegister::STATUS_APPROVE])
        ->where(function($query) use ($data) {
            foreach ($data as $item) {
                $query->orWhere(function ($q) use ($item) {
                    $q->where('working_time_details.employee_id', $item['employee_id'])
                        ->whereDate('working_time_details.from_date', '<=', $item['end_date'])
                        ->whereDate('working_time_details.to_date', '>=', $item['start_date']);
                });
            }
        });
        if ($registerIds) {
            $collection->whereNotIn('wtr.id', (array)$registerIds);
        }
        return $collection->get()->toArray();
    }

    /**
     * check employees register working time overlap list register leave day, supplement and OT
     *
     * @param array $dataWTDetail [start_date, end_date, employee_id, register_id, periods]
     * @return array
     */
    public function checkExistOtherRegistrations($dataWTDetail)
    {
        $existOT = $this->checkExistOtherRegistration(View::STR_OT, $dataWTDetail);
        return [
            'ot' => $existOT,
            'is_exist' => count($existOT),
        ];
    }

    /**
     * check date is between period start and period end
     * {format string: Y-m-d}
     *
     * @param string $date
     * @param array $period (has key 'start' and 'end')
     * @return bool
     */
    public function isDateBetween($date, $period)
    {
        return $period[0] <= $date && $date <= $period[1];
    }

    /**
     * add key volatility: is up (change according to working time register) or down (revert to working time default)
     * @param array $dataWTDetail
     * @param integer $wtStatus
     * @return array
     */
    public function mergeVolatilityIntoDataWTDetail($dataWTDetail, $wtStatus)
    {
        foreach ($dataWTDetail as $i => $wtDetail) {
            $volatility = ['up' => [], 'down' => []];
            if (!isset($wtDetail['key_working_time'])) {
                $dataWTDetail[$i]['key_working_time'] = $wtDetail['key_working_time'] = $wtDetail['old_key_working_time'];
            }
            if (!isset($wtDetail['key_working_time_half'])) {
                $dataWTDetail[$i]['key_working_time_half'] = $wtDetail['key_working_time_half'] = $wtDetail['old_key_working_time_half'];
            }

            if ($wtDetail['action'] === 'delete') {
                $wtDetail['start_date'] = $wtDetail['old_start_date'];
                $wtDetail['end_date'] = $wtDetail['old_end_date'];
                $dataWTDetail[$i] = $wtDetail;
            }
            if ($wtDetail['action'] === 'delete' || $wtStatus === WorkingTimeRegister::STATUS_REJECT) {
                $volatility['down'][] = [$wtDetail['start_date'], $wtDetail['end_date']];
                $dataWTDetail[$i]['volatility'] = $volatility;
                continue;
            }
            $volatility['up'][] = [$wtDetail['start_date'], $wtDetail['end_date']];
            foreach ($wtDetail['periods'] as $period) {
                if ($period[0] > $wtDetail['end_date'] || $period[1] < $wtDetail['start_date']) {
                    $volatility['down'][] = $period;
                }
            }
            $dataWTDetail[$i]['volatility'] = $volatility;
        }
        return $dataWTDetail;
    }

    /**
     * check allow employee OT immediately (match setting project OT 18h)
     * @param integer $empId
     * @param string $date {format: 'Y-m-d'}
     * @param array $projectOTImmediately
     * @return bool
     */
    private function checkAllowEmployeeOTImmediately($empId, $date, $projectOTImmediately)
    {
        $empProjects = OtRegister::getProjectsbyEmployee($empId, false);
        foreach ($empProjects as $empProject) {
            if (isset($projectOTImmediately[$empProject->project_id])
                && $this->isDateBetween($date, [$empProject->start_at, $empProject->end_at])) {
                return true;
            }
        }
        return false;
    }

    /**
     * get new period for other registration
     * @param array $volatility
     * @param array $register (leave day or business trip)
     * @param array $references
     * @return array
     */
    private function getNewPeriod($volatility, $register, $references)
    {
        $wtFrame = $references['wtFrame'];
        $wtDefault = $references['wtDefault'];
        $wtHalfDefault = $references['wtHalfDefault'];
        $morningStarts = $references['morningStarts'];
        $morningEnds = $references['morningEnds'];
        $afternoonStarts = $references['afternoonStarts'];
        $afternoonEnds = $references['afternoonEnds'];
        $morningHalf = $references['morningHalf'];
        $afternoonHalf = $references['afternoonHalf'];
        $projectOTImmediately = $references['projectOTImmediately'];

        $startDate = $register['startDate'];
        $endDate = $register['endDate'];
        $startHour = $register['startHour'];
        $endHour = $register['endHour'];

        $newStartTime = "{$register['startDate']} {$register['startHour']}";
        $newEndTime = "{$register['endDate']} {$register['endHour']}";
        /* change according to working time register */
        foreach ($volatility['up'] as $period) {
            if ($this->isDateBetween($startDate, $period)) {
                if (isset($morningStarts[$startHour])) {
                    $newStartTime = "{$startDate} {$wtFrame[0]}";
                } elseif (isset($afternoonStarts[$startHour])) {
                    $newStartTime = "{$startDate} {$wtFrame[2]}";
                } elseif ($register['registrationType'] === View::STR_LEAVE_DAYS) {
                    if (isset($morningHalf[$startHour])) {
                        $newStartTime = "{$startDate} {$references['wtHalfFrame'][0]}";
                    }
                    if (isset($afternoonHalf[$startHour])) {
                        $newStartTime = "{$startDate} {$references['wtHalfFrame'][1]}";
                    }
                }
                if ($register['registrationType'] === View::STR_SUPPLEMENT && $register['is_ot'] == SupplementRegister::IS_OT
                    || $register['registrationType'] === View::STR_OT) {
                    $holidays = ['annual' => $references['annualHolidays'], 'special' => $references['specialHolidays']];
                    if ($this->isWeekendOrHoliday($startDate, $holidays)) {
                        $newStartTime = $register['date_start'];
                    } else {
                        /* start OT = finish working afternoon + (0h (OT immediately, match setting Project OT 18h) or 1h (default)) */
                        $addedHourOT = $this->checkAllowEmployeeOTImmediately($register['employee_id'], $startDate, $projectOTImmediately) ? 0 : 1;
                        $startOT = Carbon::parse("{$startDate} {$wtFrame[3]}")->addHours($addedHourOT)->format('Y-m-d H:i');
                        $newStartTime = max($register['date_start'], $startOT);
                    }
                }
            }
            if ($this->isDateBetween($endDate, $period)) {
                if (isset($morningEnds[$endHour])) {
                    $newEndTime = "{$endDate} {$wtFrame[1]}";
                } elseif (isset($afternoonEnds[$endHour])) {
                    $newEndTime = "{$endDate} {$wtFrame[3]}";
                } elseif ($register['registrationType'] === View::STR_LEAVE_DAYS) {
                    if (isset($morningHalf[$endHour])) {
                        $newEndTime = "{$endDate} {$references['wtHalfFrame'][0]}";
                    }
                    if (isset($afternoonHalf[$endHour])) {
                        $newEndTime = "{$endDate} {$references['wtHalfFrame'][1]}";
                    }
                }
                if ($register['registrationType'] === View::STR_SUPPLEMENT && $register['is_ot'] == SupplementRegister::IS_OT
                    || $register['registrationType'] === View::STR_OT) {
                    $newEndTime = $register['date_end'];
                }
            }
        }
        /* revert to working time default */
        foreach ($volatility['down'] as $period) {
            if ($this->isDateBetween($startDate, $period)) {
                if (isset($morningStarts[$startHour])) {
                    $newStartTime = "{$startDate} {$wtDefault['start_time1']}";
                } elseif (isset($afternoonStarts[$startHour])) {
                    $newStartTime = "{$startDate} {$wtDefault['start_time2']}";
                } elseif ($register['registrationType'] === View::STR_LEAVE_DAYS) {
                    if (isset($morningHalf[$startHour])) {
                        $newStartTime = "{$startDate} {$wtHalfDefault['morning']}";
                    }
                    if (isset($afternoonHalf[$startHour])) {
                        $newStartTime = "{$startDate} {$wtHalfDefault['afternoon']}";
                    }
                }
                if ($register['registrationType'] === View::STR_SUPPLEMENT && $register['is_ot'] == SupplementRegister::IS_OT
                    || $register['registrationType'] === View::STR_OT) {
                    $holidays = ['annual' => $references['annualHolidays'], 'special' => $references['specialHolidays']];
                    if ($this->isWeekendOrHoliday($startDate, $holidays)) {
                        $newStartTime = $register['date_start'];
                    } else {
                        /* start OT = finish working afternoon + (0h (OT immediately, match setting Project OT 18h) or 1h (default)) */
                        $addedHourOT = $this->checkAllowEmployeeOTImmediately($register['employee_id'], $startDate, $projectOTImmediately) ? 0 : 1;
                        $startOT = Carbon::parse("{$startDate} {$wtDefault['end_time2']}")->addHours($addedHourOT)->format('Y-m-d H:i');
                        $newStartTime = max($register['date_start'], $startOT);
                    }
                }
            }
            if ($this->isDateBetween($endDate, $period)) {
                if (isset($morningEnds[$endHour])) {
                    $newEndTime = "{$endDate} {$wtDefault['end_time1']}";
                } elseif (isset($afternoonEnds[$endHour])) {
                    $newEndTime = "{$endDate} {$wtDefault['end_time2']}";
                } elseif ($register['registrationType'] === View::STR_LEAVE_DAYS) {
                    if (isset($morningHalf[$endHour])) {
                        $newEndTime = "{$endDate} {$wtHalfDefault['morning']}";
                    }
                    if (isset($afternoonHalf[$endHour])) {
                        $newEndTime = "{$endDate} {$wtHalfDefault['afternoon']}";
                    }
                }
                if ($register['registrationType'] === View::STR_SUPPLEMENT && $register['is_ot'] == SupplementRegister::IS_OT
                    || $register['registrationType'] === View::STR_OT) {
                    $newEndTime = $register['date_end'];
                }
            }
        }
        return [$newStartTime, $newEndTime];
    }

    /**
     * update time of other registrations follow working time registration
     *
     * @param array $dataWTDetail
     * @param integer $wtStatus - status of working time register
     * @return array {has status and errors if exist}
     */
    public function syncAllOtherRegistrations($dataWTDetail, $wtStatus)
    {
        $otherExist = [
            'business_trip' => $this->checkExistOtherRegistration(View::STR_MISSION, $dataWTDetail),
            'leave_day' => $this->checkExistOtherRegistration(View::STR_LEAVE_DAYS, $dataWTDetail),
            'supplement' => $this->checkExistOtherRegistration(View::STR_SUPPLEMENT, $dataWTDetail),
            'ot' => $this->checkExistOtherRegistration(View::STR_OT, $dataWTDetail),
        ];
        if (empty($otherExist['business_trip']) && empty($otherExist['leave_day']) && empty($otherExist['supplement']) && empty($otherExist['ot'])) {
            return ['status' => true];
        }

        /* merge volatility, data working time frame and key by employee_id for search */
        $dataWTDetail = $this->mergeVolatilityIntoDataWTDetail($dataWTDetail, $wtStatus);
        $viewWT = new \Rikkei\ManageTime\View\WorkingTime();
        $aryWTFrame = $viewWT->getWorkingTimeFrame();
        $aryWTHFrame = $viewWT->getWorkingTimeHalfFrame();
        $tempWTDetail = [];
        foreach ($dataWTDetail as $wtDetail) {
            $wtDetail['wtFrame'] = $aryWTFrame[$wtDetail['key_working_time']];
            $wtDetail['wtHalfFrame'] = $aryWTHFrame[$wtDetail['key_working_time_half']];
            $tempWTDetail[$wtDetail['employee_id']] = $wtDetail;
        }
        /* list time start and finish in morning and afternoon */
        $morningStarts = $morningEnds = $afternoonStarts = $afternoonEnds = [];
        foreach ($aryWTFrame as $wtFrame) {
            $morningStarts[$wtFrame[0]] = $wtFrame[0];
            $morningEnds[$wtFrame[1]] = $wtFrame[1];
            $afternoonStarts[$wtFrame[2]] = $wtFrame[2];
            $afternoonEnds[$wtFrame[3]] = $wtFrame[3];
        }
        /* list time half in morning and afternoon */
        $morningHalf = $afternoonHalf = [];
        foreach ($aryWTHFrame as $wthFrame) {
            $morningHalf[$wthFrame[0]] = $wthFrame[0];
            $afternoonHalf[$wthFrame[1]] = $wthFrame[1];
        }

        $cacheTeamCode = $cacheWTDefault = $cacheEmployee = $dataEmployee = [];
        foreach ($otherExist as $aryRegister) {
            foreach ($aryRegister as $register) {
                $empId = $register['employee_id'];
                if (isset($dataEmployee[$empId])) {
                    continue;
                }
                !isset($cacheEmployee[$empId]) && $cacheEmployee[$empId] = Employee::find($empId);
                !isset($cacheTeamCode[$empId]) && $cacheTeamCode[$empId] = Team::getOnlyOneTeamCodePrefixChange($empId);
                $teamCode = $cacheTeamCode[$empId];
                !isset($cacheWTDefault[$teamCode]) && $cacheWTDefault[$teamCode] = ManageTimeCommon::defaultWorkingTime($teamCode);
                $dataEmployee[$empId] = [
                    'info' => $cacheEmployee[$empId],
                    'teamCode' => $teamCode,
                    'wtDefault' => $cacheWTDefault[$teamCode],
                ];
            }
        }

        $dataUpdate = ['leave_day' => [], 'supplement' => [], 'business_trip' => [], 'ot' => []];
        /* leave days: convert if it does not change the number of days off */
        $errorLeaveDay = [];
        $projectOTImmediately = unserialize(CoreConfigData::getValueDb('project.ot.18h'));
        $references = [
            'morningStarts' => $morningStarts,
            'morningEnds' => $morningEnds,
            'afternoonStarts' => $afternoonStarts,
            'afternoonEnds' => $afternoonEnds,
            'morningHalf' => $morningHalf,
            'afternoonHalf' => $afternoonHalf,
            'wtHalfDefault' => ['morning' => '10:00', 'afternoon' => '15:30'],
            'annualHolidays' => CoreConfigData::getAnnualHolidays(2),
            'projectOTImmediately' => array_combine($projectOTImmediately, $projectOTImmediately),
        ];
        foreach ($otherExist['leave_day'] as $register) {
            $employee = $dataEmployee[$empId = $register['employee_id']];
            $references['wtFrame'] = $tempWTDetail[$empId]['wtFrame'];
            $references['wtHalfFrame'] = $tempWTDetail[$empId]['wtHalfFrame'];
            $references['wtDefault'] = $employee['wtDefault'];

            list ($register['newStart'], $register['newEnd']) = $this->getNewPeriod($tempWTDetail[$empId]['volatility'], $register, $references);
            $register['newNumDays'] = ManageLeaveDay::getTimeLeaveDay($register['newStart'], $register['newEnd'], $employee['info'], $employee['teamCode']);
            if ($register['status'] == LeaveDayRegister::STATUS_APPROVED && $register['newNumDays'] != $register['num_days']) {
                $newStart = Carbon::parse($register['newStart'])->format('d-m-Y H:i');
                $newEnd = Carbon::parse($register['newEnd'])->format('d-m-Y H:i');
                $register['note'] = [
                    'old_text' => trans('manage_time::message.Number of approved leave days: :num', ['num' => $register['num_days']]),
                    'new_text' => trans('manage_time::message.Number of leave days after change working time: :num', ['num' => $register['newNumDays']])
                        . "<br>({$newStart} -> {$newEnd})",
                ];
                $errorLeaveDay[] = $register;
            }
            $dataUpdate['leave_day'][] = $register;
        }

        /* supplement registration */
        $errorSupplement = [];
        $cacheSpecialHolidays = []; // only use for calculate num days supplement choose OT
        foreach ($otherExist['supplement'] as $register) {
            $employee = $dataEmployee[$empId = $register['employee_id']];
            if ($register['is_ot'] == SupplementRegister::IS_OT) {
                $teamCode = $employee['teamCode'];
                if (!isset($cacheSpecialHolidays[$teamCode])) {
                    $cacheSpecialHolidays[$teamCode] = CoreConfigData::getSpecialHolidays(2, $teamCode);
                }
                $references['specialHolidays'] = $cacheSpecialHolidays[$teamCode];
            }

            $references['wtFrame'] = $tempWTDetail[$empId]['wtFrame'];
            $references['wtHalfFrame'] = $tempWTDetail[$empId]['wtHalfFrame'];
            $references['wtDefault'] = $employee['wtDefault'];

            list ($register['newStart'], $register['newEnd']) = $this->getNewPeriod($tempWTDetail[$empId]['volatility'], $register, $references);
            $startDate = Carbon::parse($register['newStart']);
            $endDate = Carbon::parse($register['newEnd']);
            if ($register['is_ot'] == SupplementRegister::IS_OT) { /* date of start time equal date of end time */
                $workTimeStart = Employee::getTimeWorkEmployeeDate($startDate->format('Y-m-d'), $employee['info']);
                $register['newNumDays'] = ManageTimeView::getDiffTimesRegister($startDate->hour, $endDate->hour, $startDate->minute, $endDate->minute, $workTimeStart);
            } else {
                $register['newNumDays'] = ManageLeaveDay::getTimeLeaveDay($register['newStart'], $register['newEnd'], $employee['info'], $employee['teamCode']);
            }
            if ($register['status'] == SupplementRegister::STATUS_APPROVED && $register['newNumDays'] != $register['num_days']) {
                $register['note'] = [
                    'old_text' => trans('manage_time::message.Number of approved supplement: :num', ['num' => $register['num_days']]),
                    'new_text' => trans('manage_time::message.Number of supplement after change working time: :num', ['num' => $register['newNumDays']])
                        . "<br>({$startDate->format('d-m-Y H:i')} -> {$endDate->format('d-m-Y H:i')})",
                ];
                $errorSupplement[] = $register;
            }
            $dataUpdate['supplement'][] = $register;
        }

        /* OT registration */
        $errorOT = [];
        foreach ($otherExist['ot'] as $register) {
            $employee = $dataEmployee[$empId = $register['employee_id']];
            $teamCode = $employee['teamCode'];
            if (!isset($cacheSpecialHolidays[$teamCode])) {
                $cacheSpecialHolidays[$teamCode] = CoreConfigData::getSpecialHolidays(2, $teamCode);
            }
            $references['specialHolidays'] = $cacheSpecialHolidays[$teamCode];
            $references['wtFrame'] = $tempWTDetail[$empId]['wtFrame'];
            $references['wtHalfFrame'] = $tempWTDetail[$empId]['wtHalfFrame'];
            $references['wtDefault'] = $employee['wtDefault'];

            list ($register['newStart'], $register['newEnd']) = $this->getNewPeriod($tempWTDetail[$empId]['volatility'], $register, $references);
            if ($register['status'] == OtRegister::DONE && ($register['date_start'] !== $register['newStart'] || $register['date_end'] !== $register['newEnd'])) {
                $startDate = Carbon::parse($register['newStart'])->format('d-m-Y H:i');
                $endDate = Carbon::parse($register['newEnd'])->format('d-m-Y H:i');
                $register['note'] = [
                    'old_text' => '',
                    'new_text' => trans('manage_time::message.New time OT: :start -> :end', ['start' => $startDate, 'end' => $endDate]),
                ];
                $errorOT[] = $register;
            }
            $dataUpdate['ot'][] = $register;
        }

        /* exist error (can't convert start time and end time) */
        if ($errorLeaveDay || $errorSupplement || $errorOT) {
            return [
                'status' => false,
                'errors' => ['leave_day' => $errorLeaveDay, 'supplement' => $errorSupplement, 'ot' => $errorOT],
            ];
        }

        /* business trip => auto sync because it does not affect the number of days off */
        foreach ($otherExist['business_trip'] as $register) {
            $empId = $register['employee_id'];
            $references['wtFrame'] = $tempWTDetail[$empId]['wtFrame'];
            $references['wtDefault'] = $dataEmployee[$empId]['wtDefault'];
            list ($register['newStart'], $register['newEnd']) = $this->getNewPeriod($tempWTDetail[$empId]['volatility'], $register, $references);
            $dataUpdate['business_trip'][] = $register;
        }

        $this->updateOtherRegistration($dataUpdate);
        return ['status' => true];
    }

    /**
     * update new start time, end time and number of day off of other registration
     * @param array $dataUpdate
     * @return void
     */
    private function updateOtherRegistration($dataUpdate)
    {
        foreach ($dataUpdate['leave_day'] as $item) {
            LeaveDayRegister::where('id', $item['id'])
                ->update([
                    'date_start' => $item['newStart'],
                    'date_end' => $item['newEnd'],
                    'number_days_off' => $item['newNumDays'],
                ]);
        }
        foreach ($dataUpdate['supplement'] as $item) {
            SupplementRegister::where('id', $item['id'])
                ->update([
                    'date_start' => $item['newStart'],
                    'date_end' => $item['newEnd'],
                    'number_days_supplement' => $item['newNumDays'],
                ]);
            SupplementEmployee::where('supplement_registers_id', $item['id'])
                ->where('employee_id', $item['employee_id'])
                ->update([
                    'start_at' => $item['newStart'],
                    'end_at' => $item['newEnd'],
                    'number_days' => $item['newNumDays'],
                ]);
        }
        foreach ($dataUpdate['business_trip'] as $item) {
            BusinessTripEmployee::where('register_id', $item['id'])
                ->where('employee_id', $item['employee_id'])
                ->update(['start_at' => $item['newStart'], 'end_at' => $item['newEnd']]);
            /* update root registration if employee is creator */
            BusinessTripRegister::where('id', $item['id'])
                ->where('creator_id', $item['employee_id'])
                ->update(['date_start' => $item['newStart'], 'date_end' => $item['newEnd']]);
        }
        foreach ($dataUpdate['ot'] as $item) {
            OtEmployee::where('ot_register_id', $item['id'])
                ->where('employee_id', $item['employee_id'])
                ->update(['start_at' => $item['newStart'], 'end_at' => $item['newEnd']]);
            /* update root registration if employee is creator */
            OtRegister::where('id', $item['id'])
                ->where('employee_id', $item['employee_id'])
                ->update(['start_at' => $item['newStart'], 'end_at' => $item['newEnd']]);
        }
    }

    /**
     * check employees register working time overlap a register leave day or supplement or business-trip or OT
     *
     * @param string $registrationType
     * @param array $dataWTDetail [start_date, end_date, employee_id]
     * @return array
     */
    public function checkExistOtherRegistration($registrationType, $dataWTDetail)
    {
        $tblSupplementRegister = SupplementRegister::getTableName();
        $tblLeaveDayRegister = LeaveDayRegister::getTableName();
        $tblBusinessTripRegister = BusinessTripRegister::getTableName();
        $tblBusinessTripEmployee = BusinessTripEmployee::getTableName();
        $tblOTRegister = OtRegister::getTableName();
        $tblOTEmployee = OtEmployee::getTableName();
        $registrationModels = [
            View::STR_LEAVE_DAYS => LeaveDayRegister::class,
            View::STR_SUPPLEMENT => SupplementRegister::class,
            View::STR_MISSION => BusinessTripEmployee::class,
            View::STR_OT => OtEmployee::class,
        ];
        $filterColumns = [
            View::STR_LEAVE_DAYS => [
                'id' => "{$tblLeaveDayRegister}.id",
                'employee_id' => "{$tblLeaveDayRegister}.creator_id",
                'date_start' => "{$tblLeaveDayRegister}.date_start",
                'date_end' => "{$tblLeaveDayRegister}.date_end",
                'num_days' => "{$tblLeaveDayRegister}.number_days_off",
                'type' => trans('manage_time::view.Leave day register'),
                'url' => route('manage_time::profile.leave.detail', ['id' => ':id']),
            ],
            View::STR_SUPPLEMENT => [
                'id' => "{$tblSupplementRegister}.id",
                'employee_id' => "{$tblSupplementRegister}.creator_id",
                'date_start' => "{$tblSupplementRegister}.date_start",
                'date_end' => "{$tblSupplementRegister}.date_end",
                'num_days' => "{$tblSupplementRegister}.number_days_supplement",
                'type' => trans('manage_time::view.Supplement register'),
                'url' => route('manage_time::profile.supplement.detail', ['id' => ':id'])
            ],
            View::STR_MISSION => [
                'id' => "{$tblBusinessTripRegister}.id",
                'employee_id' => "{$tblBusinessTripEmployee}.employee_id",
                'date_start' => "{$tblBusinessTripEmployee}.start_at",
                'date_end' => "{$tblBusinessTripEmployee}.end_at",
                'num_days' => "{$tblBusinessTripRegister}.number_days_business_trip",
                'type' => trans('manage_time::view.Business trip register'),
                'url' => route('manage_time::profile.mission.detail', ['id' => ':id']),
            ],
            View::STR_OT => [
                'id' => "{$tblOTRegister}.id",
                'employee_id' => "{$tblOTEmployee}.employee_id",
                'date_start' => "{$tblOTEmployee}.start_at",
                'date_end' => "{$tblOTEmployee}.end_at",
                'num_days' => "NULL",
                'type' => trans('manage_time::view.OT register'),
                'url' => route('ot::ot.detail', ['id' => ':id']),
            ],
        ];
        $registerModel = $registrationModels[$registrationType];
        $columns = $filterColumns[$registrationType];

        $collection = $registerModel::join('employees', 'employees.id', '=', $columns['employee_id']);
        if ($registrationType === View::STR_MISSION) {
            $collection->join('business_trip_registers', 'business_trip_registers.id', '=', 'business_trip_employees.register_id');
            $collection->whereNull('business_trip_registers.deleted_at');
        }
        if ($registrationType === View::STR_OT) {
            $collection->join('ot_registers', 'ot_registers.id', '=', 'ot_employees.ot_register_id');
            $collection->whereNull('ot_registers.deleted_at');
        }

        $flagCheckExist = false;
        $collection->where(function ($query) use ($dataWTDetail, $columns, &$flagCheckExist) {
            foreach ($dataWTDetail as $item) {
                if (!$item['periods']) { /* don't change line in table employee tags */
                    continue;
                }
                $flagCheckExist = true;
                $query->orWhere(function ($q) use ($item, $columns) {
                    $q->where($columns['employee_id'], $item['employee_id']);
                    $q->where(function ($q2) use ($item, $columns) {
                        foreach ($item['periods'] as $period) {
                            $q2->orWhereBetween(DB::raw("DATE({$columns['date_start']})"), $period);
                            $q2->orWhereBetween(DB::raw("DATE({$columns['date_end']})"), $period);
                        }
                    });
                });
            }
        });
        /* don't change all lines in table employee tags */
        if (!$flagCheckExist) {
            return [];
        }

        $collection->select([
            "{$columns['id']}",
            "{$columns['employee_id']} AS employee_id",
            'employees.name as employee_name',
            DB::raw("SUBSTRING({$columns['date_start']}, 1, 16) AS date_start"),
            DB::raw("SUBSTRING({$columns['date_end']}, 1, 16) AS date_end"),
            DB::raw("{$columns['num_days']} AS num_days"),
            DB::raw("'{$columns['type']}' AS type"),
            DB::raw("'{$columns['url']}' AS url"),
            'status',
        ]);
        if ($registrationType === View::STR_SUPPLEMENT) {
            $collection->addSelect("{$tblSupplementRegister}.is_ot");
        }

        $registrations = [];
        foreach ($collection->get()->toArray() as $item) {
            $item['startDate'] = substr($item['date_start'], 0, 10);
            $item['endDate'] = substr($item['date_end'], 0, 10);
            $item['startHour'] = substr($item['date_start'], 11, 5);
            $item['endHour'] = substr($item['date_end'], 11, 5);
            $item['registrationType'] = $registrationType;
            $registrations[] = $item;
        }
        return $registrations;
    }

    /**
     * list employee tags in registration
     * @param integer $wtId
     * @return array
     */
    public function listEmployeeRegisterById($wtId)
    {
        $tblWTRegister = WorkingTimeRegister::getTableName();
        $tblWTDetail = WorkingTimeDetail::getTableName();
        $collection = WorkingTimeDetail::join($tblWTRegister, "{$tblWTRegister}.id", '=', "{$tblWTDetail}.working_time_id")
            ->where("{$tblWTDetail}.working_time_id", $wtId)
            ->whereNull("{$tblWTRegister}.deleted_at")
            ->select([
                "{$tblWTDetail}.employee_id",
                "{$tblWTDetail}.from_date",
                "{$tblWTDetail}.to_date",
                "{$tblWTDetail}.key_working_time",
                "{$tblWTDetail}.key_working_time_half",
            ]);
        $employeeTags = [];
        foreach ($collection->get() as $employeeTag) {
            $employeeTags[$employeeTag->employee_id] = $employeeTag->toArray();
        }
        return $employeeTags;
    }

    /**
     * merge old data working time register and get merge filter periods check exist other registration
     * @param array $dataWTDetail
     * @param integer $registerId
     * @return array
     */
    public function processMergeOldDataWTDetail($dataWTDetail, $registerId = null) {
        $employeeTags = $registerId ? $this->listEmployeeRegisterById($registerId) : [];
        /* check insert/update/delete employee tags and merge old data (start date, end date) */
        foreach ($dataWTDetail as $key => $item) {
            $employeeId = $item['employee_id'];
            if (isset($employeeTags[$employeeId])) { /* update employee tag */
                $item['action'] = 'update';
                $item['old_start_date'] = $employeeTags[$employeeId]['from_date'];
                $item['old_end_date'] = $employeeTags[$employeeId]['to_date'];
                $item['old_key_working_time'] = $employeeTags[$employeeId]['key_working_time'];
                $item['old_key_working_time_half'] = $employeeTags[$employeeId]['key_working_time_half'];
                unset($employeeTags[$employeeId]);
            } else { /* insert employee tag */
                $item['action'] = 'insert';
            }
            $dataWTDetail[$key] = $item;
        }
        /* delete employee tag */
        foreach ($employeeTags as $employeeId => $employeeTag) {
            $dataWTDetail[] = [
                'action' => 'delete',
                'employee_id' => $employeeId,
                'old_start_date' => $employeeTag['from_date'],
                'old_end_date' => $employeeTag['to_date'],
                'old_key_working_time' => $employeeTag['key_working_time'],
                'old_key_working_time_half' => $employeeTags[$employeeId]['key_working_time_half'],
            ];
        }

        foreach ($dataWTDetail as $key => $item) {
            /* merge filter periods check exist other registration */
            /* key periods <array 2D>: filter start date or end date other registration in periods */
            /* set A: new working time , set B: old working time, set S: periods existed */
            /* new item employee tag: S = A U B, B = Ø */
            if ($item['action'] === 'insert') {
                $dataWTDetail[$key]['periods'] = [[$item['start_date'], $item['end_date']]];
                continue;
            }
            /* delete item employee tag: S = A U B, A = Ø */
            if ($item['action'] === 'delete') {
                $dataWTDetail[$key]['periods'] = [[$item['old_start_date'], $item['old_end_date']]];
                continue;
            }
            /* old item: change working time frame: S = A U B */
            if ($item['old_key_working_time'] !== (string) $item['key_working_time']) {
                $dataWTDetail[$key]['periods'] = [
                    [$item['start_date'], $item['end_date']],
                    [$item['old_start_date'], $item['old_end_date']],
                ];
                continue;
            }
            /* old item: change start date and end date: S = A\B U B\A */
            $dataWTDetail[$key]['periods'] = (new View())->getComplementTwoPeriods(
                [$item['start_date'], $item['end_date']],
                [$item['old_start_date'], $item['old_end_date']]
            );
        }
        return $dataWTDetail;
    }
    
     /*
     * list my working times
     */
    public function listMyTimes()
    {
        $empId = auth()->id();
        $tbl = static::getTableName();
        $tblWorking = WorkingTimeRegister::getTableName();
        $collection = self::select(
            "{$tbl}.working_time_id",
            "{$tbl}.employee_id",
            "{$tbl}.team_id",
            "{$tbl}.from_date",
            "{$tbl}.to_date",
            "{$tbl}.start_time1",
            "{$tbl}.end_time1",
            "{$tbl}.start_time2",
            "{$tbl}.end_time2",
            "{$tbl}.half_morning",
            "{$tbl}.half_afternoon",
            "{$tbl}.created_at",
            "{$tblWorking}.approver_id",
            "{$tblWorking}.reason",
            "{$tblWorking}.status"
        )
        ->join("{$tblWorking}", "{$tblWorking}.id", '=', "{$tbl}.working_time_id")
        ->where("{$tbl}.employee_id", $empId)
        ->whereNull("{$tblWorking}.deleted_at")
        ->groupBy("{$tbl}.working_time_id");
        //filter data
        $pager = Config::getPagerData();
        self::filterGrid($collection);
        $collection->orderBy("{$tbl}.created_at", 'desc');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get working time info of employee at date
     * @param integer $empId
     * @param string $date
     * @return mixed
     */
    public function getWorkingTimeInfo($empId, $date)
    {
        return self::join('working_time_registers', 'working_time_registers.id', '=', 'working_time_details.working_time_id')
            ->whereDate('working_time_details.from_date', '<=', $date)
            ->whereDate('working_time_details.to_date', '>=', $date)
            ->where('working_time_details.employee_id', $empId)
            ->where('working_time_registers.status', WorkingTimeRegister::STATUS_APPROVE)
            ->first();
    }

    /**
     * check date is weekend or holiday
     * @param string $date
     * @param array $holidays
     * @return bool
     */
    private function isWeekendOrHoliday($date, $holidays = [])
    {
        $date = Carbon::parse($date);
        /* is weekend */
        if ($date->isWeekend()) {
            return true;
        }
        /* is annual holiday */
        if (!isset($holidays['annual'])) {
            $holidays['annual'] = CoreConfigData::getAnnualHolidays(2);
        }
        if (in_array($date->format('m-d'), $holidays['annual'])) {
            return true;
        }
        /* is special holiday */
        if (!isset($holidays['special'])) {
            $holidays['special'] = CoreConfigData::getSpecialHolidays(2);
        }
        if (in_array($date->format('Y-m-d'), $holidays['special'])) {
            return true;
        }

        return false;
    }
}