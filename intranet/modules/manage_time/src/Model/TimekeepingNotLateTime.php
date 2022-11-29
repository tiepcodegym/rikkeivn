<?php

namespace Rikkei\ManageTime\Model;

use DB;
use Log;
use Carbon\Carbon;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\Contract\View\Config;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\Team\Model\Employee;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimekeepingNotLateTime extends CoreModel
{
    use SoftDeletes;

    protected $table = 'timekeeping_not_late_time';
    protected $fillable = ['employee_id', 'start_date', 'end_date', 'minute'];

    /**
     * @return collection
     */
    public function getNotLateTime()
    {
        $tbl = self::getTableName();
        $tblEmp = Employee::getTableName();

        return self::select(
            "{$tbl}.id",
            DB::raw("DATE_FORMAT({$tbl}.start_date, '%d/%m/%Y') as start_date"),
            DB::raw("DATE_FORMAT({$tbl}.end_date, '%d/%m/%Y') as end_date"),
            "{$tbl}.minute",
            "{$tbl}.employee_id as emp_id",
            "emp.name as emp_name",
            "emp.email as emp_email"
        )
        ->leftJoin("{$tblEmp} as emp", 'emp.id', '=', "{$tbl}.employee_id")
        ->get();
    }


    /**
     * @return collection
     */
    public function getNotLateTimePager()
    {
        $dataFilter = Form::getFilterData();
        $tbl = self::getTableName();
        $tblEmp = Employee::getTableName();

        $collection =  self::select(
            "{$tbl}.id",
            DB::raw("DATE_FORMAT({$tbl}.start_date, '%d/%m/%Y') as start_date"),
            DB::raw("DATE_FORMAT({$tbl}.end_date, '%d/%m/%Y') as end_date"),
            "{$tbl}.minute",
            "{$tbl}.employee_id as emp_id",
            "emp.name as emp_name",
            "emp.email as emp_email"
        )
        ->leftJoin("{$tblEmp} as emp", 'emp.id', '=', "{$tbl}.employee_id");
        try {
            if (isset($dataFilter['emp.email'])) {
                $collection->where("emp.email", 'LIKE', '%' . $dataFilter['emp.email'] . '%');
            }
            if (isset($dataFilter['emp.name'])) {
                $collection->where("emp.name", 'LIKE', '%' . $dataFilter['emp.name'] . '%');
            }
            
            if (isset($dataFilter['timekeeping_not_late_time.start_date'])) {
                $startDateFilter = Carbon::createFromFormat('d-m-Y', $dataFilter['timekeeping_not_late_time.start_date'])->toDateString();
                $collection->where("timekeeping_not_late_time.start_date", ">=", $startDateFilter);
            }
            
            if (isset($dataFilter['timekeeping_not_late_time.end_date'])) {
                $endDateFilter = Carbon::createFromFormat('d-m-Y', $dataFilter['timekeeping_not_late_time.end_date'])->toDateString();
                $collection->where("timekeeping_not_late_time.end_date", "<=", $endDateFilter);
            }
        } catch (\Exception $e) {
            Log::info($e);
            return null;
        }
        $pager = Config::getPagerData();
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * @param $id
     * @return collection
     */
    public function getNotLateTimeById($id)
    {
        $tbl = self::getTableName();
        $tblEmp = Employee::getTableName();

        return self::select(
            "{$tbl}.id",
            DB::raw("DATE_FORMAT({$tbl}.start_date, '%d/%m/%Y') as start_date"),
            DB::raw("DATE_FORMAT({$tbl}.end_date, '%d/%m/%Y') as end_date"),
            "{$tbl}.minute",
            "{$tbl}.employee_id as emp_id",
            "emp.name as emp_name",
            "emp.email as emp_email"
        )
        ->join("{$tblEmp} as emp", 'emp.id', '=', "{$tbl}.employee_id")
        ->where("{$tbl}.id", '=', $id)
        ->first();
    }

    /**
     * check time duplicate of employee
     * @param $empId
     * @param $startDate [Y-m-d]
     * @param $endDate [Y-m-d]
     * @param int $notId
     * @return bool
     */
    public function checkDateEmp($empId, $startDate, $endDate, $notId = -1)
    {
        $collections = self::where('employee_id', $empId)->get();
        if (count($collections)) {
            foreach ($collections as $item) {
                if ($startDate <= $item->end_date && $endDate >= $item->start_date && $item->id != $notId) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $empIds
     * @param $startDate
     * @param $endDate
     * @return collection
     */
    public function getNotLateTimeByEmpDate($empIds, $startDate, $endDate)
    {
        $tbl = self::getTableName();
        $tblEmp = Employee::getTableName();

        return self::select(
            "{$tbl}.id",
            "{$tbl}.start_date",
            "{$tbl}.end_date",
            "{$tbl}.minute",
            "{$tbl}.employee_id as emp_id",
            "emp.name as emp_name",
            "emp.email as emp_email"
        )
        ->join("{$tblEmp} as emp", 'emp.id', '=', "{$tbl}.employee_id")
        ->whereIn("{$tbl}.employee_id", $empIds)
        ->where("{$tbl}.end_date", '>=', $startDate)
        ->where("{$tbl}.start_date", '<=', $endDate)
        ->get();
    }
    
    /**
     * getNotLateTimeByEmpId
     *
     * @param  int $empid
     * @return collection
     */
    public function getNotLateTimeByEmpId($empid)
    {
        $tbl = self::getTableName();
        return self::select(
            "{$tbl}.id",
            DB::raw("DATE_FORMAT({$tbl}.start_date, '%d/%m/%Y') as start_date"),
            DB::raw("DATE_FORMAT({$tbl}.end_date, '%d/%m/%Y') as end_date"),
            "{$tbl}.minute"
        )
        ->where("{$tbl}.employee_id", '=', $empid)
        ->get();
    }
}
