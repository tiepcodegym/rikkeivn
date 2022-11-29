<?php

namespace Rikkei\Notify\View;

use Carbon\Carbon;

class NotifyView
{
    const PER_PAGE = 10;
    const ALL_PER_PAGE = 50;
    const ICON_PATH = '/asset_notify/image/';

    const TYPE_MENU = 'menu';
    const TYPE_POPUP = 'popup';

    /**
     * custom display diff time
     * @param type $strTime
     * @return type
     */
    public static function diffTime($strTime)
    {
        if (!$strTime instanceof Carbon) {
            $strTime = Carbon::parse($strTime);
        }
        $diff = Carbon::now()->timestamp - $strTime->timestamp;
        //less than 1 minute
        if ($diff < 60) {
            return trans('notify::view.recently_updated');
        }
        //less than 1 hour
        if ($diff < 60 * 60) {
            return trans('notify::view.num_minutes_ago', ['minutes' => floor($diff / 60)]);
        }
        //less than 1 day
        if ($diff < 60 * 60 * 24) {
            return trans('notify::view.num_hours_ago', ['hours' => floor($diff / (60 * 60))]);
        }
        //less than 1 week
        if ($diff < 60 * 60 * 24 * 7) {
            return trans('notify::view.num_days_ago', ['days' => floor($diff / (60 * 60 * 24))]);
        }
        return $strTime->format('H:i d-m-Y');
    }

    /*
     * fix link append notify id param
     */
    public static function fixLink($id, $link = null)
    {
        if (!$link) {
            return null;
        }
        $arrUrl = parse_url($link);
        if (isset($arrUrl['host']) && $arrUrl['host'] === 'mail.google.com') {
            return $link;
        }
        if (!isset($arrUrl['query'])) {
            $arrUrl['query'] = 'notify_id=' . $id;
        } else {
            $arrUrl['query'] .= '&notify_id=' . $id;
        }
        $arrUrl['query'] = '?' . $arrUrl['query'];
        if (isset($arrUrl['fragment'])) {
            $arrUrl['fragment'] = '#' . $arrUrl['fragment'];
        }
        $scheme = $arrUrl['scheme'];
        array_shift($arrUrl);
        return $scheme . '://' . implode('', $arrUrl);
    }

    /**
     * get notify icon
     * @param array $item
     * @return string
     */
    public static function getImage($item = [])
    {
        if (isset($item['icon']) && $item['icon']) {
            return asset(self::ICON_PATH . $item['icon']);
        }
        if (isset($item['image']) && $item['image']) {
            return $item['image'];
        }
        return asset(self::ICON_PATH . 'notify.png');
    }
}

