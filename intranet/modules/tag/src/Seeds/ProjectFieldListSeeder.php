<?php
namespace Rikkei\Tag\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Tag\Model\Field;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Tag\View\TagConst;
use Exception;
use Rikkei\Tag\Model\Tag;

class ProjectFieldListSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(9)) {
            return true;
        }
        $dataFilePath = RIKKEI_TAG_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 
            'project-field-list.php';
        if (!file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        DB::beginTransaction();
        try {
            $this->createRecursive($dataDemo, TagConst::SET_TAG_PROJECT);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * create field item demo
     * 
     * @param array $data
     * @param int $parentId
     */
    protected function createRecursive($data, $parentId)
    {
        foreach ($data as $key => $item) {
            $dataChild = null;
            if (isset($item['child'] ) && count($item['child']) > 0) {
                $dataChild = $item['child'];
                unset($item['child']);
            }
            $itemDataAddtional = [
                'parent_id' => $parentId,
                'sort_order' => $key + 1,
                'set' => TagConst::SET_TAG_PROJECT
            ];
            $item = array_merge($item, $itemDataAddtional);
            $field = Field::where('name', $item['name'])
                    ->where('parent_id', $parentId)
                    ->first();
            if (isset($item['tags'])) {
                $tagsData = $item['tags'];
                unset($item['tags']);
            } else {
                $tagsData = [];
            }
            if (!$field) {
                $field = new Field();
            }
            $field->setData($item);
            $field->save();
            if (count($tagsData)) {
                foreach ($tagsData as $tagName) {
                    Tag::addTagForField($field->id, $tagName);
                }
            }
            if ($dataChild && count($dataChild)) {
                $this->createRecursive($dataChild, $field->id);
            }
        }
    }
}
