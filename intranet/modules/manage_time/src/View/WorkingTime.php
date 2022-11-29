<?php

namespace Rikkei\ManageTime\View;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Rikkei\Core\View\CacheHelper;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\Model\TimekeepingWorkingTime;
use Rikkei\ManageTime\Model\WorkingTimeRegister;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Permission;
use Rikkei\Core\Model\CoreConfigData;

class WorkingTime
{

    const ROUTE_REGISTER = 'manage_time::permiss.wktime.register';
    const ROUTE_MANAGE = 'manage_time::permis.wktime.manage';
    const ROUTE_APPROVE = 'manage_time::permis.wktime.approve';
    const ROUTE_LOG_TIME = 'manage_time::permiss.log_time';

    /**
     * get time frame woking
     *
     * @return array
     */
    public function getWorkingTimeFrame()
    {
        return [
            [
                '08:00',
                '12:00',
                '13:30',
                '17:30',
            ],
            [
                '08:00',
                '12:00',
                '13:00',
                '17:00',
            ],
            [
                '08:30',
                '12:00',
                '13:00',
                '17:30',
            ],
            [
                '08:30',
                '12:00',
                '13:30',
                '18:00',
            ],
            [
                '09:00',
                '12:00',
                '13:30',
                '18:30',
            ],
            [
                '09:00',
                '12:00',
                '13:00',
                '18:00',
            ],
            [
                '08:30',
                '12:00',
                '13:15',
                '17:45',
            ],
        ];
    }
    
    /**
     * get time half frame
     *
     * @return array
     */
    public function getWorkingTimeHalfFrame()
    {
        return [
            [
                '10:00',
                '15:30'
            ],
            [
                '10:30',
                '15:45'
            ],
        ];
    }

    /**
     * workingTimeRelationship
     * key WKT Fram => key WKT half frame
     * @return array
     */
    public function workingTimeHalfRelationship()
    {
        return [
            0 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1
        ];
    }

    /**
     * get label working time
     *
     * @param  array $data
     * @param  boolean $isArray
     * @return string
     */
    public function getLabelWorkingTime($data, $isArray = false)
    {
        $strMorning = trans('manage_time::view.Morning') . ': ' . $data[0] . ' - ' . $data[1];
        $strAfter = trans('manage_time::view.Afternoon') . ': ' . $data[2] . ' - ' . $data[3];
        if ($isArray) {
            return [$strMorning, $strAfter];
        }
        return $strMorning . '; ' . $strAfter;
    }
    
    /**
     * get label working time half
     *
     * @param  array $data
     * @param  boolean $isArray
     * @return string
     */
    public function getLabelWorkingTimeHalf($data, $isArray = false)
    {
        $strMorning = trans('manage_time::view.Morning') . ': ' . $data[0];
        $strAfter = trans('manage_time::view.Afternoon') . ': ' . $data[1];
        if ($isArray) {
            return [$strMorning, $strAfter];
        }
        return $strMorning . '; ' . $strAfter;
    }

    /*
     * list working time statuses
     */
    public function listWorkingTimeStatuses()
    {
        return [
            WorkingTimeRegister::STATUS_UNAPPROVE => trans('manage_time::view.Unapprove'),
            WorkingTimeRegister::STATUS_APPROVE => trans('manage_time::view.Approved'),
            WorkingTimeRegister::STATUS_REJECT => trans('manage_time::view.Reject'),
        ];
    }

    /*
     * render html status
     */
    public function renderStatusHtml($listStatuses, $status, $class = 'callout')
    {
        $strStatus = $listStatuses[$status];
        $html = '<div class="'. $class .' text-center white-space-nowrap ' . $class;
        switch ($status) {
            case WorkingTimeRegister::STATUS_UNAPPROVE:
                $html .= '-warning">' . $strStatus;
                break;
            case WorkingTimeRegister::STATUS_APPROVE:
                $html .= '-success">' . $strStatus;
                break;
            case WorkingTimeRegister::STATUS_REJECT:
                $html .= '-danger">' . $strStatus;
                break;
            default:
                return null;
        }
        return $html .= '</div>';
    }

    /*
     * render text status
     */
    public function getTextStatus($status)
    {
        switch ($status) {
            case WorkingTimeRegister::STATUS_UNAPPROVE:
                return trans('manage_time::view.was_unapproved');
            case WorkingTimeRegister::STATUS_APPROVE:
                return trans('manage_time::view.was_approved');
            case WorkingTimeRegister::STATUS_REJECT:
                return trans('manage_time::view.was_rejected');
            default:
                return null;
        }
    }

    /*
     * list working time statuses
     */
    public function listWTStatusesWithIcon()
    {
        return [
            null => [
                'title' => trans('manage_time::view.All'),
                'icon' => 'fa-inbox',
                'label_icon' => 'bg-aqua'
            ],
            WorkingTimeRegister::STATUS_UNAPPROVE => [
                'title' => trans('manage_time::view.Unapprove'),
                'icon' => 'fa-hourglass-half',
                'label_icon' => 'bg-yellow'
            ],
            WorkingTimeRegister::STATUS_APPROVE => [
                'title' => trans('manage_time::view.Approved'),
                'icon' => 'fa-check',
                'label_icon' => 'bg-green'
            ],
            WorkingTimeRegister::STATUS_REJECT => [
                'title' => trans('manage_time::view.Reject'),
                'icon' => 'fa-window-close',
                'label_icon' => 'bg-red'
            ]
        ];
    }
    
    /**
     * getKeyWorkingTime
     *
     * @param  string $startMorning [time in morning]
     * @param  string $startAfter  [time in afternoon]
     * @return int
     */
    public function getKeyWorkingTime($startMorning, $startAfter)
    {
        $arrWT = $this->getWorkingTimeFrame();
        
        foreach($arrWT as $key => $item) {
            if ($item[0] == $startMorning && $item[2] == $startAfter) {
                return $key;
            }
        }
        return 0;
    }
    
    /**
     * getKeyWorkingTimeHalf
     *
     * @param  string $halfMoring  [time half morning]
     * @param  string $halfAfter [time half afternoon]
     * @return int
     */
    public function getKeyWorkingTimeHalf($halfMoring, $halfAfter)
    {
        $arrHalfWT = $this->getWorkingTimeHalfFrame();
        foreach($arrHalfWT as $key => $item) {
            if ($item[0] == $halfMoring && $item[1] == $halfAfter) {
                return $key;
            }
        }
        return 0;
    }
    
    /*
     * get permission register
     */
    public function getPermissByRoute($route = self::ROUTE_REGISTER)
    {
        $scope = Permission::getInstance();
        if (!$scope->isAllow($route)) {
            return false;
        }
        if ($scope->isScopeCompany(null, $route)) {
            return 'all';
        } elseif ($scope->isScopeTeam(null, $route)) {
            return 'team';
        } else {
            return 'x';
        }
    }

    /*
     * get register permission
     */
    public function getPermisison($item = null)
    {
        $currentUser = Permission::getInstance()->getEmployee();
        $scope = Permission::getInstance();
        $route = self::ROUTE_MANAGE;
        $permissApprove = $scope->isAllow(self::ROUTE_APPROVE); // quyền approve
        $permissNotApprove = $permissApprove; //không duyệt

        try {
            if (!$item) { //không có tài liệu
                $permissEdit = true;
                $permissView = true;
            } else {
                //permission scope
                if ($scope->isScopeCompany(null, $route)) {
                    $permissEdit = ($item->employee_id == $currentUser->id);
                    $permissApprove = true;
                } elseif ($scope->isScopeTeam(null, $route)) {
                    $wktTbl = WorkingTimeRegister::getTableName();
                    $tmbTbl = TeamMember::getTableName();
                    $teamIds = $scope->isScopeTeam(null, $route);
                    //has item edit
                    $hasItem = WorkingTimeRegister::from($wktTbl)
                            ->join($tmbTbl . ' as tmb', "{$wktTbl}.employee_id", '=', 'tmb.employee_id')
                            ->where(function ($query) use ($wktTbl, $teamIds, $currentUser) {
                                $query->whereIn('tmb.team_id', $teamIds)
                                        ->orWhere("{$wktTbl}.employee_id", '=', $currentUser->id)
                                        ->orWhere("{$wktTbl}.updated_by", '=', $currentUser->id);
                            })
                            ->where("{$wktTbl}.id", $item->id)
                            ->first();
                    $permissEdit = $hasItem != null;
                    //has item approve
                    $hasItemApprove = WorkingTimeRegister::from($wktTbl)
                            ->join($tmbTbl . ' as tmb', "{$wktTbl}.employee_id", '=', 'tmb.employee_id')
                            ->where(function ($query) use ($wktTbl, $teamIds, $currentUser) {
                                $query->whereIn('tmb.team_id', $teamIds)
                                        ->orWhere("{$wktTbl}.approver_id", '=', $currentUser->id);
                            })
                            ->where("{$wktTbl}.id", $item->id)
                            ->first();
                    $permissApprove = $hasItemApprove != null;
                } else {
                    $permissEdit = in_array($currentUser->id, [$item->employee_id, $item->updated_by]);
                    $permissApprove = $currentUser->id == $item->approver_id;
                }

                $permissNotApprove = $permissApprove;
                $relatedIds = $item->getRelatedIds();
                $permissView = $permissEdit || $permissApprove || in_array($currentUser->id, $relatedIds);
            }

            /* When working time register has status is approve => can't update working time register */
            /* only user has permission approve => update register and approve immediately */
            $isApproved = $item && (int)$item->status === WorkingTimeRegister::STATUS_APPROVE;
            $permissionUpdateApproved = $isApproved && $permissApprove;

            return [
                'view' => $permissView,
                'edit' => $permissEdit,
                'approve' => $permissApprove,
                'not_approve' => $permissNotApprove,
                'update_approved' => $permissionUpdateApproved,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return redirect()->route('core::errors.system');
        }
        
    }
    
    /**
     * get array date
     *
     * @param  carbon $startDate
     * @param  carbon $endDate
     * @return array
     */
    public function getDateBetweenDate($startDate, $endDate)
    {
        $arrDate = [];
        $i = 0;
        while(strtotime($startDate) <= strtotime($endDate)) {
            $arrDate[] = $startDate->format('Y-m-d');
            $startDate->addDay();
            $i++;
        }
        return $arrDate;
    }
        
    /**
     * get working time, working time quarter of employee
     *
     * @param int $empId
     * @param string $teamCodePre
     * @param null|array $period (contains start date and end date)
     * @return array
     */
    public function getWorkingTimeByEmployeeBetween($empId, $teamCodePre, $period = null)
    {
        if (!empty($period)) {
            list ($timeSetting, $timeWorkingQuarter) = $this->getEmpWorkingTimeInPeriod($empId, $teamCodePre, $period);
            return ['timeSetting' => $timeSetting, 'timeWorkingQuater' => $timeWorkingQuarter];
        }
        $timeSetting = CacheHelper::get(CacheHelper::CACHE_TIME_SETTING_PREFIX, $empId);
        $timeWorkingQuarter = !empty($timeSetting) ? CacheHelper::get(CacheHelper::CACHE_TIME_QUATER, $empId) : [];

        /* default list dates store in cache */
        if (empty($timeSetting)) {
            $period = [
                'start_date' => Carbon::now()->firstOfMonth()->toDateString(),
                'end_date' => Carbon::now()->addMonth(1)->lastOfMonth()->toDateString(),
            ];
            list ($timeSetting, $timeWorkingQuarter) = $this->getEmpWorkingTimeInPeriod($empId, $teamCodePre, $period);
            CacheHelper::put(CacheHelper::CACHE_TIME_SETTING_PREFIX, $timeSetting, $empId);
            CacheHelper::put(CacheHelper::CACHE_TIME_QUATER, $timeWorkingQuarter, $empId);
        }
        return ['timeSetting' => $timeSetting, 'timeWorkingQuater' => $timeWorkingQuarter];
    }

    /**
     * get working time setting of all employees in registration
     *
     * @param array|Collection $employeeTags
     * @param object|Collection $registerInfo
     * @return array
     */
    public function getEmpWorkingTimeSettingInRegistration($employeeTags, $registerInfo)
    {
        $timeSetting = [];
        $hasEmpRegister = false;
        foreach ($employeeTags as $employeeTag) {
            $hasEmpRegister = $hasEmpRegister || $employeeTag->employee_id === $registerInfo->creator_id;
            $teamCode = Team::getOnlyOneTeamCodePrefixChange($employeeTag->employee_id);
            $period = [
                'start_date' => substr($employeeTag->start_at, 0, 10),
                'end_date' => substr($employeeTag->end_at, 0, 10),
            ];
            $workingTime = $this->getWorkingTimeByEmployeeBetween($employeeTag->employee_id, $teamCode, $period);
            $timeSetting[$employeeTag->employee_id] = $workingTime['timeSetting'][$employeeTag->employee_id];
        }
        if (!$hasEmpRegister) {
            $teamCode = Team::getOnlyOneTeamCodePrefixChange($registerInfo->creator_id);
            $period = [
                'start_date' => substr($registerInfo->date_start, 0, 10),
                'end_date' => substr($registerInfo->date_end, 0, 10),
            ];
            $workingTime = $this->getWorkingTimeByEmployeeBetween($registerInfo->creator_id, $teamCode, $period);
            $timeSetting[$registerInfo->creator_id] = $workingTime['timeSetting'][$registerInfo->creator_id];
        }
        return $timeSetting;
    }

    /**
     * get list time setting and time working quarter in period time
     * @param number $empId
     * @param string $teamCode
     * @param array $period - contains start date and end date
     * @return array
     */
    public function getEmpWorkingTimeInPeriod($empId, $teamCode, $period) {
        $timeSetting = [];
        $timeWorkingQuarter = [];
        $manageTimeView = new ManageTimeView();
        $empWorkingTimes = (new WorkingTimeRegister())->getWorkingTimeList($empId);
        $dateArray = (new ResourceView())->generateDatesInPeriod($period['start_date'], $period['end_date']);
        $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);
        foreach ($dateArray as $date) {
            $empWorkingTime = $this->getTimeWorkingOfDate($empWorkingTimes, $date);
            $timeSetting[$empId][$date] = $manageTimeView->buildTimeSettingEmployee($empWorkingTime, $teamCode, $rangeTimes);
            $timeWorkingQuarter[$empId][$date] = $manageTimeView->getTimeWorkingQuater($empWorkingTime, $teamCode);
        }
        return [$timeSetting, $timeWorkingQuarter];
    }
        
    /**
     * get working time, working time quater with date
     *
     * @param  int $empId
     * @param  string $teamCodePre
     * @param  date $date Y-m-d
     * @return array
     */
    public function getWorkingTimeByEmployeeDate($empId, $teamCodePre, $date)
    {
        $timeSetting = [];
        $timeWorkingQuater = [];
        if (!empty($timeCache = CacheHelper::get(CacheHelper::CACHE_TIME_SETTING_PREFIX, $empId))) {
            $timeSetting = $timeCache;
            $timeWorkingQuater = CacheHelper::get(CacheHelper::CACHE_TIME_QUATER, $empId);
        } else {
            $manatimeView = new ManageTimeView();
            $wTRegister = new WorkingTimeRegister();
            $times = $wTRegister->getWorkingTimeList($empId);

            $time = $this->getTimeWorkingOfDate($times, $date);
            $timeSetting[$empId][$date] = $manatimeView->buildTimeSettingEmployee($time, $teamCodePre);
            $timeWorkingQuater[$empId][$date] = $manatimeView->getTimeWorkingQuater($time, $teamCodePre);
            CacheHelper::put(CacheHelper::CACHE_TIME_SETTING_PREFIX, $timeSetting, $empId);
            CacheHelper::put(CacheHelper::CACHE_TIME_QUATER, $timeWorkingQuater, $empId);
        }
        return [
            'timeSetting' => $timeSetting,
            'timeWorkingQuater' => $timeWorkingQuater,
        ];
    }
    
     /**
     * @param collection $timeWorking
     * @param string $date
     * @return null|collection
     */
    public function getTimeWorkingOfDate($timeWorking, $date)
    {
        return array_first($timeWorking, function ($key, $value) use ($date) {
            return ($value->from_date <= $date && $value->to_date >= $date);
        });
    }
    
    /**
     * getLabelStatus
     *
     * @return array
     */
    public function getLabelStatusRegister()
    {
        return [
            WorkingTimeRegister::STATUS_UNAPPROVE => trans('manage_time::view.was_unapproved'),
            WorkingTimeRegister::STATUS_APPROVE => trans('manage_time::view.was_approved'),
            WorkingTimeRegister::STATUS_REJECT => trans('manage_time::view.was_rejected'),
        ];
    }

    /**
     * get string working time
     * view detai working time
     * 
     * @param  array $empIds
     * @param  date $startDate 'Y-m-d'
     * @param  date $endDate 'Y-m-d'
     * @param  string $teamCodePre
     * @return array 
     */
    public function getStrWorkingTime($empIds, $timekeepingTable, $teamCodePre)
    {
        $arrayWorkingTime = [];
        $startDate = $timekeepingTable->start_date;
        $endDate = $timekeepingTable->end_date;
        $lock = $timekeepingTable->lock_up;

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $emails = Employee::find($empIds)->lists('email')->toArray();
        if ($lock == TimekeepingTable::OPEN_LOCK_UP) {
            $teamLists =  Employee::getEmpByEmailsWithContracts($emails, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'), [
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
        } else {
            $objTimekeepingWT = new TimekeepingWorkingTime();
            $teamLists = $objTimekeepingWT->getWKTTimeKeepingEmployee($timekeepingTable->timekeeping_table_id, $empIds, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
        }
        $arrWorkingRegister = [];
        $workingTimeBranch = ManageTimeView::findTimeSetting(null, $teamCodePre);
        $dataWokringTimeB = [
            $workingTimeBranch['morningInSetting']->format('H:i'),
            $workingTimeBranch['morningOutSetting']->format('H:i'),
            $workingTimeBranch['afternoonInSetting']->format('H:i'),
            $workingTimeBranch['afternoonOutSetting']->format('H:i'),
        ];
        $arrWT = $this->getLabelWorkingTime($dataWokringTimeB, true);
        $strWTDefault = $arrWT[0] . "\n" . $arrWT[1];

        foreach($teamLists as $empId => $teamList) {
            foreach($teamList as $item) {
                $cbStart = Carbon::parse($item->wtk_from_date);
                $cbEnd = Carbon::parse($item->wtk_to_date);

                while (strtotime($cbStart) <= strtotime($cbEnd)) {
                    $data = [
                        $item->start_time1,
                        $item->end_time1,
                        $item->start_time2,
                        $item->end_time2,
                    ];
                    $arrWT =  $this->getLabelWorkingTime($data, true);
                    $arrWorkingRegister[$empId][$cbStart->format('Y-m-d')] = $arrWT[0] . "\n" . $arrWT[1];
                    $cbStart->addDay();
                }

            }
        }
        foreach ($empIds as $empId) {
            while (strtotime($startDate) <= strtotime($endDate)) {
                if (isset($arrWorkingRegister[$empId][$startDate->format('Y-m-d')])) {
                    $arrayWorkingTime[$empId][$startDate->format('Y-m-d')] = $arrWorkingRegister[$empId][$startDate->format('Y-m-d')];
                } else {
                    $arrayWorkingTime[$empId][$startDate->format('Y-m-d')] = $strWTDefault;
                }
                $startDate->addDay();
            }
        }
        return $arrayWorkingTime;
    }
}
