<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;
use Rikkei\Core\Model\MenuItem;

class RemoveActionIdMenuCheckpointListSeeder extends CoreSeeder
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
            MenuItem::where('name', 'Danh sách checkpoint')
                ->update([
                    'action_id' => null,
                ]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
