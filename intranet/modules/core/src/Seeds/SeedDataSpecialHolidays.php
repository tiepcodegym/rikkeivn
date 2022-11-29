<?php
namespace Rikkei\Core\Seeds;

use Rikkei\Core\Model\CoreConfigData;
use DB;

class SeedDataSpecialHolidays extends \Rikkei\Core\Seeds\CoreSeeder
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
        $data = CoreConfigData::getValueDb('project.special_holidays');
        $dataKey = [
            'key' => 'project.special_holidays_hn',
            'value' => $data,
        ];
        DB::beginTransaction();
        try {
            $specialHoliday = \DB::table('core_config_datas')->where('key', $dataKey['key'])->first();
            if (!$specialHoliday) {
                DB::table('core_config_datas')->insert($dataKey);
            } else {
                \DB::table('core_config_datas')->where('key', $dataKey['key'])->update(['value' => $data]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
