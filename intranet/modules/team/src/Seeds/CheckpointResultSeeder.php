<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Team;

class CheckpointResultSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        DB::beginTransaction();
        try {
            //update team_id
            DB::table('checkpoint_result')->join('checkpoint', 'checkpoint_result.checkpoint_id', '=', 'checkpoint.id')
                            ->update(['checkpoint_result.team_id' => DB::raw("`checkpoint`.`team_id`")]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        
    }
}
