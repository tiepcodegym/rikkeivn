<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;

class SupplementEmployee extends CoreModel 
{
    protected $table = 'supplement_employees';

    public $timestamps = false;

    /**
     * Get all employees in a supplement register
     * Columns: employee_id, employee_code, employee_name, start_at, end_at
     *
     * @param int $registerId
     *
     * @return SupplementEmployee join Employee collection
     */
    public static function getEmployees($registerId) 
    {
        $supplementEmployeeTbl = self::getTableName();
        $empTbl = Employee::getTableName();
        return self::join("{$empTbl}", "{$empTbl}.id", "=", "{$supplementEmployeeTbl}.employee_id")
            ->select(
                "{$supplementEmployeeTbl}.employee_id",
                "{$empTbl}.employee_code",
                "{$empTbl}.name",
                "{$supplementEmployeeTbl}.start_at",
                "{$supplementEmployeeTbl}.end_at"
            )
            ->where("supplement_registers_id", $registerId)
            ->get();
    }

    public static function removeAllEmp($registerId)
    {
        self::where("supplement_registers_id", $registerId)->delete();
    }
}
