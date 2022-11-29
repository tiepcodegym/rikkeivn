<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\DB;
use Exception;

class TeamRenameSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $teamRemoveObject = Team::select('id')
            ->whereIn('name', ['Android', 'iOS'])
            ->get();
        if (!count($teamRemoveObject)) {
            $this->insertSeedMigrate();
            return true;
        }
        $teamRemove = [];
        foreach ($teamRemoveObject as $item) {
            $teamRemove[] = $item->id;
        }
        $teamReplace = 5;
        $tableReplace = [
            'request_team',//2 6 // have id
            'css_team', //15 0
            'task_teams',// 1 1
            // 'teams_feature',
            // 'team_members',
            // 'team_projects',
            'team_projs', // 39,
            'team_members',
        ];
        DB::beginTransaction();
        try {
            $this->replaceData('request_team', 'team_id', [
                'request_id',
                'position_apply'
            ], $teamRemove, $teamReplace);
            
            $this->replaceData('css_team', 'team_id', [
                'css_id',
            ], $teamRemove, $teamReplace);
            
            $this->replaceData('task_teams', 'team_id', [
                'task_id',
            ], $teamRemove, $teamReplace);
            
            $this->replaceData('team_projs', 'team_id', [
                'project_id',
            ], $teamRemove, $teamReplace);
            
            $this->replaceData('team_members', 'team_id', [
                'employee_id',
                'role_id',
            ], $teamRemove, $teamReplace);
            
            Team::whereIn('id', $teamRemove)->delete();
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * replace data in table
     * 
     * @param string $table
     * @param string $colTeam
     * @param array $colsPrimary
     * @param array $teamRemove
     * @param array $teamReplace
     */
    protected function replaceData(
            $table, 
            $colTeam, 
            $colsPrimary, 
            array $teamRemove, 
            $teamReplace
    ) {
        // find data unique of replace data
        $collectionReplace = DB::table($table)
            ->select($colTeam)
            ->addSelect($colsPrimary)
            ->where($colTeam, $teamReplace)
            ->get();
        $uniqueData = [];
        foreach ($collectionReplace as $itemReplace) {
            $stringUniqueKey = $teamReplace;
            foreach ($colsPrimary as $col) {
                $stringUniqueKey .= '-' . $itemReplace->{$col};
            }
            $uniqueData[] = $stringUniqueKey;
        }
        
        $collection = DB::table($table)->select($colTeam)
            ->addSelect($colsPrimary)
            ->whereIn($colTeam, $teamRemove)
            ->get();
        foreach ($collection as $item) {
            $flagDelete = false;
            // init key for unique data
            $stringUniqueKey = $teamReplace;
            foreach ($colsPrimary as $col) {
                $stringUniqueKey .= '-' . $item->{$col};
            }
            // if exist key, remove item
            if (in_array($stringUniqueKey, $uniqueData)) {
                $itemRemove = DB::table($table)
                    ->where($colTeam, $item->{$colTeam});
                foreach ($colsPrimary as $col) {
                    $itemRemove->where($col, $item->{$col});
                }
                $itemRemove->delete();
                continue;
            } 
            $uniqueData[] = $stringUniqueKey;
        }
        // update
        DB::table($table)
            ->whereIn($colTeam, $teamRemove)
            ->update([
                $colTeam => $teamReplace
            ]);
    }
}
