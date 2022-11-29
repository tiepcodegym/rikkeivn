<?php

namespace Rikkei\News\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;

class BlogMeta extends CoreModel
{
    public $timestamps = false;
    protected $table = 'blog_meta';
    protected $fillable = ['post_id', 'key', 'value'];
    /**
     * up or down count type post
     *
     * @param type $postId
     * @param string $key : like, like_cmt, view, cmt
     * @param boolean $number : number up or down
     */
    public static function increCount($postId, $key = 'like', $number = 1)
    {
        if (!$key) {
            return null;
        }
        $item = self::select(['value'])
            ->where('post_id', $postId)
            ->where('key', $key)
            ->first();
        if ($item) {
            $value = $item->value;
        } else {
            $value = 0;
        }
        $value += $number;
        if ($value < 0) {
            $value = 0;
        }
        if (!$item) { //create
            self::create([
                'post_id' => $postId,
                'key' => $key,
                'value' => $value
            ]);
            return $value;
        }
        self::where('post_id', $postId)
            ->where('key', $key)
            ->update([
                'value' => $value
            ]);
        return $value;
    }

    /**
     * get all count of posts: like, like_cmt, view, cmt
     *
     * @param array $postIds
     * @return type
     */
    public static function getAllCount(array $postIds = [], $userId = null)
    {
        $collection = self::select(['post_id', 'key', 'value'])
            ->whereIn('post_id', $postIds)
            ->get();
        $result = [];
        foreach ($collection as $item) {
            $result[$item->key][$item->post_id] = [
                'count' => $item->value,
            ];
        }
        $userHasLike = LikeManage::hasLikeMultiPost($userId, $postIds);
        if (!count($userHasLike)) {
            return $result;
        }
        foreach ($userHasLike as $item) {
            $result['like'][$item->post_id]['like'] = 1;
        }
        return $result;
    }

    /**
     * recount meta of blog news
     */
    public static function reCount()
    {
        $dataInsert = [];
        $resultView = ViewManage::select(['post_id',
            DB::raw('count(*) as count')
        ])->groupBy('post_id')
            ->get();
        self::execDataReCount($resultView, 'view', $dataInsert);

        $resultCmt = PostComment::select(['post_id',
            DB::raw('count(*) as count')
        ])->groupBy('post_id')
            ->get();
        self::execDataReCount($resultCmt, 'cmt', $dataInsert);

        $resultLikePost = LikeManage::select(['post_id',
            DB::raw('count(*) as count')
        ])->where('type', LikeManage::TYPE_POST)
            ->groupBy('post_id')
            ->get();
        self::execDataReCount($resultLikePost, 'like', $dataInsert);

        $resultLikeCmt = LikeManage::select(['post_id',
            DB::raw('count(*) as count')
        ])->where('type', LikeManage::TYPE_COMMENT)
            ->groupBy('post_id')
            ->get();
        self::execDataReCount($resultLikeCmt, 'like_cmt', $dataInsert);
        DB::beginTransaction();
        try {
            // delete old data
            self::whereIn('key', ['like', 'like_cmt', 'view', 'cmt'])
                ->delete();
            self::insert($dataInsert);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * exec data recount blog meta
     *
     * @param type $collection
     * @param type $type
     * @return type
     */
    protected static function execDataReCount($collection, $type, &$dataInsert = [])
    {
        if (!count($collection)) {
            return [];
        }
        foreach ($collection as $item) {
            $dataInsert[] = [
                'post_id' => $item->post_id,
                'key' => $type,
                'value' => $item->count
            ];
        }
        return $dataInsert;
    }
}