<?php

namespace Rikkei\Project\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Api\Helper\Operation;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\View;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Http\Requests\AddCustomerCommunicationRequest;
use Rikkei\Project\Http\Requests\AddMemberCommunicationRequest;
use Rikkei\Project\Http\Requests\AddProjectCommunicationRequest;
use Rikkei\Project\Http\Requests\AddSecurityRequest;
use Rikkei\Project\Http\Requests\AddSkillRequest;
use Rikkei\Project\Model\AssumptionsConstraints;
use Rikkei\Project\Model\CommunicationProject;
use Rikkei\Project\Model\CustomerCommunication;
use Rikkei\Project\Model\MemberCommunication;
use Rikkei\Project\Model\Project;
use Rikkei\Core\View\Menu;
use Rikkei\Project\Model\ProjectBillableCost;
use Rikkei\Project\Model\ProjectBusiness;
use Rikkei\Project\Model\ProjectCalendarReport;
use Rikkei\Project\Model\ProjectCate;
use Rikkei\Project\Model\TaskComment;
use Rikkei\Project\Model\TaskHistory;
use Rikkei\Project\Model\ProjectCategory;
use Rikkei\Project\Model\ProjectClassification;
use Rikkei\Project\Model\ProjectSector;
use Rikkei\Project\Model\ProjPointBaseline;
use Rikkei\Project\Model\RiskAction;
use Rikkei\Project\Model\RiskAttach;
use Rikkei\Project\Model\SaleProject;
use Rikkei\Project\Model\Security;
use Rikkei\Project\Model\SkillRequest;
use Rikkei\Project\Model\TaskAction;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\TaskTeam;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\Breadcrumb;
use Illuminate\Http\Request;
use Rikkei\Sales\Model\Customer;
use Rikkei\Project\Http\Requests\CreateProjectRequest;
use Illuminate\Support\Facades\Input;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\Model\Role;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\Lang;
use Rikkei\Project\View\View as ViewHelper;
use Rikkei\Team\Model\Team;
use Rikkei\Project\Model\CriticalDependencie;
use Rikkei\Project\Model\AssumptionConstrain;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\StageAndMilestone;
use Rikkei\Project\Model\Training;
use Rikkei\Project\Model\ExternalInterface;
use Rikkei\Project\Model\ToolAndInfrastructure;
use Rikkei\Project\Model\Communication;
use Rikkei\Project\Model\ProjDeliverable;
use Rikkei\Project\Model\Performance;
use Rikkei\Project\Http\Requests\AddCriticalDependenciesRequest;
use Rikkei\Project\Http\Requests\AddAssumptionConstrainRequest;
use Rikkei\Project\Http\Requests\RiskRequest;
use Rikkei\Project\Http\Requests\StageAndMilestoneRequest;
use Rikkei\Project\Http\Requests\AddTrainingRequest;
use Rikkei\Project\Http\Requests\AddExternalInterfaceRequest;
use Rikkei\Project\Http\Requests\AddToolAndInfrastructureRequest;
use Rikkei\Project\Http\Requests\AddCommunicationRequest;
use Rikkei\Project\Http\Requests\AddDeliverableRequest;
use Rikkei\Project\Http\Requests\AddPerformanceRequest;
use Rikkei\Project\Http\Requests\AddQualityRequest;
use Rikkei\Project\Http\Requests\AddQualityPlanRequest;
use Rikkei\Project\Http\Requests\AddCMPlanRequest;
use Rikkei\Project\Http\Requests\AddProjectMemberRequest;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\ProjectWONote;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\QualityPlan;
use Rikkei\Project\Model\CMPlan;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\Project\Model\ProjectChangeWorkOrder;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Project\Model\ProjectLog;
use Rikkei\Project\Model\SourceServer;
use Rikkei\Core\View\CookieCore;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rikkei\Project\View\View as ViewProject;
use Illuminate\Support\Facades\View as ViewLaravel;
use Rikkei\Project\Http\Requests\EditBasicInforProjectRequest;
use Illuminate\Support\Facades\URL;
use Rikkei\Resource\Model\Programs;
use Rikkei\Project\Model\ProjectProgramLang;
use Exception;
use Illuminate\Support\Facades\Log;
use Rikkei\Project\View\View as Pview;
use Rikkei\Core\Model\EmailQueue;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Rikkei\Resource\View\View as Rview;
use Rikkei\Tag\Model\ProjectTag;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Project\View\ProjDbHelp;
use Rikkei\Sales\Model\Company;
use Auth;
use Rikkei\Sales\Model\Css;
use Rikkei\Project\Model\ProjEmployeeSystena;
use Rikkei\Project\Model\ProjectApprovedProductionCost;
use Rikkei\Project\Model\DevicesExpense;
use Rikkei\Project\Http\Requests\AddDevicesExpensesRequest;
use Rikkei\Project\Model\ProjectKind;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Project\View\ProjectExport;
use Rikkei\Project\View\View as ProjView;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Tag\Model\Tag;
use Rikkei\Project\Model\TaskNcmRequest;
use Rikkei\Team\View\TeamList;

/**
 * Class ProjectController
 * @package Rikkei\Project\Http\Controllers
 */
class ProjectController extends Controller
{
    /*
     * create project
     * @return view
     */
    public function create()
    {
        if (!Permission::getInstance()->isAllow('project::project.create')) {
            View::viewErrorPermission();
        }
        $status = Project::lablelState();
        $employees = Employee::getAllEmployeeIsPmOrLeader();
        Breadcrumb::add(Lang::get('project::view.Create project'));
        $projectMeta = new ProjectMeta();
        $project = new Project();
        $taskWOApproved = true;
        $sourceServer = new SourceServer();
        $sourceServer->setData([
            'is_check_redmine' => 1,
            'is_check_git' => 1
        ]);
        $permissionEdit = true;
        $labelTypeProject = Project::labelTypeProjectFull();
        $lblProjectKind = ProjectKind::all()->pluck('kind_name','id');
        $lblProjectCate = ProjectCategory::all()->pluck('category_name','id');
        $lblClassCate = ProjectClassification::all()->pluck('classification_name','id');
        $lblBusinessCate = ProjectBusiness::all()->pluck('business_name','id');
        $lblSectorCate = ProjectSector::all()->pluck('sub_sector','id');
        $programsOption = Programs::getListOption();
        $teamPath = Team::getTeamPathTree($withTrashed = true);
        $companies = Company::getCompanies();
        $allTeamDraft = [];

        // Check quyền edit calendar
        // Nếu là PM hoặc Leader dự án sẽ được phép edit calendar
        $permissionEditCalendar = false;
        $maxEffort = Project::MAX_EFFORT;
        $unitPrices = ProjectApprovedProductionCost::getUnitPrices();

        return view('project::edit', compact([
            'employees', 'status',
            'projectMeta', 'project', 'taskWOApproved', 'sourceServer',
            'permissionEdit', 'labelTypeProject', 'unitPrices',
            'programsOption', 'teamPath', 'allTeamDraft', 'permissionEditCalendar', 'maxEffort', 'companies', 'lblProjectKind',
            'lblProjectCate', 'lblClassCate', 'lblBusinessCate', 'lblSectorCate'
        ]));
    }

    /*
     * insert or update project
     */
    public function store(CreateProjectRequest $request)
    {
        $project = null;
        $projectId = Input::get('project_id');
        $cloneId = Input::get('clone_id');
        $isClone = $cloneId ? true : false;
        if (!$this->checkPermissionStore($projectId, $project, $cloneId)) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        if ($isClone) {
            unset($project->id);
            unset($project->created_at);
            unset($project->updated_at);
            unset($project->created_by);
            $project->name = Input::get('name');
            $project->state = Project::STATE_NEW;
        } else {
            $data = $request->all();
            $data['sale_id'] = explode(',', $data['sale_id']);
            if (!$projectId) {
                CookieCore::set('tab-keep-status-project-workorder', 'workorder');
            } else {
                if ($project && is_object($project) && $project->state != Project::STATE_NEW) {
                    unset($data['end_at']);
                }
            }
            if (isset($data['id_redmine'])) {
                $data['id_redmine'] = Str::ascii($data['id_redmine'], null);
            }
            if (isset($data['id_git'])) {
                $data['id_git'] = Str::ascii($data['id_git'], null);
            }
        }
        try {
            $projectId = Project::insertOrUpdateProject($isClone ? $project->toArray() : $data, true, $cloneId);
        } catch (Exception $ex) {
            $messages = [
                'errors' => [
                    $ex->getMessage(),
                ]
            ];
            return redirect()->route('project::project.create')->with('messages', $messages);
        }

        if ($projectId) {
            if (isset($data['project_id']) && $data['project_id']) {
                $mgs = Lang::get('project::view.Update project success');
            } elseif ($isClone) {
                $mgs = Lang::get('project::view.Clone project success');
            }
            else {
                $mgs = Lang::get('project::view.Create project success');
            }

            $messages = [
                'success' => [
                    $mgs,
                ]
            ];
            return redirect()->route('project::project.edit', ['id' => $projectId])
                ->with('messages', $messages);
        }

        if (isset($data['project_id']) && $data['project_id']) {
            $mgs = Lang::get('project::view.Update project error');
        } else {
            $mgs = Lang::get('project::view.Create project error');
        }
        $messages = [
            'errors' => [
                $mgs,
            ]
        ];
        if ($isClone) {
            return redirect()->route('project::project.edit', ['id' => $cloneId])->with('messages', $messages);
        }
        return redirect()->route('project::project.create')->with('messages', $messages);
    }

    /**
     * check permission create/save project
     * @param int|null $projectId
     * @param object $project
     * @param int|null $cloneId
     * @return bool
     */
    protected function checkPermissionStore($projectId = null, &$project = null, $cloneId = null)
    {
        $permissionScope = Permission::getInstance();
        //edit project
        if ($projectId) {
            $project = Project::find($projectId);
            if (!$project) {
                return false;
            }
            $permission = ViewHelper::checkPermissionEditWorkorder($project, 'project::project.create');
            $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];
            if (!$permissionEdit) {
                return false;
            }
        } else { //create project
            $project = null;
            if ($cloneId) {
                $project = Project::find($cloneId);
                if (!$project) {
                    return false;
                }
            }
            if ($permissionScope->isScopeCompany(null, 'project::project.create') ||
                $permissionScope->isScopeTeam(null, 'project::project.create') ||
                $permissionScope->isScopeSelf(null, 'project::project.create')) {
                return true;
            }
            return false;
        }
        return true;
    }

    /*
     * check project existis
     */
    public function checkExists(Request $request)
    {
        $projectId = Input::get('projectId');
        if (!$this->checkPermissionStore($projectId)) {
            return 'false';
        }
        $data = $request->all();
        return Project::checkExists($data);
    }

    /*
     * view page edit project
     * @param int
     * @return view
     */
    public function edit($id)
    {
        $project = Project::getProjectById($id);
        $notFound = false;
        if (!$project) {
            $notFound = true;
        } else if ($project->status != Project::STATUS_APPROVED) {
            $notFound = true;
        }
        if ($notFound) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }

        $ownerTeamsCurrentEmployee = [];
        // check permission view
        if (!ViewHelper::isAccessViewProject($project, $ownerTeamsCurrentEmployee)) {
            View::viewErrorPermission();
        }
        $isScopeCompany = Permission::getInstance()->isScopeCompany(null, 'project::dashboard');

        $projectDraft = $project->projectChild;
        $allTeam = Project::getAllTeamOfProject($id);
        //Get all leaders and sub of approved project
        $leaderAndSubProject = EmployeeRole::getLeadAndSub($allTeam, true);
        $arrIdLeaderAndSubProject = [];
        foreach ($leaderAndSubProject as $key => $item) {
            $arrIdLeaderAndSubProject[] = $item['id'];
        }

        $employeeTeamPositions = EmployeeTeamHistory::getTeamHistory(Auth()->user()->employee_id);
        $arrTeamIdOfEmp = [];
        $activeTeam = null;
        foreach ($employeeTeamPositions as $team) {
            if (in_array($team['role_id'], [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER])) {
                $arrTeamIdOfEmp[] = $team->team_id;
            }
            if ($team['is_working'] == Team::WORKING) {
                $activeTeam = $team->team_id;
            }
        }

        $groupleaderInformation = $project->getInformationGroupLeader();
        //Classified leader and subs of Division in charged and division partners
        $results = Project::classifyLeadAndSubForEachTypeDivision($leaderAndSubProject, $groupleaderInformation);
        $leadSubInChargedIds = $results['leadSubInCharged'];
        $leadSubPartnersIds = $results['leadSubPartners'];
        $allCriticalDependencies = CriticalDependencie::getAllCriticalDependencie($project->id);

        //AddPmToPersonInCharged
        $leadSubInChargedIds[] = $project->manager_id;

        //Get division In charged
        $divInCharged = !empty($groupleaderInformation) ? $groupleaderInformation->team_id : null;
        //Get Divisions Partner
//        $divPartners = Project::getDivisionPartners($allTeam, $divInCharged);

        $currentUser = Permission::getInstance()->getEmployee();
        $pmActive = $project->getPMActive();

        //Using For JS TO define can view btn display approve Cost Or Not
        $jsIsViewBtnApproveCost = in_array($currentUser->id, $leadSubInChargedIds)
            || in_array($currentUser->id, $leadSubPartnersIds)
            || count(array_intersect($ownerTeamsCurrentEmployee, $allTeam)) > 0
            || $isScopeCompany
            || SaleProject::isSalePersonOfProjs($currentUser->id, $project->id);

        //Using To define allow update approve Cost Or Not
        $isAllowUpdateApproveCostPrice = in_array($currentUser->id, $leadSubInChargedIds)
            || count(array_intersect($ownerTeamsCurrentEmployee, [$divInCharged])) > 0
            || $isScopeCompany
            || SaleProject::isSalePersonOfProjs($currentUser->id, $project->id);

        if ($projectDraft) {
            $allTeamDraft = Project::getAllTeamOfProject($projectDraft->id);
            $leaderOld = Employee::find($project->leader_id);
            $idLeader = $projectDraft->leader_id;
            $pmDraft = Employee::getEmpById($projectDraft->manager_id);
        } else {
            $allTeamDraft = $allTeam;
            $leaderOld = null;
            $idLeader = $project->leader_id;
            $pmDraft = null;
            $projectDraft = $project;
        }
        $leaders = EmployeeRole::getLeadAndSub($allTeamDraft, true);
        // Get information group leader
        $groupleaderInformation = $project->getInformationGroupLeader();

        // Check if group leader not exist in $leaders
        if ($groupleaderInformation && !array_key_exists($groupleaderInformation->id, $leaders)) {
            $leaders[$groupleaderInformation->id] = [
                'id' => $groupleaderInformation->id,
                'name' => $groupleaderInformation->name,
                'email' => $groupleaderInformation->email,
                'team_id' => $groupleaderInformation->team_id,
                'role_id' => $groupleaderInformation->role_id,
            ];
        }
        $leaderIds = array_keys($leaders);
        if ($idLeader !== null && !in_array($idLeader, $leaderIds)) {
            $idLeader = reset($leaderIds);
        }

        $getAssumptions = AssumptionsConstraints::getAssumptions($project->id, Task::TYPE_WO_ASSUMPTIONS);
        $getConstraints = AssumptionsConstraints::getAssumptions($project->id, Task::TYPE_WO_CONSTRAINTS);
        $getSkillRequest = SkillRequest::getSkillRequest($project->id);
        $getMemberCommunication = MemberCommunication::getMemberCommunicationByType($project->id, Task::TYPE_WO_MEMBER_COMMUNICATION);
        $getCustomerCommunication = CustomerCommunication::getCustomerCommunication($project->id);
        $communicationMeeting = CommunicationProject::getCommunication($project->id, Task::TYPE_WO_MEETING_COMMUNICATION);
        $communicationReport = CommunicationProject::getCommunication($project->id, Task::TYPE_WO_REPORT_COMMUNICATION);
        $communicationOther = CommunicationProject::getCommunication($project->id, Task::TYPE_WO_OTHER_COMMUNICATION);
        $getSecurity = Security::getSecurity($project->id);
        $allSaleEmployee = ProjectTag::getSales($project);
        $statusAll = Project::lablelState();
        $statusAllWithoutPqa = Project::lablelStateWithoutPqa();
        $statusAllForDraf = $statusAll;
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        if ($project->state != Project::STATE_NEW) {
            if ($permission['permissionEditPqa']) {
                $status = ViewHelper::getValuesFollowKey([
                    Project::STATE_PROCESSING,
                    Project::STATE_PENDING,
                    Project::STATE_CLOSED,
                    Project::STATE_REJECT
                ], $statusAll);
            } else {
                $status = ViewHelper::getValuesFollowKey([
                    Project::STATE_PROCESSING,
                    Project::STATE_PENDING,
                    Project::STATE_REJECT
                ], $statusAll);
            }
        } else {
            if ($permission['permissionEditPqa']) {
                $status = $statusAll;
            } else {
                $status = $statusAllWithoutPqa;
            }
        }

        $statusIssue = Task::statusNewLabel();
        $allTeamName = Team::getAllTeam();
        $projectMeta = $project->getProjectMeta();
        $priority = Task::priorityLabel();
        $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($id);
        $checkEditWorkOrder = Task::checkEditWorkOrder($id);
        $isSyncSourceServer = SourceServer::getSyncSourceServer($id);
        $projectLogs = ProjectLog::getAllProjectLog($id);
        $sourceServer = SourceServer::getSourceServer($id);
        $taskWOApproved = Task::getTaskWaitingApproveByType($id, Task::TYPE_WO);
        $checkHasTaskWorkorderApproved = Task::checkHasTaskWorkorderApproved($id, Task::TYPE_WO);
        $labelTypeProject = Project::labelTypeProjectFull();
        $lblProjectCate = ProjectCategory::all()->pluck('category_name','id');
        $lblClassCate = ProjectClassification::all()->pluck('classification_name','id');
        $lblBusinessCate = ProjectBusiness::all()->pluck('business_name','id');
        $lblSectorCate = ProjectSector::all()->pluck('sub_sector','id');
        $quality = ProjQuality::getFollowProject($project->id);
        $qualityDraftBill = ProjQuality::getQualityDraft($project->id, 'billable_effort');
        $qualityDraftPlan = ProjQuality::getQualityDraft($project->id, 'plan_effort');
        $qualityDraftProdCost = ProjQuality::getQualityDraft($project->id, 'cost_approved_production');
        $checkHasVersionWO = ProjectChangeWorkOrder::checkHasVersion($project);
        $checkHasTask = Task::checkHasTaskWorkorder($project->id);
        $taskManager = Task::getList($project->id, Task::getTypeIssueProject(), ['get_all' => true]);
        $allNameTab = Task::getAllNameTabWorkorder();
        $allPmActive = $project->getAllPMActive();
        $teamsProject = 'Teams: ' . $project->getTeamsString();
        $programsOption = Programs::getListOption();
        $projectProgramLangs = ProjectProgramLang::getProgramLangOfProject($project);
        $companies = Company::getAllCompany();
        $projectAppProductCost = ProjectApprovedProductionCost::getProjectApprpveProductionCost($id, $isAllowUpdateApproveCostPrice);
        $billableCosts = ProjectBillableCost::getByProjectId($project->id);
        $billableCostTotal = $qualityDraftBill ? $qualityDraftBill->billable_effort : ($quality ? $quality->billable_effort : 0);
        $approveCostTotal = $qualityDraftProdCost ? $qualityDraftProdCost->cost_approved_production : ($quality ? $quality->cost_approved_production : 0);
        $billableCosts = ProjectBillableCost::checkExistBillableCosts($project->id, $billableCosts, $billableCostTotal, $approveCostTotal);
        $lblProjectKind = ProjectKind::all()->pluck('kind_name','id');
        $customer = null;
        $customers = null;
        if ($project->cust_contact_id) {
            $customer = Customer::find($project->cust_contact_id);
        }
        if (!$customer) {
            $customer = new Customer();
            $company = null;
        } else {
            $customer->full_name = $customer->name . ($customer->name_ja ? ' (' .
                    $customer->name_ja . ')' : '');
            $company = Company::find($customer->company_id);
        }

        if ($project->company_id) {
            $customers = Customer::customerByCompany($project->company_id);
        }
        $company = Company::find($project->company_id);
        $teamPath = Team::getTeamPathTree();

        Breadcrumb::add('Project Dashboard', route('project::dashboard'));
        Menu::setFlagActive('project');
        //Check if is Pm and confirm customer contract
        $isNotContractConfirm = Task::isNotComfirmContract($project->id);

        $managerOfProject = ProjectMember::getManagerOfProject($project->id);

        // Check quyền edit calendar
        // Nếu là PM hoặc Leader dự án sẽ được phép edit calendar
        $permissionEditCalendar = false;
        if (in_array(data_get($currentUser, 'id'), $managerOfProject)) {
            $permissionEditCalendar = true;
        }
        $maxEffort = Project::MAX_EFFORT;

        $checkEditWorkOrderReview = true; // mọi trạng thái đều sửa dk view detail cost and loại hình
        //List unit prices
        $unitPrices = ProjectApprovedProductionCost::getUnitPrices();
        // Check current user in list Dlead of project
        $empId = $currentUser->id;
        $hasPermissionViewCostDetail = Project::hasPermissionViewCostDetail($currentUser->id, $project, $allTeam);
        $hasPermissionViewCostPriceDetail = Project::hasPermissionViewCostPriceDetail($currentUser->id, $allTeam);
        $hasPerApproveProductionCost = Permission::getInstance()->isAllow('project::project.approved-production-cost');

        $showByTeam = null;
        if (in_array($empId, $arrIdLeaderAndSubProject)) {
            if ($empId != $project->manager_id) {
                $showByTeam = $activeTeam;
            }
        }
        $listNc = TaskNcmRequest::getGridData($project->id);
        $ncStatusAll = Task::statusNCLabel();
        $owners = Employee::select('id', DB::raw("SUBSTRING_INDEX(employees.email, ". "'@', 1) as email"))->get();
        $listOpportunities = Task::getOpportunity($project->id);
        $priorityV2 = Task::priorityLabelV2();
        $statusOpp = Task::statusOpportunityLabel();
        $statusActionOpp = Task::statusActionOpportunityLabel();
        $opportunitySources = Task::getAllOpportunitySource();
        return view('project::edit', compact('status', 'priority',
            'project', 'allTeam', 'customer', 'company',
            'allTeamName', 'projectMeta', 'isNotContractConfirm',
            'checkSubmit', 'checkDisplaySubmitButton', 'checkEditWorkOrder',
            'isSyncSourceServer', 'currentUser',
            'projectLogs', 'sourceServer',
            'sourceServerDraft', 'taskWOApproved',
            'permissionEdit', 'checkHasTaskWorkorderApproved',
            'allSaleEmployee', 'labelTypeProject', 'qualityDraftProdCost',
            'projectDraft', 'quality', 'qualityDraftBill',
            'qualityDraftPlan', 'checkHasVersionWO', 'checkHasTask',
            'allTeamDraft', 'allNameTab', 'pmActive',
            'teamsProject', 'statusAllForDraf', 'programsOption',
            'projectProgramLangs', 'allPmActive', 'pmDraft', 'billableCosts',
            'leaders', 'leaderOld', 'idLeader', 'teamPath', 'permissionEditCalendar','projectAppProductCost','maxEffort', 'companies', 'customers', 'lblProjectKind',
            'checkEditWorkOrderReview',
            'jsIsViewBtnApproveCost',
            'arrIdLeaderAndSubProject',
            'arrTeamIdOfEmp',
            'empId',
            'hasPermissionViewCostDetail',
            'hasPermissionViewCostPriceDetail',
            'hasPerApproveProductionCost',
            'ownerTeamsCurrentEmployee', 'leaderIds', 'communicationMeeting', 'communicationReport', 'communicationOther',
            'isAllowUpdateApproveCostPrice', 'unitPrices', 'taskManager', 'statusIssue', 'purchaseOrder', 'getSkillRequest', 'getMemberCommunication', 'getCustomerCommunication',
            'showByTeam', 'projectCate', 'lblProjectCate', 'lblClassCate', 'lblBusinessCate', 'lblSectorCate', 'getAssumptions', 'getConstraints', 'allCriticalDependencies', 'getSecurity',
            'listNc', 'ncStatusAll', 'owners',
            'listOpportunities', 'priorityV2', 'statusOpp', 'statusActionOpp'
        ));
    }

    /**
     * Get all current skill of employee
     *
     * @param $empID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentSkill (Request $request)
    {
        $empID = $request->get('empId');
        $currentSkill = EmployeeSkill::getSkillIdsInCv($empID);
        $tagData = Tag::getTagDataProj();
        $data['currentSkill'] = $currentSkill;
        $data['tagData'] = $tagData;
        return $data;
    }

    /**
     * Get all project report by project ID
     * URI: project/get-calendar-report/{project_id}
     * Method: GET
     * Route: project::project.get-calendar-report
     *
     * @param Request $request
     * @param $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectCalendarReport(Request $request, $projectId)
    {
        $project = Project::getProjectById($projectId);
        // check permission view
        if (!ViewHelper::isAccessViewProject($project)) {
            View::viewErrorPermission();
        }

        //Get Project report
        $calendarReports = ProjectCalendarReport::getReportByProjectId($projectId);

        return response()->json($calendarReports);
    }

    /**
     * Get Project report by date
     * URI: project/get-calendar-report/{project_id}/{date}
     * Method: GET
     * Route: project::project.get-report-by-date
     *
     * @param $projectId
     * @param $date
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectCalendarReportByDate($reportId)
    {
        $report = ProjectCalendarReport::getReportByProjectDate($reportId);
        $project = Project::getProjectById($report['project_id']);
        // check permission view
        if (!ViewHelper::isAccessViewProject($project)) {
            View::viewErrorPermission();
        }

        if (empty($report)) {
            $data = [
                'status_code' => 404,
                'message' => 'Data not found!',
            ];
        } else {
            $data = [
                'status_code' => 200,
                'data' => $report
            ];
        }

        return response()->json($data);
    }

    /**
     * Public Project report
     * URI: project/project-calendar-report/create/{projectId}
     * Method: POST
     * Route: project.create-calendar-report
     *
     * @param Request $request
     * @param $projectId
     * @param $date
     * @return \Illuminate\Http\JsonResponse
     */
    public function publishCalendarReport(Request $request, $projectId)
    {
        $currentUser = Permission::getInstance()->getEmployee();
        $managerOfProject = ProjectMember::getManagerOfProject($projectId);

        // Check quyền edit calendar
        // Check permission PM hoặc Leader dự án sẽ được phép edit calendar
        if (!in_array(data_get($currentUser, 'id'), $managerOfProject)) {
            $data = [
                'status_code' => 403,
                'status' => 'danger',
                'message' => trans('project::view.project_report_not_permission')
            ];

            return response()->json($data);
        }

        $reportId = ProjectCalendarReport::publishCalendarReport($request, $projectId);

        if (!$reportId) {
            $data = [
                'status_code' => 404,
                'status' => 'danger',
                'message' => trans('project::view.public_report_failed')
            ];
        } else {
            $data = [
                'status_code' => 200,
                'status' => 'success',
                'id' => $reportId,
                'message' => trans('project::view.public_report_success')
            ];
        }

        return response()->json($data);
    }

    /**
     * Update Project report
     * URI: project/project-calendar-report/update/{reportId}
     * Method: POST
     * Route: project.update-calendar-report
     *
     * @param Request $request
     * @param $reportId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCalendarReport(Request $request, $reportId)
    {
        $report = ProjectCalendarReport::find($reportId);
        $currentUser = Permission::getInstance()->getEmployee();
        $managerOfProject = ProjectMember::getManagerOfProject($request->project_id);

        // Check quyền edit calendar
        // Check permission PM hoặc Leader dự án sẽ được phép edit calendar
        if (!in_array(data_get($currentUser, 'id'), $managerOfProject)
            || data_get($currentUser, 'id') != $report->employee_id) {
            return response()->json([
                'status_code' => 403,
                'status' => 'danger',
                'message' => trans('project::view.project_report_not_permission')
            ]);
        }

        $result = ProjectCalendarReport::updateCalendarReport($request, $reportId);

        if (!$result) {
            $data = [
                'status_code' => 404,
                'status' => 'danger',
                'message' => trans('project::view.public_report_failed')
            ];
        } else {
            $data = [
                'status_code' => 200,
                'status' => 'success',
                'id' => $reportId,
                'message' => trans('project::view.public_report_success')
            ];
        }

        return response()->json($data);
    }

    /**
     * Delete Project report
     * URI: project/delete-report/{project_id}/{date}
     * Method: DELETE
     * Route: project::project.delete-report
     *
     * @param $projectId
     * @param $date
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCalendarReport(Request $request, $reportId)
    {
        $report = ProjectCalendarReport::find($reportId);
        $currentUser = Permission::getInstance()->getEmployee();
        $managerOfProject = ProjectMember::getManagerOfProject($request->project_id);

        // Check quyền edit calendar
        // Check permission PM hoặc Leader dự án sẽ được phép delete calendar
        if (!in_array(data_get($currentUser, 'id'), $managerOfProject)
            || data_get($currentUser, 'id') != $report->employee_id) {
            return response()->json([
                'status_code' => 403,
                'status' => 'danger',
                'message' => trans('project::view.project_report_not_permission')
            ]);
        }

        $report = ProjectCalendarReport::deleteCalendarReport($reportId);

        if (!$report) {
            $data = [
                'status_code' => 404,
                'status' => 'danger',
                'message' => trans('project::view.delete_report_failed')
            ];
        } else {
            $data = [
                'status_code' => 200,
                'status' => 'success',
                'message' => trans('project::view.delete_report_success')
            ];
        }

        return response()->json($data);
    }

    /*
     * delete project
     */
    public function delete($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        if (!Permission::getInstance()->isScopeCompany(null, 'project::project.delete')) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        $project = Project::getProjectById($id);
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if ($project->state == Project::STATE_NEW) {
            $status = Project::deleteProject($project);
        } else {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Project isnot new status, canot delete');
            return response()->json($response);
        }
        if ($status) {
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Delete item success');
            $response['popup'] = 1;
            $response['refresh'] = route('project::dashboard');
            Session::flash(
                'messages', [
                    'success' => [
                        Lang::get('project::message.Delete item success'),
                    ]
                ]
            );
            return response()->json($response);
        }
        $response['error'] = 1;
        $response['message'] = Lang::get('project::message.Delete item error');
        return response()->json($response);
    }

    /*
     * add critical dependencies
     */
    public function addCriticalDependencies(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = CriticalDependencie::deleteCriticalDependencie($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = CriticalDependencie::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddCriticalDependenciesRequest::validateData($data);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = CriticalDependencie::insertCriticalDependencie($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            //send email to assigned member
            if (isset($data['member_1'])) {
                $data['project_name'] = $project->name;
                $data['project_id'] = $project->id;
                $emailMembers = array();
                foreach ($data['member_1'] as $memberId) {
                    array_push($emailMembers, Employee::getEmailEmpById($memberId));
                }
                $emailQueue = new EmailQueue();
                $emailQueue->setTo(reset($emailMembers));
                foreach ($emailMembers as $email) {
                    $emailQueue->addCc($email);
                }
                $emailQueue->setSubject(trans('project::view.Critical Dependencies Assignee', ['project_name' => $project->name]))
                    ->setTemplate('project::emails.critical_dependency_assignee', $data)
                    ->setNotify($data['member_1'], null, route('project::project.edit', ['id' => $project->id]) . '#others', ['category_id' => RkNotify::CATEGORY_PROJECT])
                    ->save();
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = CriticalDependencie::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /*
     * add assumption constrain
     */
    public function addAssumptionConstrain(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];
        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = AssumptionConstrain::deleteAssumptionConstrain($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = AssumptionConstrain::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddAssumptionConstrainRequest::validateData($data);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = AssumptionConstrain::insertAssumptionConstrain($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            //send email to assigned member
            if ($data['member_1']) {
                $data['project_name'] = $project->name;
                $data['project_id'] = $project->id;
                $emailMembers = array();
                foreach ($data['member_1'] as $memberId) {
                    array_push($emailMembers, Employee::getEmailEmpById($memberId));
                }
                $emailQueue = new EmailQueue();
                $emailQueue->setTo(reset($emailMembers));
                foreach ($emailMembers as $email) {
                    $emailQueue->addCc($email);
                }
                $emailQueue->setSubject(trans('project::view.Assumption and constrains Assignee', ['project_name' => $project->name]))
                    ->setTemplate('project::emails.assumption_constrains_assignee', $data)
                    ->setNotify($data['member_1'], null, route('project::project.edit', ['id' => $project->id]) . '#others', ['category_id' => RkNotify::CATEGORY_PROJECT])
                    ->save();
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = AssumptionConstrain::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /*
     * add risk
     */
    public function addRisk(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['projectId']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditQA'];
        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = Risk::deleteRisk($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = Risk::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = RiskRequest::validateData($data);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = Risk::insertRisk($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = Risk::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /*
     * add stage and milestone
     */
    public function addStageAndMilestone(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];

        if (!$permissionAdd) {
            View::viewErrorPermission();
        }
        if (!Task::checkEditWorkOrder($project->id)) {
            $response['status'] = false;
            $response['message_error'] = Lang::get('project::message.Workorder is processing, you canot submit');
            $response['popuperror'] = 1;
            $response['reload'] = 1;
            Session::flash(
                'messages', [
                    'errors' => [
                        Lang::get('project::message.Workorder is processing, you canot submit'),
                    ]
                ]
            );
            return response()->json($response);
        }

        $response = array();
        $response['status'] = true;
        if (isset($data['isDelete'])) {
            $status = StageAndMilestone::deleteStageAndMilestone($data);
            if ($status) {
                $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                $response['content'] = StageAndMilestone::getContentTable($project);
                return response()->json($response);
            }
            $response['status'] = false;
            return response()->json($response);
        }
        $validateAdd = StageAndMilestoneRequest::validateData($data, $project);
        if ($validateAdd->fails()) {
            $response['message_error'] = $validateAdd->errors();
            $response['status'] = false;
            return response()->json($response);
        }
        $status = StageAndMilestone::insertStageAndMilestone($data);
        if (!$status) {
            $response['status'] = false;
            return response()->json($response);
        }
        $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
        $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
        $response['content'] = StageAndMilestone::getContentTable($project);
        $response['id'] = $status;
        return response()->json($response);
    }

    /*
     * add training
     */
    public function addTraining(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];

        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = Training::deleteTraining($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = Training::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddTrainingRequest::validateData($data, $project);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = Training::insertTraining($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = Training::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /*
     * add member communication
     */
    public function addMemberCommunication(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = MemberCommunication::deleteMemberCommunication($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    if ($data['type'] == 'customer_communication') {
                        $response['content'] = MemberCommunication::getContentTable($project, Task::TYPE_WO_CUSTOMER_COMMUNICATION);
                    } else {
                        $response['content'] = MemberCommunication::getContentTable($project, Task::TYPE_WO_MEMBER_COMMUNICATION);
                    }
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddMemberCommunicationRequest::validateData($data, $project);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = MemberCommunication::insertMemberCommunication($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = MemberCommunication::getContentTable($project, $data['type']);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    public function addCustomerCommunication(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = CustomerCommunication::deleteMemberCommunication($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                        $response['content'] = CustomerCommunication::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddCustomerCommunicationRequest::validateData($data);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = CustomerCommunication::insertMemberCommunication($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = CustomerCommunication::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /*
     * add project communication
     */
    public function addProjectCommunication(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];

        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = CommunicationProject::deleteCommunication($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = CommunicationProject::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddProjectCommunicationRequest::validateData($data, $project);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = CommunicationProject::insertCommunication($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = CommunicationProject::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    public function syncProjectAllocation(Request $request)
    {
        try {
            if (!$request->ajax()) {
                return redirect('/');
            }
            $response = [];
            $data = $request->all();
            $project = Project::getProjectById($data['projectId']);
            $permission = ViewHelper::checkPermissionEditWorkorder($project);
            $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
            if ($data['projectId'] && $permissionAdd) {
                $listMemProj = ProjectMember::getMemberOfProject($data['projectId']);
                $isNotDeleted = MemberCommunication::checkDeleted($data['projectId']);
                if ($isNotDeleted) {
                    foreach ($isNotDeleted as $memberDeleted) {
                        $member = MemberCommunication::where('employee_id', $memberDeleted)->where('proj_id', $data['projectId'])->whereNull('deleted_at')->whereNull('is_not_sync')->first();
                        if ($member) {
                            $member->deleted_at = Carbon::now()->format('Y-m-d H:i:s');
                            $member->save();                        }
                    }
                }
                if (count($listMemProj)) {
                    $member = [];
                    foreach ($listMemProj as $val) {
                        $checkMember = MemberCommunication::checkMember($data['projectId'], $val->employee_id);
                        if (!$checkMember) {
                            $member[] = [
                                'proj_id' => $data['projectId'],
                                'employee_id' => $val->employee_id,
                                'role' => $val->type_emp,
                                'type' => Task::TYPE_WO_MEMBER_COMMUNICATION,
                                'is_not_sync' => null,
                                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            ];
                        } else {
                            $checkMember->role = $val->type_emp;
                            $checkMember->save();
                        }
                    }
                    MemberCommunication::insert($member);
                }
                DB::commit();
                $response['content'] = MemberCommunication::getContentTable($project, Task::TYPE_WO_MEMBER_COMMUNICATION);
                return response()->json($response);
            }
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    public function syncReportExample(Request $request)
    {
        try {
            if (!$request->ajax()) {
                return redirect('/');
            }
            $response = [];
            $data = $request->all();
            $project = Project::getProjectById($data['projectId']);
            $permission = ViewHelper::checkPermissionEditWorkorder($project);
            $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
            if ($data['projectId'] && $permissionAdd) {
                $status = CommunicationProject::syncExample($data['projectId']);
                if (!$status) {
                    $response['status'] = false;
                    return response()->json($response);
                }
                $response['content'] = CommunicationProject::getContentTable($project);
                return response()->json($response);
            }
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    public function addSecurity(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
        $response = [];
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            $response['message_error'] = Lang::get('project::message.Not found item.');
            $response['status'] = false;
            return response()->json($response);
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = Security::deleteSecurity($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = Security::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddSecurityRequest::validateData($data, $project);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = Security::insertSecurity($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = Security::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    public function addAssumptions(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
        $response = [];
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            $response['message_error'] = Lang::get('project::message.Not found item.');
            $response['status'] = false;
            return response()->json($response);
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];

        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = AssumptionsConstraints::deleteAssumptions($data);
                if ($status) {
                    if ($data['type'] == 'assumptions') {
                        $response['content'] = AssumptionsConstraints::getContentTable($project, Task::TYPE_WO_ASSUMPTIONS);
                    } else {
                        $response['content'] = AssumptionsConstraints::getContentTable($project, Task::TYPE_WO_CONSTRAINTS);
                    }
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddAssumptionConstrainRequest::validateDataAssCons($data);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = AssumptionsConstraints::insertAssumptions($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            if ($data['type'] == Task::TYPE_WO_ASSUMPTIONS) {
                $response['content'] = AssumptionsConstraints::getContentTable($project, Task::TYPE_WO_ASSUMPTIONS);
            } else {
                $response['content'] = AssumptionsConstraints::getContentTable($project, Task::TYPE_WO_CONSTRAINTS);
            }
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    public function addSkillRequest(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
        $response = [];
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            $response['message_error'] = Lang::get('project::message.Not found item.');
            $response['status'] = false;
            return response()->json($response);
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];

        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = SkillRequest::deleteSkillRequest($data);
                if ($status) {
                    $response['content'] = SkillRequest::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddSkillRequest::validateData($data);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = SkillRequest::insertSkillRequest($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $response['content'] = SkillRequest::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /*
     * add external interface
     */
    public function addExternalInterface(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];

        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = ExternalInterface::deleteExternalInterface($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = ExternalInterface::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddExternalInterfaceRequest::validateData($data);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = ExternalInterface::insertExternalInterface($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = ExternalInterface::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /*
     * add external interface
     */
    public function addToolAndInfrastructure(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];

        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = ToolAndInfrastructure::deleteToolAndInfrastructure($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = ToolAndInfrastructure::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddToolAndInfrastructureRequest::validateData($data);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $checkEpiryDate = ToolAndInfrastructure::isCheckEpiryDate($data);

            if (!$checkEpiryDate['error']) {
                $response['status'] = false;
                $response['warning'] = true;
                $response['content'] = $checkEpiryDate['content'];
                return response()->json($response);
            }
            if ($checkEpiryDate) {
                $data = ToolAndInfrastructure::tranformData($data);
            }

            $status = ToolAndInfrastructure::insertToolAndInfrastructure($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = ToolAndInfrastructure::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /*
     * add communication
     */
    public function addCommunication(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];

        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = Communication::deleteCommunication($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = Communication::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddCommunicationRequest::validateData($data);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = Communication::insertCommunication($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = Communication::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /*
     * add deliverable
     */
    public function addDeliverable(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
        $response = [];
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            $response['message_error'] = Lang::get('project::message.Not found item.');
            $response['status'] = false;
            return response()->json($response);
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];

        if (!$permissionAdd) {
            $response['message_error'] = Lang::get('project::message.You don\'t have access');
            $response['status'] = false;
            return response()->json($response);
        }
        if (!Task::checkEditWorkOrder($project->id)) {
            $response['status'] = false;
            $response['message_error'] = Lang::get('project::message.Workorder is processing, you canot submit');
            $response['popuperror'] = 1;
            $response['reload'] = 1;
            Session::flash(
                'messages', [
                    'errors' => [
                        Lang::get('project::message.Workorder is processing, you canot submit'),
                    ]
                ]
            );
            return response()->json($response);
        }
        $response['status'] = true;
        if (isset($data['isDelete'])) {
            $status = ProjDeliverable::deleteDeliverable($data);
            if ($status) {
                $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                $response['content'] = ProjDeliverable::getContentTable($project);
                return response()->json($response);
            }
            $response['status'] = false;
            return response()->json($response);
        }
        $validateAdd = AddDeliverableRequest::validateData($data, $project);
        if ($validateAdd->fails()) {
            $response['message_error'] = $validateAdd->errors();
            $response['status'] = false;
            return response()->json($response);
        }
        $status = ProjDeliverable::insertDeliverable($data);
        if (!$status) {
            $response['status'] = false;
            return response()->json($response);
        }
        $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
        $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
        $response['content'] = ProjDeliverable::getContentTable($project);
        return response()->json($response);
    }

    public static function updateProjectWONote(Request $request)
    {
        $data = $request->all();
        $response = array();
        $response['status'] = true;
        $project = Project::find($data['id']);
        $projectNote = ProjectWONote::getProjectWoNote($data['id']);
        if (!$project || !$projectNote) {
            $response['status'] = false;
            $response['message'] = Lang::get('Not found item.', 'messages', 'team');
            return response()->json($response);
        }

        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionUpdateNote = $permission['permissionEidt'];

        if (!$permissionUpdateNote) {
            $response['status'] = false;
            $response['message'] = Lang::get('You don\'t permission', 'messages', 'project');
            return response()->json($response);
        }
        $response['status'] = $projectNote->updateProjectWONote($data);
        return response()->json($response);
    }

    /*
     * add performance
     */
    public function addPerformance(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];

        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = Performance::deletePerformance($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = Performance::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            if (!isset($data['isSubmit'])) {
                $validateAdd = AddPerformanceRequest::validateData($data);
                if ($validateAdd->fails()) {
                    $response['message_error'] = $validateAdd->errors();
                    $response['status'] = false;
                    return response()->json($response);
                }
            }
            $status = Performance::insertPerformance($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $performance = Performance::getPerformance($data['project_id']);
            $response['id'] = $performance->id;
            $checkEditWorkOrder = Task::checkEditWorkOrder($project->id);
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['duration'] = ViewHelper::getDurationProject($project);
            $response['content'] = view('project::template.content-performance', ['permissionEdit' => $permissionAdd, 'checkEditWorkOrder' => $checkEditWorkOrder, 'performance' => $performance, 'project' => $project])->render();
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /*
     * add quality
     */
    public function addQuality(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];

        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = ProjQuality::deleteQuality($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = ProjQuality::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            if (!isset($data['isSubmit'])) {
                $validateAdd = AddQualityRequest::validateData($data['data']);
                if ($validateAdd->fails()) {
                    $response['message_error'] = $validateAdd->errors();
                    $response['status'] = false;
                    return response()->json($response);
                }
            }
            $status = ProjQuality::insertQuality($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $checkEditWorkOrder = Task::checkEditWorkOrder($project->id);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = ViewHelper::generateContentQuality($data, $project, $checkEditWorkOrder, $permissionAdd);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /**
     * add quality plan
     */
    public static function addQualityPlan(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];
        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;

            $validateAdd = AddQualityPlanRequest::validateData($data);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $status = QualityPlan::getQualityPlanOfProject($data['project_id'], $data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /**
     * add cm plan
     */
    public static function addCMPlan(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];
        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (!isset($data['isDelete'])) {
                $validateAdd = AddCMPlanRequest::validateData($data);
                if ($validateAdd->fails()) {
                    $response['message_error'] = $validateAdd->errors();
                    $response['status'] = false;
                    return response()->json($response);
                }
            }
            $status = CMPlan::insertCMPlan($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            if (!isset($data['isDelete'])) {
                $response['content'] = CMPlan::getContentTable($project);
            }
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /**
     * submit workorder
     * @param request
     */
    public static function submitWorkorder($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $project = Project::getProjectById($id);
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (!Task::checkEditWorkOrder($id)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Workorder is processing, you canot submit');
            $response['reload'] = 1;
            Session::flash(
                'messages', [
                    'errors' => [
                        Lang::get('project::message.Workorder is processing, you canot submit'),
                    ]
                ]
            );
            return response()->json($response);
        }
        $data = [
            'project_id' => $id
        ];
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionSubmit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        if (!$permissionSubmit) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t permission');
            return response()->json($response);
        }
        $mgsSuccess = Lang::get('project::view.Submit workorder success');
        $mgsError = Lang::get('project::view.Submit workorder error');
        $checkSubmit = false;
        $updateTime = false;
        $allNameTab = Task::getAllNameTabWorkorder();
        $tabActiveWO = CookieCore::get('tab-keep-status-workorder');
        if (in_array($project->type, [Project::TYPE_TRAINING, Project::TYPE_RD, Project::TYPE_ONSITE])) {
            $teamAllocation = ProjectMember::checkHasTeamAllocation($project->id);
            if ($teamAllocation) {
                $checkSubmit = true;
            } else {
                $messageWarning = Lang::get('project::view.Have left no team allocation');
                $tabActiveWO = $allNameTab[Task::TYPE_WO_PROJECT_MEMBER];
            }
        } else {
            $deliverable = ProjDeliverable::checkHasDeliverable($project->id);
            if ($deliverable) {
                $checkSubmit = true;
            } else {
                $messageWarning = Lang::get('project::view.Please add deliverable(s)');
                $tabActiveWO = $allNameTab[Task::TYPE_WO_DELIVERABLE];
            }
        }

        $projectDraft = $project->projectChild;
        if (!count($projectDraft)) {
            $projectDraft = $project;
        }
        if (!isset($messageWarning)) {
            $errorTimeDeliverable = ProjDeliverable::checkErrorTime($project->id, $projectDraft);
            if ($errorTimeDeliverable) {
                $messageWarning = Lang::get('project::message.Committed date of deliverable must be between time running of project');
                $checkSubmit = false;
                $updateTime = true;
                $tabActiveWO = $allNameTab[Task::TYPE_WO_DELIVERABLE];
            } else {
                $checkSubmit = true;
            }
        }
        if (!isset($messageWarning)) {
            $errorTimeProjectMember = ProjectMember::checkErrorTime($project->id, $projectDraft);
            if ($errorTimeProjectMember) {
                $messageWarning = Lang::get('project::message.Start date and end date of team allocation must be between time running of project');
                $checkSubmit = false;
                $updateTime = true;
                $tabActiveWO = $allNameTab[Task::TYPE_WO_PROJECT_MEMBER];
            } else {
                $checkSubmit = true;
            }
        }
        if (!isset($messageWarning)) {
            $errorTimeTraining = Training::checkErrorTime($project->id, $projectDraft);
            if ($errorTimeTraining) {
                $messageWarning = Lang::get('project::message.Start date and end date of training plan must be between time running of project');
                $checkSubmit = false;
                $updateTime = true;
                $tabActiveWO = $allNameTab[Task::TYPE_WO_TRANING];
            } else {
                $checkSubmit = true;
            }
        }
        if (!isset($messageWarning)) {
            $errorTimeStage = StageAndMilestone::checkErrorTime($project->id, $projectDraft);
            if ($errorTimeStage) {
                $messageWarning = Lang::get('project::message.Quality gate plan date of stage must be between time running of project');
                $checkSubmit = false;
                $updateTime = true;
                $tabActiveWO = $allNameTab[Task::TYPE_WO_STAGE_MILESTONE];
            } else {
                $checkSubmit = true;
            }
        }
        if (!isset($messageWarning)) {
            $errorStage = StageAndMilestone::checkErrorWithDeliverable($project->id, $projectDraft);
            if ($errorStage) {
                $messageWarning = Lang::get('project::message.Deliverable use stage unregistered');
                $checkSubmit = false;
                $tabActiveWO = $allNameTab[Task::TYPE_WO_DELIVERABLE];
            } else {
                $checkSubmit = true;
            }
        }

        //check update team allocation when status project is close, cancle, postpont
        if ($projectDraft->isPendding()) {
            if (!isset($messageWarning)) {
                $checkTimeTeamAllocation = ProjectMember::checkTimeTeamAllocation($project);
                $lablelState = Project::lablelState();
                if ($checkTimeTeamAllocation) {
                    $messageWarning = Lang::get('project::message.Please update time of team allocation when :state project', ['state' => $lablelState[$projectDraft->state]]);
                    $checkSubmit = false;
                    $updateTime = true;
                    $tabActiveWO = $allNameTab[Task::TYPE_WO_PROJECT_MEMBER];
                } else {
                    $checkSubmit = true;
                }
            }
            if (!isset($messageWarning)) {
                $checkTimeEndProject = Project::checkTimeEndProjectWhenPendding($project);
                $lablelState = Project::lablelState();
                if ($checkTimeEndProject) {
                    $messageWarning = Lang::get('project::message.Please update time end project when :state project', ['state' => $lablelState[$projectDraft->state]]);
                    $tabActiveWO = $allNameTab[Task::TYPE_WO_BASIC_INFO_PROJECT];
                    $updateTime = true;
                    $checkSubmit = false;
                } else {
                    $checkSubmit = true;
                }
            }
        }
        $noteSb = trim(Input::get('sb.note'));
        $keyCookie = 'sb_note_' . $project->id;
        if (!$checkSubmit) {
            $response['error'] = 1;
            $response['message'] = $messageWarning;
            $response['tabActiveWO'] = $tabActiveWO;
            if (($projectDraft->state == Project::STATE_PENDING || $projectDraft->state == Project::STATE_CLOSED)
                && $updateTime) {
                $response['updateTime'] = true;
                $response['messageUpdateTime'] = Lang::get('project::message.Do you want to automatically update time');
            } else {
                $response['updateTime'] = false;
            }
            if ($noteSb) {
                CookieCore::set($keyCookie, $noteSb);
            }
            return response()->json($response);
        }
        if (!$noteSb) {
            $noteSb = CookieCore::get($keyCookie);
        }
        if ($noteSb) {
            $data['sb_note'] = $noteSb;
        }
        CookieCore::forget($keyCookie);
        $status = ProjectWOBase::submitWorkorder($data, $project);
        if (!$status) {
            $response['error'] = 1;
            $response['message'] = $mgsError;
            return response()->json($response);
        }
        Session::flash(
            'messages', [
                'success' => [
                    $mgsSuccess
                ]
            ]
        );
        $response['success'] = 1;
        $response['message'] = $mgsSuccess;
        $response['popup'] = 1;
        $response['refresh'] = URL::route('project::project.edit', ['id' => $id]);
        return response()->json($response);
    }

    /*
     * add member to project
     */
    public function addProjectMember(Request $request)
    {
        $response = [];
        $data = $request->get('item');
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            $response['message'] = Lang::get('project::message.Not found item.');
            $response['status'] = 0;
            return response()->json($response);
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] ||$permission['permissionEditPqa'];
        if (!$permissionAdd) {
            $response['status'] = 0;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        if (!Task::checkEditWorkOrder($project->id)) {
            $response['status'] = 0;
            $response['reload'] = 1;
            Session::flash(
                'messages', [
                    'errors' => [
                        Lang::get('project::message.Workorder is processing, you canot submit'),
                    ]
                ]
            );
            return response()->json($response);
        }
        $response['status'] = 1;
        if ($request->isDelete) {
            if (ProjectMember::getPmByIdProjectMember($data['id']) != Project::getCurrentIdOfPM($data['project_id'])) {
                $member = ProjectMember::deleteProjectMember($data);
                if ($member['delete']) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    //$response['content'] = ProjectMember::getContentTable($project);
                    //$response['effort'] = ViewHelper::generateValueElementInPerformance($data['project_id']);
                    $response['member'] = [
                        'id' => $member['member']->id,
                        'flat_resource' => $member['member']->flat_resource,
                        'parent_id' => $member['member']->parent_id,
                        'status' => $member['member']->status,
                    ];
                    $response['delete'] = 1;
                    $response['approve'] = $member['approve'];
                    if (!$member['approve'] && $member['member']->parent_id) {
                        $response['message'] = Lang::get('project::message.Cancel delete item success');
                    } else {
                        $response['message'] = Lang::get('project::message.Delete item success');
                    }
                    $response['isCheckShowSubmit'] = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    return response()->json($response);
                }
                $response['status'] = 0;
                return response()->json($response);
            }
            $response['message'] = Lang::get('project::view.Must change PM at Basic info');
            $response['status'] = 0;
            return response()->json($response);
        }
        $validateAdd = AddProjectMemberRequest::validateData($data, $project);
        if ($validateAdd->fails()) {
            $response['message'] = $validateAdd->errors()->all();
            $response['status'] = 0;
            return response()->json($response);
        }
        //check change PM of basic infor at team allocation
        if (isset($data['id']) && isset($data['isEdit'])) {
            if (ProjectMember::getPmByIdProjectMember($data['id']) == Project::getCurrentIdOfPM($data['project_id'])
                || ProjectMember::getPmOfParentIdById($data['id']) == Project::getCurrentIdOfPM($data['project_id'])
            ) {
                if ($data['type'] != ProjectMember::TYPE_PM && $data['isEdit']) {
                    $response['message'] = Lang::get('project::view.Must change PM at Basic info');
                    $response['status'] = 0;
                    return response()->json($response);
                }
                if ((ProjectMember::getPmOfParentIdById($data['id'])
                        && $data['employee_id'] != ProjectMember::getPmOfParentIdById($data['id']))
                    || (ProjectMember::getPmByIdProjectMember($data['id'])
                        && $data['employee_id'] != ProjectMember::getPmByIdProjectMember($data['id']))
                ) {
                    $response['message'] = Lang::get('project::view.Must change PM at Basic info');
                    $response['status'] = 0;
                    return response()->json($response);
                }
            }
        }
        $oldEmp = ProjectMember::find($data['id']);
        if ($oldEmp && $oldEmp->employee_id != $data['employee_id']) {
            $response['oldEmpId'] = $oldEmp->employee_id;
        }
        $member = ProjectMember::insertProjectMember($data, $project);
        if (!$member) {
            $response['status'] = 0;
            $response['message'] = Lang::get('project::message.Error system');
            return response()->json($response);
        }
        $response['isCheckShowSubmit'] = ViewHelper::checkSubmitWorkOrder($data['project_id']);
        $response['message'] = Lang::get('project::message.Save data success!');
        $response['member'] = [
            'id' => $member->id,
            'flat_resource' => $member->flat_resource,
            'parent_id' => $member->parent_id,
            'status' => $member->status
        ];
        $response['team'] = ProjDbHelp::getTeamOfEmployees([$member->employee_id]);
        // check and remove employee table employee_project_systena
        $emp = ProjEmployeeSystena::getEmpProj($data['employee_id']);
        if ($emp) {
            $emp->delete();
        }
        //$response['content'] = ProjectMember::getContentTable($project);
        //$response['effort'] = ViewHelper::generateValueElementInPerformance($data['project_id']);
        return response()->json($response);
    }

    /**
     *  update reason change workorder
     */
    public function updateReason(Request $request)
    {
        $isCoo = ViewHelper::isCoo();
        $data = $request->all();
        $response = array();
        $response['status'] = false;
        if (!$isCoo) {
            $response['message'] = Lang::get('project::view.You don\'t permission');
            return response()->json($response);
        }
        $changeWorkorder = ProjectChangeWorkOrder::find($data['id']);
        if (!$changeWorkorder) {
            $response['message'] = Lang::get('project::view.Not found item.');
            return response()->json($response);
        }
        $response['status'] = ProjectChangeWorkOrder::updateReason($changeWorkorder, $data);
        return response()->json($response);
    }

    /*
     * check project existis
     */
    public function checkExistsSourceServer(Request $request)
    {
        $projectId = Input::get('projectId');
        if (!$this->checkPermissionStore($projectId)) {
            return 'false';
        }
        $data = $request->all();
        return SourceServer::checkExists($data);
    }

    /**
     * generate select leader
     * @return view
     */
    public function generateSelectLeader(Request $request)
    {
        $response = array();
        $response['status'] = true;
        $data = $request->all();
        $validate = EditBasicInforProjectRequest::validateData($data);
        if ($validate->fails()) {
            $response['message_error'] = $validate->errors();
            $response['status'] = false;
            $response['content'] = view('project::template.content-select-leader', [
                'reset' => true,
            ])->render();
            return response()->json($response);
        }
        if ($data['project_id']) {
            $permission = ViewHelper::checkPermissionEditWorkorder($data['project_id']);
            $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
            $project = Project::getProjectById($data['project_id']);
        } else {
            $permissionEdit = true;
            $project = null;
        }
        $data['value'] = (array)$data['value'];
        $data['value'] = array_filter($data['value']);
        if (count($data['value'])) {
            $allTeam = $data['value'];
        } else {
            $allTeam = [];
        }
        if ($project) {
            $result = Project::updateTeam($data, $project);
            $leaderOld = Employee::find($project->leader_id);
            $response['isCheckShowSubmit'] =
                ViewHelper::checkSubmitWorkOrder($data['project_id']);
            // Get information group leader
            $groupleaderInformation = $project->getInformationGroupLeader();
        } else {
            $result['isChange'] = false;
            $result['status'] = true;
            $leaderOld = null;
            $response['isCheckShowSubmit'] = false;
        }
        $leaders = EmployeeRole::getLeadAndSub($allTeam, true);
        // Check if group leader not exist in $leaders
        if (!empty($groupleaderInformation) && !array_key_exists($groupleaderInformation->id, $leaders)) {
            $leaders[$groupleaderInformation->id] = [
                'id' => $groupleaderInformation->id,
                'name' => $groupleaderInformation->name,
                'email' => $groupleaderInformation->email
            ];
        }

        $leaderIds = array_keys($leaders);
        if ($project) {
            $projectDraft = $project->projectChild;
            if ($projectDraft) {
                $idSelected = $projectDraft->leader_id;
            } else {
                $idSelected = $project->leader_id;
            }
        } else {
            $idSelected = null;
        }
        if (!in_array($idSelected, $leaderIds)) {
            $idSelected = reset($leaderIds);
        }
        $response['content'] = view('project::template.content-select-leader', [
            'checkEdit' => $data['checkEdit'],
            'permissionEdit' => $permissionEdit,
            'leaders' => $leaders,
            'leaderOld' => $leaderOld,
            'project' => $project,
            'idLeader' => $idSelected
        ])->render();
        $response['result'] = $result;
        return response()->json($response);
    }

    public function getContentTable(Request $request)
    {
        $data = $request->all();
        $response = array();
        $response['status'] = true;
        $project = Project::getProjectById($data['projectId']);
        if (!$project) {
            $response['status'] = false;
            return response()->json($response);
        }
        $allTab = Task::getAllNameTabWorkorder();
        $type = array_search($data['type'], $allTab);

        switch ($type) {
            case Task::TYPE_WO_CRITICAL_DEPENDENCIES:
                $response['content'] = CriticalDependencie::getContentTable($project);
                break;
            case Task::TYPE_WO_ASSUMPTION_CONSTRAINS:
                $response['content'] = AssumptionConstrain::getContentTable($project);
                break;
            case Task::TYPE_WO_RISK:
                $response['content'] = Risk::getContentTable($project);
                break;
            case Task::TYPE_WO_ISSUE:
                $response['content'] = Task::getContentTable($project);
                break;
            case Task::TYPE_WO_STAGE_MILESTONE:
                $response['content'] = StageAndMilestone::getContentTable($project);
                break;
            case Task::TYPE_WO_TRANING:
                $response['content'] = Training::getContentTable($project);
                break;
            case Task::TYPE_WO_ASSUMPTIONS:
                $response['content'] = AssumptionsConstraints::getContentTable($project, Task::TYPE_WO_ASSUMPTIONS);
                break;
            case Task::TYPE_WO_CONSTRAINTS:
                $response['content'] = AssumptionsConstraints::getContentTable($project, Task::TYPE_WO_CONSTRAINTS);
                break;
            case Task::TYPE_WO_EXTERNAL_INTERFACE:
                $response['content'] = ExternalInterface::getContentTable($project);
                break;
            case Task::TYPE_WO_COMMINUCATION:
                $response['content'] = CommunicationProject::getContentTable($project);
                break;
            case Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE:
                $response['content'] = ToolAndInfrastructure::getContentTable($project);
                break;
            case Task::TYPE_WO_DELIVERABLE:
                $response['content'] = ProjDeliverable::getContentTable($project);
                break;
            case Task::TYPE_WO_PERFORMANCE:
                $response['content'] = Performance::getContentTable($project);
                break;
            case Task::TYPE_WO_QUALITY:
                $response['content'] = ProjQuality::getContentTable($project);
                break;
            case Task::TYPE_WO_PROJECT_MEMBER:
                $response['data']['member'] = ProjectMember::getAllMemberAvai($project);
                $response['data']['lang'] = Programs::getListOption();
                $response['data']['type'] = ProjectMember::getTypeMember();
                $response['data']['team'] = ProjDbHelp::getTeamOfMembers($response['data']['member']);
                break;
            case Task::TYPE_WO_QUALITY_PLAN:
            $response['content'] = QualityPlan::getContentTable($project);
                break;
            case Task::TYPE_WO_CM_PLAN:
                $response['content'] = CMPlan::getContentTable($project);
                break;
            case Task::TYPE_WO_CHANGE_WO:
                $response['content'] = ProjectChangeWorkOrder::getContentTable($project);
                break;
            case Task::TYPE_WO_PROJECT_LOG:
                $response['content'] = ProjectLog::getContentTable($project);
                break;
            case Task::TYPE_WO_DEVICES_EXPENSE:
                $response['content'] = DevicesExpense::getContentTable($project);
                break;
            default:
                break;
        }

        return response()->json($response);
    }

    /**
     * view log workorder ajax
     */
    public function logListAjax($id)
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
        $response['html'] = ViewLaravel::make('project::components.project_activity', [
            'projectLogs' => ProjectLog::getAllProjectLog($id)
        ])->render();
        $response['success'] = 1;
        return response()->json($response);
    }

    /**
     * check has stage and milestone by project id
     * @return json
     */
    public function checkHasStageMilestone(Request $request)
    {
        $data = $request->all();
        $response = array();
        $response['status'] = false;
        $response['notFound'] = false;
        $response['dontPermission'] = false;
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            $response['notFound'] = true;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        if ($permissionAdd) {
            $checkHasStageMilestone = StageAndMilestone::getStageAndMilestoneOfProject($data['project_id'])->count();
            if (!$checkHasStageMilestone) {
                $response['status'] = true;
                $response['message'] = Lang::get('project::message.Please fill stage and milestone before');
            } else {

            }
            return response()->json($response);
        } else {
            $response['dontPermission'] = true;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
    }

    /**
     * edit project basic information
     * @param array
     * @return json
     */
    public static function editBasicInfo(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
        $response = [];
        $data = $request->all();
        $typeSubmit = isset($data['is_coo']) ? $data['is_coo'] : '';
        $typeCOO = false;
        $hasPerApproveProductionCost = Permission::getInstance()->isAllow('project::project.approved-production-cost');
        if ($typeSubmit && $typeSubmit == 'is_coo' && $hasPerApproveProductionCost) {
            $typeCOO = true;
        }
        $project = Project::getProjectById($data['project_id']);

        $currentUser = Permission::getInstance()->getEmployee();
        $isAllowUpdateApproveCostPrice = true;
//        $isAllowUpdateApproveCostPrice = $currentUser->id == $project->leader_id
//            || Permission::getInstance()->isScopeCompany()
//            || SaleProject::isSalePersonOfProjs($currentUser->id, $project->id);
        $notFound = false;
        if (!$project) {
            $notFound = true;
        } elseif ($project->status != Project::STATUS_APPROVED) {
            $notFound = true;
        }
        if ($notFound) {
            $response['message_error'] = Lang::get('project::message.Not found item.');
            $response['status'] = false;
            return response()->json($response);
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        if (isset($data['name']) &&  $data['name'] !== 'cost_approved_production') {
            if (!$permissionEdit) {
                $response['message_error'] = Lang::get('project::message.You don\'t have access');
                $response['status'] = false;
                return response()->json($response);
            }
        }

        $checkEditWorkOrderReview = false;
        if (isset($data['name']) && isset($data['name']) == 'cost_approved_production') {
            $checkEditWorkOrderReview = true;
        }

        if (!Task::checkEditWorkOrder($project->id) && !$checkEditWorkOrderReview) {
            $response['status'] = false;
            $response['message_error'] = Lang::get('project::message.Workorder is processing, you canot submit');
            $response['popuperror'] = 1;
            $response['reload'] = 1;
            Session::flash(
                'messages', [
                    'errors' => [
                        Lang::get('project::message.Workorder is processing, you canot submit'),
                    ]
                ]
            );
            return response()->json($response);
        }
        if ($data['name'] == 'state' && (!Task::isProjectClose($project->id) || count(Css::getCssNotMakeByProject($project->id))) && ($data['value'] == Project::STATE_CLOSED)) {
            $response['status'] = false;
            $response['message_error'] = Lang::get('project::message.You need close tasks before close project!');
            $response['popuperror'] = 1;
            // $response['reload'] = 1;
            Session::flash(
                'messages',
                [
                    'errors' => [
                        Lang::get('project::message.You need close tasks before close project!'),
                    ]
                ]
            );

            return response()->json($response);
        }
        $response['status'] = true;
        $validate = EditBasicInforProjectRequest::validateData($data);

        if ($validate->fails()) {
            $response['message_error'] = $validate->errors();
            $response['status'] = false;
            return response()->json($response);
        }
        if ($data['name'] == 'cost_approved_production' && !isset($data['datadetai']) && !isset($data['billableDetail'])) {
            $approveCostValue = $data['value'];
            $projectDraft = ProjectApprovedProductionCost::getProjectDraft($project);
            $typeMM = $projectDraft ? $projectDraft->type_mm : $project->type_mm;
            if ($typeMM == Project::MD_TYPE) {
                $approveCostValue = $approveCostValue / 20;
            }
            $totalApproveCostDetail = ProjectApprovedProductionCost::where('project_id', '=', $project->id)->sum('approved_production_cost');
            if ($approveCostValue < $totalApproveCostDetail) {
                $response['message_error'] = [
                    $data['name'] => [trans('project::message.The Approve Production Cost is less than total Approve Production Cost Detail')]
                ];
                $response['status'] = false;
                $response['data'] = [
                    'total_cost_approve_detail' => $totalApproveCostDetail
                ];
                return response()->json($response);
            }
        }
        DB::beginTransaction();
        try {
            if (isset($data['isQuality'])) {
                $result = ProjQuality::editBasicInfo($data, $project);
            } elseif (isset($data['isScope'])) {
                $result = ProjectMeta::editBasicInfo($data, $project);
                //Send mail to relaters of project if change customer require in scope object
                if ($data['name'] == ProjectMeta::CUSTOMER_REQUIRE_FIELD) {
                    $relaters = Project::getRelatersOfProject($project);
                    if (!empty($relaters)) {
                        $projectUrl = route("project::project.edit", ['id' => $project->id]) . '#scope';
                        $subject = Lang::get("project::view.[Workoder] Customer contract requirement has been updated");
                        foreach ($relaters as $emp) {
                            $dataMail = [
                                'email' => $emp->email,
                                'name' => $emp->name,
                                'url' => $projectUrl,
                                'projectName' => $project->name,
                            ];
                            $emailQueue = new EmailQueue();
                            $emailQueue->setTo($emp->email, $emp->name)
                                ->setSubject($subject)
                                ->setTemplate("project::emails.customer_required_relater", $dataMail)
                                ->save();
                        }
                        \RkNotify::put($relaters->lists('id')->toArray(), $subject, $projectUrl, ['category_id' => RkNotify::CATEGORY_PROJECT]);
                    }
                }
            } elseif (isset($data['isSourceServer'])) {
                $result = SourceServer::editBasicInfo($data, $project);
            } elseif ($request->input('name') == 'prog_langs') {
                $return = ProjectProgramLang::insertItems($project,
                    (array)$request->input('value'));
                if (is_array($return)) {
                    $result = $return;
                    $response = $return;
                } else {
                    $result['status'] = true;
                }
            } else if ($request->input('name') == 'kind_id') {
                Project::where('id', $project->id)->orWhere('parent_id', $project->id)->update(['kind_id' => $request->input('value')]);

                $result['status'] = true;
            }
            else {
                $result = Project::editBasicInfo($data, $project);
            }
            // Update or insert table project_approved_production_cost
            if (isset($data['datadetai']) && $data['datadetai'] != null) {
                $totalApproveCostDetail = ProjectApprovedProductionCost::updateProjectProductionCost($data['datadetai'], $project, $isAllowUpdateApproveCostPrice, $typeCOO);
                if (is_array($totalApproveCostDetail)) {
                    if ($totalApproveCostDetail['status']) {
                        $response['data'] = [
                            'total_cost_approve_detail' => $totalApproveCostDetail['total_approve_production_cost']
                        ];
                    } else {
                        $response['message_error'] = [
                            $data['name'] => [trans('project::message.The Approve Cost Detail invalid', [
                                'approve_production_cost' => $totalApproveCostDetail['approve_production_cost'],
                                'type_mm' => $totalApproveCostDetail['type_mm'],
                                'total_approve_production_cost' => $totalApproveCostDetail['total_approve_production_cost']
                            ])]
                        ];
                        $response['status'] = false;
                        $response['data'] = [
                            'total_cost_approve_detail' => $totalApproveCostDetail['total_approve_production_cost']
                        ];

                        return response()->json($response);
                    }
                }
            }
            if (isset($data['billableDetail']) && $data['billableDetail'] != null) {
                ProjectBillableCost::updateProjectBillableCostDetail($data['billableDetail'], $data['project_id']);
            }

            DB::commit();
        } catch (Exception $ex) {
            $response['message_error'] = Lang::get('project::message.Error system');
            $response['status'] = false;
            Log::info($ex);
            DB::rollback();
            return response()->json($response);
        }
        $response['typeCOO'] = $typeCOO;
        $projectAppProductCost = ProjectApprovedProductionCost::getProjectApprpveProductionCost($project->id, true);
        if ($result['status']) {
            $response['result'] = $result;
            $response['dataRespone'] = $projectAppProductCost;
            $response['isCheckShowSubmit'] =
                ViewHelper::checkSubmitWorkOrder($data['project_id']);
            return response()->json($response);
        }
        $response['status'] = false;
        if (!isset($response['message_error'])) {
            $response['message_error'] = [];
        }
        return response()->json($response);
    }

    /**
     * check is change satus wo
     * @param array
     * @return json
     */
    public static function checkIsChangeStatusWo(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
        $response = [];
        $data = $request->all();
        $projectId = $data['project_id'];
        $project = Project::getProjectById($projectId);
        $response['status'] = false;
        if (!$project) {
            return response()->json($response);
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];
        if ($permissionEdit) {
            $statusWo = (int)$data['status'];
            $taskWOApproved = Task::getTaskWaitingApproveByType($projectId, Task::TYPE_WO);
            if ($taskWOApproved) {
                if ($taskWOApproved->status != $statusWo) {
                    $response['status'] = true;
                    return response()->json($response);
                }
            } else {
                $checkHasTaskWorkorderApproved = Task::checkHasTaskWorkorderApproved($projectId, Task::TYPE_WO);
                if ($checkHasTaskWorkorderApproved) {
                    if ($statusWo != Task::STATUS_APPROVED) {
                        $response['status'] = true;
                        return response()->json($response);
                    }
                }
            }
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    /**
     * Form edit risk
     *
     * @param Request $request
     */
    public function editRisk(Request $request)
    {
        $data = $request->all();
        $permissionEdit = true;
        if (isset($data['riskId'])) {
            $riskId = $data['riskId'];
            $riskInfo = Risk::getById($riskId);
            if (!$riskInfo) {
                return;
            }
            $projectId = $riskInfo->project_id;
            $project = Project::find($projectId);
            $permission = Pview::checkPermissionEditWorkorder($project);
            $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];
        } else {
            if (!isset($data['projectId'])) {
                return View('project::components.risk-detail',
                    [
                        'permissionEdit' => $permissionEdit,
                        'isWOAddRisk' => true,
                    ]
                );
            }
            $projectId = $data['projectId'];
            $riskInfo = null;
        }
        $methods = Risk::getMethods();
        $results = Risk::getResults();
        return View('project::components.risk-detail',
            [
                'riskInfo' => $riskInfo,
                'methods' => $methods,
                'results' => $results,
                'projectId' => $projectId,
                'permissionEdit' => $permissionEdit,
                'redirectUrl' => $request->get('redirectUrl'),
                'isWOAddRisk' => true,
                'riskMitigation' => isset($riskId) ? RiskAction::getByType(RiskAction::TYPE_RISK_MITIGATION, $riskId) : null,
                'project' => Project::getTeamInChargeOfProject($projectId)
            ]
        );
    }

    /**
     * Form edit nc
     *
     * @param Request $request
     */
    public function editNc(Request $request)
    {
        $data = $request->all();
        $ncId = $data['ncId'];
        $ncInfo = Task::getById($ncId);
        if (!$ncInfo || $ncInfo->type != Task::TYPE_COMPLIANCE) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $project = $ncInfo->getProject();
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        // check permission view
        if (!ViewProject::isAccessViewProject($project)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You dont have access');
            return response()->json($response);
        }
        $assignees = TaskAssign::getAssignee($ncInfo->id);
        $accessEditTask = ViewProject::isAccessEditTask($project, $ncInfo->type, $ncInfo);
        $relaters = Project::getRelatersOfProject($project, null, true);
        if ($accessEditTask ||
            in_array(Permission::getInstance()->getEmployee()->id, $assignees)
        ) {
            $accessEditTask = true;
            $taskStatusAll = Task::getStatusTypeNormal();
        } else {
            $accessEditTask = false;
            $taskStatusAll = [];
        }
        $viewMode = false;
        return View('project::components.nc-detail',
            [
                'projectId' => $project->id,
                'ncInfo' => $ncInfo,
                'project' => Project::getTeamInChargeOfProject($project->id),
                'taskCommentList' => TaskComment::getGridData($ncId),
                'taskHistoryList' => TaskHistory::getGridData($ncId),
                'taskNcmRequest' => TaskNcmRequest::findNcmFollowTask($ncInfo,
                    ['findRequester' => true]),
                'taskAssign' => TaskNcmRequest::findNcmAssign($ncInfo),
                'accessEditTask' => $accessEditTask,
                'teamsOptionAll' => $accessEditTask ?
                    TeamList::toOption(null, true, false) : null,
                'teamsSelected' => $accessEditTask ? TaskTeam::getNcmTeams($ncInfo) :
                    TaskTeam::getNcmTeamAndName($ncInfo),
                'viewMode' => $viewMode,
                'taskStatusAll' => $taskStatusAll,
                'curEmp' => Permission::getInstance()->getEmployee(),
                'relaters' => $relaters,
                'attachs' => isset($ncId) ? RiskAttach::getAttachs($ncId, RiskAttach::TYPE_NC) : null,
            ]
        );
    }

    public function deleteFile(Request $request) {
        $fileId = RiskAttach::deleteFileAttach($request);
        return response()->json($fileId);
    }

    /**
     * Edit Issue
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function editIssue(Request $request)
    {
        $dataIssue = $request->all();
        $curEmp = Permission::getInstance()->getEmployee();
        if (isset($dataIssue['taskId'])) {
            $issueId = $dataIssue['taskId'];
            $issueInfo = Task::getById($issueId);
            if (!$issueInfo) {
                return;
            }
            $projectId = $issueInfo->project_id;
        } else {
            if (!isset($dataIssue['projectId'])) {
                return View('project::components.issue-detail',
                    [
                        'curEmp' => $curEmp,
                    ]);
            }
            $projectId = $dataIssue['projectId'];
            $issueInfo = null;
        }
        $methods = Risk::getMethods();
        $results = Risk::getResults();
        return View('project::components.issue-detail',
        [
            'issueInfo' => $issueInfo,
            'projectId' => $projectId,
            'methods' => $methods,
            'results' => $results,
            'curEmp' => $curEmp,
            'issueMitigation' => isset($issueId) ? TaskAction::getByType(TaskAction::TYPE_ISSUE_MITIGATION, $issueId) : null,
            'project' => Project::getTeamInChargeOfProject($projectId)
        ]);
    }

    public function getFormNC(Request $request)
    {
        $dataIssue = $request->all();
        $curEmp = Permission::getInstance()->getEmployee();
        $projectId = null;
        $ncInfo = null;
        if (isset($dataIssue['projectId'])) {
            $projectId = $dataIssue['projectId'];
        }
        if (isset($dataIssue['isTabWO'])) {
            $projs = Project::find($projectId);
            $relaters = Project::getRelatersOfProject($projs, null, true);
        }

        return view('project::components.nc-detail',
        [
            'ncInfo' => $ncInfo,
            'projectId' => $projectId,
            'curEmp' => $curEmp,
            'project' => Project::getTeamInChargeOfProject($projectId),
            'relaters' => isset($relaters) ? $relaters : null,
        ]);
    }

    public function getFormOpportunity(Request $request)
    {
        $dataIssue = $request->all();
        $curEmp = Permission::getInstance()->getEmployee();
        $projectId = null;
        if (isset($dataIssue['projectId'])) {
            $projectId = $dataIssue['projectId'];
        }

        return view('project::components.opportunity-detail',
        [
            'oopInfo' => null,
            'projectId' => $projectId,
            'curEmp' => $curEmp,
            'project' => Project::getTeamInChargeOfProject($projectId),
        ]);
    }

    public function getFormViewOpportunity(Request $request)
    {
        $dataIssue = $request->all();
        $curEmp = Permission::getInstance()->getEmployee();
        $projectId = $dataIssue['projectId'];
        $oopInfo = Task::getById($dataIssue['oopId']);

        return view('project::components.opportunity-detail',
        [
            'oopInfo' => $oopInfo,
            'projectId' => $projectId,
            'curEmp' => $curEmp,
            'project' => Project::getTeamInChargeOfProject($projectId),
            'viewMode' => 1,
        ]);
    }

    public function saveIssue(Request $request)
    {
        $dataIssue = $request->all();
        $taskMitigation = $request->get('issue');
        $curEmp = Permission::getInstance()->getEmployee();
        $requiredArray = ['title', 'employee_owner', 'priority', 'team', 'type', 'solution', 'content', 'cause'];
        $rule = array_fill_keys($requiredArray, 'required');
        $validator = Validator::make($dataIssue, $rule);
        if ($validator->fails()) {
            return back()->with('messages', [
                'errors' => [
                    Lang::get('project::message.Error input data!'),
                ]
            ]);
        }
        $dataIssue['created_by'] = $curEmp->id;
        $dataIssue['status'] = Task::STATUS_NEW;
        DB::beginTransaction();
        try {
            $issue = Task::store($dataIssue);
            TaskTeam::delByIssue($issue->id);
            if (isset($dataIssue['id'])) {
                TaskAssign::delByIssue($issue->id);
            }

            $taskAssign = ([
                [
                    'task_id' => $issue->id,
                    'employee_id' => $dataIssue['employee_owner'],
                    'role' => TaskAssign::ROLE_OWNER,
                    'status' => TaskAssign::STATUS_NO,
                ],
                [
                    'task_id' => $issue->id,
                    'employee_id' => $dataIssue['reporter'],
                    'role' => TaskAssign::ROLE_REPORTER,
                    'status' => TaskAssign::STATUS_NO,
                ]
            ]);
            TaskAssign::insert($taskAssign);

            $taskTeam = [
                'task_id' => $issue->id,
                'team_id' => $dataIssue['team'],
            ];

            DB::table('task_teams')->insert($taskTeam);

            if (isset($dataIssue['attach'])) {
                $valid = Validator::make($dataIssue, [
                    'attach.*' => 'file|mimes:doc,docx,xlsx,pdf,png,jpg,gif,jpeg',
                ]);
                if ($valid->fails()) {
                    return redirect()->back()->withErrors($valid)->withInput();
                }
                $messagesError = [
                    'success' => [
                        Lang::get('project::message.Error max size file'),
                    ]
                ];
                foreach ($dataIssue['attach'] as $attach) {
                    if (in_array($attach->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                        if ($attach->getSize() >= 2048*1000) {
                            return redirect()->route('project::issue.detail', ['id' => $issue->id])->with('messages', $messagesError);
                        }
                    } else {
                        if ($attach->getSize() >= 5120*1000) {
                            return redirect()->route('project::issue.detail', ['id' => $issue->id])->with('messages', $messagesError);
                        }
                    }
                }

                RiskAttach::uploadFiles($issue->id, $dataIssue['attach'], RiskAttach::TYPE_ISSUE);
            }

            if (!empty($dataIssue)) {
                $project = Project::find($dataIssue['project_id']);
                $relaters = Project::getRelatersOfProjectByIssue($project, $dataIssue);
                if (!empty($relaters)) {
                    foreach ($relaters as $emp) {
                        $this->sendMailRelatersForIssue($emp->email, $emp->name, $project, $dataIssue, $curEmp, isset($dataIssue['id']));
                    }
                    //put notification
                    $subject = !isset($dataIssue['id']) ? Lang::get("project::view.[Workoder] A issue has been created") :
                        $subject = Lang::get("project::view.[Workoder] A issue has been edit");
                    \RkNotify::put($relaters->lists('id')->toArray(), $subject, route("project::project.edit", ['id' => $project->id]) . '#issue', ['category_id' => RkNotify::CATEGORY_PROJECT]);
                }
                $messages = [
                    'success' => [
                        Lang::get('project::message.Save issue success'),
                    ]
                ];
                TaskAction::delByIssue($issue->id);
                if (!empty($taskMitigation)) {
                    $dataMiti = [];
                    foreach ($taskMitigation as $itemMiti) {
                        $dataMiti[] = [
                            'content' => $itemMiti['content'],
                            'status' => $itemMiti['status'],
                            'duedate' => $itemMiti['duedate'],
                            'assignee' => $itemMiti['assignee'],
                            'issue_id' => $issue->id,
                            'type' => TaskAction::TYPE_ISSUE_MITIGATION,
                        ];
                    }
                    if (count($dataMiti)) {
                        TaskAction::insert($dataMiti);
                    }
                }
            } else {
                $messages = [
                    'success' => [
                        Lang::get('project::message.Save issue error'),
                    ]
                ];
            }
            DB::commit();
            if ($redirectUrl = $request->get('redirectUrl')) {
                return redirect()->to($redirectUrl)->with('messages', $messages);
            }
            return redirect()->route('project::issue.detail', ['id' => $issue->id])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollBack();
            \Log::info($ex);
            $messages = [
                'success' => [
                    Lang::get('project::message.Save issue error'),
                ]
            ];
            return redirect()->route('project::project.edit', ['id' => $dataIssue['project_id']])->with('messages', $messages);
        }
    }

    public function downloadFile($fileId)
    {
        $file = RiskAttach::findOrFail($fileId);
        $path = storage_path('app/public/' . $file->path);
        $headers = ['Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        return response()->download($path, null, $headers);
    }

    public function sendMailRelatersForIssue($email, $name, $project, $dataIssue, $curEmp, $isCreated = false)
    {
        $data = [
            'email' => $email,
            'name' => $name,
            'url' => route("project::project.edit", ['id' => $project->id]) . '#issue',
            'isCreated' => $isCreated,
            'projectName' => $project->name,
            'issueContent' => $dataIssue['title'],
            'creator' => $curEmp->name,
        ];
        if (!$isCreated) {
            $subject = Lang::get("project::view.[Workoder] A issue has been created");
        } else {
            $subject = Lang::get("project::view.[Workoder] A issue has been edit");
        }
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($email, $name)
            ->setSubject($subject)
            ->setTemplate("project::emails.issue_relater", $data)
            ->save();
    }


    /**
     * Save risk action
     *
     * @param Request $request
     */
    public function saveRisk(Request $request)
    {
        $data = $request->all();
        $curEmp = Permission::getInstance()->getEmployee();
        $requiredArray = ['content', 'weakness', 'due_date'];
        if (!isset($data['owner']) || $data['owner'] === '') {
            array_push($requiredArray, 'team_owner');
        }
        $rule = array_fill_keys($requiredArray, 'required');
        $validator = Validator::make($data, $rule);

        if ($validator->fails()) {
            return back()->with('messages', [
                'errors' => [
                    Lang::get('project::message.Error input data!'),
                ]
            ]);
        }
        $data['created_by'] = $curEmp->id;
        $risk = Risk::store($data);
        if (!empty($risk)) {
            //Send mail to relaters of project
            $project = Project::find($data['project_id']);
            $relaters = Project::getRelatersOfProject($project, $risk);
            if (!empty($relaters)) {
                foreach ($relaters as $emp) {
                    $this->sendMailRelaters($emp->email, $emp->name, $project, $risk, isset($data['id']));
                }
                //put notification
                $subject = !isset($data['id']) ? Lang::get("project::view.[Workoder] A risk has been created") :
                    $subject = Lang::get("project::view.[Workoder] A risk has been edit");
                \RkNotify::put($relaters->lists('id')->toArray(), $subject, route("project::project.edit", ['id' => $project->id]) . '#risk', ['category_id' => RkNotify::CATEGORY_PROJECT]);
            }

            if (isset($data['attach'])) {
                $valid = Validator::make($data, [
                    'attach.*' => 'file|mimes:doc,docx,xlsx,pdf,png,jpg,gif,jpeg|max:5120',
                ]);
                if ($valid->fails()) {
                    return redirect()->back()->withErrors($valid)->withInput();
                }
                $messagesError = [
                    'success' => [
                        Lang::get('project::message.Error max size file'),
                    ]
                ];
                foreach ($data['attach'] as $attach) {
                    if (in_array($attach->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                        if ($attach->getSize() >= 2048*1000) {
                            return redirect()->route('project::report.risk.detail', ['id' => $risk->id])->with('messages', $messagesError);
                        }
                    } else {
                        if ($attach->getSize() >= 5120*1000) {
                            return redirect()->route('project::report.risk.detail', ['id' => $risk->id])->with('messages', $messagesError);
                        }
                    }
                }
                RiskAttach::uploadFiles($risk->id, $data['attach'], RiskAttach::TYPE_RISK);
            }

            $messages = [
                'success' => [
                    Lang::get('project::message.Save risk success'),
                ]
            ];
            RiskAction::delByRisk($risk->id);
            if (!empty($data['task'])) {
                $dataMiti = [];
                foreach ($data['task'] as $itemMiti) {
                    $dataMiti[] = [
                        'content' => $itemMiti['content'],
                        'status' => $itemMiti['status'],
                        'duedate' => $itemMiti['duedate'],
                        'assignee' => $itemMiti['assignee'],
                        'risk_id' => $risk->id,
                        'type' => RiskAction::TYPE_RISK_MITIGATION,
                    ];
                }
                if (count($dataMiti)) {
                    RiskAction::insert($dataMiti);
                }
            }

            if (!empty($data['contigency'])) {
                $dataConti = [];
                foreach ($data['contigency'] as $itemConti) {
                    $dataConti[] = [
                        'content' => $itemConti['content'],
                        'status' => $itemConti['status'],
                        'duedate' => $itemConti['duedate'],
                        'assignee' => $itemConti['assignee'],
                        'risk_id' => $risk->id,
                        'type' => RiskAction::TYPE_RISK_CONTIGENCY,
                    ];
                }
                if (count($dataConti)) {
                    RiskAction::insert($dataConti);
                }
            }
        } else {
            $messages = [
                'success' => [
                    Lang::get('project::message.Save risk error'),
                ]
            ];
        }
        if ($redirectUrl = $request->get('redirectUrl')) {
            return redirect()->to($redirectUrl)->with('messages', $messages);
        }
        if (isset($data['redirectDetailRisk'])) {
            return redirect()->to($data['redirectDetailRisk'])->with('messages', $messages);
        }
        return redirect()->route('project::report.risk.detail', ['id' => $risk->id])->with('messages', $messages);
    }

    /**
     * Send mail to relaters of project after created/edit risk
     *
     * @param string $email
     * @param string $name
     * @param Project $project
     * @param Risk $risk
     * @param boolean $isCreated
     * @return void
     */
    public function sendMailRelaters($email, $name, $project, $risk, $isCreated = false)
    {
        $data = [
            'email' => $email,
            'name' => $name,
            'url' => route("project::project.edit", ['id' => $project->id]) . '#risk',
            'isCreated' => $isCreated,
            'projectName' => $project->name,
            'riskContent' => $risk->content
        ];
        if (!$isCreated) {
            $subject = Lang::get("project::view.[Workoder] A risk has been created");
        } else {
            $subject = Lang::get("project::view.[Workoder] A risk has been edit");
        }
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($email, $name)
            ->setSubject($subject)
            ->setTemplate("project::emails.risk_relater", $data)
            ->save();
    }

    /**
     * search project by ajax
     */
    public function listSearchAjax($type = null)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            Project::searchAjax(Input::get('q'), [
                'page' => Input::get('page'),
                'typeExclude' => $type,
            ])
        );
    }

    /**
     * search TeamMember by ajax
     */
    public function listSearchTeamMemberByAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            ProjectMember::searchAjax(Input::get('q'), Input::get('projectId'))
        );
    }

    /**
     * get all PM of Project using Ajax
     */
    public function getALlPmOfProjectByAjax(Request $request)
    {
        $data = $request->all();
        $project_id = $data['project_id'];
        $project = Project::find($project_id);
        $allPmActive = $project->getAllPMActive();
        $currentPm = Project::getCurrentIdOfPM($project_id);
        return response()->json(array('allPmActive' => $allPmActive, 'currentPm' => $currentPm));
    }

    /**
     * ajax popover critical and assumption
     */
    public function popoverGetAttribute(Request $request)
    {
        $data = $request->all();
        $value = '';
        if ($data['type'] && $data['type'] == CriticalDependencie::getTableName()) {
            $value = CriticalDependencie::getValueAttribute($data['id'], $data['attribute']);
        } else if ($data['type'] && $data['type'] == AssumptionConstrain::getTableName()) {
            $value = AssumptionsConstraints::getValueAttribute($data['id'], $data['attribute']);
        } else if ($data['type'] && $data['type'] == Security::getTableName()) {
            $value = Security::getValueAttribute($data['id'], $data['attribute']);
        } else if ($data['type'] && $data['type'] == SkillRequest::getTableName()) {
            $value = SkillRequest::getValueAttribute($data['id'], $data['attribute']);
        } else if ($data['type'] && $data['type'] == MemberCommunication::getTableName()) {
            $value = MemberCommunication::getValueAttribute($data['id'], $data['attribute']);
        } else if ($data['type'] && $data['type'] == CustomerCommunication::getTableName()) {
            $value = CustomerCommunication::getValueAttribute($data['id'], $data['attribute']);
        } else if ($data['type'] && $data['type'] == CommunicationProject::getTableName()) {
            $value = CommunicationProject::getValueAttribute($data['id'], $data['attribute']);
        }
        return response()->json($value);
    }

    /**
     * update time of end date, team allocation, stages deliverable of project
     */
    public function updateTime(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
        $projectId = $request->get('projectId');
        $project = Project::find($projectId);
        $projectDraft = Project::where('parent_id', $projectId)
            ->orderBy('id', 'desc')->first();
        $currentTime = Carbon::now()->format('Y-m-d');
        $response = [];
        DB::beginTransaction();
        try {
            /* check end_at of project > current time  end update*/
            if (($project && $project->end_at > $currentTime)
                || ($projectDraft && $projectDraft->end_at > $currentTime)) {
                $data = [
                    'name' => 'end_at',
                    'value' => $currentTime,
                    'isApproved' => true
                ];
                Project::editBasicInfo($data, $project);
            }
            if (($project && $project->start_at > $currentTime)
                || ($projectDraft && $projectDraft->start_at > $currentTime)) {
                $data = [
                    'name' => 'start_at',
                    'value' => $currentTime,
                    'isApproved' => true
                ];
                Project::editBasicInfo($data, $project);
            }

            /* update team allocation's time  */
            ProjectMember::updateTime($project, $projectDraft);
            /* update Deliverable's time*/
            ProjDeliverable::updateTime($project, $projectDraft);
            /* update Stages's time*/
            StageAndMilestone::updateTime($project, $projectDraft);
            /* update time of training plan*/
            Training::updateTime($project, $projectDraft);
            $controller = new self();
            $result = $controller->submitWorkorder($project->id);
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Save data success!');
            Log::info($ex);
            return response()->json($response);
        }
    }

    public function changeRiskStatus(Request $request)
    {
        $status = $request->status;
        $riskId = $request->riskId;

        $risk = Risk::find($riskId);

        //If not exist risk or status has not changed then return
        if (!$risk || $risk->status == $status) {
            return;
        }

        DB::beginTransaction();
        try {
            $risk->status = $status;
            $risk->save();
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollBack();
        }
    }

    public function exportMember($id = null)
    {
        $projectIds = Input::get('projectIds');
        $approver = false;
        if ($projectIds == null && $id == null) {
            return redirect()->route('project::dashboard');
        }
        if ($projectIds == null) {
            $projectIds = $id;
            $approver = true;
        }
        $projectIds = rtrim($projectIds, '-');
        $projectIds = explode('-', $projectIds);
        if (!count($projectIds)) {
            return redirect()->route('project::dashboard');
        }

        // Check baseline
        $checkBaseline = Input::get('baseline');
        if ($checkBaseline) {
            $projectIds = ProjPointBaseline::whereIn('id', $projectIds)->lists('project_id')->toArray();
        }
        $projectIds = array_values($projectIds);

        foreach ($projectIds as $projectId) {
            if (!$projectId) {
                continue;
            }
            $projectId = (int)$projectId;
            $project = Project::select('id', 'name')->find($projectId);

            // check permission view
            if (!ViewProject::isAccessViewProject($project)) {
                continue;
            }
        }

        $collection = ProjectMember::getMemberOfManyProject($projectIds, $approver);
        if (!$collection) {
            return back()->with('messages', [
                'errors' => [
                    trans('project::view.export members whose end dates are greater than the current date'),
                ]
            ]);
        }

        Excel::create('Member dự án', function ($excel) use ($collection) {
            $excel->sheet('sheet1', function ($sheet) use ($collection) {
                $sheet->loadView('project::includes.export_member', [
                    'data' => $collection
                ]);
            });
        })->export('xlsx');
    }

    public function exportMemberByMonth($id = null)
    {
        $projectId = Input::get('projectId');
        if ($projectId == null && $id == null) {
            return redirect()->route('project::dashboard');
        }
        if ($projectId == null) {
            $projectId = $id;
        }
        $projectId = rtrim($projectId, '-');
        $projectId = explode('-', $projectId);
        if (!count($projectId)) {
            return redirect()->route('project::dashboard');
        }

        $projectId = array_values($projectId);

        foreach ($projectId as $projectId) {
            if (!$projectId) {
                continue;
            }
            $projectId = (int)$projectId;
            $project = Project::select('id', 'name')->find($projectId);

            // check permission view
            if (!ViewProject::isAccessViewProject($project)) {
                continue;
            }
        }
        $collection = ProjectMember::getMemberActiveOfProject($projectId);
        if (!$collection) {
            return back()->with('messages', [
                'errors' => [
                    trans('project::view.export members whose end dates are greater than the current date'),
                ]
            ]);
        }
        $months = ProjectExport::getListMonths($collection);
        foreach ($months as $m) {
            $month[] = Carbon::parse($m)->month;
            $year[] = Carbon::parse($m)->year;
        }
        $data = [];
        for ($x = 0; $x < sizeof($months); $x++) {
            foreach ($collection as $employee) {
                $mm = Rview::getInstance()->getEffortOfMonth($month[$x], $year[$x], $employee->effort, $employee->start_at, $employee->end_at);
                $mm = $mm / 100;
                if (empty($data[$employee->employee_id][$month[$x]])) {
                    $data[$employee->employee_id][$month[$x]] = $mm;
                } else {
                    $data[$employee->employee_id][$month[$x]] += $mm;
                }
            }
        }
        array_walk_recursive($data, function($item, $key) use (&$sumMonth){
            $sumMonth[$key] = isset($sumMonth[$key]) ?  $item + $sumMonth[$key] : $item;
        });
        foreach ($data as $mm => $value) {
            $sum[$mm] = array_sum($value);
        }
        Excel::create('Member dự án chi tiết theo tháng', function ($excel) use ($collection, $months, $data, $sumMonth, $sum) {
            $excel->sheet('sheet1', function ($sheet) use ($collection, $months, $data, $sumMonth, $sum) {
                $sheet->loadView('project::includes.export_member_by_month', [
                    'collection' => $collection,
                    'months' => $months,
                    'data' => $data,
                    'sumMonth' => $sumMonth,
                    'sum' => $sum
                ]);
            });
        })->export('xlsx');
    }
    /**
     * Generate customer by company
     *
     * @param Request $request
     * @return array
     */
    public function genCusAndSaleByCompany(Request $request)
    {
        $response = array();
        $res['status'] = true;
        $data = $request->all();
        if ($data['project_id']) {
            $permission = ViewHelper::checkPermissionEditWorkorder($data['project_id']);
            $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];
            $project = Project::getProjectById($data['project_id']);
        } else {
            $permissionEdit = true;
            $project = null;
        }
        if($project) {
            $response['isCheckShowSubmit'] =
                ViewHelper::checkSubmitWorkOrder($data['project_id']);
        } else {
            $result['status'] = true;
            $response['isCheckShowSubmit'] = false;
        }
        DB::beginTransaction();
        try {
            if ($data['company_id']) {
                $customerSelected = Customer::where('company_id', $data['company_id'])->first();
                //gen customer and sale by company
                $salers = Company::getSaleByCompany($data['company_id']);
                $customers = Customer::customerByCompany($data['company_id']);
                if ($customers->isEmpty()) {
                    $data['cust_contact_id'] = null;
                } else {
                    $data['cust_contact_id'] = $customerSelected->id;
                }
                $data['sale_id'] = [];
                if (!$salers->isEmpty()) {
                    foreach ($salers as $saler) {
                        $data['sale_id'][] = $saler->id;
                    }
                }
                //save project
                if ($project) {
                    $project->cust_contact_id = $data['cust_contact_id'];
                    $project->company_id = $data['company_id'];
                    //Delete old sale
                    $saleOld = Project::getAllSaleOfProject($project->id);
                    $project->saleProject()->detach($saleOld);
                    if (isset($data['sale_id'])) {
                        $project->saleProject()->attach($data['sale_id']);
                    }
                    $project->save();
                }
                DB::commit();
                $res['status'] = true;
                $res['customer'] = $customers;
                $res['saler'] = $salers;
                $res['content'] = view('project::template.content-select-customer', [
                    'permissionEdit' => $permissionEdit,
                    'project' => $project,
                    'company' => $data['company_id'],
                    'customer' => $customerSelected,
                    'customers' => $customers,
                ])->render();
                return response()->json($res);
            }
        } catch (Exception $ex) {
            DB::rollback();
            $res['error'] = 1;
            $res['message'] = Lang::get('project::message.System error!');
            return response()->json($res);
        }
    }

    /*
     * add Devices Expenses
     */
    public function addDevicesExpenses(Request $request)
    {
        $data = $request->all();
        $project = Project::getProjectById($data['project_id']);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $permission = ViewHelper::checkPermissionEditWorkorder($project);
        $permissionAdd = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];

        if ($permissionAdd) {
            $response = array();
            $response['status'] = true;
            if (isset($data['isDelete'])) {
                $status = DevicesExpense::deleteDerivedExpense($data);
                if ($status) {
                    $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
                    $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
                    $response['content'] = DevicesExpense::getContentTable($project);
                    return response()->json($response);
                }
                $response['status'] = false;
                return response()->json($response);
            }
            $validateAdd = AddDevicesExpensesRequest::validateData($data);
            if ($validateAdd->fails()) {
                $response['message_error'] = $validateAdd->errors();
                $response['status'] = false;
                return response()->json($response);
            }

            $status =  DevicesExpense::insertDerivedExpense($data);
            if (!$status) {
                $response['status'] = false;
                return response()->json($response);
            }
            $checkDisplaySubmitButton = ViewHelper::checkSubmitWorkOrder($data['project_id']);
            $response['isCheckShowSubmit'] = $checkDisplaySubmitButton;
            $response['content'] = DevicesExpense::getContentTable($project);
            return response()->json($response);
        } else {
            View::viewErrorPermission();
        }
    }

    public function exportProject(Request $request)
    {
        ini_set('max_execution_time', 500);
        $urlSubmitFilter = trim(URL::route('project::dashboard'), '/') . '/';
        $projectIds = $request->get('projectIds');
        if ($projectIds) {
            $projectIds = rtrim($projectIds, '-');
            $projectIds = explode('-', $projectIds);
            $checkBaseline = $request->get('baseline');
            if ($checkBaseline) {
                $projectIds = ProjPointBaseline::whereIn('id', $projectIds)->lists('project_id')->toArray();
            }
            $projectIds = array_values($projectIds);
        }
        $collection = Project::getManyProject($projectIds);
        $collection = Project::gridDataFilter($collection, false);
        if (!$collection) {
            return back()->with('messages', [
                'errors' => [
                    trans('project::view.There are no projects currently ongoing  to now'),
                ]
            ]);
        }

        Excel::create('Danh sách Project', function ($excel) use ($collection) {
            $excel->sheet('sheet1', function ($sheet) use ($collection) {
                $sheet->loadView('project::includes.export_project', [
                    'data' => $collection
                ]);
            });
        })->export('xlsx');
    }

    public function productionCostExport(Request $request)
    {
        $projectId = $request->id;
        $data = [];
        $dataSum = [];
        $collection = ProjectApprovedProductionCost::select('project_approved_production_cost.*', 'kind_id')
            ->leftJoin("projs", "projs.id", "=", "project_approved_production_cost.project_id")
            ->where('project_id', $projectId)
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('team_id')
            ->orderBy('role')
            ->get()
            ->toArray();

        foreach ($collection as $child) {
            $data[$child['year']][$child['month']][$child['team_id']][$child['role']][] = $child;
            $dataSum[$child['team_id']][$child['role']][] = $child;
        }

        Excel::create('Production_cost_detail', function ($excel) use ($data, $dataSum) {
            $excel->sheet('sheet1', function ($sheet) use ($data, $dataSum) {
                $sheet->cell('A1:H1', function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setValignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->setWidth('A', 12);
                $sheet->setWidth('B', 15);
                $sheet->setWidth('C', 12);
                $sheet->setWidth('F', 15);
                $sheet->setWidth('H', 15);
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('Tháng');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('Division');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('Role');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('Số MM');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('Đơn giá');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('Doanh thu');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('Tổng MM');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('Tổng doanh thu');
                });
                $index = 2;

                foreach ($data as $value0) {
                    foreach ($value0 as $value1) {
                        $countMonth = 0;
                        foreach ($value1 as $value2) {
                            $count_team = 0;
                            $sumMM_role = 0;
                            $sumRevenueOfRole = 0;
                            foreach ($value2 as $value3) {
                                foreach ($value3 as $item) {
                                    $countMonth += 1;
                                    $count_team += 1;
                                    $arrMonth = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                    $itemMonth = '';
                                    for ($i = 0; $i < count($arrMonth); $i++) {
                                        if ($arrMonth[$i] == $arrMonth[($item["month"] - 1)]) {
                                            $itemMonth = $arrMonth[$i];
                                        }
                                    }
                                    $sheet->cell('A' . $index . ':H' . $index, function ($cells) {
                                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                                    });
                                    $sheet->setCellValue('A' . $index, $itemMonth . '-' . $item["year"]);
                                    $sheet->setCellValue('B' . $index, Team::getTeamNameById($item["team_id"]));
                                    $sheet->setCellValue('C' . $index, Role::getRoleNameById($item["role"]));
                                    $sheet->setCellValue('D' . $index, $item["approved_production_cost"]);
                                    if ($item["price"] == null) {
                                        if ($item["kind_id"] == Operation::KIND_INTERNAL) {
                                            $price = ProjectApprovedProductionCost::UNIT_PRICE_INTERNAL_DEFAULT;
                                        } else {
                                            $price = ProjectApprovedProductionCost::UNIT_PRICE_DEFAULT;
                                        }
                                    } else {
                                        $price = $item['price'];
                                    }
                                    $sumMM_role += $item["approved_production_cost"];
                                    $sumRevenueOfRole += (float)$item["approved_production_cost"] * (float)$price;
                                    $sheet->setCellValue('E' . $index, $price);
                                    $sheet->setCellValue('F' . $index, (float)$item["approved_production_cost"] * (float)$price);
                                    $index += 1;
                                }
                            }
                            $start_team = $index - $count_team;
                            $end_team = $index - 1;
                            $sheet->mergeCells('B' . $start_team . ':B' . $end_team);
                            $sheet->mergeCells('G' . $start_team . ':G' . $end_team);
                            $sheet->mergeCells('H' . $start_team . ':H' . $end_team);
                            $sheet->setCellValue('G' . $start_team, $sumMM_role);
                            $sheet->setCellValue('H' . $start_team, $sumRevenueOfRole);
                        }
                        $startMonth = $index - $countMonth;
                        $endMonth = $index - 1;
                        $sheet->mergeCells('A' . $startMonth . ':A' . $endMonth);
                    }
                }

                $countChild = 0;
                foreach ($dataSum as $items) {
                    $countItem = 0;
                    $sumMM = 0;
                    $sumRevenue = 0;
                    foreach ($items as $item) {
                        $countTeam = 0;
                        $sumMM_Role = 0;
                        $sumRevenueRole = 0;
                        foreach ($item as $child) {
                            $countChild += 1;
                            $countItem += 1;
                            $countTeam += 1;
                            $sheet->cell('A' . $index . ':H' . $index, function ($cells) {
                                $cells->setBorder('thin', 'thin', 'thin', 'thin');
                            });
                            $sheet->setCellValue('A' .$index, 'SUM');
                            $sheet->setCellValue('B' . $index, Team::getTeamNameById($child["team_id"]));
                            $sheet->setCellValue('C' . $index, Role::getRoleNameById($child["role"]));
                            if ($child["price"] == null) {
                                if ($child["kind_id"] == Operation::KIND_INTERNAL) {
                                    $price = ProjectApprovedProductionCost::UNIT_PRICE_INTERNAL_DEFAULT;
                                } else {
                                    $price = ProjectApprovedProductionCost::UNIT_PRICE_DEFAULT;
                                }
                            } else {
                                $price = $child['price'];
                            }
                            $sumMM += $child["approved_production_cost"];
                            $sumRevenue += $child["approved_production_cost"] * (float)$price;
                            $sumMM_Role += $child["approved_production_cost"];
                            $sumRevenueRole += $child["approved_production_cost"] * (float)$price;
                            $sheet->setCellValue('F' . $index, (float)$child["approved_production_cost"] * (float)$price);
                            $index += 1;
                        }
                        $startCountTeam = $index - $countTeam;
                        $endCountTeam = $index - 1;
                        $sheet->mergeCells('C' . $startCountTeam . ':C' . $endCountTeam);
                        $sheet->setCellValue('D' . $startCountTeam, $sumMM_Role);
                        $sheet->mergeCells('D' . $startCountTeam . ':D' . $endCountTeam);
                        $sheet->setCellValue('F' . $startCountTeam, $sumRevenueRole);
                        $sheet->mergeCells('F' . $startCountTeam . ':F' . $endCountTeam);
                    }
                    $startCount = $index - $countItem;
                    $endCount = $index - 1;
                    $sheet->mergeCells('B' . $startCount . ':B' . $endCount);
                    $sheet->mergeCells('E' . $startCount . ':E' . $endCount);
                    $sheet->setCellValue('G' . $startCount, $sumMM);
                    $sheet->setCellValue('H' . $startCount, $sumRevenue);
                    $sheet->mergeCells('G' . $startCount . ':G' . $endCount);
                    $sheet->mergeCells('H' . $startCount . ':H' . $endCount);
                }
                $start = $index - $countChild;
                $end = $index - 1;
                $sheet->mergeCells('A' . $start . ':A' . $end);
                $sheet->cell('F' . 1 . ':H' . ($start - 1), function ($cells) {
                    $cells->setBackground('#fbe8d8');
                });
                $sheet->cell('A' . $start . ':H' . $end, function ($cells) {
                    $cells->setBackground('#f9be8e');
                });
                $range = 'A' . 1 . ':H' . $end;
                $sheet->setBorder($range, 'thin');
                $sheet->cell($range, function ($cells) {
                    $cells->setValignment('center');
                });
            });
        })->export('xlsx');
    }
        
    /**
     * getEmployeeProject
     *
     * @param  Request $request
     * @return collection
     */
    public function getEmployeeProject(Request $request)
    {
        $objProjMember = new ProjectMember();
        
        $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
        $empProj = $objProjMember->getEmployeeProject($request->id_proj, $startDate, $endDate);
        return $empProj;
    }

    public function showTeamByProj(Request $request)
    {
        $team = Project::getTeamIdByProject($request);
        return response()->json($team);
    }

    public function showApproverByProj(Request $request)
    {
        $projs = Project::find($request->projectId);
        $relaters = Project::getRelatersOfProject($projs, null, true);
        return response()->json($relaters);
    }
        
    /**
     * getJsonEmpProject
     *
     * @param  Request $request
     * @return json
     */
    public function getJsonEmpProject(Request $request)
    {
        $empProj = $this->getEmployeeProject($request);
        return response()->json($empProj);
    }

    public function savePurchase(Request $request)
    {
        $purchaseOrdId = Project::savePurchaseId($request);
        return response()->json($purchaseOrdId);
    }

    public function getPurchase(Request $request)
    {
        $purchaseOrdId = Project::apiGetPoByProjectId($request->purchaseOrdId);
        return response()->json($purchaseOrdId);
    }

    public function savePurchaseToCRM(Request $request)
    {
        $result = Project::savePurchaseIdToCRM($request);
        if (!$result) {
            $emailQueue = new EmailQueue();
            $subject = 'Lỗi không đồng bộ được Purchase Order ID sang CRM';
            if ($projectId = $request->get('projectId')) {
                $data = [
                    'projectName' => $request->get('projectName'),
                    'url' => route('project::project.edit', ['id' => $projectId])
                ];
                $emailQueue->setTo('hungnt2@rikkeisoft.com', 'hungnt2')
                    ->setTemplate('project::emails.notify_error_sync_poid_crm', $data)
                    ->setSubject($subject)
                    ->save();
            }
        }
        return response()->json($result);
    }

    public function updateCloseDate(Request $request)
    {
        $updateCloseDate = Project::updateDate($request);
        return response()->json($updateCloseDate);
    }

    public function saveContact(Request $request)
    {
        $contact = Project::saveContactCustomer($request);
        return response()->json($contact);
    }

    public function getDayOfProject(Request $request)
    {
        $days = ProjView::getMM($request->start_date, $request->end_date, $type = 2);
        return response()->json($days);
    }

    public function saveCate(Request $request)
    {
        $data = $request->all();
        $valid = Validator::make($data, [
            'cusEmail' => 'email',
        ]);
        if ($valid->fails()) {
            $response['message_error'] = "The customer's email must be a valid email address.";
            $response['status'] = false;
            return response()->json($response);
        }
        $projCate = Project::saveProjCate($request);
        return response()->json($projCate);
    }
}
