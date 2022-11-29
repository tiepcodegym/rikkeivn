<?php
namespace Rikkei\Team\Seeds;

use DB;

class CheckpointTime3_2018Seeder extends \Rikkei\Core\Seeds\CoreSeeder
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
                'check_time' => '3/2018',
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]
        ];
        foreach ($dataDemo as $data) {
            if (! DB::table('checkpoint_time')
                    ->select('id')
                    ->where('check_time', $data['check_time'])
                    ->get()
            ) { 
                DB::table('checkpoint_time')->insert($data);
            }
        }
        $this->insertSeedMigrate();
    }
}
