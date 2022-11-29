<?php


namespace Rikkei\AdminSetting\Model;


use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;

class MobileConfig extends CoreModel
{
    protected $guarded = [];

    const CONFESSION_ID = 1;
    const MARKET_ID = 2;
    const GIFT_ID = 3;
    const PROPOSED_ID = 4;

    public function mobileConfigUsers()
    {
        return $this->belongsToMany(Employee::class, 'mobile_config_users')->select(['employees.id', 'name'], 'email');
    }
}