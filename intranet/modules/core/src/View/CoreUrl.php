<?php

namespace Rikkei\Core\View;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Exception;

class CoreUrl
{
    /**
     * get url asset file with version file
     * 
     * @param string $pathFile
     * @return string
     */
    public static function asset($pathFile)
    {
        return URL::asset($pathFile . '?v=' . Config::get('view.assets_verson'));
    }

    /**
     * check request is api
     */
    public static function isApi()
    {
        try {
            if (Route::getCurrentRoute()) {
                $path = Route::getCurrentRoute()->getPath();
                return preg_match('/^api\//', $path);
            }
        } catch (Exception $ex) {
        }
        return false;
    }

    /**
     * get user current logged
     *
     * @return type
     */
    public static function getUserLogged()
    {
        if (self::isApi()) {
            return Auth::guard('api')->user();
        }
        return Auth::guard()->user();
    }
}
