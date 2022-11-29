<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class EmployeeDocExpire extends CoreModel 
{

    protected $table = 'employee_doc_expire';
    const KEY_CACHE = 'employee_doc_expire';
    
    /**
     * get DocExpire by EmployeeId
     * @param int $employeeId
     */
    public static function getAllDocExpire($employeeId)
    {
        $pager = Config::getPagerData(null, [
            'order' => 'updated_at',
            'dir' => 'DESC'
        ]);
        $collection = self::select(['id', 'name', 'place', 'issue_date', 'expired_date'])
            ->where('employee_id', $employeeId)
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
}
