<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;

class LeaveDayGroupEmail extends CoreModel
{
    protected $table = 'leave_day_group_email';

    public static function getGroupEmail($registerId)
    {
        $groupEmail = self::where('register_id', $registerId)->first();
        if ($groupEmail) {
            return explode(';', $groupEmail->group_email);
        }
        return $groupEmail;
    }
}
