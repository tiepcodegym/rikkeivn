<?php
namespace Rikkei\ManageTime\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\Core\Model\CoreConfigData;
use DB;

class WktDefaultSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(5)) {
            return;
        }

        DB::beginTransaction();
        try {
            $data = [
                'start1' => '07:00',
                'end1' => '08:30',
                'start2' => '12:00',
                'end2' => '13:30',
                'min_mor' => 4,
                'min_aft' => 3,
                'max_end_mor' => '12:00',
                'max_end_aft' => '19:00'
            ];
            $key = ManageTimeConst::KEY_RANGE_WKTIME;
            $rangeTimes = CoreConfigData::where('key', $key)
                    ->first();
            if (!$rangeTimes) {
                CoreConfigData::create([
                    'key' => ManageTimeConst::KEY_RANGE_WKTIME,
                    'value' => serialize($data)
                ]);
            } /*else {
                $rangeTimes->value = serialize($data);
                $rangeTimes->save();
            }*/
            // default email related
            $emailRelated = CoreConfigData::where('key', 'working_time_relator_vn')
                    ->first();
            if (!$emailRelated) {
                CoreConfigData::create([
                    'key' => 'working_time_relator_vn',
                    'value' => 'phuongntl@rikkeisoft.com'
                ]);
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
        }
    }
}
