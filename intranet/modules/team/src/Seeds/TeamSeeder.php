<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Team;

class TeamSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }
        $dataFilePath = RIKKEI_TEAM_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 'seed' . 
                DIRECTORY_SEPARATOR .  'team.php';
        if (! file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        if (! $dataDemo || ! count($dataDemo)) {
            return;
        }
        DB::beginTransaction();
        try {
            $this->createTeamRecursive($dataDemo, null, 0);
            $this->insertSeedMigrate();
            //update team
            /*DB::table('teams')
                ->whereIn('name',['D1','Production','D2', 'D3', 'D5','QA','Systena','Rikkei - Danang'])
                ->update(['is_soft_dev' => '1']);*/
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        
    }
    
    /**
     * create team item demo
     * 
     * @param array $data
     * @param int $parentId
     * @param int $permissionAsId
     */
    protected function createTeamRecursive($data, $parentId, $permissionAsId = 0)
    {
        foreach ($data as $key => $item) {
            $dataChild = null;
            if (isset($item['child'] ) && count($item['child']) > 0) {
                $dataChild = $item['child'];
                unset($item['child']);
            }
            $itemDataAddtional = [
                'parent_id' => $parentId,
                'sort_order' => $key + 1
            ];
            if (! isset($item['follow_team_id'])) {
                $itemDataAddtional['follow_team_id'] = $permissionAsId;
            }
            if (isset($item['flag_permission_children']) && $item['flag_permission_children']) {
                $permissionAsId = true;
                unset($item['flag_permission_children']);
            }
            $item = array_merge($item, $itemDataAddtional);
            $team = Team::where('name', $item['name'])
                    ->where('parent_id', $parentId)
                    ->first();
            if (!$team) {
                $team = new Team();
                $team->setData($item);
                $team->save();
            }
            if ($dataChild) {
                if ($permissionAsId === true) {
                    $permissionAsId = $team->id;
                }
                $this->createTeamRecursive($dataChild, $team->id, $permissionAsId);
            }
        }
    }
}
