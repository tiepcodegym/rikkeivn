<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;

class ManageTimeSettingUser extends CoreModel
{    
    protected $table = 'manage_time_setting_users';
    protected $guarded = [];
    public $timestamps = false;

}
