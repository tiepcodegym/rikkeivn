<?php

namespace Rikkei\News\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\News\Model\Poster;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\News\Model\Post;
use Illuminate\Support\Facades\Cache;

class ViewPoster
{
    protected static $instance;

    const LIMIT_DISPLAY_NEWS = 4;

    public function __construct()
    {
        $currentRouteName = Route::currentRouteName();
        if (!Permission::getInstance()->isAllow($currentRouteName)) {
            return View::viewErrorPermission();
        }
    }


    public function getFilterEmployee()
    {
        $filterEmployeeId = Form::getFilterData('number', 'employee_id');
        if ($filterEmployeeId) {
            $employee = Employee::findOrFail($filterEmployeeId);
            return $employee;
        }

        return null;
    }
    public static function convertKhongDau($str) {
		$str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
		$str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
		$str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
		$str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
		$str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
		$str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
		$str = preg_replace("/(đ)/", 'd', $str);
		$str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
		$str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
		$str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
		$str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
		$str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
		$str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
		$str = preg_replace("/(Đ)/", 'D', $str);
		$str = preg_replace("/(\“|\”|\‘|\’|\,|\!|\&|\;|\@|\#|\%|\~|\`|\=|\_|\'|\]|\[|\}|\{|\)|\(|\+|\^)/", '-', $str);

		$str = preg_replace("/( )/", '-', $str);
		return $str;
	}

    public function index()
    {
        $pager = Config::getPagerData(null, ['order' => 'order', 'dir' => 'desc']);
        $posterTbl = Poster::getTableName();
        $collections = Poster::whereNull("{$posterTbl}.deleted_at")->orderBy($pager['order'], $pager['dir'])->orderBy('start_at', 'desc');
        CoreModel::filterGrid($collections, [], null, 'LIKE');
        $filterTitle = Form::getFilterData('except','slug');
        if ($filterTitle) {
            $filterTileKhongDau = self::convertKhongDau($filterTitle);
            $filterTileKhongDau = str_replace('"', "", $filterTileKhongDau);
            $filterTileKhongDau = str_replace("'", "", $filterTileKhongDau);
            $filterTileKhongDau = implode('-', explode(' ', $filterTileKhongDau));
            $filterTileKhongDau = addslashes($filterTileKhongDau);
            $filterTitle = addslashes($filterTitle);
            $collections = $collections->where(function ($q) use ($filterTitle, $filterTileKhongDau) {
                $q->where('slug', 'like', "%{$filterTileKhongDau}%")
                    ->orWhere('title', 'like', "%{$filterTitle}%");
            });
        }

        CoreModel::pagerCollection($collections, $pager['limit'], $pager['page']);

        return $collections;
    }

    public function generateSlug(&$request, $posterId = null)
    {
        if (!$request['slug']) {
            $request['slug'] = Str::slug($request['title']);
        } else {
            $request['slug'] = Str::slug($request['slug']);
        }
        // render slug ultil not exits slug
        while(1) {
            $existsSlug = Poster::withTrashed()
                ->select(DB::raw('count(*) as count'))
                ->where('slug', $request['slug']);
            if ($posterId) {
                $existsSlug->where('id', '!=', $posterId);
            }
            $existsSlug = $existsSlug->first();
            if ($existsSlug && $existsSlug->count) {
                $request['slug'] = $request['slug'] . substr(md5(mt_rand() . time()), 0, 5);
            } else {
                break;
            }
        }
    }

    public function store($request)
    {
        $request = $request->all();
        $this->generateSlug($request);
        $model = Poster::create($request);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_POSTER);

        return $model;
    }

    public function edit($id)
    {
        $model = Poster::findOrFail($id);

        return $model;
    }

    public function update($id, $request)
    {
        $model = Poster::findOrFail($id);
        $request = $request->all();
        $this->generateSlug($request, $id);
        $model->update($request);
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_POSTER);

        return $model;
    }

    public function delete($id)
    {
        $model = Poster::findOrFail($id);
        $model->delete();
        Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_POSTER);

        return true;
    }

    public static function getPosterNews()
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        $items = Poster::whereDate('start_at', '<=', $currentDate)
            ->whereDate('end_at', '>=', $currentDate)
            ->where('status', Poster::STATUS_ACTIVE)
            ->orderBy('order', 'desc')
            ->limit(self::LIMIT_DISPLAY_NEWS)
            ->get();

        return $items;
    }
}
