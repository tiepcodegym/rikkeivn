<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Team\Model\Team;
use Rikkei\Project\Model\TaskHistory;

class TaskTeam extends CoreModel
{
    const TEST_RESULT_PASS = 1;
    const TEST_RESULT_NOT_PASS = 2;
    
    const ASSIGN_DEPART_REPRESENT = 1;
    const ASSIGN_TESTER = 2;
    const ASSIGN_EVALUATOR = 3;
    
    const KEY_CACHE = 'task_teams';
    
    protected $table = 'task_teams';
    protected $fillable = ['task_id', 'team_id'];
    
    /**
     * get team of task
     * 
     * @param model $task
     * @return array
     */
    public static function getNcmTeams($task)
    {
        if ($result = CacheHelper::get(self::KEY_CACHE)) {
            return $result;
        }
        $collection = self::select('team_id')
            ->where('task_id', $task->id)
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $result[] = $item->team_id;
        }
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }
    
    /**
     * 
     * @param type $task
     * @return type
     */
    public static function getNcmTeamAndName($task)
    {
        if ($result = CacheHelper::get(self::KEY_CACHE)) {
            return $result;
        }
        $tableNcmTeam = self::getTableName();
        $tableTeam = Team::getTableName();
        
        $collection = Team::select($tableTeam.'.name')
            ->join($tableNcmTeam, $tableNcmTeam.'.team_id', '=', $tableTeam.'.id')
            ->where($tableNcmTeam.'.task_id', $task->id)
            ->get();
        if (!count($collection)) {
            return null;
        }
        $result = '';
        foreach ($collection as $item) {
            $result .= $item->name . ', ';
        }
        $result = substr($result, 0, -2);
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }

    public static function delByIssue($data)
    {
        return self::where('task_id', $data)->delete();
    }
    
    /**
     * insert and update ncm teams
     * 
     * @param type $task
     * @param array $teamIds
     */
    public static function insertNcmTeams($task, array $teamIds)
    {
        $oldTeamIds = self::getNcmTeams($task);
        // not change
        if (!array_diff($oldTeamIds, $teamIds) &&
            !array_diff($teamIds, $oldTeamIds)
        ) {
            return true;
        }
        DB::beginTransaction();
        try {
            // delete old data if diff
            self::where('task_id', $task->id)
                ->delete();
            $dataInsert = [];
            foreach ($teamIds as $item) {
                $dataInsert[] = [
                    'task_id' => $task->id,
                    'team_id' => $item
                ];
            }
            self::insert($dataInsert);
            CacheHelper::forget(self::KEY_CACHE);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
