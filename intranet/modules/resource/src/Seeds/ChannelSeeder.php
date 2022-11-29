<?php
namespace Rikkei\Resource\Seeds;

use Illuminate\Database\Seeder;
use DB;

class ChannelSeeder extends Seeder
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
                'name' => 'vieclam24h',
            ],
            [
                'name' => 'vietnamwork',
            ],
            [
                'name' => 'ITviec',
            ],
            [
                'name' => 'facebook',
            ],
            [
                'name' => 'timviecnhanh.com',
            ],
            [
                'name' => 'jobstreet.com',
            ],
            [
                'name' => 'careelink.vn',
            ],
            [
                'name' => 'Trung tâm iviettech',
            ],
            [
                'name' => 'Các trường ĐH',
            ]
        ];
        
        foreach ($dataDemo as $data) {
            if (! DB::table('recruit_channel')->select('id')->where('name', $data['name'])->get()) {
                DB::table('recruit_channel')->insert($data);
            }
        }
        
    }
}
