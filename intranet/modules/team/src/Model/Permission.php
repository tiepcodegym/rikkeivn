<?php
namespace Rikkei\Team\Model;

use DB;
use Lang;
use Exception;
use Rikkei\Core\View\CacheBase;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;

class Permission extends \Rikkei\Core\Model\CoreModel
{
    /*
     * flag value scope
     */
    const SCOPE_NONE = 0;
    const SCOPE_SELF = 1;
    const SCOPE_TEAM = 2;
    const SCOPE_COMPANY = 3;

    protected $table = 'permissions';
    public $timestamps = false;


    /**
     * get all scope
     *
     * @return array
     */
    public static function getScopes()
    {
        return [
            'none' => self::SCOPE_NONE,
            'self' => self::SCOPE_SELF,
            'team' => self::SCOPE_TEAM,
            'company' => self::SCOPE_COMPANY,
        ];
    }

    /**
     * get scope to assign default
     *
     * @return int
     */
    public static function getScopeDefault()
    {
        return self::SCOPE_NONE;
    }

    /**
     * get scope format option
     *
     * @return array
     */
    public static function toOption()
    {
        $scopeIcon = self::scopeIconArray();
        return [
            ['value' => self::SCOPE_NONE, 'label' => $scopeIcon[self::SCOPE_NONE]],
            ['value' => self::SCOPE_SELF, 'label' => $scopeIcon[self::SCOPE_SELF]],
            ['value' => self::SCOPE_TEAM, 'label' => $scopeIcon[self::SCOPE_TEAM]],
            ['value' => self::SCOPE_COMPANY, 'label' => $scopeIcon[self::SCOPE_COMPANY]],
        ];
    }

    /**
     * get scope format icon
     *
     * @return array
     */
    public static function scopeIconArray()
    {
        return [
            self::SCOPE_NONE => '',
            self::SCOPE_SELF => '<i class="fa fa-times"></i>',
            self::SCOPE_TEAM => '<i class="fa fa-caret-up"></i>',
            self::SCOPE_COMPANY => '<i class="fa fa-circle-o"></i>',
        ];
    }

    /**
     * get scope guide follow icon
     *
     * @return string
     */
    public static function getScopeIconGuide()
    {
        $scopesLabel = [
            self::SCOPE_NONE => 'none permission',
            self::SCOPE_SELF => 'self',
            self::SCOPE_TEAM => 'their management team',
            self::SCOPE_COMPANY => 'company',
        ];
        $html = '<p>' . Lang::get('team::view.Note') . ':</p>';
        $html .= '<ul>';
        $scopeIcon = self::scopeIconArray();
        foreach ($scopesLabel as $scopeValue =>$scopeLabel) {
            $html .= '<li>';
            $html .= '<b>' . $scopeIcon[$scopeValue] . '</b>: ';
            $html .= '<span>' . Lang::get('team::view.Scope '. $scopeLabel) . '</span>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * save permission
     *
     * @param array $data
     * @param int $teamOrRoleId
     * @param boolean $flagAddTeam
     */
    public static function saveRule(array $data = [], $teamOrRoleId = null, $flagAddTeam = true) {
        if (! $data) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                if (!isset($item['action_id'])) {
                    continue;
                }
                if ($flagAddTeam) {
                    $item['team_id'] = $teamOrRoleId;
                } else {
                    $item['team_id'] = null;
                    $item['role_id'] = $teamOrRoleId;
                    $item['scope_team_ids'] = isset($item['scope_team_ids']) ? json_encode($item['scope_team_ids']) : null;
                }
                $permissionItem = self::select('*')//withTrashed()
                ->where('role_id', $item['role_id'])
                    ->where('action_id', $item['action_id'])
                    ->where('team_id', $item['team_id'])
                    ->first();
                if (!$permissionItem) {
                    $permissionItem = new Permission();
                }
                $permissionItem->deleted_at = null;
                $permissionItem->setData($item);
                $permissionItem->save();
            }
            CacheBase::flush();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * get permission of team
     *
     * @param int $teamid
     * @return collection model
     */
    public static function getTeamPermission($teamid)
    {
        return self::select('role_id', 'action_id', 'scope')
            ->where('team_id', $teamid)
            ->get();
    }

    /**
     * get permission of role
     *
     * @param int $roleId
     * @return collection model
     */
    public static function getRolePermission($roleId)
    {
        return self::select('action_id', 'scope', 'scope_team_ids')
            ->where('role_id', $roleId)
            ->get();
    }

    /**
     * rewrite delete model
     *
     * @return type
     * @throws Exception
     */
    public function delete() {
        try {
            return parent::delete();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Query builder get list employee has permission of an action
     * @param string $action key in acl.php file
     * @param bool $ignoreAdmin ignore root accout or not
     * @param string $branchCode branch code, that mean check only scope team
     * @return builder
     */
    public static function builderEmployeesAllowAction($action, $ignoreAdmin = false, $branchCode = null)
    {
        $tblEmp = Employee::getTableName();
        $permissTbl = self::getTableName();
        $actionTbl = Action::getTableName();
        $empRoleTbl = EmployeeRole::getTableName();
        $teamMemberTbl = TeamMember::getTableName();
        $teamTbl = Team::getTableName();
        $rootAccount = config('services.account_root');

        $collect = Employee::select($tblEmp . '.id', $tblEmp . '.name', $tblEmp . '.email')
            ->where(function ($scopeQuery) use (
                $action,
                $tblEmp,
                $empRoleTbl,
                $teamMemberTbl,
                $teamTbl,
                $permissTbl,
                $actionTbl,
                $rootAccount,
                $ignoreAdmin,
                $branchCode
            ) {
                //team role permission
                $scopeQuery->whereIn($tblEmp.'.id', function ($query) use (
                    $action,
                    $teamMemberTbl,
                    $teamTbl,
                    $permissTbl,
                    $actionTbl,
                    $branchCode
                ) {
                    $query->select('tmb.employee_id')
                        ->from($teamMemberTbl . ' as tmb')
                        ->join($teamTbl . ' as team', 'team.id', '=', 'tmb.team_id')
                        ->join($permissTbl . ' as permiss1', function ($join) {
                            $join->on('team.id', '=', 'permiss1.team_id')
                                    ->on('tmb.role_id', '=', 'permiss1.role_id');
                        })
                        ->join($actionTbl . ' as action1', 'permiss1.action_id', '=', 'action1.id')
                        ->where('action1.name', $action)
                        ->where('permiss1.scope', '!=', self::SCOPE_NONE);

                    if ($branchCode) {
                        $query->where('team.branch_code', $branchCode)
                            ->where('permiss1.scope', self::SCOPE_TEAM);
                    }
                })
                //team follow as
                ->orWhereIn($tblEmp.'.id', function ($query) use (
                    $action,
                    $teamMemberTbl,
                    $teamTbl,
                    $permissTbl,
                    $actionTbl,
                    $branchCode
                ) {
                    $query->select('tmb2.employee_id')
                        ->from($teamMemberTbl . ' as tmb2')
                        ->join($teamTbl . ' as team2', function ($join) {
                            $join->on('team2.id', '=', 'tmb2.team_id')
                                    ->whereNotNull('team2.follow_team_id');
                        })
                        ->join($permissTbl . ' as permiss2', function ($join) {
                            $join->on('team2.follow_team_id', '=', 'permiss2.team_id')
                                    ->on('tmb2.role_id', '=', 'permiss2.role_id');
                        })
                        ->join($actionTbl . ' as action2', 'permiss2.action_id', '=', 'action2.id')
                        ->where('action2.name', $action)
                        ->where('permiss2.scope', '!=', self::SCOPE_NONE);

                    if ($branchCode) {
                        $query->where('team2.branch_code', $branchCode)
                            ->where('permiss2.scope', self::SCOPE_TEAM);
                    }
                })
                //special role
                ->orWhereIn($tblEmp.'.id', function ($query) use (
                    $action,
                    $empRoleTbl,
                    $permissTbl,
                    $actionTbl,
                    $teamTbl,
                    $teamMemberTbl,
                    $branchCode
                ) {
                    $query->select('emp_role.employee_id')
                        ->from($empRoleTbl.' as emp_role')
                        ->join($permissTbl.' as permiss3', 'emp_role.role_id', '=', 'permiss3.role_id')
                        ->join($actionTbl.' as action3', 'permiss3.action_id', '=', 'action3.id')
                        ->where('action3.name', $action)
                        ->where('permiss3.scope', '!=', self::SCOPE_NONE);

                    if ($branchCode) {
                        $query->leftJoin($teamTbl . ' as scope_team', function ($join) use ($branchCode) {
                            $join->on('permiss3.scope_team_ids', 'LIKE', DB::raw('CONCAT("%\"", scope_team.id, "\"%")'));
                        })
                        ->leftJoin($teamMemberTbl . ' as tmb3', 'tmb3.employee_id', '=', 'emp_role.employee_id')
                        ->leftJoin($teamTbl . ' as team3', 'team3.id', '=', 'tmb3.team_id')
                        //where not null scope_team_ids and has branch code or get team member has branch_code
                        ->where('permiss3.scope', self::SCOPE_TEAM)
                        ->where(function ($subQuery) use ($branchCode) {
                            $subQuery->where(function ($subQuery1) use ($branchCode) {
                                    $subQuery1->whereNotNull('scope_team.id')
                                        ->where('scope_team.branch_code', $branchCode);
                                })
                                ->orWhere(function ($subQuery2) use ($branchCode) {
                                    $subQuery2->whereNull('permiss3.scope_team_ids')
                                        ->where('team3.branch_code', $branchCode);
                                });
                        });
                    }
                });
                if (!$ignoreAdmin) {
                    $scopeQuery->orWhere($tblEmp.'.email', $rootAccount);
                }
            })
            ->where(function ($query) use ($tblEmp) {
                $query->whereNull($tblEmp.'.leave_date')
                        ->orWhereRaw($tblEmp . '.leave_date >= CURDATE()');
            });

        return $collect;
    }

    /*
     * check has permission of employee with route spec.
     *
     * @param [int] $employeeId.
     * @param [string] $nameGroupRoute : name group function inside acl.
     * @return boolean.
     */
    public static function isScopeCompanyOfRoute($employeeId, $nameGroupRoute)
    {
        $tblEmp = Employee::getTableName();
        $collect = self::builderEmployeesAllowAction($nameGroupRoute)
            ->where($tblEmp . '.id', '=', $employeeId)
            ->first();

        if (!$collect) {
            return false;
        }
        return true;
    }

    /**
     * add query allow action
     *
     * @param builder $collection
     * @param string $actionName
     * @return null
     */
    public static function addAllowAction(&$collection, $actionName)
    {
        $tblEmp = Employee::getTableName();
        $tblPermission = self::getTableName();
        $tblAction = Action::getTableName();
        $tblEmpRole = EmployeeRole::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblTeam = Team::getTableName();
        $collection->where(function ($scopeQuery) use ($actionName, $tblEmp, $tblEmpRole, $tblTeamMember, $tblTeam, $tblPermission, $tblAction) {
            //team role permission
            $scopeQuery->whereIn("{$tblEmp}.id", function ($query) use ($actionName, $tblTeamMember, $tblTeam, $tblPermission, $tblAction) {
                $query->select('tmb1.employee_id')
                    ->from("{$tblTeamMember} AS tmb1")
                    ->join("{$tblTeam} AS t1", 't1.id', '=', 'tmb1.team_id')
                    ->join("{$tblPermission} AS per1", function ($join) {
                        $join->on('t1.id', '=', 'per1.team_id')
                            ->on('tmb1.role_id', '=', 'per1.role_id');
                    })
                    ->join("{$tblAction} AS act1", 'per1.action_id', '=', 'act1.id')
                    ->where('act1.name', $actionName)
                    ->where('per1.scope', '!=', self::SCOPE_NONE);
            })
                //team follow as
                ->orWhereIn("{$tblEmp}.id", function ($query) use ($actionName, $tblTeamMember, $tblTeam, $tblPermission, $tblAction) {
                    $query->select('tmb2.employee_id')
                        ->from("{$tblTeamMember} AS tmb2")
                        ->join("{$tblTeam} AS t2", function ($join) {
                            $join->on('t2.id', '=', 'tmb2.team_id')
                                ->whereNotNull('t2.follow_team_id');
                        })
                        ->join("{$tblPermission} AS per2", function ($join) {
                            $join->on('t2.follow_team_id', '=', 'per2.team_id')
                                ->on('tmb2.role_id', '=', 'per2.role_id');
                        })
                        ->join("{$tblAction} AS act2", 'per2.action_id', '=', 'act2.id')
                        ->where('act2.name', $actionName)
                        ->where('per2.scope', '!=', self::SCOPE_NONE);
                })
                //special role
                ->orWhereIn("{$tblEmp}.id", function ($query) use ($actionName, $tblEmpRole, $tblPermission, $tblAction) {
                    $query->select('emp_role.employee_id')
                        ->from("{$tblEmpRole} AS emp_role")
                        ->join("{$tblPermission} AS per3", 'emp_role.role_id', '=', 'per3.role_id')
                        ->join("{$tblAction} AS act3", 'per3.action_id', '=', 'act3.id')
                        ->where('act3.name', $actionName)
                        ->where('per3.scope', '!=', self::SCOPE_NONE);
                });
        });
    }

    /**
     * Get tất cả employee có action name theo teams
     *
     * @param $actionName
     * @param null $teamIds (có thể nhận cả array và ko phải mảng)
     * @return mixed Type: Collection
     */
    public function getEmployeeByActionName($actionName, $teamIds = null)
    {
        if (!is_array($teamIds)) {
            $teamIds = (array)$teamIds;
        }

        return Employee::select('employees.id', 'employees.name', 'employees.email', 'leave_date')
            ->where(function ($query) use ($actionName, $teamIds) {
                $query->whereIn('id', function ($query) use ($actionName, $teamIds) {
                    //Tim cac user co quyền đặc biệt
                    $query->select('employee_roles.employee_id')
                        ->from('employee_roles')
                        ->join('permissions', function ($join) {
                            $join->on('permissions.role_id', '=', 'employee_roles.role_id')
                                ->whereNull('permissions.team_id')
                                ->where('permissions.scope', '<>', self::SCOPE_NONE);
                        })
                        ->join('actions', function ($join) use ($actionName) {
                            $join->on('actions.id', '=', 'permissions.action_id')
                                ->where('actions.name', '=', $actionName);
                        });

                    if (!empty($teamIds)) {
                        $query->where(function ($query) use ($teamIds) {
                            $query->where('scope_team_ids', 'RLIKE', implode("|", $teamIds));
                        });
                    }
                })
                ->orWhereIn('id', function ($query) use ($actionName, $teamIds) {
                    //Tim cac user có quyền theo follow team
                    $query->select('team_members.employee_id')
                        ->from('team_members')
                        ->join('teams', function ($join) {
                            $join->on('teams.id', '=', 'team_members.team_id')
                                ->whereNotNull('teams.follow_team_id');
                        })
                        ->join('permissions', function ($join) {
                            $join->on('teams.follow_team_id', '=', 'permissions.team_id')
                                ->on('team_members.role_id', '=', 'permissions.role_id')
                                ->where('permissions.scope', '<>', self::SCOPE_NONE);
                        })
                        ->leftJoin('actions', 'permissions.action_id', '=', 'actions.id')
                        ->whereNull('team_members.deleted_at')
                        ->where('actions.name', $actionName);

                        if (!empty($teamIds)) {
                            $query->whereIn('team_members.team_id', $teamIds);
                        }
                })
                ->orWhereIn('id', function ($query) use ($actionName, $teamIds) {
                    //Tim cac user có quyền theo team
                    $query->select('team_members.employee_id')
                        ->from('team_members')
                        ->join('teams', 'teams.id', '=', 'team_members.team_id')
                        ->join('permissions', function ($join) {
                            $join->on('teams.id', '=', 'permissions.team_id')
                                ->on('team_members.role_id', '=', 'permissions.role_id')
                                ->where('permissions.scope', '<>', self::SCOPE_NONE);
                        })
                        ->leftJoin('actions', 'permissions.action_id', '=', 'actions.id')
                        ->whereNull('team_members.deleted_at')
                        ->where('actions.name', $actionName);

                    if (!empty($teamIds)) {
                        $query->whereIn('team_members.team_id', $teamIds);
                    }
                });
            })
            ->where(function ($query) {
                $query->whereNull('employees.leave_date')
                    ->orWhereRaw('employees.leave_date >= CURDATE()');
            })
            ->orderBy('employees.email');
    }
}
