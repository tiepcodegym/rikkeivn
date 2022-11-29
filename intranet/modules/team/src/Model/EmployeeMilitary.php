<?php

namespace Rikkei\Team\Model;

class EmployeeMilitary extends EmployeeItemRelate
{
    protected $table = 'employee_military';

    const SOLDIER_LEVEL_1 = 1;
    const SOLDIER_LEVEL_2 = 2;
    const SOLDIER_LEVEL_3 = 3;
    const SOLDIER_LEVEL_4 = 4;

    /**
     * get model by employeeId
     * @param int $employeeId
     * @return EmployeeMilitary Description
     */
    public static function getModelByEmplId($employeeId)
    {
        $thisTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        
        $model = self::select(["{$thisTable}.*"])
            ->join("{$employeeTable}", "{$thisTable}.employee_id", "=", "{$employeeTable}.id")
            ->where("{$thisTable}.employee_id", "=", $employeeId)
            ->first();
        
        if( !$model ) {
            $model = new static;
            $model->employee_id = $employeeId;
        }
        
        return $model;
    }
    
    /**
     * get array value => label soldier level
     * @return array [[label => 'label' , value => 'value']]
     */
    public static function toOptionSoldierLevel()
    {
        return [
            self::SOLDIER_LEVEL_1 => trans('team::profile.Sodier level1'),
            self::SOLDIER_LEVEL_2 => trans('team::profile.Sodier level2'),
            self::SOLDIER_LEVEL_3 => trans('team::profile.Sodier level3'),
            self::SOLDIER_LEVEL_4 => trans('team::profile.Sodier level4'),
        ];
    }
}

