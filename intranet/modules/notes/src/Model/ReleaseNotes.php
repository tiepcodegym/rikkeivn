<?php

namespace Rikkei\Notes\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\CookieCore;
use Cookie;

class ReleaseNotes extends Model {

    const STATUS_DISABLE = 0;
    const STATUS_ENABLE = 1;
    const TIMECACHE = 604800;
    const CACHE_LAST_VERSION = 'cache_last_version';

    protected $table = 'release_notes';
    public $timestamps = false;

    public static function getAllStatus() {
        return [
            self::STATUS_ENABLE => 'Enable',
            self::STATUS_DISABLE => 'Disable',
        ];
    }

    public static function getData() {
        $data = self::where('status', 1)
                ->orderBy('release_at', 'desc')
                ->paginate(5);
        return $data;
    }

    /**
     * get last version release notes
     *
     * @return \Rikkei\Core\View\type|null
     */
    public function getLastVersion()
    {
        if (CacheHelper::get(self::CACHE_LAST_VERSION)) {
            $version = CacheHelper::get(self::CACHE_LAST_VERSION);
        } else {
            $version = self::select('version')->orderBy('release_at', 'desc')->first();
            CacheHelper::put(self::CACHE_LAST_VERSION, $version);
        }
        return $version;
    }

}
