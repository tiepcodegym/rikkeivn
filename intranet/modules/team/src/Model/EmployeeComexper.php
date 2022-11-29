<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class EmployeeComexper extends CoreModel
{
    protected $table = 'employee_com_expers';

    /**
     * get all company of employee
     *
     * @param int $employeeId
     * @return collection
     */
    public static function getAllCompanyExper($employeeId)
    {
        $pager = Config::getPagerData(null, [
            'order' => 'updated_at',
            'dir' => 'DESC'
        ]);
        $collection = self::select(['id', 'name', 'position', 'end_at', 'start_at'])
            ->where('employee_id', $employeeId)
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * get company of employee
     *
     * @param int $employeeId
     * @return collection
     */
   public static function getCompanyOfEmployee($employeeId)
   {
        return self::select(['id', 'name'])
            ->where('employee_id', $employeeId)
            ->orderBy('name', 'asc')
            ->orderBy('id', 'desc')
            ->get();
   }
}
