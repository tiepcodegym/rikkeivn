<?php
namespace Rikkei\Education\Seeds;

use DB;

class EducationTypeSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    protected $table = 'education_types';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(3)) {
            return true;
        }
        $dataDemo = [
            [
                'code' => 'cm',
                'name' => 'Chuyên môn',
            ],
            [
                'code' => 'knm',
                'name' => 'Kỹ năng mềm',
            ],
            [
                'code' => 'ql',
                'name' => 'Quản lý',
            ]
        ];
        foreach ($dataDemo as $data) {
            if (! DB::table($this->table)
                    ->select('id')
                    ->where('name', $data['name'])
                    ->where('code', $data['code'])
                    ->get()
            ) {
                DB::table($this->table)->insert($data);
            }
        }
        $this->insertSeedMigrate();
    }
}
