<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Team;
use Rikkei\Core\Seeds\CoreSeeder;

class TeamUpdateSeeder extends CoreSeeder
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
                DIRECTORY_SEPARATOR .  'team_update.php';
        if (!file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        if (!$dataDemo || !count($dataDemo)) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach ($dataDemo as $itemData) {
                $this->updateTeam($itemData);
            }
            $this->insertSeedMigrate();
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
    protected function updateTeam($data)
    {
        $team = Team::where('name', $data['name'])
            ->first();
        if (!$team) {
            return true;
        }
        $team->setData($data)->save();
        return true;
    }
}
