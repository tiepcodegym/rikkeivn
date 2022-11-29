<?php

namespace Rikkei\AdminSetting\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\AdminSetting\Model\AdminOtDisallow;
use Exception;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\View\View;

use Rikkei\Team\Model\TeamModel;
use Rikkei\Assets\View\RequestView;

class AdminOTController extends Controller
{
    /**
     * Show admin list of division
     * @return [view]
     */
    public function otAdmin()
    {
        Breadcrumb::add(trans('admin_setting::view.Setting'));
        Breadcrumb::add(trans('admin_setting::view.List OT disallow'));
        Menu::setActive('OT disallow');

        $currentTeam = EmployeeTeamHistory::getCurrentTeams(auth()->id());

        $curEmp = Employee::where('id', auth()->id())->first();
        $leaderId = $curEmp->id;// $getleader->leader_id;
        $scope = Permission::getInstance();
        $urlFilter = 'admin::setting-ot.ott';
        $isScopeTeam = false;
        $isScopeCompany = false;

        if ($scope->isScopeCompany(null, $urlFilter)) {
            $teamIds = [];
            $teamsOptionAll = TeamList::toOption(null, true, false, null);
            $collectionModel = AdminOtDisallow::getEmployeeOtDivision(null);
            $isScopeCompany = True;

        } elseif ($teamIds= $scope->isScopeTeam(null, $urlFilter)) {
            $teamsOptionAll = TeamList::toOption(null, true, false, null);
            $collectionModel = AdminOtDisallow::getEmployeeOtDivision($teamIds);
            $isScopeTeam = True;

        }  else {
            View::viewErrorPermission();
        }

        return view('admin_setting::otdisallow.index', [
            'collectionModel' => $collectionModel , 'teamsOptionAll'=>$teamsOptionAll, '$scope' => $scope,'isScopeCompany' =>$isScopeCompany,'isScopeTeam' => $isScopeTeam,'teamIds' => $teamIds,
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
            $employeeOtDivision = AdminOtDisallow::getByDivision($getTeam, false);
            if (isset($employeeOtDivision)) {
                $employeeOtDivision->employee_id = '{' . implode(',', $getEmployees) . '}';
                $employeeOtDivision->updated_by = auth()->id();
                $employeeOtDivision->updated_at = Carbon::now();
            } else {
                $employeeOtDivision = new AdminOtDisallow();
                $employeeOtDivision->employee_id = '{' . implode(',', $getEmployees) . '}';
                $employeeOtDivision->created_by = auth()->id();
            }
            $employeeOtDivision->division = $getTeam;
            $employeeOtDivision->save();
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
            return redirect()->back()->with('messages', $messages);
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
     * edit employee_division
     */

    public function editData()
    {
        try {
            $id = request()->get('id');
            $employeeOtDivision = AdminOtDisallow::getById($id);
            $employeeOtDivision['employee_id'] = str_replace(['{', '}'], '', $employeeOtDivision['employee_id']);
            $employeeOtDivision['employee_id'] = explode(',', $employeeOtDivision['employee_id']);
            $division = $employeeOtDivision['division'];
            $employee = $employeeOtDivision['employee_id'];
            $newArr = Employee::select('id', 'name')->whereIn('id', $employee)->get()->toArray();
            return response()->json([
                'division' => $division,
                'employee_id' => $newArr
            ]);
        } catch (Exception $e) {
            logger(Lang::get('admin_setting::view.show-err'), ['e' => $e]);
            return redirect()->back()->withErrors(Lang::get('admin_setting::view.show-err'))->withInput();
        }
    }

    /**
     * delete employee_division
     */
    public function delete()
    {
        $id = request()->get('id');
        $employeeOtDivision = AdminOtDisallow::getById($id);
        if (!$employeeOtDivision) {
            return redirect()->back()->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $employeeOtDivision->delete();
        $messages = [
            'success'=> [
                Lang::get('team::messages.Delete item success!'),
            ]
        ];
        return redirect()->back()->with('messages', $messages);
    }


}
