<?php

namespace Rikkei\Core\View;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Rikkei\Team\Model\Employee;

class CacheBase
{
    /*
     * time store cache server
     */
    const TIME_CACHE = 604800;
    const PATH_CACHE = 'framework/cache/';
    const ACCESS_FILE = 'public';
    const ACCESS_FOLDER = 0777;
    const DISK = 'base';

    /**
     *key cache: folder => have / suffix
     */
    const EMPL_PERMIS = 'emp_permis/';
    const GENERAL = 'general/';
    const MENU_USER = 'menu_u/';

    const HOME_PAGE = 'home/';

    /**
     * put cache
     *
     * @param string $key
     * @param type $value
     * @return type
     */
    public static function put($key, $value, $key2 = '')
    {
        return Cache::put($key . '-' . $key2, $value, self::TIME_CACHE);
    }

    /**
     * get cache
     *
     * @param string $key
     */
    public static function get($key, $key2 = '')
    {
        return Cache::get($key . '-' . $key2);
    }

    /**
     * remove cache
     *
     * @param string $key
     * @return type
     */
    public static function forget($key)
    {
//        try {
//            Employee::changeCacheVersion();
//        }
//        catch (Exception $ex) {
//            Log::error($ex->getMessage());
//        }
        return Cache::forget($key);
    }

    /**
     * flush all cache
     */
    public static function flush()
    {
//        try {
//            Employee::changeCacheVersion();
//        }
//        catch (Exception $ex) {
//            Log::error($ex->getMessage());
//        }
        Artisan::call('cache:clear');
    }

    /**
     * put cache into file storate
     *
     * @param string $key
     * @param type $value
     * @return type
     */
    public static function putFile($folder, $key, $value)
    {
//        try {
//            if($folder == self::EMPL_PERMIS){
//                Employee::changeCacheVersion($key);
//            }
//        }
//        catch (Exception $ex) {
//            Log::error($ex->getMessage());
//        }
        $value = '<?php return ' . var_export($value, true) . ';';
        Storage::disk(self::DISK)->put(self::PATH_CACHE . $folder . $key, $value, self::ACCESS_FILE);
        try {
            @chmod(storage_path(self::PATH_CACHE . $folder), self::ACCESS_FOLDER);
            @chmod(storage_path(self::PATH_CACHE . $folder . $key), self::ACCESS_FOLDER);
        } catch (Exception $ex) {
        }
    }

    /**
     * get cache into file storate
     *
     * @param string $key
     * @return type
     */
    public static function getFile($folder, $key)
    {
        return Storage::disk(self::DISK)->exists(self::PATH_CACHE . $folder . $key) ?
            require storage_path(self::PATH_CACHE . $folder . $key) :
            null;
    }

    /**
     * get cache into file storate
     *
     * @param string $key
     * @return type
     */
    public static function hasFile($folder, $key)
    {
        return Storage::disk(self::DISK)->exists(self::PATH_CACHE . $folder . $key) ?
            true :
            false;
    }

    /**
     * foget cache into file storate
     *
     * @param string $key
     * @return type
     */
    public static function forgetFile($folder, $key = '')
    {
//        try {
//            if($folder == self::EMPL_PERMIS){
//                Employee::changeCacheVersion($key);
//            }
//        }
//        catch (Exception $ex) {
//            Log::error($ex->getMessage());
//        }
        if ($key) {
            return Storage::disk(self::DISK)->delete(self::PATH_CACHE . $folder . $key);
        }
        Storage::disk(self::DISK)->deleteDirectory(self::PATH_CACHE . $folder);
    }

    /**
     * remove cache with prefix key
     *
     * @param string $folder
     * @param string $keyPrefix
     * @return boolean
     */
    public static function forgetFilePrefix($folder, $keyPrefix)
    {
//        try {
//            if($folder == self::EMPL_PERMIS){
//                Employee::changeCacheVersion($keyPrefix);
//            }
//        }
//        catch (Exception $ex) {
//            Log::error($ex->getMessage());
//        }
        $files = Storage::disk(self::DISK)->files(self::PATH_CACHE . $folder);
        if (!$files || !count($files)) {
            return true;
        }
        $shortPath = self::PATH_CACHE . $folder . $keyPrefix;
        foreach ($files as $item) {
            if (!Str::startsWith($item, $shortPath)) {
                continue;
            }
            Storage::disk(self::DISK)->delete($item);
        }
    }
}
