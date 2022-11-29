<?php
namespace Rikkei\Sales\Seeds;

use DB;

class ProjectTypeUpdateSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
                'id' => '5',
                'name' => 'ONSITE',
            ]
        ];
        foreach ($dataDemo as $data) {
            if (! DB::table('css_project_type')->select('id')->where('name', $data['name'])->get()) {
                DB::table('css_project_type')->insert($data);
            }
        }
        $this->insertSeedMigrate();
    }
}
