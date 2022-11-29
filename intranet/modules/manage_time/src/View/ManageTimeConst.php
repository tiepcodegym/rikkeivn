<?php

namespace Rikkei\ManageTime\View;

use Rikkei\Team\Model\Team;

Class ManageTimeConst
{
    const TYPE_COMELATE = 1;
    const TYPE_MISSION = 2;
    const TYPE_SUPPLEMENT = 3;
    const TYPE_LEAVE_DAY = 4;
    const TYPE_OT = 5;
    const TYPTE_LATE_IN_EARLY_OUT = 6;
    
    const FOLDER_ATTACH_LEAVE_DAY = 'leaveday';

    const MAX_DAY = 20;
    const MAX_TRANSFER = 8;
    const MIN_TIME_LEAVE_DAY = 180;

    const NOT_USED_LEAVE_DAY = 0;
    const USED_LEAVE_DAY = 1;
    const NOT_SALARY = 0;

    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;

    const MORNING_SHIFT = 'Sáng';
    const AFTERNOON_SHIFT = 'Chiều';
    const MORNING = 2;
    const AFTERNOON = 3;

    const START_TIME_MORNING_SHIFT = '08:00';
    const END_TIME_MORNING_SHIFT = '12:00';
    const START_TIME_AFTERNOON_SHIFT = '13:30';
    const END_TIME_AFTERNOON_SHIFT = '17:30';

    const MAX_TIME_LATE_IN_EARLY_OUT = 119;

    const NOT_WORKING = 0;
    const FULL_TIME = 1;
    const PART_TIME_MORNING = 2;
    const PART_TIME_AFTERNOON = 4;
    const HOLIDAY_TIME = 3;
    const PART_TIME_ANY = 5;
    const RESET = 'reset';

    const HAS_NOT_BUSINESS_TRIP = 0; // Has not register business trip
    const HAS_BUSINESS_TRIP_FULL_DAY = 1; // Register business trip full day
    const HAS_BUSINESS_TRIP_MORNING = 2; // Register business trip on morning
    const HAS_BUSINESS_TRIP_AFTERNOON = 3; // Register business trip on afternoon

    const HAS_NOT_LEAVE_DAY = 0; // Has not register leave day
    const HAS_LEAVE_DAY_FULL_DAY = 1; // Register leave day full day
    const HAS_LEAVE_DAY_MORNING = 2; // Register leave day on morning
    const HAS_LEAVE_DAY_AFTERNOON = 3; // Register leave day on afternoon

    const HAS_LEAVE_DAY_MORNING_HALF = 5; // Register leave day on half morning
    const HAS_LEAVE_DAY_AFTERNOON_HALF = 7; // Register leave day on half afternoon

    const HAS_NOT_SUPPLEMENT = 0; // Has not register supplement
    const HAS_SUPPLEMENT_FULL_DAY = 1; // Register supplement full day
    const HAS_SUPPLEMENT_MORNING = 2; // Register supplement on morning
    const HAS_SUPPLEMENT_AFTERNOON = 3; // Register supplement on afternoon

    const IS_NOT_OT = 0;
    const IS_OT = 1;
    const IS_OT_WEEKEND = 2;
    const IS_OT_ANNUAL_SPECIAL_HOLIDAY = 3;

    const SALARY_OT_RATE = 1.5;
    const TIME_WORKING_PER_DAY = 8;

    const TIME_LATE_IN_PER_BLOCK = 10;
    const FINES_LATE_IN_PER_BLOCK = 20000;
    const FINES_LATE_IN_PER_BLOCK_DN = 10000;
    const FINES_LATE_IN_PER_BLOCK_HCM = 10000;

    const TYPE_AJAX_GET_TIMEKEEPING_TABLE = 1;
    const TYPE_AJAX_GET_TIMEKEEPING_AGGREGATE = 2;

    const KEY_RANGE_WKTIME = 'working_range_times';
    const TOTAL_WORKING_TIME = 8; //hour
    const STEPING_MINUTE = 15;

    const STT_WK_TIME_NOT_APPROVE = 1;
    const STT_WK_TIME_APPROVED = 2;
    const STT_WK_TIME_REJECT = 3;
    const TIME_END_OT = 6;
    //floor To Fraction ot hour
    const FLOOR_OT_HOUR_JAPAN = 4;

    const JP_TIME_START_OT = 30;
    const TIME_MORE_HALF = 0.7;

    /**
     * get hour work day: start and end shift
     *
     * @return [type] [description]
     */
    public static function getHourWorkDay()
    {
        $result = [];
        $result['start_morning'] = array_map('intval', explode(':', self::START_TIME_MORNING_SHIFT));
        $result['end_morning'] = array_map('intval', explode(':', self::END_TIME_MORNING_SHIFT));
        $result['start_afternoon'] = array_map('intval', explode(':', self::START_TIME_AFTERNOON_SHIFT));
        $result['end_afternoon'] = array_map('intval', explode(':', self::END_TIME_AFTERNOON_SHIFT));
        return $result;
    }

    public static function days() {
        $days = array(
            self::SUNDAY => trans('manage_time::view.Sunday'),
            self::MONDAY => trans('manage_time::view.Monday'),
            self::TUESDAY => trans('manage_time::view.Tuesday'),
            self::WEDNESDAY => trans('manage_time::view.Wednesday'),
            self::THURSDAY => trans('manage_time::view.Thursday'),
            self::FRIDAY => trans('manage_time::view.Friday'),
            self::SATURDAY => trans('manage_time::view.Saturday'),
        );
        return $days;
    }

    public static function dayJPs()
    {
        return array(
            self::SUNDAY => '日曜日',
            self::MONDAY => '月曜日',
            self::TUESDAY => '火曜日',
            self::WEDNESDAY => '水曜日',
            self::THURSDAY => '木曜日',
            self::FRIDAY => '金曜日',
            self::SATURDAY => '土曜日',
        );
    }

    /*
     * list working time statuses
     */
    public static function listWorkingTimeStatuses()
    {
        return [
            self::STT_WK_TIME_NOT_APPROVE => trans('manage_time::view.Unapprove'),
            self::STT_WK_TIME_APPROVED => trans('manage_time::view.Approved'),
            self::STT_WK_TIME_REJECT => trans('manage_time::view.Reject'),
        ];
    }

    /*
     * list working time statuses
     */
    public static function listWTStatusesWithIcon()
    {
        return [
            null => [
                'title' => trans('manage_time::view.All'),
                'icon' => 'fa-inbox',
                'label_icon' => 'bg-aqua'
            ],
            self::STT_WK_TIME_APPROVED => [
                'title' => trans('manage_time::view.Approved'),
                'icon' => 'fa-check',
                'label_icon' => 'bg-green'
            ],
            self::STT_WK_TIME_NOT_APPROVE => [
                'title' => trans('manage_time::view.Unapprove'),
                'icon' => 'fa-hourglass-half',
                'label_icon' => 'bg-yellow'
            ],
            self::STT_WK_TIME_REJECT => [
                'title' => trans('manage_time::view.Reject'),
                'icon' => 'fa-window-close',
                'label_icon' => 'bg-red'
            ]
        ];
    }

    /**
     * get block fines money of branch
     * @param  [string] $teamCode
     * @return int
     */
    public function getFinesBlockBranch($teamCode)
    {
        switch ($teamCode) {
            case Team::CODE_PREFIX_HCM:
            case Team::CODE_PREFIX_RS:
                $block = self::FINES_LATE_IN_PER_BLOCK_HCM;
                break;
            case Team::CODE_PREFIX_DN:
                $block = self::FINES_LATE_IN_PER_BLOCK_DN;
                break;
            default:
                $block = self::FINES_LATE_IN_PER_BLOCK;
                break;
        }
        return $block;
    }

    /**
     * calculatiom fines fines money with branch
     * @param  int $block
     * @param  [string] $teamCode
     * @return int
     */
    public function getFinesMoneyLateIn($block, $teamCode)
    {
        return $block * $this->getFinesBlockBranch($teamCode);
    }
}
