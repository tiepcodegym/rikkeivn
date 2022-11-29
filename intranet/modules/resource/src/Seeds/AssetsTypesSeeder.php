<?php
namespace Rikkei\Resource\Seeds;

use Illuminate\Database\Seeder;
use DB;

class AssetsTypesSeeder extends Seeder
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
                'name' => 'Phần cứng',
            ],
            [
                'name' => 'Phần mềm',
            ],
            [
                'name' => 'Tài sản thông tin',
            ]
        ];
        
        foreach ($dataDemo as $data) {
            if (! DB::table('assets_types')->select('id')->where('name', $data['name'])->get()) {
                DB::table('assets_types')->insert($data);
            }
        }
        
    }
}
