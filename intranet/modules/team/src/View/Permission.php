<?php
namespace Rikkei\Team\View;

use Route;
use Rikkei\Team\Model\Permission as PermissionModel;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Http\Controllers\AuthController;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\CacheBase;
use Rikkei\Core\Model\User;
use Rikkei\Team\Model\TeamMember;

/**
 * class permission
 * 
 * check permssion auth
 */
class Permission
{
    /**
     *  store this object
     * @var object
     */
    protected static $instance;

    /**
     *  Check will get team was deleted or not
     * @var boolean
     */
    protected $withTrashed;

    /**
     * store user current logged
     * @var model
     */
    protected $employee;
    /**
     * store rules of current user
     * @var array
     */
    protected $rules;
    
    /**
     * contructor
     */
    public function __construct($employee = null, $withTrashed = false)
    {
        $this->withTrashed = $withTrashed;
        $this->initEmployee($employee);
    }

    /**
     * init User loggined
     *
     * @return \Rikkei\Team\View\Permission
     */
    public function initEmployee($employee = null)
    {
        if (!$employee) {
            $this->employee = User::getEmployeeLogged();
        } else {
            $this->employee = $employee;
        }
        if ($this->isRoot()) {
            return $this;
        }
        if (!$this->employee || !$this->employee->isAllowLogin()) {
            $auth = new AuthController();
            return $auth->logout(Lang::get('core::message.You donot have permission login'));
        }
        $this->initRules();
        return $this;
    }
    
    /**
     * init Rules
     * 
     * @return \Rikkei\Team\View\Permission
     */
    public function initRules()
    {
        if ($this->isRoot() || !$this->employee) {
            return $this;
        }
        if ($this->rules) {
            return $this;
        }
        $prefixWithTrashed = $this->withTrashed ? '_withTrashed' : '';
        if ($this->rules = CacheBase::getFile(CacheBase::EMPL_PERMIS, $this->employee->id . $prefixWithTrashed)) {
            return $this;
        }
        $this->rules = $this->employee->getPermission($this->withTrashed);
        if (!$this->rules) {
            $this->rules = ['checked' => true];
        }
        CacheBase::putFile(
            CacheBase::EMPL_PERMIS,
            $this->employee->id . $prefixWithTrashed,
            $this->rules
        );
        return $this;
    }
    
    /**
     * get scopes of teams in a route
     * 
     * @param int $teamId
     * @param string|int|null $routeOrActionId
     * @return int|array
     */
    public function getScopeCurrentOfTeam($teamId = null, $routeOrActionId = null)
    {
        if ($this->isRoot()) {
            return ['scope' => [PermissionModel::SCOPE_COMPANY]];
        }
        if (! $routeOrActionId) {
            $routeCurrent = Route::getCurrentRoute()->getName();
        } else {
            $routeCurrent = $routeOrActionId;
        }
        if (! $this->rules || ! isset($this->rules['team'])) {
            return ['scope' => [PermissionModel::SCOPE_NONE]];
        }
        $scopes = [];
        $maxScope = PermissionModel::SCOPE_NONE;
        //if route current is number: check action id
        if (is_numeric($routeCurrent)) {
            $rulesTeam = $this->rules['team']['action'];
        } else { //if route current is string: check route name
            $rulesTeam = $this->rules['team']['route'];
        }
        $listTeamChilds = [];
        foreach ($rulesTeam as $teamIdRule => $rules) {
            if ($teamId && $teamId != $teamIdRule || !isset($rules['permissScopes'])) {
                continue;
            }
            $listTeamChilds[$teamIdRule] = $rules['childs'];
            foreach ($rules['permissScopes'] as $routePermission => $scope) {
                //check all permission
                if ($routePermission == '*') {
                    $routePermission = '.*';
                }
                $flagCheck = false; //search route action
                if (strpos($routePermission, '*') !== false) {
                    if (preg_match('/' . $routePermission . '/', $routeCurrent)) {
                        $flagCheck = true;
                    }
                } else {
                    if ($routeCurrent == $routePermission) {
                        $flagCheck = true;
                    }
                }
                if ($flagCheck) {
                    if ($teamId) {
                        return [
                            'scope' => [$scope],
                            'team_childs' => $listTeamChilds,
                        ];
                    }
                    $scopes[$teamIdRule] = (int) $scope;
                    $maxScope = $maxScope > (int) $scope ? (int) $maxScope : (int) $scope;
                }
            }
        }

        if (!$scopes) {
            return ['scope' => [PermissionModel::SCOPE_NONE]];
        }

        return [
            'scope' => $scopes,
            'max_scope' => $maxScope,
            'team_childs' => $listTeamChilds
        ];
    }

    /**
     * get scopes of roles in a route
     * 
     * @param string|int|null $routeOrActionId
     * @return int
     */
    public function getScopeCurrentOfRole($routeOrActionId = null)
    {
        if ($this->isRoot()) {
            return ['scope' => PermissionModel::SCOPE_COMPANY];
        }
        if (! $routeOrActionId) {
            $routeCurrent = Route::getCurrentRoute()->getName();
        } else {
            $routeCurrent = $routeOrActionId;
        }
        if (! $this->rules || ! isset($this->rules['role'])) {
            return ['scope' => PermissionModel::SCOPE_NONE];
        }
        //if route current is number: check action id
        if (is_numeric($routeCurrent)) {
            $rulesRole = $this->rules['role']['action'];
        } else { //if route current is string: check route name
            $rulesRole = $this->rules['role']['route'];
        }
        foreach ($rulesRole as $routePermission => $scope) {
            if ($routePermission == '*') {
                $routePermission = '.*';
            }
            $flagCheck = false; //search route action
            $teamIds = [];
            if (is_numeric($routeCurrent)) {
                if ($routeCurrent == $routePermission) {
                    $flagCheck = true;
                    //set team ids
                    if (isset($this->rules['role']['action_team'][$routePermission])) {
                        $teamIds = $this->rules['role']['action_team'][$routePermission];
                    }
                }
            } else {
                if (strpos($routePermission, '*') !== false) {
                    if (preg_match('/' . $routePermission . '/', $routeCurrent)) {
                        $flagCheck = true;
                        //set team ids
                        if (isset($this->rules['role']['route_team'][$routePermission])) {
                            $teamIds = $this->rules['role']['route_team'][$routePermission];
                        }
                    }
                } else {
                    if ($routeCurrent == $routePermission) {
                        $flagCheck = true;
                        //set team ids
                        if (isset($this->rules['role']['route_team'][$routePermission])) {
                            $teamIds = $this->rules['role']['route_team'][$routePermission];
                        }
                    }
                }
            }
            if ($flagCheck) {
                return [
                    'scope' => $scope,
                    'team_ids' => $teamIds,
                ];
            }
        }
        return ['scope' => PermissionModel::SCOPE_NONE];
    }
    
    /**
     * check allow access to route current
     * 
     * @param string|null $route route name
     * @return boolean
     */
    public function isAllow($route = null)
    {
        if ($this->isRoot()) {
            return true;
        }
        if ($this->isScopeNone(null, $route)) {
            return false;
        }
        return true;
    }
    
    /**
     * check is scope none
     * 
     * @param int $teamId
     * @param string|int|null $route route name
     * @return boolean
     */
    public function isScopeNone($teamId = null, $route = null)
    {
        if ($this->isRoot()) {
            return false;
        }
        if ($this->getScopeCurrentOfRole($route)['scope'] != PermissionModel::SCOPE_NONE) {
            return false;
        }
        $scopeTeam = $this->getScopeCurrentOfTeam($teamId, $route)['scope'];
        // scope team is array
        foreach ($scopeTeam as $scope) {
            if ($scope != PermissionModel::SCOPE_NONE) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * check is scope self
     * 
     * @param int $teamId
     * @param string|null $route route name
     * @return boolean
     */
    public function isScopeSelf($teamId = null, $route = null)
    {
        if ($this->getScopeCurrentOfRole($route)['scope'] == PermissionModel::SCOPE_SELF) {
            return true;
        }
        $scopeTeam = $this->getScopeCurrentOfTeam($teamId, $route)['scope'];
        // scope team is array
        foreach ($scopeTeam as $scope) {
            if ($scope == PermissionModel::SCOPE_SELF) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * check is scope team
     * 
     * @param int $teamId
     * @param string|null $route route name
     * @return boolean|array
     */
    public function isScopeTeam($teamId = null, $route = null)
    {
        $scopeRole = $this->getScopeCurrentOfRole($route);
        $hasScope = false;
        $scopeTeamIds = [];
        if ($scopeRole['scope'] == PermissionModel::SCOPE_TEAM) {
            $hasScope = true;
            $scopeTeamIds = $scopeRole['team_ids'];
            //if special role has scope team but not set team for this then return team member
            if (count($scopeTeamIds) < 1) {
                return TeamMember::getTeamMembersByEmployees(auth()->id())
                        ->pluck('team_id')
                        ->toArray();
            }
        }
        $scopeOfTeam = $this->getScopeCurrentOfTeam($teamId, $route);
        $scopeTeam = $scopeOfTeam['scope'];
        // scope team is array
        foreach ($scopeTeam as $scopeTeamId => $scope) {
            if ($scope == PermissionModel::SCOPE_TEAM) {
                $hasScope = true;
                if ($scopeTeamId) {
                    $teamChilds = $scopeOfTeam['team_childs'][$scopeTeamId];
                    $scopeTeamIds = array_merge($scopeTeamIds, array_diff($teamChilds, $scopeTeamIds));
                }
            }
        }
        if ($hasScope) {
            return $scopeTeamIds ? $scopeTeamIds : true;
        }
        return false;
    }

    /**
     * check is scope company
     *
     * @param int $teamId
     * @param string|null $route route name
     * @return boolean
     */
    public function isScopeCompany($teamId = null, $route = null)
    {
        if ($this->isRoot()) {
            return true;
        }
        if (!$route) {
            $route = Route::getCurrentRoute()->getName();
        }
        if ($this->getScopeCurrentOfRole($route)['scope'] == PermissionModel::SCOPE_COMPANY) {
            return true;
        }
        $scopeTeam = $this->getScopeCurrentOfTeam($teamId, $route)['scope'];
        // scope team is array
        foreach ($scopeTeam as $scope) {
            if ($scope == PermissionModel::SCOPE_COMPANY) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * get team of employee
     * 
     * @return array|null
     */
    public function getTeams()
    {
        if ($this->rules && 
                isset($this->rules['team']) && 
                isset($this->rules['team']['route'])
        ) {
            return array_keys($this->rules['team']['route']);
        }
        return [];
    }
    
    /**
     * get root account from file .env
     * 
     * @return string|null
     */
    public function getRootAccount()
    {
        return trim(config('services.account_root'));
    }
    
    /**
     * get qa account from file .env
     * 
     * @return string|null
     */
    public function getQAAccount()
    {
        return CoreConfigData::getQAAccount();
    }
    
    /**
     * get coo account from file .env
     * 
     * @return string|null
     */
    public function getCOOAccount()
    {
        return CoreConfigData::getCOOAccount();
    }
    
    /**
     * check current user is root
     * 
     * @return boolean
     */
    public function isRoot()
    {
        if ($this->employee && $this->getRootAccount() == $this->employee->email) {
            return true;
        }
        return false;
    }

    /**
     * check current user is root or admin
     * @return boolean
     */
    public function isRootOrAdmin()
    {
        if ($this->isRoot()) {
            return true;
        }
        return $this->employee->isAdmin();
    }

    /**
     * check current user is root
     * 
     * @return boolean
     */
    public function isCOOAccount()
    {
        if ($this->employee && in_array($this->employee->email, $this->getCOOAccount())) {
            return true;
        }
        return false;
    }
    
    /**
     * get employee current
     * 
     * @return null|model
     */
    public function getEmployee()
    {
        return $this->employee;
    }
    
    /**
     * get permission of employee current
     * 
     * @return array|null
     */
    public function getPermission()
    {
        return $this->rules;
    }
    
    /**
     * Singleton instance
     * 
     * @return \Rikkei\Team\View\Permission
     */
    public static function getInstance($employee = null, $withTrashed = false)
    {
        if (!isset(self::$instance)) {
            self::$instance = new static($employee, $withTrashed);
        }
        return self::$instance;
    }
    
}
