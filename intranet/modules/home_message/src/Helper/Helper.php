<?php


namespace Rikkei\HomeMessage\Helper;


use Rikkei\Core\View\CookieCore;
use Rikkei\Team\View\Config;

class Helper
{

    /**
     * Parse value in collection
     * @param $id
     * @param $collection
     * @param $fieldName
     * @param string $defaultValue
     * @param string $callback
     * @return mixed
     */
    public static function valueParser($id, $collection, $fieldName, $defaultValue = '', $callback = '')
    {
        if ($fieldName) {
            return $id = 0 ? $defaultValue : $collection[$fieldName] ?: '';
        }
        return $id = 0 ? $defaultValue : $callback;
    }

    /**
     * Parse pagerData when filter change
     * @param null $filter
     * @param array $pager
     * @return array
     */
    public static function pageParser($filter = null, $pager = [])
    {
        $url = app('request')->url() . '/';
        if (isset($_COOKIE[md5('filter_pager.' . $url)])) {
            CookieCore::setRaw($url, CookieCore::getRaw('filter_pager.' . $url));
            $pagerOld = Config::getPagerData(null, ['limit' => Constant::PAGINATE_DEFAULT]);
        } elseif (!isset($_COOKIE[md5($url)])) {
            $pagerOld = Config::getPagerData(null, ['limit' => Constant::PAGINATE_DEFAULT]);
            CookieCore::setRaw($url, $pagerOld);
        } else {
            $pagerOld = CookieCore::getRaw($url);
        }
        $pager['limit'] = $pagerOld['limit'];
        return $pager;
    }

    /**
     * Parse string by pattern
     * @param string $input
     * @param string $output
     * @param string $pattern
     * @return string
     */
    public static function BODParser($input = '', $output = 'Toàn bộ RikkeiSoft', $pattern = 'BOD')
    {
        return $input == $pattern ? $output : $input;
    }

}