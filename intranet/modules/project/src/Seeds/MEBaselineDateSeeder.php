<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Core\Model\CoreConfigData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MEBaselineDateSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('MEBaselineDateSeeder-v1')) {
            return;
        }
        DB::beginTransaction();
        try {
            $item = CoreConfigData::getItem('project.me.baseline_date');
            $item->value = 25;
            $item->save();
            $item = CoreConfigData::getItem('project.me.baseline_day');
            $item->value = Carbon::FRIDAY;
            $item->save();
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
