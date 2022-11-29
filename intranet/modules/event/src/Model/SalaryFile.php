<?php

namespace Rikkei\Event\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Event\Model\SalaryMailSent;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Event\View\ViewEvent;

class SalaryFile extends CoreModel
{
    protected $table = 'salary_files';
    protected $fillable = ['title', 'filename', 'type', 'created_by'];

    /**
     * get list files
     * @return type
     */
    public static function getData($type = ViewEvent::FILE_TYPE_SALARY)
    {
        $pager = Config::getPagerData();
        $collection = self::from(self::getTableName() . ' as sf')
                ->leftJoin(Employee::getTableName() . ' as emp', 'sf.created_by', '=', 'emp.id')
                ->leftJoin(SalaryMailSent::getTableName() . ' as sms', 'sf.id', '=', 'sms.file_id')
                ->select('sf.*', 'emp.email', DB::raw('COUNT(DISTINCT(sms.id)) as count_row'))
                ->where('type', $type)
                ->groupBy('sf.id');
        //permission
        if (Permission::getInstance()->isScopeCompany()) {
            //all
        } elseif (Permission::getInstance()->isScopeTeam()) {
            $currUser = Permission::getInstance()->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $collection->join(TeamMember::getTableName() . ' as tmb', 'emp.id', '=', 'tmb.employee_id')
                    ->whereIn('tmb.team_id', $teamIds);
        } elseif (Permission::getInstance()->isScopeSelf()) {
            $collection->where('sf.created_by', Permission::getInstance()->getEmployee()->id);
        } else {
            CoreView::viewErrorPermission();
        }
        //filter
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'desc');
        }
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
}
