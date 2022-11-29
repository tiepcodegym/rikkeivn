<?php

namespace Rikkei\Assets\View;

use DB;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Permission as PermissionsView;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Assets\Model\RequestAsset;
use Rikkei\Assets\Model\RequestAssetHistory;
use Rikkei\Assets\Model\RequestAssetItem;
use Rikkei\Assets\Model\AssetsHistoryRequest;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\View;
use Carbon\Carbon;
use Rikkei\Assets\Model\RequestAssetItemsWarehouse;
use Rikkei\Team\Model\EmployeeTeamHistory;

class RequestView
{
    /**
     * Get petitioner information
     * @param [int] $employeeId
     * @return [model]
     */
    public static function getPetitionerInfo($employeeId)
    {
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';
        $employeeTable = Employee::getTableName();
        $teamMemberTable = TeamMember::getTableName();

        if (empty($employeeId)) {
            return null;
        }

        return Employee::select(
            "{$employeeTable}.id",
            "{$employeeTable}.email",
            "{$employeeTable}.id as employee_id",
            "{$teamTableAs}.name as employee_group",
            "{$employeeTable}.employee_code as employee_code",
            "{$employeeTable}.name as employee_name",
            "{$employeeTable}.email as employee_email",
            DB::raw("GROUP_CONCAT(DISTINCT " . "CONCAT(`{$roleTableAs}`.`role`, ' - ', `{$teamTableAs}`.`name`)" . " SEPARATOR'; ')" . " as role_name")
        )
            ->join("{$teamMemberTable}", "{$employeeTable}.id", '=', "{$teamMemberTable}.employee_id")
            ->join("{$roleTable} as {$roleTableAs}", "{$roleTableAs}.id", '=', "{$teamMemberTable}.role_id")
            ->join("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", '=', "{$teamMemberTable}.team_id")
            ->where("{$teamMemberTable}.employee_id", $employeeId)
            ->first();
    }

    /**
     * Get review for employee create request
     * @param [int] $employeeId
     * @return [array]
     */
    public static function getReviewersByEmployee($employeeId, $reviewerId = null)
    {
        $route = 'request.asset.review';
        $employee = Employee::find($employeeId);
        $employeeTable = Employee::getTableName();
        $teamMemberTable = TeamMember::getTableName();

        if (!$employee) {
            return null;
        }
        $roleIds = [Team::ROLE_TEAM_LEADER];
        $teamIds = TeamMember::join("{$employeeTable}", "{$employeeTable}.id", '=', "{$teamMemberTable}.employee_id")
            ->where("{$teamMemberTable}.employee_id", $employee->id)
            ->lists("{$teamMemberTable}.team_id")
            ->toArray();
        $collect = Employee::select("{$employeeTable}.id", "{$employeeTable}.name", "{$employeeTable}.email")
            ->join("{$teamMemberTable}", "{$employeeTable}.id", '=', "{$teamMemberTable}.employee_id")
            ->whereIn("{$teamMemberTable}.team_id", $teamIds)
            ->whereIn("{$teamMemberTable}.role_id", $roleIds)
            ->whereNull("{$employeeTable}.leave_date")
            ->distinct("{$employeeTable}.id")
            ->first();
        $actionId = Action::where('name', '=', $route)->first()->id;
        if ($collect) {
            $leaderReview = RequestAssetPermission::searchEmployeeAjaxReview($collect->name, $employee->id);
            if ($leaderReview && count($leaderReview["items"])) {
                return $collect;
            }
        }
        return;
    }

    /**
     * Check employee is sub-leader
     * @param [int] $employeeId
     * @return boolean
     */
    public static function isSubLeader($employeeId)
    {
        return TeamMember::where('employee_id', $employeeId)
            ->where('role_id', Team::ROLE_SUB_LEADER)
            ->count();
    }

    /*
     * Get all team of employee
     */
    public static function getTeamsByEmployee($employeeId)
    {
        return TeamMember::select('team_id')
            ->where('employee_id', $employeeId)
            ->get();
    }

    /**
     * View detail request asset
     * @param [int] $requestId
     * @return [view]
     */
    public static function viewRequest($requestId)
    {
        $empTeamHistoryTbl = EmployeeTeamHistory::getTableName();
        $teamTbl = Team::getTableName();
        $userCurrent = PermissionsView::getInstance()->getEmployee();
        $requestAsset = RequestAsset::getRequestDetail($requestId);
        if (!$requestAsset) {
            return redirect()->route('asset::resource.request.index')->withErrors(Lang::get('asset::message.Not found item'));;
        }
        $requestAssetHistoriesAllocate = RequestAssetHistory::where('request_id', '=', $requestAsset->id)->where('action', "=", RequestAssetHistory::ACTION_ALLOCATE)->first();
        $assetsHistoryRequests = null;
        if (isset($requestAssetHistoriesAllocate) && $requestAssetHistoriesAllocate) {
            $assetsHistoryRequests = AssetsHistoryRequest::getAssetHistoriesByRequestId($requestAssetHistoriesAllocate->id);
        }
        if (!RequestAssetPermission::isAllowViewRequest($requestAsset, $userCurrent->id)) {
            View::viewErrorPermission();
        }
        if ($requestAsset->request_date) {
            $requestAsset->request_date = Carbon::createFromFormat('Y-m-d', $requestAsset->request_date)->format('d-m-Y');
        }

        $reqIt = RequestAssetItemsWarehouse::where('request_id', $requestId)->where('status', RequestAssetItemsWarehouse::STATUS_UNALLOCATE)->first();
        $mainTeamCurrent = EmployeeTeamHistory::select(["{$empTeamHistoryTbl}.id", "{$empTeamHistoryTbl}.team_id", "{$empTeamHistoryTbl}.is_working", "{$teamTbl}.name", "{$teamTbl}.branch_code"])
            ->join("{$teamTbl}", "{$teamTbl}.id", '=', "{$empTeamHistoryTbl}.team_id")
            ->whereNull("{$empTeamHistoryTbl}.deleted_at")
            ->where("{$empTeamHistoryTbl}.employee_id", $userCurrent->id)
            ->where("{$empTeamHistoryTbl}.is_working", EmployeeTeamHistory::IS_WORKING)->first();

        $params = [
            'requestAsset' => $requestAsset,
            'requestAsssetItem' => RequestAssetItem::getRequestAssetItems($requestAsset, true),
            'requestAssetHistories' => RequestAssetHistory::getHistoriesByRequestId($requestAsset->id),
            'assetsHistoryRequests' => $assetsHistoryRequests,
            'regionOfEmp' => AssetView::getRegionByEmp($requestAsset->employee_id),
            'branchs' => AssetView::getAssetBranch(),
            'reqIt' => $reqIt,
            'mainTeamCurrent' => $mainTeamCurrent,
        ];
        return view('asset::request.view')->with($params);
    }
}
