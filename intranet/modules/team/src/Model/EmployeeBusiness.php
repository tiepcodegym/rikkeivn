<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;

class EmployeeBusiness extends CoreModel
{
    const KEY_CACHE = 'employee_profile_business';

    protected $table = 'employee_profile_business';
    protected $fillable = ['employee_id', 'work_place', 'start_at', 'end_at', 'position'];

    public static function getGridData($employeeId)
    {
        $pager = Config::getPagerData();
        $collection = self::where('employee_id', $employeeId);
        self::filterGrid($collection);
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'asc');
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    public static function insertOrUpdate($data = [])
    {
        if (isset($data['id'])) {
            $item = self::find($data['id']);
            if (!$item) {
                throw new \Exception(trans('team::messages.Not found item.'), 404);
            }
            $item->update($data);
        } else {
            $item =self::create($data);
        }
        return $item;
    }
}
