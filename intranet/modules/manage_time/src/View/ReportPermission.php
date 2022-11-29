<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Rikkei\ManageTime\View;

use Rikkei\Team\View\Permission;

/**
 * Description of ReportPermission
 *
 * @author HuongPV - Pro
 */
class ReportPermission
{

    /**
     * [isScopeManageOfCompany: is scope of company to manage report]
     * @return boolean
     */
    public static function isScopeManageOfCompany()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::timekeeping.manage.report');
    }

    /**
     * [isScopeManageOfTeam: is scope of team to manage report]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeManageOfTeam($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'manage_time::timekeeping.manage.report');
    }

    /**
     * [isScopeApproveOfTeam: is scope of self to approve register]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeApproveOfSelf($teamId = null)
    {
        return Permission::getInstance()->isScopeSelf($teamId, 'manage_time::timekeeping.manage.report');
    }

}
