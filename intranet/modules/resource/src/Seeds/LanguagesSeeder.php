<?php
namespace Rikkei\Resource\Seeds;

use DB;

class LanguagesSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
                'name' => 'English',
            ],
            [
                'name' => 'Japanese',
            ],
            [
                'name' => 'Chinese',
            ],
            [
                'name' => 'France',
            ]
        ];
        
        foreach ($dataDemo as $data) {
            if (! DB::table('languages')->select('id')->where('name', $data['name'])->get()) {
                DB::table('languages')->insert($data);
            }
        }
        $this->insertSeedMigrate();
    }
}
