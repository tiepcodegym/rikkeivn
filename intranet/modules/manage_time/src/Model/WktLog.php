<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Employee;

class WktLog extends CoreModel
{
    protected $table = 'working_time_logs';
    protected $fillable = ['employee_id', 'date', 'time_in', 'time_out'];

    /*
     * insert or update data
     */
    public static function insertOrUpdate($month, $data = [])
    {
        $month = Carbon::createFromFormat('m-Y', $month);
        $dateStart = clone $month;
        $dateStart->startOfMonth();
        $dateEnd = clone $month;
        $dateEnd->endOfMonth();

        $employeeId = auth()->id();
        $dataTimeIn = $data['time_in'];
        $dataTimeOut = $data['time_out'];
        while ($dateEnd->gte($dateStart)) {
            $dateFormat = $dateStart->format('d-m-Y');
            $dataItem = [
                'employee_id' => $employeeId,
                'date' => $dateStart->toDateString(),
                'time_in' => isset($dataTimeIn[$dateFormat]) && $dataTimeIn[$dateFormat] ? $dataTimeIn[$dateFormat] : null,
                'time_out' => isset($dataTimeOut[$dateFormat]) && $dataTimeOut[$dateFormat] ? $dataTimeOut[$dateFormat] : null,
            ];
            $itemDate = self::where('employee_id', $employeeId)
                    ->where('date', $dateStart->toDateString())
                    ->first();
            $hasData = $dataItem['time_in'] || $dataItem['time_out'];
            if ($itemDate) {
                if ($hasData) {
                    $itemDate->update($dataItem);
                } else {
                    $itemDate->delete();
                }
            } else {
                if ($hasData) {
                    self::create($dataItem);
                }
            }
            $dateStart->addDay();
        }
    }

    /**
     * get data logs by month
     *
     * @param string $month     format 'm-Y'
     * @param int|null $employeeId
     * @param boolean $hasFilterEmp      true is find by employeeId
     *
     * @return array
     * 
     */
    public static function listByMonth($month, $employeeId = null, $hasFilterEmp = true)
    {
        if (!$month instanceof Carbon) {
            $month = Carbon::createFromFormat('m-Y', $month);
        }
        $collect = self::select(DB::raw('DATE_FORMAT(date, "%d-%m-%Y") as date'), 'time_in', 'time_out')
                ->where('date', '>=', $month->startOfMonth()->toDateString())
                ->where('date', '<=', $month->endOfMonth()->toDateString());
        if ($hasFilterEmp) {
            if (!$employeeId || is_array($employeeId)) {
                $employeeId = auth()->id();
            }
            $collect->where('employee_id', $employeeId);
        } else {
            $empTbl = Employee::getTableName();
            $wktLogTbl = self::getTableName();
            $collect->join("{$empTbl}", "{$empTbl}.id", "=", "{$wktLogTbl}.employee_id");
            $collect->addSelect(['employee_code', 'email', 'name', "{$empTbl}.id as employee_id"]);
            if ($employeeId && is_array($employeeId)) {
                $collect->whereIn("{$empTbl}.id", $employeeId);
            }
        }
        $collect = $collect->get();
        if ($collect->isEmpty()) {
            return [];
        }
        $results = [];
        foreach ($collect as $item) {
            if (!$hasFilterEmp) {
                $results[$item->employee_id][$item->date] = [
                    'time_in' => $item->time_in,
                    'time_out' => $item->time_out,
                    'employee_code' => $item->employee_code,
                    'nickname' => strtolower(CoreView::getNickName($item->email)),
                    'name' => $item->name,
                ];
            } else {
                $results[$item->date] = ['time_in' => $item->time_in, 'time_out' => $item->time_out];
            }
        }
        return $results;
    }
}
