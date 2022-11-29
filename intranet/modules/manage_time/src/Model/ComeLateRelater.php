<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;

class ComeLateRelater extends CoreModel
{
    protected $table = 'come_late_relaters';

    /**
     * [get all related persons to register]
     * @param  [int] $comeLateId 
     * @return array  
     */
    public static function getRelatedPersons($registerId)
    {
        $registerRelaterTable = self::getTableName();
        $registerRelaterTableAs = $registerRelaterTable;
    	$registerTable = ComeLateRegister::getTableName();
        $registerTableAs = 'come_late_register_table';
        $employeeTable = Employee::getTableName();
        $employeeTableAs = 'employee_table';

    	$relatedPersons = self::select("{$employeeTableAs}.id as relater_id", "{$employeeTableAs}.name as relater_name", "{$employeeTableAs}.email as relater_email")
    			->join("{$registerTable} as {$registerTableAs}", "{$registerTableAs}.id", '=', "{$registerRelaterTableAs}.come_late_id")
    			->join("{$employeeTable} as {$employeeTableAs}", "{$employeeTableAs}.id", '=', "{$registerRelaterTableAs}.employee_id")
    			->where("{$registerRelaterTableAs}.come_late_id", $registerId)
    			->get();

    	return $relatedPersons;
    }
}