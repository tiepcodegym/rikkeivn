<?php

namespace Rikkei\Education\Http\Controllers;

use Rikkei\Education\Http\Services\EmployeeOtService;
use Rikkei\Core\View\Breadcrumb;
use URL;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\Menu;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Core\View\View;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeOTController extends Controller
{

    private $employeeOtService;

    public function __construct(EmployeeOtService $employeeOtService)
    {
        Breadcrumb::add('Team');
        Breadcrumb::add('Member', URL::route('education::education.ot.index'));
        Menu::setActive('team');
        $this->employeeOtService = $employeeOtService;
    }

    public function index()
    {
        $route = 'education::education.ot.index';
        $currentUser = Permission::getInstance()->getEmployee();

        $projects = $this->employeeOtService->getProjectByRole($currentUser->id);

        $employees = $this->employeeOtService->getListEmployee();
        $dataSearch = CoreForm::getFilterData('except', null);
        $id = !is_null($dataSearch) && isset($dataSearch['team_id']) ? $dataSearch['team_id'] : null;
        $projectCurrent = isset($dataSearch['project']) ? $dataSearch['project'] : null;

        $teamByProject = [];
        $isScopeTeam = false;
        $isScopeCompany = false;
        $isScopeSelf = false;

        // Check user is role team
        if (Permission::getInstance()->isScopeTeam(null, $route)){
            $arrTeams = $this->employeeOtService->getTeamTree($id);
            $teamByProject = $this->employeeOtService->getTeamIdsByProject($projects);
            $currentTeamActive = $arrTeams['teamIdsAvailable'];

            if (!in_array($id, $currentTeamActive) && !is_null($id)){
                $dataCollection = $this->employeeOtService->getList(null, $projects);
                $dataCollectionAll = $this->employeeOtService->getList(null, $projects);
            } else {
                $dataCollection = $this->employeeOtService->getList();
                $dataCollectionAll = $this->employeeOtService->getList();
            }

            $isScopeTeam = true;
        }
        // Check user is role company
        elseif (Permission::getInstance()->isScopeCompany(null, $route)) {
            $dataCollection = $this->employeeOtService->getList();
            $dataCollectionAll = $this->employeeOtService->getList();
            $isScopeCompany = true;
            $arrTeams['teamIdsAvailable'] = [];

        }
        // Check user is role self
        elseif (Permission::getInstance()->isScopeSelf(null, $route)){
            $teamByProject = $this->employeeOtService->getTeamIdsByProject($projects);
            $arrTeams['teamIdsAvailable'] = [];
            $isScopeSelf = true;
            $dataCollection = $this->employeeOtService->getList(null, $projects);
            $dataCollectionAll = $this->employeeOtService->getList(null, $projects);
        } else{
            View::viewErrorPermission();
        }

        $dataCollection = $this->employeeOtService->filterList($dataCollection);
        $dataCollectionAll = $this->employeeOtService->filterListAll($dataCollectionAll);
        $ot_week = 0;
        $ot_weekend = 0;
        $ot_holiday = 0;
        $total_ot_hours = 0;
        foreach ($dataCollectionAll as $key => $item) {
            $ot_in_week = ($item->team_count > 1) ? $item->ot_in_week/$item->team_count : $item->ot_in_week;
            $ot_end_week = ($item->team_count > 1) ? $item->ot_end_week/$item->team_count : $item->ot_end_week;
            $ot_holidays_week = ($item->team_count > 1) ? $item->ot_holidays_week/$item->team_count : $item->ot_holidays_week;
            
            $ot_week += $ot_in_week;
            $ot_weekend += $ot_end_week;
            $ot_holiday += $ot_holidays_week;
            $total_ot_hours += ($ot_in_week * 1.5 + $ot_end_week * 2 + $ot_holidays_week * 3);
        }
        $dataOtTotal = [
            'ot_week' => round($ot_week, 2),
            'ot_weekend' => round($ot_weekend, 2),
            'ot_holiday' => round($ot_holiday, 2),
            'total_ot_hours' => round($total_ot_hours, 2)
        ];

        $arrTeams['teamIdsAvailable'] = array_merge($arrTeams['teamIdsAvailable'], $teamByProject);
        $arrTeams['teamIdsAvailable'] = array_unique($arrTeams['teamIdsAvailable']);

        $data = [
            'categories' => $this->employeeOtService->getOtCategories(),
            'collectionModel' => $dataCollection,
            'employeeList' => $employees,
            'dataForRender' => $dataCollection->groupBy('employee_code'),
            'teamIdCurrent' => $id,
            'projectCurrent' => $projectCurrent,
            'teamIdsAvailable' => $arrTeams['teamIdsAvailable'],
            'isScopeTeam' => $isScopeTeam,
            'isScopeCompany' => $isScopeCompany,
            'isScopeSelf' => $isScopeSelf,
            'dataOtTotal' => $dataOtTotal,
            'projects' => $projects
        ];

        return view('education::ot.index', $data);
    }

    public function exportOTList(){
        $route = trim(URL::route('education::education.ot.index'), '/') . '/';
        $routePermission = 'education::education.ot.index';
        $currentUser = Permission::getInstance()->getEmployee();
        
        $projects = $this->employeeOtService->getProjectByRole($currentUser->id);
        
        $employees = $this->employeeOtService->getListEmployee();
        $dataSearch = CoreForm::getFilterData('except', null, $route);
        $id = !is_null($dataSearch) && isset($dataSearch['team_id']) ? $dataSearch['team_id'] : null;
        $projectCurrent = isset($dataSearch['project']) ? $dataSearch['project'] : null;

        $teamByProject = [];
        $isScopeTeam = false;
        $isScopeCompany = false;
        $isScopeSelf = false;

        // Check user is role team
        if (Permission::getInstance()->isScopeTeam(null, $routePermission)){
            $arrTeams = $this->employeeOtService->getTeamTree($id);
            $teamByProject = $this->employeeOtService->getTeamIdsByProject($projects);
            $currentTeamActive = $arrTeams['teamIdsAvailable'];

            if (!in_array($id, $currentTeamActive) && !is_null($id)){
                $dataCollection = $this->employeeOtService->getList($route, $projects);
            } else {
                $dataCollection = $this->employeeOtService->getList($route);
            }

            $isScopeTeam = true;
        }
        // Check user is role company
        elseif (Permission::getInstance()->isScopeCompany(null, $routePermission)) {
            $dataCollection = $this->employeeOtService->getList($route);
            $isScopeCompany = true;
            $arrTeams['teamIdsAvailable'] = [];

        }
        // Check user is role self
        elseif (Permission::getInstance()->isScopeSelf(null, $routePermission)){
            $teamByProject = $this->employeeOtService->getTeamIdsByProject($projects);
            $arrTeams['teamIdsAvailable'] = [];
            $isScopeSelf = true;
            $dataCollection = $this->employeeOtService->getList($route, $projects);
        } else{
            View::viewErrorPermission();
        }
        
        $dataCollection = $this->employeeOtService->filterListAll($dataCollection);
        if (!$dataCollection) {
            return back()->with('messages', [
                'errors' => [
                    'Không có dữ liệu.',
                ]
            ]);
        }
        foreach ($dataCollection as $item) {
            $item->ot_in_week = ($item->team_count > 1) ? $item->ot_in_week/$item->team_count : $item->ot_in_week;
            $item->ot_end_week = ($item->team_count > 1) ? $item->ot_end_week/$item->team_count : $item->ot_end_week;
            $item->ot_holidays_week = ($item->team_count > 1) ? $item->ot_holidays_week/$item->team_count : $item->ot_holidays_week;
        }

        Excel::create('OT list', function ($excel) use ($dataCollection) {
            $excel->sheet('Sheet1', function ($sheet) use ($dataCollection) {
                $sheet->loadView('education::ot.include.export_ot_list', [
                    'data' => $dataCollection
                ]);
            });
        })->export('xlsx');
    }
}
