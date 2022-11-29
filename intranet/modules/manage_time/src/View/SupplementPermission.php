<?php

namespace Rikkei\ManageTime\View;

use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\Model\Employee;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\ManageTime\Model\SupplementRelater;
use Rikkei\ManageTime\Model\SupplementTeam;
use Rikkei\ManageTime\View\ManageTimeCommon;

class SupplementPermission
{
    /**
     * [isAllowView: check view register information]
     * @param  [int]  $registerId
     * @param  [int]  $employeeId
     * @return boolean
     */
    public static function isAllowView($registerId, $tagEmployeeInfo, $employeeId)
    {
        $registerRecord = SupplementRegister::getInformationRegister($registerId);

        if (static::isScopeManageOfCompany() || static::isScopeApproveOfCompany()) {
            return true;
        }

        if (static::isAllowViewDetail($registerRecord, $tagEmployeeInfo, $employeeId)) {
            return true;
        }

        $relatedPersons = SupplementRelater::getRelatedPersons($registerId);
        if (count($relatedPersons)) {
            foreach ($relatedPersons as $item) {
                if ($item->relater_id == $employeeId) {
                    return true;
                }
            }
        }

        if (self::isScopeManageOfTeam() || self::isScopeApproveOfTeam()) {
            $teamIds = ManageTimeCommon::getArrTeamIdByEmployee($employeeId);
            $registerTeams = SupplementTeam::getTeams($registerId);
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

        if (self::isScopeApproveOfTeam()) {
            if ($register->approver_id == $employeeId) {
                return true;
            }
            $teamIds = ManageTimeCommon::getArrTeamIdByEmployee($employeeId);
            $registerTeams = SupplementTeam::getTeams($register->id);
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
        return Permission::getInstance()->isScopeSelf($teamId, 'manage_time::manage-time.manage.supplement.approve');
    }

    /**
     * [isScopeApproveOfTeam: is scope of team to approve register]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeApproveOfTeam($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'manage_time::manage-time.manage.supplement.approve');
    }

    /**
     * [isScopeApproveOfCompany: is scope of company to approve register]
     * @return boolean
     */
    public static function isScopeApproveOfCompany()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::manage-time.manage.supplement.approve');
    }

    /**
     * Check permission create or edit for other employee
     * @return [boolean]
     */
    public static function allowCreateEditOther()
    {
        return Permission::getInstance()->isAllow('manage_time::manage.create.other');
    }

    /**
     * Check current employee logging can edit register or not
     * 
     * @param SupplementRegister $registerRecord
     * @param int $employeeId id of current user logging
     *
     * @return boolean
     */
    public static function isCanEditDetail($registerRecord, $employeeId)
    {
        return static::isAllowEditDetail($registerRecord, $employeeId)
            && !in_array($registerRecord->status, [
                SupplementRegister::STATUS_APPROVED,
                SupplementRegister::STATUS_CANCEL,
            ]);
    }

    /**
     * Check current employee logging has been allowed to edit this register
     *
     * @param SupplementRegister $registerRecord
     * @param int $employeeId id of current user logging
     *
     * @return boolean
     */
    public static function isAllowEditDetail($registerRecord, $employeeId)
    {
        return $employeeId == $registerRecord->creator_id || static::allowCreateEditOther();
    }

    /**
     * Check current employee logging has been viewed this register
     *
     * @param SupplementRegister $registerRecord
     * @param SupplementEmployee $tagEmployeeInfo
     * @param int $employeeId id of current user logging
     *
     * @return boolean
     */
    public static function isAllowViewDetail($registerRecord, $tagEmployeeInfo, $employeeId)
    {
        foreach ($tagEmployeeInfo as $tagEmp) {
            if ($employeeId == $tagEmp->employee_id) {
                return true;
            }
        }

        return static::isAllowEditDetail($registerRecord, $employeeId);
    }
}
