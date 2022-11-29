<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;

class UpdateProgramsSeeder extends CoreSeeder
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
                'name' => 'Front end',
            ],
            [
                'name' => 'Back end',
            ],
            [
                'name' => 'Fullstack',
            ],
            [
                'name' => 'Blockchain',
            ],
        ];
        DB::beginTransaction();
        try {
            foreach ($dataDemo as $data) {
                if (! DB::table('programming_languages')->select('id')->where('name', $data['name'])->get()) {
                    DB::table('programming_languages')->insert($data);
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        
    }
}
