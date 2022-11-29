<?php

namespace Rikkei\News\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\News\Model\Post;
use Rikkei\News\Model\PostCategory;
use Exception;
use Log;
use DB;

class News10yearsSeed extends CoreSeeder
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
        DB::beginTransaction();
        try {
            $tag = '#Rikkei10';
            $data = Post::select('id as post_id')->where('tags', 'LIKE', '%'. $tag .',%')->orderBy('created_at', 'desc')->get();
            foreach ($data as $table){
                $table['cat_id'] = 18;
                $blogCate = PostCategory::insert([
                    'post_id' => $table->post_id,
                    'cat_id' => $table->cat_id,
                ]);
            };
            $this->insertSeedMigrate();
            DB::commit();
            } catch (Exception $ex) {
                DB::rollback();
                Log::info($ex->getMessage());
            }
    }
}
