<?php

namespace Rikkei\Resource\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Rikkei\Core\Http\Controllers\Controller as Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Resource\Model\Dashboard;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\View\Config;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Project\Model\Project;
use Rikkei\Resource\View\View;
use Illuminate\Http\Request;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\Input;
use Rikkei\Core\View\CookieCore;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\Model\ResourceDashboard;
use Rikkei\Resource\View\UtilizationView;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Tag\Model\Tag;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    const LIMIT = 50; // records on a page

    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('resource');
        Breadcrumb::add('Resource dashboard');
    }

    /**
     * Dashboard
     * @return view
     */
    public function index($year = null, $teamId = null)
    {
        if (!$year) {
            $year = (int)date('Y');
        }
        $sixMonth = View::getLastSixMonth();
        $twelveMonth = View::getMonthsInYear($year);

        //Count employee by effort and count plan
        $empWithEffort = $this->getResultDataEmpWithEffort($twelveMonth, $teamId, $year);
        $empPlan = $this->getResultDataCountEmpPlan($twelveMonth, $teamId, $year);

        //Total man month
        $totalMmNoBorrow = $this->getResultDataTotalManMonth($twelveMonth, $year, $teamId);
        $totalManDay = $this->getResultDataTotalManMonth($twelveMonth, $year, $teamId, true);
        $totalEmp = $this->getResultDataCountEmployee($twelveMonth, $year, $teamId);

        //Role in projects
        $totalRole = $this->getResultDataTotalRole($year, $teamId);

        //Employee by programming language
        $totalProgLang = $this->getResultDataCountProLang($year, $teamId);

        //Man month and count project by project type
        $projTypeMm = $this->getResultDataMmProject($year, $teamId);

        // Get result data language programming follow skillsheets.
        $dataLP = EmployeeSkill::getDataLanguageProgrammingSkillSheets($year, $teamId);

        return view('resource::dashboard.index', [
            'sixMonth' => $sixMonth,
            'twelveMonth' => $twelveMonth,
            'empWithEffort' => $empWithEffort,
            'totalManDay' => $totalManDay,
            'totalMmNoBorrow' => $totalMmNoBorrow,
            'totalRole' => $totalRole,            
            'totalProgLang' => $totalProgLang,
            'projTypeMm' => $projTypeMm,
            'totalEmp' => $totalEmp,
            'empPlan' => $empPlan,
            'teamsOptionAll' => TeamList::toOption(null, true, false),
            'teamId' => $teamId,
            'currentMonth' => date('m/Y'),
            'listYears' => Dashboard::getDashboardYearOption(),
            'yearSelected' => $year,
            'dataLP' => $dataLP,
        ]);
    }

    /**
     * Get result data count employee with effort
     * 
     * @param array $sixMonth
     * @param int $teamId
     * @param int $year
     * @return array
     */
    public function getResultDataEmpWithEffort($twelveMonth, $teamId, $year)
    {
        $dataEmpWithEffort = ResourceDashboard::getByTypeAndTeam(ResourceDashboard::TOTAL_EMPLOYEE_EFFORT, $year, $teamId);
        if ($dataEmpWithEffort && is_array(json_decode($dataEmpWithEffort->data, true))) {
            return json_decode($dataEmpWithEffort->data, true);
        }
        return Dashboard::totalEmpWithEffort($twelveMonth, $teamId);
    }

    /**
     * Get result data count employee plan
     * 
     * @param array $twelveMonth
     * @param int $teamId
     * @param int $year
     * @return array
     */
    public function getResultDataCountEmpPlan($twelveMonth, $teamId, $year)
    {
        $dataCountEmpPlan = ResourceDashboard::getByTypeAndTeam(ResourceDashboard::COUNT_EMPLOYEE_PLAN, $year, $teamId);
        if ($dataCountEmpPlan && is_array(json_decode($dataCountEmpPlan->data, true))) {
            return json_decode($dataCountEmpPlan->data, true);
        }
        return Dashboard::countEmpPlan($twelveMonth, $teamId);
    }

    /**
     * Get result data total man month
     * 
     * @param array $twelveMonth
     * @param int $year
     * @param int $teamId
     * @param boolean $borrow
     * @return array
     */
    public function getResultDataTotalManMonth($twelveMonth, $year, $teamId, $borrow = false)
    {
        if ($borrow) {
            $dataTotalManMonth = ResourceDashboard::getByTypeAndTeam(ResourceDashboard::TOTAL_MAN_MONTH, $year, $teamId);
        } else {
            $dataTotalManMonth = ResourceDashboard::getByTypeAndTeam(ResourceDashboard::TOTAL_MAN_MONTH_NO_BORROW, $year, $teamId);
        }
        if ($dataTotalManMonth && is_array(json_decode($dataTotalManMonth->data, true))) {
            return json_decode($dataTotalManMonth->data, true);
        }
        return $totalManMonth = Dashboard::totalManMonth($twelveMonth, $teamId, $borrow);
    }

    /**
     * Get result data count employee
     * 
     * @param array $twelveMonth
     * @param int $year
     * @param int $teamId
     * @return array
     */
    public function getResultDataCountEmployee($twelveMonth, $year, $teamId)
    {
        $dataCountEmp = ResourceDashboard::getByTypeAndTeam(ResourceDashboard::COUNT_EMPLOYEE, $year, $teamId);
        if ($dataCountEmp && is_array(json_decode($dataCountEmp->data, true))) {
            return json_decode($dataCountEmp->data, true);
        }
        return Dashboard::countEmpChart($twelveMonth, $teamId);
    }

    /**
     * Get result data total role
     * 
     * @param int $year
     * @param int $teamId
     * @return array
     */
    public function getResultDataTotalRole($year, $teamId)
    {
        $dataTotalRole = ResourceDashboard::getByTypeAndTeam(ResourceDashboard::TOTAL_ROLE, $year, $teamId);
        if ($dataTotalRole && is_array(json_decode($dataTotalRole->data, true))) {
            return json_decode($dataTotalRole->data, true);
        }
        return Dashboard::totalRole($teamId);
    }

    /**
     * Get result data count programming language
     * 
     * @param int $year
     * @param int $teamId
     * @return array
     */
    public function getResultDataCountProLang($year, $teamId)
    {
        $dataCountProLang = ResourceDashboard::getByTypeAndTeam(ResourceDashboard::COUNT_PROLANG, $year, $teamId);
        if ($dataCountProLang && is_array(json_decode($dataCountProLang->data, true))) {
            return json_decode($dataCountProLang->data, true);
        }
        return Dashboard::countProLang($teamId);
    }

    /**
     * Get result data MM project in current month
     * 
     * @param $int $year
     * @param int $teamId
     * @return array
     */
    public function getResultDataMmProject($year, $teamId)
    {
        $dataMm = ResourceDashboard::getByTypeAndTeam(ResourceDashboard::MM_PROJECT, $year, $teamId);
        $firstLastDay = View::getInstance()->getFirstLastDaysOfMonth(date('m'), date('Y'));
        if ($dataMm && is_array(json_decode($dataMm->data, true))) {
            return json_decode($dataMm->data, true);
        }
        return Dashboard::MmLastMOnthByProjType($firstLastDay, $teamId);
    }

    /**
     * Resource utilization
     */
    public function utilization()
    {
        $filter = CookieCore::getRaw('filter.resource.dashboard');
        if (!$filter) {
            $filter = [
                'projId' => '',
                'projStatus' => '',
                'programs' => '',
                'teamId' => '',
                'empId' => '',
                'startDate' => View::getInstance()->setDefautDateFilter()[0],
                'endDate' => View::getInstance()->setDefautDateFilter()[1],
                'limit' => self::LIMIT,
                'region' => '',
                'page' => 1
            ];
        }
        if (!isset($filter['viewMode'])) {
            $filter['viewMode'] = 'week';
        }

        if (!isset($filter['startDate'])) {
            $filter['startDate'] = View::getInstance()->setDefautDateFilter()[0];
        }

        if (!isset($filter['endDate'])) {
            $filter['endDate'] = View::getInstance()->setDefautDateFilter()[1];
        }

        if (!isset($filter['endDate'])) {
            $filter['endDate'] = View::getInstance()->setDefautDateFilter()[1];
        }

        if (!isset($filter['effort'])) {
            $filter['effort'] = 0;
        }

        $dashboard = $this->getDashboard($filter);

        $params = $this->getParams($filter);
        $utilizationView = new UtilizationView();
        $dataView = $utilizationView->getDataForView($dashboard['dashboard'], $filter);

        //$columnsList store columns by viewmode (day, week, month)
        switch ($filter['viewMode']) {
            case 'day':
                $columnsList = $utilizationView->getDates($filter['startDate'], $filter['endDate']);
                break;
            case 'month':
                $columnsList = $utilizationView->getMonths($filter['startDate'], $filter['endDate']);
                break;
            default:
                $columnsList = $utilizationView->getWeeks($filter['startDate'], $filter['endDate']);
                break;
        }

        $teamsOptionAll = TeamList::toOption(null, true, false);
        $statusOptions = Project::lablelState();

        // storage employees by permission
        $emps = null; 

        //Get project list filter
        $curEmp = Permission::getInstance()->getEmployee();
        $teamsOfEmp = CheckpointPermission::getArrTeamIdByEmployee($curEmp->id);
        if (Permission::getInstance()->isScopeCompany()) {
            $projOptions = Project::getProjectOptions();
        } elseif ($listTeam = Permission::getInstance()->isScopeTeam()) {
            $dashboard['teamsOfEmp'] = $listTeam;
            if (is_array($listTeam)) {
                $projOptions = Project::getProjectOptions(null, $listTeam);
            } else {
                $projOptions = Project::getProjectOptions(null, $teamsOfEmp);
            }
        } elseif (Permission::getInstance()->isScopeSelf()) {
            $projOptions = Project::getProjectOptions($curEmp->id);
        }

        //Get programming list
        $programs = Programs::getInstance()->getList();
        //convert name programming to uppercase.
        foreach ($programs as $program) {
            $program->name = strtoupper($program->name);
        }
        // Get result data language programming follow skillsheets.
        $dataLP = EmployeeSkill::getDataLanguageProgrammingSkillSheets();
        foreach ($dataLP as $key => $data) {
            $data->name = strtoupper($data->name);
            $programs->push($data);
        }
        $teamOfPTPM = Team::where('is_soft_dev', Team::IS_SOFT_DEVELOPMENT)
            ->pluck('id')
            ->toArray();
        // dd($dataView);

        return view('resource::dashboard.utilization', [
            'collectionModel' => $dataView, 
            'dashboard' => $dataView,
            'teamsOptionAll' => $teamsOptionAll, 
            'projOptions' => $projOptions,
            'statusOptions' => $statusOptions,
            'teamsOfEmp' => $dashboard["teamsOfEmp"],
            'emps' => $emps,
            'startDateFilter' => $params['startDate'],
            'endDateFilter' => $params['endDate'],
            'month' => $params['month'],
            'cur_year' => $params['cur_year'],
            'currentWeek' => $params['currentWeek'],
            'currentDate' => $params['currentDate'],
            'count' => count($dashboard["result"]),
            'programs' => $programs->unique('name'),
            'columnsList' => $columnsList,
            'viewMode' => $filter['viewMode'],
            'filter' => $filter,
            'teamOfPTPM' => $teamOfPTPM,
        ]);
    }

    public function exportUtilization()
    {
        $filter = CookieCore::getRaw('filter.resource.dashboard');
        if (!$filter) {
            $filter = [
                'projId' => '',
                'projStatus' => '',
                'programs' => '',
                'teamId' => '',
                'empId' => '',
                'startDate' => View::getInstance()->setDefautDateFilter()[0],
                'endDate' => View::getInstance()->setDefautDateFilter()[1],
                'limit' => self::LIMIT,
                'region' => '',
                'page' => 1
            ];
        }
        if (!isset($filter['viewMode'])) {
            $filter['viewMode'] = 'week';
        }

        if (!isset($filter['startDate'])) {
            $filter['startDate'] = View::getInstance()->setDefautDateFilter()[0];
        }

        if (!isset($filter['endDate'])) {
            $filter['endDate'] = View::getInstance()->setDefautDateFilter()[1];
        }

        if (!isset($filter['endDate'])) {
            $filter['endDate'] = View::getInstance()->setDefautDateFilter()[1];
        }

        if (!isset($filter['effort'])) {
            $filter['effort'] = 0;
        }

        $dashboard = $this->getDashboard($filter);

        $params = $this->getParams($filter);
        $utilizationView = new UtilizationView();
        $dataCollection = $utilizationView->getDataForView($dashboard['dashboard'], $filter, false);
        
        switch ($filter['viewMode']) {
            case 'day':
                $columnsList = $utilizationView->getDates($filter['startDate'], $filter['endDate']);
                break;
            case 'month':
                $columnsList = $utilizationView->getMonths($filter['startDate'], $filter['endDate']);
                break;
            default:
                $columnsList = $utilizationView->getWeeks($filter['startDate'], $filter['endDate']);
                break;
        }
        $viewMode = $filter['viewMode'];
        $month = $params['month'];
        $cur_year = $params['cur_year'];
        $currentWeek = $params['currentWeek'];
        $currentDate = $params['currentDate'];

        Excel::create('Resource utilization', function ($excel) use ($dataCollection, $columnsList, $viewMode, $cur_year, $currentWeek, $currentDate, $month) {
            $excel->sheet('Sheet1', function ($sheet) use ($dataCollection, $columnsList, $viewMode, $cur_year, $currentWeek, $currentDate, $month) {
                $sheet->loadView('resource::dashboard.export_utilization', [
                    'dashboard' => $dataCollection,
                    'columnsList' => $columnsList,
                    'viewMode' => $viewMode,
                    'month' => $month,
                    'cur_year' => $cur_year,
                    'currentWeek' => $currentWeek,
                    'currentDate' => $currentDate,
                ]);
            });
        })->export('xlsx');
    }

    /**
     * Get week number of time
     * @param string|datetime $time
     * @return int week number
     */
    public function getWeek($time)
    {
        $w=(int)date('W', strtotime($time));
        $m=(int)date('n', strtotime($time));
        $w=$w==1?($m==12?53:1):($w>=51?($m==1?0:$w):$w);
        return $w;
    }

    /**
     * Get month number of time
     * @param string|datetime $time
     * @return int month number
     */
    public function getMonth($time)
    {
        return date("m", strtotime($time));
    }

    /**
     * Get allocation
     * @param int|null $teamId
     * @return array
     */
    public function getDashboard($filter)
    {
        $projId = $filter['projId'];
        $projStatus = $filter['projStatus'];
        $programs = isset($filter['programs']) ? $filter['programs'] : null;
        $teamId = $filter['teamId'];
        $empFilter = $filter['empId'];
        $startDate = $filter['startDate'];
        $endDate = $filter['endDate'];
        $limit = $filter['limit'];
        $page = $filter['page'];
        //get data
        $curEmp = Permission::getInstance()->getEmployee();
        $teamsOfEmp = CheckpointPermission::getArrTeamIdByEmployee($curEmp->id);
        if (Permission::getInstance()->isScopeCompany(null, "resource::dashboard.utilization")) {
            $result = Dashboard::getInstance()->empDashboard($startDate, $endDate, $projId, $projStatus, $teamId, null, null, $empFilter, $programs, $filter['viewMode']);
        } elseif (Permission::getInstance()->isScopeTeam(null, "resource::dashboard.utilization")) {
            $result = Dashboard::getInstance()->empDashboard($startDate, $endDate, $projId, $projStatus, $teamId, null, $teamsOfEmp, $empFilter, $programs, $filter['viewMode']);
        } elseif (Permission::getInstance()->isScopeSelf(null, "resource::dashboard.utilization")) {
            $result = Dashboard::getInstance()->empDashboard($startDate, $endDate, $projId, $projStatus, $teamId, $curEmp->id, null, null, $programs, $filter['viewMode']);
        }

        $result = $result->get();
        $dashboard = [];
        $nicknames = [];
        $today = date('Y-m-d');
        foreach ($result as $item) {
            if (!empty($item->cols)) {
                $rows = explode(Dashboard::GROUP_CONCAT, $item->cols);
                if (count($rows)) {
                    foreach ($rows as $row) {
                        $proj = explode(Dashboard::CONCAT, $row);
                        if (!isset($proj[0]) || !$proj[0] ||
                            !isset($proj[1]) || !$proj[1]) {
                            continue;
                        }
                        $start = $this->getWeek($proj[0]);
                        $end = $this->getWeek($proj[1]);
                        $dashboard[$item->email.CoreModel::GROUP_CONCAT.$item->id.CoreModel::GROUP_CONCAT.$item->leave_date.CoreModel::GROUP_CONCAT.$item->join_date.CoreModel::GROUP_CONCAT.$item->team][] = 
                        [
                            'proj_id' => isset($proj[4]) ? $proj[4] : '',
                            'proj_name' => isset($proj[3]) ? $proj[3] : '',
                            'start' => $start,
                            'end' => $end,
                            'start_date' => date('Y-m-d', strtotime($proj[0])),
                            'end_date' => date('Y-m-d', strtotime($proj[1])),
                            'start_year' => date('Y', strtotime($proj[0])),
                            'end_year' => date('Y', strtotime($proj[1])),
                            'effort'    => isset($proj[2]) ? $proj[2] : 0
                        ];
                    }
                }
            } else {
                $dashboard[$item->email.CoreModel::GROUP_CONCAT.$item->id.CoreModel::GROUP_CONCAT.$item->leave_date.CoreModel::GROUP_CONCAT.$item->join_date.CoreModel::GROUP_CONCAT.$item->team][] = 
                [
                    'proj_id' => null,
                    'proj_name' => null,
                    'start' => null,
                    'end' => null,
                    'start_date' => null,
                    'end_date' => null,
                    'start_year' => null,
                    'end_year' => null,
                    'effort'    => null
                ];
            }
        }

        return [
            "dashboard" => $dashboard,
            "result" => $result,
            "teamsOfEmp" => $teamsOfEmp
        ];
    }

    /**
     * View detail effort by view mode
     *
     * @param Request $request
     * @return Response View
     */
    public function viewWeekDetail(Request $request)
    {
        $year = $request->input('year');
        $week = $request->input('week');
        $empId = $request->input('empId');
        $empName = $request->input('empName');
        $leaveDate = $request->input('leaveDate');
        $joinDate = $request->input('joinDate');
        $viewMode = $request->input('viewMode');

        //Get first day, last day of week
        if ($viewMode === 'week') {
            $firstLast = View::getInstance()->getStartAndEndDate($week, $year);
        } else {
            $firstLast = View::getInstance()->getFirstLastDaysOfMonth($week, $year);
        }

        $days = View::getInstance()->getDays($firstLast[0], $firstLast[1]);
        $projects = Dashboard::getProjectsByEmp($empId, $firstLast[0], $firstLast[1]);
        return view('resource::dashboard.include.week_detail', 
                    [
                        'days' => $days,
                        'year' => $year,
                        'week' => $week,
                        'projects' => $projects,
                        'empName' => $empName,
                        'leaveDate' => $leaveDate,
                        'joinDate' => $joinDate,
                        'viewMode' => $viewMode,
                    ])->render();
    }

    /**
     * Load dashboard when filter
     * @return html
     */
    public function ajax()
    {
        $filter = Input::get();
        if (!isset($filter['limit'])) {
            $filter['limit'] = self::LIMIT;
        }
        if (empty($filter['startDate'])) {
            $filter['startDate'] = View::getInstance()->setDefautDateFilter()[0];
        }

        if (empty($filter['endDate'])) {
            $filter['endDate'] = View::getInstance()->setDefautDateFilter()[1];
        }

        if (empty($filter['effort'])) {
            $filter['effort'] = 0;
        }

        $params = $this->getParams($filter);
        $dashboard = $this->getDashboard($filter);

        $utilizationView = new UtilizationView();
        $dataView = $utilizationView->getDataForView($dashboard['dashboard'], $filter);

        //$columnsList store columns by viewmode (day, week, month)
        switch ($filter['viewMode']) {
            case 'day':
                $columnsList = $utilizationView->getDates($filter['startDate'], $filter['endDate']);
                break;
            case 'month':
                $columnsList = $utilizationView->getMonths($filter['startDate'], $filter['endDate']);
                break;
            default:
                $columnsList = $utilizationView->getWeeks($filter['startDate'], $filter['endDate']);
                break;
        }

        // save filter to cookie
        CookieCore::setRaw('filter.resource.dashboard', $filter);
        return view('resource::dashboard.include.utilization_data', 
                    [
                        'collectionModel' => $dataView, 
                        'dashboard' => $dataView,
                        'startDateFilter' => $params['startDate'],
                        'endDateFilter' => $params['endDate'],
                        'month' => $params['month'],
                        'cur_year' => $params['cur_year'],
                        'count' => count($dashboard["result"]),
                        'currentWeek' => $params['currentWeek'],
                        'currentDate' => $params['currentDate'],
                        'columnsList' => $columnsList,
                        'viewMode' => $filter['viewMode'],
                        'filter' => $filter,
                    ])->render();
    }

    /**
     * Get params 
     * cur: start month filter. Default is current month
     * max: filter in $max month
     * last: $cur + $max
     * cur_year: current year
     * month: current month
     * startMonth: text start month filter. Format Y-m
     * endMonth: text end month filter. Format Y-m
     * @param array $filter
     * @return array
     */
    public function getParams($filter)
    {
        $curYear = date('Y');
        $startDateFilter = empty($filter['startDate']) ? View::getInstance()->setDefautDateFilter()[0] : $filter['startDate'];
        $endDateFilter = empty($filter['endDate']) ? View::getInstance()->setDefautDateFilter()[1] : $filter['endDate'];

        return [
            'cur_year' => $curYear,
            'month' => date('m'),
            'startDate' => $startDateFilter,
            'endDate' => $endDateFilter,
            'currentWeek' => \Carbon\Carbon::now()->weekOfYear,
            'currentDate' => date('Y-m-d'),
        ];
    }

    /**
     * search employee by ajax
     */
    public function empSearchAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            Employee::searchAjaxWithTeamName(Input::get('email'))
        );
    }

    /**
     * export employee effort
     */
    public function exportEmployeeEffort()
    {
        if (!isset($filter['viewMode'])) {
            $filter['viewMode'] = 'week';
        }
        $filter = CookieCore::getRaw('filter.resource.dashboard');
        if (!$filter) {
            $filter = [
                'projId' => '',
                'projStatus' => '',
                'programs' => '',
                'teamId' => '',
                'empId' => '',
                'startDate' => View::getInstance()->setDefautDateFilter()[0],
                'endDate' => View::getInstance()->setDefautDateFilter()[1],
                'limit' => self::LIMIT,
                'region' => '',
                'page' => 1
            ];
        }
        $projId = $filter['projId'];
        $projStatus = $filter['projStatus'];
        $programs = isset($filter['programs']) ? $filter['programs'] : null;
        $teamId = $filter['teamId'];
        $empFilter = $filter['empId'];
        $startDate = $filter['startDate'];
        $endDate = $filter['endDate'];
        $limit = $filter['limit'];
        $page = $filter['page'];
        $curEmp = Permission::getInstance()->getEmployee();
        $teamsOfEmp = CheckpointPermission::getArrTeamIdByEmployee($curEmp->id);
        if (Permission::getInstance()->isScopeCompany(null, "resource::dashboard.utilization")) {
            $result = Dashboard::getInstance()->empDashboard($startDate, $endDate, $projId, $projStatus, $teamId, null, null, $empFilter, $programs, $filter['viewMode']);
        } elseif (Permission::getInstance()->isScopeTeam(null, "resource::dashboard.utilization")) {
            $result = Dashboard::getInstance()->empDashboard($startDate, $endDate, $projId, $projStatus, $teamId, null, $teamsOfEmp, $empFilter, $programs, $filter['viewMode']);
        } elseif (Permission::getInstance()->isScopeSelf(null, "resource::dashboard.utilization")) {
            $result = Dashboard::getInstance()->empDashboard($startDate, $endDate, $projId, $projStatus, $teamId, $curEmp->id, null, null, $programs, $filter['viewMode']);
        }
        $result = $result->get();
        $period = CarbonPeriod::create($startDate, '1 month', $endDate);
        foreach ($period as $dt) {
            $months[] = $dt->format("Y-m");
        }
        foreach ($months as $m) {
            $month[] = Carbon::parse($m)->month;
            $year[] = Carbon::parse($m)->year;
        }
        $data = [];
        for ($x = 0; $x < sizeof($months); $x++) {
            foreach ($result as $employee) {
                if (!empty($employee->cols)) {
                    $employeeInfor = explode(',,', $employee->cols);
                    $mm = View::getInstance()->getEffortOfMonth($month[$x], $year[$x], $employeeInfor[2], $employeeInfor[0], $employeeInfor[1]);
                    $mm = $mm / 100;
                    if (empty($data[$employee->id][$month[$x] . '-' . $year[$x]])) {
                        $data[$employee->id][$month[$x] . '-' . $year[$x]] = round($mm, 2);
                    } else {
                        $data[$employee->id][$month[$x] . '-' . $year[$x]] += round($mm, 2);
                    }
                } else {
                    $data[$employee->id][$month[$x] . '-' . $year[$x]] = 0;
                }
            }
        }
        array_walk_recursive($data, function($item, $key) use (&$sumMonth){
            $sumMonth[$key] = isset($sumMonth[$key]) ?  $item + $sumMonth[$key] : $item;
        });
        foreach ($data as $mm => $value) {
            $sum[$mm] = array_sum($value);
        }
        Excel::create('Effort nhân viên', function ($excel) use ($result, $months, $data, $sumMonth, $sum) {
            $excel->sheet('sheet1', function ($sheet) use ($result, $months, $data, $sumMonth, $sum) {
                $sheet->loadView('resource::dashboard.include.export_employee_effort', [
                    'result' => $result,
                    'months' => $months,
                    'data' => $data,
                    'sumMonth' => $sumMonth,
                    'sum' => $sum
                ]);
            });
        })->export('xlsx');
    }
}
