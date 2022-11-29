<?php
namespace Rikkei\Sales\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;

class UpdateDeletedAtCssTableSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('UpdateDeletedAtCssTableSeeder-v1')) {
            return;
        }
        DB::beginTransaction();
        try {
            DB::table('css')
                ->where('deleted_at', '0000-00-00 00:00:00')
                ->update(['deleted_at' => null]);
            DB::table('css_result')
                ->where('deleted_at', '0000-00-00 00:00:00')
                ->update(['deleted_at' => null]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
