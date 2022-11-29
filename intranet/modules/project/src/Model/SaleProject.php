<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Project;

class SaleProject extends CoreModel
{
	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sale_project';

    /**
     * check employee is Salesperson of project.
     *
     * @param $employeeId: that id of employee check.
     * @param $projectId: that id of project check.
     * @return null | [object].
     */
    public static function isSalePersonOfProjs($employeeId, $projectId) {
        $saleProjsTbl = self::getTableName();
        $empTbl = Employee::getTableName();

        return Employee::select("{$saleProjsTbl}.employee_id")
                        ->join($saleProjsTbl, "{$saleProjsTbl}.employee_id", '=', "{$empTbl}.id")
                        ->whereNull("{$empTbl}.leave_date")
                        ->where("{$saleProjsTbl}.project_id", '=', $projectId)
                        ->where("{$saleProjsTbl}.employee_id", '=', $employeeId)
                        ->first();
    }
}
