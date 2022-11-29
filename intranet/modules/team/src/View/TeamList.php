<?php

namespace Rikkei\Team\View;

use Rikkei\Team\Model\Team;
use Lang;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Arr;
use DB;
use Rikkei\Team\Model\TeamMember;

class TeamList
{
    const KEY_CACHE_ALL_CHILD = 'team_list_all_child';
    const KEY_CACHE_ALL_TEAM = 'team_list_all';

    /**
     * Team List tree
     * 
     * @return type
     */
    public static function getTreeHtml($idActive = null)
    {
        $html = '<ul class="treeview team-tree">';
        $html .= self::getTreeDataRecursive(null, 0, $idActive);
        $html .= '</ul>';
        return $html;
    }
    
    /**
     * get team tree option recursive
     * 
     * @param int $id
     * @param int $level
     */
    protected static function getTreeDataRecursive($parentId = null, $level = 0, $idActive = null) 
    {
        $teamList = Team::select('id', 'name', 'parent_id')
                ->where('parent_id', $parentId)
                ->orderBy('sort_order', 'asc')
                ->get();
        $countCollection = count($teamList);
        if (!$countCollection) {
            return;
        }
        $html = '';
        $i = 0;
        foreach ($teamList as $team) {
            $classLi = '';
            $classLabel = 'team-item';
            $optionA = " data-id=\"{$team->id}\"";
            $classA = '';
            if ($i == $countCollection - 1) {
                $classLi = 'last';
            }
            if ($team->id == $idActive) {
                $classA .= 'active';
            }
            $classLi = $classLi ? " class=\"{$classLi}\"" : '';
            $classLabel = $classLabel ? " class=\"{$classLabel}\"" : '';
            $classA = $classA ? " class=\"{$classA}\"" : '';

            $hrefA = route('team::setting.team.view', ['id' => $team->id]);
            $html .= "<li{$classLi}>";
            $html .= "<label{$classLabel}>";
            $html .= "<a href=\"{$hrefA}\"{$classA}{$optionA} level='$level'>";
            $html .= $team->name;
            $html .= '</a>';
            $html .= '</label>';
            $htmlChild = self::getTreeDataRecursive($team->id, $level + 1, $idActive);
            if ($html) {
                $html .= '<ul>';
                $html .= $htmlChild;
                $html .= '</ul>';
            }
            $html .= '</li>';
        }
        return $html;
    }

    /**
     * Team List to option
     *
     * @param null $skipId
     * @param bool $isFunction
     * @param bool $valueNull
     * @param $isGetLeader
     * @return array
     */
    public static function toOption($skipId = null, $isFunction = false, $valueNull = true, $isGetLeader = null, $hasContainsTrashed = true)
    {
        $options = [];
        if ($valueNull) {
            $options[] = [
                'label' => Lang::get('team::view.--Please choose--'),
                'value' => '',
                'option' => '',
            ];
        }
        $teamList = CacheHelper::get(self::KEY_CACHE_ALL_TEAM);
        if (!$teamList) {
            $collection = Team::select(['name', 'id', 'leader_id', 'is_soft_dev', 'code', 'parent_id', 'is_function'])
                ->orderBy('sort_order', 'asc');
            if ($skipId) {
                $collection->whereNotIn('id', (array) $skipId);
            }
            if ($isGetLeader) {
                $leaderIds = TeamMember::where('role_id', Team::ROLE_TEAM_LEADER)->pluck('employee_id')->toArray();
                $collection->join(DB::raw('(select id, name as leader_name from employees where id in ' . $leaderIds . ') as emp'), 'emp.id', '=', 'teams.leader_id')
                    ->addSelect('leader_name');
            }
            if (!$hasContainsTrashed) {
                $collection->whereNull('teams.deleted_at');
            }
            $teamList = $collection->get()->toArray();
            CacheHelper::put(self::KEY_CACHE_ALL_TEAM, $teamList);
        }

        (new self())->genDataTeam($options, $teamList, $parent_id = null, $char = '', $isFunction, true);
        return $options;
    }
    
        /**
     * Team List to option
     *
     * @param null $skipId
     * @param bool $isFunction
     * @param bool $valueNull
     * @param $isGetLeader
     * @return array
     * @param int $leaderId
     */

    public static function toOptionWithChild($skipId = null,$isFunction = false, $valueNull = true, $isGetLeader = null, $leaderid)
    {
        $options = [];
        if ($valueNull) {
            $options[] = [
                'label' => Lang::get('team::view.--Please choose--'),
                'value' => '',
                'option' => '',
            ];
        }
        $teamList = false; //CacheHelper::get(self::KEY_CACHE_ALL_TEAM);
        if (!$teamList) {
            $collection = Team::select(['name', 'id', 'leader_id', 'is_soft_dev', 'code', 'parent_id', 'is_function'])
            ->where('leader_id',$leaderid)
                ->orderBy('sort_order', 'asc');
                
           /* if ($skipId) {
                $collection->whereNotIn('id', (array) $skipId);
            }
            if ($isGetLeader) {
                $leaderIds = TeamMember::where('role_id', Team::ROLE_TEAM_LEADER)->pluck('employee_id')->toArray();
                $collection->join(DB::raw('(select id, name as leader_name from employees where id in ' . $leaderIds . ') as emp'), 'emp.id', '=', 'teams.leader_id')
                    ->addSelect('leader_name');
            }
            */
            $teamList = $collection->get()->toArray();
            //CacheHelper::put(self::KEY_CACHE_ALL_TEAM, $teamList);
        }
        foreach($teamList as $team) {
            
            (new self())->genDataTeam($options, [$team],  $team['parent_id'], $char = '', $isFunction, true);
            (new self())->toOptionFunctionRecursive($options, $team['id'],  null, true, +1);
        }
        return $options;
    }

    /**
     * Team list to option recuresive call all child
     * 
     * @param array $options
     * @param int $parentId
     * @param int|null $skipId
     * @param boolean $isFunction
     * @param int $level
     */
    protected static function toOptionFunctionRecursive(&$options, $parentId, $skipId, $isFunction, $level, $hasPrefix = true)
    {
        $teamList = CacheHelper::get(self::KEY_CACHE_ALL_CHILD, $parentId);
        if (!$teamList) {
            $teamList = Team::select('id', 'name', 'parent_id', 'is_function', 'follow_team_id', 'leader_id', 'is_soft_dev', 'code')
                ->where('parent_id', $parentId)
                ->orderBy('sort_order', 'asc');
            if ($skipId) {
                $teamList->whereNotIn('id', (array) $skipId);
            }
            $teamList = $teamList->get();
            CacheHelper::put(self::KEY_CACHE_ALL_CHILD, $teamList, $parentId);
        }
        $countCollection = count($teamList);
        if (!$countCollection) {
            return;
        }
        $prefixLabel = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $level);
        if (!$hasPrefix) {
            $prefixLabel = str_repeat(str_repeat('&nbsp;', 8), $level);
        }
        foreach ($teamList as $team) {
            if ($isFunction && (!$team->is_function || $team->permission_as)) {
                $optionMore = ' disabled';
            } else {
                $optionMore = '';
            }
            $leaderName = '';
            if($team->leader_id) {
                $leader = Employee::getEmpById($team->leader_id);
                if ($leader) {
                    $leaderName = $leader->name;
                }
            }
            $optionItem = [
                'label' => $prefixLabel . $team->name,
                'value' => $team->id,
                'option' => $optionMore,
                'leader_name' => $leaderName,
                'leader_id' => $team->leader_id,
                'is_soft_dev' => $team->is_soft_dev,
                'code' => $team->code,
                'parent_id' => $team->parent_id,
            ];
            if (!$hasPrefix) {
                $optionItem['label'] = $team->name;
                $optionItem['prefix'] = $prefixLabel;
            }
            $options[] = $optionItem;
            self::toOptionFunctionRecursive($options, $team->id, $skipId, $isFunction, $level + 1, $hasPrefix);
        }
    }
    
    /**
     * Team list to checkbox
     * @param type $skipId
     * @param type $isFunction
     * @return array
     */
    public static function toCheckbox($skipId = null, $isFunction = false)
    {
        $options = [];
        self::toOptionFunctionRecursive($options, null, $skipId, $isFunction, 0, false);
        return $options;
    }

    /**
     * get list team child ids of selected team
     * @param type $teamId
     * @return array
     */
    public static function getTeamChildIds ($teamId) {
        if (!is_array($teamId)) {
            $teamId = [$teamId];
        }
        $subParentIds = Team::whereIn('parent_id', $teamId)
                ->lists('id')->toArray();
        if (!$subParentIds) {
            return $teamId;
        }
        return array_merge($teamId, self::getTeamChildIds($subParentIds));
    }

    /**
     * get all team dev and team leaf
     * 
     * @return type
     */
    public static function getTeamLeaf()
    {
        if ($result = CacheHelper::get(Team::KEY_CACHE, null, false)) {
            return $result;
        }
        $result = [];
        $teamsArray = [];
        self::getTeamTreeOptionRecursive($teamsArray, null, $path = '', 0);
        self::getTeamLeafRec($result, $teamsArray);
        CacheHelper::put(Team::KEY_CACHE, $result, null, false);
        return $result;
    }

    /**
     * get team leaf 
     *
     * @param type $result
     * @param type $teamsArray
     */
    protected static function getTeamLeafRec(&$result, $teamsArray)
    {
        foreach ($teamsArray as $teamId => $teamData) {
            if (isset($teamData['child']) && $teamData['child']
                && $teamId != 35 // spec ptpm dn - remove after
            ) {
                self::getTeamLeafRec($result, $teamData['child']);
                continue;
            }
            $result['leaf'][$teamId] = $teamData['data']['name'];
            if ($teamData['data']['is_soft_dev']) {
                if (preg_match('/^Product|QA$/i', $teamData['data']['name'])) {
                    $result['qa'][] = $teamId;
                } else {
                    $result['dev'][] = $teamId;
                }
            }
            if (isset($teamData['child']) && $teamData['child']
                && $teamId == 35 // spec ptpm dn - remove after
            ) {
                self::getTeamLeafRec($result, $teamData['child']);
            }
        }
    }

    /**
     * get team tree option recursive
     * 
     * @param int $id
     * @param int $level
     */
    protected static function getTeamTreeOptionRecursive(
        &$result,
        $parentId = null,
        $path = '',
        $level = 0
    ) {
        $teamCollection = Team::select(['id', 'name', 'parent_id', 'is_soft_dev'])
            ->where('parent_id', $parentId)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        if (!count($teamCollection)) {
            return null;
        }
        foreach ($teamCollection as $team) {
            $newPath = $path ? $path . '.' . $team->id : $team->id;
            $result = Arr::add($result, $newPath, [
                'data' => [
                    'name' => $team->name,
                    'is_soft_dev' => $team->is_soft_dev,
                    'level' => $level
                ],
            ]);
            self::getTeamTreeOptionRecursive($result, $team->id, $newPath . '.child', $level + 1);
        }
    }

    /*
     * get all team list
     */
    public static function getList($options = [])
    {
        return Team::getCollections(array_merge([
            'select' => ['id', 'name', 'parent_id'],
            'orderby' => [
                ['column' => 'sort_order', 'dir' => 'asc']
            ],
        ], $options));
    }

    /**	
     * get name team deleted	
     * @param int $id
     * @return Collection
     */	
    public static function getNameTeamDeleted($id)	
    {
        return DB::table('teams')	
            ->select('name')	
            ->where('id', $id)	
            ->whereNotNull('deleted_at')	
            ->first();	
    }

    /**
     * get deleted team list (deleted_at != null)
     *
     * @param array $aryId
     * @return array
     */
    public function getDeletedTeamIds($aryId)
    {
        return Team::withTrashed()
            ->whereIn('id', $aryId)
            ->whereNotNull('deleted_at')
            ->pluck('id')
            ->toArray();
    }

    /**
     * get team list by permission scope
     * @param bool $isScopeCopany
     * @param integer|array $teamFilterId
     * @param integer $employeeId
     * @return collection
     */
    public static function getEmployeeTeamList($isScopeCopany = true, $teamFilterId = null, $employeeId = null)
    {
        $teamTbl = Team::getTableName();
        $collect = Team::select($teamTbl.'.id', $teamTbl.'.name', $teamTbl.'.parent_id');
        if ($teamFilterId) {
            $collect->whereIn($teamTbl.'.id', Team::teamChildIds($teamFilterId));
        }
        if ($isScopeCopany) {
            return $collect->get();
        }
        if (!$employeeId) {
            $employeeId = auth()->id();
        }
        $tmbTbl = \Rikkei\Team\Model\TeamMember::getTableName();
        $collect->join($tmbTbl . ' as tmb', 'tmb.team_id', '=', $teamTbl.'.id')
                ->where('tmb.employee_id', $employeeId);
        return $collect->get();
    }

    public static function sortParentChilds($collection, $parent = null, $depth = 0)
    {
        $results = [];
        if ($collection->isEmpty()) {
            return $results;
        }
        foreach ($collection as $key => $item) {
            if ($item->parent_id == $parent) {
                $aryItem = $item->toArray();
                $aryItem['depth'] = $depth;
                $results[] = $aryItem;
                $childs = self::sortParentChilds($collection, $item->id, $depth + 1);
                if ($childs) {
                    $results = array_merge($results, $childs);
                }
            }
        }
        return $results;
    }

    /**
     * get all team follow code
     * @param  [type] $code
     * @return [type]
     */
    public static function getTeamCode($code)
    {
        $parent = Team::select("id")
        ->where('code', 'LIKE', '%' . $code . '%')
        ->where(function ($query) {
            $query->where('parent_id', '=', Team::MAX_LEADER)
                ->orWhereNull('parent_id');
        })
        ->whereNull('deleted_at')
        ->first();
        if (!$parent) {
            return [];
        }
        return self::getTeamChildIds($parent->id);
    }

    /*
     * get soft dev team is left (not parent of other teams)
     */
    public static function getSoftDevLeafTeams($options = [])
    {
        $teamTbl = Team::getTableName();
        $collection = Team::select($teamTbl . '.id', $teamTbl . '.name')
            ->leftJoin($teamTbl . ' as team1', function ($join) use ($teamTbl) {
                $join->on($teamTbl . '.id', '=', 'team1.parent_id')
                    ->whereNull('team1.deleted_at');
            })
            ->whereNull('team1.id')
            ->where($teamTbl . '.is_soft_dev', 1)
            ->orderBy($teamTbl . '.parent_id', 'asc')
            ->orderBy($teamTbl . '.name', 'asc')
            ->groupBy($teamTbl . '.id');
        if (isset($options['team_id'])) {
            $teamIds = is_array($options['team_id']) ? $options['team_id'] : [$options['team_id']];
            $teamIds = Team::teamChildIds($teamIds);
            $collection->whereIn($teamTbl . '.id', $teamIds);
        }
        return $collection->get();
    }

    /**
     * get option data team
     *
     * @param array $options
     * @param array $teamList
     * @param int $parentId
     * @param bool $isFunction
     * @param bool $hasPrefix
     * @param string $char
     */
    public function genDataTeam(&$options, $teamList, $parentId = null, $char = '', $isFunction = false, $hasPrefix = true)
    {
        if (empty($teamList)) {
            return;
        }
        foreach ($teamList as $key => $team) {
           
            if ($team['parent_id'] == $parentId) {
                if ($isFunction && (!$team['is_function'])) {
                    $optionMore = ' disabled';
                } else {
                    $optionMore = '';
                }
                $optionItem = [
                    'label' => $char . $team['name'],
                    'value' => $team['id'],
                    'option' => $optionMore,
                    'leader_id' => $team['leader_id'],
                    'is_soft_dev' => $team['is_soft_dev'],
                    'leader_name' => !empty($team['leader_name']) ? $team['leader_name'] : '',
                    'code' => $team['code'],
                    'parent_id' => $team['parent_id'],
                ];

                if (!$hasPrefix) {
                    $optionItem['label'] = $team->name;
                    $optionItem['prefix'] = $char;
                }
                $options[] = $optionItem;
                unset($teamList[$key]);
                $this->genDataTeam($options, $teamList, $team['id'], $char . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $isFunction, $hasPrefix);
            }
        }
    }

    /**
     * reset cache team list
     * @return [array]
     */
    public function resetCacheTeamList()
    {
        CacheHelper::forget(self::KEY_CACHE_ALL_TEAM);
        return self::toOption(null, true, false);
    }

    /**
     * Lấy team prefix của nhiều nhân viên
     * @param $empIds
     * @return array
     */
    public function getTeamPrefixOfEmpIds($empIds)
    {
        $objTeam = new Team();
        $collection = $objTeam->getListTeamByEmpId($empIds);
        $data = [];
        foreach ($empIds as $empId) {
            $data[$empId] = Team::CODE_PREFIX_HN;
        }
        if (count($collection)) {
            foreach ( $collection as $item) {
                $arrBranch = explode(',', $item->branch_code);
                if (in_array(Team::CODE_PREFIX_JP, $arrBranch)) {
                    $team = Team::CODE_PREFIX_JP;
                } elseif (in_array(Team::CODE_PREFIX_DN, $arrBranch)) {
                    $team = Team::CODE_PREFIX_DN;
                } elseif (in_array(Team::CODE_PREFIX_HCM, $arrBranch)) {
                    $team = Team::CODE_PREFIX_HCM;
                } elseif (in_array(Team::CODE_PREFIX_AI, $arrBranch)) {
                    $team = Team::CODE_PREFIX_AI;
                } else {
                    $team = Team::CODE_PREFIX_HN;
                }
                $data[$item->empId] = $team;
            }
        }
        return $data;
    }
}
