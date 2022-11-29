<?php

namespace Rikkei\News\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\CacheHelper;
use Rikkei\News\View\ViewNews;
use Rikkei\Core\View\CacheBase;

class Category extends CoreModel
{
    use SoftDeletes;

    protected $table = 'blog_categories';

    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 2;

    const KEY_CACHE = 'cache_cate';

    const SLUG_YUME = 'yume';
    const SLUG_UNREAD_POST = 'unread-posts';
    const SLUG_VIDEO = 'multimedia';
    const SLUG_AUDIO = 'yume-radio';
    const SLUG_SUGGEST_POST = 'suggest-posts';
    const SLUG_HASHTAG = 'tags';
    const MENU_PARENT = 0;

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * get grid data
     *
     * @return object
     */
    public static function getGridData()
    {
        $pager = TeamConfig::getPagerData();
        $collection = self::select('id', 'title', 'slug', 'status',
            'sort_order')
            ->orderBy($pager['order'], $pager['dir']);
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
                $this->slug = ViewNews::generalSlug($this->slug);
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
            // auto created_by
            if (!$this->created_by) {
                $this->created_by = Permission::getInstance()->getEmployee()->id;
            }
            $result = parent::save($options);
            CacheHelper::forget(self::KEY_CACHE);
            CacheBase::forgetFile(CacheBase::HOME_PAGE, 'categories');
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * rewrite delete
     */
    public function delete()
    {
        $result = parent::delete();
        CacheBase::forgetFile(CacheBase::HOME_PAGE, 'categories');
        return $result;
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
     * get all category active
     *
     * @return array
     */
    public static function getAllActiveCategory()
    {
        if ($result = CacheHelper::get(self::KEY_CACHE)) {
            return $result;
        }
        $collection = self::select('id', 'title', 'slug', 'parent_id')
            ->where('status', self::STATUS_ENABLE)
            ->orderBy('sort_order', 'asc')
            ->orderBy('title', 'asc')
            ->get();
        if (count($collection)) {
            $result = $collection->toArray();
        } else {
            $result = [];
        }

//        array_push($result, ['id' => null, 'title' => 'YUME', 'slug' => self::SLUG_YUME]);
        CacheHelper::put(self::KEY_CACHE, $result);

        return $result;
    }

    /**
     * get category active follow slug
     *
     * @param string $slug
     * @return object
     */
    public static function findFollowSlug($slug)
    {
        return self::where('slug', $slug)
            ->where('status', self::STATUS_ENABLE)
            ->first();
    }

    /**
     * get all parent category active
     *
     * @return array
     */
    public static function getAllParentActiveCategory()
    {
        if ($result = CacheHelper::get(self::KEY_CACHE)) {
            return $result;
        }
        $collection = self::select('id', 'title')
            ->where('status', self::STATUS_ENABLE)
            ->where('parent_id', self::MENU_PARENT)
            ->pluck('title', 'id');
        if (count($collection)) {
            $result = $collection->toArray();
        } else {
            $result = [];
        }

//        array_push($result, ['id' => null, 'title' => 'YUME', 'slug' => self::SLUG_YUME]);
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }

    /**
     * get all category active
     *
     * @return array
     */
    public static function getAllActiveCategoryCollection()
    {
        if ($result = CacheHelper::get(self::KEY_CACHE)) {
            return $result;
        }
        $collection = self::select('id', 'title', 'slug', 'parent_id')
            ->where('status', self::STATUS_ENABLE)
            ->orderBy('sort_order', 'asc')
            ->orderBy('title', 'asc')
            ->with(['parent', 'children'])
            ->get();
        if (count($collection)) {
            $result = $collection;
        } else {
            $result = [];
        }

//        array_push($result, ['id' => null, 'title' => 'YUME', 'slug' => self::SLUG_YUME]);
        CacheHelper::put(self::KEY_CACHE, $result);

        return $result;
    }

    public static function getCategoriesApi($param)
    {
        $collection = self::select('id', 'title', 'slug', 'parent_id')
            ->whereNull('deleted_at');
        if (!empty($param['category_id'])) {
            $collection->where('id', '=', $param['category_id']);
        }
        if (!empty($param['slug'])) {
            $collection->where('slug', '=', $param['slug']);
        }
        if (!empty($param['updated_from'])) {
            $collection->where('updated_at', '>=', $param['updated_from']);
        }
        $collection->orderBy('sort_order', 'ASC');
        return $collection->get();
    }
}
