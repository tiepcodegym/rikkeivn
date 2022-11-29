<?php

namespace Rikkei\Recruitment\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Rikkei\Recruitment\Model\RecruitmentApplies;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;

class RecruitmentAppliesController extends \Rikkei\Core\Http\Controllers\Controller
{
    /**
     * get presenter follow phone
     *  load ajax
     */
    public function getPresenter()
    {
        if (! Permission::getInstance()->isAllow('team::team.member.edit') || 
            ! Permission::getInstance()->isAllow('team::team.member.create') ||
            ! Permission::getInstance()->isAllow('team::team.member.save')) {
            echo '';
            exit;
        }
        $phone = Input::get('phone');
        if (! $phone) {
            echo '';
            exit;
        }
        echo RecruitmentApplies::getPresenterName($phone);
        exit;
    }
}
