<?php

namespace Rikkei\Project\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectApprovedProductionCost;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Core\View\View;
use Rikkei\Project\Model\ProjectWatch;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\RiskAction;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Project\View\View as ViewProject;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\ProjDeliverable;
use Illuminate\Support\Facades\Validator;
use Rikkei\Project\Model\Task;
use Illuminate\Support\Facades\Lang;
use Rikkei\Project\Model\ProjPointReport;
use Carbon\Carbon;
use Rikkei\Project\Model\ProjPointBaseline;
use Rikkei\Core\View\CookieCore;
use Rikkei\Project\Model\SourceServer;
use Illuminate\Support\Facades\View as ViewLaravel;
use Rikkei\Project\View\GeneralProject;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Rikkei\Project\Model\DashboardLog;
use Exception;
use Illuminate\Support\Facades\Log;
use Rikkei\Project\Model\TaskNcmRequest;
use Rikkei\Project\Model\ProjPointFlat;
use Rikkei\Resource\Model\Programs;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\ProjRewardBudget;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Ot\Model\OtRegister;

class PointController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('project', 'project/dashboard');
    }

    /**
     * Display dashboard page
     * @param false|string $isWatch
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index($isWatch = false)
    {
        $weekList = ViewProject::getWeekList();
        if (!Input::get('bl')) {
            if (GeneralProject::isRedirectBLInDb() &&
                isset($weekList['list']) && count($weekList['list'])
            ) {
                $prevBaseline = reset($weekList['list']);
                if (isset($prevBaseline['value']) && $prevBaseline['value']) {
                    return redirect()->route('project::point.baseline',
                        ['slug' => $prevBaseline['value']]);
                }
            }
        }
        $urlSubmitFilter = trim(URL::route('project::dashboard'), '/') . '/';
        if ($isWatch && $isWatch == 'watch-list') $urlSubmitFilter = trim($urlSubmitFilter, '/') . '?' . $isWatch . '/';
        $isTypeFilter = Form::getFilterData('exception', 'proj_type');
        $filterAll = (array) Form::getFilterData(null, null, $urlSubmitFilter);
        if (!$isTypeFilter && !$isWatch) {
            // Set default types filter
            $typeFilter = [
                'in' => [
                    'projs.type' => [
                        Project::TYPE_OSDC,
                        Project::TYPE_BASE,
                        Project::TYPE_ONSITE,
                    ]
                ]
            ];
            $flagTypeFilter = [
                'exception' => [
                    'proj_type' => 1
                ]
            ];
            $filterAll = array_merge_recursive($filterAll, $typeFilter, $flagTypeFilter);
            CookieCore::setRaw('filter.' . $urlSubmitFilter, $filterAll);
            return redirect()->away(app('request')->fullUrl());
        }
        CookieCore::forget('tab-keep-status-project-dashboard');
        CookieCore::forget('tab-keep-status-project-workorder');
        CookieCore::forget('tab-keep-status-workorder');
        Breadcrumb::add('Project Dashboard');
        $status = Project::lablelState();
        $types = Project::labelTypeProject();

        $collection = Project::getGridData(null, null, $isWatch);
        $collection = Project::gridDataFilter($collection);
        return view('project::point.tab-index', [
            'collectionModel' => $collection,
            'status' => $status,
            'weekList' => $weekList,
            'viewBaseline' => false,
            'allColorStatus' => ViewProject::getPointColor(),
            'types' => $types,
            'isWatch' => $isWatch,
            'isAllowRaise' => true
        ]);
    }

    /**
     * Display dashboard page
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $project = Project::find($id);
        $notFound = false;
        if (!$project) {
            $notFound = true;
        } else if ($project->status != Project::STATUS_APPROVED) {
            $notFound= true;
        }
        if ($notFound) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        // check permission view
        if (!ViewProject::isAccessViewProject($project)) {
            View::viewErrorPermission();
        }

        if ($project->isLong()) {
            $budgetData = ProjRewardBudget::getRewardBudgets($project->id);
        } else {
            $budgetData = null;
        }
        //check permission edit
        $permissionEditPoint = false;
        $permissionEditPointPM = false;
        $permissionEditPointQA = false;
        $permissionEditNote = false;
        $permissionEditSubPM = false;
        $permissionEditSale = false;
        $isReviewBudget = false;
        $isApproveBudget = false;
        $isCoo = Permission::getInstance()->isCOOAccount();
        $currentUser = Permission::getInstance()->getEmployee();
        $isLeader = $currentUser->id == $project->leader_id;
        if ($project->isOpen()) {
            $accessProject = ViewProject::checkPermissionEditWorkorder($project,
                'project::point.save');
            $permissionEditPoint = $accessProject['permissionEidt'];
            $permissionEditPointPM = $accessProject['persissionEditPM'];
            $permissionEditPointQA = $accessProject['permissionEditQA'];
            $permissionEditNote = $accessProject['permissionEditNote'];
            $permissionEditSubPM = $accessProject['permissionEditSubPM'];
            $permissionEditSale = $accessProject['permissionEditSale'];;
        }
        if (!$project->canChangeDashboard()) {
            $accessProject = ViewProject::checkPermissionEditWorkorder($project,
                'project::point.save');
            $permissionEditPointPM = false;
            $permissionEditSubPM = false;
            $permissionEditPoint = $permissionEditPoint ||
                    $permissionEditPointPM ||
                    $permissionEditSubPM;
            $permissionEditNote = $accessProject['permissionEditNote'];
        }
        $projectPoint = ProjectPoint::findFromProject($id);
        $projectMeta = $project->getProjectMeta();
        $isSyncSource = SourceServer::getSyncSourceServer($id);
        $delivers = null;
        $cssTasksCC = null;
        $procTaskCompliance = null;
        $procReportNumber = $projectPoint->getProcReportNumber();
        $isProjectSync = SourceServer::getSyncSourceServer($id);
        $checkCate = in_array($project->category_id, [Project::CATEGORY_DEVELOPMENT, Project::CATEGORY_MAINTENANCE]);
        $otDay = round(OtRegister::getInstance()->getOtTimeOfProject($project->id) / CoreConfigData::get('project.mm'), 2);
        $projectPointInformation = ViewProject::getProjectPointInfo(
                $project,
                $projectPoint,
                $projectMeta,
                $delivers,
                $cssTasksCC,
                $procTaskCompliance,
                $procReportNumber,
                $otDay,
                ['content_color' => 1]
            );
        $totalFlatResourceOfDev = ProjectMember::getTotalFlatResourceOfDev($id);
        $costProductivityProgLang = false;
        $costProductivityProgLangIds = [];
        if ($projectPoint->cost_productivity_proglang) {
            $costProductivityProgLang = json_decode($projectPoint->cost_productivity_proglang, true);
        }
        if (!$projectPoint) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        if (!$projectMeta->is_show_reward_budget
                || $projectMeta->is_show_reward_budget == ProjectMeta::REWARD_BUGGET_SUBMIT
        ) {
            $isReviewBudget = true;
        } else {
            $isApproveBudget = true;

        }
        $allPm = ProjectMember::getAllPmIdOfProject($id);
        $isPm = in_array($currentUser->id, $allPm);
        Breadcrumb::add('Project Dashboard', route('project::dashboard'));
        Breadcrumb::add(Lang::get('project::view.Project Report'));
        Menu::setFlagActive('project');
        $cssResult = new CssResult();
        $timeCloseProject = false;
        if (!empty($project->noticeToClose()) || in_array(intval($project['state']), [4 ,5])) {
            $timeCloseProject = true;
        }
        return view('project::point.edit', [
            'project' => $project,
            'projectPoint' => $projectPoint,
            'projectPointInformation' => $projectPointInformation,
            'permissionEditPP' => [
                'pm' => $permissionEditPointPM,
                'qa' => $permissionEditPointQA,
                'general' => $permissionEditPoint,
                'note' => $permissionEditNote,
                'note_bl' => $permissionEditNote,
                'subPM' => $permissionEditSubPM,
                'sale' => $permissionEditSale,
            ],
            'isSyncSource' => $isSyncSource,
            'procTaskCompliance' => $procTaskCompliance,
            'viewBaseline' => false,
            'evaluationLabel' => ProjectPoint::evaluationLabel(),
            'allColorStatus' => ViewProject::getPointColor(),
            'weekList' => ViewProject::getWeekListBaselineDetail($project),
            'pointEdit' => true,
            'projectMeta' => $projectMeta,
            'pmActive' => $project->getPMActive(),
            'isProjectSync' => $isProjectSync,
            'dashboardLogs' => DashboardLog::getAllLogs($id),
            'isApproveBudget' => $isApproveBudget,
            'isReviewBudget' => $isReviewBudget,
            'isLeader' => $isLeader,
            'isCoo' => $isCoo,
            'isPm' => $isPm,
            'totalFlatResourceOfDev' => $totalFlatResourceOfDev,
            'costProductivityProgLang' => $costProductivityProgLang,
            'effortDevCurrent' => ProjectMember::getTotalEffortMemberApproved(null, $project->id),
            'budgetData' => $budgetData,
            'cssResults' => $cssResult->cssResultsOfProject($id),
            'timeCloseProject' => $timeCloseProject,
            'otTime' => $otDay,
            'checkCate' => $checkCate
        ]);
    }

    /**
     * save point of project
     *
     * @param int $id
     */
    public function save($id, $type = null, $reportLastDate = false)
    {
        $project = Project::find($id);
        $projectPoint = ProjectPoint::findFromProject($id);
        $response = [];
        if (!$project || !$projectPoint) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return $response;
        }
        if (!$project->isOpen()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return $response;
        }
        $permissionClass = Permission::getInstance();
        //check permission edit
        $permissionEditPoint = false;
        $permissionEditPointPM = false;
        $permissionEditPointQA = false;
        $permissionEditSubPM = false;

        if ($project->isOpen()) {
            $accessProject = ViewProject::checkPermissionEditWorkorder($project, 'project::point.save');
            $permissionEditPoint = $accessProject['permissionEidt'];
            $permissionEditPointPM = $accessProject['persissionEditPM'];
            $permissionEditPointQA = $accessProject['permissionEditQA'];
            $permissionEditSubPM = $accessProject['permissionEditSubPM'];

        }
        $dataInput = Input::get();
        if (!$dataInput) {
            $response['success'] = 1;
            if ($type) {
                return $response;
            }
            return $response;
        }
        unset($dataInput['_token']);
        unset($dataInput['report_last_at']);
        unset($dataInput['raise']);
        $isSyncSource = SourceServer::getSyncSourceServer($id);
        if ($isSyncSource['redmine']) {
            if (isset($dataInput['qua_leakage_errors'])) {
                unset($dataInput['qua_leakage_errors']);
            }
            if (isset($dataInput['qua_defect_errors'])) {
                unset($dataInput['qua_defect_errors']);
            }
        }
        unset($dataInput['date_updated_css']);
        $permissionField = $this->permissionField();
        foreach ($dataInput as $key => $value) {
            if (in_array($key, $permissionField[ProjectMember::TYPE_COO]) &&
                !$permissionClass->isCOOAccount()
            ) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.You don\'t have access');
                return $response;
            }
            if ((in_array($key, $permissionField[ProjectMember::TYPE_PM]) &&
                    !$permissionEditPointPM)
                && (in_array($key, $permissionField[ProjectMember::TYPE_SUBPM]) &&
                    !$permissionEditSubPM)
            ) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.You don\'t have access');
                return $response;
            }
            if ($dataInput[$key] == '') {
                $dataInput[$key]  = null;
            }
            if ($key == 'css_css') {
                $dataInput['date_updated_css'] = Carbon::now()->format('Y-m-d H:i:s');
            }
        }
        $validation = $this->validationInput($dataInput, $projectPoint);
        if (!$validation) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            if ($type) {
                return $response;
            }
            return $response;
        }
        // get old point
        $projectPointOld = $projectPoint->getTotalPoint();
        $cssPointOld = $projectPoint->getCssCs();
        if (isset($dataInput['cost_productivity_proglang']) && $dataInput['cost_productivity_proglang']) {
            /* update project meta */
            if (count($dataInput['cost_productivity_proglang']) == 1) {
                $productivity = reset($dataInput['cost_productivity_proglang']);
                $projectMeta = $project->projectMeta;
                if (isset($productivity['loc_current'])) {
                    $projectMeta->lineofcode_current = $productivity['loc_current'];
                }
                if (isset($productivity['loc_baseline'])) {
                    $projectMeta->lineofcode_baseline = $productivity['loc_baseline'];
                }
                $projectMeta->save();
            }
            /* end update*/
            $dataInput['cost_productivity_proglang'] = json_encode($dataInput['cost_productivity_proglang']);
        }
        if (!$project->isTypeTrainingOfRD()) {
            $dataSave = $dataInput;
            if (isset($dataSave['color'])) {
                unset($dataSave['color']);
            }
            $projectPoint->setData($dataSave);
        } else {
            if (isset($dataInput['css_css'])) {
                $projectPoint->css_css = $dataInput['css_css'];
            }
        }
        if ($reportLastDate) {
            $projectPoint->setTimeStamps($project);
        }
        $otDay = round(OtRegister::getInstance()->getOtTimeOfProject($project->id) / CoreConfigData::get('project.mm'), 2);
        $projectPointInformation = ViewProject::getProjectPointInfo(
                $project,
                $projectPoint,
                null, null, null, null, null, $otDay,
                [
                    'not_use_cache' => 1,
                    'content_color' => 1
                ]
        );
        $response['success'] = 1;
        $response['data'] = $this->calculatorPointDataDom($projectPointInformation);
        $response['color'] = $this->calculatorColorStatus($projectPointInformation);
        $response['input'] = $this->calculatorInput($projectPointInformation);
        $response['content_color'] = $this->calculatorContentColor($projectPointInformation);
        $projectPoint->setTimeStamps($project);
        $projectPoint->save([], [
            'projectPointInformation' => $projectPointInformation,
            'project' => $project,
            'dataInput' => $dataInput
        ]);

        //update ME
        /*$cssPointNew = $projectPointInformation['css_css'];
        if ($cssPointOld != $cssPointNew) {
            MeEvaluation::updateProjectPointChange($project, $projectPointInformation['point_total'], [
                'project_point_old' => $projectPointOld,
                'css_point_old' => $cssPointOld,
                'css_point_new' => $cssPointNew,
                'is_coo_update' => true
            ]);
        }*/
        if ($type) {
            return $response;
        }
        return $response;
    }

    /**
     * @return array
     */
    public function getPoint(){
        $data = $_GET;
        if (empty($data['id']) || !is_numeric($data['id'])) {
            return [
                'status' => false,
                'msg' => Lang::get('project::message.Error input data!')
            ];
        }
        $project = Project::find(intval($data['id']));
        if (empty($project)) {
            return [
                'status' => false,
                'msg' => Lang::get('project::message.Not found item.')
            ];
        }
        if (empty($data['column'])) {
            return [
                'status' => false,
                'msg' => Lang::get('project::message.Error input data!')
            ];
        }
        $firstLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endLastWeek = Carbon::now()->subWeek()->endOfWeek();
        $projectBlLast = ProjPointBaseline::where([
            ['created_at', '>=', $firstLastWeek],
            ['created_at', '<=', $endLastWeek],
            ['project_id', $project->id],
        ])->first();
        if (!empty($projectBlLast)) {
            return [
                'status' =>  true,
                'msg' => !empty($projectBlLast[$data['column']]) ? $projectBlLast[$data['column']] : ''
            ];
        } else {
            return [
                'status' => false,
                'msg' => Lang::get('project::message.Not found item.')
            ];
        }
    }

    /**
     * caculator point of project follow dom html
     *
     * @param array $projectPointInformation
     * @return array
     */
    protected function calculatorPointDataDom($projectPointInformation)
    {
        $evaluationLabel = ProjectPoint::evaluationLabel();

        return [
            '.cost_billable_effort' => $projectPointInformation['cost_billable_effort'],
            '.cost_plan_effort_total' => $projectPointInformation['cost_plan_effort_total'],
            '.cost_resource_allocation_total' => $projectPointInformation['cost_resource_allocation_total'],
            '.cost_resource_allocation_current' => $projectPointInformation['cost_resource_allocation_current'],
            '.cost_effort_efftiveness' => $projectPointInformation['cost_effort_effectiveness'],
            '.cost_effort_efftiveness_point' => $projectPointInformation['cost_effort_effectiveness_point'],
            '.cost_busy_rate' => $projectPointInformation['cost_busy_rate'],
            '.cost_busy_rate_point' => $projectPointInformation['cost_busy_rate_point'],
            '.cost_effort_efficiency2' => $projectPointInformation['cost_effort_efficiency2'],
            '.cost_effort_efficiency2_point' => $projectPointInformation['cost_effort_efficiency2_point'],
            '.cost_productivity' => $projectPointInformation['cost_productivity'],
            '.cost_plan_effort_current' => $projectPointInformation['cost_plan_effort_current'],

            '.proc_compliance' => $projectPointInformation['proc_compliance'],
            '.proc_compliance_point' => $projectPointInformation['proc_compliance_point'],
            '.proc_report_point' => $projectPointInformation['proc_report_point'],
            '.proc_report' => $projectPointInformation['proc_report'],
            '.proc_report_yes' => $projectPointInformation['proc_report_yes'],
            '.proc_report_delayed' => $projectPointInformation['proc_report_delayed'],
            '.proc_report_no' => $projectPointInformation['proc_report_no'],

            '.tl_schedule' => $projectPointInformation['tl_schedule'],
            '.tl_schedule_point' => $projectPointInformation['tl_schedule_point'],
            '.tl_deliver' => $projectPointInformation['tl_deliver'],
            '.tl_deliver_point' => $projectPointInformation['tl_deliver_point'],

            '.qua_leakage' => $projectPointInformation['qua_leakage'],
            '.qua_leakage_point' => $projectPointInformation['qua_leakage_point'],
            '.qua_defect' => $projectPointInformation['qua_defect'],
            '.qua_defect_point' => $projectPointInformation['qua_defect_point'],

            '.css_css' => $projectPointInformation['css_css'],
            '.css_cs_point' => $projectPointInformation['css_css_point'],
            '.css_ci' => $projectPointInformation['css_ci'],
            '.css_ci_point' => $projectPointInformation['css_ci_point'],
            '.css_ci_negative' => $projectPointInformation['css_ci_negative'],
            '.css_ci_positive' => $projectPointInformation['css_ci_positive'],

            '.project_total_point' => $projectPointInformation['point_total'],
            '.project_evaluation' => $evaluationLabel[$projectPointInformation['project_evaluation']],
        ];
    }

    /**
     * calculator of all status point
     *
     * @param array $projectPointInformation
     * @return array
     */
    protected function calculatorColorStatus($projectPointInformation)
    {
        $allColorStatus = ViewProject::getPointColor();
        return [
            '.cost_color' => $allColorStatus[$projectPointInformation['cost']],
            '.proc_color' => $allColorStatus[$projectPointInformation['proc']],
            '.tl_color' => $allColorStatus[$projectPointInformation['tl']],
            '.qua_color' => $allColorStatus[$projectPointInformation['quality']],
            '.css_color' => $allColorStatus[$projectPointInformation['css']],
            '.summary_color' => $allColorStatus[$projectPointInformation['summary']],
            'summary_point_color' => $allColorStatus[$projectPointInformation['summary']]
        ];
    }

    /**
     * get all input of project point
     *
     * @param array $projectPointInformation
     */
    protected function calculatorInput($projectPointInformation)
    {
        return [
            'cost_plan_effort_current' =>
                $projectPointInformation['cost_plan_effort_current'] !== null ?
                round($projectPointInformation['cost_plan_effort_current'], 2) : null,
            'cost_actual_effort' => $projectPointInformation['cost_actual_effort'] !== null ?
                round($projectPointInformation['cost_actual_effort'], 2) : null,
            'qua_leakage_errors' => $projectPointInformation['qua_leakage_errors'],
            'qua_defect_errors' => $projectPointInformation['qua_defect_errors'],
            'tl_schedule' => $projectPointInformation['tl_schedule'] !== null ?
                round($projectPointInformation['tl_schedule'], 2) : null,
            'css_css' => $projectPointInformation['css_css'] !== null ?
                round($projectPointInformation['css_css'], 2) : null
        ];
    }

    /**
     * get all input of content color
     * @param type $projectPointInformation
     * @return array projectPointInformation css class content color
     */
    protected function calculatorContentColor($projectPointInformation) {
        return [
            '.cost_effort_effectiveness_point' => $projectPointInformation['cost_effort_effectiveness_color'],
            '.cost_busy_rate_point' => $projectPointInformation['cost_busy_rate_color'],
            '.qua_leakage_point' => $projectPointInformation['qua_leakage_color'],
            '.qua_defect_point' => $projectPointInformation['qua_defect_color'],
            '.tl_schedule_point' => $projectPointInformation['tl_schedule_color'],
            '.tl_deliver_point' => $projectPointInformation['tl_deliver_color'],
            '.proc_compliance_point' => $projectPointInformation['proc_compliance_color'],
            '.proc_report_point' => $projectPointInformation['proc_report_color'],
            '.css_cs_point' => $projectPointInformation['css_css_color'],
            '.css_ci_point' => $projectPointInformation['css_ci_color']
        ];
    }


    /**
     * field permission
     *
     * @return type
     */
    protected function permissionField()
    {
        return [
            //fields PM have access edit
            ProjectMember::TYPE_PM => [
                'cost_actual_effort',
                'cost_plan_effort_current',

                'qua_leakage_errors',
                'qua_defect_errors',

                'tl_schedule'
            ],
            ProjectMember::TYPE_SUBPM => [
                'cost_actual_effort',
                'cost_plan_effort_current',

                'qua_leakage_errors',
                'qua_defect_errors',

                'tl_schedule'
            ],
            ProjectMember::TYPE_COO => [
                'css_css'
            ]
        ];
    }

    /**
     * Update note for point project
     *
     * @param int $id
     */
    public function updateNote($id)
    {
        $project = Project::find($id);
        // $baselineNote = Input::get('data.bl_summary_note');
        if (Input::get('baselineId')) {
            // only allow update note summay in baseline
            /*if ($baselineNote === null) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.You don\'t have access');
                return response()->json($response);
            }*/
            $projectPoint = ProjPointBaseline::find(Input::get('baselineId'));
        } else {
            $projectPoint = ProjectPoint::findFromProject($id);
        }
        $response = [];
        if (!$project || !$projectPoint) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (!$project->isOpen()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.The project is closed, you don\'t have permission to edit');
            return response()->json($response);
        }
        //check permission edit
        $permissionEditPoint = false;
        if (Permission::getInstance()->isScopeCompany(null, 'project::point.save')) {
            $permissionEditPoint = true;
        } elseif (Permission::getInstance()->isScopeTeam(null, 'project::point.save')) {
            $teamsProject = $project->getTeamIds();
            $teamsEmployee = Permission::getInstance()->getTeams();
            $intersect = array_intersect($teamsEmployee, $teamsProject);
            if (count($intersect)) {
                $permissionEditPoint = true;
            }
        }
        if (!$permissionEditPoint){ //edit self project
            $members = $project->getMemberTypes();
            $employeeCurrent = Permission::getInstance()->getEmployee();
            if (isset($members[ProjectMember::TYPE_PM]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PM])
            ) {
                $permissionEditPoint = true;
            } elseif (
                (isset($members[ProjectMember::TYPE_PQA]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA])) ||
                (isset($members[ProjectMember::TYPE_SQA]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SQA]))
            ) {
                $permissionEditPoint = true;
            } elseif (isset($members[ProjectMember::TYPE_SUBPM]) &&
                in_array($employeeCurrent->id, $members[ProjectMember::TYPE_SUBPM])
            ) {
                $permissionEditPoint = true;
            }
        }
        if (!$permissionEditPoint) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        // note baseline
        /*if ($baselineNote !== null) {
            $key = 'bl_summary_note';
            $value = e($baselineNote);
            $dataInput[$key] = $value;
            $response['data'][$key] = $value;
            $response['disableTooltip'][$key] = ViewProject::isLtLength($value);
        } else { // note report*/
        $dataInput = Input::get('data');
        if (!$dataInput) {
            $response['success'] = 1;
            return response()->json($response);
        }
        $response['data'] = [];
        foreach ($dataInput as $key => $value) {
            $value = trim($dataInput[$key]);
            if ($value == '') {
                $dataInput[$key] = null;
            } else {
                $value = e($value);
                $dataInput[$key] = $value;
                $response['data'][$key] = $value;
                $response['disableTooltip'][$key] = ViewProject::isLtLength($value);
            }
        }
        $projectPoint->setData($dataInput);
        $projectPoint->save([], [
            'is_change_color' => false
        ]);
        $response['success'] = 1;
        return response()->json($response);
    }

    /**
     * validation input point
     *
     * @param array $dataInput
     * @param object $projectPoint
     * @return boolean
     */
    protected function validationInput($dataInput, $projectPoint)
    {
        $keyDataInput = array_keys($dataInput);
        if (in_array('cost_plan_effort_current', $keyDataInput)) {
            $costPlanEffortTotal = $projectPoint->getCostPlanEffortTotal();
            $costPlanValidation = '|between:0,' . $costPlanEffortTotal;
        } else {
            $costPlanValidation = '';
        }
        if (in_array('cost_resource_allocation_current', $keyDataInput)) {
            $costResource = $projectPoint->getCostResourceAllocationTotal();
            $costResourceValidation = '|between:0,' . $costResource;
        } else {
            $costResourceValidation = '';
        }
        // validate is number at productive programming
        if (isset($dataInput['cost_productivity_proglang']) && $dataInput['cost_productivity_proglang']) {
            foreach ($dataInput['cost_productivity_proglang'] as $value) {
                if (isset($value['loc_baseline']) && $value['loc_baseline']) {
                    if (!is_numeric($value['loc_baseline'])) {
                        return false;
                    }
                }
                if (isset($value['loc_current']) && $value['loc_current']) {
                    if (!is_numeric($value['loc_current'])) {
                        return false;
                    }
                }
                if (isset($value['dev_effort']) && $value['dev_effort']) {
                    if (!is_numeric($value['dev_effort'])) {
                        return false;
                    }
                }
            }
        }
        $validator = Validator::make($dataInput, [
            'cost_plan_effort_current' => 'numeric' . $costPlanValidation,
            'cost_resource_allocation_current' => 'numeric'.$costResourceValidation,
            'cost_actual_effort' => 'numeric|min:0',

            'proc_compliance' => 'integer|min:0',
            'proc_report_yes' => 'integer|min:0',
            'proc_report_no' => 'integer|min:0',
            'proc_report_delayed' => 'integer|min:0',

            'tl_schedule' => 'numeric|min:0',

            'qua_leakage_errors' => 'integer|min:0',
            'qua_defect_errors' => 'integer|min:0',
            'qua_defect_reward_errors' => 'integer|min:0'
        ]);
        if ($validator->fails()) {
            return false;
        }
        return true;
    }

    /**
     * Display dashboard baseline page
     */
    public function baseline($slug, $isWatch = false)
    {
        CookieCore::forget('tab-keep-status-project-dashboard-baseline');
        CookieCore::forget('tab-keep-status-project-workorder');
        CookieCore::forget('tab-keep-status-workorder');
        list($year, $week) = explode('-', $slug);
        $week = (int) $week;
        $now = Carbon::parse();
        $firstDay = clone $now;
        $lastDay = clone $now;
        $firstWeek = $firstDay->setISODate($year, $week, 1)->setTime(0,0,0);
        $lastWeek = $lastDay->setISODate($year, $week, 7)->setTime(23,59,59);
        Breadcrumb::add('Project Dashboard Baseline');
        $status = Project::lablelState();
        $types = Project::labelTypeProject();
        $urlSubmitFilter = trim(URL::route('project::dashboard'), '/') . '/';
        if ($isWatch) $urlSubmitFilter = trim($urlSubmitFilter, '/') . '?' . $isWatch . '/';
        $collection = Project::getGridData($firstWeek, $lastWeek);
        $collection  = Project::gridDataFilter($collection);
        return view('project::point.tab-index', [
            'collectionModel' => $collection,
            'status' => $status,
            'weekList' => ViewProject::getWeekList($firstWeek),
            'viewBaseline' => true,
            'allColorStatus' => ViewProject::getPointColor(),
            'types' => $types,
            'isWatch' => $isWatch,
            'isAllowRaise' => true
        ]);
    }

    /**
     * Display baseline detail
     */
    public function baselineDetail($id)
    {
        $projectPoint = ProjPointBaseline::find($id);
        if (!$projectPoint) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $project = Project::find($projectPoint->project_id);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        // check permission view
        if (!ViewProject::isAccessViewProject($project)) {
            View::viewErrorPermission();
        }
        //check permission edit
        $permissionEditPoint = false;
        $permissionEditNote = false;
        $permissionEditPointQA = false;
        $permissionEditSale = false;
        if ($project->isOpen()) {
            $accessProject = ViewProject::checkPermissionEditWorkorder($project,
                'project::point.save');
            $permissionEditPoint = $accessProject['permissionEidt'];
            $permissionEditNote = $accessProject['permissionEditNote'];
            $permissionEditPointQA = $accessProject['permissionEditQA'];
            $permissionEditSale = $accessProject['permissionEditSale'];;
        }
        if (!$project->canChangeDashboard()) {
            $accessProject = ViewProject::checkPermissionEditWorkorder($project,
                'project::point.save');
            $permissionEditPoint = false;
            $permissionEditNote = $accessProject['permissionEditNote'];
        }
        $checkCate = in_array($project->category_id, [Project::CATEGORY_DEVELOPMENT, Project::CATEGORY_MAINTENANCE]);
        $costProductivityProgLang = false;
        $costProductivityProgLangIds = [];
        if ($projectPoint->cost_productivity_proglang) {
            $costProductivityProgLang = json_decode($projectPoint->cost_productivity_proglang, true);
            foreach ($costProductivityProgLang as $key => $item) {
                if ($key == "") {
                    unset($costProductivityProgLang[$key]);
                }
            }
            $costProductivityProgLangIds = array_keys($costProductivityProgLang);
        }
        Breadcrumb::add('Project Dashboard Baseline');
        Breadcrumb::add('Project Baseline detail');
        Menu::setActive('project', 'project/dashboard');
        $otDay = round(OtRegister::getInstance()->getOtTimeOfProject($project->id) / CoreConfigData::get('project.mm'), 2);
        $projectPointInformation = ViewProject::getProjectPointInfo(
                $project,
                $projectPoint,
                null, null, null, null, null, $otDay,
                ['content_color' => 1]
            );
        $cssResult = new CssResult();
        return view('project::point.edit', [
            'project' => $project,
            'projectPoint' => $projectPoint,
            'projectPointInformation' => $projectPointInformation,
            'viewBaseline' => true,
            'viewNote' => true,
            'permissionEditPP' => [
                'pm' => false,
                'qa' => $permissionEditPointQA,
                'general' => $permissionEditPoint,
                'note' => $permissionEditNote,
                'note_bl' => $permissionEditNote,
                'subPM' => false,
                'sale' => $permissionEditSale,
            ],
            'delivers' => null,
            'isSyncSource' => null,
            'cssTasksCC' => null,
            'procTaskCompliance' => null,
            'collectionModel' => null,
            'reportList' => null,
            'evaluationLabel' => ProjectPoint::evaluationLabel(),
            'allColorStatus' => ViewProject::getPointColor(),
            'weekList' => ViewProject::getWeekListBaselineDetail($project, $projectPoint),
            'pointEdit' => false,
            'pmActive' => $project->getPMActive(),
            'dashboardLogs' => DashboardLog::getAllLogs($projectPoint->project_id),
            'programLang' => Programs::getProgLangByIds($costProductivityProgLangIds),
            'costProductivityProgLang' => $costProductivityProgLang,
            'cssResults' => $cssResult->cssResultsOfProject($id),
            'timeCloseProject' => true,
            'otTime' => $otDay,
            'checkCate' => $checkCate
        ]);
    }

    /**
     * baseline from detail
     */
    public function baselineDetailSlug($id, $slug)
    {
        list($year, $week) = explode('-', $slug);
        $now = Carbon::parse();
        $firstDay = clone $now;
        $lastDay = clone $now;
        $firstWeek = $firstDay->setISODate($year, $week, 1)->setTime(0,0,0);
        $lastWeek = $lastDay->setISODate($year, $week, 7)->setTime(23,59,59);
        $baselineItem = ProjPointBaseline::getItemInWeek($firstWeek, $lastWeek);
        if (!$baselineItem) {
            return $this->baselineDetail($baselineItem->id, false);
        }
        return $this->baselineDetail($baselineItem->id, $baselineItem);
    }

    /**
     * export dashboard
     */
    public function export($ids = null)
    {
        if ($ids === null) {
            return redirect()->route('project::dashboard');
        }
        $ids = explode('-', $ids);
        if (!count($ids)) {
            return redirect()->route('project::dashboard');
        }
        $projects = [];
        foreach ($ids as $projectId) {
            if (!$projectId) {
                continue;
            }
            $projectId = (int) $projectId;
            $project = Project::find($projectId);
            if (!$project) {
                continue;
            }
            $projectPoint = ProjectPoint::findFromProject($projectId);
            if (!$projectPoint) {
                continue;
            }
            // check permission view
            if (!ViewProject::isAccessViewProject($project)) {
                continue;
            }
            $projectPointInformation = ViewProject::getProjectPointInfo(
                    $project,
                    $projectPoint,
                    null, null, null, null, null,
                    ['content_color' => 1]
                );
            $projects[] = [
                'project' => $project,
                'projectPoint' => $projectPoint,
                'projectPointInformation' => $projectPointInformation,
            ];
        }
        if (!count($projects)) {
            return redirect()->route('project::dashboard');
        }
        return view('project::point.export', [
            'projects' => $projects,
            'export' => true,
            'viewBaseline' => true,
            'permissionEditPP' => [
                'pm' => false,
                'qa' => false,
                'general' => false,
                'subPM' => false,
            ],
            'delivers' => false,
            'isSyncSource' => false,
            'cssTasksCC' => false,
            'procTaskCompliance' => false,
            'collectionModel' => false,
            'reportList' => false,
            'evaluationLabel' => ProjectPoint::evaluationLabel(),
            'allColorStatus' => ViewProject::getPointColor(),
            'viewNote' => false
        ]);
    }

    /**
     * export dashboard
     */
    public function exportBaseline($ids = null)
    {
        if ($ids === null) {
            return redirect()->route('project::dashboard');
        }
        $ids = explode('-', $ids);
        if (!count($ids)) {
            return redirect()->route('project::dashboard');
        }
        $projects = [];
        foreach ($ids as $baselineId) {
            if (!$baselineId) {
                continue;
            }
            $baselineId = (int) $baselineId;
            $baseline = ProjPointBaseline::find($baselineId);
            if (!$baseline) {
                continue;
            }
            $project = Project::find($baseline->project_id);
            if (!$project) {
                continue;
            }
            // check permission view
            if (!ViewProject::isAccessViewProject($project)) {
                continue;
            }
            $projectPointInformation = ViewProject::getProjectPointInfo(
                    $project,
                    $baseline
                );
            $projects[] = [
                'project' => $project,
                'projectPoint' => $baseline,
                'projectPointInformation' => $projectPointInformation,
            ];
        }
        if (!count($projects)) {
            return redirect()->route('project::dashboard');
        }
        return view('project::point.export', [
            'projects' => $projects,
            'export' => true,
            'viewBaseline' => true,
            'permissionEditPP' => [
                'pm' => false,
                'qa' => false,
                'general' => false,
                'subPM' => false,
            ],
            'delivers' => false,
            'isSyncSource' => false,
            'cssTasksCC' => false,
            'procTaskCompliance' => false,
            'collectionModel' => false,
            'reportList' => false,
            'evaluationLabel' => ProjectPoint::evaluationLabel(),
            'allColorStatus' => ViewProject::getPointColor()
        ]);
    }

    /**
     * init point ajax
     *  get task list, get css value
     */
    public function initAjax($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $project = Project::find($id);
        $response = [];
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }

        if (!ViewProject::isAccessViewProject($project)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        $templateDefault = 'project::task.ajax.task_list_popup';
        $response['html_task']['.task-list-ajax[data-type="' . Task::TYPE_ISSUE . '"]'] =
            ViewLaravel::make('project::point.tab.task_list_summary', [
            'collectionModel' => Task::getList($id, null, null, true),
            'taskPriorities' => Task::priorityLabel(),
            'taskStatus' => Task::getStatusTypeNormal(),
            'project' => $project,
        ])->render();
        $response['html_task']['.task-list-ajax[data-type="' . Task::TYPE_ISSUE_COST . '"]'] =
            ViewLaravel::make($templateDefault, [
            'collectionModel' => Task::getList($id, Task::TYPE_ISSUE_COST),
            'taskPriorities' => Task::priorityLabel(),
            'taskStatus' => Task::getStatusTypeNormal(),
        ])->render();
        $response['html_task']['.task-list-ajax[data-type="' . Task::TYPE_ISSUE_QUA . '"]'] =
            ViewLaravel::make($templateDefault, [
            'collectionModel' => Task::getList($id, Task::TYPE_ISSUE_QUA),
            'taskPriorities' => Task::priorityLabel(),
            'taskStatus' => Task::getStatusTypeNormal(),
        ])->render();
        $response['html_task']['.task-list-ajax[data-type="' . Task::TYPE_ISSUE_TL . '"]'] =
            ViewLaravel::make($templateDefault, [
            'collectionModel' => Task::getList($id, Task::TYPE_ISSUE_TL),
            'taskPriorities' => Task::priorityLabel(),
            'taskStatus' => Task::getStatusTypeNormal(),
        ])->render();
        $response['html_task']['.task-list-ajax[data-type="' . Task::TYPE_ISSUE_PROC . '"]'] =
            ViewLaravel::make('project::task.ajax.issue_list_popup', [
            'collectionModel' => Task::getList($id, Task::TYPE_ISSUE_PROC),
            'taskPriorities' => Task::priorityLabel(),
            'taskStatus' => Task::getStatusTypeNormal(),
        ])->render();
        $response['html_task']['.task-list-ajax[data-type="' . Task::TYPE_ISSUE_CSS . '"]'] =
            ViewLaravel::make($templateDefault, [
            'collectionModel' => Task::getList($id, Task::TYPE_ISSUE_CSS),
            'taskPriorities' => Task::priorityLabel(),
            'taskStatus' => Task::getStatusTypeNormal(),
        ])->render();
        $response['html_task']['.task-list-ajax[data-type="' . Task::TYPE_COMMENDED . '"]'] =
            ViewLaravel::make('project::task.ajax.task_list_customer_feedback', [
            'collectionModel' => Task::getList(
                    $id,
                    [Task::TYPE_COMMENDED, Task::TYPE_CRITICIZED],
                    ['status' => true]
            ),
            'project' => $project,
        ])->render();
        $response['html_task']['.task-list-ajax[data-type="' . Task::TYPE_OUT_REPORT . '"]'] =
            ViewLaravel::make('project::point.tab.report_list', [
            'collectionModel' => ProjPointReport::getList($id)
        ])->render();
        $response['html_task']['.task-list-ajax[data-type="' . Task::TYPE_COMPLIANCE . '"]'] =
            ViewLaravel::make('project::point.tab.task_list_nc', [
                'collectionModel' => TaskNcmRequest::getListTaskNcmAjax($id),
                'taskPriorities' => Task::priorityLabel(),
                'taskStatus' => Task::getStatusTypeNormal(),
        ])->render();
        $listStatusIssue = [Task::STATUS_NEW, Task::STATUS_REOPEN, Task::STATUS_PROCESS];
        $response['html_task']['.task-list-ajax[data-type="' . Task::TYPE_CRITICIZED . Task::TYPE_ISSUE_COST . Task::TYPE_ISSUE_QUA . Task::TYPE_ISSUE_TL . Task::TYPE_ISSUE_PROC . '"]'] =
            ViewLaravel::make('project::task.ajax.issue_list_popup', [
                'collectionModel' => Task::getList($id, Task::getTypeIssueProject(), [], null,null, $listStatusIssue),
                'taskPriorities' => Task::priorityLabel(),
                'taskStatus' => Task::getStatusWithoutClosed(),
        ])->render();

        $statusRiskList = [Risk::STATUS_HAPPEN, Risk::STATUS_OPEN, Risk::STATUS_OCCURED, Risk::STATUS_CANCELLED];
        $columnsSelect = ['proj_op_ricks.id', 'content', 'level_important', 'proj_op_ricks.type', 'owner', 'employees.email as owner_email', 'teams.name', 'proj_op_ricks.status',
            'proj_op_ricks.due_date', 'proj_op_ricks.updated_at', 'proj_op_ricks.created_at'];
        $response['html_task']['.risk-list-ajax[data-type="' . Risk::TYPE_QUALITY . Risk::TYPE_PROCESS . Risk::TYPE_COST . Risk::TYPE_DELIVERY . '"]'] =
            ViewLaravel::make('project::task.ajax.risk_list_popup', [
                'collectionModel' => Risk::getAllRisk($id, $columnsSelect, $statusRiskList),
            ])->render();

        $response['html_task']['.risk-list-ajax[data-type="' . Risk::TYPE_PROCESS . '"]'] =
            ViewLaravel::make('project::task.ajax.risk_list_popup', [
                'collectionModel' => Risk::getAllRisk($id, $columnsSelect, null, [Risk::TYPE_PROCESS]),
            ])->render();

        $accessProject = ViewProject::checkPermissionEditWorkorder($project, 'project::point.save');
        $accessProjectPm = $accessProject['persissionEditPM'];
        $accessProjectSubPm = $accessProject['permissionEditSubPM'];
        if (!$project->canChangeDashboard()) {
            $accessProjectPm = false;
            $accessProjectSubPm = false;
        }
        $response['html_task']['.task-list-ajax[data-type="' . Task::TYPE_OUT_DELIVER . '"]'] =
            ViewLaravel::make('project::point.tab.deliver_list', [
            'collectionModel' => ProjDeliverable::getApprovedItem($id),
            'changeRequestList' => ProjDeliverable::getChangeList(),
            'permissionEditPointPM' => $accessProjectPm,
            'projectIsOpen' => $project->isOpen(),
            'permissionEditSubPM' => $accessProjectSubPm,
        ])->render();

        // response dom element another
        $response['dom']['hover']['.content-wrapper .content-header h1'] =
            'Teams: ' . $project->getTeamsString();

        $projectPoint = ProjectPoint::findFromProject($id);
        $response['is_open'] = $project->isOpen();
        /*if ($response['is_open']) {
            $changed = $projectPoint->saveCssCssCheckDeliver();
            if ($changed) {
                $projectPointInformation = ViewProject::getProjectPointInfo($project, $projectPoint);
                $allColorStatus = ViewProject::getPointColor();
                $evaluationLabel = ProjectPoint::evaluationLabel();
                $response['html']['.css_css'] = $projectPointInformation['css_css'];
                $response['html']['.css_cs_point'] = $projectPointInformation['css_css_point'];
                $response['html']['.project_total_point'] = $projectPointInformation['point_total'];
                $response['html']['.project_evaluation'] = $evaluationLabel[$projectPointInformation['project_evaluation']];
                $response['color']['.css_color'] = $allColorStatus[$projectPointInformation['css']];
                $response['color']['.summary_color'] = $allColorStatus[$projectPointInformation['summary']];
                $response['input']['css_css'] = $projectPointInformation['css_css'];
            }
        }*/
        $response['success'] = 1;
        return response()->json($response);
    }

    /**
     * get list report
     */
    public function reportListAjax($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $project = Project::find($id);
        $response = [];
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (!ViewProject::isAccessViewProject($project)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        $response['html'] = ViewLaravel::make('project::point.tab.report_list', [
            'collectionModel' => ProjPointReport::getList($id)
        ])->render();
        $response['is_open'] = $project->isOpen();
        $response['success'] = 1;
        return response()->json($response);
    }

    /**
     * deliver list
     */
    public function deliverListAjax($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $project = Project::find($id);
        $response = [];
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (!ViewProject::isAccessViewProject($project)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        $accessProject = ViewProject::checkPermissionEditWorkorder($project, 'project::point.save');
        $accessProjectPM = $accessProject['persissionEditPM'];
        $accessProjectSubPm = $accessProject['permissionEditSubPM'];
        $response['html'] = ViewLaravel::make('project::point.tab.deliver_list', [
            'collectionModel' => ProjDeliverable::getApprovedItem($id),
            'permissionEditPointPM' => $accessProjectPM,
            'projectIsOpen' => $project->isOpen(),
            'permissionEditSubPM' => $accessProjectSubPm,
        ])->render();
        $response['is_open'] = $project->isOpen();
        $response['success'] = 1;
        return response()->json($response);
    }

    /**
     * Check condition to allow show warning require note when report project
     * have red field and yellow fields
     */
    public function checkReportNote($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $project = Project::find($id);
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (in_array($project->type, [Project::TYPE_ONSITE, Project::TYPE_TRAINING, Project::TYPE_RD])) {
            $response['success'] = 1;
            return response()->json($response);
        }
        $costActualEffort = Input::get('cost_actual_effort');
        $quaLeakageErrors = Input::get('qua_leakage_errors');
        $quaDefectErrors = Input::get('qua_defect_errors');
        $tlSchedule = Input::get('tl_schedule');
        $cssCss = Input::get('css_css');

        // check work order
        $yearSearch = '2020';

        $objProjCost = new ProjectApprovedProductionCost();
        $projCost = $objProjCost->getAllByProjectIdYearThan($id, $yearSearch);

        $messageWork = '';
        if (!count($projCost)) {
            $messageWork .= '<li>' . Lang::get('project::message.Work Order/Basic info/approved production cost/view detail') . '</li>';
        }
        if (!$project->kind_id) {
            $messageWork .= '<li>' . Lang::get('project::message.Work Order/Basic info/project kind') . '</li>';
        }
        if ($messageWork) {
            $response['error'] = 1;
            $link = '<a href="' . URL::route('project::project.edit', ['id' => $id]) . '" style="color:white"> <u>'. trans('ot::view.View details') . '</u></a>';
            $response['message'] = '<p>' . Lang::get('project::message.warningEmptyProject') . '</p>' . $messageWork . $link;
            return $response;
        }
        // end check work order
        $getPointByWeek = ViewProject::getWeekListBaselineDetail($project);
        $getPointByPreWeek = ProjPointBaseline::find($getPointByWeek[0]['id']);

        $warningFieldNames = '';
        $warningFieldNames .= ViewProject::checkCostTab($id, $costActualEffort, $getPointByPreWeek);
        $warningFieldNames .= ViewProject::checkQualityTab($id, $quaLeakageErrors, $quaDefectErrors, $getPointByPreWeek);
        $warningFieldNames .= ViewProject::checkTimelineTab($id, $tlSchedule, $getPointByPreWeek);
        $warningFieldNames .= ViewProject::checkCssTab($id, $cssCss, $getPointByPreWeek);

        if ($warningFieldNames) {
            $response['error'] = 1;
            $response['message'] = '<p>' . Lang::get('project::message.warningReportProject') . '</p>' . $warningFieldNames;
        } else {
            $response['success'] = 1;
        }
        return response()->json($response);
    }

    /**
     * report project
     */
    public function reportSubmit($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $project = Project::find($id);
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        // check permission view
        if (!ViewProject::isAccessViewProject($project)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        //check permission edit
        $permissionEditPointPM = false;
        $permissionEditSubPM = false;
        if ($project->isOpen()) {
            $accessProject = ViewProject::checkPermissionEditWorkorder($project, 'project::point.save');
            $permissionEditPointPM = $accessProject['persissionEditPM'];
            $permissionEditSubPM = $accessProject['permissionEditSubPM'];
        }
        //check can chagne project dashboard
        if (!$project->canChangeDashboard()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Can not be changed values');
            return response()->json($response);
        }
        if (!$permissionEditPointPM && !$permissionEditSubPM) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        //in use, not comment
        $projectPoint = ProjectPoint::findFromProject($id);
        //get old data of fields will change
        $inputFields = DashboardLog::logFields();
        $oldData = [];
        foreach (array_keys($inputFields) as $field) {
            $oldData[$field] = $projectPoint->{$field};
        }
        try {
            //save point
            $updatePoint = self::save($id, true);
            //log change
            DashboardLog::insertLog($id, $oldData);
            ProjPointBaseline::baselineItem($project);
        } catch (Exception $ex) {
            Log::info($ex);
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            return response()->json($response);
        }

        if (isset($updatePoint['error'])) {
            $error = true;
            $response['message'] = $updatePoint['message'];
        } else {
            $response['message'] = Lang::get('project::message.Report success');
            $response['content'] = $updatePoint;
            $response['popup'] = 1;
            $response['refresh'] = URL::route('project::point.edit', ['id' => $project->id]);
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('project::message.Report success')
                        ]
                    ]
            );
        }

        if (isset($error)) {
            $response['error'] = 1;
        } else {
            $response['success'] = 1;
        }
        return response()->json($response);
    }

    /**
     * raise dashboard
     */
    public function raise()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $ids = Input::get('ids');
        $raiseNote = Input::get('raiseNote');
        if (!$ids || !count($ids)) {
            $response['message'] = Lang::get('project::message.Plesse choose project to raise');
            $response['error'] = 1;
            return $response;
        }
        $projects = [];
        $countRaise = 0;
        foreach ($ids as $projectId) {
            if (!$projectId) {
                continue;
            }
            $projectId = (int) $projectId;
            $project = Project::find($projectId);
            if (!$project) {
                continue;
            }
            $projectPoint = ProjectPoint::findFromProject($projectId);
            if (!$projectPoint) {
                continue;
            }
            // check permission view
            $members = $project->getMemberTypes();
            $employeeCurrent = Permission::getInstance()->getEmployee();
            if (!isset($members[ProjectMember::TYPE_PQA])
                ||
                (isset($members[ProjectMember::TYPE_PQA])
                &&
                !in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA]))
            ) {
                $access = ViewProject::checkPermissionEditWorkorder($project, 'project::point.save');
                if (!$access['persissionEditPM'] || !$access['permissionEditQA'] || !$access['permissionEditSubPM']) {
                    continue;
                }
            }
            $oldRaise = $projectPoint->raise;
            $projectPoint->raise = ProjectPoint::RAISE_UP;
            $projectPoint->raise_note = $raiseNote;
            $projectPoint->save();
            DashboardLog::insertLogRaise($projectPoint, $oldRaise);
            $countRaise++;
        }
        if (!$countRaise) {
            $response['message'] = Lang::get('project::message.You don\'t have '
                    . 'access to raise projects');
            $response['error'] = 1;
            return $response;
        }
        Session::flash(
            'messages', [
                    'success'=> [
                        Lang::get('project::message.Raise success'),
                    ]
                ]
        );
        $response['message'] = Lang::get('project::message.Raise success');
        $response['success'] = 1;
        $response['popup'] = 1;
        $response['refresh'] = route('project::dashboard');
        return $response;
    }

    /**
     * raise destroy
     */
    public function raiseDestroy($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $project = Project::find($id);
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        // check permission view
        $members = $project->getMemberTypes();
        $employeeCurrent = Permission::getInstance()->getEmployee();
        if (!isset($members[ProjectMember::TYPE_PQA])
            ||
            (isset($members[ProjectMember::TYPE_PQA])
            &&
            !in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA]))
        ) {
            $access = ViewProject::checkPermissionEditWorkorder($project, 'project::point.save');
            if (!$access['persissionEditPM'] || !$access['permissionEditQA'] || !$access['permissionEditSubPM']) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.You don\'t have access');
                return response()->json($response);
            }
        }
        $projectPoint = ProjectPoint::findFromProject($id);
        $oldRaise = $projectPoint->raise;
        if ($projectPoint->raise == ProjectPoint::RAISE_UP) {
            $projectPoint->raise = ProjectPoint::RAISE_DOWN;
            $projectPoint->save();
            DashboardLog::insertLogRaise($projectPoint, $oldRaise);
        }
        $response['success'] = 1;
        $response['message'] = Lang::get('project::message.Destroy raise success');
        return response()->json($response);
    }

    /**
     * raise dashboard
     */
    public function raiseBaseline()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $now = Carbon::now();
        if (!GeneralProject::isRedirectBLInDb($now)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Raise baseline only active on Monday');
            return response()->json($response);
        }
        $ids = Input::get('ids');
        $raiseNote = Input::get('raiseNote');
        if (!$ids || !count($ids)) {
            $response['message'] = Lang::get('project::message.Plesse choose project to raise baseline');
            $response['error'] = 1;
            return $response;
        }
        $countRaise = 0;
        foreach ($ids as $baselineId) {
            if (!$baselineId) {
                continue;
            }
            $baselineId = (int) $baselineId;
            $baseline = ProjPointBaseline::find($baselineId);
            if (!$baseline) {
                continue;
            }
            if (!GeneralProject::isLastWeek($baseline->created_at, $now)) {
                continue;
            }
            $project = Project::find($baseline->project_id);
            if (!$project) {
                continue;
            }
            // check permission view
            $members = $project->getMemberTypes();
            $employeeCurrent = Permission::getInstance()->getEmployee();
            if (!isset($members[ProjectMember::TYPE_PQA])
                ||
                (isset($members[ProjectMember::TYPE_PQA])
                &&
                !in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA]))
            ) {
                $access = ViewProject::checkPermissionEditWorkorder($project, 'project::point.save');
                if (!$access['persissionEditPM'] || !$access['permissionEditQA'] || !$access['permissionEditSubPM']) {
                    continue;
                }
            }
            $oldRaise = $baseline->raise;
            if ($baseline->raise == ProjectPoint::RAISE_DOWN) {
                $baseline->raise = ProjectPoint::RAISE_UP;
                $baseline->raise_note = $raiseNote;
                $baseline->save();
                DashboardLog::insertLogRaise($baseline, $oldRaise, true);
            }
            $countRaise++;
        }
        if (!$countRaise) {
            $response['message'] = Lang::get('project::message.You don\'t have '
                    . 'access to raise projects');
            $response['error'] = 1;
            return $response;
        }
        Session::flash(
            'messages', [
                    'success'=> [
                        Lang::get('project::message.Raise baseline success!'),
                    ]
                ]
        );
        $response['message'] = Lang::get('project::message.Raise baseline success');
        $response['success'] = 1;
        $response['popup'] = 1;
        $response['refresh'] = route('project::dashboard');
        return $response;
    }

    /**
     * raise destroy
     */
    public function raiseDestroyBaseline($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $now = Carbon::now();
        if (!GeneralProject::isRedirectBLInDb($now)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Destroy baseline only active on Monday');
            return response()->json($response);
        }
        $baseline = ProjPointBaseline::find($id);
        if (!$baseline) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (!GeneralProject::isLastWeek($baseline->created_at, $now)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Destroy baseline only active on Monday');
            return response()->json($response);
        }
        $project = Project::find($baseline->project_id);
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        // check permission view
        $members = $project->getMemberTypes();
        $employeeCurrent = Permission::getInstance()->getEmployee();
        if (!isset($members[ProjectMember::TYPE_PQA])
            ||
            (isset($members[ProjectMember::TYPE_PQA])
            &&
            !in_array($employeeCurrent->id, $members[ProjectMember::TYPE_PQA]))
        ) {
            $access = ViewProject::checkPermissionEditWorkorder($project, 'project::point.save');
            if (!$access['persissionEditPM'] || !$access['permissionEditQA'] || !$access['permissionEditSubPM']) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.You don\'t have access');
                return response()->json($response);
            }
        }
        $oldRaise = $baseline->raise;
        if ($baseline->raise == ProjectPoint::RAISE_UP) {
            $baseline->raise = ProjectPoint::RAISE_DOWN;
            $baseline->save();
            DashboardLog::insertLogRaise($baseline, $oldRaise, true);
        }
        $response['success'] = 1;
        $response['message'] = Lang::get('project::message.Destroy raise baseline of last week success');
        return response()->json($response);
    }

    /**
     * dashboard get note by ajajx
     */
    public function dashboardNotes()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $ids = (array) Input::get('ids');
        //get week slug mean had baseline
        $weekSlug = Input::get('weekSlug');
        $noBaseline = Input::get('noBaseline');

        $response = [];
        if (!$ids || !count($ids)) {
            $response['success'] = 1;
            return response()->json($response);
        }
        $pointNotes = ProjectPoint::getNotes($ids, $weekSlug, $noBaseline);
        if (!$pointNotes->isEmpty()) {
            foreach ($pointNotes as $item) {
                $response['data'][$item->project_id] = [
                    Task::TYPE_ISSUE_COST => $this->getNoteText($item, [
                        'cost_billable_note',
                        'cost_plan_total_note',
                        'cost_plan_current_note',
                        'cost_resource_total_note',
                        'cost_resource_current_note',
                        'cost_actual_effort_note',
                        'cost_ees_note',
                        'cost_eey2_note',
                        'cost_busy_rate_note',
                        'cost_productivity',
                    ]),
                    Task::TYPE_ISSUE_QUA => $this->getNoteText($item, [
                        'qua_leakage_note',
                        'qua_defect_note',
                    ]),
                    Task::TYPE_ISSUE_TL => $this->getNoteText($item, [
                        'tl_schedule_note',
                        'tl_deliver_note',
                    ]),
                    Task::TYPE_ISSUE_PROC => $this->getNoteText($item, [
                        'proc_compliance_note',
                        'proc_report_note',
                    ]),
                    Task::TYPE_ISSUE_CSS => $this->getNoteText($item, [
                        'css_css_note',
                        'css_idea_note',
                    ]),
                    Task::TYPE_ISSUE_SUMMARY => $this->getNoteText($item, [
                        'bl_summary_note'
                    ], ['not_minus' => true])
                ];
                $response['prev_weeks'][$item->project_id] = $item->prev_time ? Carbon::parse($item->prev_time)->format('Y-W') : null;
            }
        }
        $currWeek = ProjectPoint::parseWeekBySlug($weekSlug ? $weekSlug : Carbon::now()->format('Y-W'));
        $response['curr_week'] = ViewProject::formatPeriodDate($currWeek[0], $currWeek[1]);
        $response['success'] = 1;
        return response()->json($response);
    }

    /**
     * get note text of object
     *
     * @param object $item
     * @param array $attributes
     * @param array $config
     * @return string
     */
    protected function getNoteText($item, $attributes, array $config = [])
    {
        $note = '';
        $attributes = (array) $attributes;
        foreach ($attributes as $attribute) {
            if ($item->$attribute) {
                $note .= '<p>';
                if (!isset($config['not_minus']) || !$config['not_minus']) {
                    $note .= '- ';
                }
                $note .= View::nl2br($item->$attribute);
                $note .= '</p>';
            }
        }
        return $note;
    }

    /**
     * save deliver item from dashboard
     */
    public function deliverSave($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $deliverItem = ProjDeliverable::find($id);
        if (!$deliverItem || $deliverItem->status != ProjDeliverable::STATUS_APPROVED) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $project = Project::find($deliverItem->project_id);
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (!$project->isOpen()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Project closed');
            return response()->json($response);
        }
        //check permission edit
        $accessProject = ViewProject::checkPermissionEditWorkorder($project, 'project::point.save');
        if (!$accessProject['persissionEditPM'] && !$accessProject['permissionEditSubPM']) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        //get old data of fields will change
        $inputFields = DashboardLog::deliverableFields();
        $oldData = [];
        foreach (array_keys($inputFields) as $field) {
            $oldData[$field] = Carbon::parse($deliverItem->{$field})->format('Y-m-d');
        }
        if (Input::get('data.actual_date')) {
            $deliverItem->actual_date = Input::get('data.actual_date');
        } else {
            $deliverItem->actual_date = null;
        }
        try {
            $deliverItem->save();
            ProjPointFlat::flatItemProject($project);
            //log change
            DashboardLog::insertDeliverableLog($deliverItem, $oldData);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
        $response['message'] = Lang::get('project::message.Save deliver success, refresh page to view newest point');
        $response['success'] = 1;
        return response()->json($response);
    }

    /**
     * view help dashboard
     */
    public function helpView()
    {
        Breadcrumb::add('Project Dashboard', route('project::dashboard'));
        Breadcrumb::add(Lang::get('project::view.Project Report help'));
        Menu::setActive('project', 'project/dashboard');
        return view('project::help.dashboard');
    }

    /**
     * view help dashboard
     */
    public function helpViewWo()
    {
        Breadcrumb::add('Project Dashboard', route('project::dashboard'));
        Breadcrumb::add(Lang::get('project::view.Project Workorder help'));
        Menu::setActive('project', 'project/dashboard');
        return view('project::help.wo');
    }

    /**
     * view get dashboard logs
     * @return type
     */
    public function dashboardLogListAjax($id) {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $project = Project::find($id);
        $response = [];
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (!ViewProject::isAccessViewProject($project)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        $response['html'] = ViewLaravel::make('project::components.project_activity', [
            'projectLogs' => DashboardLog::getAllLogs($id)
        ])->render();
        $response['success'] = 1;
        return response()->json($response);
    }

    /**
     * save loc current, loc baseline, total effort of dev at cost tab of report
     * using ajax
     */
    public function saveCostProductivityProgLang($id) {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $projPoint = ProjectPoint::findFromProject($id);
        $proglang_id = Input::get('id');
        $name = Input::get('name');
        $value = Input::get('value');
        if (!is_numeric($value)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Input must be numeric');
            return response()->json($response);
        }
        if ($value < 0) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Input must be greater than or equal to zero');
            return response()->json($response);
        }
        if ($projPoint->cost_productivity_proglang) {
            $data = json_decode($projPoint->cost_productivity_proglang, true);
            $data[$proglang_id][$name] = $value;
            $projPoint->cost_productivity_proglang = json_encode($data);
        } else {
            $data[$proglang_id] = [];
            $data[$proglang_id][$name] = $value;
            $projPoint->cost_productivity_proglang = json_encode($data);
        }
        if ($projPoint->save()) {
            $response['success'] = 1;
            return response()->json($response);
        }
        $response['error'] = 1;
        $response['message'] = Lang::get('project::message.Error system');
        return response()->json($response);

    }

    /**
     * Display dashboard page
     *
     * @return \Illuminate\Http\Response
     */
    public function projectBaseline()
    {
        if (!Permission::getInstance()->isAllow('project::baseline.all')) {
            View::viewErrorPermission();
        }
        $status = Project::lablelState();
        $types = Project::labelTypeProject();
        Breadcrumb::add('Project Baseline');
        return view('project::point.baseline', [
            'collectionModel' => ProjPointBaseline::getBaselineAll(),
            'status' => $status,
            'viewBaseline' => true,
            'allColorStatus' => ViewProject::getPointColor(),
            'types' => $types,
            'isAllowRaise' => true
        ]);
    }

    public function ajaxAddOrRemoveWatcher()
    {
        $projectId = Input::get('project_id');
        $status = ProjectWatch::checkExists($projectId);

        return response()->json(['status' => $status]);
    }

    public function insertCompliance()
    {
        $data = $_POST;
        try {
            if ($data['viewBaseline']) {
                $compliance = ProjPointBaseline::saveComplianceBaseline($data);
                return response()->json($compliance);
            }
            $compliance = ProjectPoint::saveCompliance($data);
            return response()->json($compliance);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            return response()->json($response);
        }
    }
}
