<?php
namespace Rikkei\Core\Seeds;

use Rikkei\Core\Model\MenuItem;
use Illuminate\Support\Facades\DB;

class RemoveMenuItemSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('RemoveMenuItemSeeder-v2')) {
            return true;
        }
        $dataRemove = [
            'test/manage/passwords'
        ];
        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            MenuItem::whereIn('url', $dataRemove)->delete();
            $this->insertSeedMigrate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
