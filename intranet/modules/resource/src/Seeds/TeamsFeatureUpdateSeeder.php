<?php
namespace Rikkei\Resource\Seeds;

use DB;

class TeamsFeatureUpdateSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     * Transfer data to new table request team
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
            DB::table('teams_feature')
                ->whereIn('name',['D1','Production','D2', 'Mobile', 'Android','iOS', 'D3', 'D5','QA','Systena','Rikkei - Danang', 'Strategy'])
                ->update(['is_soft_dev' => 1]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            $ex.getMessage();
        }
        
    }
}
