<?php
namespace Rikkei\Resource\Seeds;

use DB;

class ProgramsUpdatePrimaryChartSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
            DB::table('programming_languages')->whereIn('name', ['PHP', 'Java', 'Android', 'Ios', '.Net'])
                ->update(['primary_chart' => 1]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            $ex.getMessage();
        }
        
    }
}
