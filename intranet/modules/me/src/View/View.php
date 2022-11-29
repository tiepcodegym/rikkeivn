<?php

namespace Rikkei\Me\View;

use Rikkei\Project\View\MeView;
use Carbon\Carbon;

class View extends MeView
{
    const TYPE_S = 9;
    const TYPE_A = 7;
    const TYPE_B = 4;
    const TYPE_C = 0;

    private static $instance = null;

    /**
     * get instance of this class
     * @return object
     */
    public static function getInstance()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /*
     * list array contribute level
     */
    public function arrayContriLevels()
    {
        return [
            self::TYPE_S,
            self::TYPE_A,
            self::TYPE_B,
            self::TYPE_C,
        ];
    }

    /*
     * list array attribute level and label
     */
    public function listContributeLabels()
    {
        return [
            self::TYPE_S . '-100' => 'S',
            self::TYPE_A . '-' . self::TYPE_S => 'A',
            self::TYPE_B . '-' . self::TYPE_A => 'B',
            self::TYPE_C . '-' . self::TYPE_B => 'C',
        ];
    }

    public function arrayContriValLabels($strVal = false)
    {
        $append = $strVal ? '' : null;
        return [
            self::TYPE_S . $append => 'S',
            self::TYPE_A . $append => 'A',
            self::TYPE_B . $append => 'B',
            self::TYPE_C . $append => 'C',
        ];
    }

    public function listOldContributeLabels()
    {
        return \Rikkei\Me\Model\ME::filterContributes();
    }

    public static function typesMustComment()
    {
        return [self::TYPE_S, self::TYPE_A, self::TYPE_C];
    }

    /**
     * get number days of month base on base line months
     *
     * @param object $item ME evaluation item
     * @param array $rangeMonths list [start, end] date of month base on baseline date
     * @return integer
     */
    public static function getDaysOfMonthBaseline($evalTime, $rangeMonths)
    {
        $evalTime = ($evalTime instanceof Carbon) ? $evalTime : Carbon::parse($evalTime);
        $evalMonth = $evalTime->format('Y-m');
        $timeFirstDay = $evalTime;
        $timeLastDay = clone $evalTime;
        $timeLastDay->lastOfMonth();
        if (isset($rangeMonths[$evalMonth])) {
            $timeFirstDay = Carbon::parse($rangeMonths[$evalMonth]['start']);
            $timeLastDay = Carbon::parse($rangeMonths[$evalMonth]['end']);
        }
        return \Rikkei\Project\View\View::getMM($timeFirstDay, $timeLastDay, 2);
    }
}
