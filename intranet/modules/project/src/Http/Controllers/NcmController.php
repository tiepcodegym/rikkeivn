<?php

namespace Rikkei\Project\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Project\Model\Project;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\Task;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\TaskNcmRequest;
use Rikkei\Team\View\TeamList;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\Model\TaskTeam;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Project\Model\TaskComment;
use Rikkei\Project\Model\TaskHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Mpdf\Mpdf;
use Rikkei\Project\Model\RiskAttach;
use Rikkei\Notify\Classes\RkNotify;

class NcmController extends Controller
{
    /**
     * create ncms
     * 
     * @param int $id
     * @return json
     */
    public function create($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $project = Project::find($id);
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
        }
        if (!$project->isOpen()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Project isnot in status New or Processing');
        }
        // check permission view
        if (!ViewProject::isAccessViewProject($project)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You dont have access');
        }
        $htmlView = view('project::ncm.include.form', [
            'project' => $project,
            'taskItem' => new Task([
                'type' => Task::TYPE_COMPLIANCE
            ]),
            'taskNcmRequest' => new TaskNcmRequest(),
            'taskAssign' => new TaskAssign(),
            'accessEditTask' => true,
            'teamsOptionAll' => TeamList::toOption(null, true, false),
            'teamsSelected' => [],
            'viewMode' => false,
            'taskStatusAll' => Task::getStatusTypeNormal()
        ])->render();
        $response['popup'] = 1;
        $response['success'] = 1;
        $response['htmlModal'] = $htmlView;
        return response()->json($response);
    }
    
    /**
     * edit task
     */
    public function edit($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $task = Task::find($id);
        if (!$task || $task->type != Task::TYPE_COMPLIANCE) {
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
            $response['message'] = Lang::get('project::message.You dont have access');
            return response()->json($response);
        }
        $assignees = TaskAssign::getAssignee($task->id);
        $accessEditTask = ViewProject::isAccessEditTask($project, $task->type, $task);
        if ($accessEditTask ||
            in_array(Permission::getInstance()->getEmployee()->id, $assignees)
        ) {
            $accessEditTask = true;
            $taskStatusAll = Task::getStatusTypeNormal();
        } else {
            $accessEditTask = false;
            $taskStatusAll = [];
        }
        $htmlView = view('project::ncm.include.form', [
            'project' => $project,
            'taskItem' => $task,
            'taskCommentList' => TaskComment::getGridData($id),
            'taskHistoryList' => TaskHistory::getGridData($id),
            'taskNcmRequest' => TaskNcmRequest::findNcmFollowTask($task, 
                ['findRequester' => true]),
            'taskAssign' => TaskNcmRequest::findNcmAssign($task),
            'accessEditTask' => $accessEditTask,
            'teamsOptionAll' => $accessEditTask ? 
                TeamList::toOption(null, true, false) : null,
            'teamsSelected' => $accessEditTask ? TaskTeam::getNcmTeams($task) : 
                TaskTeam::getNcmTeamAndName($task),
            'viewMode' => false,
            'taskStatusAll' => $taskStatusAll
        ])->render();
        $response['popup'] = 1;
        $response['success'] = 1;
        $response['htmlModal'] = $htmlView;
        return response()->json($response);
    }
    
    /**
     * save ncms
     */
    public function save()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $taskId = Input::get('id');
        $dataTask = Input::get('task');
        $dataTaskTeams = Input::get('teams');
        $dataTaskNcmRequest = Input::get('ncm');
        $dataTaskAssigns = (array) Input::get('task_assign');
        $response = [];
        $typeTask = Task::TYPE_COMPLIANCE;
        //init task
        if (!$taskId) {
            $idEdit = false;
            $project = Project::find(Input::get('project_id'));
            if (!$project) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Not found item.');
                return response()->json($response);
            }
            // project open => createable task
            if (!$project->isOpen()) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Project closed');
                return response()->json($response);
            }
            $task = new Task();
            $task->type = $typeTask;
            $task->created_by = Permission::getInstance()->getEmployee()->id;
            $task->project_id = $project->id;
        } else {
            $idEdit = true;
            $task = Task::find($taskId);
            if (!$task || $task->type != $typeTask) {
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
            unset($dataTask['type']);
        }

        //validation and access
        $accessEditTask = ViewProject::isAccessEditTask($project, $typeTask, $task);
        if (!$accessEditTask) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        if ($accessEditTask) {
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
        $arrayRulesTask = [
            'title' => 'required|max:255',
            'content' => 'required',
            'duedate' => 'required',
        ];
        $arrayRulesNcm = [
            'request_date' => 'required',
            'requester' => 'required',
            'fix_reason' => 'required',
            'document' => 'max:255',
            'request_standard' => 'max:255',
        ];
        $validator = Validator::make(array_merge($dataTask, $dataTaskNcmRequest),
            array_merge($arrayRulesTask, $arrayRulesNcm));
        if ($validator->fails()
            || !$dataTaskTeams || !count($dataTaskTeams)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Please fill full data');
            return response()->json($response);
        }
        $task->setData($dataTask);
        if ($task->id) {
            $typeCreateTask = TaskAssign::TYPE_STATUS_CHANGE;
        } else {
            $typeCreateTask = TaskAssign::TYPE_STATUS_NEW;
        }
        DB::beginTransaction();
        try {
            $task->save([], $project, [
                'ncm' => $dataTaskNcmRequest,
                'task_assign' => $dataTaskAssigns,
                'teams' => $dataTaskTeams,
                'is_create' => !$taskId,
            ]);
            $taskNcmRequest = TaskNcmRequest::findNcmFollowTask($task);
            //check data
            if (!isset($dataTaskNcmRequest['evaluate_date']) || !$dataTaskNcmRequest['evaluate_date']) {
                $dataTaskNcmRequest['evaluate_date'] = null;
            }
            $taskNcmRequest->setData($dataTaskNcmRequest);
            $taskNcmRequest->save();
            TaskNcmRequest::insertNcmAssigners($task, $dataTaskAssigns);
            TaskTeam::insertNcmTeams($task, $dataTaskTeams);
            DB::commit();
            $response['reload'] = 1;
            $response['popup'] = 1;
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Save data success!');
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('project::message.Save data success!'),
                        ]
                    ]
            );

            // Send mail and noti to PM
            $route = route('project::report.ncm.detail', ['id' => $taskNcmRequest->task_id]);
            \RkNotify::put(
                $project->manager_id,
                $idEdit ? trans('project::view.A non-compliant process has been edited') : trans('project::view.A new non-compliant process has been created'),
                $route
            );
            self::sendMailToPM($project, $route, $idEdit);

            return response()->json($response);
        } catch (Exception $ex) {
            DB::rollback();
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
        
        if (!$task->isTaskCustomerIdea()) {
            $dataTaskAssigns = reset($dataTaskAssigns);
        }
        switch ($task->type) {
            case Task::TYPE_QUALITY_PLAN:
                $response['refresh'] = URL::route('project::project.edit', 
                ['id' => $project->id]) . '#' . 
                    Task::getNameTabWOItem(Task::TYPE_WO_QUALITY_PLAN);
                break;
            case Task::TYPE_COMMENDED: case Task::TYPE_CRITICIZED:
                $response['refresh'] = URL::route('project::point.edit', 
                    ['id' => $project->id]) . '#css';
                break;
            case Task::TYPE_COMPLIANCE:
                $response['refresh'] = URL::route('project::point.edit', 
                    ['id' => $project->id]) . '#process';
                break;
            default:
                $response['refresh'] = URL::route('project::task.index', 
                    ['id' => $project->id]);
        }
        $response['popup'] = 1;
        return response()->json($response);
    }
    
    /**
     * Delete task ncm
     * 
     * @param int $id
     */
    public function delete($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $typeTask = Task::TYPE_COMPLIANCE;
        $task = Task::find($id);
        if (!$task || $task->type != $typeTask) {
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
        //validation and access
        $accessEditTask = ViewProject::isAccessEditTask($project, $typeTask, $task);
        if (!$accessEditTask) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        try {
            $task->delete([
                'project' => $project
            ]);
            $response['reload'] = 1;
            $response['popup'] = 1;
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Delete item success');
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('project::message.Delete item success'),
                        ]
                    ]
            );
            return response()->json($response);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * view list ncm
     */
    public function viewList()
    {
        Breadcrumb::add('Report', URL::route('project::report.ncm'));
        Breadcrumb::add('NCM', URL::route('project::report.ncm'));
        Menu::setActive('Report statistic', 'report');
        
        return view('project::ncm.view.list', [
            'collectionModel' => TaskNcmRequest::getGridData(),
            'titlePage' => Lang::get('project::view.Report NC'),
            'ncStatusAll' => Task::statusNCLabel(),
            'taskPriority' => Task::priorityLabel(),
            'owners' => Employee::select('id', 'email')->get(),
        ]);
    }

    public function saveNC(Request $request)
    {
        $dataIssue = $request->all();
        $curEmp = Permission::getInstance()->getEmployee();
        $isRootOrAdmin = Permission::getInstance()->isRootOrAdmin();
        $requiredArray = ['title', 'employee_owner', 'priority', 'employee_assignee', 'employee_approver'];
        $rule = array_fill_keys($requiredArray, 'required');
        $validator = Validator::make($dataIssue, $rule);
        if ($validator->fails()) {
            return back()->with('messages', [
                'errors' => [
                    Lang::get('project::message.Error input data!'),
                ]
            ]);
        }
        if (isset($dataIssue['id'])) {
            $nc = Task::find($dataIssue['id']);            
        }

        $dataIssue['created_by'] = $curEmp->id;
        $dataIssue['type'] = Task::TYPE_NC;
        DB::beginTransaction();
        try {
            $issue = Task::store($dataIssue);

            $statusNC = $dataIssue['status'];
            $isEdit = isset($dataIssue['isEdit']) ? true : false;
            if ($isEdit && !empty($statusNC) && in_array($statusNC, [Task::STATUS_CLOSED, Task::STATUS_REJECT])) {
                $approver = TaskAssign::where('task_id', $issue->id)->where('role', TaskAssign::ROLE_APPROVER)->where('status', TaskAssign::STATUS_NO)->first();
                if (($approver && $curEmp->id != $approver->employee_id) && !$isRootOrAdmin) {
                    $messages = [
                        'errors' => [
                            "You don't have permission to edit status close or cancelled"
                        ]
                    ];
                    return redirect()->route('project::nc.detail', ['id' => $issue->id])->with('messages', $messages);
                }
            }

            TaskAssign::delByIssue($issue->id);
            $taskAssign = [
                [
                    'task_id' => $issue->id,
                    'employee_id' => $dataIssue['employee_owner'],
                    'role' => TaskAssign::ROLE_OWNER,
                    'status' => TaskAssign::STATUS_NO,
                ],
                [
                    'task_id' => $issue->id,
                    'employee_id' => $dataIssue['employee_assignee'],
                    'role' => TaskAssign::ROLE_ASSIGNEE,
                    'status' => TaskAssign::STATUS_NO,
                ],
                [
                    'task_id' => $issue->id,
                    'employee_id' => $dataIssue['employee_approver'],
                    'role' => TaskAssign::ROLE_APPROVER,
                    'status' => TaskAssign::STATUS_NO,
                ],
            ];
            if (!empty($dataIssue['reporter'])) {
                $taskAssign[] = [
                    'task_id' => $issue->id,
                    'employee_id' => $dataIssue['reporter'],
                    'role' => TaskAssign::ROLE_REPORTER,
                    'status' => TaskAssign::STATUS_NO,
                ];
            }
            TaskAssign::insert($taskAssign);

            TaskTeam::delByIssue($issue->id);
            $taskTeam = [
                'task_id' => $issue->id,
                'team_id' => $dataIssue['team'],
            ];
            TaskTeam::insert($taskTeam);

            if (isset($dataIssue['attach'])) {
                $valid = Validator::make($dataIssue, [
                    'attach.*' => 'file|mimes:doc,docx,xlsx,pdf,png,jpg,gif,jpeg|max:5120',
                ]);
                if ($valid->fails()) {
                    return redirect()->back()->withErrors($valid)->withInput();
                }
                RiskAttach::uploadFiles($issue->id, $dataIssue['attach'], RiskAttach::TYPE_NC);
            }

            $cmt = TaskComment::where('task_id', $issue->id)->first();
            if (!$cmt || $cmt->content != $dataIssue['comment']) {
                TaskComment::delByTaskId($issue->id);
                if (!empty($dataIssue['comment'])) {
                    TaskComment::create([
                        'task_id' => $issue->id,
                        'content' => $dataIssue['comment'],
                        'created_by' => $dataIssue['created_by'],
                    ]);
                }
            }

            $receivers = $this->getMailReceiver($issue->id);
            //send mail noti when create
            if (!$isEdit) {
                $this->sendMailWhenCreate($receivers, $issue, $curEmp);
            }
            //send mail noti when update status
            if (isset($nc) && $nc->status != $statusNC) {
                $this->sendMailWhenUpdateStatus($receivers, $nc, $nc->status, $statusNC, $curEmp);
            }

            $messages = [
                'success' => [
                    'Save NC success'
                ]
            ];
            
            DB::commit();
            if ($redirectUrl = $request->get('redirectUrl')) {
                return redirect()->to($redirectUrl)->with('messages', $messages);
            }
            return redirect()->route('project::nc.detail', ['id' => $issue->id])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollBack();
            \Log::info($ex);
            $messages = [
                'errors' => [
                    'Save NC error'
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        }
    }

    private function getMailReceiver($ncId)
    {
        $tblTaskAssign = TaskAssign::getTableName();
        $tblEmp = Employee::getTableName();
        $tblTask = Task::getTableName();
        $arrRole = [
            TaskAssign::ROLE_ASSIGNEE,
            TaskAssign::ROLE_REPORTER,
            TaskAssign::ROLE_APPROVER
        ];
        $ncReceivers = TaskAssign::select(
            "{$tblTaskAssign}.*",
            "{$tblTask}.title as task_title",
            "{$tblEmp}.name as emp_name",
            "{$tblEmp}.email as emp_email"
        )
        ->join("{$tblTask}", "{$tblTaskAssign}.task_id", '=', "{$tblTask}.id")
        ->join("{$tblEmp}", "{$tblTaskAssign}.employee_id", '=', "{$tblEmp}.id")
        ->where('task_id', $ncId)
        ->whereIn('role', $arrRole)
        ->get();
        return $ncReceivers;
    }

    private function sendMailWhenCreate($receivers, $nc, $curEmp)
    {
        $project = Project::find($nc->project_id);
        $data = [
            'ncTitle' => $nc->title,
            'projectName' => $project ? $project->name : '',
            'creator' => $curEmp->name,
            'url' => route('project::nc.detail', ['id' => $nc->id])
        ];
        foreach ($receivers as $item) {
            if ($curEmp->email != $item->emp_email) {
                $data['name'] = $item->emp_name;
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($item->emp_email, $item->emp_name)
                    ->setSubject("Một NC vừa được tạo mới")
                    ->setTemplate("project::emails.nc_create", $data)
                    ->save();
            }
        }
    }

    private function sendMailWhenUpdateStatus($receivers, $nc, $statusOld, $statusNew, $curEmp)
    {
        $statusLabel = Task::statusNCLabel();
        $statusOld = in_array($statusOld, array_keys($statusLabel)) ? $statusLabel[$statusOld] : '';
        $statusNew = in_array($statusNew, array_keys($statusLabel)) ? $statusLabel[$statusNew] : '';
        $project = Project::find($nc->project_id);
        $data = [
            'statusOld' => $statusOld,
            'statusNew' => $statusNew,
            'ncTitle' => $nc->title,
            'projectName' => $project ? $project->name : '',
            'url' => route('project::nc.detail', ['id' => $nc->id])
        ];
        foreach ($receivers as $item) {
            if ($curEmp->email != $item->emp_email) {
                $data['name'] = $item->emp_name;
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($item->emp_email, $item->emp_name)
                    ->setSubject("Một NC đã được cập nhật trạng thái")
                    ->setTemplate("project::emails.nc_update_status", $data)
                    ->save();
            }
        } 
    }

    public function detail($ncId)
    {
        Breadcrumb::add(Lang::get('project::view.Detail'));
        $ncInfo = Task::getById($ncId);
        if (!$ncInfo) {
            return redirect()->back()->with('messages', ['errors' => [trans('project::message.NC not found')]]);
        }
        $projectId = $ncInfo->project_id;
        $curEmp = Permission::getInstance()->getEmployee();

        $projs = Project::find($projectId);
        $relaters = Project::getRelatersOfProject($projs, null, true);
        return View('project::nc.detail',
        [
            'id' => $ncId,
            'curEmp' => $curEmp,
            'ncInfo' => $ncInfo,
            'projectId' => $projectId,
            'project' => Project::getTeamInChargeOfProject($projectId),
            'comments' => TaskComment::getCommentOfTask($ncId),
            'relaters' => $relaters,
            'attachs' => isset($ncId) ? RiskAttach::getAttachs($ncId, RiskAttach::TYPE_NC) : null,
            'isEdit' => true
        ]);
    }

    public function deleteNC(Request $request)
    {
        $issue = Task::deleteIssue($request);
        return response()->json($issue);
    }
    
    
    public function viewDetail($id)
    {
        $task = Task::find($id);
        if (!$task || $task->type != Task::TYPE_COMPLIANCE) {
            return redirect()->route('project::report.ncm')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        $project = $task->getProject();
        if (!$project) {
            return redirect()->route('project::report.ncm')
                ->withErrors(Lang::get('project::message.Not found item.'));
        }
        Breadcrumb::add('Report', URL::route('project::report.ncm'));
        Breadcrumb::add('NCM', URL::route('project::report.ncm'));
        Breadcrumb::add('Detail');
        Menu::setActive('Report statistic', 'report');
        return view('project::ncm.view.detail', [
            'project' => $project,
            'taskItem' => $task,
            'taskNcmRequest' => TaskNcmRequest::findNcmFollowTask($task, 
                ['findRequester' => true]),
            'taskAssign' => TaskNcmRequest::findNcmAssign($task),
            'accessEditTask' => false,
            'teamsOptionAll' => null,
            'teamsSelected' => TaskTeam::getNcmTeamAndName($task),
            'viewMode' => true,
            'titlePage' => Lang::get('project::view.View NCM detail'),
            'taskCommentList' => TaskComment::getGridData($id),
            'taskHistoryList' => TaskHistory::getGridData($id)
        ]);
    }

    /*
     *export file PDF
     *
     */
    public function getPDF(Request $request)
    {
        $task = Task::find($request->taskId);
        $ncmRequest = TaskNcmRequest::find($request->task);
        $taskNcmRequest = TaskNcmRequest::findNcmFollowTask($task, ['findRequester' => true]);
        if (isset($taskNcmRequest)) {
            $taskNcmRequest->request_date = Carbon::parse($taskNcmRequest->request_date)->toDateString();
            $taskNcmRequest->actual_date = Carbon::parse($taskNcmRequest->actual_date)->toDateString();
            if ($taskNcmRequest['evaluate_date']) {
                $taskNcmRequest->evaluate_date = Carbon::parse($taskNcmRequest->evaluate_date)->toDateString();
            }
            if ($task['actual_date']) {
                $task->actual_date = Carbon::parse($task->actual_date)->toDateString();
            }
        }
        $ncmRequestResultLabels = TaskNcmRequest::getTestResultLabels();
        $taskAssign = TaskNcmRequest::findNcmAssign($task);
        $teamsSelected = TaskTeam::getNcmTeamAndName($task);
        if ($request->has('download')) {
            $mpdf = new \Mpdf\Mpdf(['tempDir' => storage_path('app/mpdf_tmp')]);
            $mpdf->setFooter('{PAGENO}');
            $mpdf->curlAllowUnsafeSslRequests = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            $mpdf->WriteHTML(view('project::ncm.pdf.form', [
                'task' => $task,
                'ncmRequest' => $taskNcmRequest,
                'taskAssign' => $taskAssign,
                'teamsSelected' => $teamsSelected,
                ])->render());
            $mpdf->Output();
        }
    }

    /**
     * Send mail to PM when create or edit NCM
     * @param $project
     * @param $route
     * @param $isEdit
     * @throws Exception
     */
    public function sendMailToPM($project, $route, $isEdit)
    {
        $emailQueue = new EmailQueue();

        $pm = Employee::where('id', $project->manager_id)->select('name', 'email')->first()->toArray();
        $data = [
            'dear_name' => $pm['name'],
            'project_name' => $project->name,
            'subject' => trans('project::view.Process None Compliance'),
            'route' => $route,
            'isEdit' => $isEdit
        ];
        $emailQueue->setTo($pm['email'], $project->name)
            ->setSubject($data['subject'])
            ->setTemplate("project::ncm.include.ncm_noti", $data)
            ->save();
    }
}

