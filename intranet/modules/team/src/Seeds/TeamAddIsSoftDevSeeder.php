<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Team;

class TeamAddIsSoftDevSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('TeamIsSoftDevSeeder-v1')) {
            return true;
        }
       
        DB::beginTransaction();
        try {
            $this->insertSeedMigrate();
            //update team
            DB::table('teams')
                ->whereIn('name',['Rikkei Đà Nẵng - PTPM'])
                ->update(['is_soft_dev' => '1']);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        
    }
}
