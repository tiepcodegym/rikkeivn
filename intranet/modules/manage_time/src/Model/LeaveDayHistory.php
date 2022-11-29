<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;

class LeaveDayHistory extends CoreModel
{
    protected $table = 'leave_day_history';

    public static function getLeaveAdded($timekeepingTableId)
    {
        return self::where('timekeeping_table_id', $timekeepingTableId)->get();
    }

    /**
     * 
     * @param int $timekeepingTableId
     * @param array $employeeIds
     */
    public static function deleteOldData($timekeepingTableId, $employeeIds)
    {
        return self::where('timekeeping_table_id', $timekeepingTableId)
            ->whereIn('employee_id', $employeeIds)
            ->delete();
    }
}