<?php
namespace Rikkei\Tag\Seeds;

use DB;
use Rikkei\Tag\Model\Field;
use Rikkei\Core\Seeds\CoreSeeder;

class TagSetSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('TagSetSeeder-v2')) {
            return true;
        }
        $dataFilePath = RIKKEI_TAG_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 
            'tag-set.php';
        if (!file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        DB::beginTransaction();
        try {
            foreach ($dataDemo as $item) {
                $field = Field::find($item['id']);
                if (!$field) {
                    $field = new Field();
                }
                $field->setData($item)
                    ->save();
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        
    }
}
