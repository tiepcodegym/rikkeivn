<?php

namespace Rikkei\Files\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;

class ManageFileGroupEmail extends CoreModel
{
    protected $table = 'manage_file_group_email';

    public static function getGroupEmail($registerId)
    {
        $groupEmail = self::where('register_id', $registerId)->first();
        if ($groupEmail) {
            return explode(';', $groupEmail->group_email);
        }
        return $groupEmail;
    }
}
