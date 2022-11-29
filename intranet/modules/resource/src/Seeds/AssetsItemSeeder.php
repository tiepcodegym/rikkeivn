<?php
namespace Rikkei\Resource\Seeds;

use Illuminate\Database\Seeder;
use DB;
use Rikkei\Resource\Model\AssetsTypes;

class AssetsItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataDemo = [
            [
                'type_id' => AssetsTypes::getIdByName('Phần cứng'),
                'name' => 'Màn hình máy tính',
            ],
            [
                'type_id' => AssetsTypes::getIdByName('Phần cứng'),
                'name' => 'Bàn phím máy tính',
            ],
            [
                'type_id' => AssetsTypes::getIdByName('Phần cứng'),
                'name' => 'Chuột máy tính',
            ],
            [
                'type_id' => AssetsTypes::getIdByName('Phần cứng'),
                'name' => 'Case máy tính',
            ],
            [
                'type_id' => AssetsTypes::getIdByName('Phần cứng'),
                'name' => 'Lap top cá nhân',
            ],
            [
                'type_id' => AssetsTypes::getIdByName('Tài sản thông tin'),
                'name' => 'Tài liệu thông tin',  
            ],

        ];
        
        foreach ($dataDemo as $data) {
            if (! DB::table('assets_items')->select('id')->where('name', $data['name'])->get()) {
                DB::table('assets_items')->insert($data);
            }
        }
        
    }
}
