<?php

namespace Rikkei\Project\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config as SupportConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View as ViewLaravel;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\Document\View\DocConst;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\RiskAttach;
use Rikkei\Project\Model\RiskComment;
use Rikkei\Project\Model\SaleProject;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\TaskAction;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\TaskComment;
use Rikkei\Project\Model\TaskHistory;
use Rikkei\Project\Model\TaskNcmRequest;
use Rikkei\Project\Model\TaskRisk;
use Rikkei\Project\View\CheckWarningTask;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Project\View\TaskHelp;
use Rikkei\Project\View\ValidatorExtend;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamConst;

class TaskController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('project', 'project/dashboard');
    }

    /**
     * Display task list page
     */
    public function index(
            $id, 
            $type = null, 
            $status = null, 
            $title = null,
            $typeShow = null
    ) {
        $project = Project::find($id);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        // check permission view
        if (!ViewProject::isAccessViewProject($project)) {
            View::viewErrorPermission();
        }
        Breadcrumb::add('Project Dashboard', route('project::dashboard'));
        Breadcrumb::add('My task', route('project::task.my.task'));
                
        if (!$type) {
            $type = Task::getTypeIssues();
        }
        $typeKey = array_keys($type);
        if (!$status) {
            $status = Task::getStatusTypeNormal();
        }
        if (!$title) {
            $title = Lang::get('project::view.Task list');
        }
        return view('project::task.index', [
            'collectionModel' => Task::getGridData($id, $typeKey),
            'taskTypes' => $type,
            'taskStatus' => $status,
            'taskPriorities' => Task::priorityLabel(),
            'project' => $project,
            'titlePage' => $title,
            'typeShow' => $typeShow
        ]);
    }
    
    /**
     * show approve task
     */
    public function approve($id)
    {
        return $this->index($id, 
            Task::getTypeApproved(), 
            Task::getStatusTypeWO(),
            Lang::get('project::view.Approve list'),
            Task::TYPE_WO
        );
    }
    
    /**
     * add task
     */
    public function add($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        if (!$project->isOpen()) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Project isnot in status New or Processing'));
        }
        
        // check permission view
        if (!ViewProject::isAccessViewProject($project)) {
            View::viewErrorPermission();
        }
        $type = Input::get('type');
        if (!$type || in_array($type, [Task::TYPE_WO, Task::TYPE_SOURCE_SERVER])) {
            $type = Task::TYPE_ISSUE;
        }
        switch ($type) {
            case Task::TYPE_COMPLIANCE:
                $type = Task::TYPE_ISSUE_COST;
        }
        Breadcrumb::add('Project Dashboard', route('project::dashboard'));
        Breadcrumb::add('Project task', URL::route('project::task.index', ['id' => $id]));
        Breadcrumb::add('Task create');
        $curEmp = Permission::getInstance()->getEmployee();
        return view('project::task.edit', [
            'taskTypes' => ViewProject::getTaskTypeCreateAvailable($project),
            'taskStatus' => Task::getStatusTypeNormal(),
            'taskPriorities' => Task::priorityLabel(),
            'project' => $project,
            'type' => $type,
            'assignees' => $project->getMembers(),
            'taskItem' => new Task([
                'type' => $type
            ]),
            'accessEditTask' => true,
            'taskAssigns' => [],
            'assignees' => [],
            'participants' => [],
            'creatorTask' => null,
            'pmActive' => $project->getPMActive(),
            'teamsProject' => 'Teams: ' . $project->getTeamsString(),
            'taskCommentList' => TaskComment::getGridData($id),
            'curEmp' => $curEmp
        ]);
    }
    
    /**
     * edit task
     */
    public function edit($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $project = $task->getProject();

        // type task special, not edit
        if ($task->type == Task::TYPE_GENERAL || !$project) {
            $newController = new TaskGeneralController();
            return $newController->edit($id, $task);
        }
        if (in_array($task->type, [
            Task::TYPE_COMPLIANCE
        ])) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        
        if (!$project) {
            return redirect()->route('project::dashboard')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        // check permission view
        if (!ViewProject::isAccessViewProject($project)) {
            View::viewErrorPermission();
        }
        $accessEditDueDateCF = false;
        $curEmp = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, 'project::task.save') || SaleProject::isSalePersonOfProjs($curEmp->id, $project->id)) { // permission company or team sales of project.
            $accessEditDueDateCF = true;
        }
        if ($task->isTaskApproved()) {
            Breadcrumb::add('Approve list', URL::route('project::task.index.approve', 
                ['id' => $project->id]));
            Breadcrumb::add('My task', route('project::task.my.task'));
            if ($project->isOpen()) {
                $accessEditTask = ViewProject::isAccessEditTask(
                    $project, 
                    $task->type,
                    $task
                );
            } else {
                $accessEditTask = false;
            }
            $permission = Permission::getInstance();
            $approver = $reviewerDelete = TaskAssign::findAssignee(
                $task->id, 
                null,
                TaskAssign::ROLE_APPROVER, 
                null
            );
            $reviewerNo = TaskAssign::findAssignee(
                $task->id,
                null,
                TaskAssign::ROLE_REVIEWER,
                TaskAssign::STATUS_NO,
                true
            );
            $revieweredExists = TaskAssign::findAssignee(
                $task->id, 
                null,
                TaskAssign::ROLE_REVIEWER, 
                [TaskAssign::STATUS_REVIEWED, TaskAssign::STATUS_FEEDBACK],
                true
            );
            return view('project::task.edit_approve', [
                'project' => $project,
                'taskItem' => $task,
                'accessApprove' => $permission->isAllow('project-access::task.approve.save'),
                'taskCommentList' => TaskComment::getGridData($id),
                'taskHistoryList' => TaskHistory::getGridData($id),
                'taskAssigns' => TaskAssign::getAssigneeAndRole($id),
                'pmActive' => $project->getPMActive(),
                'allStatusAssign' => TaskAssign::statusLabel(),
                'userCurrent' => $permission->getEmployee(),
                'accessChangeApprover' => 
                    $permission->isAllow('project-access::task.approve.chagnge.approver') 
                    && $task->status != Task::STATUS_APPROVED,
                'accessChangeReviewer' => 
                    ($permission->isAllow('project-access::task.approve.chagnge.reviewer')
                    || ($approver && $permission->getEmployee()->id == $approver->employee_id))
                    && $task->status != Task::STATUS_APPROVED,
                'reviewerOnly' => $reviewerNo == 1 && !$revieweredExists,
                'teamsProject' => 'Teams: ' . $project->getTeamsString(),
                'taskWoChangesContent' => GeneralProject::getContentWoChangesHtml($id, $project)
            ]);
        }
        $curEmp = Permission::getInstance()->getEmployee();
        $taskStatus = Task::getStatusTypeNormal();
        if ($project->isOpen()) {
            $accessEditTask = ViewProject::isAccessEditTask($project, $task->type, $task);
        } else {
            $accessEditTask = false;
        }
        Breadcrumb::add('Project Dashboard', route('project::dashboard'));
        Breadcrumb::add('Project task', URL::route('project::task.index', ['id' => $project->id]));
        Breadcrumb::add('My task', route('project::task.my.task'));
        $creator = $task->created_by;
        if ($creator) {
            $creator = Employee::find($creator);
            if (!$creator) {
                $creator = null;
            }
        }
        return view('project::task.edit', [
            'taskTypes' => Task::typeLabel(),
            'taskStatus' => $taskStatus,
            'taskPriorities' => Task::priorityLabel(),
            'project' => $project,
            'assignees' => TaskAssign::getAssigneesInfo($task->id),
            'participants' => TaskAssign::getAssigneesInfo($task->id, true),
            'taskItem' => $task,
            'accessEditTask' => $accessEditTask,
            'taskCommentList' => TaskComment::getGridData($id),
            'taskHistoryList' => TaskHistory::getGridData($id),
            'creatorTask' => $creator,
            'pmActive' => $project->getPMActive(),
            'teamsProject' => 'Teams: ' . $project->getTeamsString(),
            'curEmp' => $curEmp,
            'taskFreequencyOfRp' => Task::getLableFrequencyOfReport(),
            'accessEditDueDateCF' => $accessEditDueDateCF
        ]);
    }

    public function exportIssue()
    {
        $urlFilter = route('project::report.issue') . '/';
        $teamIds = [];
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $route = 'project::report.issue';
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            if (is_array($teamIds)) {
                $teamIds = array_filter(array_values($teamIds));
                $teamIds = implode($teamIds, ', ');
            }
            $teamIdsAvailable = true;
        } else {// permission team or self profile.
            $teamIdsAvailable = [];
            if (($scopeTeamIds = Permission::getInstance()->isScopeTeam(null, $route))) {
                $teamIdsAvailable = is_array($scopeTeamIds) ? $scopeTeamIds : [];
            }
            // get list team_id responsible by pqa.
            $curEmp = Permission::getInstance()->getEmployee();
            $teamIdsResponsibleByPqa = PqaResponsibleTeam::getListTeamIdResponsibleTeam($curEmp->id);
            if (!$teamIdsResponsibleByPqa->isEmpty()) {
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsResponsibleByPqa->pluck('team_id')->toArray());
            }
            $teamIdsAvailable = array_unique($teamIdsAvailable);
            //ignore team childs
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
            if ($teamIds = Form::getFilterData('except', 'teams.id', $urlFilter)) {
                $teamIds = implode($teamIds, ', ');
            }
            if (!$teamIds) {
                $teamIds = null;
                $flagNoCheck = true;
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::select('name')
                    ->find($teamIds);
            }
        }
        $dataIssue = Task::getAllTask($teamIdsAvailable);
        $dataIssue = $dataIssue->get();
        if (!$dataIssue) {
            return back()->with('messages', [
                'errors' => [
                    trans('project::view.There are no issue currently ongoing  to now'),
                ]
            ]);
        }
        Excel::create('Danh sách issue', function ($excel) use ($dataIssue) {
            $excel->sheet('sheet1', function ($sheet) use ($dataIssue) {
                $sheet->loadView('project::task.include.export-issue', [
                    'dataIssue' => $dataIssue
                ]);
            });
        })->export('xlsx');
    }
    
    /**
     * add task ajax
     */
    public function addAjax($id)
    {
        $response = [];
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $project = Project::find($id);
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (!$project->isOpen()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Project isnot in '
                    . 'status New or Processing');
            return response()->json($response);
        }
        
        // check permission view
        if (!ViewProject::isAccessViewProject($project)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        $accessEditDueDateCF = false;
        $curEmp = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, 'project::task.save') || SaleProject::isSalePersonOfProjs($curEmp->id, $project->id)) { // team sales of project.
            $accessEditDueDateCF = true;
        }
        $type = Input::get('type');
        if (!$type || in_array($type, [
            Task::TYPE_WO, 
            Task::TYPE_SOURCE_SERVER,
            Task::TYPE_COMPLIANCE])
        ) {
            $type = Task::TYPE_ISSUE_COST;
        }
        if ($type == Task::TYPE_RISK) {
            $riskId = Input::get('risk_id');
        }
        $parentId = Input::get('parent_id');
        $response['htmlModal'] = view('project::task.ajax.edit', [
            'taskTypes' => ViewProject::getTaskTypeCreateAvailable($project),
            'taskStatus' => Task::getStatusTypeNormal(),
            'taskPriorities' => Task::priorityLabel(),
            'taskFreequencyOfRp' => Task::getLableFrequencyOfReport(),
            'taskAction' => Task::actionLabel(),
            'project' => $project,
            'type' => $type,
            'taskItem' => new Task([
                'type' => $type
            ]),
            'accessEditTask' => true,
            'taskAssigns' => [],
            'assignees' => [],
            'participants' => [],
            'creatorTask' => null,
            'parentId' => empty($parentId) ? null : $parentId,
            'riskId' => empty($riskId) ? null : $riskId,
            'editFormAjax' => true,
            'accessEditDueDateCF' => $accessEditDueDateCF,
        ])->render();
        $response['is_open'] = $project->isOpen();
        //get modal title
        $typeList = Task::typeLabel();
        $typeTitle = empty($typeList[$type]) ? '' : $typeList[$type];
        if ($type == Task::TYPE_COMMENDED) {
            $typeTitle = Lang::get('project::view.Customer feedback');
        }
        $response['modalTitle'] = empty($typeTitle) ? Lang::get('project::view.Task create') : $typeTitle;        $response['success'] = 1;
        $response['popup'] = 1;
        return response()->json($response);
    }
    
    /**
     * edit task
     */
    public function editAjax($id)
    {
        $response = [];
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $task = Task::find($id);
        if (!$task) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (in_array($task->type, [
                Task::TYPE_SOURCE_SERVER,
                Task::TYPE_COMPLIANCE,
                Task::TYPE_GENERAL,
                Task::TYPE_COMPLIANCE
            ])
        ) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $project = $task->getProject();
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
        $accessEditDueDateCF = false;
        $curEmp = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, 'project::task.save') || SaleProject::isSalePersonOfProjs($curEmp->id, $project->id)) { // team sales of project.
            $accessEditDueDateCF = true;
        }
        if ($task->isTaskApproved()) {
            Breadcrumb::add('Approve list', URL::route('project::task.index.approve', 
                ['id' => $project->id]));
            Breadcrumb::add('My task', route('project::task.my.task'));
            if ($project->isOpen()) {
                $accessEditTask = ViewProject::isAccessEditTask(
                    $project, 
                    $task->type,
                    $task
                );
            } else {
                $accessEditTask = false;
            }
            $permission = Permission::getInstance();
            $approver = $reviewerDelete = TaskAssign::findAssignee(
                $task->id, 
                null,
                TaskAssign::ROLE_APPROVER, 
                null
            );
            $reviewerNo = TaskAssign::findAssignee(
                $task->id, 
                null,
                TaskAssign::ROLE_REVIEWER,
                TaskAssign::STATUS_NO,
                true
            );
            $revieweredExists = TaskAssign::findAssignee(
                $task->id, 
                null,
                TaskAssign::ROLE_REVIEWER, 
                [TaskAssign::STATUS_REVIEWED, TaskAssign::STATUS_FEEDBACK],
                true
            );
            $response['htmlModal'] = view('project::task.ajax.edit_ajax_approve', [
                'project' => $project,
                'taskItem' => $task,
                'accessApprove' => $permission->isAllow('project-access::task.approve.save'),
                'taskCommentList' => TaskComment::getGridData($id),
                'taskHistoryList' => TaskHistory::getGridData($id),
                'taskAssigns' => TaskAssign::getAssigneeAndRole($id),
                'pmActive' => $project->getPMActive(),
                'allStatusAssign' => TaskAssign::statusLabel(),
                'userCurrent' => $permission->getEmployee(),
                'accessChangeApprover' => 
                    $permission->isAllow('project-access::task.approve.chagnge.approver') 
                    && $task->status != Task::STATUS_APPROVED,
                'accessChangeReviewer' => 
                    ($permission->isAllow('project-access::task.approve.chagnge.reviewer')
                    || ($approver && $permission->getEmployee()->id == $approver->employee_id))
                    && $task->status != Task::STATUS_APPROVED,
                'reviewerOnly' => $reviewerNo == 1 && !$revieweredExists,
                'teamsProject' => 'Teams: ' . $project->getTeamsString(),
                'taskWoChangesContent' => GeneralProject::getContentWoChangesHtml($id, $project)
            ])->render();
            $response['is_open'] = $project->isOpen();
            //get modal title
            $typeList = Task::typeLabel();
            $typeTitle = empty($typeList[$task->type]) ? '' : $typeList[$task->type];
            if ($task->type == Task::TYPE_COMMENDED) {
                $typeTitle = 'Customer feedback';
            }
            $response['modalTitle'] = empty($typeTitle) ? Lang::get('project::view.Task edit') : $typeTitle;
            $response['success'] = 1;
            $response['popup'] = 1;
            return response()->json($response);
        }
        $taskStatus = Task::getStatusTypeNormal();
        if ($project->isOpen()) {
            $accessEditTask = ViewProject::isAccessEditTask($project, $task->type, $task);
        } else {
            $accessEditTask = false;
        }
        $parentId = Input::get('parent_id');
        $riskId = Input::get('risk_id');
        $response['htmlModal'] = view('project::task.ajax.edit', [
            'taskTypes' => Task::typeLabel(),
            'taskStatus' => $taskStatus,
            'taskPriorities' => Task::priorityLabel(),
            'taskFreequencyOfRp' => Task::getLableFrequencyOfReport(),
            'taskAction' => Task::actionLabel(),
            'project' => $project,
            'assignees' => TaskAssign::getAssigneesInfo($task->id),
            'participants' => TaskAssign::getAssigneesInfo($task->id, true),
            'taskItem' => $task,
            'accessEditTask' => $accessEditTask,
            'parentId' => empty($parentId) ? null : $parentId,
            'riskId' => empty($riskId) ? null : $riskId,
            'editFormAjax' => true,
            'accessEditDueDateCF' => $accessEditDueDateCF,
        ])->render();
        $response['is_open'] = $project->isOpen();
        //get modal title
        $typeList = Task::typeLabel();
        $typeTitle = empty($typeList[$task->type]) ? '' : $typeList[$task->type];
        if ($task->type == Task::TYPE_COMMENDED) {
            $typeTitle = 'Customer feedback';
        }
        $response['modalTitle'] = empty($typeTitle) ? Lang::get('project::view.Task edit') : $typeTitle;
        $response['success'] = 1;
        $response['popup'] = 1;
        return response()->json($response);
    }
    
    /**
     * save task
     */
    public function save()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        $taskId = Input::get('id');
        $dataTask = Input::get('task');
        $dataTaskAssigns = Input::get('task_assign');
        $dataTaskParticipants = Input::get('task_participant');
        $response = [];
        $arrayRules = [];
        $curEmp = Permission::getInstance()->getEmployee();
        //init task
        if (!$taskId) {
            $project = Project::find(Input::get('project_id'));
            if (!$project) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Not found item.');
                return response()->json($response);
            }
            $typeTask = Input::get('task.type');
            if (!$typeTask) {
                $typeTask = Input::get('type');
            }
            if (!$typeTask) {
                $typeTask = Task::TYPE_ISSUE_COST;
            }
            if (!in_array($typeTask, Task::getTypeNormal(true))) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Error input data!');
                return response()->json($response);
            }
            $task = new Task();
            $task->type = $typeTask;
            $task->created_by = Permission::getInstance()->getEmployee()->id;
            if (!empty(Input::get('parent_id'))) {
                $task->parent_id = Input::get('parent_id');
            }
        } else {
            $task = Task::find($taskId);
            if (!$task) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Not found item.');
                return response()->json($response);
            }

            //store old data to check changed
            $oldTask = Task::find($taskId);
            $oldAssignees = TaskAssign::getAssignCusFeedback($oldTask);

            $project = $task->getProject();
            if (!$project) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Not found item.');
                return response()->json($response);
            }
            if (Input::get('task.type')) {
                $typeTask = Input::get('task.type');
            } else {
                $typeTask = $task->type;
            }
            // check type task is issue or customer idea
            if ($task->isTaskIssues()) {
                $taskTypes = Task::getTypeIssuesCreator(true);
                if (!in_array($typeTask, $taskTypes)) {
                    $typeTask = Task::TYPE_ISSUE_COST;
                    $dataTask['type'] = Task::TYPE_ISSUE_COST;
                }
            } else if ($task->isTaskCustomerIdea()){
                $taskTypes = Task::getTypeCustomerIdea(true);
                if (!in_array($typeTask, $taskTypes)) {
                    $typeTask = Task::TYPE_COMMENDED;
                    $dataTask['type'] = Task::TYPE_COMMENDED;
                }
            } else {
                unset($dataTask['type']);
            }
        }

        //validation and access
        $accessEditTask = ViewProject::isAccessEditTask($project, $typeTask, $task);
        $accessEditDueDateCF = false;
        $curEmp = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, 'project::task.save') || SaleProject::isSalePersonOfProjs($curEmp->id, $project->id)) { // team sales of project.
            $accessEditDueDateCF = true;
        }
        if ($accessEditTask) {
            // project close => check edit status close task
            if (!$project->isOpen()) {
                if (Task::hasEditStatusTasks($task, $project)) {
                    if ($dataTask['status']) {
                        $task->setData(['status' => $dataTask['status']]);
                        $task->save();
                        $response['success'] = 1;
                        $response['message'] = Lang::get('project::message.Save data success!');
                        $response['refresh'] = URL::route('project::task.edit', [
                            'id' => $task->id
                        ]);

                        return response()->json($response);
                    } else {
                        $response['error'] = 1;
                        $response['message'] = Lang::get('project::message.Project closed');

                        return response()->json($response);
                    }
                }
            }
            if ($task->isTaskIssues() || 
                $task->type == Task::TYPE_QUALITY_PLAN
            ) {
                ValidatorExtend::addAfterEqual();
                $arrayRules = [
                    'created_at' => 'required|date',
                    'title' => 'required',
                    'duedate' => 'required|date|after_equal:task.created_at',
                    'status' => 'required',
                    'priority' => 'required',
                    'content' => 'required',
                    'actual_date' => 'date|after_equal:task.created_at'
                ];
            } elseif ($task->isTaskCustomerIdea()) {
                $arrayRules = [
                    'title' => 'required',
                    'content' => 'required',
                ];
                if (!$accessEditDueDateCF) {
                    unset($dataTask['duedate']);
                }
                unset($dataTask['actual_date']);
            } elseif ($task->type == Task::TYPE_COMPLIANCE) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Error input data!');
                return response()->json($response);
                /*$arrayRules = [
                    'title' => 'required',
                    'status' => 'required',
                    'priority' => 'required',
                    'content' => 'required',
                ];
                unset($dataTask['duedate']);
                unset($dataTask['actual_date']);*/
            } elseif ($task->type == Task::TYPE_RISK) {
                $arrayRules = [
                    'created_at' => 'required|date',
                    'title' => 'required',
                    'duedate' => 'required|date',
                    'status' => 'required',
                    'priority' => 'required',
                    'content' => 'required',
                    'actual_date' => 'date'
                ];
            } else {
                $arrayRules = [
                    'status' => 'required'
                ];
                unset($dataTask['title']);
                unset($dataTask['duedate']);
                unset($dataTask['content']);
                unset($dataTask['priority']);
            }
        } else {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        $validator = Validator::make($dataTask, $arrayRules);
        if ($validator->fails() || !$dataTaskAssigns || !count($dataTaskAssigns)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            return response()->json($response);
        }
        //check member assign
        if ($task->status != Task::STATUS_CLOSED && 
            $dataTask['status'] == Task::STATUS_CLOSED
        ) {
            $tasksToLeaderEdit = Project::checkIsLeaderToEditTask($project->id, auth()->id());
            if ($task->created_by && $task->created_by !=
                Permission::getInstance()->getEmployee()->id
            ) {
                if (count($tasksToLeaderEdit) === 0) {
                    $response['error'] = 1;
                    $response['message'] = Lang::get('project::message.Only creators and group leader can close');
                    return response()->json($response);
                }
            }
        }
        $taskStatus = Task::getStatusTypeNormal();
        
        if (!isset($dataTask['created_at']) || !$dataTask['created_at']) {
            $dataTask['created_at'] = null;
            if (!$task->id) {
                $dataTask['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            }
        }
        // check permission edit task
        if (!$task->id) {
            if (!$accessEditTask) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.You don\'t have access');
                return response()->json($response);
            } else {
                $task->setData($dataTask);
            }
        } else {
            if (!$accessEditTask) {
                $task->setData(['assign' => $dataTask['assign']]);
            } else {
                $task->setData($dataTask);
            }
        }
        $task->setData([
            'project_id' => $project->id
        ]);
        $taskStatus = Task::getStatusTypeNormal(true);
        if (!in_array($task->status, $taskStatus)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            return response()->json($response);
        }
        if ($task->id) {
            $typeCreateTask = TaskAssign::TYPE_STATUS_CHANGE;
        } else {
            $typeCreateTask = TaskAssign::TYPE_STATUS_NEW;
        }

        DB::beginTransaction();
        try {
            $task->save([], $project);
            if (!$task->isTaskCustomerIdea()) {
                $dataTaskAssigns = reset($dataTaskAssigns);
            }
            TaskAssign::insertMembers($task, $dataTaskAssigns, [
                'project' => $project,
                'type' => $typeCreateTask,
            ]);
            TaskAssign::insertMembers($task, $dataTaskParticipants, [
                'project' => $project,
                'type' => $typeCreateTask,
            ], true);

            if (!$taskId && !empty(Input::get('risk_id'))) {
                TaskRisk::insert([
                    'task_id' => $task->id,
                    'risk_id' => Input::get('risk_id'),
                ]);
            }

            //Send mail to project relater
            //Send only when task is customer feedback type and (is create new task or task has changed least a field)
            $taskHelp = new TaskHelp();
            if ($taskHelp->isCustomerFeedback($task->type)) {
                if ($taskId) {
                    $fieldChanged = $taskHelp->fieldsChanged($oldTask,$oldAssignees , $dataTask, $dataTaskAssigns, $dataTaskParticipants);
                } else {
                    $fieldChanged = [];
                }
                if (!$taskId || ($taskId && count($fieldChanged))) {
                    $relatersOfProject = Project::getRelatersOfProject($project);
                    $assignees = Employee::getEmpByIds($dataTaskAssigns);
                    $participants = $dataTaskParticipants ? Employee::getEmpByIds($dataTaskParticipants) : null;
                    $this->sendMailRelater($relatersOfProject, $project, $taskId, $task, $fieldChanged, $assignees, $participants);
                }
                
                //send email PQA and leader PQA when feedback negative
                if ($task->type == Task::TYPE_CRITICIZED) {
                    $objViewProject = new ViewProject();
                    $empsPQA = $objViewProject->getEmpPQAProject($project->id);
                    $empLeaderPQA = $objViewProject->getLeaderPQA($empsPQA);
                    if (!$empLeaderPQA) {
                        $empLeaderPQA = with(new Employee())->getEmpLeaderPQAByBranch(Team::CODE_PREFIX_HN);
                    }
                    $arrSendEmail = [];
                    if (count($empsPQA)) {
                        foreach($empsPQA as $item) {
                            $arrSendEmail[$item->email] = $item;
                        }
                    }
                    if ($empLeaderPQA && !array_key_exists($empLeaderPQA->email, $arrSendEmail)) {
                        $arrSendEmail[$empLeaderPQA->email] = $empLeaderPQA;
                    }
                    if ($arrSendEmail && isset($relatersOfProject)) {
                        foreach($relatersOfProject as $item) {
                            if (array_key_exists ($item->email, $arrSendEmail)) {
                                unset($arrSendEmail[$item->email]);
                            }
                        }
                    }
                    $this->sendEmailNegative($arrSendEmail, $project, $taskId, $task, $fieldChanged);
                }
            }
            

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            return response()->json($response);
        }
        $response['success'] = 1;
        $response['message'] = Lang::get('project::message.Save data success!');
        if (Input::get('editFormAjax')) {
            $taskEdited = new Task();
            $taskEdited->id = $task->id;
            $taskEdited->title = $task->title;
            $taskEdited->status = $task->getStatus();
            $taskEdited->priority = $task->getPriority();
            $taskEdited->type = $task->getType();
            $taskEdited->created = Carbon::parse($task->created_at)->toDateString();
            $taskEdited->due_date = Carbon::parse($task->duedate)->toDateString();
            $today = Carbon::parse(Carbon::today());
            if($task->status != Task::STATUS_CLOSED 
                    && $task->duedate !== null
                    && $task->duedate->lt($today)
            ) {
                $taskEdited->setColor = true;
            }
            $response['taskObject'] = $taskEdited;
            $response['reloadBlockAjax'] = [
                '.task-list-ajax[data-type="'.Task::TYPE_ISSUE.'"]',
                '.task-list-ajax[data-type="'.$task->type.'"]'
            ];
            if ($task->type == Task::TYPE_CRITICIZED) {
                $response['reloadBlockAjax'][] = 
                    '.task-list-ajax[data-type="'.Task::TYPE_COMMENDED.'"]';
            }
            if ($task->type == Task::TYPE_RISK) {
                $response['reload'] = 1;
            }
        } else {
            switch ($task->type) {
                case Task::TYPE_QUALITY_PLAN:
                    $response['refresh'] = URL::route('project::project.edit', 
                    ['id' => $project->id]) . '#' . 
                        Task::getNameTabWOItem(Task::TYPE_WO_QUALITY);
                    break;
                default:
                    $response['refresh'] = URL::route('project::task.edit', [
                        'id' => $task->id
                    ]);
            }
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('project::message.Save data success!'),
                        ]
                    ]
            );
        }
        $response['popup'] = 1;
        return response()->json($response);
    }

    /**
     * Send mail to relaters of project (pqa, leader, pm, saler)
     * 
     * @param Project $project
     * @param int $taskId
     * @return void
     */
    public function sendMailRelater($relatersOfProject, $project, $taskId, $task, $fieldChanged, $assignees, $participants)
    {
        $idsSent = [];
        if (!empty($relatersOfProject)) {
            foreach ($relatersOfProject as $emp) {
                $this->sendMail($emp->email, $emp->name, $project, $task, $fieldChanged, empty($taskId));
                $idsSent[] = $emp->id;
            }
            foreach ($assignees as $empAssign) {
                if (!in_array($empAssign->id, $idsSent)) {
                    $this->sendMail($empAssign->email, $empAssign->name, $project, $task, $fieldChanged, empty($taskId));
                    $idsSent[] = $empAssign->id;
                }
            }
            if ($participants) {
                foreach ($participants as $empPar) {
                    if (!in_array($empPar->id, $idsSent)) {
                        $this->sendMail($empPar->email, $empPar->name, $project, $task, $fieldChanged, empty($taskId));
                        $idsSent[] = $empPar->id;
                    }
                }
            }
            $subject = empty($taskId) ? Lang::get("project::view.[Project report] A customer feedback has been created in project :name", ['name' => $project->name]) :
                Lang::get("project::view.[Project report] A customer feedback has been edit in project :name", ['name' => $project->name]);
            \RkNotify::put(
                $idsSent,
                $subject,
                route("project::point.edit", ['id' => $project->id]) . '#css',
                ['category_id' => RkNotify::CATEGORY_PROJECT]
            );
        }
    }
    
    /**
     * Send mail
     * 
     * @param string $email
     * @param string $name
     * @param int $projectId
     * @param boolean $isCreated
     * @return void
     */
    public function sendMail($email, $name, $project, $task, $fieldChanged, $isCreated = true)
    {
        $data = [
            'email' => $email,
            'name' => $name,
            'url' => route("project::point.edit", ['id' => $project->id]) . '#css',
            'isCreated' => $isCreated,
            'projectName' => $project->name,
            'taskTitle' => $task->title,
            'changed' => $fieldChanged,
        ];
        if ($isCreated) {
            $subject = Lang::get("project::view.[Project report] A customer feedback has been created in project :name", ['name' => $project->name]);
        } else {
            $subject = Lang::get("project::view.[Project report] A customer feedback has been edit in project :name", ['name' => $project->name]);
        }
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($email, $name)
                ->setSubject($subject)
                ->setTemplate("project::emails.customer_feedback_relater", $data)
                ->save();
    }
    
    /**
     * save comment task
     */
    public function saveComment()
    {
        $task = Task::find(Input::get('id'));
        $dataTask = Input::get('tc');
        $dataTask['content'] = trim($dataTask['content']);
        $response = [];
        if (!$task) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $urlRefresh = URL::route('project::task.edit', ['id' => $task->id]);
        if ($task->type != Task::TYPE_GENERAL) {
            $project = Project::find($task->project_id);
            if (!$project) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Not found item.');
                return response()->json($response);
            }
            //check member
            if (!ViewProject::isAccessViewProject($project)) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.You don\'t have access');
                return response()->json($response);
            }
            if ($task->type == Task::TYPE_REWARD) {
                $taskAssigns = TaskAssign::getAssignReward($task);
                $permission = Permission::getInstance();
                $userCurrent = $permission->getEmployee();
                if ((isset($taskAssigns['role'][TaskAssign::ROLE_PM]['employee_id']) && 
                    $userCurrent->id == $taskAssigns['role'][TaskAssign::ROLE_PM]['employee_id']) ||
                    (isset($taskAssigns['role'][TaskAssign::ROLE_REVIEWER]['employee_id']) && 
                    $userCurrent->id == $taskAssigns['role'][TaskAssign::ROLE_REVIEWER]['employee_id']) ||
                    (isset($taskAssigns['role'][TaskAssign::ROLE_APPROVER]['employee_id']) && 
                    $userCurrent->id == $taskAssigns['role'][TaskAssign::ROLE_APPROVER]['employee_id']) ||
                    $permission->isAllow('project::reward.submit') ||
                    $permission->isAllow('project::reward.confirm') ||
                    $permission->isAllow('project::reward.approve')
                ) {
                    // have access
                    $urlRefresh = URL::route('project::reward', ['id' => $project->id]);
                } else {
                    $response['error'] = 1;
                    $response['message'] = Lang::get('You don\'t have access');
                    return response()->json($response);
                }
            } else {
                if (!$project->isOpen()) {
                    $response['error'] = 1;
                    $response['message'] = Lang::get('project::message.Project closed');
                    return response()->json($response);
                }
            }
        } else {
            if (!ViewProject::isAccessEditTaskGeneral($task, 'project::task.general.view')) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.You don\'t have access');
                return response()->json($response);
            }
        }
        if ($task->type != Task::TYPE_COMPLIANCE) {
            $response['refresh'] = $urlRefresh;
        }
        $validator = Validator::make($dataTask, [
            'content' => 'required'
        ]);
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            return response()->json($response);
        }

        $commentId = Input::get('comment_id');
        $currentUser = auth()->user(); //lay user hien tai
        if ($commentId && $dataTask['content']) {
            // edit comment.
            $taskComment = TaskComment::find($commentId);
            if ($taskComment->created_by == $currentUser->employee_id) {
                $taskComment->content = $dataTask['content'];
                $taskComment->save();
            } else {
                $response['message'] = Lang::get('project::message.You don\'t have access');
                return response()->json($response);
            }
        } else {
            //save comment task
            $taskComment = new TaskComment();
            $taskComment->task_id = $task->id;
            $taskComment->setData($dataTask)->save();
            $taskComment->name = $currentUser->name;
            $taskComment->email = $currentUser->email;
        }
        $response['commentHtml'] = view('project::task.include.comment_item', ['item' => $taskComment])->render();
        $response['success'] = 1;
        $response['popup'] = 1;
        return response()->json($response);
    }
  
    /**
     * my task
     */
    public function taskSelf()
    {
        Breadcrumb::add('My task');
        $title = Lang::get('project::view.My task');
        $status = Task::getStatusTypeMySelf();
        return view('project::task.index_self', [
            'collectionModel' => Task::getGridDataSelfTask(
                    Permission::getInstance()->getEmployee()->id
            ),
            'taskStatus' => $status,
            'taskPriorities' => Task::priorityLabel(),
            'titlePage' => $title,
            'taskType' => Task::typeLabel()
        ]);
    }

    /**
     * get task list method ajax
     * 
     * @param int $id project id
     */
    public function taskListAjax($id)
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
        $type = Input::get('type');
        $statusFilter = Input::get('statusFilter');
        $status = Input::get('status');
        $template = Input::get('template');
        switch ($template) {
            case 1:
                $template = 'project::point.tab.task_list_summary';
                break;
            case 2:
                $template = 'project::task.ajax.task_list_popup';
                break;
            default:
                $template = 'project::point.tab.task_list';
        }
        if ($status && !is_array($status)) {
            $status = (boolean) $status;
        }
        if ($type == Task::TYPE_COMMENDED || in_array(Task::TYPE_COMMENDED, (array) $type)) {
            $response['html'] = ViewLaravel::make('project::task.ajax.task_list_customer_feedback', [
                'collectionModel' => Task::getList($id, $type, ['status' => $status]),
                'project' => $project,
            ])->render();
        } elseif ($type == Task::TYPE_COMPLIANCE) {
            $response['html'] = ViewLaravel::make('project::point.tab.task_list_nc', [
                'collectionModel' => TaskNcmRequest::getListTaskNcmAjax($id), 
                'taskStatus' => Task::getStatusTypeNormal(),
                'taskPriorities' => Task::priorityLabel(),
                'project' => $project,
            ])->render();
        } elseif ($type == Task::TYPE_RISK && !$statusFilter) {
            $response['html'] = ViewLaravel::make('project::task.ajax.task_child_of_risk', [
                'collectionModel' => Task::getTaskRisk(Input::get('riskId')),
                'riskId' => Input::get('riskId'),
                'index' => 0,
                'type' => $type,
                'project' => $project,
            ])->render();
        } else {
            $response['html'] = ViewLaravel::make($template, [
                'collectionModel' => Task::getList($id, $type, ['status' => $status], true), 
                'taskStatus' => Task::getStatusTypeNormal(),
                'taskPriorities' => Task::priorityLabel(),
                'type' => $type,
                'project' => $project,
            ])->render();
        }
        $response['is_open'] = $project->isOpen();
        $response['success'] = 1;
        return response()->json($response);
    }

    public function generateHtml($id)
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
        $type = Input::get('type');
        if ($type == Task::TYPE_COMMENDED || $type == Task::TYPE_CRITICIZED) {
            $type = [Task::TYPE_COMMENDED, Task::TYPE_CRITICIZED];
        }
        $response = ViewLaravel::make('project::task.ajax.task_list_customer_feedback', [
                'collectionModel' => Task::getList($id, $type),
                'project' => $project,
            ])->render();
        return response()->json($response);
    }

    /**
     * get titles task of projects
     */
    public function taskTitles()
    {
        $ids = Input::get('ids');
        $response = [];
        if (!$ids || !count($ids)) {
            $response['success'] = 1;
            return response()->json($response);
        }
        
        foreach ($ids as $id) {
            $project = Project::find($id);
            if (!$project) {
                continue;
            }
            // check permission view
            if (!ViewProject::isAccessViewProject($project)) {
                continue;
            }
            $response['data'][$id] = $project->getTaskTitle();
        }
        $response['success'] = 1;
        return response()->json($response);
    }

    /**
     * show list comment by ajax
     */
    public function commentListAjax($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $task = Task::find($id);
        if (!$task) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $project = $task->getProject();
        if (!$project) {
            if ($task->type == Task::TYPE_GENERAL) {
                $response['success'] = 1;
                $response['html'] = ViewLaravel::make('project::task.include.comment_list', [
                    'collectionModel' => TaskComment::getGridData($id)
                ])->render();

                return response()->json($response);
            }
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
        $response['success'] = 1;
        $response['html'] = ViewLaravel::make('project::task.include.comment_list', [
                'collectionModel' => TaskComment::getGridData($id)
            ])->render();
        return response()->json($response);
    }

    /**
     * show list comment by ajax
     */
    public function historyListAjax($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $task = Task::find($id);
        if (!$task) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $project = $task->getProject();
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
        $response['success'] = 1;
        $response['html'] = ViewLaravel::make('project::task.include.history_list', [
                'collectionModel' => TaskHistory::getGridData($id)
            ])->render();
        return response()->json($response);
    }

    /**
     * Project manager confirm contract read
     *
     * @param int $projectId
     */
    public function contractConfirm($projectId)
    {
         Task::where('project_id', $projectId)
            ->where('type', Task::TYPE_CONTRACT_CONFIRM)
            ->where('status', Task::STATUS_NEW)
            ->update(['status' => Task::STATUS_CLOSED]);

        return redirect()->route('project::project.edit', $projectId)
            ->with('messages', [
                'success'=> [
                    'Confirm contract success'
                ]
            ]);
    }

    public function taskChild(Request $request)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = ViewLaravel::make('project::task.ajax.task_child_of_customer_feedback', [
            'collectionModel' => Task::getTaskChild($request->get('taskId')),
            'parentId' => $request->get('taskId'),
            'redirect' => !empty($request->get('redirect')),
            'hasColumnProject' => $request->get('hasColumnProject') == 1,
            'index' => !empty($request->get('index')) ? $request->get('index') : null,
        ])->render();
        
        return $response;
    }

    public function taskRisk(Request $request)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = ViewLaravel::make('project::task.ajax.task_child_of_risk', [
                'collectionModel' => Task::getTaskRisk($request->get('riskId')),
                'riskId' => $request->get('riskId'),
                'index' => $request->get('index'),
            ])->render();
        
        return $response;
    }

    public function deleteComment(Request $request)
    {
        $commentId = $request->input('id');
        $comment = TaskComment::find($commentId);
        $curEmp = Permission::getInstance()->getEmployee();
        if (!$comment) {
            return response()->json([
                'status' => 0,
                'message' => trans('core::message.Not found item'),
            ], 500);
        }

        DB::beginTransaction();
        try {
            if ($curEmp->id == $comment->created_by) {
                $comment->delete();
            }
            DB::commit();
            return response()->json([
                'status' => 1,
            ]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return response()->json([
                'status' => 0,
                'message' => trans('core::message.Error system'),
            ], 500);
        }

    }
        
    /**
     * sendEmailNegative
     *
     * @param  array $employees array(collection)
     * @param  collection $project
     * @param  int|null $taskId
     * @param  collection $task
     * @param  mixed $fieldChanged
     * @return void
     */
    public function sendEmailNegative($employees, $project, $taskId, $task, $fieldChanged)
    {
        if (empty($employees)) {
            return;
        }
        foreach($employees as $employee) {
            $data = [
                'email' => $employee->email,
                'name' => $employee->name,
                'url' => route("project::point.edit", ['id' => $project->id]) . '#css',
                'isCreated' => empty($taskId),
                'projectName' => $project->name,
                'taskTitle' => $task->title,
                'changed' => $fieldChanged,
            ];
            if (empty($taskId)) {
                $subject = Lang::get("project::view.[Project] Project :name a customer feedback negative has been created", ['name' => $project->name]);
            } else {
                $subject = Lang::get("project::view.[Project] Project :name a customer feedback negative has been edit", ['name' => $project->name]);
            }
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($employee->email, $employee->name)
                ->setSubject($subject)
                ->setTemplate("project::emails.customer_feedback_negative", $data)
                ->save();
        }
    }

    public function detail($issueId)
    {
        Breadcrumb::add(Lang::get('project::view.Detail'));
        $issueInfo = Task::getById($issueId);
        if (!$issueInfo) {
            return redirect()->back()->with('messages', ['errors' => [trans('project::message.Issue not found')]]);
        }
        $projectId = $issueInfo->project_id;
        $methods = Risk::getMethods();
        $results = Risk::getResults();
        $curEmp = Permission::getInstance()->getEmployee();
        return View('project::issue.detail',
            [
                'curEmp' => $curEmp,
                'issueInfo' => $issueInfo,
                'methods' => $methods,
                'results' => $results,
                'projectId' => $projectId,
                'issueMitigation' => isset($issueId) ? TaskAction::getByType(TaskAction::TYPE_ISSUE_MITIGATION, $issueId) : null,
                'project' => Project::getTeamInChargeOfProject($projectId),
                'attachs' => isset($issueId) ? RiskAttach::getAttachs($issueId, RiskAttach::TYPE_ISSUE) : null,
                'comments' => RiskComment::getComments($issueId, RiskComment::TYPE_ISSUE),
            ]);
    }

    public function issue()
    {
        Breadcrumb::add(Lang::get('project::view.List'));
        $pager = Config::getPagerData(null, ['order' => 'tasks.id', 'dir' => 'desc']);
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $priority = Task::priorityLabel();
        $statusIssue = Task::statusNewLabel();
        $typeIssue = Task::typeLabelForIssue();
        $per = new Permission();
        $urlFilter = route('project::report.issue') . '/';
        $teamIds = [];
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $route = 'project::report.issue';
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            if (is_array($teamIds)) {
                $teamIds = array_filter(array_values($teamIds));
                $teamIds = implode($teamIds, ', ');
            }
            $teamIdsAvailable = true;
        } else {// permission team or self profile.
            $teamIdsAvailable = [];
            if (($scopeTeamIds = Permission::getInstance()->isScopeTeam(null, $route))) {
                $teamIdsAvailable = is_array($scopeTeamIds) ? $scopeTeamIds : [];
            }
            // get list team_id responsible by pqa.
            $curEmp = Permission::getInstance()->getEmployee();
            $teamIdsResponsibleByPqa = PqaResponsibleTeam::getListTeamIdResponsibleTeam($curEmp->id);
            if (!$teamIdsResponsibleByPqa->isEmpty()) {
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsResponsibleByPqa->pluck('team_id')->toArray());
            }
            $teamIdsAvailable = array_unique($teamIdsAvailable);
            //ignore team childs
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
            if ($teamIds = Form::getFilterData('except', 'teams.id', $urlFilter)) {
                $teamIds = implode($teamIds, ', ');
            }
            if (!$teamIds) {
                $teamIds = null;
                $flagNoCheck = true;
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::select('name')
                    ->find($teamIds);
            }
        }
        $list = Task::getAllTask($teamIdsAvailable);
        if (count($list) > 0) {
            $list = CoreModel::pagerCollection($list, $pager['limit'], $pager['page']);
        }
        $projFilter = null;
        if (isset($dataFilter['projs.id'])) {
            $projFilter = Project::getProjectById($dataFilter['projs.id']);
        }
        return View('project::task.list', [
            'collectionModel' => $list,
            'projFilter' => $projFilter,
            'priority' => $priority,
            'statusIssue' => $statusIssue,
            'typeIssue' => $typeIssue,
            'teamIdCurrent' => $teamIds,
            'teamIdsAvailable' => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable
        ]);
    }

    public function saveCommentIssue(Request $request)
    {
        $data = $request->all();
        DB::beginTransaction();
        try {
            if (!$data['content']) {
                $validator = Validator::make($data, [
                    'content' => 'required',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }

            $dataContent = RiskComment::getMentions($data["content"]);
            $linkPattern = "/\[\:.+\:\]/";
            $content = trim(preg_replace($linkPattern, "", $dataContent));
            $linkPattern = "/\[\:.+\:\]/";
            $issueComment = new RiskComment();
            $issueComment->obj_id = $data['issue_id'];
            $content = trim(preg_replace($linkPattern, "", $data["content"]));
            $issueComment->content = $content;
            $issueComment->type = RiskComment::TYPE_ISSUE;
            $issueComment->created_by = Auth::user()->employee_id;
            $issueComment->save();

            if (!empty($data['attach_comment'][0]) && count($data['attach_comment'])) {
                if (isset($data['attach_comment'])) {
                    $valid = Validator::make($data, [
                        'attach_comment.*' => 'file|mimes:doc,docx,xlsx,pdf,png,jpg,gif,jpeg|max:5120',
                    ]);
                    if ($valid->fails()) {
                        return redirect()->back()->withErrors($valid)->withInput();
                    }

                    $messagesError = [
                        'success' => [
                            Lang::get('project::message.Error max size file'),
                        ]
                    ];
                    foreach ($data['attach_comment'] as $attach) {
                        if (in_array($attach->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                            if ($attach->getSize() >= 2048*1000) {
                                return redirect()->route('project::issue.detail', ['id' => $data['issue_id']])->with('messages', $messagesError);
                            }
                        } else {
                            if ($attach->getSize() >= 5120*1000) {
                                return redirect()->route('project::issue.detail', ['id' => $data['issue_id']])->with('messages', $messagesError);
                            }
                        }
                    }
                    RiskAttach::uploadFiles($issueComment->id, $data['attach_comment'], RiskAttach::TYPE_COMMENT);
                }
            }
            $project = Project::find($data['project_id']);
            $teamPqa = Team::getTeamPQAByType();
            if (isset($teamPqa)) {
                foreach ($teamPqa as $team) {
                    if (isset($team->mail_group)) {
                        if (empty($team->mail_group)) {
                            continue;
                        } else {
                            $this->sendMailRelatersForComment($team->mail_group, $team->name, $project, $data, Auth::user()->name);
                        }
                    }
                }
            }
            //send mail & noti to the person mentioned
            if (!empty($data["emp_mention"])) {
                Task::notiToPersonMentioned($data["emp_mention"], RiskComment::TYPE_ISSUE, $data['issue_id'], $content);
            }
            DB::commit();
            return redirect()->route('project::issue.detail', ['id' => $data['issue_id']])->with('messages', ['success' => [trans('project::message.Add comment successful.')]]);
        } catch (Exception $ex) {
            \Log::info($ex);
            DB::rollBack();
            return redirect()->route('project::issue.detail', ['id' => $data['issue_id']])->with('messages', ['error' => [trans('project::message.Add comment error.')]]);
        }
    }

    public function sendMailRelatersForComment($email, $name, $project, $data, $member)
    {
        $dataComment = [
            'email' => $email,
            'name' => $name,
            'url' => route("project::issue.detail", ['id' => $data['issue_id']]),
            'projectName' => $project->name,
            'issueContent' => $data['content'],
            'creator' => $member
        ];
        $subject = Lang::get("project::view.[Workoder] A issue has had a new comment");
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($email, $name)
            ->setSubject($subject)
            ->setTemplate("project::emails.issue_relater_comment", $dataComment)
            ->save();
    }

    public function deleteIssue(Request $request)
    {
        $issue = Task::deleteIssue($request);
        return response()->json($issue);
    }
}
