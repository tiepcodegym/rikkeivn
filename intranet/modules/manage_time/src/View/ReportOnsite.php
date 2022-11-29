<?php
namespace Rikkei\ManageTime\View;

use Rikkei\Core\View\Form;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Carbon\Carbon;

class ReportOnsite
{

    /**
     * [isAllowReport: is Allow Report Permission]
     * @return boolean
     */
    public function getEmployeesOnsite($isExport = false)
    {
        $startDateFilter = Form::getFilterData('except', "tbl.date_start", route('manage_time::hr.report-onsite') . '/');
        $endDateFilter = Form::getFilterData('except', "tbl.date_end", route('manage_time::hr.report-onsite') . '/');
        $year = Form::getFilterData('except', "tbl.year", route('manage_time::hr.report-onsite') . '/');
        if (strtotime($startDateFilter) > strtotime($endDateFilter)) {
            $startDateFilter = null;
            $endDateFilter = null;
        }

        if (!$startDateFilter|| ($startDateFilter && !$this->checkFormatDate($startDateFilter))) {
            $timeStart = Carbon::now()->firstOfMonth();
            $startDateFilter = $timeStart->format('d-m-Y');
        } else {
            $timeStart = Carbon::parse($startDateFilter);
        }
        if (!$endDateFilter || ($endDateFilter && !$this->checkFormatDate($endDateFilter))) {
            $timeEnd = Carbon::now()->lastOfMonth();
            $endDateFilter = $timeEnd->format('d-m-Y');
        } else {
            $timeEnd = Carbon::parse($endDateFilter);
        }

        $objBTEmployee = new BusinessTripEmployee();
        return [
            'dataEmp' => $objBTEmployee->reportOnsiteWithYear($timeStart, $timeEnd, $year, $isExport),
            'timeStart' => $timeStart,
            'timeEnd' => $timeEnd,
        ];
    }

    /**
     * check format date
     * @param  date $date
     * @return boolean
     */
    public function checkFormatDate($date)
    {
        return preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/", $date);
    }
}
