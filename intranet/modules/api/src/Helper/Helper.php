<?php

namespace Rikkei\Api\Helper;

use Carbon\Carbon;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Api\Models\ApiToken;
use Rikkei\Core\Model\CoreConfigData;

class Helper
{
    const TYPE_LABEL = 1; // list route with label
    const TYPE_ROUTE = 2; // list only route

    /**
     * list routes name
     *
     * @param int $type - get label with routes or get only routes
     * @return array
     */
    public static function listRoutes($type = self::TYPE_ROUTE)
    {
        $path = RIKKEI_API_PATH . 'data-sample' . DIRECTORY_SEPARATOR .  'routes.php';
        if (! file_exists($path)) {
            return [];
        }
        $data = require $path;
        if ($type === self::TYPE_LABEL) {
            return $data;
        }
        $routes = [];
        foreach ($data as $groupApi) {
            $routes = array_merge($routes, $groupApi['api']);
        }
        return $routes;
    }

    /**
     * get api token by route name
     *
     * @param string $route - current route
     * @return string
     */
    public static function getTokenByRouteName($route)
    {
        $key = 'token_' . str_slug($route);
        $token = CacheHelper::get($key);
        if (!$token && ($item = ApiToken::where('route', $route)->first())) {
            $token = [
                'token' => $item->token,
                'expired_at' => $item->expired_at,
            ];
            CacheHelper::put($key, $token);
        }
        // token length > 0 and has not expired
        $bearerToken = trim($token['token']);
        if ($bearerToken && (!$token['expired_at'] || $token['expired_at'] > Carbon::now()->toDateTimeString())) {
            return $bearerToken;
        }
        return trim(CoreConfigData::getApiToken());
    }
}
