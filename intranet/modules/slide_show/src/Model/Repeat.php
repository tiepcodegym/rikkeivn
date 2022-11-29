<?php

namespace Rikkei\SlideShow\Model;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\Lang;

class Repeat extends CoreModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'repeat_slider';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'slide_id'];

    const TYPE_REPEAT_HOURLY = 1;
    const TYPE_REPEAT_DAILY = 2;
    const TYPE_REPEAT_WEEKLY = 3;

    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;

    /**
     * get all type repeat
     * @return array
     */
    public static function getAllTypeRepeat()
    {
        return [
            self::TYPE_REPEAT_HOURLY => Lang::get('slide_show::view.Hourly'),
            self::TYPE_REPEAT_DAILY => Lang::get('slide_show::view.Daily'),
            self::TYPE_REPEAT_WEEKLY => Lang::get('slide_show::view.Weekly'),
        ];
    }

    /**
     * get label day of week
     * @param array
     */
    public static function getLabelDayOfWeek()
    {
        return [
            self::SUNDAY => Lang::get('slide_show::view.sunday'),
            self::MONDAY => Lang::get('slide_show::view.monday'),
            self::TUESDAY => Lang::get('slide_show::view.tuesday'),
            self::WEDNESDAY => Lang::get('slide_show::view.wednesday'),
            self::THURSDAY => Lang::get('slide_show::view.thursday'),
            self::FRIDAY => Lang::get('slide_show::view.firday'),
            self::SATURDAY => Lang::get('slide_show::view.saturday'),
        ];
    }
}