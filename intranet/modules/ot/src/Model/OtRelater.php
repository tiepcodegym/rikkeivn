<?php

namespace Rikkei\Ot\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;

class OtRelater extends CoreModel 
{
    protected $table = 'ot_relaters';

    /**
     * [get all related persons to register]
     * @param  [int] $registerId 
     * @return array  
     */
    public static function getRelatedPersons($otRegisterId)
    {
        $relaterTable = self::getTableName();
    	$otRegisterTable = OtRegister::getTableName();
        $employeeTable = Employee::getTableName();

    	$relatedPersons = self::select("{$employeeTable}.id as relater_id", "{$employeeTable}.name as relater_name", "{$employeeTable}.email as relater_email")
    			->join("{$otRegisterTable}", "{$otRegisterTable}.id", '=', "{$relaterTable}.ot_register_id")
    			->join("{$employeeTable}", "{$employeeTable}.id", '=', "{$relaterTable}.relater_id")
    			->where("{$relaterTable}.ot_register_id", $otRegisterId)
    			->get();

    	return $relatedPersons;
    }
}
