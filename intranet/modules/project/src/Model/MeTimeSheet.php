<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Carbon\Carbon;
use DB;

class MeTimeSheet extends CoreModel
{
    protected $table = 'me_timesheets';
    protected $fillable = ['employee_email', 'date', 'late_time', 'shift'];
    public $timestamps = false;

    /**
     * get late time by email
     * @param type $email
     * @param type $time
     * @return type
     */
    public static function getTimeLateByEmail($email, $time)
    {
        if (!$time) {
            return 0;
        }
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        return self::where('employee_email', $email)
                ->where(DB::raw('DATE_FORMAT(date, "%Y-%m")'), $time->format('Y-m'))
                ->count();
    }

    /**
     * check mont exist timesheet
     * @param mixed $time
     * @param boolean $prev
     * @return boolean
     */
    public static function checkExistsMonth($time, $prev = false) 
    {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        if ($prev) {
            $time->subMonthNoOverflow();
        }
        $exits = self::where(DB::raw('DATE_FORMAT(date, "%Y-%m")'), $time->format('Y-m'))
                    ->first();
        if ($exits) {
            return true;
        }
        return false;
    }
}
