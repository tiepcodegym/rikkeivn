<?php

namespace Rikkei\ManageTime\Model;

use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\CoreModel;
use Rikkei\ManageTime\View\ManageTimeConst;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimekeepingNotLate extends CoreModel
{
    use SoftDeletes;

    protected $table = 'timekeeping_not_late';
    protected $fillable = ['employee_id', 'weekdays'];


    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    /**
     * get label day of week
     * @param array
     */
    public function getLabelDayOfWeek()
    {
        return [
            self::SUNDAY => trans('manage_time::view.Sunday'),
            self::MONDAY => trans('manage_time::view.Monday'),
            self::TUESDAY => trans('manage_time::view.Tuesday'),
            self::WEDNESDAY => trans('manage_time::view.Wednesday'),
            self::THURSDAY => trans('manage_time::view.Thursday'),
            self::FRIDAY => trans('manage_time::view.Friday'),
            self::SATURDAY => trans('manage_time::view.Saturday'),
        ];
    }

    public function getStrFullWeek()
    {
        return TimekeepingNotLate::SUNDAY . ',' .
            TimekeepingNotLate::MONDAY . ',' .
            TimekeepingNotLate::TUESDAY . ',' .
            TimekeepingNotLate::WEDNESDAY . ',' .
            TimekeepingNotLate::THURSDAY . ',' .
            TimekeepingNotLate::FRIDAY . ',' .
            TimekeepingNotLate::SATURDAY;
    }

    /**
     * @param $empIds
     * @return mixed
     */
    public function getNotLateByEmpDate($empIds)
    {
        $tbl = self::getTableName();
        $tblEmp = Employee::getTableName();

        return self::select(
            "{$tbl}.id",
            "{$tbl}.weekdays",
            "{$tbl}.employee_id as emp_id",
            "emp.name as emp_name",
            "emp.email as emp_email"
        )
        ->join("{$tblEmp} as emp", 'emp.id', '=', "{$tbl}.employee_id")
        ->whereIn("{$tbl}.employee_id", $empIds)
        ->get();
    }

    public function getEmployeeNotLate()
    {
        $tbl = self::getTableName();
        $tblEmp = Employee::getTableName();

        return self::select(
            "{$tbl}.id",
            "{$tbl}.weekdays",
            "{$tbl}.employee_id as emp_id",
            "emp.name as emp_name",
            "emp.email as emp_email"
        )
        ->join("{$tblEmp} as emp", 'emp.id', '=', "{$tbl}.employee_id")
        ->orderBy("{$tbl}.id", 'ASC')
        ->get();
    }

    public function getDayWeek($day)
    {
        $arrDay = explode(',', $day);
        $arrTextDay = [];
        foreach ($arrDay as $number) {
            $arrTextDay[] = $this->getDayByNumber($number);
        }
        return $arrTextDay;
    }

    /**
     * get day by number
     * @param int $number 0 - 6
     * @return string
     */
    public function getDayByNumber($number)
    {
        $arrDay = $this->getLabelDayOfWeek();
        return $arrDay[$number];
    }

    public function getNotLateEById($id)
    {
        $tbl = self::getTableName();
        $tblEmp = Employee::getTableName();

        return self::select(
            "{$tbl}.id",
            "{$tbl}.weekdays",
            "{$tbl}.employee_id as emp_id",
            "emp.name as emp_name",
            "emp.email as emp_email"
        )
        ->join("{$tblEmp} as emp", 'emp.id', '=', "{$tbl}.employee_id")
        ->where("{$tbl}.id", '=', $id)
        ->first();
    }
}
