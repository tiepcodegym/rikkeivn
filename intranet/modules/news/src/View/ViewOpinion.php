<?php

namespace Rikkei\News\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\News\Model\Opinion;
use Rikkei\News\Model\Post;
use Rikkei\News\Model\LikeManage;
use Rikkei\News\Model\PostComment;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;

class ViewOpinion
{
    protected static $instance;

    public function __construct()
    {
        $currentRouteName = Route::currentRouteName();
        if (!Permission::getInstance()->isAllow($currentRouteName)) {
            return View::viewErrorPermission();
        }
    }


    public function getFilterEmployee()
    {
        $filterEmployeeId = Form::getFilterData('number', 'employee_id');
        if ($filterEmployeeId) {
            $employee = Employee::findOrFail($filterEmployeeId);
            return $employee;
        }

        return null;
    }

    public function index()
    {
        $pager = Config::getPagerData(null, ['order' => 'created_at', 'dir' => 'desc']);
        $employeeTbl = Employee::getTableName();
        $opinionTbl = Opinion::getTableName();
        $collections = Opinion::select([
            DB::raw("{$opinionTbl}.*"),
            DB::raw("{$employeeTbl}.name as employee_name"),
            DB::raw("{$employeeTbl}.nickname as nickname"),
            DB::raw("{$employeeTbl}.email as employee_email"),
            DB::raw("{$employeeTbl}.id as employee_id")
        ])->join($employeeTbl, "{$employeeTbl}.id", '=', "{$opinionTbl}.employee_id")
                             ->whereNull("{$opinionTbl}.deleted_at")->orderBy($pager['order'], $pager['dir']);
        CoreModel::filterGrid($collections, [], null, 'LIKE');
        CoreModel::pagerCollection($collections, $pager['limit'], $pager['page']);

        return $collections;
    }

    public function store($request)
    {
        $model = Opinion::create($request->all());

        return $model;
    }

    public function edit($id)
    {
        $model = Opinion::findOrFail($id);
        $model->with('employee');

        return $model;
    }

    public function update($id, $request)
    {
        $model = Opinion::findOrFail($id);
        $model = $model->update($request->only('status'));

        return $model;
    }

    public function delete($id)
    {
        $model = Opinion::findOrFail($id);
        $model->delete();

        return true;
    }
}
