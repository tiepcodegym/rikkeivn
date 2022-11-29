<?php

namespace Rikkei\Core\Model;

use Carbon\Carbon;
use DB;

class ForgotTurnOff extends CoreModel
{
    protected $table = 'forgot_turn_off';

    protected $fillable = [
        'employee_id', 'forgot_date', 'amount', 'ip_address', 'computer_name', 'area',
    ];

    public static function getFinesLastMonth()
    {
        // get last month
        $lastMonth = Carbon::now()->subMonth(1);
        $firstMonth = $lastMonth->firstOfMonth()->format('Y-m-d');
        $endMonth = $lastMonth->lastOfMonth()->format('Y-m-d');

        return self::select(DB::raw('employee_id, SUM(amount) as total_amount, COUNT(*) as count'))
            ->where('forgot_date', '>=', $firstMonth)
            ->where('forgot_date', '<=', $endMonth)
            ->groupBy('employee_id')
            ->get();
    }
}