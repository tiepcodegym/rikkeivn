<?php

namespace Rikkei\Sales\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller as Controller;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Notify\Classes\RkNotify;
use Yajra\Datatables\Datatables;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Project;
use Auth;
use Illuminate\Support\Facades\Input;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Project\Model\Risk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Rikkei\Team\View\Permission;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\TaskAssign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Rikkei\Project\View\ValidatorExtend;
use Illuminate\Http\Request;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Project\Model\TaskRisk;
use Rikkei\Project\Http\Controllers\ProjectController;

class TrackingController extends Controller {
    
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('sales');
        Breadcrumb::add('Sales');
        Breadcrumb::add('Sales tracking');
    }

    public function index()
    {
        $type = Task::TYPE_GENERAL;
        $title = Lang::get('project::view.Task general create');
        $project = Project::getProjectsOfSaleTracking();
        
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
        $methods = Risk::getMethods();
        $results = Risk::getResults();
        return view('sales::tracking.index', [
            'taskStatus' => [
                Task::STATUS_NEW => 'New',
                Task::STATUS_PROCESS => 'Processing',
                Task::STATUS_RESOLVE => 'Resolved',
                Task::STATUS_FEEDBACK => 'Feedback',
                Task::STATUS_CLOSED => 'Closed',
                Task::STATUS_REJECT => 'Reject',
            ],
            'taskPriority' => Task::priorityLabel(),
            'taskType' => Task::typeFeedbackLabel(),
            'riskStatus' => Risk::statusLabel(),
            'levelsImportant' => Risk::levelImportantLabel(),
            'type' => $type,
            'taskItem' => new Task([
                'type' => $type
            ]),
            'accessEditTask' => true,
            'titlePage' => $title,
            'assignees' => [],
            'taskPriorities' => Task::priorityLabel(),
            'participants' => [],
            'project' => $project,
            'methods'  => $methods,
            'results'  => $results,
        ]);
    }

    /**
     * Get list task own
     * Load from ajax
     *
     * @param Datatables $datatables
     */
    public function myTasks(Datatables $datatables)
    {
        $options['hasUsedDatatables'] = true;
        $filter = ['status', 'title', 'project_name', 'pm', 'priority', 'created_at', 'duedate', 'assignee'];
        foreach ($filter as $field) {
            $options[$field] = Input::get($field);
        }

        $tasks = Task::getSelfRelateTask(Auth::user()->employee_id, $options);
        return $datatables->of($tasks)
            ->addColumn('', function ($model) {
                return '';
            })
            ->editColumn('title', function ($model) {
                return '<a target="_blank" href="'.route('project::task.edit', ['id' => $model->id]).'" >' . $model->title . '</a>';
            })
            ->editColumn('project_name', function ($model) {
                return '<a target="_blank" href="'.route('project::point.edit', ['id' => $model->project_id]).'" >' . $model->project_name . '</a>';
            })
            ->editColumn('status', function ($model) {
                return Task::statusLabel()[$model->status];
            })
            ->editColumn('priority', function ($model) {
                return Task::priorityLabel()[$model->priority];
            })
            ->editColumn('created_at', function ($model) {
                return date('Y-m-d', strtotime($model->created_at));
            })
            ->editColumn('duedate', function ($model) {
                if (empty($model->duedate)) {
                    return $model->duedate;
                }
                return date('Y-m-d', strtotime($model->duedate));
            })
            ->editColumn('email', function ($model) {
                return CoreView::getNickName($model->email);
            })
            ->editColumn('assignee', function ($model) {
                return CoreView::getNickName($model->assignee);
            })
            ->make(true);
    }

    public function customerFeedback(Datatables $datatables)
    {
        $filter = ['status', 'title', 'assignee', 'priority', 'created_at', 'type', 'project_name', 'duedate'];
        foreach ($filter as $field) {
            $options[$field] = Input::get($field);
        }
        $feedbacks = Task::getTasksComment($options);
        return $datatables->of($feedbacks)
            ->addColumn('', function ($model) {
                return '';
            })
            ->editColumn('created_at', function ($model) {
                return date('Y-m-d', strtotime($model->created_at));
            })
            ->editColumn('duedate', function ($model) {
                return date('Y-m-d', strtotime($model->duedate));
            })
            ->editColumn('email', function ($model) {
                return CoreView::getNickName($model->email);
            })
            ->editColumn('priority', function ($model) {
                return Task::priorityLabel()[$model->priority];
            })
            ->editColumn('status', function ($model) {
                return Task::statusLabel()[$model->status];
            })
            ->editColumn('type', function ($model) {
                return Task::typeLabel()[$model->type];
            })
            ->editColumn('title', function ($model) {
                return '<a target="_blank" href="'.route('project::task.edit', ['id' => $model->id]).'" >' . $model->title . '</a>';
            })
            ->editColumn('project_name', function ($model) {
                return '<a target="_blank" href="'.route('project::point.edit', ['id' => $model->project_id]).'#css" >' . $model->project_name . '</a>';
            })
            ->editColumn('count_issues', function ($model) {
                if (empty($model->count_issues)) {
                    return $model->count_issues;
                }
                return '<a data-direction="open" data-id="'.$model->id.'" href="javascript:void(0);" onclick="displayIssue(' . $model->id . ', this);">' .  $model->count_issues . ' <span class="glyphicon glyphicon-menu-down"></span></a>';
            })
            ->editColumn('title', function ($model) {
                $style = '';
                $today = Carbon::today();
                if($model->status != Task::STATUS_CLOSED && 
                    $model->duedate !== null      &&
                    $model->duedate->lt($today)
                ) {
                    $style = 'style="color:red"';
                }
                return '<a target="_blank" ' . $style . ' href="'.route('project::task.edit', ['id' => $model->id]).'" >' . $model->title . '</a>';
            })
            ->addColumn('', function ($model) {
                return '<button data-toggle="modal" data-id="'.$model->id.'" data-target="#feedbackchildModal" class="btn-add feedbackchildren_btn"><i class="fa fa-plus"></i></button>';
            })
            ->make(true);
    }

    public function risks(Datatables $datatables)
    {
        $riskStatus = Risk::statusLabel();
        $filter = ['status', 'content', 'weakness', 'owner', 'level_important', 'project'];
        foreach ($filter as $field) {
            $options[$field] = Input::get($field);
        }
        $risks = Risk::getRisksOfSale($options);
        return $datatables->of($risks)
            ->addColumn('', function ($model) {
                return '';
            })
            ->editColumn('level_important', function ($model) {
                return Risk::getKeyLevelRisk($model->level_important);
            })
            ->editColumn('status', function ($model) {
                return isset($riskStatus[$model->status]) ? $riskStatus[$model->status] : '';
            })
            ->editColumn('owner', function ($model) {
                return CoreView::getNickName($model->owner);
            })
            ->editColumn('content', function ($model) {
                return '<a target="_blank" href="'.route('project::report.risk.detail', ['id' => $model->id]).'" >' . $model->content . '</a>';
            })
            ->editColumn('project_name', function ($model) {
                return '<a target="_blank" href="'.route('project::project.edit', ['id' => $model->project_id]).'">' . $model->project_name . '</a>';
            })
            ->editColumn('count_task', function ($model) {
                if ($model->count_task) {
                    return '<a data-direction="open" data-id="'.$model->id.'" class="count-task" href="javascript:void(0);" >' .  $model->count_task . ' <span class="glyphicon glyphicon-menu-down"></span></a>';
                }
                return $model->count_task;
            })
            ->addColumn('', function ($model) {
                return '<button data-toggle="modal" data-id="'.$model->id.'" data-target="#riskchildModal" class="btn-add riskChild_btn"><i class="fa fa-plus"></i></button>';
            })
            ->make(true);
    }

    /**
     * save Relate task, customer feedback task. 
     */
    public function saveTasks(Request $request)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $taskId = Input::get('id');
        $dataTaskAssigns = Input::get('task_assign');
        $dataTaskParticipants = Input::get('task_participant');
        $response = [];
        $arrayRules = [];
        $arrayFillable = [
            'created_at' => null,
            'title' => null,
            'status' => null,
            'duedate' => null,
            'actual_date' => null,
            'priority' => null,
            'content' => null,
            'project_id' => null,
        ];
        $typeTask = Input::get('task.type');
        $projectId = Input::get('task.project_id');
        $parentId = Input::get('parent_id');
        $riskId = Input::get('risk_id');
        if (!$typeTask) {
            $typeTask = Input::get('type');
        }
        if ((!$typeTask) || ($projectId == null && !$parentId)) {
            $typeTask = Task::TYPE_GENERAL;
        }
        if ($typeTask != Task::TYPE_GENERAL) {
            if (!in_array($typeTask, Task::getTypeNormal(true))) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Error input data!');
                return response()->json($response);
            }
        }
        $dataTask = array_intersect_key(Input::get('task'), $arrayFillable);
        $task = new Task();
        $task->type = $typeTask;
        $task->created_by = Permission::getInstance()->getEmployee()->id;
        if (!empty(Input::get('parent_id'))) {
                $task->parent_id = Input::get('parent_id');
            if ($task->type == Task::TYPE_ISSUE_CSS) {
                $dataTask['project_id'] = Task::find(Input::get('parent_id'))['project_id'];
            }
        }
        // check permission
        if (!ViewProject::isAccessEditTaskGeneral($task)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        //validation and access
        if ($task->isTaskIssues() || 
            $task->type == Task::TYPE_QUALITY_PLAN
        ) {
            ValidatorExtend::addAfterEqual();
            $arrayRules = [
                'title' => 'required',
                'duedate' => 'required|date|after_equal:task.created_at',
                'status' => 'required',
                'priority' => 'required',
                'content' => 'required',
                'actual_date' => 'date|after_equal:task.created_at',
            ];
        } elseif ($task->isTaskCustomerIdea()) {
            $arrayRules = [
                'title' => 'required',
                'content' => 'required',
                'project_id' => 'required',
            ];
            unset($dataTask['duedate']);
            unset($dataTask['actual_date']);
        } elseif ($task->type == Task::TYPE_COMPLIANCE) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            return response()->json($response);
        } elseif ($task->type == Task::TYPE_RISK) {
            $arrayRules = [
                'created_at' => 'required|date',
                'title' => 'required',
                'duedate' => 'required|date',
                'status' => 'required',
                'priority' => 'required',
                'content' => 'required',
            ];
            if (!empty(Input::get('risk_id'))) {
                $dataTask['project_id'] = Risk::find(Input::get('risk_id'))['project_id'];
            }
        } elseif ($task->type == Task::TYPE_GENERAL) {
            $createdAt = Carbon::parse($task->created_at)->format('Y-m-d');
            $arrayRules = [
                'title' => 'required',
                'status' => 'required',
                'duedate' => 'required|date|after_equal_value:'.$createdAt,
                'priority' => 'required',
                'content' => 'required',
                'actual_date' => 'date|after_equal_value:'.$createdAt
            ];
            ValidatorExtend::addAfterEqualValue();
        } else {
            $arrayRules = [
                'status' => 'required'
            ];
            unset($dataTask['title']);
            unset($dataTask['duedate']);
            unset($dataTask['content']);
            unset($dataTask['priority']);
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
            if ($task->created_by && $task->created_by != 
                Permission::getInstance()->getEmployee()->id
            ) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Creator has only access to close');
                return response()->json($response);
            }
        }
        $task->setData($dataTask);
        $task->project_id = empty($task->project_id) ? null : $task->project_id;
        if ($task->id) {
            $typeCreateTask = TaskAssign::TYPE_STATUS_CHANGE;
        } else {
            $typeCreateTask = TaskAssign::TYPE_STATUS_NEW;
        }
        $saveReturl = [];
        DB::beginTransaction();
        try {
            $task->created_at = Carbon::parse(Input::get('created_at'))->format('Y-m-d');
            $task->save([], null, $saveReturl);
            TaskAssign::insertMembers($task, $dataTaskAssigns, [
                'type' => $typeCreateTask,
                'historyResult' => (isset($saveReturl['historyResult']) && 
                    is_object($saveReturl['historyResult'])) ? true : false,
                'send_created_by' => false
            ]);
            TaskAssign::insertMembers($task, $dataTaskParticipants, [
                'type' => $typeCreateTask,
                'historyResult' => (isset($saveReturl['historyResult']) && 
                    is_object($saveReturl['historyResult'])) ? true : false,
                'send_created_by' => false
            ], true);
            if (!$taskId && !empty(Input::get('risk_id'))) {
                TaskRisk::insert([
                    'task_id' => $task->id,
                    'risk_id' => Input::get('risk_id'),
                ]);
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
        Session::flash(
            'messages', [
                    'success'=> [
                        Lang::get('project::message.Save data success!'),
                    ]
                ]
        );
        $response['reload'] = 1;
        $response['popup'] = 1;
        return response()->json($response);
    }

    /**
     * save risks task. 
     */
    public function saveRisk(Request $request)
    {
        $data = $request->all();
        $requiredArray = ['content', 'level_important', 'weakness'];
        if(!isset($data['owner']) || $data['owner'] === ''){
            array_push($requiredArray, 'team_owner');
        }
        $rule = array_fill_keys($requiredArray, 'required');
        $validator = Validator::make($data, $rule);

        if ($validator->fails()) {
           return back()->with('messages', [
                'errors'=> [
                    Lang::get('project::message.Error input data!'),
                ]
            ]);
        }
        $curEmp = Permission::getInstance()->getEmployee();
        $data['created_by'] = $curEmp->id;
        $risk = Risk::store($data);
        if (!empty($risk)) {
            //Send mail to relaters of project
            $project = Project::find($data['project_id']);
            $relaters = Project::getRelatersOfProject($project, $risk);
            if (!empty($relaters)) {
                foreach ($relaters as $emp) {
                    $ProjectController = new ProjectController();
                    $ProjectController->sendMailRelaters($emp->email, $emp->name, $project, $risk, isset($data['id']));
                }
                //put notification
                $subject = !isset($data['id']) ? Lang::get("project::view.[Workoder] A risk has been created") :
                        $subject = Lang::get("project::view.[Workoder] A risk has been edit");
                \RkNotify::put($relaters->lists('id')->toArray(), $subject, route("project::project.edit", ['id' => $project->id]) . '#risk', ['category_id' => RkNotify::CATEGORY_PROJECT]);
            }
            
            $messages = [
                'success'=> [
                    Lang::get('project::message.Save risk success'),
                ]
            ]; 
        } else {
            $messages = [
                'success'=> [
                    Lang::get('project::message.Save risk error'),
                ]
            ]; 
        }
        if ($redirectUrl = $request->get('redirectUrl')) {
            return redirect()->to($redirectUrl)->with('messages', $messages);
        }
        return redirect()->to(route('sales::tracking', ['id' => $data['project_id']]) . "#risk")->with('messages', $messages);
    }
}
