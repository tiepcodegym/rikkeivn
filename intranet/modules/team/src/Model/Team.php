<?php
namespace Rikkei\Team\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\User;
use Rikkei\Core\View\CacheBase;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\Form;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission as PermissionView;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Team\Model\Permission;

class Team extends CoreModel
{

    use SoftDeletes;

    /*
     * flag allow number max leader of a team
     */
    const MAX_LEADER = 1;

    const KEY_CACHE = 'team';
    const TEAM_TYPE_SYSTENA = 1;
    const TEAM_TYPE_HR = 2;
    const TEAM_TYPE_PQA = 5;
    const TEAM_TYPE_QA = 6;
    const TEAM_TYPE_SALE = 7;

    const ROLE_TEAM_LEADER = 1;
    const ROLE_SUB_LEADER = 2;
    const ROLE_MEMBER = 3;
    const DETAIL = 'Detail';

    const TYPE_REGION_HN = 21;
    const TYPE_REGION_DN = 22;
    const TYPE_REGION_JP = 23;
    const TYPE_REGION_HCM = 24;

    const IS_SOFT_DEVELOPMENT = 1;
    const IS_NOT_SOFT_DEV = 0;

    const CODE_RK_DANANG = 'rdn';
    const CODE_TEAM_IT = 'hanoi_it';
    const CODE_HC_TH = 'hanoi_hcth';
    const CODE_BOD = 'bod';
    const TEAM_BOD_ID = 1;

    const WORKING = 1;
    const END_WORK = 2;

    const CODE_PREFIX_HN = 'hanoi';
    const CODE_PREFIX_DN = 'danang';
    const CODE_PREFIX_JP = 'japan';
    const CODE_PREFIX_HCM = 'hcm';
    const CODE_PREFIX_AI = 'ai';
    const CODE_PREFIX_ROBOTICS = 'hanoi_robotics';
    const CODE_PREFIX_RS = 'rs';
    const CODE_PREFIX_ACADEMY = 'academy';
    const CODE_PREFIX_DIGITAL = 'digital';
    const CACHE_TEAM_CODE_PREFIX = 'team_code_prefix';
    const CACHE_TEAM_MEMBER_LIST = 'team_member_list';
    const CACHE_HOLIDAYS_COMPENSATE = 'holidays_compensatedays';

    const TEAM_PQA_ID = 22;


    protected $table = 'teams';

    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', 'parent_id', 'sort_order', 'follow_team_id', 'is_function', 'type', 'description', 'leader_id', 'email', 'mail_group'
    ];

    public static function listRegion()
    {
        return [
            self::TYPE_REGION_HN => trans('team::view.Hanoi'),
            self::TYPE_REGION_DN => trans('team::view.Danang'),
            self::TYPE_REGION_HCM => trans('team::view.HoChiMinh'),
            self::TYPE_REGION_JP => trans('team::view.Japan'),
        ];
    }

    public static function listPrefixByRegion()
    {
        return [
            self::TYPE_REGION_HN => self::CODE_PREFIX_HN,
            self::TYPE_REGION_DN => self::CODE_PREFIX_DN,
            self::TYPE_REGION_HCM => self::CODE_PREFIX_HCM,
            self::TYPE_REGION_JP => self::CODE_PREFIX_JP,
        ];
    }

    /**
     * Get the leader info for the team
     */
    public function leaderInfo() {
        return $this->belongsTo('Rikkei\Team\Model\Employee', 'leader_id');
    }

    /**
     * Get the setting address mail info for the team
     */
    public function addressMail() {
        return $this->hasOne('Rikkei\Education\Model\SettingAddressMail', 'team_id', 'id');
    }

    /**
     * move position team
     *
     * @param boolean $up
     */
    public function move($up = true)
    {
        $siblings = Team::select('id', 'sort_order')
            ->where('parent_id', $this->parent_id)
            ->orderBy('sort_order')
            ->get();
        if (count($siblings) < 2) {
            return true;
        }
        $dataOrder = $siblings->toArray();
        $flagIndexToCurrent = false;
        $countDataOrder = count($dataOrder);
        if ($up) {
            if ($dataOrder[0]['id'] == $this->id) { //item move up is first
                return true;
            }
            for ($i = 1; $i < $countDataOrder; $i++) {
                if (!$flagIndexToCurrent) {
                    $dataOrder[$i]['sort_order'] = $i;
                    if ($dataOrder[$i]['id'] == $this->id) {
                        $dataOrder[$i]['sort_order'] = $i - 1;
                        $dataOrder[$i - 1]['sort_order'] = $i;
                        $flagIndexToCurrent = true;
                    }
                } else {
                    unset($dataOrder[$i]);
                }
            }
        } else {
            if ($dataOrder[count($dataOrder) - 1]['id'] == $this->id) { //item move down is last
                return true;
            }
            for ($i = 0; $i < $countDataOrder - 1; $i++) {
                if (!$flagIndexToCurrent) {
                    $dataOrder[$i]['sort_order'] = $i;
                    if ($dataOrder[$i]['id'] == $this->id) {
                        $dataOrder[$i]['sort_order'] = $i + 1;
                        $dataOrder[$i + 1]['sort_order'] = $i;
                        $flagIndexToCurrent = true;
                        $i++;
                    }
                } else {
                    unset($dataOrder[$i]);
                }
            }
        }
        DB::beginTransaction();
        try {
            foreach ($dataOrder as $data) {
                $team = self::find($data['id']);
                $team->sort_order = $data['sort_order'];
                $team->save();
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * delete team and all child
     *
     * @throws \Rikkei\Team\Model\Exception
     */
    public function delete()
    {
        if ($length = $this->getNumberMember()) {
            throw new Exception(Lang::get("team::messages.Team :name has :number members, can't delete!",[
                'name' => $this->name,
                'number' => $length
            ]), self::ERROR_CODE_EXCEPTION);
        }
        $children = Team::select('id')
            ->where('parent_id', $this->id)->get();
        DB::beginTransaction();
        try {
            //delete all children of team
            if (count($children)) {
                foreach ($children as $child) {
                    Team::find($child->id)->delete();
                }
            }

            // TO DO check table Relationship: team position, user, css, ...

            //delete team rule
            Permission::where('team_id', $this->id)->delete();
            //set permission as of  teams follow this team to 0
            Team::where('follow_team_id', $this->id)->update([
                'follow_team_id' => null
            ]);
            parent::delete();
            CacheBase::flush();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * rewrite vave the team to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array()) {
        if (! $this->parent_id) {
            $this->parent_id = null;
        }
        // update model
        if ($this->id) {
            //delete team rule of this team
            if (! $this->is_function || $this->follow_team_id) {
                Permission::where('team_id', $this->id)->delete();
            }
            CacheBase::flush();
        } else {
            CacheHelper::forget(self::KEY_CACHE);
            CacheHelper::forget(self::KEY_CACHE . '_withTrashed');
            CacheHelper::forget(TeamList::KEY_CACHE_ALL_CHILD, $this->parent_id);
        }
        return parent::save($options);
    }

    /**
     * get number children of a team
     *
     * @return int
     */
    public function getNumberChildren()
    {
        $children = self::select(DB::raw('count(*) as count'))
            ->where('parent_id', $this->id)
            ->first();
        return $children->count;
    }

    /**
     * get number member of a team
     *
     * @return int
     */
    public function getNumberMember()
    {
        $children = TeamMember::select(DB::raw('count(*) as count'))
            ->where('team_id', $this->id)
            ->first();
        return $children->count;
    }

    /**
     * get team permission as
     *
     * @return boolean|model
     */
    public function getTeamPermissionAs()
    {
        if (!$this->follow_team_id) {
            return null;
        }
        $teamAs = Team::find($this->follow_team_id);
        if (!$teamAs) {
            return null;
        }
        return $teamAs;
    }

    /**
     * get team permission as
     *
     * @return boolean|model
     */
    public function getTeamIdPermissionAs()
    {
        if (!$this->follow_team_id) {
            return null;
        }
        $teamAs = Team::select(['id'])
            ->where('id', $this->follow_team_id)
            ->first();
        if (!$teamAs) {
            return null;
        }
        return $teamAs->id;
    }

    /**
     * get teams by team_id list
     *
     * @param array $arrTeamIds
     * @return object list
     */
    public function getTeamsByTeamIds($arrTeamIds){
        return self::whereIn('id', $arrTeamIds)->get();
    }

    /**
     * Get Team with deleted_at != null by id
     *
     * @param int $teamId
     * @return Team
     */
    public function getTeamWithTrashedById($teamId){
        return self::where('id',$teamId)
                ->withTrashed()
                ->first();
    }

    /**
     * Get Team with deleted_at != null by parent id
     *
     * @param int $parentId
     * @return Team list
     */
    public function getTeamByParentId($parentId){
        return self::where('parent_id',$parentId)
                ->withTrashed()
                ->get();
    }

    /**
     *
     */
    public function getTeamByParentIdNoTrashed($parentId){
        return self::where('parent_id',$parentId)
                ->get();
    }

    /**
     * get leader of team
     *
     * @return model|null
     */
    public function getLeader()
    {
        if (! $this->leader_id) {
            return null;
        }
        $today = Carbon::today()->format('Y-m-d');
        $leader = Employee::where('id', $this->leader_id)
            ->where(function ($query) use ($today) {
                $query->whereNull('leave_date')
                    ->orWhereDate('leave_date', '=', $today)
                    ->orWhereDate('leave_date', '>', $today);
                })
            ->first();
        if (!$leader) {
            return null;
        }
        return $leader;
    }

    /**
     * check team is function
     *
     * @return boolean
     */
    public function isFunction()
    {
        if ($this->is_function) {
            return true;
        }
        return false;
    }

    /**
     * get children of team
     *
     * @param int|null $teamParentId
     * @return model
     */
    public static function getTeamChildren($teamParentId = null)
    {
        if ($teams = CacheHelper::get(self::KEY_CACHE)) {
            return $teams;
        }
        $teams = Team::select('id', 'name', 'parent_id')
                ->where('parent_id', $teamParentId)
                ->orderBy('sort_order', 'asc')
                ->get();
        CacheHelper::put(self::KEY_CACHE, $teams);
        return $teams;
    }

    /**
     * get team path
     *
     * @return array|null
     */
    public static function getTeamPath($withTrashed = false)
    {
        $prefixWithTrashed = $withTrashed ? '_withTrashed' : '';

        if ($teamPath = CacheHelper::get(self::KEY_CACHE . $prefixWithTrashed)) {
            return $teamPath;
        }
        $teamAll = Team::select('id', 'parent_id');
        if ($withTrashed) {
            $teamAll->withTrashed();
        }
        $teamAll = $teamAll->get();
        if (! count($teamAll)) {
            return null;
        }
        $teamPaths = [];
        foreach ($teamAll as $team) {
            self::getTeamPathRecursive($teamPaths[$team->id], $team->parent_id, $withTrashed);
            self::getTeamChildRecursive($teamPaths[$team->id], $team->id, $withTrashed);
            if (! isset($teamPaths[$team->id])) {
                $teamPaths[$team->id] = null;
            }
        }
        CacheHelper::put(self::KEY_CACHE . $prefixWithTrashed, $teamPaths);
        return $teamPaths;
    }

    /**
     * get team path recursive
     *
     * @param array $teamPaths
     * @param null|int $parentId
     */
    protected static function getTeamPathRecursive(&$teamPaths = [], $parentId = null, $withTrashed = false)
    {
        if (! $parentId) {
            return;
        }
        if ($withTrashed) {
            $teamParent = Team::query()->withTrashed()->find($parentId);
        } else {
            $teamParent = Team::find($parentId);
        }
        if (! $teamParent) {
            return;
        }
        $teamPaths[] = (int) $teamParent->id;
        self::getTeamPathRecursive($teamPaths, $teamParent->parent_id, $withTrashed);
    }

    /**
     * get team child recursive
     *
     * @param array $teamPaths
     * @param null|int $teamId
     */
    protected static function getTeamChildRecursive(&$teamPaths = [], $teamId = null, $withTrashed = false)
    {
        if (! $teamId) {
            return;
        }
        $teamChildren = Team::select('id', 'parent_id')
            ->where('parent_id', $teamId);
        if ($withTrashed) {
            $teamChildren->withTrashed();
        }
        $teamChildren = $teamChildren->get();

        if (! count($teamChildren)) {
            return;
        }
        foreach ($teamChildren as $item) {
            $teamPaths['child'][] = (int) $item->id;
            self::getTeamChildRecursive($teamPaths, $item->id, $withTrashed);
        }
    }

    /**
     * get team child recursive
     *
     * @param array $teamPaths
     * @param null|int $teamId
     */
    public static function getTeamChildRecursivePublic(&$teamPaths = [], $teamId = null)
    {
        if (! $teamId) {
            return;
        }
        $teamChildren = Team::select('id', 'parent_id')
            ->where('parent_id', $teamId)
            ->get();
        if (! count($teamChildren)) {
            return;
        }
        foreach ($teamChildren as $item) {
            $teamPaths['child'][] = (int) $item->id;
            self::getTeamChildRecursive($teamPaths, $item->id);
        }
    }

    public static function getTeamNameOfEmployee($empId)
    {
        $employeeTable = Employee::getTableName();
        $roleTable = Role::getTableName();
        $teamTable = Team::getTableName();
        $teamMbTable = TeamMember::getTableName();

        $registerRecord = Employee::select(
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$roleTable}.role, ' - ', {$teamTable}.name) ORDER BY {$roleTable}.role DESC SEPARATOR '; ') as role_name")
        )
        ->leftjoin("{$teamMbTable}", "{$teamMbTable}.employee_id", "=", "{$employeeTable}.id")
        ->leftjoin("{$teamTable}", "{$teamTable}.id", "=", "{$teamMbTable}.team_id")
        ->leftjoin("{$roleTable}", "{$roleTable}.id", "=", "{$teamMbTable}.role_id")
        ->where("{$roleTable}.special_flg", DB::raw(Role::FLAG_POSITION))
        ->where("{$employeeTable}.id", $empId)
        ->withTrashed()
        ->first();

        return $registerRecord;
    }

    /**
     * get grid data of member list
     * @param null $teamIds
     * @param null $isWorking
     * @param null $urlFilter
     * @param array $options
     * @return mixed
     */
    public static function getMemberGridData($teamIds = null, $isWorking = null, $urlFilter = null, $options = [])
    {
        $countTeam = 0;
        if ($teamIds == 0) {
            // permission company + don't select team: view all employees (has team or no team)
            // $allTeam = Team::getAllTeam(true)->pluck('id')->toArray();
            // $teamIds = implode($allTeam, ', ');
            $countTeam = 2;
            $teamId = null;
        } else {
            $teamArray = array_map('intval', explode(',', $teamIds));
            if (isset($teamArray[1])) {
                $countTeam = 2;
            }
            $teamId = '(' . $teamIds . ')';
        }

        $teamTable = self::getTableName();
        $teamTableAs = 'team_table';
        $employeeTable = Employee::getTableName();
        $employeeTableAs = $employeeTable;
        $employeeTeamTable = TeamMember::getTableName();
        $employeeTeamTableAs = 'team_member_table';
        $roleTabel = Role::getTableName();
        $roleTabelAs = 'role_table';
        $roleSpecialTabelAs = 'role_special_table';
        $memberRoleTable = EmployeeRole::getTableName();
        $memberRoleTabelAs = 'member_role_table';
        $pager = Config::getPagerData($urlFilter);
        $tableEmplCvAttrValue = EmplCvAttrValue::getTableName();

        $selectFields = [
            "{$employeeTable}.id as id",
            "{$employeeTable}.employee_code as employee_code",
            "{$employeeTable}.name as name",
            "{$employeeTable}.email as email",
            DB::raw("DATE_FORMAT({$employeeTable}.join_date, '%Y-%m-%d') as join_date"),
            DB::raw("DATE_FORMAT({$employeeTable}.leave_date, '%Y-%m-%d') as leave_date"),
            "{$employeeTable}.offcial_date",
            "{$teamTableAs}.name as team_name",
            "user.avatar_url"
        ];
        if (isset($options['select'])) {
            $selectFields = $options['select'];
        }
        $collection = Employee::select($selectFields)
                ->leftJoin(User::getTableName() . ' as user', $employeeTable . '.id', '=', 'user.employee_id');
        if (!isset($options['select'])) {
            if ($countTeam > 1 || !$teamId) {
                $collection->addSelect(
                    DB::raw("GROUP_CONCAT(DISTINCT " .
                        "CONCAT(`{$roleTabelAs}`.`role`, ' - ', `{$teamTableAs}`.`name`)" .
                        " SEPARATOR '; ')" .
                        "as role_name")
                );
            } else {
                $collection->addSelect(
                    "{$roleTabelAs}.role as role_name"
                );
            }
        }
        $typeJoin = $teamId ? 'join' : 'leftJoin';
        //join team member
        if ($isWorking === Team::END_WORK || $isWorking === null) {
            $tblTeamHistory = self::buildTeamMemberTableForMemberList();
            if ($isWorking === null) {
                $tblTeamMember = TeamMember::getTableName();
                $tblTeamHistory = "SELECT employee_id, team_id, role_id, NULL AS end_at"
                    . " FROM {$tblTeamMember}"
                    . " UNION {$tblTeamHistory}";
            }
            $collection->{$typeJoin}(
                DB::raw("({$tblTeamHistory}) AS {$employeeTeamTableAs}"),
                function ($join) use ($typeJoin, $teamId, $employeeTable, $employeeTeamTableAs) {
                    $join->on("{$employeeTable}.id", '=', "{$employeeTeamTableAs}.employee_id");
                    if ($typeJoin === 'join') {
                        $join->on("{$employeeTeamTableAs}.team_id", 'IN', DB::raw($teamId));
                    }
                }
            );
        } else {
            if ($teamId) {
                $collection->join(
                    "{$employeeTeamTable} as {$employeeTeamTableAs}",
                    function ($join) use ($teamId, $employeeTable, $employeeTeamTableAs
                    ) {
                        $join->on("{$employeeTable}.id", '=', "{$employeeTeamTableAs}.employee_id");
                        $join->on("{$employeeTeamTableAs}.team_id", 'IN', DB::raw($teamId))
                            ->whereNull("{$employeeTeamTableAs}.deleted_at");
                    });
            } else {
                $collection->leftJoin(
                    "{$employeeTeamTable} as {$employeeTeamTableAs}",
                    function ($join) use ($employeeTable, $employeeTeamTableAs
                    ) {
                        $join->on("{$employeeTable}.id", '=', "{$employeeTeamTableAs}.employee_id");
                    });
            }
        }

        //join team
        if ($teamId) {
            $collection->join(
                "{$teamTable} as {$teamTableAs}",
                function ($join) use ($teamId, $teamTableAs, $employeeTeamTableAs
            ) {
                $join->on("{$teamTableAs}.id", '=', "{$employeeTeamTableAs}.team_id");
                $join->on("{$teamTableAs}.id", 'IN', DB::raw($teamId));
                //use soft delete
//                if (Team::isUseSoftDelete()) {
//                    $join->whereNull("{$teamTableAs}.deleted_at");
//                }
            });
        } else {
            $collection->leftJoin(
                "{$teamTable} as {$teamTableAs}",
                function ($join) use ($teamTableAs, $employeeTeamTableAs
            ) {
                $join->on("{$teamTableAs}.id", '=', "{$employeeTeamTableAs}.team_id");
                //use soft delete
//                if (Team::isUseSoftDelete()) {
//                    $join->whereNull("{$teamTableAs}.deleted_at");
//                }
            });
        }

        //join role
        if ($teamId) {
            $collection->join(
                "{$roleTabel} as {$roleTabelAs}",
                function ($join) use ($roleTabelAs, $employeeTeamTableAs
            ) {
                $join->on("{$roleTabelAs}.id", '=', "{$employeeTeamTableAs}.role_id");
                $join->on("{$roleTabelAs}.special_flg", '=', DB::raw(Role::FLAG_POSITION));
                if (Role::isUseSoftDelete()) {
                    $join->whereNull("{$roleTabelAs}.deleted_at");
                }
            });
        } else {
            $collection->leftJoin(
                "{$roleTabel} as {$roleTabelAs}",
                function ($join) use ($roleTabelAs, $employeeTeamTableAs
            ) {
                $join->on("{$roleTabelAs}.id", '=', "{$employeeTeamTableAs}.role_id");
                $join->on("{$roleTabelAs}.special_flg", '=', DB::raw(Role::FLAG_POSITION));
                if (Role::isUseSoftDelete()) {
                    $join->whereNull("{$roleTabelAs}.deleted_at");
                }
            });
        }
        // join get role special
        $collection->leftJoin("{$memberRoleTable} as {$memberRoleTabelAs}",
                "{$memberRoleTabelAs}.employee_id", '=', "{$employeeTable}.id")
            ->leftJoin("{$roleTabel} as {$roleSpecialTabelAs}",
                "{$roleSpecialTabelAs}.id", '=', "{$memberRoleTabelAs}.role_id");
        if (!isset($options['select'])) {
            $collection->addSelect(Db::raw("GROUP_CONCAT(DISTINCT `{$roleSpecialTabelAs}`.`role` ORDER BY `{$roleSpecialTabelAs}`.`role` ASC" .
                    " SEPARATOR '; ') as role_special"));
        }
        // join contract type
        $workTbl = EmployeeWork::getTableName();
        $collection->leftJoin("{$workTbl}",
                "{$workTbl}.employee_id", '=', "{$employeeTable}.id");
        if (!isset($options['select'])) {
            $collection->addSelect("{$workTbl}.contract_type");
        }

        // join skillsheet status
        if (isset($options['isListPage'])) {

            $collection->leftJoin("{$tableEmplCvAttrValue}", function ($join) use ($employeeTable, $tableEmplCvAttrValue) {
                            $join->on("{$employeeTable}.id", '=', "{$tableEmplCvAttrValue}.employee_id")
                                 ->where("{$tableEmplCvAttrValue}.code", '=', 'status');
                        });
            if (!isset($options['select'])) {
                $collection->addSelect("{$tableEmplCvAttrValue}.value as valSkillSheet", "{$tableEmplCvAttrValue}.code as status");
            }
        }

        $collection->groupBy("{$employeeTable}.id");

        if ($isWorking) {
            $currentDay = date("Y-m-d");
            if ($isWorking == Team::WORKING) {
                $collection->where(function($query) use ($employeeTable, $currentDay){
                    $query->orWhereDate("{$employeeTable}.leave_date", ">=", $currentDay)
                            ->orWhereNull("{$employeeTable}.leave_date");
                });
            } else {
                $collection->where(function($query) use ($employeeTable, $currentDay, $options){
                    $query->whereNotNull("{$employeeTable}.leave_date");
                    if (isset($options['del_account'])) {
                        $query->whereDate("{$employeeTable}.leave_date", ">=", Carbon::now()->startOfMonth()->format('Y-m-d'))
                            ->whereDate("{$employeeTable}.leave_date", "<=", Carbon::now()->format('Y-m-d'));

                    } else {
                        $query->whereDate("{$employeeTable}.leave_date", "<", $currentDay);
                    }
                });
            }
        }
        // $collection->whereNotIn("{$employeeTable}.account_status", [getOptions::FAIL_CDD]);
        $filterStatus = Form::getFilterData('expect', "status", $urlFilter);
        if (isset($options['isListPage']) && $isWorking == Team::WORKING) {
            if ($filterStatus == getOptions::PREPARING) {
                $collection->whereIn("{$employeeTable}.account_status", [getOptions::PREPARING]);
            } elseif ($filterStatus == getOptions::WORKING) {
                $collection->whereNotIn("{$employeeTable}.account_status", [getOptions::PREPARING,getOptions::FAIL_CDD]);
            } else {
                $collection->whereNotIn("{$employeeTable}.account_status", [getOptions::FAIL_CDD]);
            }
        } else {
            $collection->whereNotIn("{$employeeTable}.account_status", [getOptions::FAIL_CDD]);
        }

        //$collection = $collection->where("{$employeeTableAs}.working_type", "!=", getOptions::WORKING_INTERNSHIP);
        self::filterGrid($collection, ['except'], $urlFilter, 'LIKE');
        $filterContractType = (array) Form::getFilterData('in', "{$workTbl}.contract_type", $urlFilter);
        if ($filterContractType) {
            $collection->whereIn("{$workTbl}.contract_type", $filterContractType);
        }
        $filterRoles = (array) Form::getFilterData('in', "{$roleSpecialTabelAs}.id", $urlFilter);
        if ($filterRoles) {
            $collection->whereIn("{$roleSpecialTabelAs}.id", $filterRoles);
        }
        $filterStatusSkillSheet = Form::getFilterData('except', "status", $urlFilter);
        if (isset($options['isListPage'])) {
            if ($filterStatusSkillSheet == 1) {
                $filterStatusSkillSheet = array("0", "1");
                $collection->where(function ($query) use ($filterStatusSkillSheet, $tableEmplCvAttrValue){
                        $query->whereIn("{$tableEmplCvAttrValue}.value", $filterStatusSkillSheet)
                            ->orWhere("{$tableEmplCvAttrValue}.value", '=', null);
                });
            } elseif ($filterStatusSkillSheet == '') {
                $filterStatusSkillSheet = array_keys(EmplCvAttrValue::getValueStatus());
                $collection->where(function ($query) use ($filterStatusSkillSheet, $tableEmplCvAttrValue) {
                    $query->whereIn("{$tableEmplCvAttrValue}.value", $filterStatusSkillSheet)
                        ->orWhere("{$tableEmplCvAttrValue}.value", '=', null);
                });
            } else {
                $collection->where("{$tableEmplCvAttrValue}.value", $filterStatusSkillSheet);
            }
        }
        if (!isset($options['return_builder'])) {
            if (Form::getFilterPagerData('order', $urlFilter)) {
                $collection->orderBy($pager['order'], $pager['dir']);
            } else {
                $collection->orderBy("{$employeeTable}.created_at", 'desc')
                            ->orderBy("{$employeeTable}.join_date", 'desc');
            }

            // filter member role to export
            self::filterMemberRole($collection, [
                $urlFilter,
                $employeeTable,
                $teamTable,
                $employeeTeamTable
            ]);

        }
        //check return query builder
        if (isset($options['return_builder'])) {
            return $collection;
        }

        if (isset($options['export']) || isset($options['del_account'])) {
            return $collection->get();
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * filter member role to export
     */
    public static function filterMemberRole(&$collection, $params = [])
    {
        list($urlFilter, $employeeTable, $teamTable, $employeeTeamTable) = $params;
        $filterMemberRole = Form::getFilterData('except', 'member_role', $urlFilter);
        if ($filterMemberRole) {
            switch ($filterMemberRole) {
                case getOptions::ROLE_SQA:
                    //list member in team QA or response for in skillsheet contain SQA or role in project workorder is SQA
                    $collection->where(function ($query) use ($employeeTable, $teamTable, $employeeTeamTable) {
                        $query->whereIn($employeeTable.'.id', function ($subQuery) {
                            $subQuery->select('employee_id')
                                ->from(ProjectMember::getTableName())
                                ->where('type', ProjectMember::TYPE_SQA)
                                ->where('status', ProjectMember::STATUS_APPROVED)
                                ->where('is_disabled', '!=', ProjectMember::STATUS_DISABLED);
                        })
                            ->orWhereIn($employeeTable . '.id', function ($subQuery) {
                                $subQuery->select('employee_id')
                                    ->from(EmplCvAttrValue::getTableName())
                                    ->where('code', 'role')
                                    ->where('value', 'like', '%"' . getOptions::ROLE_SQA . '"%');
                            })
                            ->orWhereIn($employeeTable.'.id', function ($subQuery) use ($teamTable, $employeeTeamTable) {
                                $subQuery->select('tmb.employee_id')
                                    ->from($employeeTeamTable . ' as tmb')
                                    ->join($teamTable . ' as team', 'tmb.team_id', '=', 'team.id')
                                    ->where('team.code', \Rikkei\Team\View\TeamConst::CODE_HN_QA)
                                    ->groupBy('tmb.employee_id');
                            });
                    });
                    break;
                default:
                    $mapProjRoles = ProjectMember::mapProjMemberRoles();
                    $collection->where(function ($query) use (
                        $employeeTable,
                        $filterMemberRole,
                        $mapProjRoles
                    ) {
                        $query->whereIn($employeeTable.'.id', function ($subQuery) use ($mapProjRoles, $filterMemberRole) {
                            $subQuery->select('employee_id')
                                ->from(ProjectMember::getTableName())
                                ->where('type', isset($mapProjRoles[$filterMemberRole]) ? $mapProjRoles[$filterMemberRole] : $filterMemberRole)
                                ->where('status', ProjectMember::STATUS_APPROVED);
                        })
                            ->orWhereIn($employeeTable . '.id', function ($subQuery) use ($filterMemberRole) {
                                $subQuery->select('employee_id')
                                    ->from(EmplCvAttrValue::getTableName())
                                    ->where('code', 'role')
                                    ->where('value', 'like', '%"' . $filterMemberRole . '"%');
                            });
                    });
                    break;
            }
        }
    }

    /**
     * find id systena team
     *
     * @return null|id
     */
    public static function findSystenaId()
    {
        $team = self::select('id')
            ->where('type', self::TEAM_TYPE_SYSTENA)
            ->first();
        if ($team) {
            return $team->id;
        }
        return null;
    }

    public static function getTeamByLeader($empId) {
        return self::where('leader_id', $empId)->first();
    }

    /**
     * Get all team_id that user is the leader
     *
     * @param $eId    Id of employee
     *
     * @return array   array team_id
    */
    public static function getListIdTeamIsLeader($eId)
    {
        return self::where('leader_id',$eId)->select('id')->lists('id')->toArray();
    }

    /**
     * Get teams by employee
     * $isLeader = true => get teams with user is leader
     *
     * @param int $empId
     * @param boolean $isLeader
     * @return Team collection
     */
    public static function getTeamByEmp($empId, $isLeader = false) {
        $team = self::join("team_members", "teams.id", "=", "team_members.team_id")
                ->where('team_members.employee_id', $empId);
        if ($isLeader) {
            $team->where('teams.leader_id', $empId);
        }
        $team->orderBy('teams.id', 'desc');
        return $team->select('teams.id', 'teams.name', 'teams.type', 'teams.leader_id')->first();
    }

    public static function getTeamById($teamId) {
        if ($team = CacheHelper::get(self::KEY_CACHE, self::DETAIL . $teamId)) {
            return $team;
        }
        $team = self::where('id', $teamId)->withTrashed()->first();
        CacheHelper::put(self::KEY_CACHE, $team, self::DETAIL . $teamId);
        return $team;
    }

    /*
     * get all team
     */
    public static function getAllTeam($isTrashed = false)
    {
        if ($teams = CacheHelper::get(self::KEY_CACHE)) {
            return $teams;
        }
        $teams = self::select('id', 'name', 'code', 'branch_code');
        if ($isTrashed) {
            $teams = $teams->withTrashed();
        }
         $teams = $teams->get();
        CacheHelper::put(self::KEY_CACHE, $teams);
        return $teams;
    }

    /**
     * get label of team
     * @return array
     */
    /*public static function getLabelTeam()
    {
        return [
            'RDN-BGD'               => TeamConst::CODE_BOD,
            'RJP-BGD'               => TeamConst::CODE_BOD,
            'TCT-BGD'               => TeamConst::CODE_BOD,
            'D0'                   => TeamConst::CODE_HN_D0,
            'D1'                   => TeamConst::CODE_HN_D1,
            'D2'                   => TeamConst::CODE_HN_D2,
            'D3'                   => TeamConst::CODE_HN_D3,
            'D5'                   => TeamConst::CODE_HN_D5,
            'PTPM-QA'                    => TeamConst::CODE_HN_QA,
            'RDN-PTPM'                    => TeamConst::CODE_DANANG,
            'PTPM-PROD'                    => TeamConst::CODE_HN_PRODUCTION,
            'TCT-PKT'                   => TeamConst::CODE_HN_HCTH,
            'TCT-HCTH'                   => TeamConst::CODE_HN_HCTH,
            'RDN_HCTH'                   => TeamConst::CODE_DANANG,
            'TCT-NS'                   => TeamConst::CODE_HN_HR,
            'RJP'                   => TeamConst::CODE_JAPAN,
            'TCT-SALES'                   => TeamConst::CODE_HN_SALES,
            'TCT-IT'                   => TeamConst::CODE_HN_IT,
            'RDN'                   => TeamConst::CODE_DANANG
        ];
    }*/

    /**
     * label team leader for upload member.
     * @return array
     */
    /*public static function lableTeamLear()
    {
        return [
            'Team Leader',
            'Trưởng phòng Kế toán',
            'Chủ tịch Hội Đồng Quản Trị'
        ];
    }*/

    /**
     * label sub leader for upload member.
     * @return array
     */
    /*public static function lableSubLear()
    {
        return [
            'Sub Leader'
        ];
    }*/

    /**
     * get position member in team
     * @param string
     * @return int
     */
    /*public static function getPositionTeam($namePosition)
    {
        $lableTeamLear = self::lableTeamLear();
        $lableSubLear = self::lableSubLear();
        if (in_array($namePosition, $lableTeamLear)) {
            return self::ROLE_TEAM_LEADER;
        } else if (in_array($namePosition, $lableSubLear)) {
            return self::ROLE_SUB_LEADER;
        } else {
            return self::ROLE_MEMBER;
        }
    }*/

    /**
     * label team specital for upload member.
     * @return array
     */
    /*public static function getLabelTeamSpecial()
    {
        return [
            'Giám đốc Chi nhánh Đà nẵng' => 'Rikkei - Danang',
            'Trưởng phòng nhân sự'       => 'Nhân sự',
            'Hành chính Kế toán Đà Nẵng' => 'Rikkei - Danang',
        ];
    }*/

    /**
     * label position special for upload member.
     * @param string
     * @return array
     */
    /*public static function labelPositionSpecial($namePosition)
    {
        $labelTeam = self::getLabelTeamSpecial();
        $teamId = self::getTeamIdByName($labelTeam[$namePosition]);
        return [
            'Giám đốc Chi nhánh Đà nẵng' => [
                                                'team_id' => $teamId,
                                                'role_id' => self::ROLE_TEAM_LEADER,
                                            ],
            'Trưởng phòng nhân sự'       => [
                                                'team_id' => $teamId,
                                                'role_id' => self::ROLE_TEAM_LEADER,
                                            ],
            'Hành chính Kế toán Đà Nẵng' => [
                                                'team_id' => $teamId,
                                                'role_id' => self::ROLE_MEMBER,
                                            ],
        ];
    }*/

    /**
     * label for upload member.
     * @param string
     * @return array
     */
    /*public static function teamSpecial($namePosition)
    {
        $labelPositionSpecial = self::labelPositionSpecial($namePosition);
        if (array_key_exists($namePosition, $labelPositionSpecial)) {
            $labelTeam = self::getLabelTeam();
            return $labelPositionSpecial[$namePosition];
        }
        return null;
    }*/

    /**
     * get team id by name for upload member
     * @param string
     * @return int
     */
    public static function getTeamIdByName($name)
    {
        $team = self::where('name', trim($name))
                    ->first();
        if ($team) {
            return $team->id;
        }
        return;
    }

    /**
     * get leader of team
     * @param int
     * @return int
     */
    public static function getLeaderOfTeam($id)
    {
        $team = self::find($id);
        if(!$team) {
            return null;
        }
        return $team->leader_id;
    }

    /**
     * get name of teams
     *
     * @param array $ids
     * @return array
     */
    public static function getTeamsName(array $ids)
    {
        $collection = self::select('name')
            ->whereIn('id', $ids)
            ->get();
        $result = [];
        if (!count($collection)) {
            return $result;
        }
        foreach ($collection as $item) {
            $result[] = $item->name;
        }
        return $result;
    }

    /**
     * Get team by type
     *
     * @param int $type
     * @return Team
     */
    public static function getTeamByType($type) {
        return self::where('type', $type)
                   ->select('*')
                   ->first();
    }

    public static function getTeamPQAByType() {
        return self::where('type', self::TEAM_TYPE_PQA)
            ->select('*')
            ->get();
    }

    public static function checkPqaLeader($id)
    {
        $pqaTeam = self::find(self::TEAM_PQA_ID);
        if ($pqaTeam->leader_id == $id) {
            return true;
        }
        return false;
    }

    public static function searchAjax($name, array $config = []) {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
            'typeExclude' => null
        ];
        $config = array_merge($arrayDefault, $config);
        $collection = self::select(['id', 'name'])
                    ->where('name', 'LIKE', '%' . $name . '%')
                    ->orderBy('name');

        self::pagerCollection($collection, $config['limit'], $config['page']);
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->name,
            ];
        }
        return $result;
    }

    public static function getTeamList($conditions = [], $columns = ['*']) {
        $teams = self::select($columns);
        if (is_array($conditions) && count($conditions)) {
            foreach ($conditions as $field => $value) {
                $teams->where($field, $value);
            }
        }
        return $teams->get();
    }

    /**
     * Get teams name by team id
     *
     * @param array $ids array team_id
     * @return array
     */
    public static function getNamesByIds($ids) {
        $teams = self::whereIn('id', $ids)->select('name')->get();
        $result = [];
        foreach ($teams as $team) {
            $result[] = $team->name;
        }

        return $result;
    }

    /**
     * get leader id of team by team code
     * @param string
     * @return int
     */
    public static function getLeaderIdByCode($teamCode)
    {
        $team = self::where('code', $teamCode)->first();

        if(!$team) {
            return null;
        }
        return $team->leader_id;
    }

    /**
     * Purpose : check leader of team by
     *
     * @param $employee_id
     *
     * @return bool
     */
    public static function checkLeaderOfTeamByTeamCode($teamCode, $employeeId)
    {
        return self::where('code', $teamCode)
                    ->where('leader_id', $employeeId)
                    ->count() ? true : false;
    }

    /**
     * get all team path
     *
     * @return array
     */
    public static function getTeamPathTree($withTrashed = true)
    {
        $prefixWithTrashed = $withTrashed ? '_withTrashed' : '';

        if ($result = CacheHelper::get(self::KEY_CACHE . $prefixWithTrashed)) {
            return $result;
        }
        $collection = self::select(['id', 'parent_id', 'name', 'code', 'is_soft_dev', 'type'])
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc');

        if ($withTrashed) {
            $collection->withTrashed();
        }

        $collection = $collection->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        self::getTeamPathTreeRecursive($collection, $result, 0);
        CacheHelper::put(self::KEY_CACHE . $prefixWithTrashed, $result);
        return $result;
    }

    /**
     * call recursive of team path tree
     *
     * @param collection $collection
     * @param array $result
     * @param int $idParentCheck
     * @return boolean
     */
    protected static function getTeamPathTreeRecursive(
        &$collection,
        &$result,
        $idParentCheck
    ) {
        if (!count($collection)) {
            return true;
        }
        foreach ($collection as $keyIndex => $item) {
            // init element result
            $item->id = (int) $item->id;
            if (!isset($result[$item->id])) {
                $result[$item->id] = [
                    'parent' => [],
                    'child' => [],
                    'data' => []
                ];
            }
            $result[$item->id]['data'] = [
                'name' => $item->name,
                'code' => $item->code,
                'is_soft_dev' => $item->is_soft_dev,
                'type' => $item->type,
            ];
            if ((int) $item->parent_id !== $idParentCheck) {
                continue;
            }
            if (!isset($result[$idParentCheck])) {
                $result[$idParentCheck] = [
                    'parent' => [],
                    'child' => [],
                    'data' => []
                ];
            }
            // insert array: parent in db + array parent of parent
            $result[$item->id]['parent'] =
                array_merge([$idParentCheck], $result[$idParentCheck]['parent']);
            // insert child element
            $result[$idParentCheck]['child'][] = $item->id;
            $collection->forget($keyIndex);
            self::getTeamPathTreeRecursive($collection, $result, $item->id);
        }
    }

    /**
     * get all leader of team
     *
     * @return array
     */
    public static function getAllLeaderTeam()
    {
        if ($result = CacheHelper::get(Employee::KEY_CACHE)) {
            return $result;
        }
        $tableEmployee = Employee::getTableName();
        $tableTeam = self::getTableName();

        $collection = self::select([$tableTeam.'.id as id',
            $tableTeam.'.leader_id', $tableEmployee.'.name'])
            ->join($tableEmployee, $tableEmployee.'.id', '=',
                $tableTeam.'.leader_id')
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $item->id = (int) $item->id;
            $result[$item->id] = [
                'id' => $item->leader_id,
                'name' => $item->name
            ];
        }
        CacheHelper::put(Employee::KEY_CACHE, $result);
        return $result;
    }

    /**
     * Get teams has not child
     *
     * @return Team
     */
    public static function getTeamsChildest($teamIds = null)
    {
        $result = self::whereRaw("(Select count(id) from teams as t where parent_id = teams.id and t.deleted_at is null) = 0");
        if ($teamIds) {
            $result->whereIn('id', $teamIds);
        }
        $result->select('id', 'name')
                ->where('is_soft_dev', self::IS_SOFT_DEVELOPMENT)
                ->orderBy('name');
        return $result->get();
    }

    public static function getTeamOfEmployee($empId)
    {
        return self::join('team_members', 'team_members.team_id', '=', 'teams.id')
                ->where('team_members.employee_id', $empId)
                ->select('teams.*')
                ->get();
    }

    /**
     * get teams by list employees
     * @param int $empIds
     * @return mixed
     */
    public static function getTeamOfEmployees($empIds)
    {
        return self::join('team_members', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('team_members.employee_id', $empIds)
            ->select('team_members.employee_id','teams.*')
            ->get();
    }
    /**
     * get branch holiday
     * @return array 
     */

    public static function getHolidaysByBranches() 
    {
        $listTeam = ['danang', 'hcm','japan','hanoi', 'hn'];
        $teamHolidays = [];
            foreach ($listTeam as $team) {
                $teamHolidays[$team] = CoreConfigData::getSpecialHolidays(2, $team);
                }
        return $teamHolidays;
    }



    public static function getTeamByName($name)
    {
        return self::where('name', $name)->first();
    }

    /**
     * get member of bod team
     */
    public static function getMemberOfBod()
    {
        $empTbl = Employee::getTableName();
        return Employee::select($empTbl.'.id', $empTbl.'.email', $empTbl.'.name', $empTbl.'.position')
                ->join(TeamMember::getTableName() . ' as tmb', $empTbl . '.id', '=', 'tmb.employee_id')
                ->join(self::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
                ->where('team.code', self::CODE_BOD)
                ->get();
    }

    /**
     * Check a employee is member of HR
     *
     * @param int $employeeId
     * @return boolean
     */
    public static function isMemberHr($employeeId)
    {
        $membersTeamHR = self::join('team_members', 'teams.id', '=', 'team_members.team_id')
                ->where('teams.type', self::TEAM_TYPE_HR)
                ->lists('employee_id')
                ->toArray();
        return in_array($employeeId, $membersTeamHR);
    }

    /**
     * get team type pqa
     * @return mixed
     */
    public static function getTeamTypePqa()
    {
        return self::where('type', Team::TEAM_TYPE_PQA)->orWhere('type', Team::TEAM_TYPE_QA)->lists('id')->toArray();
    }

    /**
     * get all team child ids
     * @param array|integer $teamIds
     * @param null|array $teams
     * @return array
     */
    public static function teamChildIds($teamIds, $teams = null, $withTrashed = null)
    {
        if ($teams === null) {
            $allTeams = Team::select(['id', 'parent_id'])->withoutGlobalScope(SmScope::class);
            if ($withTrashed) {
                $allTeams->withTrashed();
            }
            $allTeams = $allTeams->get()->toArray();
            $teams = [];
            foreach ($allTeams as $team) {
                $parentId = $team['parent_id'] === null ? '-1' : $team['parent_id'];
                $teams[$parentId][] = $team;
            }
        }
        $teamIds = (array)$teamIds;
        $teamChildIds = [];
        foreach ($teamIds as $teamId) { // convert for search by isset()
            $teamChildIds[$teamId] = $teamId;
        }
        while ($teamIds) {
            $teamId = array_shift($teamIds);
            if (isset($teams[$teamId])) {
                foreach ($teams[$teamId] as $team) {
                    $id = $team['id'];
                    if (!isset($teamChildIds[$id])) {
                        $teamChildIds[$id] = $id;
                        $teamIds[] = $id;
                    }
                }
            }
        }
        return $teamChildIds;
    }

    /**
     * get all team child ids
     * @param type $teamIds
     * @return array
     */
    public static function getTeamIdChildParent($teamIds)
    {
        if (!is_array($teamIds)) {
            $teamIds = [$teamIds];
        }
        $currTeamIds = self::whereIn('parent_id', $teamIds)->lists('id')->toArray();

        if (!$currTeamIds) {
            return $teamIds;
        }
        return array_unique(array_merge($teamIds, self::teamChildIds($currTeamIds)));
    }

    /*
     * get first team has code by team
     */
    public static function getFirstHasCode($team)
    {
        if (!data_get($team, 'code') && !data_get($team, 'parent_id')) {
            return null;
        }
        if ($team->code || !$team->parent_id) {
            return $team;
        }
        $parent = self::find($team->parent_id);
        if ($parent && $parent->code) {
            return $parent;
        }
        return self::getFirstHasCode($parent);
    }

    /*
     * list prefix branch
     */
    public static function listPrefixBranch()
    {
        return [
            self::CODE_PREFIX_HN => Lang::get('team::view.Hanoi'),
            self::CODE_PREFIX_DN => Lang::get('team::view.Danang'),
            self::CODE_PREFIX_HCM => Lang::get('team::view.HoChiMinh'),
            self::CODE_PREFIX_JP => Lang::get('team::view.Japan')
        ];
    }

    /**
     * Get prefix from team code
     *
     * @return string
     */
    public static function getTeamCodePrefix($teamCode)
    {
        if (empty($teamCode)) {
            return null;
        }
        return explode('_', $teamCode)[0];
    }

    /**
     * Get team codes of employee
     *
     * @param Employee $employee
     *
     * @return array
     */
    public static function getTeamCodePrefixOfEmployee($employee, $teamOfEmp = null)
    {
        if (is_object($employee)) {
            $employeeId = $employee->id;
        } else {
            $employeeId = $employee;
        }

        if (!empty($teamCodePrefix = CacheHelper::get(self::CACHE_TEAM_CODE_PREFIX, $employeeId))) {
            return $teamCodePrefix;
        }

        if (empty($teamOfEmp)) {
            $teamOfEmp = static::getTeamOfEmployee($employeeId);
        }

        $teamCodePrefix = [];
        if (!empty($teamOfEmp)) {
            foreach ($teamOfEmp as $team) {
                $codePre = static::getTeamCodePrefix($team->code);
                if ($codePre) {
                    $teamCodePrefix[] = $codePre;
                }
            }
        }

        // Lưu cache team code prefix của nhân viên
        CacheHelper::put(self::CACHE_TEAM_CODE_PREFIX, $teamCodePrefix, $employeeId);

        return $teamCodePrefix;
    }

    /**
     * Get only one team code prefix
     * Priority: 1. 'japan', 2. 'danang', 3. 'hanoi'
     *
     * @param Employee $employee
     *
     * @return string|null null when has not team
     */
    public static function getOnlyOneTeamCodePrefix($employee, $teams = null)
    {
        if (is_object($employee)) {
            $employeeId = $employee->id;
        } else {
            $employeeId = $employee;
        }

        $teamCodePrefix = static::getTeamCodePrefixOfEmployee($employeeId, $teams);
        if (count($teamCodePrefix)) {
            if (in_array(self::CODE_PREFIX_JP, $teamCodePrefix)) {
                return self::CODE_PREFIX_JP;
            }
            if (in_array(self::CODE_PREFIX_DN, $teamCodePrefix)) {
                return self::CODE_PREFIX_DN;
            }
            if (in_array(self::CODE_PREFIX_HCM, $teamCodePrefix)) {
                return self::CODE_PREFIX_HCM;
            }
            if (in_array(self::CODE_PREFIX_AI, $teamCodePrefix)) {
                return self::CODE_PREFIX_AI;
            }
            if (in_array(self::CODE_PREFIX_RS, $teamCodePrefix)) {
                return self::CODE_PREFIX_RS;
            }
        }
        return self::CODE_PREFIX_HN;
    }

    /**
     * Get only one team code prefix and change team khi team AI or RS
     *
     * @param $employee
     * @param null $team
     * @return string
     */
    public static function getOnlyOneTeamCodePrefixChange($employee, $team = null)
    {
        $teamCode = static::getOnlyOneTeamCodePrefix($employee, $team);
        return static::changeTeam($teamCode);
    }

    /*
     * get role of employee
     */
    public function getRoleByEmpId($empId)
    {
        $roleTbl = Role::getTableName();
        return Role::join(EmployeeTeamHistory::getTableName() . ' as tmb', $roleTbl . '.id', '=', 'tmb.role_id')
                ->where('tmb.team_id', $this->id)
                ->where('tmb.employee_id', $empId)
                ->select($roleTbl.'.*')
                ->orderBy('tmb.id', 'desc')
                ->first();
    }

    public function isTeamJapan()
    {
        return explode('_', $this->code)[0] === self::CODE_PREFIX_JP;
    }

    /*
     * get list team_id of employee
     *
     * @param $employeeId
     * @return collection.
     */
    public static function getListTeamOfEmp($employeeId)
    {
        return self::join("team_members", "teams.id", "=", "team_members.team_id")
                ->where('team_members.employee_id', $employeeId)
                ->get();
    }

    /*
     * get branch team follow code: hanoi, danang, japan.
     *
     * @return collection.
     */
    public static function getListBranchMainTeam()
    {
        $teamCode = ['hanoi', 'danang', 'japan', 'hcm'];
        $selfTable = self::getTableName();

        return self::select(
            "{$selfTable}.id",
            "{$selfTable}.name"
        )
            ->whereIn("{$selfTable}.code", $teamCode)
            ->get();
    }

    /**
     * get array team id of team
     * @param [int|null] $employeeId
     * @param [collection|null] $team
     * @return [array] team id
     */
    public static function getIdsTeam($employeeId = false, $team = false)
    {
        if (!$employeeId) {
            $employeeId = PermissionView::getInstance()->getEmployee()->id;
        }
        if (!$team) {
            $team = self::getListTeamOfEmp($employeeId);
        }
        $teamIds = [];
        foreach ($team as $value) {
            $teamIds[] = $value->id;
        }
        return $teamIds;
    }

    /**
     * Ajax get id and name of team
     * @param [string] $name
     * @param [array] $config
     * @return [collection]
     */
    public static function searchAjaxOrigin($name, array $config = [])
    {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
            'typeExclude' => null
        ];
        $config = array_merge($arrayDefault, $config);
        $collection = self::select(['id', 'name'])
                    ->where('name', 'LIKE', '%' . $name . '%')
                    ->where(function ($query) {
                        $query->where('parent_id', 1)
                            ->orWhereNull('parent_id');
                    })
                    ->orderBy('name');
        self::pagerCollection($collection, $config['limit'], $config['page']);
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->name,
            ];
        }
        return $result;
    }

    /**
     * Get projects from list id
     * @param array $ids
     * @param array $columns
     * @return Project collection
     */
    public static function getTeamByIds($ids, $columns = ['*'])
    {
        return self::whereIn('id', $ids)->select($columns)->get();
    }

    /**
     * get country code of team
     * @param [string] $teamCode
     * @return [string | null]
     */
    public static function getCountryCode($teamCode)
    {
        $vietNam = [self::CODE_PREFIX_HN, self::CODE_PREFIX_DN];
        $japan = [self::CODE_PREFIX_JP];

        if (in_array($teamCode, $japan)) {
            return 'jp';
        }
        return 'vn';
    }

    /**
     * Check that two employees belong to the same team?
     *
     * @param [int] $empId1
     * @param [int] $empId2
     * @return boolean
     */
    public static function checkSameTeam($empId1, $empId2)
    {
        $listTeamOfEmp1 = self::getListTeamOfEmp($empId1);
        $arrayTeamIdOfEmp1 = [];
        foreach ($listTeamOfEmp1 as $item) {
            $arrayTeamIdOfEmp1[] = $item->id;
        }

        $listTeamOfEmp2 = self::getListTeamOfEmp($empId2);
        $arrayTeamIdOfEmp2 = [];
        foreach ($listTeamOfEmp2 as $item) {
            $arrayTeamIdOfEmp2[] = $item->id;
        }
        $result = array_intersect($arrayTeamIdOfEmp1, $arrayTeamIdOfEmp2);

        return !empty($result);
    }

    /**
    * get leader by Id
    * @param [string] $idLeader
    * @return [object | null]
    */
    public static function getLeaderById($idLeader)
    {
        if (!empty($idLeader)) {
            $infoLeader = Employee::find($idLeader);
        } else {
            return null;
        }
        return $infoLeader;
    }

    /**
     * @param  [type] $teamCode
     * @return [type]
     */
    public static function changeTeam($teamCode)
    {
        switch ($teamCode) {
            case static::CODE_PREFIX_AI:
                $teamCode = static::CODE_PREFIX_HN;
                break;
            case static::CODE_PREFIX_RS:
                $teamCode = static::CODE_PREFIX_HCM;
                break;
            default:
                break;
        }
        return $teamCode;
    }

    /**
     * get document view link
     * @return type
     */
    public function getDocViewLink()
    {
        return route('doc::team.view', ['id' => $this->id, 'slug' => str_slug($this->name)]);
    }

    /**
     * get name of team
     * @param [int] $teamId
     * @return [string | null]
     */
    public static function getTeamNameById($teamId)
    {
        if (!empty($teamId)) {
            $team = self::where('id', $teamId)->withTrashed()->first();
        } else {
            return null;
        }
        return $team->name;
    }
    /**
     * get Team bob and pqa
     * @return [type]
     */
    public function getTeamBODPQA()
    {
        return self::select('id')
            ->where('code', TeamConst::CODE_BOD)
            ->orWhere('code', 'hanoi_finance')
            ->orWhere(function ($query) {
                $query->where('type', Team::TEAM_TYPE_PQA)
                    ->orWhere('type', Team::TEAM_TYPE_QA);
            })
            ->lists('id')->toArray();
    }

    /**
     * statistics data employees of team
     *
     * @param $arrayTeams
     * @param $teamListData
     * @param null $teamId
     * @param array $teamData
     * @return array
     */
    public static function getTeamDataRecursive($arrayTeams, $teamListData, $teamId = null, &$teamData = [])
    {
        $teamData = array_merge($teamData, isset($teamListData[$teamId]) ? $teamListData[$teamId] : []);
        foreach ($arrayTeams as $key => $team) {
            if ((int)$team['parent_id'] === $teamId) {
                unset($arrayTeams[$key]);
                self::getTeamDataRecursive($arrayTeams, $teamListData, $team['id'], $teamData);
            }
        }
        return $teamData;
    }

    public static function getHrEmailByRegion($region)
    {
        $selfTable = self::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $permission = new Permission;

        $sql = $permission->getEmployeeByActionName('sendMailToCandidate.candidate');
        $sql = DB::table(DB::raw("({$sql->toSql()}) as emps"))
            ->mergeBindings($sql->getQuery());

        return $sql->join($teamMemberTable, 'emps.id', '=', $teamMemberTable . '.employee_id')
            ->leftjoin($selfTable, $teamMemberTable . '.team_id', '=', $selfTable . '.id')
            ->where($selfTable.'.branch_code', $region)
            ->groupBy('emps.id')
            ->pluck('emps.email');
    }

    public static function getHrBranchByEmail($email)
    {
        $teamCode = Team::listPrefixByRegion();
        return TeamMember::leftjoin('teams', 'teams.id', '=', 'team_members.team_id')
            ->leftjoin('employees', 'employees.id', '=', 'team_members.employee_id')
            ->whereNull('teams.deleted_at')
            ->where('employees.email', $email)
            ->whereIn('teams.branch_code', $teamCode)
            ->groupBy('teams.id')
            ->pluck('teams.branch_code')
            ->first();
    }

    /**
     * get all employees of team in period time from $startDate to $endDate
     *
     * @param null|int $type
     * @param null|int $teamId
     * @param null|string $startDate
     * @param null|string $endDate
     * @param null|array $selectedFields
     * @return mixed
     */
    public static function getEmpsByTeamInPeriodTime($type = null, $teamId = null, $startDate = null, $endDate = null, $selectedFields = null)
    {
        $tblEmpTeamHistory = EmployeeTeamHistory::getTableName();
        $tblTeam = Team::getTableName();
        $tblEmp = Employee::getTableName();
        $collection = Employee::join($tblEmpTeamHistory, "{$tblEmpTeamHistory}.employee_id", '=', "{$tblEmp}.id")
            ->join($tblTeam, "{$tblTeam}.id", '=', "{$tblEmpTeamHistory}.team_id");
        if (!empty($type)) {
            $collection->where("{$tblTeam}.type", $type);
        }
        if (!empty($teamId)) {
            $collection->where("{$tblTeam}.id", $teamId);
        }
        if (!empty($startDate)) {
            $collection->where(function ($query) use ($tblEmpTeamHistory, $startDate) {
                $query->whereDate("{$tblEmpTeamHistory}.end_at", '>=', $startDate)
                    ->orWhereNull("{$tblEmpTeamHistory}.end_at");
            });
            $collection->where(function ($query) use ($tblEmp, $startDate) {
                $query->whereDate("{$tblEmp}.leave_date", '>=', $startDate)
                    ->orWhereNull("{$tblEmp}.leave_date");
            });
        }
        if (!empty($endDate)) {
            $collection->whereDate("{$tblEmpTeamHistory}.end_at", '<=', $endDate);
        }
        if (empty($selectedFields)) {
            $selectedFields = [
                "{$tblEmp}.id",
                "{$tblEmp}.email",
                "{$tblEmp}.name",
            ];
        }
        return $collection->select($selectedFields)
            ->groupBy("{$tblEmp}.id");
    }

    /**
     * Lấy all team cha theo team hiện tai
     *
     * @param $teamIds
     * @return array
     */
    public function getParentByTeam($teamIds)
    {
        $litsTeam = Team::getTeamPathTree();
        $teamParent = [];
        foreach ($teamIds as $value) {
            if (array_key_exists($value, $litsTeam)) {
                $teamParent = array_merge($teamParent, $litsTeam[$value]['parent']);
            }
        }
        return array_unique($teamParent);
    }


    /**
     * @return array
     */
    public function getAllBranch()
    {
        return [
            self::CODE_PREFIX_JP,
            self::CODE_PREFIX_DN,
            self::CODE_PREFIX_HCM,
            self::CODE_PREFIX_HN
        ];
    }

    /**
     *
     * @return array
     */
    public function teamBelongsToBranch()
    {
        return [
            self::CODE_PREFIX_AI => self::CODE_PREFIX_HN,
        ];
    }

    /**
     * @return mixed
     */
    public function setCacheholidaysCompensate()
    {
        if (CacheBase::get(self::CACHE_HOLIDAYS_COMPENSATE)) {
            CacheBase::forget(self::CACHE_HOLIDAYS_COMPENSATE);
        }
        $allBranch = $this->getAllBranch();
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);
        foreach ($allBranch as $branch) {
            $specialHolidays = CoreConfigData::getSpecialHolidays(2, $branch);
            $compensationDays = CoreConfigData::getCompensatoryDays($branch);
            $holidayWeekday[$branch] = [
                'annualHolidays' => $annualHolidays,
                'specialHolidays' => $specialHolidays,
                'compensationDays' => $compensationDays,
                'rangeTimes' => $rangeTimes,
            ];
        }
        $teamBlBranch = $this->teamBelongsToBranch();
        foreach ($teamBlBranch as $team => $branch) {
            $holidayWeekday[$team] = $holidayWeekday[$branch];
        }
        CacheBase::put(self::CACHE_HOLIDAYS_COMPENSATE, $holidayWeekday);
    }

    /**
     * get ngày nghỉ lễ, làm bù .. của các team trong tool
     *
     * @return mixed
     */
    public function getHolidaysCompensate()
    {
        if (!CacheBase::get(self::CACHE_HOLIDAYS_COMPENSATE)) {
            $this->setCacheholidaysCompensate();
        }
        return CacheBase::get(self::CACHE_HOLIDAYS_COMPENSATE);
    }

    /**
     * build table team member for member list
     * @return string
     */
    public static function buildTeamMemberTableForMemberList()
    {
        $tblTH = EmployeeTeamHistory::getTableName();
        $tblTeamHistoryAs = 'tbl_emp_team_history';
        $aryEmpIdLeave = Employee::whereDate('leave_date', '<', Carbon::now()->toDateString())->pluck('id')->toArray();
        $strEmpIds = implode(',', $aryEmpIdLeave);
        return "SELECT {$tblTH}.employee_id, {$tblTH}.team_id, {$tblTH}.role_id, {$tblTeamHistoryAs}.end_at"
            . " FROM {$tblTH}"
            . " INNER JOIN ("
            . "    SELECT employee_id, IF(MAX(end_at IS NULL) = 0, MAX(DATE(end_at)), NULL) AS end_at"
            . "    FROM {$tblTH}"
            . "    WHERE employee_id IN ({$strEmpIds}) AND deleted_at IS NULL"
            . "    GROUP BY employee_id) AS {$tblTeamHistoryAs}"
            . " ON {$tblTH}.employee_id = {$tblTeamHistoryAs}.employee_id"
            . "    AND (CASE WHEN {$tblTeamHistoryAs}.end_at IS NULL AND {$tblTH}.end_at IS NULL THEN 1"
            . "        WHEN DATE({$tblTH}.end_at) = {$tblTeamHistoryAs}.end_at THEN 1"
            . "        ELSE 0 END = 1)"
            . " WHERE {$tblTH}.deleted_at IS NULL"
            . " GROUP BY {$tblTH}.employee_id, {$tblTH}.team_id, {$tblTeamHistoryAs}.end_at";
    }

    /**
     * get all team information of employee
     * @param $empId
     * @return Collection
     */
    public function getTeamByEmpId($empId)
    {
        $tblTeam = Team::getTableName();
        $tblTM = TeamMember::getTableName();
        $tblEmp = Employee::getTableName();

        return Employee::select(
            "{$tblEmp}.id",
            "team.id as team_id",
            "team.name as team_name",
            "team.branch_code"
        )
        ->leftJoin("{$tblTM} as tm", 'tm.employee_id', '=',  "{$tblEmp}.id")
        ->leftJoin("{$tblTeam} as team", 'team.id', '=', 'tm.team_id')
        ->where("{$tblEmp}.id", $empId)
        ->get();
    }

    /**
     * get branch prefix by employee id, default hanoi
     * @param $empId
     * @return string
     */
    public function getBranchPrefixByEmpId($empId)
    {
        $teams = $this->getTeamByEmpId($empId);
        $branch = self::CODE_PREFIX_HN;
        $arrBranch = $teams->lists('branch_code')->toArray();
        if (!count($teams)) {
            return $branch;
        }
        if (in_array(self::CODE_PREFIX_JP, $arrBranch)) {
            $branch =  self::CODE_PREFIX_JP;
        }
        if (in_array(self::CODE_PREFIX_DN, $arrBranch)) {
            $branch = self::CODE_PREFIX_DN;
        }
        if (in_array(self::CODE_PREFIX_HCM, $arrBranch)) {
            $branch = self::CODE_PREFIX_HCM;
        }

        return $branch;
    }

    /**
     * Lấy branch của nhiều nhân viên
     * @param $empIds
     * @return mixed
     */
    public function getListTeamByEmpId($empIds)
    {
        $tblTeamMember = TeamMember::getTableName();
        $tbl = self::getTableName();
        $tblEmp = Employee::getTableName();

        return Employee::select(
            "{$tblEmp}.id as empId",
            "{$tblEmp}.employee_code",
            "{$tblEmp}.name as empName",
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(team_emp.name)) as team_name"),
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(team_emp.branch_code)) as branch_code")
        )
        ->leftJoin("{$tblTeamMember} as teamMem", "teamMem.employee_id", '=', "{$tblEmp}.id")
        ->join("{$tbl} as team_emp", "team_emp.id", '=', "teamMem.team_id")
        ->whereIn("{$tblEmp}.id", $empIds)
        ->groupBy("{$tblEmp}.id")
        ->get();
    }

    public static function listEmployeeStatus()
    {
        return [
            getOptions::PREPARING => trans('resource::view.Candidate.Detail.Preparing'),
            getOptions::WORKING => trans('resource::view.Candidate.Detail.Working')
        ];
    }

}
