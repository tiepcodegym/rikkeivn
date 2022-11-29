<?php
/**
 * Created by PhpStorm.
 * User: quanhv
 * Date: 08/01/20
 * Time: 11:04
 */

namespace Rikkei\Education\Http\Controllers;

use phpDocumentor\Reflection\Types\Integer;
use Rikkei\Core\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;

use Rikkei\Education\Http\Services\EmployeeService;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Core\View\Form as FieldForm;
use URL;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Core\View\View;

use Yajra\Datatables\Datatables;

class EmployeeManagerController extends Controller
{

    protected $routeEmployee;
    protected $employeeService;

    public function __construct(EmployeeService $service)
    {
        $this->routeEmployee = URL::route('education::education.manager.employee.index');
        Breadcrumb::add('HR');
        Breadcrumb::add('Employees', $this->routeEmployee);
        Menu::setActive('HR');
        $this->employeeService = $service;
    }

    public function index()
    {

        Breadcrumb::add(trans('education::view.manager_employee.Manager employee'), $this->routeEmployee);

        $teamId = null;
        $dataSearch = FieldForm::getFilterData('search_employee', null, null);

        $urlFilter = $this->routeEmployee . '/';

        $teamIdsAvailable = null;
        $teamTreeAvailable = [];

        $route = 'education::education.manager.employee.index';

        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $teamIdsAvailable = true;
        } else {// permission team or self profile.
            if (Permission::getInstance()->isScopeTeam(null, $route)) {
                $teamIdsAvailable = (array)Permission::getInstance()->getTeams();
            }
            // get list team_id responsible by pqa.
            $curEmp = Permission::getInstance()->getEmployee();
            $teamIdsResonsibleByPqa = PqaResponsibleTeam::getListTeamIdResponsibleTeam($curEmp->id);
            if ($teamIdsResonsibleByPqa) {
                foreach ($teamIdsResonsibleByPqa as $teamId) {
                    $teamIdsAvailable[] = $teamId->team_id;
                }
            }
            if (!$teamIdsAvailable || !Permission::getInstance()->isAllow($route)) {
                View::viewErrorPermission();
            }
            $teamIdsAvailable = array_unique($teamIdsAvailable);
            if (!$teamId) {
                $teamId = end($teamIdsAvailable);
            }

            //get team and all child avaliable
            $teamIdsChildAvailable = [];
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable)) {
                $teamPathTree = Team::getTeamPath();
                foreach ($teamIdsAvailable as $teamId) {
                    if (isset($teamPathTree[$teamId]) && $teamPathTree[$teamId]) {
                        if (isset($teamPathTree[$teamId]['child'])) {
                            $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]['child']);
                            $teamIdsChildAvailable = array_merge($teamIdsChildAvailable, $teamPathTree[$teamId]['child']);
                            unset($teamPathTree[$teamId]['child']);
                        }
                        $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]);
                    }
                    $teamTreeAvailable = array_merge($teamTreeAvailable, [$teamId]);
                }
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsChildAvailable);
            }

            if (!in_array($teamId, $teamIdsAvailable)) {
                View::viewErrorPermission();
            }

            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::find($teamId);
            }
        }

        if (isset($dataSearch['team_id'])) {
            $teamId = (int)$dataSearch['team_id'];
        }


//        $teamIdsAvailable = $teamIdsAvailable->pluck('id')->toArray();
//        if (Permission::getInstance()->isScopeTeam(null, $route)) {
//
//            $argTeamIds = array_merge($teamIdsChildAvailable, (array)Permission::getInstance()->getTeams());
//            $argTeamIds = array_unique($argTeamIds);
//            if (!in_array($teamId, $argTeamIds)) {
//                $teamId = -1;
//            }
//        }

//        dd((array)Permission::getInstance()->getTeams());
        // Get employees list is working
        $employees = $this->employeeService->getMembers($teamId);

        $data = [
            'urlFilter' => $urlFilter,
            'currentUrlx' => $this->routeEmployee,
            'roles' => Employee::getRolesPosition(),
            'collectionModel' => $employees,
            'teamIdCurrent' => $teamId,
            'teamIdsAvailable' => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable,
        ];

        return view('education::manager-employee.index', $data);
    }

    public function detail($id, Request $request)
    {

        $urlFilter = $this->routeEmployee . '/';
        $employee = $this->employeeService->getEmployeeWorkingById($id);

        if (is_null($employee)) {
            return redirect()->route('education::education.manager.employee.index');
        }

        Breadcrumb::add(trans('education::view.manager_employee.Education detail'));
        Menu::setActive('HR');

        $data = [
            'urlFilter' => $urlFilter,
            'roles' => Employee::getRolesPosition(),
            'employee' => $employee,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date
        ];
        return view('education::manager-employee.detail', $data);
    }

    public function ajaxEducationDetail(Request $request, Datatables $datatables)
    {
        $teaching = Employee::ROLE_TEACHER;
        if (json_decode($request->isStudy)) {
            $teaching = Employee::ROLE_STUDENT;
        }
        $collection = $this->employeeService->getListDataStudy($request, $teaching);
        return $this->employeeService->getDataTable($datatables, $collection, $request->isStudy);
    }

    public function educationExport()
    {
        $urlFilter = $this->routeEmployee . '/';
        $dataSearch = FieldForm::getFilterData('search_employee', null, $urlFilter);

        $this->employeeService->exportTraining($dataSearch['team_id'], $urlFilter);
    }
}