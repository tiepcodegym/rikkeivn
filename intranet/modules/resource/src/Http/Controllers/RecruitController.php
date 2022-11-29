<?php

namespace Rikkei\Resource\Http\Controllers;

use Illuminate\Support\Str;
use Rikkei\Core\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;
use Rikkei\Team\Model\Permission as TeamPermission;
use Rikkei\Core\View\Form;
use Rikkei\Resource\Model\Channels;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\TeamList;
use Rikkei\Resource\Model\TeamFeature;
use Rikkei\Resource\Model\RecruitPlan;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Team\View\Permission;
use Carbon\Carbon;
use Validator;
use DB;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\Lang;
use Rikkei\Resource\View\getOptions;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Core\View\View as CoreView;
use Excel;

class RecruitController extends Controller {
    
    /**
     * construct
     */
    public function _construct() {
        Breadcrumb::add(trans('resource::view.Recruitment'), route('resource::recruit.index'));
        Menu::setActive('resource');
    }
    
    /**
     * recruit statistic
     * @return type
     */
    public function index (Request $request)
    {
        if (!Permission::getInstance()->isScopeCompany() && !Permission::getInstance()->isScopeTeam()) {
            CoreView::viewErrorPermission();
        }

        Breadcrumb::add(trans('resource::view.Statistics'));

        $workingTypeInternal = getOptions::workingTypeInternal();
        $currYear = $request->has('year') ? $request->get('year') : Carbon::now()->format('Y');
        //get plan foreach month
        $plansMonth = RecruitPlan::getPlanOfYear($currYear);
        //get actual foreach month
        $actualsMonth = RecruitPlan::getActualOfYear($currYear, $workingTypeInternal);
        //all contract
        $actualsMonthTotal = RecruitPlan::getActualOfYear($currYear);
        //get candidate passed separator by month
        $passesMonth = RecruitPlan::getCandidatesSepByMonth($currYear, [], $workingTypeInternal);
        //all contract
        $passesMonthTotal = RecruitPlan::getCandidatesSepByMonth($currYear);
        $leavedMonthTotal = RecruitPlan::getCandidatesSepByMonth($currYear, [], [], 'out');
        //get total candidate passed before year (only internal)
        $totalCddPassedBefore = RecruitPlan::totalCandidatePassedBefore($currYear, $workingTypeInternal);
        //all cdd pass before (all contract)
        $cddPassedBeforeTotal = RecruitPlan::totalCandidatePassedBefore($currYear);
        $title = trans('resource::view.Recruitment statistics');
        return view(
            'resource::recruit.index',
            compact(
                'currYear',
                'recruiter',
                'plansMonth',
                'actualsMonth',
                'actualsMonthTotal',
                'passesMonth',
                'passesMonthTotal',
                'leavedMonthTotal',
                'totalCddPassedBefore',
                'cddPassedBeforeTotal',
                'title'
            )
        );
    }

    /**
     * recruit statistic
     * @return type
     */
    public function indexCandidate (Request $request) {
        Breadcrumb::add(trans('resource::view.Statistics'));
        //get range year
        $nowYearInt = Carbon::now()->year - 5;
        $minYear = (int) RecruitPlan::min('year') - 5;
        if ($minYear <= 0 || $minYear < $nowYearInt) {
            $minYear = $nowYearInt;
        }     
        $rangeYears = [$minYear, Carbon::now()->addYear(10)->format('Y')];
        $currYear = $request->has('year') ? $request->get('year') : Carbon::now()->format('Y');
        $recruiter = $request->has('recruiter') ? $request->get('recruiter') : null;
        if ($recruiter == trans('resource::view.Candidate.Create.All recruiters')){
            $recruiter = '';
        }
        //get cv received by month
        $cvReceived = RecruitPlan::getCVInYear($currYear, $recruiter);        
        //get test results by month
        $testResults = RecruitPlan::getCandidateResultInYear($currYear, 'test', $recruiter);
        //get interview results by month
        $interviewResults = RecruitPlan::getCandidateResultInYear($currYear, 'interview', $recruiter);
        //get offer results by month
        $offerResults = RecruitPlan::getCandidateResultInYear($currYear, 'offer', $recruiter);
        //get recruiter list
        $teamPermission = new TeamPermission();
        $hrAccounts = $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray();
        return view('resource::recruit.indexCandidate', 
                compact('rangeYears', 'currYear', 'recruiter', 'cvReceived', 'testResults', 'interviewResults', 'offerResults', 'hrAccounts'));
    }
    
    /**
     * redirect to index
     * @return type
     */
    public function statistics() {
        return redirect()->route('resource::recruit.index');
    }
    
    /**
     * building plan
     * @param Request $request
     * @return type
     */
    public function buildPlan(Request $request) {
        Breadcrumb::add(trans('resource::view.Building plan'));

        $currYear = $request->has('year') ? $request->get('year') : Carbon::now()->format('Y');
        //list plan for each team
        $plans = RecruitPlan::where('year', $currYear)->get();
        $checkEdit = false;
        $plansArray = [];
        if (!$plans->isEmpty()) {
            $checkEdit = true;
            foreach ($plans as $plan) {
                $plansArray[$plan->team_id][$plan->year][$plan->month] = $plan->number;
            }
        }
        //get team list
        $teamList = TeamFeature::getList();
        $hasPermissEditTeam = Permission::getInstance()->isAllow('resource::plan.team.edit');
        return view('resource::recruit.plan', compact('currYear', 'teamList', 'plansArray', 'checkEdit', 'hasPermissEditTeam'));
    }
    
    /**
     * update plan
     * @param Request $request
     * @return type
     * @throws \Exception
     */
    public function updatePlan(Request $request) {
        $valid = Validator::make($request->all(), [
            'year' => 'required|numeric',
            'plans' => 'required'
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $year = $request->input('year');
        $plans = $request->input('plans');
        $hasVal = false;
        
        DB::beginTransaction();
        try {
            foreach ($plans as $teamId => $teamPlans) {
                foreach ($teamPlans as $month => $number) {
                    if (!$hasVal && $number !== '') {
                        $hasVal = true;
                    }
                    if ($number < 0) {
                        return redirect()->back()->withInput()->with('messages', ['errors' => [trans('resource::message.Please input number greater or equal 0')]]);
                    }
                    $plan = RecruitPlan::where('team_id', $teamId)
                            ->where('year', $year)
                            ->where('month', $month)
                            ->first();
                    if (!$plan) {
                        $plan = new RecruitPlan();
                    }
                    $plan->team_id = $teamId;
                    $plan->year = $year;
                    $plan->month = $month;
                    $plan->number = $number === '' ? null : $number;
                    $plan->save();
                }
            }
            if (!$hasVal && !$request->get('is_edit')) {
                return redirect()->back()->withInput()->with('messages', ['errors' => [trans('resource::message.Please input plan')]]);
            }
            DB::commit();
            return redirect()->back()->with('messages', ['success' => [trans('resource::message.Save successful')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->back()->withInput()->with('messages',  ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }

    /**
     * report detail recruitment
     * @param type $month
     * @param type $year
     */
    public function reportDetail($timeType, $type, $year, $month = null)
    {
        Breadcrumb::add(trans('resource::view.Detail.en'));
        $aryTimeTypes = ['month', 'year'];
        $aryTypes = ['in', 'out', 'total-in', 'total-out', 'test', 'interview', 'offer', 'dev-position'];
        if (!in_array($timeType, $aryTimeTypes) || !in_array($type, $aryTypes)) {
            abort(404);
        }

        $time = Carbon::createFromDate($year, $month, 1)->setTime(0, 0, 0);
        $contracTypeInternal = [];
        if (in_array($type, ['in', 'out'])) {
            $contracTypeInternal = getOptions::workingTypeInternal();
        }

        $strAryTypes = explode('-', $type);
        $method = '';
        foreach ($strAryTypes as $strType) {
            $method .= ucfirst($strType);
        }

        $isScopeCopany = Permission::getInstance()->isScopeCompany();
        $isScopeTeam = Permission::getInstance()->isScopeTeam();
        if (!$isScopeCopany && !$isScopeTeam) {
            CoreView::viewErrorPermission();
        }
        if ($isScopeCopany) {
            $isScopeTeam = false;
        }
        $routeTeamFilter = route('resource::recruit.report_detail', ['timeType' => 'filter-time', 'type' => 'filter', 'year' => null, 'month' => null]);
        $cdTeamFilter = FormView::getFilterData('excerpt', 'team_id', $routeTeamFilter);
        if ($isScopeTeam) {
            $currentUser = Permission::getInstance()->getEmployee();
            $teams = $currentUser->teams;
            $teamList = [];
            if (!$teams->isEmpty()) {
                if (!$cdTeamFilter) {
                    $cdTeamFilter = $teams->first()->id;
                }
                foreach ($teams as $team) {
                    $teamList[] = [
                        'label' => $team->name,
                        'value' => $team->id
                    ];
                }
            }
            $generalTeams = TeamList::getEmployeeTeamList(false, $cdTeamFilter, $currentUser->id);
        } else {
            $teamList = TeamList::toOption(null, true, false);
            array_push($teamList, ['value' => -1, 'label' => 'Others']);
            $generalTeams = TeamList::getEmployeeTeamList(true, $cdTeamFilter);
        }

        $collectionModel = call_user_func(
            '\Rikkei\Resource\Model\RecruitPlan::employee'. $method,
            $time,
            $timeType,
            $contracTypeInternal,
            $cdTeamFilter
        );

        //list joined by teams
        $joinedTeams = RecruitPlan::getEmpJoinedSepByTeam($time, $timeType, $contracTypeInternal);
        //list candidate joined teams;
        $cddJoinedTeams = RecruitPlan::getCandidatePassedSepByTeam($time, $timeType, $contracTypeInternal);

        //list leaved off by teams
        $leavedTeams = RecruitPlan::getEmpLeavedSepByTeam($time, $timeType, $contracTypeInternal);

        $title = trans('resource::view.Recruitment statistics detail');
        $programs = null;
        if (in_array($type, ['test', 'interview', 'offer', 'in', 'total-in', 'dev-position'])) {
            $programs = \Rikkei\Resource\Model\Programs::getListOption();
        }

        return view(
            'resource::recruit.detail',
            compact(
                'collectionModel',
                'month',
                'year',
                'teamList',
                'joinedTeams',
                'leavedTeams',
                'cddJoinedTeams',
                'title',
                'type',
                'timeType',
                'aryTimeTypes',
                'aryTypes',
                'programs',
                'cdTeamFilter',
                'routeTeamFilter',
                'isScopeTeam',
                'generalTeams'
            )
        );
    }

    /**
     * update employee account status
     * @param  Request
     * @return JsonResponse
     */
    public function updateAccountStatus(Request $request) {
        if (!($request->ajax())) {
            return redirect('/');
        }
        $employeeId = $request->input('employeeId');
        $accStatus = $request->input('accStatus');
        $response = [];
        //find employee
        $employee = Employee::find($employeeId);
        if (!$employee) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'employeeId' => 'required',
            'accStatus' => 'required',
        ]);
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            return response()->json($response);
        }
        $employee->setData(['account_status' => $accStatus]);
        try {
            $employee->save();
        } catch (Exception $ex) {
            Log::info($ex);
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            return response()->json($response);
        }
        $response['success'] = 1;
        $response['accStatus_value'] = $employee->getAccountStatus();
        $response['accStatus'] = $employee->account_status;
        return response()->json($response);
    }
    /**
     * update leader approve employee resign
     * @param Request
     * @return JsonResponse
     */
    public function updateLeaderApprove(Request $request) {
        if (!($request->ajax())) {
            return redirect('/');
        }
        $employeeId = $request->input('employeeId');
        $leaderCheck = (int) $request->input('check');
        $response = [];
        //find employee
        $employee = Employee::find($employeeId);
        if (!$employee) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'employeeId' => 'required',
            'check' => 'required',
        ]);
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            return response()->json($response);
        }
        $employee->setData(['leader_approved' => $leaderCheck]);
        try {
            $employee->save();
        } catch (Exception $ex) {
            Log::info($ex);
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            return response()->json($response);
        }
        $response['success'] = 1;
        $response['check'] = $employee->leader_approved;
        return response()->json($response);
    }

    public function exportDetail($timeType, $type, $year, $month = null)
    {
        $urlFilter = route('resource::recruit.report_detail', [
            'timeType' => $timeType,
            'type' => $type,
            'year' => $year,
            'month' => $month
        ]) . '/no-filter/';
        $routeTeamFilter = route('resource::recruit.report_detail', ['timeType' => 'filter-time', 'type' => 'filter', 'year' => null, 'month' => null]);
        $cdTeamFilter = FormView::getFilterData('excerpt', 'team_id', $routeTeamFilter);
        $contracTypeInternal = [];
        if (in_array($type, ['in', 'out'])) {
            $contracTypeInternal = getOptions::workingTypeInternal();
        }
        $time = Carbon::createFromDate($year, $month, 1)->setTime(0, 0, 0);

        if (in_array($type, ['in', 'out'])) {
            $methodIn = 'employeeIn';
            $methodOut = 'employeeOut';
        } elseif (in_array($type, ['total-in', 'total-out'])) {
            $methodIn = 'employeeTotalIn';
            $methodOut = 'employeeTotalOut';
        } else {
            abort(404);
        }
        $collectionIn = call_user_func(
            '\Rikkei\Resource\Model\RecruitPlan::' . $methodIn,
            $time,
            $timeType,
            $contracTypeInternal,
            $cdTeamFilter,
            $urlFilter,
            true
        );
        $collectionOut = call_user_func(
            '\Rikkei\Resource\Model\RecruitPlan::' . $methodOut,
            $time,
            $timeType,
            $contracTypeInternal,
            $cdTeamFilter,
            $urlFilter,
            true
        );

        $fileName = str_slug(trans('resource::view.Recruitment statistics')) . '_' . $year . ($month ? '_' . $month : '');
        if (!$contracTypeInternal) {
            $fileName .= '_All';
        }
        Excel::create($fileName, function ($excel) use ($collectionIn, $collectionOut, $year, $month, $contracTypeInternal) {
            $excel->sheet($year . ($month ? '_' . $month : ''), function ($sheet) use ($collectionIn, $collectionOut, $year, $month, $contracTypeInternal) {
                $sheet->loadView('resource::recruit.export.stats-detail', [
                    'collectionIn' => $collectionIn,
                    'collectionOut' => $collectionOut,
                    'year' => $year,
                    'month' => $month,
                    'contractTypes' => $contracTypeInternal
                ]);
            });
        })->download('xlsx');
    }

    /*
     * view recruitment monthly report
     */
    public function monthlyReport()
    {
        Breadcrumb::add(Lang::get('resource::view.Monthly report'));
        $data = $this->builderMonthlyReport();
        $data['collectionModel'] = $data['recruiters'];
        unset($data['recruiters']);
        return view('resource::recruit.monthly_report', $data);
    }

    /*
     * export recruitment monthly report
     */
    public function exportMonthlyReport()
    {
        $data = $this->builderMonthlyReport();
        $fileName = Str::slug(trans('resource::view.Monthly recruitment report') . '_' . $data['month']);
        Excel::create($fileName, function ($excel) use ($data) {
            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->loadView('resource::recruit.export.monthly_report', $data);
            });
        })->download('xlsx');
    }

    /*
     * build data monthly report
     */
    public function builderMonthlyReport()
    {
        $urlFilter = route('resource::monthly_report.recruit.index') . '/';
        $month = Form::getFilterData('except', 'month', $urlFilter);
        $month = $month ? $month : Carbon::now()->format('Y-m');
        $recruiters = Team::getEmpsByTeamInPeriodTime(Team::TEAM_TYPE_HR, null, "{$month}-01", null)->get();
        $selectedFields = ['id', 'name', 'color'];
        $objChannel = Channels::getInstance();
        $allChannels = $objChannel->getChannelsByIds(null, $selectedFields);
        if (!isset($_COOKIE[md5('filter.' . $urlFilter)])) {
            $selectedChannels = $allChannels;
            $filterChannelIds = $allChannels->pluck('id')->toArray();
        } else {
            $filterChannelIds = Form::getFilterData('except', 'channelIds', $urlFilter);
            $filterChannelIds = is_array($filterChannelIds) ? $filterChannelIds : [];
            $selectedChannels = $objChannel->getChannelsByIds($filterChannelIds, $selectedFields);
        }
        $collectionChannels = $objChannel->getMonthlyReportList($filterChannelIds, $month);
        $groupChannels = getOptions::getInstance()->beforeRenderViewMonthlyReport($collectionChannels, $recruiters, $month);

        return [
            'recruiters' => $recruiters,
            'allChannels' => $allChannels,
            'selectedChannels' => $selectedChannels,
            'groupChannels' => $groupChannels,
            'filterChannelIds' => $filterChannelIds,
            'month' => $month,
        ];
    }
}
