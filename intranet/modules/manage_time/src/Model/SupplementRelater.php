<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;

class SupplementRelater extends CoreModel
{
    protected $table = 'supplement_relaters';

    /**
     * [get all related persons to register]
     * @param  [int] $registerId 
     * @return array  
     */
    public static function getRelatedPersons($registerId)
    {
        $relaterTable = self::getTableName();
        $relaterTableAs = $relaterTable;
    	$registerTable = SupplementRegister::getTableName();
        $registerTableAs = 'supplement_register_table';
        $employeeTable = Employee::getTableName();
        $employeeTableAs = 'employee_table';

    	$relatedPersons = self::select("{$employeeTableAs}.id as relater_id", "{$employeeTableAs}.name as relater_name", "{$employeeTableAs}.email as relater_email")
    			->join("{$registerTable} as {$registerTableAs}", "{$registerTableAs}.id", '=', "{$relaterTableAs}.register_id")
    			->join("{$employeeTable} as {$employeeTableAs}", "{$employeeTableAs}.id", '=', "{$relaterTableAs}.relater_id")
    			->where("{$relaterTableAs}.register_id", $registerId)
    			->get();

    	return $relatedPersons;
    }
}