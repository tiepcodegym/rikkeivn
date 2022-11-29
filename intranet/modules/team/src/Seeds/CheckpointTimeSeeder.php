<?php
namespace Rikkei\Team\Seeds;

use DB;

class CheckpointTimeSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
                'check_time' => '3/2017',
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

        //delete old data
        $checktime = DB::table('checkpoint_time')
                    ->where('check_time', '9/2016')
                    ->select('id')
                    ->first();
        if ($checktime) {
            $checkpoints = DB::table('checkpoint')
                    ->where('checkpoint_time_id', $checktime->id)
                    ->get();
            if (count($checkpoints)) {
                foreach ($checkpoints as $checkpoint) {
                    $results = DB::table('checkpoint_result')
                            ->where('checkpoint_id', $checkpoint->id)
                            ->get();
                    foreach ($results as $result) {
                        DB::table('checkpoint_result_detail')
                            ->where('result_id', $result->id)
                            ->delete();

                    }
                    DB::table('checkpoint_result')
                            ->where('checkpoint_id', $checkpoint->id)
                            ->delete();
                }
            }

            DB::table('checkpoint')
                ->where('checkpoint_time_id', $checktime->id)
                ->delete();
            DB::table('checkpoint_time')
                    ->where('id', $checktime->id)
                    ->delete();
        }

        $this->insertSeedMigrate();
    }
}
