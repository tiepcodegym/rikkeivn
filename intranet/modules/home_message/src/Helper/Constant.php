<?php


namespace Rikkei\HomeMessage\Helper;


use Illuminate\Support\Facades\Lang;

class Constant
{
    const PAGINATE_DEFAULT = 20;

    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';

    const JS_DATE_FORMAT = 'YYYY-MM-DD';
    const JS_TIME_FORMAT = 'HH:mm:ss';

    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;

    const HOME_MESSAGE_GROUP_PRIORITY = 1;
    const HOME_MESSAGE_GROUP_BIRTHDAY = 2;
    const HOME_MESSAGE_GROUP_DISPLAY_DEFINED_TIME_IN_WEEK = 4;

    const HOME_MESSAGE_DAY_TYPE_DEFINED_DAY_IN_YEAR = 1;
    const HOME_MESSAGE_DAY_TYPE_DEFINED_DAY_IN_WEEK = 2;

    const HOME_MESSAGE_BANNER_TYPE_OTHER = 0;
    const HOME_MESSAGE_BANNER_TYPE_NEWS = 1;
    const HOME_MESSAGE_BANNER_TYPE_SHAKE = 2;
    const HOME_MESSAGE_BANNER_TYPE_TEST = 3;
    const HOME_MESSAGE_BANNER_TYPE_GRATEFUL = 4;
    const HOME_MESSAGE_BANNER_TYPE_DONATE = 5;
    const HOME_MESSAGE_BANNER_TYPE_LUNAR = 6;
    const HOME_MESSAGE_BANNER_TYPE_AIM = 7;
    const HOME_MESSAGE_BANNER_TYPE_TEN_YEARS_GIFT = 8;
    const HOME_MESSAGE_BANNER_TYPE_WOMEN_DAY = 9;
    const HOME_MESSAGE_BANNER_STATUS_UNAVAILABLE = 0;
    const HOME_MESSAGE_BANNER_STATUS_AVAILABLE = 1;

    const BLOG_SLUG = '/news/post/';

    public static function dateTimeFormat()
    {
        return self::DATE_FORMAT . ' ' . self::TIME_FORMAT;
    }

    public static function jsDateTimeFormat()
    {
        return self::JS_DATE_FORMAT . ' ' . self::JS_TIME_FORMAT;
    }

    public static function dayOfWeek()
    {
        return [
            self::MONDAY => Lang::get('HomeMessage::view.monday'),
            self::TUESDAY => Lang::get('HomeMessage::view.tuesday'),
            self::WEDNESDAY => Lang::get('HomeMessage::view.wednesday'),
            self::THURSDAY => Lang::get('HomeMessage::view.thursday'),
            self::FRIDAY => Lang::get('HomeMessage::view.friday'),
            self::SATURDAY => Lang::get('HomeMessage::view.saturday'),
            self::SUNDAY => Lang::get('HomeMessage::view.sunday'),
        ];
    }

    public static function homeMessageBannerTypes()
    {
        return [
            self::HOME_MESSAGE_BANNER_TYPE_OTHER => trans('HomeMessage::view.BANNER_TYPE_OTHER'),
            self::HOME_MESSAGE_BANNER_TYPE_NEWS => trans('HomeMessage::view.BANNER_TYPE_NEWS'),
            self::HOME_MESSAGE_BANNER_TYPE_SHAKE => trans('HomeMessage::view.BANNER_TYPE_SHAKE'),
            self::HOME_MESSAGE_BANNER_TYPE_TEST => trans('HomeMessage::view.BANNER_TYPE_TEST'),
            self::HOME_MESSAGE_BANNER_TYPE_GRATEFUL => trans('HomeMessage::view.BANNER_TYPE_GRATEFUL'),
            self::HOME_MESSAGE_BANNER_TYPE_DONATE => trans('HomeMessage::view.BANNER_TYPE_DONATE'),
            // self::HOME_MESSAGE_BANNER_TYPE_LUNAR => trans('HomeMessage::view.BANNER_TYPE_LUNAR'),
            self::HOME_MESSAGE_BANNER_TYPE_AIM => trans('HomeMessage::view.BANNER_TYPE_AIM'),
            self::HOME_MESSAGE_BANNER_TYPE_TEN_YEARS_GIFT => trans('HomeMessage::view.BANNER_TYPE_TEN_YEARS_GIFT'),
            self::HOME_MESSAGE_BANNER_TYPE_WOMEN_DAY => trans('HomeMessage::view.BANNER_TYPE_WOMEN_DAY'),
        ];
    }

    public static function homeMessageBannerGenders()
    {
        return [
            ['label' => trans('HomeMessage::view.FEMALE'), 'value' => '0'],
            ['label' => trans('HomeMessage::view.MALE'), 'value' => '1'],
            ['label' => trans('HomeMessage::view.ALL_GENDER'), 'value' => null],
        ];
    }
}