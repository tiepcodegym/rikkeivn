<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class EmployeeWantOnsite extends CoreModel
{
    protected $table = 'employee_want_onsite';
    protected $fillable = [
        'place', 'start_at', 'end_at', 'reason', 'note'
    ];
    /**
     * get list items japan work by EmployeeId
     * @param type $employeeId
     * @return object 
     */
    public static function getItemsWantOnsite($employeeId)
    {
        $pager = Config::getPagerData(null, [
            'order' => 'updated_at',
            'dir' => 'DESC'
        ]);
        $collection = self::select('place', 'start_at', 'end_at', 'id')
            ->where('employee_id', $employeeId)
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    } 
}
