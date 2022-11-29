<?php

namespace Rikkei\ManageTime\View;

use Rikkei\Team\View\Permission;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\Model\BusinessTripRelater;
use Rikkei\ManageTime\Model\BusinessTripTeam;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\SupplementPermission;

class MissionPermission
{
    /**
     * [isAllowView: check view register information]
     * @param  [int]  $registerId
     * @param  [int]  $employeeId
     * @return boolean
     */
    public static function isAllowView($registerId, $tagEmployeeInfo, $employeeId)
    {
        $registerRecord = BusinessTripRegister::getInformationRegister($registerId);

        if (self::isScopeManageOfCompany() || self::isScopeApproveOfCompany() || self::allowCreateEditOther()) {
            return true;
        }

        if (SupplementPermission::isAllowViewDetail($registerRecord, $tagEmployeeInfo, $employeeId)) {
            return true;
        }

        $relatedPersons = BusinessTripRelater::getRelatedPersons($registerId);
        if (count($relatedPersons)) {
            foreach ($relatedPersons as $item) {
                if ($item->relater_id == $employeeId) {
                    return true;
                }
            }
        }

        if (self::isScopeManageOfTeam() || self::isScopeApproveOfTeam()) {
            $teamIds = ManageTimeCommon::getArrTeamIdByEmployee($employeeId);
            $registerTeams = BusinessTripTeam::getTeams($registerId);
            foreach ($registerTeams as $team) {
                if (in_array($team->team_id, $teamIds)) {
                    return true;
                }
            }
        }

        return $employeeId == $registerRecord->approver_id;
    }

    /**
     * [isAllowApprove: check approve register]
     * @param  [int]  $registerId
     * @param  [int]  $employeeId
     * @return boolean
     */
    public static function isAllowApprove($register, $employeeId)
    {
        if (self::isScopeApproveOfCompany() || (self::isScopeApproveOfSelf() && $register->approver_id == $employeeId)) {
            return true;
        }

        if(self::isScopeApproveOfTeam()) {
            if ($register->approver_id == $employeeId) {
                return true;
            }
            $teamIds = ManageTimeCommon::getArrTeamIdByEmployee($employeeId);
            $registerTeams = BusinessTripTeam::getTeams($register->id);
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
        return Permission::getInstance()->isScopeSelf($teamId, 'manage_time::manage-time.manage.mission.approve');
    }

    /**
     * [isScopeApproveOfTeam: is scope of team to approve register]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeApproveOfTeam($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'manage_time::manage-time.manage.mission.approve');
    }

    /**
     * [isScopeApproveOfCompany: is scope of company to approve register]
     * @return boolean
     */
    public static function isScopeApproveOfCompany()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::manage-time.manage.mission.approve');
    }

    /**
     * Check permission create or edit for other employee
     * @return [boolean]
     */
    public static function allowCreateEditOther()
    {
        return Permission::getInstance()->isAllow('manage_time::manage.create.other');
    }

    public static function isAllowReport()
    {
        return Permission::getInstance()->isAllow('manage_time::timekeeping.manage.report-business-trip');
    }
}
