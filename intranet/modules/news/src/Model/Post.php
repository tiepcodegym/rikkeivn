<?php

namespace Rikkei\News\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Rikkei\News\View\ViewPoster;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\Config as TeamConfig;
use Illuminate\Support\Facades\URL;
use Exception;
use Illuminate\Support\Facades\Config;
use Rikkei\Core\View\CoreImageHelper;
use Rikkei\Core\View\Form as FormCore;
use Rikkei\News\View\ViewNewPost;
use Illuminate\Support\Facades\Cache;

class Post extends CoreModel
{
    use SoftDeletes;

    private static $instance;

    const STATUS_DISABLE = 0;
    const STATUS_ENABLE = 1;
    const BE_COMMENTED = 1;
    const NO_COMMENT = 0;
    const BE_IMPORTANT = 1;
    const NO_IMPORTANT = 0;

    const IS_NOT_PUBLIC = 0;
    const IS_PUBLIC = 1;

    /**
     * Cache Expire Time use in Home Page
     */
    const CACHE_EXPIRE = 480;

    /**
     * is published
     */
    const STATUS_PUBLISHED = 1;
    const STATUS_NOT_PUBLISHED = 0;

    /**
     * is important
     */
    const STATUS_IMPORTANT = 1;
    const STATUS_NOT_IMPORTANT = 0;

    const CACHE_KEY = 'news';
    const CACHAE_SUFFIX_KEY = 'home';
    const CACHAE_SUFFIX_SLIDE = 'slide';
    const CACHAE_SUFFIX_VIDEO = 'video';
    const CACHAE_SUFFIX_TOP_MEMBER = 'top_member';
    const CACHAE_SUFFIX_TOP_POST = 'top_post';
    const CACHAE_SUFFIX_POSTER = 'poster';
    const CACHAE_SUFFIX_MAGAZINE = 'magazine';
    const CACHAE_SUFFIX_SLUG = 'slug';
    const CACHAE_SUFFIX_FIRST_CATE_POST = 'first_cate_post';
    const CACHAE_SUFFIX_RELATE_POST = 'relate_post';
    const CACHAE_SUFFIX_TOP_HASH_TAG = 'top_hash_tag';
    const CACHAE_SUFFIX_COMMENT = 'comment';
    const CACHAE_SUFFIX_COMMENT_REPLY = 'comment_reply';
    const CACHAE_SUFFIX_COUNT_COMMENT_REPLY = 'count_comment_reply';
    
    const TYPE_AUDIO = 2;
    const TYPE_VIDEO = 1;
    const TYPE_OTHER = 0;

    const IS_VIDEO_TRUE = true;
    const IS_VIDEO_FALSE = false;

    const SET_TOP_POST = 1;
    const NOT_SET_TOP_POST = 0;

    protected $table = 'blog_posts';

    const LIMIT_POST = 10;
    const SLUG_CATEGORY_TRAINING = 'dao-tao';

    /**
     * get instance
     * @return object
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * get grid data
     *
     * @return object
     */
    public static function getGridData()
    {
        $pager = TeamConfig::getPagerData();
        $collection = self::select('id', 'title', 'slug', 'status', 'image', 'is_set_comment', 'is_video', 'youtube_link', 'youtube_id');
        if (FormCore::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('public_at', 'desc')
                ->orderBy('created_at', 'desc');
        }

        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get all status
     *
     * @return array
     */
    public static function getAllStatus()
    {
        return [
            self::STATUS_ENABLE => 'Enable',
            self::STATUS_DISABLE => 'Disable',
        ];
    }

    /**
     *
     * @return array
     */
    public static function getAllPublic()
    {
        return [
            self::IS_NOT_PUBLIC => 'Không công khai',
            self::IS_PUBLIC => 'Công khai'
        ];
    }

    public static function getAllTypePost()
    {
        return [
            self::TYPE_AUDIO => 'Audio',
            self::TYPE_VIDEO => 'Video',
            self::TYPE_OTHER => 'Post',
        ];
    }

    /**
     * get status of item
     *
     * @param int $status
     * @param array $allStatus
     * @return string
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
     * get data post by $id
     *
     * @return string
     */
    public static function getGridDataPost($id)
    {
        $postTable = self::getTableName();
        $postAttachTable = PostAttach::getTableName();
        $postPostSchedule = PostSchedule::getTableName();
        return self::select("{$postTable}.*", "{$postAttachTable}.path", "{$postAttachTable}.id as attach_id", "{$postAttachTable}.deleted_at as deleted_attach", "{$postPostSchedule}.publish_at")
            ->leftJoin("{$postAttachTable}", "{$postAttachTable}.post_id",'=',"{$postTable}.id")
            ->leftJoin("{$postPostSchedule}","{$postPostSchedule}.post_id", '=',"{$postTable}.id")
            ->where("{$postTable}.id", $id)
            ->whereNull("{$postTable}.deleted_at")
            ->orderBy("{$postAttachTable}.id", 'DESC')
            ->first();
    }

    /**
     * get all data post
     *
     * @return string
     */
    public static function getAllDataPost($isAll = null, $limit = 10)
    {
        $postTable = self::getTableName();
        $postAttachTable = PostAttach::getTableName();
        $collection = self::select("{$postTable}.*", "{$postAttachTable}.path", "{$postAttachTable}.id as attach_id", "{$postAttachTable}.deleted_at as deleted_attach")
            ->leftJoin("{$postAttachTable}", "{$postAttachTable}.post_id",'=',"{$postTable}.id")
            ->whereNull("{$postAttachTable}.deleted_at")
            ->whereNull("{$postTable}.deleted_at")
            ->where($postTable.'.status', self::STATUS_ENABLE)
            ->where($postTable.'.is_video', '=', self::TYPE_AUDIO)
            ->orderBy($postTable.'.public_at', 'desc')
            ->groupBy("{$postTable}.id");

        if ($isAll) {
            $pager = TeamConfig::getPagerDataQuery();
            $pager['limit'] = 10;

            self::pagerCollection($collection, $pager['limit'], $pager['page']);

            return $collection;
        }

        return $collection->limit($limit)->get();
    }

    /**
     * rewrite save model
     *
     * @param array $options
     */
    public function save(array $options = [])
    {
        DB::beginTransaction();
        try {
            // auto render slug
            if (!$this->slug) {
                $this->slug = Str::slug($this->title);
            } else {
                $this->slug = Str::slug($this->slug);
            }
            // render slug ultil not exits slug
            while(1) {
                $existsSlug = self::withTrashed()
                    ->select(DB::raw('count(*) as count'))
                    ->where('slug', $this->slug);
                if ($this->id) {
                    $existsSlug->where('id', '!=', $this->id);
                }
                $existsSlug = $existsSlug->first();
                if ($existsSlug && $existsSlug->count) {
                    $this->slug = $this->slug . substr(md5(mt_rand() . time()), 0, 5);
                } else {
                    break;
                }
            }
            // auto render puiblic at
            if (!$this->public_at && $this->status == self::STATUS_ENABLE) {
                $this->public_at = Carbon::now()->format('Y-m-d H:i:s');
            } else if ($this->status != self::STATUS_ENABLE) {
                $this->public_at = null;
            }
            // auto created_by
            if (!$this->created_by) {
                $this->created_by = Permission::getInstance()->getEmployee()->id;
            }
            $result = parent::save($options);
            $this->clearCache();
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * override delete method
     */
    public function delete()
    {
        $result = parent::delete();
        $this->clearCache();
        return $result;
    }

    /**
     * Clear all cache
     *
     * @return void
     */
    public function clearCache()
    {
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_KEY);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_SLIDE);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_VIDEO);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_TOP_POST);
    }

    /**
     * get url asset of post
     *
     * @return string
     */
    public function getImage($noImage = null)
    {
        if ($noImage) {
            $noImage = URL::asset('common/images/noimage.png');
        }
        if (!$this->image) {
            return $noImage;
        }
        if (!file_exists(public_path($this->image))) {
            return $noImage;
        }
        return URL::asset($this->image);
    }

    /**
     * get thubnail image
     *
     * @param string $noImage
     */
    public function getThumbnail($noImage = true)
    {
        $image = CoreImageHelper::getInstance()
            ->setImage($this->image)
            ->resize(Config::get('image.news.size_thumbnail_width'),
                Config::get('image.news.size_thumbnail_height'));

        if (!$image && $noImage) {
            return URL::asset('common/images/noimage.png');
        }
        return $image;
    }

    /**
     * get detail image
     *
     * @return string
     */
    public function getDetailImage()
    {
        return CoreImageHelper::getInstance()
            ->setImage($this->image)
            ->resize(Config::get('image.news.size_detail_width'),
                Config::get('image.news.size_detail_height'));
    }

    /**
     * get url of post
     *
     * @return string
     */
    public function getUrl()
    {
        return URL::route('news::post.view', ['slug' => $this->slug]);
    }

    /**
     * get url of post by web 10years
     *
     * @return string
     */
    public function getUrlByWeb10years()
    {
        return config('services.rikkei_10years_1_news_url').$this->id;
    }

    /**
     * get public date of post
     *
     * @return string
     */
    public function getPublicDate()
    {
        if (!$this->public_at) {
            return null;
        }
        $date = Carbon::parse($this->public_at);
        return $date->format('d/m/Y');
    }

    /**
     * get list post of home page
     *
     * @return object
     */
    public static function getPosts(array $option = [])
    {
        $pager = TeamConfig::getPagerDataQuery();
//        $pager['limit'] = 15;
        if (isset($option['cate_slug']) && $option['cate_slug']) {
            $pager['limit'] = 10;
        } else {
            $pager['limit'] = self::LIMIT_POST;
        }

        $tablePost = self::getTableName();
        $tableCate = Category::getTableName();
        $tablePostCate = PostCategory::getTableName();
        $collection = self::select([$tablePost.'.id', $tablePost.'.title',
            $tablePost.'.slug', $tablePost.'.image', $tablePost.'.short_desc',
            $tablePost.'.public_at', $tablePost.'.author', $tablePost.'.created_at',
            $tablePost.'.is_video', $tablePost.'.youtube_id', $tablePost.'.youtube_link',
            $tablePost.'.is_set_comment'])
            ->where($tablePost.'.status', self::STATUS_ENABLE)
//            ->where($tablePost.'.is_video', '!=' ,self::IS_VIDEO_TRUE)
            ->groupBy($tablePost . '.id')
            ->orderBy($tablePost.'.public_at', 'desc');
        if (isset($option['cate_slug']) && $option['cate_slug']) {
            $collection->join($tablePostCate, $tablePostCate.'.post_id', '=', $tablePost.'.id')
                ->join($tableCate, $tablePostCate.'.cat_id', '=', $tableCate.'.id')
                ->where($tableCate.'.slug', $option['cate_slug']);
        }
        if (isset($option['search']) && $option['search']) {
            $filterTitle = $option['search'];
            $filterKhongDau = ViewPoster::convertKhongDau($filterTitle);
            $filterKhongDau = str_replace('"', "", $filterKhongDau);
            $filterKhongDau = str_replace("'", "", $filterKhongDau);
            $filterKhongDau = implode('-', explode(' ', $filterKhongDau));
            $filterKhongDau = addslashes($filterKhongDau);
            $filterTitle = addslashes($filterTitle);
            $collection = $collection->where(function ($q) use ($tablePost, $filterTitle, $filterKhongDau) {
                $q->where($tablePost . '.title', 'like', '%' . $filterTitle . '%')
                    ->orWhere($tablePost.'.tags', 'LIKE', '%'. $filterTitle .',%')
                    ->orWhere($tablePost . '.slug', 'like', '%' . $filterKhongDau . '%');
            });
        }

        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * get category active follow slug
     *
     * @param string $slug
     * @return object
     */
    public static function findFollowSlug($slug)
    {
        return self::select(['id', 'title', 'slug', 'status', 'image', 'short_desc', 'is_video', 'youtube_link', 'youtube_id',
            'desc', 'public_at', 'author', 'render', 'is_set_comment', 'published', 'tags'])
            ->where('slug', $slug)
            ->where('status', self::STATUS_ENABLE)
            ->first();
    }

    /**
     * get first category of post
     *
     * @return object
     */
    public function getFirstCategory()
    {
        $tableCate = Category::getTableName();
        $tablePostCate = PostCategory::getTableName();

        return Category::select($tableCate.'.id', $tableCate.'.title', $tableCate.'.slug', $tableCate.'.parent_id')
            ->join($tablePostCate, $tablePostCate.'.cat_id', '=', $tableCate.'.id')
            ->where($tablePostCate.'.post_id', $this->id)
            ->where($tableCate.'.status', Category::STATUS_ENABLE)
            ->orderBy($tableCate.'.sort_order', 'asc')
            ->orderBy($tableCate.'.title', 'asc')
            ->first();
    }

    /**
     * get post relate
     *
     * @param object $category
     * @param int $limit
     * @return collection
     */
    public function getRelatePost($category = null, $limit = 5)
    {
        if ($this->is_video) {
            return self::getPostVideos(false, 6);
        }
        $tablePost = self::getTableName();

        $collection = self::select($tablePost.'.id', $tablePost.'.title', $tablePost.'.slug', $tablePost.'.image', $tablePost.'.author', $tablePost.'.public_at');
        if ($category) {
            $tableCate = Category::getTableName();
            $tablePostCate = PostCategory::getTableName();
            $collection->join($tablePostCate, $tablePostCate.'.post_id', '=', $tablePost.'.id')
                ->join($tableCate, $tablePostCate.'.cat_id', '=', $tableCate.'.id')
                ->where($tableCate.'.id', $category->id)
                ->groupBy($tablePost.'.id');
        }
        $collection->where($tablePost.'.id', '!=', $this->id)
            ->where($tablePost.'.status', self::STATUS_ENABLE)
            ->where($tablePost.'.is_video', '!=' ,self::IS_VIDEO_TRUE)
            ->orderBy($tablePost.'.public_at', 'desc')
            ->take($limit);

        return $collection->get();
    }

    /**
     * get list post ajax
     *
     * @param array $option
     */
    public static function searchAjax(array $option = [])
    {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 10,
            'q' => ''
        ];
        $option = array_merge($arrayDefault, $option);
        $collection = self::select('id', 'title', 'image')
            ->where('status', self::STATUS_ENABLE)
            ->where('title', 'LIKE', '%' . $option['q'] . '%')
            ->orderBy('public_at', 'desc');
        self::pagerCollection($collection, $option['limit'], $option['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => e($item->title),
                'image' => $item->getImage(true),
            ];
        }
        return $result;
    }

    /**
     * get posts follow ids
     *
     * @param array $ids
     * @return collection
     */
    public static function getPostFollowIds(array $ids)
    {
        return self::select('id', 'title', 'image', 'slug', 'short_desc')
            ->where('status', self::STATUS_ENABLE)
            ->whereIn('id', $ids)
            ->orderBy('public_at', 'desc')
            ->get();
    }

    /**
     * get total View of post
     **/
    public function getToltalView()
    {
        return ViewManage::getTotalView($this->id);
    }

    /**
    * get total Like of post
    **/
    public function getTotalLike()
    {
        return LikeManage::getTotalLike($this->id);
    }

    /**
    * check like of post
    **/
    public function checkLike()
    {
        $countlike = LikeManage::where('employee_id','=',Permission::getInstance()->getEmployee()->id)->where('post_id','=',$this->id)->count();
        if($countlike > 0){
            return true;
        }
        return false;
    }

    /**
    * get post follow render
    **/
    public static function getPostFollowRender($render){
         return self::where('render', $render)
                    ->where('status', self::STATUS_ENABLE)
                    ->first();
    }

    /**
    * get list like of post
    **/
    public function getListLike(){
       return LikeManage::getListLike($this->id);
    }
    /**
     * get post find id
     **/
    public static function checkIsSetComment($id)
    {
        return self::find($id);
    }
    /**
     * get posts diff Id
     **/
    public static function getNotReadPost($userInfo, $option)
    {
        $data = [];
        $tablePost = self::getTableName();
        $countOldView = ViewManage::select(['post_id'])
            ->where('employee_id', '=', $userInfo->employee_id)
            ->get();
        foreach ($countOldView as $value) {
            $data[] = $value->post_id;
        }
        $collection = self::select([$tablePost.'.id', $tablePost.'.title',
            $tablePost.'.slug', $tablePost.'.image', $tablePost.'.short_desc',
            $tablePost.'.public_at', $tablePost.'.author', $tablePost.'.created_at',
            $tablePost.'.is_set_comment'])
            ->where($tablePost.'.status', self::STATUS_ENABLE)
            ->where($tablePost.'.is_video', '!=' ,self::IS_VIDEO_TRUE)
            ->whereNotIn($tablePost . '.id', $data)
            ->groupBy($tablePost . '.id')
            ->orderBy($tablePost.'.public_at', 'desc');

        if (isset($option['cate_slug']) && $option['cate_slug']) {
            if ($option['cate_slug'] == Category::SLUG_UNREAD_POST) {
                $pager = TeamConfig::getPagerDataQuery();
                $pager['limit'] = 10;
                self::pagerCollection($collection, $pager['limit'], $pager['page']);

                return $collection;
            }
        }


        return $collection->take(6)->get();

    }
    /**
     * get posts important
     **/
    public static function getPostsSlide()
    {
        $tablePost = self::getTableName();
        $collection = self::select([$tablePost.'.id', $tablePost.'.title',
            $tablePost.'.slug', $tablePost.'.image', $tablePost.'.short_desc',
            $tablePost.'.public_at', $tablePost.'.author', $tablePost.'.created_at',
            $tablePost.'.is_set_comment'])
            ->where($tablePost.'.status', self::STATUS_ENABLE)
            ->where($tablePost.'.is_video', '!=' ,self::IS_VIDEO_TRUE)
            ->where($tablePost.'.important', self::STATUS_IMPORTANT)
            ->groupBy($tablePost . '.id')
            ->orderBy($tablePost.'.public_at', 'desc')
            ->get();

        return $collection;
    }

    public static function getPostVideos($isAll = false, $limit = 10)
    {
        $tablePost = self::getTableName();
        $collection = self::select([$tablePost.'.id', $tablePost.'.title',
            $tablePost.'.slug', $tablePost.'.image', $tablePost.'.short_desc',
            $tablePost.'.youtube_id', $tablePost.'.youtube_link', $tablePost.'.is_video',
            $tablePost.'.public_at', $tablePost.'.author', $tablePost.'.created_at',
            $tablePost.'.is_set_comment'])
            ->where($tablePost.'.status', self::STATUS_ENABLE)
            ->where($tablePost.'.is_video', '=' ,self::IS_VIDEO_TRUE)
            ->orderBy($tablePost.'.public_at', 'desc');
        if ($isAll) {
            $pager = TeamConfig::getPagerDataQuery();
            $pager['limit'] = 10;

            self::pagerCollection($collection, $pager['limit'], $pager['page']);

            return $collection;
        }

        return $collection->limit($limit)->get();
    }

    public static function getTopHashTag()
    {
        $dayInLast30days = Carbon::now()->subDays(30)->format('Y-m-d');
        $tablePost = self::getTableName();
        $topTags = [];
        $tags = self::where($tablePost . '.status', self::STATUS_ENABLE)
            ->whereNotNull('tags')
            ->whereDate($tablePost . '.public_at', '>=', $dayInLast30days)
            ->pluck('tags')
            ->toArray();
        foreach ($tags as $stringTag) {
            $arrayTags = explode(',', $stringTag);
            foreach ($arrayTags as $tag) {
                if ($tag == '') continue;
                if (isset($topTags[$tag])) {
                    $topTags[$tag]++;
                } else {
                    $topTags[$tag] = 1;
                }
            }
        }
        $arrayTags = self::shuffle_assoc($topTags);

        return array_slice($arrayTags , 0, 5);
    }

    public static function shuffle_assoc($array) {
        $keys = array_keys($array);
        shuffle($keys);
        $new = [];
        foreach($keys as $key) {
            $new[$key] = $array[$key];
        }

        $array = $new;
        return $array;
    }

    public static function getTopPost()
    {
        $dayInLast30days = Carbon::now()->subDays(30)->format('Y-m-d');
        $tablePost = self::getTableName();
        $tableBlogMeta = BlogMeta::getTableName();

        $collection = self::select([
                $tablePost.'.id', $tablePost.'.title',
                $tablePost.'.slug', $tablePost.'.image', $tablePost.'.short_desc',
                $tablePost.'.public_at', $tablePost.'.author', $tablePost.'.created_at',
                $tablePost.'.is_set_comment',
                DB::raw("SUM(${tableBlogMeta}.value) as value")
            ])
            ->leftJoin($tableBlogMeta, $tableBlogMeta.'.post_id', '=', $tablePost.'.id')
            ->where($tablePost . '.status', self::STATUS_ENABLE)
            ->where($tablePost.'.is_video', '!=', self::IS_VIDEO_TRUE)
            ->whereDate($tablePost . '.public_at', '>=', $dayInLast30days)
            ->orWhere(function ($query) use ($tablePost) {
                return $query->where($tablePost . '.set_top', self::SET_TOP_POST);
            })
            ->groupBy($tablePost . '.id')
            ->orderBy('set_top', 'desc')
            ->orderBy('value', 'desc')
            ->orderBy('public_at', 'desc')
            ->limit(5)
            ->get();

        return $collection;
    }

    public static function getNewPost(array $option = [])
    {
        $tablePost = self::getTableName();
        $tableCate = Category::getTableName();
        $tablePostCate = PostCategory::getTableName();
        $collection = self::select([$tablePost.'.id', $tablePost.'.title',
            $tablePost.'.slug', $tablePost.'.image', $tablePost.'.short_desc',

            $tablePost.'.public_at', $tablePost.'.author', $tablePost.'.created_at',
            $tablePost.'.is_set_comment', $tablePost.'.tags'])
            ->where($tablePost.'.status', self::STATUS_ENABLE)
            ->groupBy($tablePost . '.id')
            ->orderBy($tablePost.'.public_at', 'desc');
        if (isset($option['cate_slug']) && $option['cate_slug']) {
            $collection->join($tablePostCate, $tablePostCate.'.post_id', '=', $tablePost.'.id')
                ->join($tableCate, $tablePostCate.'.cat_id', '=', $tableCate.'.id')
                ->where($tableCate.'.slug', $option['cate_slug']);
        }

        return $collection->first();
    }
    /**
     * get top member
     **/
    public static function getTopMembers()
    {
        return ViewNewPost::getTopMember();
    }

    /**
     * get top post most comment
     **/
    public static function getTopPostComment()
    {
        return ViewNewPost::getTopPostMostComment();
    }

    /**
     * get top post most comment
     **/
    public static function getPostSlide()
    {
        return ViewNewPost::getPostSlide();
    }

    /**
     * get post relate tags
     *
     * @param object $category
     * @param int $limit
     * @return collection
     */
    public function getRelatePostTags($id, $limit = 5, $tags = null)
    {
        $tablePost = self::getTableName();
        $collection = self::select(
            $tablePost.'.id', $tablePost.'.title', $tablePost.'.slug', $tablePost.'.short_desc',
            $tablePost.'.is_video', $tablePost.'.youtube_id', $tablePost.'.youtube_link',
            $tablePost.'.image', $tablePost.'.author', $tablePost.'.public_at');
        if ($tags) {
            $collection->where($tablePost.'.tags', 'LIKE', '%#'. $tags[0] .',%');
            foreach ($tags as $key => $value) {
                if ($key > 0) {
                    $collection->orWhere($tablePost.'.tags', 'LIKE', '%#'. $tags[$key] .',%');
                }
            }
        }
        $collection->where($tablePost.'.id', '!=', $id)
            ->where($tablePost.'.status', self::STATUS_ENABLE)
            ->orderBy($tablePost.'.public_at', 'desc')
            ->take($limit);

        return $collection->get();
    }

    public static function getPostsByTag($tag)
    {
        $pager = TeamConfig::getPagerDataQuery();
        $pager['limit'] = 10;

        $tablePost = self::getTableName();
        $collection = self::select(
            $tablePost.'.id', $tablePost.'.title', $tablePost.'.slug',
            $tablePost.'.is_video', $tablePost.'.youtube_id', $tablePost.'.youtube_link',
            $tablePost.'.short_desc', $tablePost.'.image', $tablePost.'.author', $tablePost.'.public_at');
        $collection->where($tablePost.'.tags', 'LIKE', '%'. $tag .',%')
                        ->where($tablePost.'.status', self::STATUS_ENABLE)
                        ->orderBy($tablePost.'.public_at', 'desc');

        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * get filter error message
     * @param $ex
     * @param int $limitLen
     * @return string
     */
    public function errorMessage($ex, $limitLen = 100)
    {
        $message = $ex->getMessage();
        if ($ex->getCode() == 422) { //type custom
            return $ex->getMessage();
        }
        if (strlen($message) <= $limitLen) {
            return $message;
        }
        return substr($message, 0, $limitLen) . '...';
    }

    /**
     * get grid data
     *
     * @return object
     */
    public static function getGridDataHighlight()
    {
        $pager = TeamConfig::getPagerData();
        $collection = self::select('id', 'title', 'slug', 'status', 'image', 'is_set_comment', 'is_video', 'youtube_link', 'youtube_id');
        if (FormCore::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('public_at', 'desc')
                ->orderBy('created_at', 'desc');
        }

        self::filterGrid($collection);
        $collection = $collection->orWhere('important', 1)->orWhere('set_top', 1);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * API get data request approved from intranet
     */
    public function getDataPosts()
    {
        $collection = self::select(
            'blog_posts.id',
            'blog_posts.is_video',
            'blog_posts.title',
            'blog_posts.slug',
            'blog_posts.short_desc',
            'blog_posts.is_public',
            'blog_posts.status',
            'blog_posts.image',
            'blog_posts.desc',
            'blog_posts.youtube_link',
            'blog_posts.youtube_id',
            'blog_posts.public_at as date',
            'blog_posts.tags',
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(blog_post_cats.cat_id)) AS category")
        )
            ->leftJoin('blog_post_cats', 'blog_posts.id', '=', 'blog_post_cats.post_id')
            ->where('blog_posts.published', Post::STATUS_PUBLISHED)
            ->where('blog_posts.deleted_at', NULL)
            ->groupBy('blog_posts.id');

        return $collection->get();
    }

    public function formatDataPosts($dataPost)
    {
        $categoryTraining = Category::findFollowSlug(self::SLUG_CATEGORY_TRAINING);

        $result = array();
        foreach ($dataPost as $key => $post) {
            $result[$key] = $post;
            if (!empty($post['category']) && $categoryTraining && in_array($categoryTraining->id, explode(',', $post['category']))) {
                $result[$key]['category'] = $categoryTraining->id;
            } else {
                unset($result[$key]['category']);
            }
            $result[$key]['date'] = !empty($result['date']) ? $result['date'] : date('Y-m-d H:i:s');
            $result[$key]['public_at'] = $result[$key]['date'];
            $result[$key]['short_desc'] = !empty($post['short_desc']) ? $post['short_desc'] : str_limit(strip_tags($post['desc']), 300);
            $result[$key]['tags'] = array_diff(explode(',', str_replace('#', '', $post['tags'])), array(''));
        }

        return $result;
    }

    /**
     * get grid data featured article
     *
     * @return object
     */
    public static function getGridDataFeaturedArticle()
    {
        $pager = TeamConfig::getPagerData();
        $collection = self::select('id', 'title', 'slug', 'status', 'image', 'is_set_comment', 'is_video', 'youtube_link', 'youtube_id', 'important', 'set_top');

        if (FormCore::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('public_at', 'desc')
                ->orderBy('created_at', 'desc');
        }

        self::filterGrid($collection);
        $collection->where(function ($query) {
            return $query->where('important', true)->orWhere('set_top', true);
        });
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
}
