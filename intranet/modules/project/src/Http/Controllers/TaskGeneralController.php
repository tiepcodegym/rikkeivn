<?php

namespace Rikkei\Project\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\TaskComment;
use Rikkei\Project\Model\TaskHistory;
use Rikkei\Project\View\ValidatorExtend;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Permission;

class TaskGeneralController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('Profile', 'profile');
    }

    /**
     * Display task list page
     */
    public function index()
    {
        Breadcrumb::add('Task general', route('project::task.general.list'));
        $status = Task::getStatusTypeNormal();
        $statusMySelf = Task::getStatusTypeMySelf();
        $title = Lang::get('project::view.Task general');
        $titleMySelf = Lang::get('project::view.My task');
        $urlMyTask = route('project::task.general.list', ['tab' => 'self']);
        $urlGeneralTask = route('project::task.general.list', ['tab' => 'general']);
        $userCurrent = Permission::getInstance()->getEmployee();
        $isTypeFilter = Form::getFilterData('exception', 'proj_type', $urlGeneralTask);
        if (!$isTypeFilter) {
            $filterAll = (array) Form::getFilterData(null, null, $urlGeneralTask);
            $emailFilter = [
                'email' => $userCurrent->email
            ];
            $statusFilter = [
                'in' => [
                    'tasks.status' => [
                        Task::STATUS_NEW,
                        Task::STATUS_PROCESS,
                        Task::STATUS_RESOLVE,
                        Task::STATUS_FEEDBACK,
                    ]
                ]
            ];
            $flagTypeFilter = [
                'exception' => [
                    'proj_type' => 1
                ]
            ];
            $filterAll = array_merge_recursive($filterAll, $emailFilter, $flagTypeFilter, $statusFilter);
            CookieCore::setRaw('filter.' . $urlGeneralTask, $filterAll);
            return redirect()->away(app('request')->fullUrl());
        }

        return view('project::task.index_general', [
            'collectionModel' => Task::getGridDataGeneral($urlGeneralTask),
            'taskStatus' => $status,
            'taskStatusMySelf' => $statusMySelf,
            'taskPriorities' => Task::priorityLabel(),
            'titlePage' => $title,
            'titlePageMySelf' => $titleMySelf,
            'taskType' => Task::typeLabel(),
            'urlMyTask' => $urlMyTask,
            'urlGeneralTask' => $urlGeneralTask,
            'collectionModelMySelf' => Task::getGridDataSelfTask(
                Permission::getInstance()->getEmployee()->id,
                $urlMyTask
            ),
            'countNewOrProcessGeneralTask' => Task::getGridDataGeneral($urlGeneralTask, $countTask = true),
            'countNewOrProcessSelfTask' => Task::getGridDataSelfTask(
                Permission::getInstance()->getEmployee()->id,
                $urlMyTask, $countSelfTask = true
            ),
        ]);
    }
    
    /**
     * create general task
     */
    public function create()
    {
        $type = Task::TYPE_GENERAL;
        Breadcrumb::add('Task general', route('project::task.general.list'));
        Breadcrumb::add('Task general create', route('project::task.general.create'));
        return view('project::task.edit_general', [
            'taskStatus' => Task::getStatusTypeNormal(),
            'taskPriorities' => Task::priorityLabel(),
            'taskItem' => new Task([
                'type' => $type
            ]),
            'accessEditTask' => true,
            'assignees' => [],
            'participants' => [],
            'creatorTask' => null,
            'titlePage' => Lang::get('project::view.Task general create')
        ]);
    }
    
    /**
     * create general task
     */
    public function edit($id, $task = null)
    {
        if (!$task) {
            $task = Task::find($id);
            if (!$task) {
                return redirect()->route('project::task.general.list')
                    ->withErrors(Lang::get('project::message.Not found item.'));
            }
        }
        $permission = Permission::getInstance();
        if (!ViewProject::isAccessEditTaskGeneral($task, 'project::task.general.view')) {
            View::viewErrorPermission();
        }
        $creator = $task->created_by;
        if ($creator) {
            $creator = Employee::find($creator);
            if (!$creator) {
                $creator = null;
            }
        }
        Breadcrumb::add('Task general', route('project::task.general.list'));
        Breadcrumb::add('Task general create', route('project::task.general.create'));
        return view('project::task.edit_general', [
            'taskStatus' => Task::getStatusTypeNormal(),
            'taskPriorities' => Task::priorityLabel(),
            'taskItem' => $task,
            'accessEditTask' => ViewProject::isAccessEditTaskGeneral($task),
            'creatorTask' => $creator,
            'titlePage' => Lang::get('project::view.Task general create'),
            'assignees' => TaskAssign::getAssigneesInfo($task->id),
            'participants' => TaskAssign::getAssigneesInfo($task->id,true),
            'taskCommentList' => TaskComment::getGridData($id),
            'taskHistoryList' => TaskHistory::getGridData($id),
            'showComment' => true
        ]);
    }
    
    /**
     * save task general
     */
    public function save($id = null)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $dataTaskAssigns = Input::get('task_assign');
        $dataTaskParticipants = Input::get('task_participant');
        $response = [];
        $typeTask = Task::TYPE_GENERAL;
        $arrayFillable = [
            'title' => null,
            'status' => null,
            'duedate' => null,
            'actual_date' => null,
            'priority' => null,
            'content' => null
        ];
        $dataTask = array_intersect_key(Input::get('task'), $arrayFillable);
        //init task
        if (!$id) {
            $task = new Task();
            $task->type = $typeTask;
            $task->created_by = Permission::getInstance()->getEmployee()->id;
        } else {
            $task = Task::find($id);
            if (!$task) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::message.Not found item.');
                return response()->json($response);
            }
        }
        // check permission
        if (!ViewProject::isAccessEditTaskGeneral($task)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
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
        if ($task->id) {
            $typeCreateTask = TaskAssign::TYPE_STATUS_CHANGE;
        } else {
            $typeCreateTask = TaskAssign::TYPE_STATUS_NEW;
        }
        $saveReturl = [];
        DB::beginTransaction();
        try {
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
            //callback after insert task
            if ($callbackFunc = Input::get('callback')) {
                call_user_func($callbackFunc, Input::get('employee_id'), $task->id);
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
        $isPopup = Input::get('is_popup');
        if (!$isPopup) {
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('project::message.Save data success!'),
                        ]
                    ]
            );
            $response['refresh'] = URL::route('project::task.edit', 
                    ['id' => $task->id]);
        } else {
            $response['task_link'] = URL::route('project::task.general.create.ajax', ['id' => $task->id]);
            $response['task_id'] = $task->id;
        }
        $response['popup'] = 1;
        return response()->json($response);
    }

    // save task priority or task status
    public function updatePriorityStatus(Request $request) {
        if (!($request->ajax())) {
            return redirect('/');
        }
        $id = $request->input('id');
        $status = $request->input('status');
        $priority = $request->input('priority');
        $response = [];
        $arrayFillable = [
            'status' => null,
            'priority' => null,
        ];
        $dataTask = array_intersect_key($request->all(), $arrayFillable);
        //find task
        $task = Task::find($id);
        if (!$task) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $project = $task->getProject();
        // if type of task is wo or reward, it won't be allow to edit
        if ((int)$task->type === Task::TYPE_WO || (int)$task->type === Task::TYPE_REWARD) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Can not be changed values');
            return response()->json($response);
        }
        // check permission
        if (!ViewProject::isAccessEditTaskGeneral($task) && !Task::hasEditStatusTasks($task, $project)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        if ($priority) {
            $arrayRules = [
                'priority' => 'required',
            ];
            unset($dataTask['status']);
        }
        if ($status) {
             $arrayRules = [
                'status' => 'required',
            ];
            unset($dataTask['priority']);
        }
        $validator = Validator::make($dataTask, $arrayRules);
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            return response()->json($response);
        }
        $task->setData($dataTask);
        $saveReturl = [];
        DB::beginTransaction();
        try {
            $task->save([], null, $saveReturl);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            return response()->json($response);
        }
        $response['success'] = 1;
        $response['refresh'] = URL::route('project::task.general.list');
        $response['priority'] = $task->getPriority();
        $response['priority_value'] = $task->priority;
        $response['status'] = $task->getStatus();
        $response['status_value'] = $task->status;
        return response()->json($response);
    }

    /*
     * render create task ajax
     */
    public function createAjax($id = null)
    {
        $creator = null;
        if ($id) {
            $task = Task::find($id);
            if (!$task) {
                return response()->json('Not found task!', 404);
            }
            if (!ViewProject::isAccessEditTaskGeneral($task, 'project::task.general.view')) {
                View::viewErrorPermission();
            }
            $creator = Employee::find($task->created_by);
        } else {
            $task = new Task(['type' => Task::TYPE_GENERAL]); 
        }

        return [
            'html' => view('project::task.include.task_general_body', [
                'taskStatus' => Task::getStatusTypeNormal(),
                'taskPriorities' => Task::priorityLabel(),
                'taskItem' => $task,
                'accessEditTask' => $id ? ViewProject::isAccessEditTaskGeneral($task) : true,
                'creatorTask' => $creator,
                'titlePage' => Lang::get('project::view.Task general create'),
                'assignees' => $id ? TaskAssign::getAssigneesInfo($task->id) : [],
                'participants' => $id ? TaskAssign::getAssigneesInfo($task->id, true) : [],
                'isPopup' => 1,
            ])->render()
        ];
    }
    
    // ========== stask project task ==========
    public function importProjectTask(Request $request)
    {
        DB::beginTransaction();
        try {
            $file = Input::file('file_upload');
            if (!$file) {
                return redirect()->back()->withErrors(['errors' => [Lang::get('event::message.miss file')]]);
            }
            $exFile = $file->getClientOriginalExtension();
            if (!in_array($exFile, ['xlsx', 'xls'])) {
                return redirect()->back()->withErrors(Lang::get('asset::message.File not invalid'));
            }
            
            $excel = Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader) {
                $reader->setHeaderRow(4);
            })->get();
            $headings = $excel->getHeading();
            $dataFile = $excel->toArray();
            if (!$this->checkHeadingFileProjectTask($headings)) {
                return redirect()->back()->withErrors(Lang::get('asset::message.File not invalid'));
            }
            
            $messages = [Lang::get('asset::message.Import success')];
            if (!$dataFile) {
                return redirect()->back()->with('messages', ['success' => $messages]);
            }
            $data = $this->validationFileProjectTask($dataFile);
            if ($data['messageErrors']) {
                $messages[] = 'Các hàng sau không được import: ';
                $messages = array_merge($messages, $data['messageErrors']);
            }
            if (!$data['fileImportTask']) { return redirect()->back()->withErrors($messages);}

            // get and check name project by name;
            $messagesError = $this->checkNameProject($data['fileImportTask'], $data['file_name_project']);
            $messages = array_merge($messages, $messagesError);
            if (!$data['fileImportTask']) {return redirect()->back()->withErrors($messages);}
            // check nhan vien assgin, người tham gia - tạm dừng

            $empAssgin = Employee::getEmpByEmails($data['fileImportTaskAssign'], ['email','id']);
            $arrayEmpAssgin = $this->getArrayWithKeyValue($empAssgin, 'id', 'email');
            if ($arrayEmpAssgin) {
                $emailEmpAssgin = array_values($arrayEmpAssgin);
            } else {
                $emailEmpAssgin = [];
            }
            $empAssginParticipant = Employee::getEmpByEmails($data['fileImportTaskAssignParticipant'], ['email','id']);
            $arrayEmpAssginParticipant  = $this->getArrayWithKeyValue($empAssginParticipant, 'id', 'email');
            if ($arrayEmpAssgin) {
                $emailEmpAssginParticipant = array_values($arrayEmpAssginParticipant);
            } else {
                $emailEmpAssginParticipant = [];
            }
            foreach ($data['fileImportTask'] as $key => $dataInsert) {
                $emaillFileAssgin = $data['fileImportTaskAssign'][$key];
                if (in_array($emaillFileAssgin, $emailEmpAssgin)) {
                    $task = Task::create($dataInsert);
                    $dataAssgin = [
                        'task_id' => $task->id,
                        'employee_id' => array_search($emaillFileAssgin, $arrayEmpAssgin),
                        'role' => TaskAssign::ROLE_OWNER,
                    ];
                    TaskAssign::insert($dataAssgin);
                    if (in_array($emaillFileAssgin, $emailEmpAssginParticipant)) {
                        $dataAssginParticipant   = [
                            'task_id' => $task->id,
                            'employee_id' => array_search($emaillFileAssgin, $arrayEmpAssginParticipant),
                            'role' => TaskAssign::ROLE_PARTICIPANT,
                        ];
                        TaskAssign::insert($dataAssginParticipant);
                    }
                } else {
                    $messageErrors[] = 'Hàng ' . ($key + 5) . ' lỗi: người được assign không tìm thấy';
                }
            }
            DB::commit();
            return redirect()->back()->with('messages', ['success' => $messages]);
    } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = ['errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
            
    }
    
    public function checkNameProject(&$fileImportTask, $fileNameProject)
    {
        $messages = [];
        $objProject = new Project();
        $projects = $objProject->getProjectByname($fileNameProject);
        if ($projects) {
            $arrProject = $this->getArrayWithKeyValue($projects, 'id', 'name');
            $nameProjsCount = array_count_values($arrProject);
            $nameProjectError = $this->checkNumberNameProj($nameProjsCount);
            if ($nameProjectError) {
                foreach($nameProjectError as $name) {
                    $key = array_search($name, $fileNameProject);
                    $messages[] = 'Hàng ' . ($key + 5) . ' lỗi do tên dự án, trong dự án có nhiều tên này';
                    unset($fileImportTask[$key]);
                }
            }
            $nameProjectsDB = array_values($arrProject);
            foreach($fileImportTask as $key => $item) {
                if (!in_array($item['du_an'], $nameProjectsDB)) {
                    $messages[] = 'Hàng ' . ($key + 5) . ' lỗi do tên dự án không tồn tại';
                    unset($fileImportTask[$key]);
                } else {
                    $fileImportTask[$key]['project_id'] = array_search($item['du_an'], $arrProject);
                    unset($fileImportTask[$key]['du_an']);
                }
            }
        } else {
            foreach($fileImportTask as $key => $item) {
                $messages[] = 'Hàng ' . ($key + 5) . ' lỗi do tên dự án không tồn tại';
                unset($fileImportTask[$key]);
            }
        }
        
        return $messages;
    }

    public function getArrayWithKeyValue($collection, $key, $value)
    {
        $arrResult = [];
        if (!$collection) return $arrResult;
        foreach ($collection as $item) {
            $arrResult[$item->{$key}] = $item->{$value};
        }
        return $arrResult;
    }
    public function checkNumberNameProj($data)
    {
        $dataResult = [];
        foreach($data as $key => $value) {
            if ($value > 1) $dataResult[] = $key      ;
        }
        return $dataResult;
    }
    
    public function validationFileProjectTask($dataFile)
    {
        $nameProjectFile = [];
        $dataImportTask =[];
        $dataImportTaskAssign = [];
        $dataImportTaskAssignParticipant = [];
        $messageErrors = [];

        $statusTask = Task::getStatusTypeNormal();
        $priorityTask = Task::priorityLabel();
        $typeTask = Task::getTypeIssuesCreator();
        $lableStatusTask = array_values($statusTask);
        $lablePriorityTask = array_values($priorityTask);
        $lableTypeTask = array_values($typeTask);
        $curEmp = Permission::getInstance()->getEmployee();

        foreach($dataFile as $key => $data) {
            if (!$data['du_an'] ||
                !$data['tieu_de'] ||
                !$data['deadline'] ||
                !$data['ngay_tao'] ||
                !$data['noi_dung'] ||
                !$data['nguoi_duoc_assign']) {
                $messageErrors[] = 'Hàng ' . ($key + 5) . ' lỗi: tên dự án, tiêu đề, deadline, ngày tạo, nội dung, người được asign không được để trống';
                continue;
            }
            if (!$data['trang_thai'] || !$data['loai'] || !$data['do_uu_tien']) {
                $messageErrors[] = 'Hàng ' . ($key + 5) . ' lỗi: trạng thái, loại, hoặc độ ưu tiên không được để trống';
                continue;
            }
            if (!in_array($data['trang_thai'], $lableStatusTask) ||
                !in_array($data['loai'], $lableTypeTask) ||
                !in_array($data['do_uu_tien'], $lablePriorityTask)) {
                $messageErrors[] = 'Hàng ' . ($key + 5) . ' lỗi không tồn tại';
                continue;
            }
            $deadline = $this->checkDateFileProectTask($data['deadline']);
            $ngayTao = $this->checkDateFileProectTask($data['ngay_tao']);
            if (!$deadline || !$ngayTao) {
                $messageErrors[] = 'Hàng ' . ($key + 5) . ' lỗi: không đúng định dạng YYYY-MM-DD';
                continue;
            }
            if (strtotime($data['deadline']) < strtotime($data['ngay_tao'])) {
                $messageErrors[] = 'Hàng ' . ($key + 5) . ' lỗi: ngày deadline phải lớn hơn ngày tạo';
                continue;
            }
            
            $nameProjectFile[$key] = trim($data['du_an']);
            $dataImportTask[$key] = [
                'title' => trim($data['tieu_de']),
                'project_id' => '',
                'status' => array_search($data['trang_thai'], $statusTask),
                'duedate' => $deadline,
                'type' => array_search($data['loai'], $lableTypeTask),
                'noi_dung' => trim(strip_tags($data['noi_dung'])),
                'actual_date' => $ngayTao,
                'priority' => array_search($data['do_uu_tien'], $lablePriorityTask),
                'solution' => isset($data['solution']) ? trim(strip_tags($data['solution'])): '',
                'created_by' => $curEmp->id,
                'du_an' =>  trim($data['du_an']),
            ];
            
            $dataImportTaskAssign[$key] = $data['nguoi_duoc_assign'] . '@rikkeisoft.com';
            if (!empty($data['nguoi_tham_gia'])) {
                $dataImportTaskAssignParticipant[$key] = $data['nguoi_tham_gia'] .'@rikkeisoft.com';
            }
        }
        
        return [
            'messageErrors' => $messageErrors,
            'fileImportTask' => $dataImportTask,
            'fileImportTaskAssign' => $dataImportTaskAssign,
            'fileImportTaskAssignParticipant' => $dataImportTaskAssignParticipant,
            'file_name_project' => $nameProjectFile,
        ];
    }

    public function checkDateFileProectTask($date)
    {
        if(!$date instanceof Carbon) {
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
                return false;    
            }
            return Carbon::createFromFormat('Y-m-d', $date);
        }
        return $date;
    }

    public function checkHeadingFileProjectTask($headings)
    {
        $headingsRequired = [
            'du_an',
            'tieu_de',
            'trang_thai',
            'deadline',
            'loai',
            'noi_dung',
            'nguoi_tham_gia',
            'nguoi_duoc_assign',
            'ngay_tao',
            'do_uu_tien',
            'solution',
            'ngay_tao',
        ];
        foreach($headingsRequired as $heading) {
            if(!in_array($heading, $headings)) {
                return false;
            }
        }
        return true;
    }
    // ========== end project task ==========
}
