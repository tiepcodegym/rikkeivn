<?php
namespace Rikkei\Tag\Seeds;

use DB;
use Rikkei\Tag\Model\Field;
use Rikkei\Tag\Model\Tag;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Tag\View\TagConst;

class TagDatabaseSeeder extends CoreSeeder
{
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
        $data = ['HiRDB', 'SymfowareDB'];
        $dataFramework = ['NuxtJS'];
        $dataLanguage = ['TypeScript', 'XML'];
        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                $tag = Tag::where('value', $item)->first();
                $field = Field::where('code', 'database')->first();
                if (!$tag && $field) {
                    Tag::create([
                        'field_id' => $field->id,
                        'value' => $item,
                    ]);
                }                
            }
            foreach ($dataFramework as $item) {
                $tag = Tag::where('value', $item)->first();
                $field = Field::where('code', 'framework')->first();
                if (!$tag && $field) {
                    Tag::create([
                        'field_id' => $field->id,
                        'value' => $item,
                    ]);
                }                
            }
            foreach ($dataLanguage as $itemLang) {
                $tag = Tag::where('value', $itemLang)->first();
                $field = Field::where('code', 'language')->first();
                if (!$tag && $field) {
                    Tag::create([
                        'field_id' => $field->id,
                        'value' => $itemLang,
                    ]);
                }
                if ($itemLang = 'TypeScript' && $tag) {
                    $tag->status = TagConst::FIELD_STATUS_ENABLE;
                    $tag->save();
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
