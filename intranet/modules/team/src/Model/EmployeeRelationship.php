<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;

class EmployeeRelationship extends CoreModel
{

    protected $table = 'employee_relationship';
    const STATUS_IS_DIE = 1;
    const STATUS_IS_NOT_DIE = 0;

    /**
     * get collection employee relations
     *
     * @param int $employeeId
     * @return Collection
     */
    public static function gridRelationsViewByEmpl($employeeId)
    {
        $pager = Config::getPagerData(null, ['dir' => 'DESC']);
        $collection = self::select([
            'id', 'name', 'relationship', 'date_of_birth', 'mobile', 'career',
            'is_dependent'
        ])
        ->where("employee_id", $employeeId)
        ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }


    /**
     * check birthday is input only year
     *
     * @return boolean
     */
    public function isOnlyYearBirth()
    {
        return !$this->date_of_birth || strlen($this->date_of_birth) < 5;
    }

    /**
     * get array list employee relations
     *
     * @param array $employeeId
     * @return array.
     */
    public static function getListRelationshipEmp($employeeId)
    {
        $empTbl = Employee::getTableName();
        $selfTbl = self::getTableName();

        return self::select([
            "{$empTbl}.employee_code",
            "{$empTbl}.name",
            "{$empTbl}.email",
            "{$selfTbl}.name as r_name",
            "{$selfTbl}.relationship as r_relationship",
            "{$selfTbl}.date_of_birth",
            "{$selfTbl}.mobile as r_mobile",
            "{$selfTbl}.id_number as r_id_number"
        ])
        ->leftJoin($empTbl, "{$selfTbl}.employee_id", '=', "{$empTbl}.id")
        ->whereIn("{$selfTbl}.employee_id", $employeeId)
        ->orderBy('email', 'ASC')
        ->get()->toArray();
    }

    public static function getRelationEmpByRegister($registerId)
    {
        $selfTbl = self::getTableName();
        $leaveDayRelaMem = LeaveDayRelationMember::getTableName();
        $ralationshipName = RelationNames::getTableName();

        return self::select([
            "{$selfTbl}.name as r_name",
            "{$leaveDayRelaMem}.*",
            "{$ralationshipName}.name as r_relationship_name"
        ])
            ->leftJoin("{$leaveDayRelaMem}", "{$leaveDayRelaMem}.employee_relationship_id", '=', "{$selfTbl}.id")
            ->leftJoin("{$ralationshipName}", "{$selfTbl}.relationship", '=', "{$ralationshipName}.id")
            ->where("{$leaveDayRelaMem}.leave_day_registers_id", $registerId)
            ->get()->toArray();
    }

    public static function getListRelationEmpNotDie($employeeId)
    {
        $selfTbl = self::getTableName();
        $relationName = RelationNames::getTableName();

        return self::select([
            "{$selfTbl}.id as r_id",
            "{$selfTbl}.name as r_name",
            "{$selfTbl}.relationship as r_relationship",
            "{$selfTbl}.date_of_birth",
            "{$selfTbl}.mobile as r_mobile",
            "{$selfTbl}.id_number as r_id_number",
            "{$relationName}.name as r_relationship_name",
        ])
            ->leftJoin($relationName, "{$relationName}.id", '=', "{$selfTbl}.relationship")
            ->where("{$selfTbl}.employee_id", $employeeId)
            ->where("{$selfTbl}.is_die", '!=', self::STATUS_IS_DIE)
            ->orderBy('email', 'ASC')
            ->get()->toArray();
    }
}

