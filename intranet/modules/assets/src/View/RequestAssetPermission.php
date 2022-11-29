<?php

namespace Rikkei\Assets\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Rikkei\Assets\Model\RequestAsset;
use Rikkei\Assets\Model\RequestAssetTeam;
use Rikkei\Assets\View\AssetView;
use Rikkei\Core\Model\User;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Team\Model\Permission as PermissionModel;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Team\View\Permission;

class RequestAssetPermission
{
    /*
     * Permission create request scope company
     */
    public static function isScopeCompanyCreateRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeCompany($teamId, 'asset::resource.request.edit');
    }
    /*
     * Permission create request scope team
     */
    public static function isScopeTeamCreateRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'asset::resource.request.edit');
    }
    /*
     * Permission create request scope self
     */
    public static function isScopeSelfCreateRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeSelf($teamId, 'asset::resource.request.edit');
    }

    /*
     * check list of request ids permission, $createdBy use in edit page
     */
    public static function permissEditRequesets($requestIds = [], $createdBy = null)
    {
        $tblRq = RequestAsset::getTableName();
        $routeEdit = 'asset::resource.request.edit';
        $routeAllowcate = 'asset::asset.asset-allocation';
        $scope = Permission::getInstance();
        if ($scope->isAllow($routeAllowcate) || $scope->isScopeCompany(null, $routeEdit)) {
            if ($createdBy) {
                return true;
            }
            return 'company';
        }
        if ($scope->isScopeTeam(null, $routeEdit)) {
            $currUser = $scope->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $rqIds = RequestAsset::join(TeamMember::getTableName() . ' as tmb', $tblRq . '.created_by', '=', 'tmb.employee_id')
                    ->where(function ($query) use ($teamIds, $tblRq, $currUser) {
                        $query->whereIn('tmb.team_id', $teamIds)
                                ->orWhere($tblRq.'.created_by', '=', $currUser->id);
                    })
                    ->whereIn($tblRq . '.id', $requestIds)
                    ->lists($tblRq . '.id')
                    ->toArray();
            if ($createdBy) {
                if ($rqIds) {
                    return true;
                }
                return false;
            }
            return $rqIds;
        }
        if ($scope->isScopeSelf(null, $routeEdit)) {
            if ($createdBy) {
                return $createdBy == $scope->getEmployee()->id;
            }
            return 'self';
        }
        if ($createdBy && $createdBy == $scope->getEmployee()->id) {
            return true;
        }
        return [];
    }

    /*
     * check permission foreach item in list
     */
    public static function checkPermissInList($requestId, $listPermiss = [], $createdBy = null)
    {
        if ($listPermiss == 'company') {
            return true;
        }
        if (is_array($listPermiss) && in_array($requestId, $listPermiss)) {
            return true;
        }
        if ($listPermiss == 'self') {
            return $createdBy == auth()->id();
        }
        return false;
    }

    /*
     * Permission view list request scope company
     */
    public static function isScopeCompanyViewListRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeCompany($teamId, 'asset::resource.request.index');
    }

    /*
     * Permission view list request scope team
     */
    public static function isScopeTeamViewListRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'asset::resource.request.index');
    }

    /*
     * Permission view list request scope self
     */
    public static function isScopeSelfViewListRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeSelf($teamId, 'asset::resource.request.index');
    }

    /*
     * Permission view detail request scope company
     */
    public static function isScopeCompanyViewDetailRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeCompany($teamId, 'asset::resource.request.view');
    }

    /*
     * Permission view detail request scope team
     */
    public static function isScopeTeamViewDetailRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'asset::resource.request.view');
    }

    /*
     * Permission view detail request scope self
     */
    public static function isScopeSelfViewDetailRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeSelf($teamId, 'asset::resource.request.view');
    }

    /*
     * Permission view detail request
     * @param  [model] $requestAsset
     * @param  [int] $employeeId
     * @return boolean
     */
    public static function isAllowViewRequest($requestAsset, $employeeId = null)
    {
        if (!$employeeId) {
            $employeeId = Permission::getInstance()->getEmployee()->id;
        }
        if (in_array($employeeId, [$requestAsset->employee_id, $requestAsset->created_by, $requestAsset->reviewer])
                || self::isScopeCompanyViewDetailRequest()) {
            return true;
        } elseif (self::isScopeTeamViewDetailRequest()) {
            $teamIds = TeamMember::where('employee_id', $employeeId)->lists('team_id')->toArray();
            $teamCode = Employee::getNewestTeamCode($employeeId);
            $teamCode = explode('_', $teamCode)[0];

            $tblRqAsset = RequestAsset::getTableName();
            $hasItem = RequestAsset::leftJoin(TeamMember::getTableName() . ' as tmb', $tblRqAsset.'.employee_id', '=', 'tmb.employee_id')
                    ->leftJoin(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
                    ->leftJoin(TeamMember::getTableName() . ' as tmb_creator', $tblRqAsset.'.created_by', '=', 'tmb_creator.employee_id')
                    ->leftJoin(Team::getTableName() . ' as team_creator', 'tmb_creator.team_id', '=', 'team_creator.id')
                    ->where(function ($query) use ($teamIds, $teamCode) {
                        $query->whereIn('tmb.team_id', $teamIds)
                                ->orWhereIn('tmb_creator.team_id', $teamIds)
                                ->orWhere('team.code', 'like', $teamCode . '%')
                                ->orWhere('team_creator.code', 'like', $teamCode . '%');
                                if (in_array($teamCode, [Team::CODE_TEAM_IT, Team::CODE_PREFIX_HN]) ) {
                                    $query->orWhere('team.code' , Team::CODE_PREFIX_AI);
                                    $query->orWhere('team.code' , 'like', Team::CODE_PREFIX_ACADEMY . '%');
                                }
                    })
                    ->where($tblRqAsset . '.id', $requestAsset->id)
                    ->groupBy($tblRqAsset . '.id')
                    ->first();
            if ($hasItem) {
                return true;
            }
        } else {
        }
        return false;
    }

    /*
     * Permission review request scope company
     */
    public static function isScopeCompanyReviewRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeCompany($teamId, 'asset::resource.request.review');
    }

    /*
     * Permission review request scope team
     */
    public static function isScopeTeamReviewRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'asset::resource.request.review');
    }

    /*
     * Permission review request scope self
     */
    public static function isScopeSelfReviewRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeSelf($teamId, 'asset::resource.request.review');
    }

    /*
     * Permission review request
     * @param  [model] $requestAsset
     * @param  [int] $employeeId
     * @return boolean
     */
    public static function isAllowReviewRequest($requestAsset, $employeeId)
    {
        if (self::isScopeCompanyReviewRequest() || ($requestAsset->reviewer == $employeeId) && self::isScopeSelfReviewRequest()) {
            return true;
        } elseif (self::isScopeTeamReviewRequest()) {
            $teamIds = self::isScopeTeamReviewRequest();
            $teamCode = Employee::getNewestTeamCode($employeeId);
            $teamCode = explode('_', $teamCode)[0];

            $tblRqAsset = RequestAsset::getTableName();
            $hasItem = RequestAsset::leftJoin(TeamMember::getTableName() . ' as tmb', $tblRqAsset.'.reviewer', '=', 'tmb.employee_id')
                    ->leftJoin(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
                    ->leftJoin(TeamMember::getTableName() . ' as tmb_requester',  $tblRqAsset.'.employee_id', '=', 'tmb_requester.employee_id')
                    ->leftJoin(Team::getTableName() .' as team_requester', 'team_requester.id', '=', 'tmb_requester.team_id')
                    ->where(function ($query) use ($teamIds, $teamCode) {
                        $query->whereIn('tmb.team_id', $teamIds)
                                ->orWhere('team.code', 'like', $teamCode . '%');
                        if (in_array($teamCode, [Team::CODE_TEAM_IT, Team::CODE_PREFIX_HN])) {
                            $query->orWhere('team_requester.code', Team::CODE_PREFIX_AI);
                            $query->orWhere('team_requester.code' , 'like', Team::CODE_PREFIX_ACADEMY . '%');
                        }
                    })
                    ->where($tblRqAsset . '.id', $requestAsset->id)
                    ->groupBy($tblRqAsset . '.id')
                    ->first();
            if ($hasItem) {
                return true;
            }
        } else {
        }
        return false;
    }

    /*
     * Permission approve request scope company
     */
    public static function isScopeCompanyApproveRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeCompany($teamId, 'asset::resource.request.approve');
    }
    /*
     * Permission approve request scope team
     */
    public static function isScopeTeamApproveRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeTeam($teamId, 'asset::resource.request.approve');
    }
    /*
     * Permission approve request scope self
     */
    public static function isScopeSelfApproveRequest($teamId = null)
    {
        return Permission::getInstance()->isScopeSelf($teamId, 'asset::resource.request.approve');
    }

    /**
     * Permission for approver to approve request
     * @return boolean
     */
    public static function isAllowApproveRequest($requestAsset, $employeeId)
    {
        $route = 'asset::resource.request.approve';
        $scopeTeams = Permission::getInstance()->isScopeTeam(null, $route);
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            return true;
        } elseif ($scopeTeams) {
            $scopeTeams = is_array($scopeTeams) ? $scopeTeams : [];

            $tblRqAsset = RequestAsset::getTableName();
            $hasItem = RequestAsset::leftJoin(TeamMember::getTableName() . ' as tmb', $tblRqAsset.'.employee_id', '=', 'tmb.employee_id')
                    ->whereIn('tmb.team_id', $scopeTeams) // quyen duyet theo chi nhanh
                    ->where($tblRqAsset . '.id', $requestAsset->id)
                    ->groupBy($tblRqAsset . '.id')
                    ->first();
            if ($hasItem) {
                return true;
            }
        } else {
        }
        return false;
    }

    /*
     * Get employees can approve request
     */
    public static function getEmployeesCanApproveRequest($employee = null)
    {
        if (!$employee) {
            $employee = Permission::getInstance()->getEmployee();
        }
        $teamCode = $employee->newestTeamCode();
        $teamCode = explode('_', $teamCode)[0];
        if (!$teamCode) {
            $teamCode = Team::CODE_PREFIX_HN;
        }
        $action = 'request.asset.approve';
        $tblEmp = Employee::getTableName();
        $permissTbl = PermissionModel::getTableName();
        $actionTbl = Action::getTableName();

        return Employee::select($tblEmp . '.id as employee_id', $tblEmp . '.name as employee_name', $tblEmp . '.email as employee_email')
                ->leftJoin(TeamMember::getTableName() . ' as tmb', $tblEmp . '.id', '=', 'tmb.employee_id')
                ->leftJoin(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
                //team
                ->leftJoin($permissTbl . ' as permiss1', function ($join) {
                    $join->on('team.id', '=', 'permiss1.team_id')
                            ->on('tmb.role_id', '=', 'permiss1.role_id');
                })
                ->leftJoin($actionTbl . ' as action1', 'permiss1.action_id', '=', 'action1.id')
                //team follow
                ->leftJoin($permissTbl . ' as permiss2', function ($join) {
                    $join->on('team.follow_team_id', '=', 'permiss2.team_id')
                            ->on('tmb.role_id', '=', 'permiss2.role_id');
                })
                ->leftJoin($actionTbl . ' as action2', 'permiss2.action_id', '=', 'action2.id')
                //employee roles
                ->leftJoin(EmployeeRole::getTableName() . ' as emp_role', $tblEmp . '.id', '=', 'emp_role.employee_id')
                ->leftJoin($permissTbl . ' as permiss3', 'emp_role.role_id', '=', 'permiss3.role_id')
                ->leftJoin($actionTbl . ' as action3', 'permiss3.action_id', '=', 'action3.id')
                ->where(function ($query) use ($action, $teamCode) {
                    $query->where(function ($query1) use ($action, $teamCode) {
                        $query1->where('action1.name', $action)
                                ->where('permiss1.scope', '!=', PermissionModel::SCOPE_NONE)
                                ->where(function ($subQuery) use ($teamCode) {
                                    //if scope company or (team/self and same team code prefix)
                                    $subQuery->where('permiss1.scope', PermissionModel::SCOPE_COMPANY)
                                            ->orWhere('team.code', 'like', $teamCode . '%');
                                });
                    })
                    ->orWhere(function ($query2) use ($action, $teamCode) {
                        $query2->where('action2.name', $action)
                                ->where('permiss2.scope', '!=', PermissionModel::SCOPE_NONE)
                                ->where(function ($subQuery) use ($teamCode) {
                                    $subQuery->where('permiss2.scope', PermissionModel::SCOPE_COMPANY)
                                            ->orWhere('team.code', 'like', $teamCode . '%');
                                });
                    })
                    ->orWhere(function ($query3) use ($action, $teamCode) {
                        $query3->where('action3.name', $action)
                                ->where('permiss3.scope', '!=', PermissionModel::SCOPE_NONE)
                                ->where(function ($subQuery) use ($teamCode) {
                                    $subQuery->where('permiss3.scope', PermissionModel::SCOPE_COMPANY)
                                            ->orWhere('team.code', 'like', $teamCode . '%');
                                });
                    });
                })
                ->where(function ($query) use ($tblEmp) {
                    $query->whereNull($tblEmp.'.leave_date')
                            ->orWhereRaw($tblEmp . '.leave_date >= CURDATE()');
                })
                ->groupBy($tblEmp . '.id')
                ->get();
    }

    public static function searchEmployeeAjax($keySearch, array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 5,
        ];

        $userCurrent = Permission::getInstance()->getEmployee();
        $tblEmployee = Employee::getTableName();
        $tblUser = User::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $config = array_merge($configDefault, $config);
        $now = Carbon::now();
        $collection = Employee::select("{$tblEmployee}.id", "{$tblEmployee}.name", "{$tblEmployee}.email", "{$tblUser}.avatar_url")
            ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", '=', "{$tblEmployee}.id")
            ->whereNull("{$tblEmployee}.leave_date")
            ->where(function ($query) use ($tblEmployee, $keySearch) {
                $query->orWhere("{$tblEmployee}.email", 'LIKE', '%' . $keySearch . '%')
                    ->orWhere("{$tblEmployee}.name", 'LIKE', '%' . $keySearch . '%');
            })
            ->orderBy("{$tblEmployee}.email")
            ->distinct("{$tblEmployee}.id")
            ->where(function ($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            });
        if (self::isScopeCompanyCreateRequest()) {
            Employee::pagerCollection($collection, $config['limit'], $config['page']);
            $result['total_count'] = $collection->total();
            $result['incomplete_results'] = true;
            $result['items'] = [];
            foreach ($collection as $item) {
                $result['items'][] = [
                    'id' => $item->id,
                    'text' => $item->name . ' (' . AssetView::getNickName($item->email) . ')',
                    'avatar_url' => $item->avatar_url,
                ];
            }
            return $result;
        } elseif ($teamsByEmployee = self::isScopeTeamCreateRequest()) {
            if (is_array($teamsByEmployee)) {
                $collection = $collection->whereIn("{$tblTeamMember}.team_id", $teamsByEmployee);
            }
        } else {
            $collection = $collection->where("{$tblEmployee}.id", $userCurrent->id);
        }
        Employee::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->name . ' (' . AssetView::getNickName($item->email) . ')',
                'avatar_url' => $item->avatar_url,
            ];
        }
        return $result;
    }

    public static function searchEmployeeAjaxReview($keySearch, $employeeId = null, array $config = [])
    {
        $route = 'request.asset.review';
        $permissionModel = new PermissionModel();
        $collection = $permissionModel->getEmployeeByActionName($route);
        $collection->addSelect('avatar_url')
            ->leftJoin('users', 'users.employee_id', '=', 'employees.id')
            ->where(function ($query) use ($keySearch) {
                $query->where("employees.name", 'LIKE', "%{$keySearch}%");
                $query->orWhere("employees.email", 'LIKE', "%{$keySearch}%");
            });

        $resultCollection = $collection->get();

        $result['items'] = [];
        foreach ($resultCollection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->name . ' (' . AssetView::getNickName($item->email) . ')',
                'avatar_url' => $item->avatar_url,
            ];
        }
        return $result;
    }

    /**
     * check permission delete request
     *
     * @param object $requestIds
     * @return boolean
     */
    public static function checkPermissionDel($requestIds = null)
    {
        $route = 'asset::resource.request.delete-request';
        $employeeId = Permission::getInstance()->getEmployee()->id;
        $tblRqAsset = RequestAsset::getTableName();
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            return true;
        } elseif (Permission::getInstance()->isScopeTeam(null, $route)) {

            $teamIds = CheckpointPermission::getArrTeamIdByEmployee($employeeId);

            $collection = RequestAsset::leftJoin(TeamMember::getTableName() . ' as tmb', $tblRqAsset.'.employee_id', '=', 'tmb.employee_id')
                ->leftJoin(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
                ->leftJoin(TeamMember::getTableName() . ' as tmb_creator', $tblRqAsset.'.created_by', '=', 'tmb_creator.employee_id')
                ->leftJoin(Team::getTableName() . ' as team_creator', 'tmb_creator.team_id', '=', 'team_creator.id')
                ->where(function ($query) use ($teamIds, $tblRqAsset, $employeeId) {
                    $query->whereIn('tmb.team_id', $teamIds)
                        ->orWhereIn('tmb_creator.team_id', $teamIds)
                        ->orWhere("{$tblRqAsset}.created_by", $employeeId);
                });
            if ($requestIds) {
                $collection->whereIn($tblRqAsset . '.id', $requestIds);
            }
            return $collection->groupBy("{$tblRqAsset}.id")->pluck("{$tblRqAsset}.id")->toArray();

        } else {
            $collection = RequestAsset::where('created_by', $employeeId)->orWhere('employee_id', $employeeId);
            if ($requestIds) {
                $collection->whereIn($tblRqAsset . '.id', $requestIds);
            }
            return $collection->groupBy("{$tblRqAsset}.id")->pluck("{$tblRqAsset}.id")->toArray();
        }
        return [];
    }
}
