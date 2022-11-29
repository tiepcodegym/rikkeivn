<?php

namespace Rikkei\Ot\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;

class OtBreakTime extends CoreModel
{
    protected $table = 'ot_break_times';

    /*
     * Get break time by employee register
     */
    public static function getBreakTimesByRegister($registerId, $employeeId)
    {
        return self::select('ot_date', 'break_time')
            ->where('ot_register_id', $registerId)
            ->where('employee_id', $employeeId)
            ->get();
    }
}