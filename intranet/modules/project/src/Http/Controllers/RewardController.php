<?php

namespace Rikkei\Project\Http\Controllers;

use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Model\Task;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;
use Rikkei\Project\Model\TaskHistory;
use Illuminate\Support\Facades\Session;
use Rikkei\Core\View\View;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\TaskAssign;
use Exception;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Project\Model\TaskComment;
use Rikkei\Project\Model\Project;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Project\Model\ProjRewardEmployee;
use Rikkei\Project\Model\ProjRewardMeta;
use Rikkei\Project\Model\ProjectPoint;
use Carbon\Carbon;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\MeEvaluation;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Project\Model\ProjRewardBudget;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\ProjReward;
use Rikkei\Project\View\ProjDbHelp;
use Rikkei\Project\Model\TaskReward;
use Rikkei\Core\View\Form as FormView;
use Illuminate\Support\Facades\Validator;

class RewardController extends Controller
{
    /*
     * type submit of reward
     */
    const TYPE_SUBMIT_SAVE = 0;
    const TYPE_SUBMIT_SUBMIT = 1;
    const TYPE_SUBMIT_FEEDBACK = 2;
    
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('project', 'project/dashboard');
    }
    
    public function index($id, $taskId = null)
    {
        $project = Project::find($id);

        if($taskId) {
            $task = Task::find($taskId);
        }else {
            $task = Project::rewardAvai($project);
        }
        if (!$task) {
            return redirect()->route('project::dashboard')->withErrors(Lang::get(
                'project::message.Not found item.'
            ));
        }
        $permission = Permission::getInstance();
        $userCurrent = $permission->getEmployee();
        $taskAssign = TaskAssign::getAssignReward($task);
        if (!in_array($userCurrent->id, $taskAssign['employee']) &&
            !ProjReward::isAccessViewReward($project)) {
            View::viewErrorPermission();
        }
        $rewardMembers = ProjRewardEmployee::getRewardEmployess($task);
        $totalRewardEmp = ProjRewardEmployee::getTotalRewardEmployees($id);
        $rewardMeta = ProjRewardMeta::getRewardMeta($task);
        if (!$rewardMeta) {
            return redirect()->route('project::dashboard')->withErrors(Lang::get(
                'project::message.Not found item.'
            ));
        }
        $rewardMetaInfor = GeneralProject::projectRewardInfo($rewardMeta);
        $typesMember = ProjectMember::getTypeMember();
        $typesMember[ProjectMember::TYPE_REWARD] = 'Add';
        if ($project->isLong()) {
            $tasks = Task::getAllTaskofLong($project);
            $start_time = Carbon::parse($rewardMeta->month_reward)->addMonths(-Project::CYCLE_REWARD);
            if($rewardMeta->month_reward == $project->end_at) {
                $end_time = Carbon::parse($rewardMeta->month_reward);
                $diffMonth = ($end_time ->diffInMonths(Carbon::parse($project->start_at))) % Project::CYCLE_REWARD + Project::CYCLE_REWARD;
                $start_time = Carbon::parse($rewardMeta->month_reward)->addMonths(-$diffMonth);
            }else {
                $end_time = Carbon::parse($rewardMeta->month_reward)->addMonths(-1);
            }

            $periodExec = GeneralProject::getPeriodMonthTime(
                $start_time, 
                $end_time
            );
        }else {
            $tasks = null;
            $periodExec = GeneralProject::getPeriodMonthTime(
                Carbon::parse($project->start_at), 
                Carbon::parse($project->end_at)
            );
        }
        
        $meMembers = MeEvaluation::getMEEmployeesOfProject($project);
        $changeStateBM = false; // allow change state of bonus money;
        if (Permission::getInstance()->isAllow('project::reward.update.bonusMoney')
            || $userCurrent->id == $project->leader_id) {
            $changeStateBM = true;
        }

        Breadcrumb::add('Project Dashboard', URL::route('project::dashboard'));
        Breadcrumb::add('Project Reward');

        return view('project::task.edit_reward', [
            'project' => $project,
            'taskItem' => $task,
            'tasks' =>$tasks,
            'taskId' => $taskId,
            'taskCommentList' => TaskComment::getGridData($task->id),
            'taskHistoryList' => TaskHistory::getGridData($task->id),
            'taskAssigns' => $taskAssign,
            'allStatusAssign' => TaskAssign::statusLabel(),
            'userCurrent' => $permission->getEmployee(),
            'permission' => $permission,
            'rewardMembers' => $rewardMembers,
            'rewardMeta' => $rewardMeta,
            'rewardMetaInfor' => $rewardMetaInfor,
            'evaluationLabel' => ProjectPoint::evaluationLabel(),
            'periodExec' => $periodExec,
            'typesMember' => $typesMember,
            'meMembers' => $meMembers,
            'changeStateBM' => $changeStateBM,
            'totalRewardEmp' => $totalRewardEmp,
        ]);
    }
    
    /**
     * submit data
     */
    public function submit($id, $taskId = null)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $project = Project::find($id);
        if($taskId) {
            $task = Task::find($taskId);
        }else {
            $task = Project::rewardAvai($project);
        }
        $response = [];
        if (!$task) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if ($task->status == Task::STATUS_APPROVED) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Reward be confirmed by COO');
            return response()->json($response);
        }
        $rewardMeta = ProjRewardMeta::getRewardMeta($task);
        if (!$rewardMeta) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (Input::has('reward.new')) {
            $resultNew = ProjRewardEmployee::createNewEmpReward($task, Input::get('reward.new'));
            if (!$resultNew['status']) {
                return response()->json($resultNew);
            }
            $dataRwSubmit = Input::get('reward.submit');
            $dataRwSubmit = $dataRwSubmit ? ($dataRwSubmit + $resultNew['data']) : $resultNew['data'];
            Input::merge(['reward' => ['submit' => $dataRwSubmit], 'add_new' => 1]);
        }
        $rewardMembers = ProjRewardEmployee::getRewardEmployess($task);
        $rewardMetaInfor = GeneralProject::projectRewardInfo($rewardMeta);
        $taskAssigns = TaskAssign::getAssignReward($task);
        $permission = Permission::getInstance();
        $userCurrent = $permission->getEmployee();
        $subPm = ProjectMember::getSubPmOfProject($id);
        // check type submit
        $typeSubmit = self::TYPE_SUBMIT_SAVE;
        switch (Input::get('save')) {
            case self::TYPE_SUBMIT_SUBMIT:
            case self::TYPE_SUBMIT_FEEDBACK:
                $typeSubmit = (int) Input::get('save');
                break;
            default:
                $typeSubmit = self::TYPE_SUBMIT_SAVE;
        }
        $data = [
            'rewardMetaInfor' => $rewardMetaInfor,
            'taskAssigns' => $taskAssigns,
            'userCurrent' => $userCurrent,
            'rewardMembers' => $rewardMembers,
            'task' => $task,
            'project' => $project,
            'permission' => $permission,
            'type_submit' => $typeSubmit,
            'rewardMeta' => $rewardMeta
        ];
        
        // check task is new
        switch ($task->status) {
            case Task::STATUS_NEW:
            case Task::STATUS_FEEDBACK:
                // check total input
                /*$rewardInput = Input::get('reward.submit');
                $totalRewardInput = $this->totalArray($rewardInput);
                if ($totalRewardInput > $rewardMetaInfor['reward_pm_dev']) {
                    $response['error'] = 1;
                    $response['message'] = Lang::get('project::view.Please fill total reward  littler norm reward');
                    return response()->json($response);
                }
                // check permission: pm
                if ((!isset($taskAssign['role'][TaskAssign::ROLE_PM]['employee_id']) || 
                            $userCurrent->id != $taskAssign['role'][TaskAssign::ROLE_PM]['employee_id']) &&
                        !$permission->isAllow('project::reward.submit')
                ) {
                    $response['error'] = 1;
                    $response['message'] = Lang::get('project::message.You don\'t have access');
                    return response()->json($response);
                } 
                DB::beginTransaction();
                try {
                    foreach ($rewardMembers as $rewardMember) {
                        if (!isset($rewardInput[$rewardMember->id])) {
                            continue;
                        }
                        $rewardMember->reward_submit = $rewardInput[$rewardMember->id];
                        $rewardMember->save();
                    }
                    if (!Input::get('save')) {
                        // change status task and send email
                        $task->status = Task::STATUS_SUBMITTED;
                        $task->save([], $project, [
                            'history' => false
                        ]);
                        TaskHistory::storeHistory($task, $project, true, true, [
                            'text_change_custom' => 'reward_submit'
                        ]);
                        // send mail to leader
                        if (isset($taskAssign['role'][TaskAssign::ROLE_REVIEWER]['employee_id'])) {
                            $subject = Lang::get('project::email.[Project reward] Project '
                                . 'Reward of :project be submitted', [
                                'project' => $project->name
                            ]);
                            $emailQueue = new EmailQueue();
                            $emailQueue->setTo($taskAssign['role'][TaskAssign::ROLE_REVIEWER]['email'], 
                                    $taskAssign['role'][TaskAssign::ROLE_REVIEWER]['name'])
                                ->setSubject($subject)
                                ->setTemplate('project::emails.reward_submitted', [
                                    'dear_name' => $taskAssign['role'][TaskAssign::ROLE_REVIEWER]['name'],
                                    'project_name' => $project->name,
                                    'reward_link' => URL::route('project::reward', ['id' => $project->id])
                                ])
                                ->save();
                        }
                        $message = Lang::get('project::message.Submit project reward success');
                    } else {
                        $message = Lang::get('project::message.Save project reward success');
                        $response['success'] = 1;
                        $response['message'] = $message;
                        DB::commit();
                        return response()->json($response);
                    }
                    Session::flash(
                        'messages', [
                                'success'=> [
                                    $message,
                                ]
                            ]
                    );
                    $response['success'] = 1;
                    $response['refresh'] = URL::route('project::reward', ['id' => $project->id]);
                    $response['popup'] = 1;
                    DB::commit();
                    return $response;
                } catch (Exception $ex) {
                    $response['error'] = 1;
                    $response['message'] = Lang::get('project::message.Error system');
                    Log::info($ex);
                    DB::rollback();
                    return response()->json($response);
                }
                break;*/
                $config = [
                    'input_key' => 'reward.submit',
                    'role_permission' => TaskAssign::ROLE_PM,
                    'task_status_save' => Task::STATUS_SUBMITTED,
                    'route_name_permission' => 'project::reward.submit',
                    'reward_col_db' => 'reward_submit',
                    'role_send_email' => TaskAssign::ROLE_REVIEWER,
                    'role_send_email_cc' => null,
                    'role_send_email_cc_subPm' => null,
                    'mail_subject' => Lang::get('project::email.[Project reward] Project '
                                . 'Reward of :project be submitted', [
                                'project' => $data['project']->name
                            ]),
                    'mail_template' => 'project::emails.reward_submitted',
                    'message_submit' => Lang::get('project::message.Submit project reward success'),
                    'task_history_key' => 'reward_submit',
                    'feedback_cmt' => null
                ];
                return $this->processSubmit($data, $config, $taskId);
            case Task::STATUS_SUBMITTED:
                $config = [
                    'input_key' => 'reward.confirm',
                    'role_permission' => TaskAssign::ROLE_REVIEWER,
                    'task_status_save' => Task::STATUS_REVIEWED,
                    'route_name_permission' => 'project::reward.confirm',
                    'reward_col_db' => 'reward_confirm',
                    'role_send_email' => TaskAssign::ROLE_APPROVER,
                    'role_send_email_cc' => TaskAssign::ROLE_PM,
                    'role_send_email_cc_subPm' => $subPm,
                    'mail_subject' => Lang::get('project::email.[Project reward] Project '
                                . 'Reward of :project be verified', [
                                'project' => $data['project']->name
                            ]),
                    'mail_template' => 'project::emails.reward_confirmed',
                    'message_submit' => Lang::get('project::message.Review project reward success'),
                    'task_history_key' => 'reward_confirm',
                    'feedback_cmt' => null
                ];
                return $this->processSubmit($data, $config, $taskId);
            case Task::STATUS_REVIEWED:
                $config = [
                    'input_key' => 'reward.approve',
                    'role_permission' => TaskAssign::ROLE_APPROVER,
                    'task_status_save' => Task::STATUS_APPROVED,
                    'route_name_permission' => 'project::reward.approve',
                    'reward_col_db' => 'reward_approve',
                    'role_send_email' => TaskAssign::ROLE_PM,
                    'role_send_email_cc' => TaskAssign::ROLE_REVIEWER,
                    'role_send_email_cc_subPm' => $subPm,
                    'mail_subject' => Lang::get('project::email.[Project reward] Project '
                                . 'Reward of :project be confirmed', [
                                'project' => $data['project']->name
                            ]),
                    'mail_template' => 'project::emails.reward_approved',
                    'email_notifiPayReward' => true,
                    'message_submit' => Lang::get('project::message.Approve project reward success'),
                    'task_history_key' => 'reward_approve',
                    'feedback_cmt' => null
                ];
                return $this->processSubmit($data, $config, $taskId);
        }
        $response['error'] = 1;
        $response['message'] = Lang::get('project::message.Not found item.');
        return response()->json($response);
    }

    /*
     * feedback reward
     */
    public function feedback($id, $taskId = null)
    {
        $feedbackReason = Input::get('feedback_reason');
        if (!$feedbackReason) {
            return redirect()
                ->back()
                ->with('messages', ['errors' => [Lang::get('validation.required', ['attribute' => 'Feedback reason'])]]);
        }
        $project = Project::find($id);
        if ($taskId) {
            $task = Task::find($taskId);
        } else {
            $task = Project::rewardAvai($project);
        }
        if (!$task) {
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [Lang::get('project::message.Not found item.')]]);
        }
        if ($task->bonus_money == Task::REWARD_IS_PAID) {
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [Lang::get('project::message.Project reward has paid')]]);
        }
        $currentUser = Permission::getInstance()->getEmployee();
        //get list task assigne
        $taskAssigns = TaskAssign::getAssignReward($task);
        //save data
        DB::beginTransaction();
        try {
            $task->status = Task::STATUS_FEEDBACK;
            $task->save([], $project, [
                'history' => false
            ]);
            //save history
            TaskHistory::storeHistory(
                $task,
                $project,
                ['reward_feedback_reason' => $feedbackReason],
                true,
                ['text_change_custom' => 'reward_feedback_reason']
            );
            //send email
            $pmName = $taskAssigns['role'][TaskAssign::ROLE_PM]['name'];
            $dataEmail = [
                'dear_name' => $pmName,
                'project_name' => $project->name,
                'reward_link' => URL::route('project::reward', ['id' => $project->id])
            ];
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($taskAssigns['role'][TaskAssign::ROLE_PM]['email'], $pmName)
                    ->setSubject(Lang::get('project::email.project_reward_mail_subject_feedback', [
                        'project' => $project->name,
                        'name' => ucfirst(preg_replace('/\s|@.*/', '', $currentUser->email))
                    ]))
                    ->setTemplate('project::emails.reward_feedback', $dataEmail)
                    ->save();
            DB::commit();

            return redirect()
                    ->back()
                    ->with('messages', ['success' => [Lang::get('project::message.Feedback success!')]]);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();

            return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [Lang::get('project::message.Error system')]]);
        }
    }

    /**
     * get total of array
     * 
     * @param array $array
     * @return float
     */
    private function totalArray(array &$array = [])
    {
        $total = 0;
        if (!$array) {
            return $total;
        }
        foreach ($array as $key => $item) {
            $item = preg_replace('/\,|\s/', '', $item);
            if ($item == '') {
                $array[$key] = $item;
            } elseif ($item < 0) {
                return false;
            } else {
                $array[$key] = (float) $item;
                $total += $item;
            }
        }
        return $total;
    }
    
    /**
     * remove format of number
     * 
     * @param array $array
     * @return array
     */
    private function numberFormatToNumber(array $array = [])
    {
        $result = [];
        foreach ($array as $key => $item) {
            $result[$key] = preg_replace('/\,|\s/', '', $item);
        }
        return $result;
    }
    
    /**
     * process submit, confirm, verify
     * 
     * @param array $data
     * @param array $config
     * @return json string
     */
    private function processSubmit(array $data = [], array $config = [], $taskId = null)
    {
        $rewardInput = Input::get($config['input_key']);
        /* not check total dev team, only check total reward with total submit
         * if (isset($data['task'])) {
            $totaInputDevTeam = $this->totalInputDevTeam($rewardInput, $data['task']);
            if ($totaInputDevTeam > $data['rewardMetaInfor']['reward_pm_dev']) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::view.validate total dev team');
                return response()->json($response);
            }
        }*/
        $totalRewardInput = $this->totalArray($rewardInput);
        if ($totalRewardInput === false) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::view.Please fill reward >= 0');
            return response()->json($response);
        }
        if ($totalRewardInput > $data['rewardMetaInfor']['reward_actual']) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::view.Please fill total reward littler actual reward');
            return response()->json($response);
        }
        // check permission: pm
        if ((!isset($data['taskAssigns']['role'][$config['role_permission']]['employee_id']) || 
                    $data['userCurrent']->id != $data['taskAssigns']['role'][$config['role_permission']]['employee_id']) &&
                !$data['permission']->isAllow($config['route_name_permission'])
        ) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        } 
        DB::beginTransaction();
        try {
            foreach ($data['rewardMembers'] as $rewardMember) {
                $rewardMember->{$config['reward_col_db']} = $rewardInput[$rewardMember->id];
                $rewardMember->save();
            }
            $saveAction = Input::get('save');
            // save data
            if (!$saveAction || !in_array($saveAction, [1,2])) {
                $message = Lang::get('project::message.Save project reward success');
                $response['success'] = 1;
                if (Input::get('add_new')) {
                    $response['refresh'] = route('project::reward', ['id' => $data['project']->id, 'taskID' => $taskId]);
                }
                $response['message'] = $message;
                DB::commit();
                return response()->json($response);
            }
            // change status task and send email
            $data['task']->status = $config['task_status_save'];
            if ($config['task_status_save'] == Task::STATUS_APPROVED) {
                $today = date("Y-m-d H:i:s");
                $data['rewardMeta']->approve_date = $today;
                $data['rewardMeta']->save();
                $data['rewardMeta']->save([], $data['project'], [
                    'history' => false
                ]);
            }
            $data['task']->save([], $data['project'], [
                'history' => false
            ]);
            TaskHistory::storeHistory($data['task'], $data['project'], true, true, [
                'text_change_custom' => $config['task_history_key']
            ]);
            if ($config['feedback_cmt']) {
                $taskComment = new TaskComment();
                $taskComment->setData([
                    'task_id' => $data['task']->id,
                    'content' => $config['feedback_cmt'],
                    'type' => TaskComment::TYPE_COMMENT_FEEDBACK
                ])
                ->save();
            }
            // send mail to leader or coo or pm
            if (isset($data['taskAssigns']['role'][$config['role_send_email']]['employee_id'])) {
                $emailQueue = new EmailQueue();
                if ($config['role_send_email_cc'] && isset($data['taskAssigns']['role'][$config['role_send_email_cc']]['employee_id'])) {
                    $emailQueue->addCc($data['taskAssigns']['role'][$config['role_send_email_cc']]['email']);
                    $emailQueue->addCcNotify($data['taskAssigns']['role'][$config['role_send_email_cc']]['employee_id']);
                }
                if ($config['role_send_email_cc_subPm']) {
                    foreach ($config['role_send_email_cc_subPm'] as $subPm) {
                        $emailQueue->addCc($subPm->email);
                        $emailQueue->addCcNotify($subPm->id);
                    }
                }
                $dataMail = [
                    'dear_name' => $data['taskAssigns']['role'][$config['role_send_email']]['name'],
                    'project_name' => $data['project']->name,
                    'reward_link' => URL::route('project::reward', ['id' => $data['project']->id])
                ];
                $emailLeadWatcher = CoreConfigData::getValueDb('project.email_lead_watcher');
                $emailQueue->addCc($emailLeadWatcher)
                        ->addCcNotify(Employee::getIdByEmail($emailLeadWatcher));
                $emailQueue->setTo($data['taskAssigns']['role'][$config['role_send_email']]['email'],
                        $data['taskAssigns']['role'][$config['role_send_email']]['name'])
                    ->setSubject($config['mail_subject'])
                    ->setTemplate($config['mail_template'], $dataMail)
                    ->setNotify(
                        $data['taskAssigns']['role'][$config['role_send_email']]['employee_id'],
                        null,
                        URL::route('project::reward', ['id' => $data['project']->id]), [
                            'category_id' => RkNotify::CATEGORY_PROJECT,
                            'content_detail' => RkNotify::renderSections($config['mail_template'], $dataMail)
                        ])->save();
            }
            // sent email to leader to submit pay reward's state
            if (isset($config['email_notifiPayReward'])
                    && isset($data['taskAssigns']['role'][TaskAssign::ROLE_REVIEWER]['email'])
            ) {
                $dataMail2 = [
                    'dear_name' => $data['taskAssigns']['role'][TaskAssign::ROLE_REVIEWER]['name'],
                    'project_name' => $data['project']->name,
                    'reward_link' => URL::route('project::reward', ['id' => $data['project']->id])
                ];
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($data['taskAssigns']['role'][TaskAssign::ROLE_REVIEWER]['email'],
                        $data['taskAssigns']['role'][TaskAssign::ROLE_REVIEWER]['name'])
                    ->setSubject('Pay reward')
                    ->setTemplate('project::emails.reward_notifiPayReward', $dataMail2)
                    ->setNotify(
                        $data['taskAssigns']['role'][TaskAssign::ROLE_REVIEWER]['employee_id'],
                        'Pay reward project ' . $data['project']->name,
                        URL::route('project::reward', ['id' => $data['project']->id]), [
                            'category_id' => RkNotify::CATEGORY_PROJECT,
                            'content_detail' => RkNotify::renderSections('project::emails.reward_notifiPayReward', $dataMail2)
                        ])->save();
            }
            $message = $config['message_submit'];
            Session::flash(
                'messages', [
                        'success'=> [
                            $message,
                        ]
                    ]
            );
            $response['success'] = 1;
            $response['refresh'] = URL::route('project::reward', ['id' => $data['project']->id, 'taskID' => $taskId]);
            $response['popup'] = 1;
            DB::commit();
            return $response;
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            Log::info($ex);
            DB::rollback();
            return response()->json($response);
        }
    }
    
    /**
     * save or public reward
     * 
     * @param int $id
     * @param int $save
     */
    public function budgetSave($id, $save = null, $month_reward = null)
    {
        $project = Project::find($id);
        $currentUser = Permission::getInstance()->getEmployee();
        $isCoo = Permission::getInstance()->isCOOAccount();
        $isLeader = false;
        if ($currentUser->id == $project->leader_id) {
            $isLeader = true;
        }
        $response = [];
        if(!$project || $project->status != Project::STATUS_APPROVED) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item');
            return response()->json($response);
        }
        if (!$project->isOpen() || !($isLeader 
                || $isCoo 
                || Permission::getInstance()->isAllow('project::reward.budget.update'))
        ) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        $rewardBudget = Input::get('reward_budget');
        if (!$rewardBudget) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Please fill full data');
            return response()->json($response);
        }
        $rewardBudget = $this->numberFormatToNumber($rewardBudget);
        foreach ($rewardBudget as $level => $reward) {
            if ($reward < 0) {
                $response['error'] = 1;
                $response['message'] = Lang::get('project::view.Please fill reward >= 0');
                return response()->json($response);
            }
            // validate reward smaller
            if (isset($rewardBudget[$level-1]) && 
                $rewardBudget[$level-1] > $reward
            ) {
                $response['error'] = 1;
                $response['message'] = Lang::get("project::message.Please fill "
                    . "correct reward, level bigger's reward must greater level smaller's reward");
                return response()->json($response);
            }
        }
        DB::beginTransaction();
        try {
            ProjRewardBudget::insertRewardBudgets($project, $rewardBudget);
            if (!$save) {
                $projectMeta = $project->getProjectMeta();
                $isShow = $projectMeta->is_show_reward_budget;
                if ($isShow == ProjectMeta::REWARD_BUGGET_SHOW) { // to status private
                    $projectMeta->is_show_reward_budget = ProjectMeta::REWARD_BUGGET_HIDE;
                    $response['message'] = 
                        Lang::get('project::message.Private reward budget success');
                    $response['htmlDom']['.btn-reward-public-text'] = 
                        Lang::get('project::view.Approve');
                    $response['htmlDom']['.budget-status-callout .callout strong'] = 
                        Lang::get('project::view.Private');
                    $response['addClassDom']['.budget-status-callout .callout'] = 
                        'callout-warning';
                    $response['removeClassDom']['.budget-status-callout .callout'] = 
                        'callout-success';
                } elseif ($isShow == ProjectMeta::REWARD_BUGGET_HIDE || $isShow == ProjectMeta::REWARD_BUGGET_REVIEWED) { // to status approved
                    $projectMeta->is_show_reward_budget = ProjectMeta::REWARD_BUGGET_SHOW;
                    $response['message'] = Lang::get('project::message.Public reward budget success');
                    $response['htmlDom']['.btn-reward-public-text'] = Lang::get('project::view.Private');
                    $response['htmlDom']['.budget-status-callout .callout strong'] = 
                        Lang::get('project::view.Approved');
                    $response['addClassDom']['.budget-status-callout .callout'] = 
                        'callout-success';
                    $response['removeClassDom']['.budget-status-callout .callout'] = 
                        'callout-warning';
                } else {
                    $projectMeta->is_show_reward_budget = ProjectMeta::REWARD_BUGGET_REVIEWED;
                    $response['message'] = 
                        Lang::get('project::message.Reviewed reward budget success');
                    $response['refresh'] = URL::route('project::point.edit', ['id' => $project->id]);
                    //send email to Coo to approved
                    $this->sendMailWhenReviewedBudget($project, true);
                }
                $projectMeta->save();
            } else {
                $response['message'] = Lang::get('project::message.Update reward budget success');
            }
            $response['success'] = 1;
            if (!$save) {
                $response['popup'] = 1;
                $response['reload'] = true;
            }
            DB::commit();
            return response()->json($response);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            return response()->json($response);
        }
    }
    
    /**
     * 
     * @param type $project
     */
    private function sendMailWhenApproveBudget($project)
    {
        $pm = $project->getPMActive();
        if (!$pm) {
            return true;
        }
        $emaiQueue = new EmailQueue();
        $leader = $project->groupLeader;
        if ($leader) {
            $emaiQueue->addCc($leader->email, $leader->name);
            $emaiQueue->addCcNotify($leader->id);
        }
        $watcherEmail = CoreConfigData::getValueDb('project.email_lead_watcher');
        if ($watcherEmail) {
            $watcher = Employee::getNameByEmail($watcherEmail);
            if ($watcher) {
                $emaiQueue->addCc($watcherEmail, $watcher->name);
                $emaiQueue->addCcNotify($watcher->id);
            }
        }
        $cooEmail = CoreConfigData::getValueDb('project.account_approver_reward');
        if ($cooEmail) {
            $coo = Employee::getNameByEmail($cooEmail);
            if ($coo) {
                $emaiQueue->addCc($cooEmail, $coo->name);
                $emaiQueue->addCcNotify($coo->id);
            }
        }
        $rewardLink = URL::route('project::point.edit', ['id' => $project->id]) . '#reward';
        $emaiQueue->setTo($pm->email, $pm->name)
            ->setSubject('[Reward budget] Project Reward Budget of ' . 
                $project->name . ' approved, please view it.')
            ->setTemplate('project::emails.reward_budget_approved', [
                'dear_name' => $pm->name,
                'project_name' => $project->name,
                'project_pm' => $pm->name,
                'project_group' => $project->getTeamsString(),
                'reward_link' => $rewardLink
            ])
            ->setNotify($pm->id, null, $rewardLink, ['category_id' => RkNotify::CATEGORY_PROJECT])
            ->save();
    }
    
    /**
     * sent email to Coo when project reward budget reviewed
     * @param object $project
     */
    private function sendMailWhenReviewedBudget($project)
    {
        $emaiQueue = new EmailQueue();
        $cooEmail = CoreConfigData::getValueDb('project.account_approver_reward');
        if ($cooEmail) {
            $coo = Employee::getNameByEmail($cooEmail);
            if ($coo) {
                $pm = $project->getPmActive();
                if ($pm) {
                    $pmName = $pm->name . ' (' . $pm->email . ')';
                }
                $rewardLink = URL::route('project::point.edit', ['id' => $project->id]) . '#reward';
                $emaiQueue->setTo($cooEmail, $coo->name)
                    ->setSubject(trans('project::email.Subject email approve report reward for first approve project', ['name' => $project->name]))
                    ->setTemplate('project::emails.reward_notifiApproveBudget', [
                        'dear_name' => $coo->name,
                        'project_name' => $project->name,
                        'project_pm' => $pmName,
                        'project_group' => $project->getTeamsString(),
                        'reward_link' => $rewardLink
                    ])
                    ->setNotify($coo->id, null, $rewardLink, ['category_id' => RkNotify::CATEGORY_PROJECT])
                    ->save();
            }
        }
    }
    /**
     * list reward actual
     */
    public function listActual()
    {
        Breadcrumb::add('Report', URL::route('project::report.reward.list'));
        Breadcrumb::add('Reward', URL::route('project::report.reward.list'));
        Menu::setActive('Project', 'project');
        if (!Permission::getInstance()->isScopeTeam(null, 'project::reward') &&
            !Permission::getInstance()->isScopeCompany(null, 'project::reward')
        ) {
            View::viewErrorPermission();
        }
        $lastMonthApprove = ProjDbHelp::getDateDefaultRewardFilter()->format('m-Y');
        try {
            $collectionModel = ProjReward::getGridDataReward();
        } catch (Exception $ex) {
            FormView::forgetFilter();
            $collectionModel = ProjReward::getGridDataReward();
        }
        $taskStatusAll = ProjReward::getStatusAvai();
        if (request()->ajax() || request()->wantsJson()) {
            $html = '';
            if (!$collectionModel->isEmpty()) {
                $i = Input::get('index');
                $i = $i ? $i : 1;
                foreach ($collectionModel as $item) {
                    $html .= view('project::reward.report-item', [
                        'item' => $item,
                        'i' => $i,
                        'monthFilter' => Input::get('monthFilter'),
                        'taskStatusAll' => $taskStatusAll
                    ])->render();
                    $i++;
                }
            }
            return [
                'htmlContent' => $html,
                'nextPageUrl' => $collectionModel->nextPageUrl()
            ];
        }
        return view('project::reward.report', [
            'collectionModel' => $collectionModel,
            'titlePage' => Lang::get('project::view.Report reward'),
            'taskStatusAll' => $taskStatusAll,
            'projectTypeAll' => [
                ProjReward::TYPE_TASK => trans('project::view.Base'),
                ProjReward::TYPE_ME => trans('project::view.OSDC'),
            ],
            'lastMonthApprove' => $lastMonthApprove,
            'listPaidStatus' => ProjReward::getPaidStatus(),
        ]);
    }
    
    /**
     * export data
     */
    public function exportApproveData () {
        return ProjReward::exportData();
    }
        
    /**
     * update state bonus_money in table 
     */
    public function updateBonusMoney ($id){
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $project = Project::find($id);
        $task = Project::rewardAvai($project);
        
        if (!$task) {
            return redirect()->route('project::dashboard')->withErrors(Lang::get(
                'project::message.Not found item.'
            ));
        }
        $taskAssign = TaskAssign::getAssignReward($task);
        $task->bonus_money = Input::get('bonus_money');
        if ($task->save()) {
            if ($task->bonus_money && isset($taskAssign['role'][TaskAssign::ROLE_PM]['email'])) {
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($taskAssign['role'][TaskAssign::ROLE_PM]['email'], 
                        $taskAssign['role'][TaskAssign::ROLE_PM]['name'])
                    ->setSubject('Paid reward')
                    ->setTemplate('project::emails.reward_paidBonusMoney', [
                        'dear_name' => $taskAssign['role'][TaskAssign::ROLE_PM]['name'],
                        'project_name' => $project->name,
                        'reward_link' => URL::route('project::reward', ['id' => $project->id])
                    ])
                    ->setNotify(
                        $taskAssign['role'][TaskAssign::ROLE_PM]['employee_id'],
                        'Paid reward on project "' . $project->name . '"',
                        URL::route('project::reward', ['id' => $project->id, 'category_id' => RkNotify::CATEGORY_PROJECT])
                    )
                    ->save();
            }
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Bonus money is updated!');
            $response['popup'] = 1;
            return response()->json($response);
        }
        $response['error'] = 1;
        $response['message'] = Lang::get('project::message.Error system');
        $response['popup'] = 1;
        return response()->json($response);
    }
    
    /**
     * get total of Dev team
     * 
     * @param array $array
     * @return float
     */
    private function totalInputDevTeam(array &$array = [], $task)
    {
        $devTeam = ProjRewardEmployee::getIdIsTypeDev($task);
        $total = 0;
        if (!$array) {
            return $total;
        }
        foreach ($array as $key => $item) {
            if (in_array($key, $devTeam)) {
                $item = preg_replace('/\,|\s/', '', $item);
                if ($item == '') {
                    $array[$key] = $item;
                } elseif ($item < 0) {
                    return false;
                } else {
                    $array[$key] = (float) $item;
                    $total += $item;
                }
            }
        }
        return $total;
    }
    
    /**
     * export reward of project
     * @param int $ids project id
     * @return json
     */
    public function exportReward () {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $ids = Input::get('id');
        if (!$ids) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            $response['popup'] = 1;
            return response()->json($response);
        }
        if ($ids === null) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            $response['popup'] = 1;
            return response()->json($response);
        }
        $ids = explode('-', $ids);
        if (!count($ids)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            $response['popup'] = 1;
            return response()->json($response);
        }
        $rewards = [];
        $permission = Permission::getInstance();
        $userCurrent = $permission->getEmployee();
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
            $task = Project::rewardAvai($project);
            if (!$task) {
                continue;
            }
            if($task->status != Task::STATUS_APPROVED) {
                continue;
            }
            //check permission
            $taskAssign = TaskAssign::getAssignReward($task);
            if (!in_array($userCurrent->id, $taskAssign['employee']) &&
                !ProjReward::isAccessViewReward($project)) {
                continue;
            }
            $data = $this->getDataReward($project, $task, $taskAssign);
            $rewards[$projectId] = $data;
        }
        return response()->json($rewards);
    }

    /**
     * get data reward of project
     * @param object $project
     * @return array
     */
    public function getDataReward($project, $task, $taskAssign) {
        $rewardMembers = ProjRewardEmployee::getRewardEmployess($task);
        $rewardMeta = ProjRewardMeta::getRewardMeta($task);
        if (!$rewardMeta) {
            return redirect()->route('project::dashboard')->withErrors(Lang::get(
                                    'project::message.Not found item.'
            ));
        }
        $rewardMetaInfor = GeneralProject::projectRewardInfo($rewardMeta);

        $periodExec = GeneralProject::getPeriodMonthTime(
                        Carbon::parse($project->start_at), Carbon::parse($project->end_at)
        );
        $meMembers = MeEvaluation::getMEEmployeesOfProject($project);
        $evaluationLabel = ProjectPoint::evaluationLabel();
        $data = [];
        $data['project_name'] = $project->name;
        $data['project_group'] = $project->getTeamsString();
        if (isset($taskAssign['role'][TaskAssign::ROLE_PM])) {
            $data['project_pm_name'] = $taskAssign['role'][TaskAssign::ROLE_PM]['name'];
            $data['project_pm_email'] = $taskAssign['role'][TaskAssign::ROLE_PM]['email'];
        }
        if (isset($taskAssign['role'][TaskAssign::ROLE_REVIEWER])) {
            $data['project_leader_name'] = $taskAssign['role'][TaskAssign::ROLE_REVIEWER]['name'];
            $data['project_leader_email'] = $taskAssign['role'][TaskAssign::ROLE_REVIEWER]['email'];
        }
        if (isset($evaluationLabel[$rewardMeta->evaluation])) {
            $data['level'] = $evaluationLabel[$rewardMeta->evaluation];
        }
        $data['billable'] = $rewardMeta->billable;
        $data['reward_budget'] = $rewardMeta->reward_budget;
        $data['count_defect'] = $rewardMeta->count_defect;
        $data['count_defect_pqa'] = $rewardMeta->count_defect_pqa;
        $data['count_leakage'] = $rewardMeta->count_leakage;
        $data['reward_actual'] = $rewardMetaInfor['reward_actual'];
        $data['reward_qa'] = $rewardMetaInfor['reward_qa'];
        $data['reward_pqa'] = $rewardMetaInfor['reward_pqa'];
        $data['reward_pm_dev'] = $rewardMetaInfor['reward_pm_dev'];
        $data['total_point'] = 0;
        $data['reward_confirm'] = 0;
        foreach ($rewardMembers as $key => $member) {
            $member_point = round($this->caculatorPoint($periodExec, $member, $meMembers));
            $data['total_point'] += $member_point;
            $data['reward_confirm'] += $member->reward_approve;
            if ($member->type == ProjectMember::TYPE_PQA) {
                $data['PQA'][$key]['point'] = $member_point;
                $data['PQA'][$key]['name'] = $member->name;
                $data['PQA'][$key]['type'] = ProjectMember::getTypeMemberByKey($member->type);
                $data['PQA'][$key]['reward_approve'] = $member->reward_approve;
                $data['last_PQA'] = $key;
            } elseif ($member->type == ProjectMember::TYPE_SQA) {
                $data['SQA'][$key]['point'] = $member_point;
                $data['SQA'][$key]['name'] = $member->name;
                $data['SQA'][$key]['type'] = ProjectMember::getTypeMemberByKey($member->type);
                $data['SQA'][$key]['reward_approve'] = $member->reward_approve;
                $data['last_SQA'] = $key;
            } else {
                $data['Dev'][$key]['point'] = $member_point;
                $data['Dev'][$key]['name'] = $member->name;
                $data['Dev'][$key]['type'] = ProjectMember::getTypeMemberByKey($member->type);
                $data['Dev'][$key]['reward_approve'] = $member->reward_approve;
                $data['last_Dev'] = $key;
            }
        }
        return $data;
    }

    /**
     * caculate point of member
     * @param array $periodExec
     * @param object $member
     * @param array $meMembers
     * @return int
     */
    public function caculatorPoint($periodExec, $member, $meMembers) {
        $effortMemberReward = $member->effort_resource;
        $effortMemberReward = json_decode($effortMemberReward, true);
        $point = 0;
        foreach ($periodExec as $itemPeriodExec) {
            if (isset($meMembers[$member->employee_id][$itemPeriodExec])) {
                $meMembers[$member->employee_id][$itemPeriodExec];
                $meItem = $meMembers[$member->employee_id][$itemPeriodExec];
            } else {
                $meItem = 0;
            }
            if (isset($effortMemberReward[$itemPeriodExec])) {
                $effortItem = $effortMemberReward[$itemPeriodExec];
            } else {
                $effortItem = 0;
            }
            $point += $meItem * $effortItem;
        }
        return $point;
    }

    /*
     * update comment of employee reward actual
     */
    public function rewardComment() {
        if (ProjRewardEmployee::updateComment()) {
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Save data success!');
            return response()->json($response);
        }
        $response['error'] = 1;
        $response['message'] = Lang::get('project::Error system');
        return response()->json($response);
    }
    
    /**
     * get comment of employee reward actual
     * @return json
     */
    public function getComment() {
        return ProjRewardEmployee::getComment();
    }

    /**
     * export me and reward
     * @type ajax
     * @return json
     */
    public function rewardExport() {
        $data =  ProjReward::exportData('project::reward', true);
        $result = [];
        if (!count($data)) {
            return response()->json($result);
        }
        $prevD = reset($data)['team'];
        $prevEmail = reset($data)['emp_email'];
        $reason = '';
        $totalEmpReward = 0;
        $totalTeamReward = 0;
        $iteamTeam = [];
        $iteamEmp = [];
        $iteamRe = [];
        $listKey = array_keys($data);
        $lastKey = end($listKey);
        $count = 0;
        foreach ($data as $key => $value) {
            if ($value['reward_approve']) {
                $value['reward_approve'] = (int) $value['reward_approve']/1000;
            } else {
                $value['reward_approve'] = 0;
            }
            switch ($value['proj_type']) {
                case Project::TYPE_BASE:
                    $type = ProjectMember::getTypeMemberByKey($value['member_type']);
                    $reason = 'Base '.$value['proj_name'].'('.$type.')'.': '.$value['reward_approve'];
                    break;
                case Project::TYPE_OSDC:
                    $reason = 'OSDC '.$value['proj_name'].'(ME: '.$value['me_point'].')'.': '.$value['reward_approve'];
                    break;
                case Project::TYPE_TRAINING:
                    $reason = 'TRAINING '.$value['proj_name'].'(ME: '.$value['me_point'].')'.': '.$value['reward_approve'];
                    break;
                case Project::TYPE_RD:
                    $reason = 'R&D '.$value['proj_name'].'(ME: '.$value['me_point'].')'.': '.$value['reward_approve'];
                    break;
                case Project::TYPE_ONSITE:
                    $reason = 'ONSITE '.$value['proj_name'].'(ME: '.$value['me_point'].')'.': '.$value['reward_approve'];
                    break;
                default:
                    $reason = 'TeamME '.$value['teamme_name'].'(ME: '.$value['me_point'].')'.': '.$value['reward_approve'];
                    break;
            }
            if (!$value['comment']) {
                $value['comment'] = '';
            }
            if ($value['reward_approve']) {
                $totalEmpReward += (int) $value['reward_approve'];
                $totalTeamReward += (int) $value['reward_approve'];
            }
            
            if ($value['team'] != $prevD) {
                $iteamTeam['emp'][] = $iteamEmp;
                $result[] = $iteamTeam;
                $iteamTeam = [];
                $totalTeamReward = (int) $value['reward_approve'];
                $iteamEmp = [];
                $totalEmpReward = (int) $value['reward_approve'];
                $count = 0;
            } else {
                if ($value['emp_email'] != $prevEmail) {
                    $totalEmpReward = (int) $value['reward_approve'];
                    $iteamTeam['emp'][] = $iteamEmp;
                    $iteamEmp = [];
                }
            } 
            $iteamRe['reason'] = $reason;
            $count ++;
            $iteamRe['comment'] = $value['comment'];
            $iteamEmp['emp_name'] = $value['emp_name'];
            $iteamEmp['emp_code'] = $value['emp_code'];
            $iteamEmp['emp_reward'] = $totalEmpReward;
            $iteamEmp['reason'][] = $iteamRe;
            $iteamEmp['emp_line'] = count($iteamEmp['reason']);
            $iteamTeam['team_name'] = $value['team'];
            $iteamTeam['team_reward'] = $totalTeamReward;
            $iteamTeam['team_line'] = $count;

            $prevD = $value['team'];
            $prevEmail = $value['emp_email'];
            if($key == $lastKey) {
                $iteamTeam['emp'][] = $iteamEmp;
                $result[] = $iteamTeam;
            }
        }
        return response()->json(view('project::reward.tableExport', ['result' => $result])->render());
    }
    
    public static function deleteActual()
    {
        $response = [];
        $taskId = Input::get('id');
        if (!$taskId) {
            $response['success'] = 0;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $task = Task::find($taskId);
        if (!$task) {
            $response['success'] = 0;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (!TaskReward::isDeleteAvai($task, true)) {
            $response['success'] = 0;
            $response['message'] = Lang::get('project::message.You don\'t permission');
            return response()->json($response);
        }
        try {
            $projectId = $task->project_id;
            TaskReward::deleteActual($task);
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('project::message.Delete reward success'),
                        ]
                    ]
            );
            $response['success'] = 1;
            $response['refresh'] = URL::route('project::project.edit', ['id' => $projectId]);
            $response['popup'] = 1;
            return response()->json($response);
        } catch (Exception $ex) {
            Log::info($ex);
            $response['success'] = 0;
            $response['message'] = Lang::get('project::message.Error system');
            return response()->json($response);
        }
    }

    /**
     * edit number reward actual
     */
    public function editNumber()
    {
        $response = [];
        if (!Permission::getInstance()->isAllow('project::reward.base.actual.edit')) {
            $response['status'] = 0;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        $data = (array)Input::get('i');
        foreach ($data as $key => $value) {
            $data[$key] = preg_replace('/\,/i', '', $value);
        }
        $validator = Validator::make($data, [
            '*' => 'required|numeric|min:0'
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->first();
            return $response;
        }
        $task = Task::find(Input::get('id'));
        if (!$task) {
            $response['status'] = 0;
            $response['message'] = Lang::get('project::message.Not found item.');
            return $response;
        }
        $meta = ProjRewardMeta::getRewardMeta($task);
        if (!$meta) {
            $response['status'] = 0;
            $response['message'] = Lang::get('project::message.Not found item.');
            return $response;
        }
        $text = $this->compareEditNumber($meta, $data);
        $meta->fill($data);
        DB::beginTransaction();
        try {
            $meta->save();
            if ($text) {
                TaskHistory::storeHistory($task, null, [
                    'reward_edit_number' => $text,
                ], null, [
                    'text_change_custom' => 'reward_edit_number'
                ]);
            }
            DB::commit();
        } catch (Exception $ex) {
            Log::error($ex);
            $response['status'] = 0;
            $response['message'] = Lang::get('project::message.Error system');
            DB::rollback();
            return $response;
        }
        $response['status'] = 1;
        Session::flash(
            'messages', [
                    'success'=> [
                        Lang::get('project::message.Update reward actual number successfully'),
                    ]
                ]
        );
        $response['reload'] = 1;
        return $response;
    }

    /**
     * render text diff edit number
     *
     * @param object $meta
     * @param array $new
     * @return string
     */
    private function compareEditNumber($meta, $new)
    {
        $labels = [
            'reward_budget' => 'Budget reward',
            'count_defect' => 'Number of IT/ST defects',
            'count_defect_pqa' => 'Defect of final inspection',
            'count_leakage' => 'Number of leakage',
        ];
        $text = '';
        foreach ($labels as $key => $label) {
            if (!isset($new[$key]) || $new[$key] == $meta->{$key}) {
                continue;
            }
            $text .= sprintf('%s: %s -> %s; ',
                $label,
                is_numeric($meta->{$key}) ? number_format($meta->{$key}) : $meta->{$key},
                is_numeric($new[$key]) ? number_format($new[$key]) : $new[$key]
            );
        }
        return $text;
    }

    /**
     * delete reward employee
     */
    public function deleteEmployee()
    {
        if (!Permission::getInstance()->isAllow('project::reward.submit')) {
            return View::viewErrorPermission();
        }
        $id = Input::get('id');
        if (!$id || !($item = ProjRewardEmployee::find($id))) {
            return response()->json(trans('project::message.Not found item.'), 404);
        }
        if ($item->type != ProjectMember::TYPE_REWARD) {
            return response()->json(trans('core::message.You don\'t have access'), 403);
        }
        $item->delete();
        return response()->json(trans('project::message.Delete item success'));
    }
}
