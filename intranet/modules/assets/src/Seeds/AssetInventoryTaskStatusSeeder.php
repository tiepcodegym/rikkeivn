<?php

namespace Rikkei\Assets\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\Task;
use Illuminate\Support\Facades\DB;

class AssetInventoryTaskStatusSeeder extends CoreSeeder
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
            Task::where('status', 0)
                    ->update(['status' => Task::STATUS_NEW]);

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
