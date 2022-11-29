<?php
namespace Rikkei\Core\Seeds;

use DB;

class ApiTokenSeeder extends CoreSeeder
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
        $data = [
            [
                'key' => 'project.api_token',
                'value' => '8063ff4ca1e41df7bc90c8ab6d0f6207d491cf6dad7c66ea797b4614b71922e97257k775',
            ],
        ];
        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                if (! DB::table('core_config_datas')->select('id')->where('key', $item['key'])->get()) {
                    DB::table('core_config_datas')->insert($item);
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
