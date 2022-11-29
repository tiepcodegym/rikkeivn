<?php

namespace Rikkei\Project\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Me\View\View as RKMeView;
use Rikkei\Project\Model\BaselineDate;
use Rikkei\Project\Model\MeComment;
use Rikkei\Project\View\View as ProjView;

class MeView {

    const KEY_CONFIG_REWARD = 'contribute_reward';
    // separator filter month
    const SEP_DATE = 24;

    //payment state of reward
    const STATE_UNPAID = 0;
    const STATE_PAID = 1;

    const KEY_REVIEW_FILTER = 'review_data_filter';
    const KEY_MAIL_ACTIVITY = 'me_activities_mail';

    /**
     * get point from string array
     * @param type $strIds
     * @param type $strPoints
     */
    public static function getListPoint ($strAttrs) {
        $listPoints = [];
        $attrs = explode(',', $strAttrs);
        if (!$strAttrs || count($attrs) < 1) {
            return $listPoints;
        }
        foreach ($attrs as $itemAttrs) {
            $arrAttrs = explode('|', $itemAttrs);
            if (count($arrAttrs) < 1) {
                continue;
            }
            $listPoints[$arrAttrs[0]] = $arrAttrs[1];
        }
        return $listPoints;
    }

    /**
     * get comment type from string array
     * @param type $strAttrIds
     * @param type $strTypes
     * @return type
     */
    public static function getCommentClass ($strAttrs) {
        $attrs = explode(',', $strAttrs);
        $listTypes = [];
        if (!$strAttrs || count($attrs) < 1) {
            return $listTypes;
        }
        foreach ($attrs as $itemAttrs) {
            $arrAttrs = explode('|', $itemAttrs);
            if (count($arrAttrs) < 1 || $arrAttrs[0] == -1) {
                continue;
            }
            $attrId = $arrAttrs[1];
            $typeClass = 'td'.MeComment::classType($arrAttrs[2]);
            if (!isset($listTypes[$attrId])) {
                $listTypes[$attrId] = [$typeClass];
            } else {
                $listTypes[$attrId][] = $typeClass;
            }
        }

        return $listTypes;
    }

    /**
     * get work dates from string
     * @param type $strStartAt
     * @param type $strEndAt
     * @param type $strEffort
     * @return array
     */
    public static function getWorkDates ($item) {
        $time = $item->eval_time;
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        $result = 0;

        if ($item->project_id) {
            if (!$item->pjm_attrs) {
                return $result;
            }
            $attrs = explode(',', $item->pjm_attrs);
        } else {
            if (!$item->tpjm_attrs) {
                return $result;
            }
            $attrs = explode(',', $item->tpjm_attrs);
        }
        if (count($attrs) < 1) {
            return $result;
        }

        $timeFirstDay = clone $time;
        $timeFirstDay->startOfMonth();
        $timeLastDay = $time->lastOfMonth();
        try {
            foreach ($attrs as $idx => $strAttr) {
                $arrAttr = explode('|', $strAttr);
                if (count($arrAttr) < 1) {
                    continue;
                }
                $timeStart = Carbon::parse($arrAttr[1]);
                $timeEnd = Carbon::parse($arrAttr[2]);
                $effort = (float) $arrAttr[3];
                //check out range of month
                if ($timeStart->gt($timeLastDay) || $timeEnd->lt($timeFirstDay)) {
                    continue;
                }
                if ($timeStart->lt($timeFirstDay)) {
                    $timeStart = $timeFirstDay;
                }
                if ($timeEnd->gt($timeLastDay)) {
                    $timeEnd = $timeLastDay;
                }

                $result += ProjView::getMM($timeStart, $timeEnd, 2) * $effort / 100;
            }
        } catch (\Exception $e) {
            return number_format($result, 1, '.', ',');
        }

        return number_format($result, 1, '.', ',');
    }

    /**
     * get list config rewards
     * @return type
     */
    public static function listRewards ($filterMonth = null) {
        $keyDb = 'me.config.reward';
        if ($filterMonth && $filterMonth != '_all_'
                && $filterMonth->format('Y-m') > config('project.me_sep_month')
                && $filterMonth->format('Y-m') < config('project.me_new2_sep_month')) {
            $keyDb = 'me.new.config.reward';
        } elseif ($filterMonth && $filterMonth != '_all_' && $filterMonth->format('Y-m') >= config('project.me_new2_sep_month')) {
            $keyDb = 'me.new2.config.reward';
        }
        $list = CacheHelper::get($keyDb);
        if ($list) {
            return unserialize($list);
        }
        $list = CoreConfigData::getValueDb($keyDb);
        if ($list) {
            CacheHelper::put($keyDb, $list);
            return unserialize($list);
        }
        return [];
    }

    /**
     * get list reward onsite
     *
     * @return array
     */
    public static function listRewardsOnsite()
    {
        $keyDb = 'me.config.reward_onsite';
        $list = CacheHelper::get($keyDb);
        if ($list) {
            return unserialize($list);
        }
        $list = CoreConfigData::getValueDb($keyDb);
        if ($list) {
            CacheHelper::put($keyDb, $list);
            return unserialize($list);
        }
        return [
            RKMeView::TYPE_S => 1000000,
            RKMeView::TYPE_A => 500000,
            RKMeView::TYPE_B => 0,
            RKMeView::TYPE_C => 0
        ];
    }

    /**
     * hỗ trợ nhân viên onsite
     *
     * @param  int $month
     * @return int
     */
    public static function getListAllowanceOnste($month = null)
    {
        if (!$month) {
            return 0;
        }
        if ($month <= 6) {
            return 1000000;
        } elseif ($month > 6 && $month <= 12) {
            return 1500000;
        } elseif ($month > 12) {
            return 2000000;
        } else {

        }
    }

    /**
     * get item reward from list
     * @param type $avgPoint
     * @param type $list
     * @return int
     */
    public static function getItemReward ($avgPoint, $list) {
        if (!$list) {
            return 0;
        }
        foreach ($list as $value => $reward) {
            if ($avgPoint >= $value) {
                if (!$reward) {
                    return 0;
                }
                return $reward;
            }
        }
        return 0;
    }

    /**
     * get effort reward by group concat select ("...|pjm_id,pjm_effort|...")
     * @param type $item
     * @return type
     */
    public static function getEffortReward ($item, $rangeMonths = [])
    {
        $strEffort = $item->pjm_efforts;
        if (!$item->proj_name) {
            $strEffort = $item->pjmteam_efforts;
        }
        if (!$strEffort) {
            return 0;
        }
        $arrEfforts = explode(',', $strEffort);
        $result = 0;

        $evalTime = ($item->eval_time instanceof Carbon) ? $item->eval_time : Carbon::parse($item->eval_time);
        $evalMonth = $evalTime->format('Y-m');
        $timeFirstDay = $evalTime;
        $timeLastDay = clone $evalTime;
        $timeLastDay->lastOfMonth();
        if (isset($rangeMonths[$evalMonth])) {
            $timeFirstDay = Carbon::parse($rangeMonths[$evalMonth]['start']);
            $timeLastDay = Carbon::parse($rangeMonths[$evalMonth]['end']);
        }
        $dayOfMonth = ProjView::getMM($timeFirstDay, $timeLastDay, 2);

        try {
            foreach ($arrEfforts as $strItem) {
                $arrItem = explode('|', $strItem);
                if (count($arrItem) < 4) {
                    continue;
                }
                $startDate = Carbon::parse($arrItem[1]);
                $endDate = Carbon::parse($arrItem[2]);
                $effort = (float) $arrItem[3];

                if ($startDate->gt($timeLastDay) || $endDate->lt($timeFirstDay)) {
                    continue;
                }
                if ($startDate->lt($timeFirstDay)) {
                    $startDate = $timeFirstDay;
                }
                if ($endDate->gt($timeLastDay)) {
                    $endDate = $timeLastDay;
                }
                $result += ProjView::getMM($startDate, $endDate, 2) * $effort / $dayOfMonth;
            }
        } catch (\Exception $ex) {
            return $result;
        }

        return $result;
    }

    /**
     * get status label
     * @param type $status
     * @param type $listStatuses
     * @return type
     */
    public static function getRewardStatus ($status, $listStatuses) {
        if (isset($listStatuses[$status])) {
            return $listStatuses[$status];
        }
        return 'N/A';
    }

    /**
     * list paid statuses
     * @return type
     */
    public static function rewardPaidLabels()
    {
        return [
            self::STATE_PAID => trans('project::me.Paid'),
            self::STATE_UNPAID => trans('project::me.Unpaid')
        ];
    }

    /**
     * get project type label
     * @param type $arrayTypes
     * @return string
     */
    public static function getProjectTypeLabel($type = null, $arrayTypes = [])
    {
        if (!$type) {
            return 'Team';
        }
        if (isset($arrayTypes[$type])) {
            return $arrayTypes[$type];
        }
        return null;
    }

    /*
     * custom create date from format
     */
    public static function parseDateFromFormat($strTime, $format = 'Y-m-d H:i:s', $erroVal = '')
    {
        try {
            return Carbon::createFromFormat($format, $strTime);
        } catch (\Exception $ex) {
            return Carbon::now();
        }
    }

    /*
     * get project baseline date
     */
    public static function getBaselineDate()
    {
        $date = CoreConfigData::getValueDb('project.me.baseline_date');
        if (!$date) {
            $date = self::SEP_DATE;
        }
        return $date;
    }

    /**
     * get baseline week
     * @return type
     */
    public static function getBaselineWeekDates()
    {
        $date = self::getBaselineDate();
        $blWeek = Carbon::now();
        $blWeek->setDateTime($blWeek->year, $blWeek->month, $date, 0, 0, 0)->startOfWeek();
        $friDay = clone $blWeek;
        $friDay->addDays(6 - 2);
        if ($friDay->day > $date) {
            $blWeek->subDays(7);
            $friDay->subDays(7);
        }
        return [$blWeek->day, $friDay->day];
    }

    /*
     * get default filter month
     */
    public static function defaultFilterMonth()
    {
        $projBaselineDate = self::getBaselineDate();
        $filterMonth = Carbon::now();
        if ($filterMonth->day <= $projBaselineDate) {
            $filterMonth->subMonthNoOverflow();
        }
        return $filterMonth;
    }

    /*
     * render project links by string
     */
    public static function renderListProjects($strProject = null)
    {
        if (!$strProject) {
            return null;
        }
        $arrProjects = explode(',,', $strProject);
        $result = '';
        foreach ($arrProjects as $strProj) {
            $arrProjs = explode('||', $strProj);
            if (count($arrProjs) !== 2) {
                continue;
            }
            $result .= '<a target="_blank" href="'. route('project::project.edit', ['id' => $arrProjs[0]]) .'">'. e($arrProjs[1]) .'</a>, ';
        }
        return trim($result, ', ');
    }

    /*
     * get range baseline time of month
     */
    public static function getBaselineRangeTime($time)
    {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        $timeStart = clone $time;
        $timeStart->startOfMonth();
        $timeEnd = clone $time;
        $timeEnd->lastOfMonth();
        $baselineDate = BaselineDate::getDate($time);
        $prevTime = clone $time;
        $prevTime->subMonthNoOverflow();
        $baselinePrevDate = BaselineDate::getDate($prevTime);
        if ($baselineDate) {
            $timeEnd->setDate($time->year, $time->month, $baselineDate);
        }
        if ($baselinePrevDate) {
            $timeStart->setDate($prevTime->year, $prevTime->month, $baselinePrevDate + 1);
        }
        return [
            'start' => $timeStart,
            'end' => $timeEnd
        ];
    }

    /*
     * get list range start, end date of month base on baseline date
     */
    public static function listRangeBaselineDate($listMonths)
    {
        if (!$listMonths) {
            return [];
        }
        $blTbl = BaselineDate::getTableName();
        $listDates = BaselineDate::select('bl.month', 'bl.date', 'blprev.month as prev_month', 'blprev.date as prev_date')
            ->from($blTbl . ' as bl')
            ->leftJoin(
                $blTbl . ' as blprev',
                DB::raw('DATE_SUB(CONCAT(bl.month, "-01"), INTERVAL 1 MONTH)'),
                '=',
                DB::raw('CONCAT(blprev.month, "-01")')
            )
            ->whereIn('bl.month', $listMonths)
            ->get();
        if ($listDates->isEmpty()) {
            return [];
        }
        $results = [];
        foreach ($listDates as $dateItem) {
            $startDate = $dateItem->month . '-01';
            if ($dateItem->prev_month) {
                $startDate = Carbon::parse($dateItem->prev_month)->addDays($dateItem->prev_date)->format('Y-m-d');
            }
            $results[$dateItem->month] = [
                'start' => $startDate,
                'end' => $dateItem->month . '-' . $dateItem->date
            ];
        }
        return $results;
    }

    /*
     * check date is weekend or not, if is weekend return next date
     */
    public static function findNextWorkDate($date)
    {
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        if (!$date->isWeekend()) {
            return $date;
        }
        $date->addDay();
        return self::findNextWorkDate($date);
    }

    /*
     *
     */
    public static function textDayOfWeek($date)
    {
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        switch ($date->dayOfWeek) {
            case Carbon::SUNDAY:
                return trans('project::me.Sunday');
            case Carbon::MONDAY:
                return trans('project::me.Monday');
            case Carbon::TUESDAY:
                return trans('project::me.Tuesday');
            case Carbon::WEDNESDAY:
                return trans('project::me.Wednesday');
            case Carbon::THURSDAY:
                return trans('project::me.Thursday');
            case Carbon::FRIDAY:
                return trans('project::me.Friday');
            case Carbon::SATURDAY:
                return trans('project::me.Saturday');
            default: return null;
        }
    }

    public static function filterNewReward($array)
    {
        $existsAry = [];
        $newAry = [];
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $existsAry[$key] = $value;
            } else {
                $newAry[$key] = $value;
            }
        }
        return [
            'exists' => $existsAry,
            'new' => $newAry
        ];
    }

    public static function renderNewVerLink($link)
    {
        $month = Carbon::parse(config("project.me_sep_month"))->format('m-Y');
        return '<div><i>'. trans('me::view.View item after') . ' ' . $month .': <a href="'. $link .'">'. trans('me::view.click here') .'</i></div>';
    }
}
