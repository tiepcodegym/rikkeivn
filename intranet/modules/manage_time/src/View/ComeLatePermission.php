<?php

namespace Rikkei\ManageTime\View;

use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\Model\Employee;
use Rikkei\ManageTime\Model\ComeLateRegister;
use Rikkei\ManageTime\Model\ComeLateRelater;
use Rikkei\ManageTime\Model\ComeLateTeam;
use Rikkei\ManageTime\View\ManageTimeCommon;

class ComeLatePermission
{
    /**
     * [isAllowView: check view register information]
     * @param  [int]  $registerId
     * @param  [int]  $employeeId
     * @return boolean
     */
    public static function isAllowView($registerId, $employeeId)
    {
        $registerRecord = ComeLateRegister::getInformationRegister($registerId);

        if (self::isScopeManageOfCompany() || self::isScopeApproveOfCompany() || self::allowCreateEditOther()) {
            return true;
        }

        if ($employeeId == $registerRecord->creator_id || $employeeId == $registerRecord->approver_id) {
            return true;
        }

        $relatedPersons = ComeLateRelater::getRelatedPersons($registerId);
        if (count($relatedPersons)) {
            foreach ($relatedPersons as $item) {
                if ($item->relater_id == $employeeId) {
                    return true;
                }
            }
        }

        if (self::isScopeManageOfTeam() || self::isScopeApproveOfTeam()) {
            $teamIds = ManageTimeCommon::getArrTeamIdByEmployee($employeeId);
            $registerTeams = ComeLateTeam::getTeams($registerId);
            foreach ($registerTeams as $team) {
                if (in_array($team->team_id, $teamIds)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * [isAllowApprove: check approve register]
     * @param  [int]  $registerId
     * @param  [int]  $employeeId
     * @return boolean
     */
    public static function isAllowApprove($register, $employeeId)
    {
        if (self::isScopeApproveOfCompany() || (self::isScopeApproveOfSelf() && $register->approver == $employeeId)) {
            return true;
        }
        if (self::isScopeApproveOfTeam()) {
            if ($register->approver == $employeeId) {
                return true;
            }
            $teamIds = ManageTimeCommon::getArrTeamIdByEmployee($employeeId);
            $registerTeams = ComeLateTeam::getTeams($register->id);
            foreach ($registerTeams as $team) {
                if (in_array($team->team_id, $teamIds)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * [isScopeManageOfTeam: is scope of team to manage register]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeManageOfTeam($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'manage_time::manage-time.manage.view');
    }

    /**
     * [isScopeManageOfCompany: is scope of company to manage register]
     * @return boolean
     */
    public static function isScopeManageOfCompany()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::manage-time.manage.view');
    }

    /**
     * [isScopeApproveOfTeam: is scope of self to approve register]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeApproveOfSelf($teamId = null)
    {
        return Permission::getInstance()->isScopeSelf($teamId, 'manage_time::manage-time.manage.comelate.approve');
    }

    /**
     * [isScopeApproveOfTeam: is scope of team to approve register]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeApproveOfTeam($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'manage_time::manage-time.manage.comelate.approve');
    }

    /**
     * [isScopeApproveOfCompany: is scope of company to approve register]
     * @return boolean
     */
    public static function isScopeApproveOfCompany()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::manage-time.manage.comelate.approve');
    }

    /**
     * Check permission create or edit for other employee
     * @return [boolean]
     */
    public static function allowCreateEditOther()
    {
        return Permission::getInstance()->isAllow('manage_time::manage.create.other');
    }
}
