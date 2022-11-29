<?php

namespace Rikkei\News\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;

class PostCategory extends CoreModel
{
    protected $table = 'blog_post_cats';

    /**
     * get categories of a post
     *
     * @param int $postId
     * @return object
     */
    public static function getCategoriesOfPost($postId)
    {
        $tablePostCat = self::getTableName();
        $tablePost = Post::getTableName();
        $tableCategory = Category::getTableName();

        return self::select($tableCategory.'.id', $tableCategory.'.title', $tableCategory.'.slug')
            ->join($tablePost, $tablePost.'.id', '=', $tablePostCat.'.post_id')
            ->join($tableCategory, $tableCategory.'.id', '=', $tablePostCat.'.cat_id')
            ->where($tablePostCat.'.post_id', $postId)
            ->orderBy($tableCategory.'.title')
            ->get();
    }

    /**
     * get ids of post
     *
     * @param int $postId
     * @return array
     */
    public static function getIdsCategoryOfPost($postId)
    {
        $tablePostCat = self::getTableName();
        $tablePost = Post::getTableName();

        $collection = self::select($tablePostCat.'.cat_id')
            ->join($tablePost, $tablePost.'.id', '=', $tablePostCat.'.post_id')
            ->where($tablePostCat.'.post_id', $postId)
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $result[] = $item->cat_id;
        }
        return $result;
    }

    public static function savePostCategory($postId, array $categoryIds)
    {
        //find category avai
        $categoryAvai = Category::select('id')
            ->whereIn('id',$categoryIds)
            ->get();
        $categoryAvaiPost = [];
        if (count($categoryAvai)) {
            foreach ($categoryAvai as $item) {
                $categoryAvaiPost[] = [
                    'cat_id' => $item->id,
                    'post_id' => $postId
                ];
            }
        }
        DB::beginTransaction();
        try {
            //delete old category
            self::where('post_id', $postId)->delete();
            if (count($categoryAvaiPost)) {
                self::insert($categoryAvaiPost);
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
