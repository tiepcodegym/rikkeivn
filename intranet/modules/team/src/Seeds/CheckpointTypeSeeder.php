<?php
namespace Rikkei\Team\Seeds;

use DB;

class CheckpointTypeSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
                'name' => 'Dev',
            ],
            [
                'name' => 'QA',
            ]
        ];
        foreach ($dataDemo as $data) {
            if (! DB::table('checkpoint_type')
                    ->select('id')
                    ->where('name', $data['name'])
                    ->get()
            ) {
                DB::table('checkpoint_type')->insert($data);
            }
        }
        $this->insertSeedMigrate();
    }
}
