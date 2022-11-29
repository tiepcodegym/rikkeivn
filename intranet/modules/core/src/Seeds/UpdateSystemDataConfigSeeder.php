<?php
namespace Rikkei\Core\Seeds;

use Illuminate\Support\Facades\DB;

class UpdateSystemDataConfigSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('UpdateSystemDataConfigSeeder-V1')) {
            return;
        }
        $dataKey = [
            'key' => 'address_company',
            'value' => '<strong>Head Office: </strong>21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi.
<strong>Da Nang Office:</strong> 11F VietNam News Agency Build., 81 Quang Trung St., Hai Chau Dist., Da Nang City.
<strong>Ho Chi Minh Office:</strong> 7th Floor Maritime Safety South Building, 42 Tu Cuong St., Ward 4, Tan Binh Dist., Ho Chi Minh City, Vietnam.
<strong>Japan Office:</strong> 3F, Tamachi 16th Fujishima Building, 4-13-4 Shiba, Minato-ku, Tokyo, Japan.',
        ];
        DB::beginTransaction();
        try {
            $config = DB::table('core_config_datas')->where('key', $dataKey['key'])->first();
            if (!$config) {
                DB::table('core_config_datas')->insert($dataKey);
            } else {
                DB::table('core_config_datas')->where('key', $dataKey['key'])->update(['value' => $dataKey['value']]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
