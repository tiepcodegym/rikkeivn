<?php

namespace Rikkei\News\Model;


use Illuminate\Support\Facades\Auth;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\News\Model\LikeManage;
use DB;
use Illuminate\Support\Facades\Cache;

class PostComment extends CoreModel
{
    use SoftDeletes;

    protected $table = 'blog_post_comments';
    protected $fillable = ['post_id', 'comment', 'edit_comment','user_id', 'status', 'parent_id', 'approved_by'];
    protected $dates = ['deleted_at'];


    const STATUS_COMMENT_ACTIVE = 1;
    const STATUS_COMMENT_NOT_ACTIVE = 0;
    const PARENT_COMMENT_ID = 0;
    const PAGE = 1;
    const NEW_PER_PAGE = 10;
    const MAX_WORD_DISPLAY = 200;
    const MAX_LINE_DISPLAY = 5;

    //key cache value auto_approve_comment
    const CACHE_AUTO_APPROVE_COMMENT = 'auto_approve_comment';

    /**
     * @param null|integer $postId
     * @return mixed
     */
    public static function getParentCommentByPost($postId = null)
    {
        $userInfo = Auth::user();
        $collection = self::join('employees', 'employees.id', '=', 'blog_post_comments.user_id')
            ->join('users', 'users.employee_id', '=', 'blog_post_comments.user_id')
            ->select(
                'blog_post_comments.*',
                'employees.name as employee_name',
                'users.avatar_url',
                'employees.id as employee_id',
                DB::raw('(SELECT count(post_id) FROM blog_like_manage where post_id = blog_post_comments.id and type = ' .LikeManage::TYPE_COMMENT. ') AS count_like_comment'),
                DB::raw('(SELECT count(post_id) FROM blog_like_manage where post_id = blog_post_comments.id and type = ' .LikeManage::TYPE_COMMENT. ' and employee_id = ' .$userInfo->employee_id. ') AS check_liked')
            )
            ->where([['post_id', $postId], ['parent_id', PostComment::PARENT_COMMENT_ID]])
            ->orderBy('created_at', 'DESC');
        if (!Permission::getInstance()->isAllow('news::post.approveComment')) {
            $collection->where('status', PostComment::STATUS_COMMENT_ACTIVE)->orWhere([
                ['user_id', $userInfo->employee_id],
                ['parent_id', PostComment::PARENT_COMMENT_ID],
                ['post_id', $postId],
            ]);
        }
        return $collection->paginate(PostComment::NEW_PER_PAGE);
    }

    public static function getAllCommentByPost($postId = null)
    {
        $userInfo = Auth::user();
        $collection = self::join('employees', 'employees.id', '=', 'blog_post_comments.user_id')
            ->join('users', 'users.employee_id', '=', 'blog_post_comments.user_id')
            ->select('blog_post_comments.*', 'employees.name as employee_name', 'users.avatar_url', 'employees.id as employee_id')
            ->where('post_id', $postId)
            ->orderBy('created_at', 'DESC');
        if (!Permission::getInstance()->isAllow('news::post.approveComment')) {
            $collection->where('status', PostComment::STATUS_COMMENT_ACTIVE)->orWhere([
                ['user_id', $userInfo->employee_id],
                ['post_id', $postId],
            ]);
        }
        return $collection->paginate(PostComment::NEW_PER_PAGE);
    }

    /**
     * @param integer|array $parentId
     * @param integer $perPage
     * @param integer $page
     * @return mixed
     */
    public static function getAllReplyComment($parentId, $perPage, $page, $userInfo)
    {
        $comments = self::select(
                'blog_post_comments.*',
                'employees.name as employee_name',
                'users.avatar_url',
                DB::raw('(SELECT count(post_id) FROM blog_like_manage where post_id = blog_post_comments.id and type = ' .LikeManage::TYPE_COMMENT. ') AS count_like_comment'),
                DB::raw('(SELECT count(post_id) FROM blog_like_manage where post_id = blog_post_comments.id and type = ' .LikeManage::TYPE_COMMENT. ' and employee_id = ' .$userInfo->employee_id. ') AS check_liked')
            );
        if (is_array ($parentId)) {
            $comments->whereIn('parent_id', $parentId);
        } else {
            $comments->where('parent_id', $parentId);
        }
        if (!Permission::getInstance()->isAllow('news::post.approveComment')) {
            $comments->where(function ($query) use ($userInfo) {
                $query->where('status', self::STATUS_COMMENT_ACTIVE)
                    ->orWhere('user_id', $userInfo->employee_id);
            });
        }

        $comments->join('employees', 'employees.id', '=', 'blog_post_comments.user_id')
            ->join('users', 'users.employee_id', '=', 'blog_post_comments.user_id')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->orderBy('created_at', 'DESC');
        return $comments->get();
    }

    /**
     * @param null|integer $parentId
     * @return mixed
     */
    public static function getCommentByParentComment($parentId = null)
    {
        $userInfo = Auth::user();
        $collection = self::join('employees', 'employees.id', '=', 'blog_post_comments.user_id')->join('users', 'users.employee_id', '=', 'blog_post_comments.user_id')
            ->where([['parent_id', PostComment::PARENT_COMMENT_ID], ['status', PostComment::STATUS_COMMENT_ACTIVE]])
            ->select('blog_post_comments.*', 'employees.name', 'users.avatar_url', 'employees.id as employee_id')
            ->orderBy('created_at', 'DESC');

        if (!Permission::getInstance()->isAllow('news::post.approveComment')) {
            $collection->orWhere([['parent_id', PostComment::PARENT_COMMENT_ID], ['status', PostComment::STATUS_COMMENT_NOT_ACTIVE], ['user_id', $userInfo->employee_id]]);
        } else {
            $collection->orWhere([['parent_id', $parentId], ['status', PostComment::STATUS_COMMENT_NOT_ACTIVE]]);
        }
        return $collection->paginate(PostComment::NEW_PER_PAGE);
    }

    /**
     * @param Request $request
     * @return static
     */
    public static function insert($request)
    {
        BlogMeta::increCount($request['post_id'], 'cmt', 1);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT . '_' . $request['post_id']);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT_REPLY . '_' . $request['post_id']);
        return self::create($request);
    }

    /**
     * @param integer $id
     * @param integer $status
     */
    public static function updateComment($id, $status)
    {
        $comment = self::find($id);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT . '_' . $comment->post_id);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT_REPLY . '_' . $comment->post_id);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COUNT_COMMENT_REPLY . '_' . $id);
        return $comment->update($status);
    }

    /**
     * @return mixed
     */
    public static function getAllComment()
    {
        $pager = TeamConfig::getPagerData(null, ['dir' => 'desc']);
        if ($pager['order'] == 'id') {
            $pager['order'] = 'blog_post_comments.id';
        }
        $collection = self::from('blog_post_comments')
            ->join('blog_posts', 'blog_posts.id', '=', 'blog_post_comments.post_id')
            ->join('employees', 'employees.id', '=', 'blog_post_comments.user_id')
            ->select(
                'blog_posts.id', 'blog_posts.title',
                'blog_post_comments.*',
                'employees.email'
            )->orderBy($pager['order'], $pager['dir']);
        self::filterGrid($collection);

        //filter status
        $collection = static::filterStatus($collection);

        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * Filter comment by status: approve or unapprove
     * @param PostComment $collection
     * @return PostComment collection
     */
    private static function filterStatus($collection)
    {
        $filterStatus = Form::getFilterData('except', 'blog_post_comments.status');
        if (!is_null($filterStatus)) {
            $collection->where(function ($query) use ($filterStatus) {
                if ($filterStatus == self::STATUS_COMMENT_ACTIVE) {
                    $query->where('blog_post_comments.status', self::STATUS_COMMENT_ACTIVE);
                    $query->whereNull('blog_post_comments.edit_comment');
                } else {
                    $query->where('blog_post_comments.status', self::STATUS_COMMENT_NOT_ACTIVE);
                    $query->orWhere(function ($queryChild) {
                        $queryChild->where('blog_post_comments.status', self::STATUS_COMMENT_ACTIVE)
                                   ->whereNotNull('blog_post_comments.edit_comment');
                    });
                }
            });
        }

        return $collection;
    }

    /**
     * @return array
     */
    public static function getAllStatus()
    {
        return [
            self::STATUS_COMMENT_ACTIVE => 'Approve',
            self::STATUS_COMMENT_NOT_ACTIVE => 'Unapprove',
        ];
    }

    /**
     * @param array $allStatus
     * @return mixed|null
     */
    public function getLabelStatus(array $allStatus = [])
    {
        if (!$allStatus) {
            $allStatus = self::getAllStatus();
        }
        if (isset($allStatus[$this->status])) {
            return $allStatus[$this->status];
        }
        return null;
    }

    /**
     * rewrite delete post cmt
     */
    public function delete()
    {
        $childs = self::where('parent_id', $this->id)->count();
        if ($childs) {
            self::where('parent_id', $this->id)->delete();
        }
        parent::delete();
        BlogMeta::increCount($this->post_id, 'cmt', -($childs + 1));
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT . '_' . $this->post_id);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT_REPLY . '_' . $this->post_id);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COUNT_COMMENT_REPLY . '_' . $this->id);
        return $childs + 1;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public static function deleteAllComment($data)
    {
        $commentsId = $data['data'];
        foreach ($commentsId as $id) {
            Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COUNT_COMMENT_REPLY . '_' . $id);
        }
        $postsId = self::whereIn('id', $commentsId)->get()->pluck('post_id')->toArray();
        foreach ($postsId as $postId) {
            Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT . '_' . $postId);
            Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COMMENT_REPLY . '_' . $postId);
        }
        return self::whereIn('id', $commentsId)->delete();
    }

    /**
     * @param integer $id
     * @return mixed
     */
    public static function getDetailComment($id)
    {
        return self::from('blog_post_comments')
            ->join('blog_posts', 'blog_posts.id', '=', 'blog_post_comments.post_id')
            ->join('employees', 'employees.id', '=', 'blog_post_comments.user_id')
            ->where('blog_post_comments.id', $id)
            ->select('blog_post_comments.*', 'blog_posts.title', 'employees.email')
            ->first();
    }

    /**
     * @param integer $parentId
     * @return mixed
     */
    public static function countAllCommentReplyById($parentId)
    {
        return self::where('parent_id', $parentId)->count();
    }

    /**
     * @return mixed
     */
    public static function getAllIdCommentNotApprove()
    {
        return PostComment::where([
                ['status', '=', 0],
            ])
            ->orWhere([
                ['status', '=', 1],
                ['edit_comment', '!=', null],
            ])
            ->pluck('id')->toArray();
    }

    public function getCommentAttribute($value)
    {
        $value = preg_replace('/@:([0-9]+)\(/', '', $value);
        $value = preg_replace('/\);/', '', $value);
        return $value;
    }
}
