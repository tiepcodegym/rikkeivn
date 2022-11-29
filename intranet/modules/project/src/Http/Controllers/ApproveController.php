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
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\TaskAssign;
use Exception;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\TaskComment;
use Illuminate\Support\Facades\Log;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Project;
use Rikkei\Sales\Model\Company;

class ApproveController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('project', 'project/dashboard');
    }
    
    /**
     * get data from process review and approve
     */
    protected function processReviewData($taskId)
    {
        $task = Task::find($taskId);
        $response = [];
        $response['error'] = null;
        $response['message'] = null;
        if (!$task) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
        }
        $project = $task->getProject();
        if (!$project) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        if (!$task->isTaskApproved()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        return [
            $task,
            $project,
            $response['error'],
            $response['message']
        ];
    }


    /**
     * review submit
     */
    public function reviewSubmit($id, $myTask = false)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        list ($task, $project, $error, $message) = $this->processReviewData($id);
        if ($error) {
            $response['error'] = $error;
            $response['message'] = $message;
            return response()->json($response);
        }
        if ($task->status != Task::STATUS_SUBMITTED) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Workorder isnot submitting');
            return response()->json($response);
        }
        $userCurrent = Permission::getInstance()->getEmployee();
        $reviewer = TaskAssign::findAssigneeById($id, $userCurrent->id);
        $allStatus = TaskAssign::statusLabel();
        if (!$reviewer) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        if ($reviewer->status != TaskAssign::STATUS_NO) {
            $status = View::getLabelOfOptions($reviewer->status, $allStatus);
            $response['error'] = 1;
            $response['message'] = $status . ', ' . Lang::get('project::message.You canot accept review');
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $reviewer->status = TaskAssign::STATUS_REVIEWED;
            $reviewer->save();
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Reviewed success!');
            $arrayFlashMessage = [
                'success'=> [
                    Lang::get('project::message.Reviewed success!'),
                ]
            ];
            if ($myTask) {
                $response['refresh'] = URL::route('project::task.my.task');
            } else {
                $response['refresh'] = URL::route('project::task.edit', ['id' => $task->id]);
            }
            $response['popup'] = 1;
            if (TaskAssign::isAllreviewed($id)) {
                $task->status = Task::STATUS_REVIEWED;
                $task->save([], $project, [
                    'history' => false
                ]);
                TaskAssign::sendMailApprover($task, ['project' => $project]);
                $arrayFlashMessage['success'][] = 
                    Lang::get('project::message.Workorder change status to reviewed');
            }
            TaskHistory::storeHistory($task, $project, true, true, [
                'text_change_custom' => 'wo_submit_reivewed'
            ]);
            Session::flash('messages', $arrayFlashMessage);
            DB::commit();
            return response()->json($response);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            DB::rollback();
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * review feedback
     */
    public function reviewFeedback($id, $myTask = false)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        list ($task, $project, $error, $message) = $this->processReviewData($id);
        if ($error) {
            $response['error'] = $error;
            $response['message'] = $message;
            return response()->json($response);
        }
        if ($task->status != Task::STATUS_SUBMITTED) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Workorder isnot submitting');
            return response()->json($response);
        }
        $userCurrent = Permission::getInstance()->getEmployee();
        $reviewer = TaskAssign::findAssigneeById($id, $userCurrent->id);
        $allStatus = TaskAssign::statusLabel();
        if (!$reviewer) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t permission');
            return response()->json($response);
        }
        if ($reviewer->status != TaskAssign::STATUS_NO) {
            $status = View::getLabelOfOptions($reviewer->status, $allStatus);
            $response['error'] = 1;
            $response['message'] = $status . ', ' . Lang::get('project::message.You canot feedback');
            return response()->json($response);
        }
        $feedbackComment = trim(Input::get('fb.comment'));
        if (!$feedbackComment) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Please input comment to feedback');
            return response()->json($response);
        }
        $taskComment = new TaskComment();
        $taskComment->setData([
            'task_id' => $task->id,
            'content' => $feedbackComment,
            'created_by' => $userCurrent->id,
            'type' => TaskComment::TYPE_COMMENT_FEEDBACK
        ]);
        DB::beginTransaction();
        try {
            $reviewer->status = TaskAssign::STATUS_FEEDBACK;
            $reviewer->save();
            $taskComment->save();
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Feedback success!');
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('project::message.Feedback success!'),
                        ]
                    ]
            );
            if ($myTask) {
                $response['refresh'] = URL::route('project::task.my.task');
            } else {
                $response['refresh'] = URL::route('project::task.edit', ['id' => $task->id]);
            }
            $response['popup'] = 1;
            $task->status = Task::STATUS_FEEDBACK;
            $task->save([], $project, [
                'history' => false
            ]);
            TaskHistory::storeHistory($task, $project, true, true, [
                'text_change_custom' => 'wo_submit_feedback'
            ]);
            // send mail to pm
            TaskAssign::sendMailApprover($task, [
                'project' => $project,
                'employee' => $project->getPMActive(),
                'subject' => 'project::email.[Work order] Please change '
                    . ':titleTask of project :project',
                'template' => 'project::emails.wo_feedback',
                'feedback_content' => $feedbackComment
            ]);
            DB::commit();
            return response()->json($response);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            DB::rollback();
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * approve submit
     */
    public function approveSubmit($id, $myTask = false)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        list ($task, $project, $error, $message) = $this->processReviewData($id);
        if ($error) {
            $response['error'] = $error;
            $response['message'] = $message;
            return response()->json($response);
        }
        if ($task->status != Task::STATUS_REVIEWED) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Workorder isnot reviewed');
            return response()->json($response);
        }
        $userCurrent = Permission::getInstance()->getEmployee();
        $approver = TaskAssign::findAssigneeById($id, $userCurrent->id, TaskAssign::ROLE_APPROVER);
        $accessApprove = Permission::getInstance()->isAllow('project-access::task.approve.save');
        if ((!$approver || $approver->status != TaskAssign::STATUS_NO) && !$accessApprove) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t permission');
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if ($approver) {
                $approver->status = TaskAssign::STATUS_APPROVED;
                $approver->save();
            }
            //Send mail to saler in project
            if ($task->type == Task::TYPE_WO) {
                $isProjectUseToApproved = Task::isProjectUseToApproved($project);
                $saleOfProj = Project::getAllSaleOfProject($project->id);
                $sales = Employee::getEmpByIds($saleOfProj);
                    if (!empty($sales)) {
                        if (!$isProjectUseToApproved) {
                            foreach ($sales as $sale) {
                                    $data = [
                                        'saleName' => $sale->name,
                                        'projectUrl' => route("project::project.edit", ['id' => $project->id]),
                                        'projectName' => $project->name,
                                    ];
                                    $emailQueue = new \Rikkei\Core\Model\EmailQueue();
                                    $emailQueue->setTo($sale->email, $sale->name)
                                        ->setSubject(Lang::get("project::view.[Intranet] A new project has been approved"))
                                        ->setTemplate("project::emails.wo_first_approved", $data)
                                        ->setNotify($sale->id, null, $data['projectUrl'], ['category_id' => RkNotify::CATEGORY_PROJECT])
                                        ->save();
                            }
                        } else {
                            foreach ($sales as $sale) {
                                    TaskAssign::sendMailApprover($task, [
                                        'project' => $project,
                                        'employee' => $sale,
                                        'subject' => 'project::email.[Work order] '
                                        . ':titleTask of project :project approved',
                                        'template' => 'project::emails.wo_approved'
                                    ]);
                            }
                        }
                    }
                //Create task view customer contract
                $projectDraft = Project::where('parent_id', $project->id)
                        ->where('status', '!=', Project::STATUS_APPROVED)->first();

                if (!$isProjectUseToApproved || (
                    $projectDraft &&
                    $project->manager_id &&
                    $projectDraft->manager_id &&
                    $projectDraft->manager_id != $project->manager_id
                )) {
                    $taskDelete = Task::where('project_id', $project->id)
                        ->where('type', Task::TYPE_CONTRACT_CONFIRM)
                        ->where('status', Task::STATUS_NEW)
                        ->lists('id')
                        ->toArray();
                    TaskAssign::whereIn('task_id', $taskDelete)->delete();
                    Task::whereIn('id', $taskDelete)->delete();
                    $taskContract = new Task();
                    $taskContract->title = 'Confirm customer contract in project ' . $project->name;
                    $taskContract->project_id = $project->id;
                    $taskContract->type = Task::TYPE_CONTRACT_CONFIRM;
                    $taskContract->status = Task::STATUS_NEW;
                    $taskContract->save();
                    $managerId = !$isProjectUseToApproved ? $project->manager_id : $projectDraft->manager_id;
                    $taskAssign = new TaskAssign();
                    $taskAssign->task_id = $taskContract->id;
                    $taskAssign->employee_id = $managerId;
                    $taskAssign->status = TaskAssign::STATUS_NO;
                    $taskAssign->role = TaskAssign::ROLE_OWNER;
                    $taskAssign->save();

                    Company::sendMailConfirmContract($managerId, $project);
                }
            }

            if (!empty($projectDraft->kind_id)) {
                $project->kind_id = $projectDraft->kind_id;
            }

            $project->save();
            $task->status = Task::STATUS_APPROVED;
            $task->save([], $project, [
                'history' => false
            ]);
            TaskHistory::storeHistory($task, $project, true, true, [
                'text_change_custom' => 'wo_submit_approved'
            ]);
            // send mail to pm
            TaskAssign::sendMailApprover($task, [
                'project' => $project,
                'employee' => $project->getPMActive(),
                'subject' => 'project::email.[Work order] '
                . ':titleTask of project :project approved',
                'template' => 'project::emails.wo_approved'
            ]);
            DB::commit();
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Approved success!');
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('project::message.Approved success!'),
                        ]
                    ]
            );
            if ($myTask) {
                $response['refresh'] = URL::route('project::task.my.task');
            } else {
                $response['refresh'] = URL::route('project::task.edit', ['id' => $task->id]);
            }
            $response['popup'] = 1;
            return response()->json($response);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            Log::info($ex);
            DB::rollback();
            return response()->json($response);
        }
    }
    
    /**
     * approve feedback
     */
    public function approveFeedback($id, $myTask = false)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        list ($task, $project, $error, $message) = $this->processReviewData($id);
        if ($error) {
            $response['error'] = $error;
            $response['message'] = $message;
            return response()->json($response);
        }
        if ($task->status != Task::STATUS_REVIEWED) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Workorder isnot reviewed');
            return response()->json($response);
        }
        $userCurrent = Permission::getInstance()->getEmployee();
        $approver = TaskAssign::findAssigneeById($id, $userCurrent->id, TaskAssign::ROLE_APPROVER);
        $accessApprove = Permission::getInstance()->isAllow('project-access::task.approve.save');
        if ((!$approver || $approver->status != TaskAssign::STATUS_NO) && !$accessApprove) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t permission');
            return response()->json($response);
        }
        $feedbackComment = trim(Input::get('fb.comment'));
        if (!$feedbackComment) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Please input comment to feedback');
            return response()->json($response);
        }
        $taskComment = new TaskComment();
        $taskComment->setData([
            'task_id' => $task->id,
            'content' => $feedbackComment,
            'created_by' => $userCurrent->id,
            'type' => TaskComment::TYPE_COMMENT_FEEDBACK
        ]);
        DB::beginTransaction();
        try {
            if ($approver) {
                $approver->status = TaskAssign::STATUS_FEEDBACK;
                $approver->save();
            }
            $taskComment->save();
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Feedback success!');
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('project::message.Feedback success!'),
                        ]
                    ]
            );
            if ($myTask) {
                $response['refresh'] = URL::route('project::task.my.task');
            } else {
                $response['refresh'] = URL::route('project::task.edit', ['id' => $task->id]);
            }
            $response['popup'] = 1;
            $task->status = Task::STATUS_FEEDBACK;
            $task->save([], $project, [
                'history' => false
            ]);
            TaskHistory::storeHistory($task, $project, true, true, [
                'text_change_custom' => 'wo_submit_feedback'
            ]);
            // send mail to pm
            TaskAssign::sendMailApprover($task, [
                'project' => $project,
                'employee' => $project->getPMActive(),
                'subject' => 'project::email.[Work order] Please change '
                    . ':titleTask of project :project',
                'template' => 'project::emails.wo_feedback',
                'feedback_content' => $feedbackComment
            ]);
            DB::commit();
            return response()->json($response);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            Log::info($ex);
            DB::rollback();
            return response()->json($response);
        }
    }
    
    /**
     * approve feedback
     */
    public function undoFeedback($id, $myTask)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        list ($task, $project, $error, $message) = $this->processReviewData($id);
        if ($error) {
            $response['error'] = $error;
            $response['message'] = $message;
            return response()->json($response);
        }
        if ($task->status != Task::STATUS_FEEDBACK) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Workorder isnot feedback');
            return response()->json($response);
        }
        $feebacker = TaskAssign::findAssigneeFeedback($id);
        if (!$feebacker) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found user feedback');
            return response()->json($response);
        }
        // 
        // TODO : check PM change wo => not undo feedback
        //
        if (!GeneralProject::isAccessFeedback($feebacker)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t permission');
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            TaskAssign::resetFeedback($id);
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Undo feedback success!');
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('project::message.Undo feedback success!'),
                        ]
                    ]
            );
            if ($myTask) {
                $response['refresh'] = URL::route('project::task.my.task');
            } else {
                $response['refresh'] = URL::route('project::task.edit', ['id' => $task->id]);
            }
            $response['popup'] = 1;
            if ($feebacker->role == TaskAssign::ROLE_REVIEWER) {
                $task->status = Task::STATUS_SUBMITTED;
            } elseif ($feebacker->role == TaskAssign::ROLE_APPROVER) {
                $task->status = Task::STATUS_REVIEWED;
            }
            $task->save([], $project, [
                'history' => false
            ]);
            \Rikkei\Project\Model\ProjectWOBase::updateStatusSubmit($project);
            TaskHistory::storeHistory($task, $project, true, true, [
                'text_change_custom' => 'wo_submit_undo_feedback'
            ]);
            // send mail to pm
            TaskAssign::sendMailApprover($task, [
                'project' => $project,
                'employee' => $project->getPMActive(),
                'subject' => 'project::email.[Project dashboard] Project '
                    . ':project: Workorder undo feedback',
                'template' => 'project::emails.wo_undo_feedback'
            ]);
            DB::commit();
            return response()->json($response);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            Log::info($ex);
            DB::rollback();
            return response()->json($response);
        }
    }
    
    /**
     * change approver
     */
    public function changeApprover($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        list ($task, $project, $error, $message) = $this->processReviewData($id);
        if ($error) {
            $response['error'] = $error;
            $response['message'] = $message;
            return response()->json($response);
        }
        //check access change approver: COO
        if (!Permission::getInstance()->isAllow('project-access::task.approve.chagnge.approver')) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        
        if ($task->status == Task::STATUS_APPROVED) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Workorder approved');
            return response()->json($response);
        }
        $approverChangeId = Input::get('assign.id');
        if (!$approverChangeId || !is_numeric($approverChangeId)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            return response()->json($response);
        }
        $approverChange = Employee::find($approverChangeId);
        if (!$approverChange) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            TaskAssign::changeApproverWO($task, $approverChange, [
                'project' => $project
            ]);
            DB::commit();
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Change approver success');
            $response['data'] = [
                'id' => $approverChangeId,
                'account' => GeneralProject::getNickName($approverChange->email),
                'name' => $approverChange->name
            ];
            return response()->json($response);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            Log::info($ex);
            DB::rollback();
            return response()->json($response);
        }
    }
    
    /**
     * delete reviewer
     */
    public function deleteReviewer($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        list ($task, $project, $error, $message) = $this->processReviewData($id);
        if ($error) {
            $response['error'] = $error;
            $response['message'] = $message;
            return response()->json($response);
        }
        //check access change approver: COO, approver
        $approver = $reviewerDelete = TaskAssign::findAssignee(
            $task->id, 
            null,
            TaskAssign::ROLE_APPROVER, 
            null
        );
        if (
            !Permission::getInstance()->isAllow('project-access::task.approve.chagnge.reviewer') &&
            Permission::getInstance()->getEmployee()->id != $approver->employee_id
        ) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        if ($task->status == Task::STATUS_APPROVED) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Workorder approved');
            return response()->json($response);
        }
        $reviewerId = Input::get('assign.id');
        if (!$reviewerId || !is_numeric($reviewerId)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            return response()->json($response);
        }
        // find reviewer delete
        $reviewer = Employee::find($reviewerId);
        if (!$reviewer) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $reviewerDelete = TaskAssign::findAssignee(
            $task->id, 
            $reviewerId,
            TaskAssign::ROLE_REVIEWER, 
            TaskAssign::STATUS_NO,
            true
        );
        if (!$reviewerDelete) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        //check number reviewer: reviewer not confirm and not reviewed
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
            TaskAssign::STATUS_REVIEWED,
            true
        );
        $feedbackedExists = TaskAssign::findAssignee(
            $task->id, 
            null,
            TaskAssign::ROLE_REVIEWER, 
            [ TaskAssign::STATUS_FEEDBACK, TaskAssign::STATUS_FEEDBACK_NOTUNDO ],
            true
        );
        if ($reviewerNo == 1 && !$revieweredExists && !$feedbackedExists) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Exists at least a reviewer');
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            TaskAssign::deleteAssignee(
                $task->id,
                $reviewerId,
                TaskAssign::ROLE_REVIEWER,
                TaskAssign::STATUS_NO
            );
            TaskHistory::storeHistory($task, $project, 
                [
                    'wo_remove_reviewer' => $reviewer->email
                ], 
                [
                    'wo_remove_reviewer' => $reviewer->email
                ], 
                [
                    'text_change_custom' => 'wo_remove_reviewer'
                ]
            );
            // change wo to reviewed, refresh page
            if ($reviewerNo == 1 && $revieweredExists && !$feedbackedExists) {
                Session::flash(
                    'messages', [
                            'success'=> [
                                Lang::get('project::message.Remove reviewer success'),
                                Lang::get('project::message.Workorder change status to reviewed'),
                            ]
                        ]
                );
                $response['refresh'] = URL::route('project::task.edit', ['id' => $task->id]);
                $response['popup'] = 1;
                if (TaskAssign::isAllreviewed($id)) {
                    $task->status = Task::STATUS_REVIEWED;
                    $task->save([], $project, [
                        'history' => false
                    ]);
                    TaskAssign::sendMailApprover($task, ['project' => $project]);
                }
            }
            // after delete, have a reviewer
            if ($reviewerNo == 2 && !$revieweredExists && !$feedbackedExists) {
                $response['data']['reviewer_only'] = 1;
            } else {
                $response['data']['reviewer_only'] = 0;
            }
            DB::commit();
            if ($reviewerId == Permission::getInstance()->getEmployee()->id) {
                $response['data']['self_remove'] = 1;
            }
            $response['success'] = 1;
            $response['message'] = Lang::get('project::message.Remove reviewer success');
            return response()->json($response);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            Log::info($ex);
            DB::rollback();
            return response()->json($response);
        }
    }
    
    /**
     * add reviewer
     */
    public function addReviewer($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        list ($task, $project, $error, $message) = $this->processReviewData($id);
        if ($error) {
            $response['error'] = $error;
            $response['message'] = $message;
            return response()->json($response);
        }
        //check access add approver: COO, approver
        $approver = $reviewerDelete = TaskAssign::findAssignee(
            $task->id, 
            null,
            TaskAssign::ROLE_APPROVER, 
            null
        );
        if (
            !Permission::getInstance()->isAllow('project-access::task.approve.chagnge.reviewer') &&
            Permission::getInstance()->getEmployee()->id != $approver->employee_id
        ) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.You don\'t have access');
            return response()->json($response);
        }
        if ($task->status == Task::STATUS_APPROVED) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Workorder approved');
            return response()->json($response);
        }
        $reviewerId = Input::get('assign.id');
        if (!$reviewerId || !is_numeric($reviewerId)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error input data!');
            return response()->json($response);
        }
        // find reviewer add
        $reviewer = Employee::find($reviewerId);
        if (!$reviewer) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Not found item.');
            return response()->json($response);
        }
        $reviewerAdd = TaskAssign::findAssignee(
            $task->id, 
            $reviewerId,
            TaskAssign::ROLE_REVIEWER, 
            null,
            true
        );
        if ($reviewerAdd) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Existing reviewer');
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $assignee = new TaskAssign();
            $assignee->setData([
                'task_id' => $id,
                'employee_id' => $reviewerId,
                'role' => TaskAssign::ROLE_REVIEWER,
                'status' => TaskAssign::STATUS_NO
            ])
            ->save();
            if ($task->status == Task::STATUS_REVIEWED) {
                $task->status = Task::STATUS_SUBMITTED;
                $task->save([], $project, [
                    'history' => false
                ]);
            }
            
            TaskHistory::storeHistory($task, $project, 
                [
                    'wo_add_reviewer' => $reviewer->email
                ], 
                [
                    'wo_add_reviewer' => $reviewer->email
                ], 
                [
                    'text_change_custom' => 'wo_add_reviewer'
                ]
            );
            TaskAssign::sendMailApprover($task, [
                'project' => $project,
                'employee' => $reviewer,
                'subject' => 'project::email.[Work order] Please review '
                    . ':titleTask of project :project',
                'template' => 'project::emails.wo_need_review'
            ]);
            DB::commit();
            $response['popup'] = 1;
            $response['success'] = 1;
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('project::message.Add reviewer success'),
                        ]
                    ]
            );
            $response['refresh'] = URL::route('project::task.edit', ['id' => $id]);
            $response['message'] = Lang::get('project::message.Add reviewer success');
            return response()->json($response);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('project::message.Error system');
            Log::info($ex);
            DB::rollback();
            return response()->json($response);
        }
    }
}
