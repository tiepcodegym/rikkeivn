<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\Validator;
use Rikkei\Resource\Model\Programs;
use Rikkei\Team\View\Config;
use Rikkei\Tag\Model\Tag;
use Rikkei\Team\Model\Employee;
use DB;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Carbon\Carbon;
use function redirect;

class EmployeeSkill extends CoreModel
{
    const KEY_CACHE = 'employee_skill';
    protected $table = 'employee_skill_levels';
    protected $fillable = [
        'tag_id', 'type', 'level', 'exp_y', 'exp_m',
    ];

    /**
     * save employee skill
     * 
     * @param int $employeeId
     * @param array $skillIds
     * @param array $skills
     * @param int $type
     */
    public static function saveItems($employeeId, $skillIds = [], $skills = [], $type = null, $profile = false)
    {
        if (! $type) {
            $type = Skill::TYPE_PROGRAM;
        }
        $skillTable = Skill::getTableName();
        self::where('employee_id', $employeeId)
            ->whereIn('skill_id', function ($query) use ($skillTable, $type , $skillIds, $profile) {
                $query->from($skillTable)
                    ->select('id')
                    ->where('type', $type);
                if($profile) {
                    $query->whereIn('id', $skillIds);
                }
            })->delete();
            
        if (! $skills || ! $skillIds || ! $employeeId) {
            return;
        }
        $skillAdded = [];
        
        $typeSkills = Skill::getAllType();
        $tblName = $typeSkills[$type];
        foreach ($skillIds as $key => $skillId) {
            if (! isset($skills[$key]) || 
                ! isset($skills[$key]["employee_{$tblName}"]) ||
                ! $skills[$key]["employee_{$tblName}"] || 
                in_array($skillId, $skillAdded)) {
                continue;
            }
            $employeeSkillData = $skills[$key]["employee_{$tblName}"];
            $arrayRule = [
                'level' => 'required|max:255',
                'experience' => 'required|max:255',
            ];
            $validator = Validator::make($employeeSkillData, $arrayRule);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->send();
            }
            $employeeSkillItem = new self();
            $employeeSkillItem->setData($employeeSkillData);
            $employeeSkillItem->skill_id = $skillId;
            $employeeSkillItem->employee_id = $employeeId;
            $employeeSkillItem->updated_at = date('Y-m-d H:i:s');
            $employeeSkillItem->save();
            $skillAdded[] = $skillId;
        }
    }
    
    /**
     * get skills follow employee
     *
     * @param type $employeeId
     * @param mixed $type
     * @return object model
     */
    public static function getItemsFollowEmployee($employeeId, $type = null)
    {
        if ($type == null ) {
            $type = Skill::TYPE_PROGRAM;
        }
        $thisTable = self::getTableName();
        $skillTable = Skill::getTableName();
        
        return self::select('level', 'experience', 'name', 'image', 'id', 'type')
            ->join($skillTable, "{$skillTable}.id", '=', "{$thisTable}.skill_id")
            ->where('employee_id', $employeeId)
            ->where('type',$type)
            ->get();
    }
    
    /**
     * get Gridview skill by employeeId
     * @param int $employeeId
     * @param null|int $type
     * @return Collection
     */
    public static function getGridItemsFollowEmployee($employeeId, $type = null)
    {
        if ($type == null ) {
            $type = Skill::TYPE_PROGRAM;
        }
        $thisTable = self::getTableName();
        $skillTable = Skill::getTableName();
        
        $collection = self::select('level',  'experience', 'skills.name as name', 'skills.id as id', 'type')
            ->join($skillTable, "{$skillTable}.id", '=', "{$thisTable}.skill_id")
            ->where('employee_id', $employeeId);
        if ($type !== Skill::TYPE_ALL) {
            $collection->where('type', $type);
        }
        $pager = Config::getPagerData(null, ['order' => "{$thisTable}.updated_at", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy("{$thisTable}.updated_at", 'ASC');
        $collection = self::filterGrid($collection, [], null, 'REGEXP', $employeeId);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     *
     * @param type $collection
     * @param type $except
     * @param type $urlSubmitFilter
     * @param type $compare
     * @return type
     * @description rewrite filterGrid
     */
    public static function filterGrid(&$collection, $except = array(), $urlSubmitFilter = null, $compare = 'REGEXP', $employeeId=null)
    {
        $filter = \Rikkei\Core\View\Form::getFilterData(null, null, $urlSubmitFilter);
        if ($filter && count($filter)) {
            foreach ($filter as $key => $value) {
                if (in_array($key, $except)) {
                    continue;
                }
                if (is_array($value)) {
                    if ($key == 'number' && $value) {
                        foreach ($value as $col => $filterValue) {
                            if ($filterValue == 'NULL') {
                                $collection = $collection->whereNull($col);
                            } else {
                                $collection = $collection->where($col, $filterValue);
                            }
                        }
                    } elseif ($key == 'in' && $value) {
                        foreach ($value as $col => $filterValue) {
                            $collection = $collection->whereIn($col, $filterValue);
                        }
                    } else {
                        if (isset($value['from']) && $value['from']) {
                            $collection = $collection->where($key, '>=', $value['from']);
                        }
                        if (isset($value['to']) && $value['to']) {
                            $collection = $collection->where($key, '<=', $value['to']);
                        }
                    }
                } else {
                    if ($key == 'skills.name') {
                        $ids = Programs::checkExistLikeName($value, $employeeId);
                        if (! empty($ids)) {
                            $collection->whereRaw("(`skills`.`name` LIKE '%$value%' or (`skills`.`name` in (?) and `skills`.`type`=?))", [
                                $ids,
                                Skill::TYPE_PROGRAM
                            ]);
                        } else {
                            $collection = $collection->where($key, 'LIKE', addslashes("%$value%"));
                        }
                    } else {
                        switch ($compare) {
                            case 'LIKE':
                                $collection = $collection->where($key, $compare, addslashes("%$value%"));
                                break;
                            default:
                                $collection = $collection->where($key, $compare, addslashes("$value"));
                        }
                    }
                }
            }
        }
        return $collection;
    }

    /**
     * get all skill of employee
     *
     * @param type $employeeId
     * @return type
     */
    public static function getAllSkill($employeeId)
    {
        $pager = Config::getPagerData(null, [
            'order' => 'updated_at',
            'dir' => 'DESC'
        ]);
        $collection = self::select(['id', 'name', 'type', 'level', 'exp_y', 'exp_m'])
            ->where('employee_id', $employeeId)
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * get skill of employee
     *
     * @param int $employeeId
     * @return collection
     */
    public static function getSkillInCv($employeeId)
    {
        $collection = self::select(['id', 'tag_id', 'type', 'level', 'exp_y', 'exp_m'])
            ->where('employee_id', $employeeId)
            ->orderBy('type', 'asc')
            ->orderBy('tag_id', 'asc')
            ->get();
        $result = [
            'os' => [],
            'language' => [],
            'database' => [],
            'tagData' => [],
        ];
        if (!count($collection)) {
            return $result;
        }
        $tagIds = [];
        foreach ($collection as $item) {
            if (!in_array($item->type, ['os', 'language', 'database'])) {
                continue;
            }
            $result[$item->type][$item->id] = $item;
            $tagIds[] = $item->tag_id;
        }
        $tagCollection = Tag::select(['id', 'value'])
            ->whereIn('id', $tagIds)
            ->get();
        foreach ($tagCollection as $item) {
            $result['tagData'][$item->id] = $item->value;
        }
        return $result;
    }

    /**
     * get skill of employee
     *
     * @param int $employeeId
     * @return collection
     */
    public static function getSkillIdsInCv($employeeId, $isGroup = true)
    {
        $collection = self::select(['id', 'tag_id', 'type', 'level', 'exp_y', 'exp_m'])
            ->where('employee_id', $employeeId)
            ->orderBy($isGroup ? 'type' : DB::raw(
                'CASE WHEN type="language" THEN "a" '
                . 'WHEN type="frame" THEN "b" ELSE type END'
            ), 'asc')
            ->orderBy('level', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();
        if (!$isGroup) {
            return $collection;
        }
        $result = [];
        if (!count($collection)) {
            return $result;
        }
        foreach ($collection as $item) {
            $result[$item->type][$item->id] = $item;
        }
        return $result;
    }

    /**
     * remove skill expert
     *
     * @param array $ids
     */
    public static function removeBulk(array $ids)
    {
        self::whereIn('id', $ids)
            ->delete();
    }

    /**
     * Get result data language programming follow skillsheet in current month.
     *
     * @param $int $year
     * @param int $teamId
     * @return collection.
     */
    public static function getDataLanguageProgrammingSkillSheets($year = null, $teamId = null)
    {
        $empTable = Employee::getTableName();
        $thisTable = self::getTableName();
        $tagTable = Tag::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $teamTable = Team::getTableName();
        $currYear = (int)Carbon::now()->format('Y');
        $collection = Tag::select("{$tagTable}.value as name", DB::raw("count(distinct {$thisTable}.employee_id) as number"))
                    ->join($thisTable, "{$tagTable}.id", '=', "{$thisTable}.tag_id")
                    ->leftJoin($empTable, "{$empTable}.id", '=', "{$thisTable}.employee_id")
                    ->leftJoin($teamMemberTable, "{$teamMemberTable}.employee_id", '=', "{$empTable}.id")
                    ->leftJoin($teamTable, "{$teamMemberTable}.team_id", '=', "{$teamTable}.id")
                    ->where("{$thisTable}.type", '=', 'language');
        if ($teamId) {
            $collection->where("{$teamTable}.id", '=', $teamId);
        }
        if ($year) {
            $collection->whereYear("{$thisTable}.created_at", "=", $year);
        }
        if ($year && $year <= $currYear) {
            $collection->where(function ($query) use ($empTable, $year) {
                $query->orWhereNull("{$empTable}.leave_date")
                        ->orWhere(function ($query2) use ($empTable, $year) {
                            $query2->whereNotNull("{$empTable}.leave_date")
                                    ->whereYear("{$empTable}.leave_date", '>', $year);
                        });
            });
        }
        return $collection->groupBy("{$tagTable}.value")
                        ->get();
    }
}
