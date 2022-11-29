<?php

namespace Rikkei\News\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;


class LikeManage extends CoreModel
{
    const LIKE = 1;
    const TYPE_POST = 0; // like post
    const TYPE_COMMENT = 1; // like cmt
    
    protected $table = 'blog_like_manage';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['employee_id', 'post_id', 'like', 'type'];

    /**
     * like|dislike post or comment
     *
     * @param int $id post or comment id
     * @param int $type type post or comment
     */
    public static function like($id, $type = null)
    {
        $result = self::getLikeTypeKey($type);
        $curUser = Permission::getInstance()->getEmployee();
        $oldLike = self::where('employee_id', $curUser->id)
                ->where('post_id', $id)
                ->where('type', $result['type']);
        if ($oldLike->count()) { // dislike
            $oldLike->delete();
            return [
                'count' => BlogMeta::increCount($id, $result['key'], -1),
                'like' => 0,
            ];
        }
        // like
        self::create([
            'employee_id' => $curUser->id,
            'post_id' => $id,
            'type' => $result['type']
        ]);
        return [
            'count' => BlogMeta::increCount($id, $result['key'], 1),
            'like' => 1,
        ];
    }

    /**
     * get total like
     * @param int $id post or comment id
     * @param int $type type post or comment
     */
    public static function getTotalLike($id, $type = self::TYPE_POST)
    {
        return self::where('post_id', $id)->where('type', $type)->count();
    }

    /**
     * get list like of post
     *
     * @param int $id post or comment id
     * @param int $type type post or comment
     */
    public static function getlistLike($id, $type = null)
    {
        $result = self::getLikeTypeKey($type);
        return self::select(['users.name', 'users.avatar_url'])
            ->leftJoin('users', 'blog_like_manage.employee_id', '=', 'users.employee_id')
            ->where('blog_like_manage.post_id', $id)
            ->where('blog_like_manage.type', $result['type'])
            ->get();
    }

    /**
     * check user has like
     *
     * @param int $userId
     * @param int $postId
     * @param int $type
     * @return boolean
     */
    public static function hasLike($userId, $postId, $type = null)
    {
        if (!$type) {
            $type = self::TYPE_POST;
        }
        return self::select(['post_id'])
            ->where('employee_id', $userId)
            ->where('post_id', $postId)
            ->where('type', $type)
            ->first() ? true : false;
    }

    /**
     * check user has like
     *
     * @param int $userId
     * @param int $postsId
     * @param int $type
     * @return boolean
     */
    public static function hasLikeMultiPost($userId, $postsId, $type = null)
    {
        if (!$type) {
            $type = self::TYPE_POST;
        }
        return self::select(['post_id'])
            ->where('employee_id', $userId)
            ->whereIn('post_id', $postsId)
            ->where('type', $type)
            ->get();
    }

    /**
     * get like type and key
     *
     * @param int $type
     * @return array
     */
    public static function getLikeTypeKey($type = null)
    {
        if (!$type) {
            $type = self::TYPE_POST;
        }
        if ($type == self::TYPE_POST) {
            $key = 'like';
        } elseif ($type == self::TYPE_COMMENT) {
            $key = 'like_cmt';
        } else {
            $key = null;
        }
        return [
            'type' => $type,
            'key' => $key,
        ];
    }
}