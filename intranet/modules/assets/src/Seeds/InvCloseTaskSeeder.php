<?php

namespace Rikkei\Assets\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Assets\Model\InventoryItem;
use Rikkei\Assets\View\AssetConst;
use Rikkei\Project\Model\Task;
use Illuminate\Support\Facades\DB;

class InvCloseTaskSeeder extends CoreSeeder
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
            $taskIds = InventoryItem::where('status', '!=', AssetConst::INV_RS_NOT_YET)
                    ->lists('task_id')
                    ->toArray();
            if ($taskIds) {
                Task::whereIn('id', $taskIds)
                        ->where('status', '!=', Task::STATUS_CLOSED)
                        ->update(['status' => Task::STATUS_CLOSED]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
