<?php

namespace Rikkei\ManageTime\View;

use Rikkei\Team\View\Permission;
use Rikkei\ManageTime\Model\LeaveDayHistories;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\LeaveDayRelater;
use Rikkei\ManageTime\Model\LeaveDayTeam;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Auth;
use Rikkei\ManageTime\Model\LeaveDay;

class LeaveDayPermission
{
    /**
     * [isAllowView: check view register information]
     * @param $registerId
     * @param $employeeId
     * @param null $registerRecord
     * @return bool
     */
    public static function isAllowView($registerId, $employeeId, $registerRecord = null)
    {
        if (empty($registerRecord)) {
            $registerRecord = LeaveDayRegister::getInformationRegister($registerId);
        }

        if (self::isScopeManageOfCompany() || self::isScopeApproveOfCompany() || self::allowCreateEditOther()) {
            return true;
        }

        if ($employeeId == $registerRecord->creator_id || $employeeId == $registerRecord->approver_id) {
            return true;
        }

        if ($registerRecord->substitute_id) {
            if ($employeeId == $registerRecord->substitute_id) {
                return true;
            }
        }

        $relatedPersons = LeaveDayRelater::getRelatedPersons($registerId);
        if (count($relatedPersons)) {
            foreach ($relatedPersons as $item) {
                if ($item->relater_id == $employeeId) {
                    return true;
                }
            }
        }

        if (self::isScopeManageOfTeam() || self::isScopeApproveOfTeam()) {
            $teamIds = ManageTimeCommon::getArrTeamIdByEmployee($employeeId);
            $registerTeams = LeaveDayTeam::getTeams($registerId);
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
        if (self::isScopeApproveOfCompany() || (self::isScopeApproveOfSelf() && $register->approver_id == $employeeId)) {
            return true;
        }

        if (self::isScopeApproveOfTeam()) {
            if ($register->approver_id == $employeeId) {
                return true;
            }
            $teamIds = ManageTimeCommon::getArrTeamIdByEmployee($employeeId);
            $registerTeams = LeaveDayTeam::getTeams($register->id);
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
        return Permission::getInstance()->isScopeSelf($teamId, 'manage_time::manage-time.manage.leave_day.approve');
    }

    /**
     * [isScopeApproveOfTeam: is scope of team to approve register]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeApproveOfTeam($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'manage_time::manage-time.manage.leave_day.approve');
    }

    /**
     * [isScopeApproveOfCompany: is scope of company to approve register]
     * @return boolean
     */
    public static function isScopeApproveOfCompany()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::manage-time.manage.leave_day.approve');
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
     * Save changes history
     *
     * @param array $changes
     * @param int $type
     *
     * @return void
     */
    public function saveHistory($empId, $changes, $type)
    {
        $currentLogId = Auth::id();
        $history = new LeaveDayHistories();
        $history->setData([
            'employee_id' => $empId,
            'content' => json_encode($changes),
            'type' => $type,
            'created_by' => $currentLogId,
        ]);

        $history->save();
    }

    /**
     * Get fields changed
     *
     * @param int $employeeId
     * @param float $oldDayOt
     * @param float $newDayOt
     * @return array|null   if content is empty then return null
     */
    public static function getFieldsChanged($employeeId, $fields)
    {
        $leaveDays = new LeaveDay();
        $leaveDays->employee_id = $employeeId;
        foreach ($fields as $field => $value) {
            $leaveDays->$field = $value['old'];
            $newData[$field] = $value['new'];
        }

        $leaveDayPermis = new LeaveDayPermission();
        $change = $leaveDayPermis->findChanges($leaveDays, $newData);
        return count($change) ? $change : null;
    }

    public function findChanges($oldObject, $newArray)
    {
        $changes = [];
        foreach ($newArray as $column => $value) {
            if ($this->isFieldIgnoreHistory($column)) {
                continue;
            }
            if ($oldObject->$column != $value) {
                $changes[$column] = [
                    'old' => $oldObject->$column,
                    'new' => $value,
                ];
            }
        }

        return $changes;
    }

    /**
     * Check field is ignore compare
     *
     * @param string $field
     *
     * @return boolean
     */
    public function isFieldIgnoreHistory($field)
    {
        return in_array($field, $this->ignoreHistory());
    }

    /**
     * Field ignore compare
     *
     * @return array
     */
    public function ignoreHistory()
    {
        return ['updated_at', 'note', 'leave_day_ot', 'employee_id'];
    }

    /**
     * Check permission view leave day histories
     *
     * @return boolean
     */
    public static function isAllowViewHistories()
    {
        $route = 'manage_time::admin.manage-day-of-leave.index';
        return Permission::getInstance()->isScopeCompany(null, $route)
                || Permission::getInstance()->isScopeTeam(null, $route);
    }

    /**
     * [isScopeAcquisitionOfSelf: is scope of self to acquisition status]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeAcquisitionOfSelf($teamId = null)
    {
        return Permission::getInstance()->isScopeSelf($teamId, 'manage_time::profile.leave.acquisition-status');
    }

    /**
     * [isScopeAcquisitionOfTeam: is scope of team to acquisition status]
     * @param  [int]  $teamId
     * @return boolean
     */
    public static function isScopeAcquisitionOfTeam($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'manage_time::profile.leave.acquisition-status');
    }

    /**
     * [isScopeAcquisitionOfCompany: is scope of company to acquisition status]
     * @return boolean
     */
    public static function isScopeAcquisitionOfCompany()
    {
        return Permission::getInstance()->isScopeCompany(null, 'manage_time::profile.leave.acquisition-status');
    }
}
