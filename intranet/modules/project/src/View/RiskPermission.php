<?php

namespace Rikkei\Project\View;

use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\Risk;

/**
 * View ouput gender
 */
class RiskPermission
{
    /**
     * Risk list
     * 
     * @param array $columns
     * @param array $conditions
     * @param string $order
     * @param string $dir
     * @return Risk 
     */
    public static function getList($columns, $conditions, $order, $dir, $teamIdsAvailable) {
        $emp = Permission::getInstance()->getEmployee();
        $list = Risk::getRisks($columns, $conditions, $order, $dir, $teamIdsAvailable);
        return $list;
    }
    
}
