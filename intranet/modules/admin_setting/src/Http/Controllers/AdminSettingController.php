<?php

namespace Rikkei\AdminSetting\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\AdminSetting\Model\AdminDivision;
use Exception;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;

class AdminSettingController extends Controller
{
    /**
     * Show admin list of division
     * @return [view]
     */
    public function listAdmin()
    {
        Breadcrumb::add(trans('admin_setting::view.Setting'));
        Breadcrumb::add(trans('admin_setting::view.Admin list'));
        Menu::setActive('Admin list');
        $collectionModel = AdminDivision::getGridData();
        foreach ($collectionModel as $value) {
            $nameArray = [];
            $value->admin = str_replace(['{', '}'], '', $value->admin);
            $value->admin = explode(',', $value->admin);
            if (count($value->admin) > 0) {
                foreach ($value->admin as $val) {
                    array_push($nameArray, Employee::getNameEmpById($val));
                }
                $value->admin = implode(', ', $nameArray);
            }
            $value->division = Team::getTeamNameById($value->division);
        }
        return view('admin_setting::index', [
            'collectionModel' => $collectionModel
        ]);
    }

    /**
     * create new admin of division
     * @return [view]
     */
    public function saveEmployee()
    {
        $employees = request()->get('employees');
        $team = request()->get('group-team');
        $employeesEdit = request()->get('employees-edit');
        $teamEdit = request()->get('groupTeam-edit');
        $getTeam = (isset($employeesEdit)) ? $teamEdit : $team;
        $getEmployees = (isset($employeesEdit)) ? $employeesEdit : $employees;

        try {
            $adminDivision = AdminDivision::getByDivision($getTeam, false);
            if (isset($adminDivision)) {
                $adminDivision->admin = '{' . implode(',', $getEmployees) . '}';
                $adminDivision->updated_by = auth()->id();
                $adminDivision->updated_at = Carbon::now();
            } else {
                $adminDivision = new AdminDivision();
                $adminDivision->admin = '{' . implode(',', $getEmployees) . '}';
                $adminDivision->created_by = auth()->id();
            }
            $adminDivision->division = $getTeam;
            $adminDivision->save();
            if (isset($employeesEdit)) {
                $messages = [
                    'success'=> [
                        Lang::get('admin_setting::view.Update admin success'),
                    ]
                ];
            } else {
                $messages = [
                    'success'=> [
                        Lang::get('admin_setting::view.Add admin success'),
                    ]
                ];
            }
            return redirect()->route('admin::setting.list')->with('messages', $messages);
        } catch (Exception $e) {
            if (isset($employeesEdit)) {
                $messages = Lang::get('admin_setting::view.Can`t update admin');
            } else {
                $messages = Lang::get('admin_setting::view.Can`t add admin');
            }
            logger($messages, ['e' => $e]);
            return redirect()->back()->withErrors($messages)->withInput();
        }
    }

    /**
     * edit admin_division
     */
    public function editData()
    {
        try {
            $id = request()->get('id');
            $adminDivision = AdminDivision::getById($id);
            $adminDivision['admin'] = str_replace(['{', '}'], '', $adminDivision['admin']);
            $adminDivision['admin'] = explode(',', $adminDivision['admin']);
            $division = $adminDivision['division'];
            $admin = $adminDivision['admin'];
            $newArr = Employee::select('id', 'name')->whereIn('id', $admin)->get()->toArray();
            return response()->json([
                'division' => $division,
                'admin' => $newArr
            ]);
        } catch (Exception $e) {
            logger(Lang::get('admin_setting::view.show-err'), ['e' => $e]);
            return redirect()->back()->withErrors(Lang::get('admin_setting::view.show-err'))->withInput();
        }
    }

    /**
     * delete admin_division
     */
    public function delete()
    {
        $id = request()->get('id');
        $adminDivision = AdminDivision::getById($id);
        if (!$adminDivision) {
            return redirect()->back()->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $adminDivision->delete();
        $messages = [
            'success'=> [
                Lang::get('team::messages.Delete item success!'),
            ]
        ];
        return redirect()->back()->with('messages', $messages);
    }
}
