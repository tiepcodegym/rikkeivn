<?php

namespace Rikkei\Project\View;

use Rikkei\Core\View\CookieCore;
use Carbon\Carbon;
use Rikkei\Core\Model\CoreConfigData;
use DatePeriod;
use DateInterval;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\ProjPointReport;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;

class ProjDbHelp
{
    /**
     * get work day of month
     * 
     * @param carbon $date
     * @return int
     */
    public static function getWorkDay($date = null)
    {
        if (!$date) {
            $date = Carbon::now();
        }
        $key = ProjConst::KEY_CK_DAYWORK . $date->format('Y-m');
        self::isSameDWVersion();
        if ($dayworks = CookieCore::get($key)) {
            return $dayworks;
        }
        $dayWorks = cal_days_in_month ( 
            CAL_GREGORIAN , 
            $date->format('m'), 
            $date->format('Y')
        );
        $specialHolidays = CoreConfigData::getSpecialHolidays(2);
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $weekend = (array) CoreConfigData::get('project.weekend');
        $last = clone $date;
        $start = clone $date;
        $last->endOfMonth()->startOfDay()->modify('+1 day');
        $start->startOfMonth();
        $periodDay = new DatePeriod($start, new DateInterval('P1D'), $last);
        foreach($periodDay as $dt) {
            $curr = $dt->format('D');
            if (in_array($curr, $weekend) ||
                in_array($dt->format('Y-m-d'), $specialHolidays) ||
                in_array($dt->format('m-d'), $annualHolidays)
            ) {
                $dayWorks--;
            }
        }
        if ($dayWorks < 0) {
            $dayWorks = 0;
        }
        CookieCore::set($key, $dayWorks, 20);
        return $dayWorks;
    }
    
    /**
     * join array to string of element array
     * 
     * @param array $array
     * @param string $key
     * @return string
     */
    public static function joinItemArray($array, $key)
    {
        if (!$array) {
            return null;
        }
        $result = '';
        foreach ($array as $item) {
            if (isset($item[$key])) {
                $result .= $item[$key] . ', ';
            }
        }
        return substr($result, 0, -2);
    }
    
    /**
     * options of color report change
     *
     * @return array
     */
    public static function toOptionReportColor()
    {
        return [
            ProjectPoint::COLOR_STATUS_BLUE => 'Blue',
            ProjectPoint::COLOR_STATUS_YELLOW => 'Yellow',
            ProjectPoint::COLOR_STATUS_RED => 'Red',
            ProjectPoint::COLOR_STATUS_WHITE => 'White',
        ];
    }
    
    /**
     * increment version of ldb version
     */
    public static function incrementDWVersion()
    {
        $item = CoreConfigData::getItem(ProjConst::KEY_CK_DAYWORK);
        $value = (int) $item->value;
        if ($value > 9e100) {
            $value = 1;
        } else {
            $value++;
        }
        $item->value = $value;
        $item->save();
    }
    
    /**
     * check same version day work version
     * 
     * @return boolean
     */
    public static function isSameDWVersion()
    {
        $keyLocal = ProjConst::KEY_CK_DAYWORK . '-ver';
        $versServer = CoreConfigData::getValueDb(ProjConst::KEY_CK_DAYWORK);
        $verLocal = CookieCore::get($keyLocal);
        if ($versServer == $verLocal) {
            return true;
        }
        CookieCore::set($keyLocal, $versServer, 30);
        CookieCore::forgetPrefix(ProjConst::KEY_CK_DAYWORK);
        return false;
    }
    
    /**
     * get default filter reward report date
     * @return datetime
     */
    public static function getDateDefaultRewardFilter() {
        $now = Carbon::now();
        if ($now->day <= ProjConst::DAY_REWARD_PAID) {
            $now->modify('-1 month');
        }
        return $now;
    }

    /**
     * get team follow employees
     *
     * @param collection $projMembers
     * @return collection
     */
    public static function getTeamOfMembers($projMembers)
    {
        $employeeIds = [];
        foreach ($projMembers as $item) {
            $employeeIds[$item->employee_id] = $item->employee_id;
        }
        return self::getTeamOfEmployees($employeeIds);
    }
    
    /**
     * get team follow employees
     *
     * @param array $employeeIds
     * @param null|array|string $addSelect
     * @return collection
     */
    public static function getTeamOfEmployees($employeeIds, $addSelect = null)
    {
        if (!count($employeeIds)) {
            return [];
        }
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $collection = Team::select(['t_tm.team_id', 't_tm.employee_id'])
            ->join($tblTeamMember . ' AS t_tm', 't_tm.team_id', '=', $tblTeam.'.id')
            ->whereIn('t_tm.employee_id', $employeeIds);
        if ($addSelect) {
            $collection->addSelect($addSelect);
        }
        return $collection->get();
    }
}
