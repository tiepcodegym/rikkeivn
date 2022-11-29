<?php

namespace Rikkei\Project\View;

use Carbon\Carbon;
use Rikkei\Project\View\View as ProjView;

class MRExcel
{
    const IDX_CODE = 1;
    const IDX_COMPANY = 2;
    const IDX_PROJ_NAME = 3;
    const IDX_PROJ_CODE = 4;
    const IDX_PROJ_TYPE = 5;
    const IDX_TYPE_CALC = 6;
    const IDX_ESTIMATED = 7;
    const IDX_MEMBER = 8;
    const IDX_ROLE = 9;
    const IDX_EFFORT = 10;
    const IDX_START_AT = 11;
    const IDX_END_AT = 12;
    const IDX_FIRST_MONTH = 13;

    const MORE_COL = 25;
    const NUM_PER_MONTH = 4;
    const TYPE_COL = 'F';
    const MEMBER_COL = 'H';
    const ROLE_COL = 'I';
    const PROJ_NAME_COL = 'D';
    const PROJ_CODE_COL = 'E';
    const COMPANY_COL = 'C';
    const OFFSET_COL = 'M';

    /**
     * generate array month excel column
     * @param object $fromMonth
     * @param object $toMonth
     * @return array
     */
    public static function monthColumns($fromMonth, $toMonth)
    {
        $arrayMonths = [];
        $arrayIdx = [];
        $idx = 0;
        $countMonth = clone $fromMonth;
        while ($countMonth->lt($toMonth)) {
            $month = $countMonth->month;
            if (($idx == 0) ||
                    ($idx > 0 && $arrayIdx[$idx - 1] === 12)) {
                $month .= '/' . $countMonth->year;
            }
            $arrayIdx[$idx] = $month;
            $arrayMonths[$countMonth->format('m-Y')] = $month;
            $countMonth->addMonthNoOverflow();
            $idx++;
        }
        $arrayMonths[$toMonth->format('m-Y')] = $toMonth->format('n/Y');
        return $arrayMonths;
    }

    /**
     * list excel status label
     * @return type
     */
    public static function listStatusLabels()
    {
        return [
            'Released',
            'Billing',
            'Money received',
            'Closed'
        ];
    }

    /**
     * get column excel name by index
     * @param int $index
     * @return string
     */
    public static function getColNameByIndex($index)
    {
        $num = $index - ord('A');
        return \PHPExcel_Cell::stringFromColumnIndex($num);
    }

    /*
     * get billable effort foreach month
     */
    public static function getEffortOfMonth($month, $projMember, $dayOfMonth = null)
    {
        $startDate = $projMember->start_at;
        $endDate = $projMember->end_at;
        $effort = $projMember->effort;
        if (!$month instanceof Carbon) {
            $month = Carbon::createFromFormat('m-Y', $month);
        }
        $startMonth = $month->startOfMonth();
        $endMonth = clone $startMonth;
        $endMonth->lastOfMonth();
        if (!$startDate instanceof Carbon) {
            $startDate = Carbon::parse($startDate);
        }
        if (!$endDate instanceof Carbon) {
            $endDate = Carbon::parse($endDate);
        }
        if ($endDate->lt($startMonth) || $startDate->gt($endMonth)) {
            return null;
        }
        if ($startDate->lte($startMonth) && $endDate->gte($endMonth)) {
            return number_format($effort / 100, 2, '.', ',');
        }
        if ($startDate->lt($startMonth)) {
            $startDate = $startMonth;
        }
        if ($endDate->gt($endMonth)) {
            $endDate = $endMonth;
        }
        if (!$dayOfMonth) {
            $dayOfMonth = ProjView::getMM($startMonth, $endMonth, 2);
        }
        $workDay = ProjView::getMM($startDate, $endDate, 2);
        return number_format($workDay / $dayOfMonth * ($effort / 100), 2, '.', ',');
    }

    /*
     * get array actual work day of month
     */
    public static function getDayOfListMonths($arrayMonths)
    {
        $results = [];
        foreach (array_keys($arrayMonths) as $monthFormat) {
            $month = Carbon::createFromFormat('m-Y', $monthFormat);
            $startMonth = $month->startOfMonth();
            $endMonth = clone $month;
            $endMonth->lastOfMonth();
            $results[$monthFormat] = [
                'num_day' => ProjView::getMM($startMonth, $endMonth, 2),
                'month' => $month
            ];
        }
        return $results;
    }

    /*
     * shorted sheet name
     */
    public static function shortName($sheetName)
    {
        $sheetName = strtoupper(str_slug(trim($sheetName)));
        $arrName = explode('-', $sheetName);
        if (count($arrName) > 1) {
            $sheetName = '';
            foreach ($arrName as $name) {
                $sheetName .= $name[0];
            }
        }
        return substr($sheetName, 0, 15);
    }

    /*
     * convert break line html
     */
    public static function breakLine($text)
    {
        if (!$text) {
            return null;
        }
        $strText = explode("\n", $text);
        $outText = '';
        foreach ($strText as $str) {
            $outText .= e($str) . "<br />";
        }
        return trim($outText);
    }

}

