<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;

class ComeLateDayWeek extends CoreModel
{
    CONST MONDAY = 1;
    CONST TUESDAY = 2;
    CONST WEDNESDAY = 3;
    CONST THURSDAY = 4;
    CONST FRIDAY = 5;

    protected $table = 'come_late_day_weeks';

    /**
     * [get all days apply for register]
     * @param  [int] $registerId 
     * @return [int] array  
     */
    public static function getDaysApply($registerId)
    {
    	$registerTable = ComeLateRegister::getTableName();
        $registerTableAs = 'come_late_register_table';
        $registerDayWeekTable = self::getTableName();
        $registerDayWeekAs = $registerDayWeekTable;

    	$days = self::select("{$registerDayWeekAs}.day as day")
    			->join("{$registerTable} as {$registerTableAs}", "{$registerTableAs}.id", '=', "{$registerDayWeekAs}.come_late_id")
    			->where("{$registerTableAs}.id", $registerId)
    			->get();

    	return $days;
    }
}