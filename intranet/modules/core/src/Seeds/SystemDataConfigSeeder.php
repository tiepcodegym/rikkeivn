<?php
namespace Rikkei\Core\Seeds;

use DB;

class SystemDataConfigSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'key' => 'web_url',
                'value' => 'http://rikkeisoft.com/',
            ],
            [
                'key' => 'face_url',
                'value' => 'https://www.facebook.com/rikkeisoft/',
            ],
            [
                'key' => 'youtube_url',
                'value' => 'https://www.youtube.com/channel/UCg4sqAGemXn5basWdzxEbVg',
            ],
            [
                'key' => 'email_contact',
                'value' => 'contact@rikkeisoft.com',
            ],
            [
                'key' => 'address_company',
                'value' => '<strong>Head Office: </strong>21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi.
<strong>Da Nang Office:</strong> 4th Floor, Massda Building, An Don, An Hai Bac, Son Tra, Da Nang.
<strong>Japan Office:</strong> 3rd Floor, Ishige Building, 4-9-3 Shiba, Minato-ku, Tokyo, Japan.',
            ],
        ];

        foreach ($data as $item) {
            if (! DB::table('core_config_datas')->select('id')->where('key', $item['key'])->get()) {
                DB::table('core_config_datas')->insert($item);
            }
        }
    }
}
