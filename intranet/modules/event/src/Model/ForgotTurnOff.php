<?php

namespace Rikkei\Event\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;

class ForgotTurnOff extends CoreModel
{

    protected $table = 'forgot_turn_off';

    const MAX_EMAIL_IMPORT = 1000;

    public static function insertForgotDate($data)
    {
        $response = [
            'employee_id' => $data['employee_id'],
            'forgot_date' => $data['date'],
            'ip_address' => $data['ip_address'],
            'computer_name' => $data['computername'],
            'area' => $data['area'],
            'amount' => '10000',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        $item = self::where('employee_id', $data['employee_id'])
            ->where('forgot_date', $data['date'])
            ->first();
        if (!isset($item)) {
            self::insert($response);

        }
    }

}
