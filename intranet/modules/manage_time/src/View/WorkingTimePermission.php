<?php

namespace Rikkei\ManageTime\View;

use Rikkei\ManageTime\Model\WorkingTime as WorkingTimeModel;
use Rikkei\ManageTime\View\WorkingTime;
use Rikkei\ManageTime\Model\WorkingTimeRegister;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\TeamMember;

class WorkingTimePermission
{
    /*
     * check permission from list
     */
    public static function permissEditItems($ids, $createdBy = [])
    {
        $routeManage = WorkingTime::ROUTE_MANAGE;
        $scope = Permission::getInstance();
        if ($scope->isScopeCompany(null, $routeManage)) {
            if ($createdBy) {
                return true;
            }
            return 'company';
        }
        if ($scope->isScopeTeam(null, $routeManage)) {
            $currUser = $scope->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $wkIds = WorkingTimeRegister::from(WorkingTimeRegister::getTableName() . ' as working_time_registers')
                    ->join(TeamMember::getTableName() . ' as tmb', 'working_time_registers.employee_id', '=', 'tmb.employee_id')
                    ->where(function ($query) use ($teamIds, $currUser) {
                        $query->whereIn('tmb.team_id', $teamIds)
                                ->orWhere('working_time_registers.employee_id', '=', $currUser->id)
                                ->orWhere('working_time_registers.updated_by', '=', $currUser->id);
                    })
                    ->whereIn('working_time_registers.id', $ids)
                    ->lists('working_time_registers.id')
                    ->toArray();
            if ($createdBy) {
                if ($wkIds) {
                    return true;
                }
                return false;
            }
            return $wkIds;
        }
        if ($createdBy) {
            return in_array($scope->getEmployee()->id, $createdBy);
        }
        return 'self';
    }

    /*
     * check permiss edit foreach item
     */
    public static function checkPermissInList($id, $listPermiss = [], $createdBy = [])
    {
        if ($listPermiss == 'company') {
            return true;
        }
        if (is_array($listPermiss) && in_array($id, $listPermiss)) {
            return true;
        }
        if ($listPermiss == 'self') {
            return in_array(auth()->id(), $createdBy);
        }
        return false;
    }

    /*
     * check permiss approve foreach item
     */
    public static function permissApproveItems($ids, $approverId = [])
    {
        $approverId = is_array($approverId) ? $approverId : [$approverId];
        $routeApprove = WorkingTime::ROUTE_APPROVE;
        $scope = Permission::getInstance();
        if ($scope->isScopeCompany(null, $routeApprove)) {
            if ($approverId) {
                return true;
            }
            return 'company';
        }
        if ($scope->isScopeTeam(null, $routeApprove)) {
            $currUser = $scope->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $wkIds = WorkingTimeRegister::from(WorkingTimeRegister::getTableName() . ' as working_time_registers')
                    ->join(TeamMember::getTableName() . ' as tmb', 'working_time_registers.employee_id', '=', 'tmb.employee_id')
                    ->where(function ($query) use ($teamIds, $currUser) {
                        $query->whereIn('tmb.team_id', $teamIds)
                                ->orWhere('working_time_registers.approver_id', '=', $currUser->id);
                    })
                    ->whereIn('working_time_registers.id', $ids)
                    ->lists('working_time_registers.id')
                    ->toArray();
            if ($approverId) {
                if ($wkIds) {
                    return true;
                }
                return false;
            }
            return $wkIds;
        }
        if ($approverId) {
            return in_array($scope->getEmployee()->id, $approverId);
        }
        return 'self';
    }

    /*
     * check permiss approve in list
     */
    public static function checkApproveInList($id, $listPermiss = [], $approverId = [])
    {
        $approverId = is_array($approverId) ? $approverId : [$approverId];
        if ($listPermiss == 'company') {
            return true;
        }
        if (is_array($listPermiss) && in_array($id, $listPermiss)) {
            return true;
        }
        if ($listPermiss == 'self') {
            return in_array(auth()->id(), $approverId);
        }
        return false;
    }
}
