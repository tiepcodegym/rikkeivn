<?php
namespace Rikkei\Sales\Seeds;

use DB;

class CssMailConfigSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        $dataDemo = [
            [
                'key' => 'cssmail',
                'value' => 'Rikkeisoft pqa,hungnt2@rikkeisoft.com,jptifcobqhlvvauc',
            ]
        ];
        foreach ($dataDemo as $data) {
            if (! DB::table('core_config_datas')->select('id')->where('key', $data['key'])->get()) {
                DB::table('core_config_datas')->insert($data);
            }
        }
        $this->insertSeedMigrate();
    }
}
