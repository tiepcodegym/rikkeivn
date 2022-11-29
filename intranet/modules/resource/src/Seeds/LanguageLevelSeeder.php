<?php
namespace Rikkei\Resource\Seeds;

use DB;
use Rikkei\Resource\Model\LanguageLevel;

class LanguageLevelSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        DB::beginTransaction();
        try {
            $data = [
                [
                    'name' => 'N1',
                    'language_id' => 2
                ],
                [
                    'name' => 'N2',
                    'language_id' => 2
                ],
                [
                    'name' => 'N3',
                    'language_id' => 2
                ],
                [
                    'name' => 'N4',
                    'language_id' => 2
                ],
                [
                    'name' => 'N5',
                    'language_id' => 2
                ],
            ];
            LanguageLevel::insert($data);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            $ex.getMessage();
        }
    }
}
