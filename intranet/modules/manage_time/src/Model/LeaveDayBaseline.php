<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\ManageTime\Model\LeaveDay;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveDayBaseline extends CoreModel
{
    protected $table = 'leave_day_baseline';
    protected $fillable = [
        'month',
        'employee_id',
        'day_last_year',
        'day_last_transfer',
        'day_current_year',
        'day_seniority',
        'day_ot',
        'day_used',
        'note'
    ];

    /**
     * Cronjob save baseline leave days
     * @param boolean $isCron
     * @return void
     * @throws \Exception
     */
    public static function cronSaveDays($isCron = true)
    {
        $timeNow = Carbon::now();
        //if not date 01 each month
        if ($isCron && $timeNow->day != 1) {
            return;
        }
        $collectLeaveDays = LeaveDay::groupBy('employee_id')->get();
        if ($collectLeaveDays->isEmpty()) {
            return;
        }

        $prevMonth = $timeNow->startOfMonth()->subDay()->format('Y-m');
        $allBaseLineDays = self::where('month', $prevMonth)
                ->get()
                ->groupBy('employee_id');

        $colsUpdate = self::getFillableCols();
        DB::beginTransaction();
        try {
            foreach ($collectLeaveDays as $dayItem) {
                $dataItem = array_only($dayItem->toArray(), $colsUpdate);
                //has baseline
                if (isset($allBaseLineDays[$dayItem->employee_id])) {
                    $blItem = $allBaseLineDays[$dayItem->employee_id]->first();
                    $blItem->update($dataItem);
                } else {
                    $dataItem['month'] = $prevMonth;
                    self::create($dataItem);
                }
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * Lấy dữ liệu của bảng leave_day_baseline tương ứng danh sách nhân viên và tháng tìm kiếm
     * @param arrEmployeeId mảng employees.id
     * @param arrMonth mảng các tháng cần tìm kiếm
     * @return Array dữ liệu leave_day_baseline tương ứng với danh sách nhân viên và tháng tìm kiếm
     */
    public static function getLeaveDayBaseLineByFilterData($arrEmployeeId, $arrMonth)
    {
        $leaveDayBaseLine = self::select(['month', 'employee_id', 'day_last_year', 'day_last_transfer', 'day_current_year', 'day_seniority', 'day_ot', 'day_used'])
            ->whereIn('employee_id', $arrEmployeeId)
            ->whereIn('month', $arrMonth)
            ->orderBy('employee_id', 'asc')
            ->orderBy('month', 'asc')
            ->distinct()
            ->get();
        return $leaveDayBaseLine;    
    }
}
