<?php

namespace Rikkei\Project\Model;

use Lang;
use Rikkei\Project\Model\Task;
use Rikkei\Project\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\Team\Model\Employee;
use Carbon\Carbon;

class ProjEmployeeSystena extends ProjectWOBase
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'employee_project_systena';

    protected $fillable = ['employee_id'];

    /**
     * get employe project
     * @param  [int] $empId
     * @return [type]
     */
    public static function getEmpProj($empId)
    {
       return self::select("id", "employee_id")->where('employee_id', '=', $empId)->first();
    }

    /**
     * get all employee
     * @param  [date] $month [Y-m]
     * @return [builder]
     */
    public static function getAllEmpSystena($month)
    {
        $tblEmp = Employee::getTableName();
        $tblEmpSystena = self::getTableName();

        $date = Carbon::createFromFormat('Y-m', $month);
        $start = $date->startOfMonth()->toDateString();
        $end = $date->endOfMonth()->toDateString();

        return self::select(
            DB::raw("0 as projId"),
            DB::raw("1 as projName"),
            "{$tblEmpSystena}.employee_id as empId",
            "emp.employee_code as empCode",
            "emp.name as empName",
            DB::raw("{$tblEmpSystena}.id as projMemId"),
            DB::raw("'" . $start . "' as empStart"),
            DB::raw("'" . $end . "' as empEnd"),
            DB::raw("'" . $start . "' as projStart"),
            DB::raw("'" .$end . "' as projEnd"),
            DB::raw("1 as projInfor")
        )
        ->join("{$tblEmp} as emp", "emp.id", '=', "{$tblEmpSystena}.employee_id");
    }

    /**
     * get all employee
     * @return [type]
     */
    public static function getAllEmp()
    {
        $tblEmp = Employee::getTableName();
        $tblEmpSystena = self::getTableName();

        return self::select(
            "{$tblEmpSystena}.id",
            "{$tblEmpSystena}.employee_id as empId",
            "emp.employee_code as empCode",
            "emp.name as empName"
        )
        ->join("{$tblEmp} as emp", "emp.id", '=', "{$tblEmpSystena}.employee_id")
        ->get();
    }
}
