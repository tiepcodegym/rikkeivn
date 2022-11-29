<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Carbon\Carbon;

class BaselineDate extends CoreModel
{
    protected $table = 'project_baseline_date';
    protected $fillable = ['month', 'date'];

    /*
     * update baseline date current month
     */
    public static function updateCurrentMonth($date)
    {
        $monthNow = Carbon::now()->format('Y-m');
        $item = self::where('month', $monthNow)->first();
        if (!$item) {
            $item = self::create(['month' => $monthNow, 'date' => $date]);
        } else {
            $item->update(['date' => $date]);
        }
        return $item;
    }

    /**
     * get baseline date of month
     * @param type $month format Y-m
     */
    public static function getDate($month)
    {
        if ($month instanceof Carbon) {
            $month = $month->format('Y-m');
        }
        $item = self::where('month', $month)->first();
        if (!$item) {
            return null;
        }
        return $item->date;
    }
}
