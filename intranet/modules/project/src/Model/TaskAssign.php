<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\EmailQueue;
use Exception;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\PqaResponsibleTeam;

class TaskAssign extends CoreModel
{
    protected $table = 'task_assigns';
    public $timestamps = false;
    
    /*
     * primary key
     */
    protected $primaryKey = ['task_id', 'employee_id', 'role'];
    protected $fillable = [ 'task_id', 'employee_id', 'role', 'status'];
    public $incrementing = false;
    
    const TYPE_STATUS_NEW = 1;
    const TYPE_STATUS_CHANGE = 2;
    const ROLE_OWNER = 0;
    const ROLE_MEMBER = 1;    
    const ROLE_REVIEWER = 2;
    const ROLE_APPROVER = 3;
    const ROLE_ASSIGNEE = 4;
    const ROLE_PM = 10;
    const ROLE_COO = 11;
    const ROLE_PQAL = 12;
    const ROLE_SQAL = 13;
    const ROLE_PARTICIPANT = 20;
    const ROLE_REPORTER = 21;

    const STATUS_NO = 0;
    const STATUS_REVIEWED = 1;
    const STATUS_FEEDBACK = 2;
    const STATUS_APPROVED = 3;
    const STATUS_FEEDBACK_NOTUNDO = 7;

    public static function roleLabel()
    {
        return [
            self::ROLE_OWNER => Lang::get('project::view.Assignee'),
            self::ROLE_PARTICIPANT => Lang::get('project::view.Participants'),
        ];
    }

    /**
     * insert member to task assign only member of project
     * 
     * @param object $task
     * @param array $assigneeIds
     * @param array $employeeAvai
     * @param array $option
     * @return boolean
     */
    public static function insertMember($task, $assigneeIds, $employeeAvai, $option = [])
    {
        if (!$task->id) {
            return;
        }
        $assigneeIds = (array) $assigneeIds;
        $assigneeIdsClone = $assigneeIds;
        $employeeAvai = (array) $employeeAvai;
        if (!$employeeAvai) {
            return;
        }
        $assignInsert = array_intersect($assigneeIds, $employeeAvai);
        if (!$assignInsert || !count($assignInsert)) {
            return;
        }
        $oldAssigneeIds = self::getAssignee($task->id);
        $dataInsert = [];
        foreach ($assignInsert as $item) {
            $dataInsert[] = [
                'task_id' => $task->id,
                'employee_id' => $item
            ];
        }
        if (array_diff($assignInsert, $oldAssigneeIds) || 
            array_diff($oldAssigneeIds, $assignInsert)
        ) {
            if (!isset($option['project'])) {
                $project = $task->getProject();
            } else {
                $project = $option['project'];
            }
            $members = $project->getMembersKey();
            $old = $new = '';
            foreach ($oldAssigneeIds as $item) {
                if (isset($members[$item])) {
                    $old .= $members[$item]->email . ', ';
                }
            }
            foreach ($assignInsert as $item) {
                if (isset($members[$item])) {
                    $new .= $members[$item]->email . ', ';
                }
            }
            $old = substr($old, 0, -2);
            $new = substr($new, 0, -2);
            $old = [
                'task_assigns' => $old
            ];
            $new = [
                'task_assigns' => $new
            ];
            $history = true;
        } else {
            $history = false;
        }
        
        DB::beginTransaction();
        try {
            if ($history) {
                TaskHistory::storeHistory($task, $project, $old, $new);
            }
            self::where('task_id', $task->id)->delete();
            self::insert($dataInsert);
            self::sendEmailToAssignee($task, $assignInsert, $option);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public static function delByIssue($issueId)
    {
        self::where('task_id', $issueId)->delete();
    }

    /**
     * insert or update assignee of task
     *      keep old assignee and insert new assign
     * 
     * @param type $task
     * @param array $assignees
     * @param array $option
     * @return type
     * @throws Exception
     */
    public static function insertOrUpdateAssigneeWOReviewer(
            $task, 
            array $assignees, 
            array $option = []
    ) {
        if (!$task->id || !$assignees) {
            return;
        }
        $idsAssigneeNew = [];
        // foreach new assign reviewer
        foreach ($assignees as $item) {
            $assignExists = self::where('task_id', $task->id)
                ->where('employee_id', $item['employee_id'])
                ->where('role', $item['role'])
                ->first();
            if (!$assignExists) {
                $assignExists = new self();
                $assignExists->setData($item);
                $assignExists->task_id = $task->id;
            }
            $idsAssigneeNew[] = $item['employee_id'];
            $assignExists->status = self::STATUS_NO;
            $assignExists->save();
        }
        //update status for old assinee
        $assignsOld = self::select('employee_id', 'role', 'status')
            ->where('task_id', $task->id)
            ->whereNotIn('employee_id', $idsAssigneeNew)
            ->get();
        if (count($assignsOld)) {
            foreach ($assignsOld as $assignOld) {
                $assignOld->status = self::STATUS_NO;
                $assignOld->save();
                if ($assignOld->role == self::ROLE_REVIEWER) {
                    $idsAssigneeNew[] = $assignOld->employee_id;
                }
            }
        }
        if (isset($option['project']) && $option['project']) {
            $project = $option['project'];
        } else {
            $project = null;
        }
        //insert COO / approver
        $approver = self::where('task_id', $task->id)
                ->where('role', self::ROLE_APPROVER)
                ->first();
        if (!$approver) {
            $emailCoo = CoreConfigData::getCOOAccount();
            $idCoo = Employee::getIdEmpByEmail($emailCoo);
            $groupLeader = $project->groupLeader;

            $approver = new self();
            $approver->task_id = $task->id;
            $approver->role = self::ROLE_APPROVER;
            if ($groupLeader) {
                $approver->employee_id = $groupLeader->id;
            } elseif ($idCoo) {
                $approver->employee_id = $idCoo;
            } else {
                // nothing
            }
        }
        if ($approver) {
            $approver->status = self::STATUS_NO;
            $approver->save();
        }

        TaskHistory::storeHistory($task, $project, true, true, [
            'text_change_custom' => 'wo_submit_custom'
        ]);
        self::sendMailReviewer($task, $idsAssigneeNew, $option);
    }
    
    /**
     * change approver for wo
     *      delete old approver, add new approver, send email
     * 
     * @param object $task
     * @param object $approver
     * @param array $option
     * @return type
     * @throws Exception
     */
    public static function changeApproverWO (
            $task, 
            $approver, 
            array $option = []
    ) {
        if (!$task->id || !$approver) {
            return;
        }
        // remove old approver
        $approverOld = self::where('task_id', $task->id)
            ->where('role', self::ROLE_APPROVER)
            ->first();
        if ($approverOld) {
            $approverOld = Employee::find($approverOld->employee_id);
        }
        self::where('task_id', $task->id)
            ->where('role', self::ROLE_APPROVER)
            ->delete();
        
        // add new approver
        $assign = new self();
        $assign->task_id = $task->id;
        $assign->employee_id = $approver->id;
        $assign->role = self::ROLE_APPROVER;
        $assign->status = self::STATUS_NO;
        $assign->save();
        if (isset($option['project']) && $option['project']) {
            $project = $option['project'];
        } else {
            $project = null;
        }
        TaskHistory::storeHistory($task, $project, 
            [
                'wo_change_approver' => $approverOld ? $approverOld->email : ''
            ], 
            [
                'wo_change_approver' => $approver->email
            ], 
            [
                'text_change_custom' => 'wo_change_approver'
            ]
        );
        self::sendMailApprover($task, [
            'project' => $project,
            'employee' => $approver,
            'subject' => 'project::email.[Work order] Please approve '
                . ':titleTask of project :project',
            'template' => 'project::emails.wo_change_approver'
        ]);
    }
    
    /**
     * send mail to reviewer
     * 
     * @param object $task
     * @param array $employeeIds
     * @param array $option
     * @return boolean
     */
    public static function sendMailReviewer(
            $task,
            array $employeeIds = [],
            array $option = []
    ) {
        if (!isset($option['project'])) {
            $project = $task->getProject();
        } else {
            $project = $option['project'];
        }
        if (!$task || !$project) {
            return;
        }
        $pm = $project->getPmActive();
        if ($pm) {
            $pm = $pm->name . ' (' . $pm->email . ')';
        }
        $dataOriginTemplate = [
            'project_name' => $project->name,
            'task_type' => $task->getType(),
            'task_link' => URL::route('project::task.edit', ['id' => $task->id]),
            'task_title' => $task->title,
            'project_pm' => $pm,
            'project_group' => $project->getTeamsString()
        ];
        $employees = Employee::select('id', 'name', 'email')
            ->whereIn('id', $employeeIds)
            ->get();
        $subject = Lang::get('project::email.[Work order] Please review '
                . ':titleTask of project :project', [
            'titleTask' => $task->title,
            'project' => $project->name
        ]);
        $empIds = [];
        foreach ($employees as $employee) {
            $empIds[] = $employee->id;
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($employee->email, $employee->name)
                ->setSubject($subject)
                ->setTemplate('project::emails.wo_need_review', array_merge($dataOriginTemplate, [
                    'dear_name' => $employee->name
                ]))
                ->save();
        }
        \RkNotify::put($empIds, $subject, $dataOriginTemplate['task_link'], ['category_id' => RkNotify::CATEGORY_PROJECT]);
        return true;
    }
    
    /**
     * send mail to reviewer
     * 
     * @param object $task
     * @param array $employeeIds
     * @param array $option
     * @return boolean
     */
    public static function sendMailApprover(
            $task,
            array $option = []
    ) {
        $option = array_merge([
            'project' => null,
            'employee' => false,
            'subject' => 'project::email.[Work order] Please approve '
                . ':titleTask of project :project',
            'template' => 'project::emails.wo_need_approve'
        ], $option);
        if (!isset($option['project'])) {
            $project = $task->getProject();
        } else {
            $project = $option['project'];
        }
        if (!$task || !$project) {
            return;
        }
        if ($option['employee'] === false) {
            $approver = self::findEmployeeByRole($task->id, self::ROLE_APPROVER);
            if (!$approver) {
                $approver = GeneralProject::getCOOEmployee();
            }
            $option['employee'] = $approver;
        }
        
        if (!$option['employee']) {
            return null;
        }
        $pm = $project->getPmActive();
        if ($pm) {
            $pm = $pm->name . ' (' . $pm->email . ')';
        }
        $dataOriginTemplate = [
            'project_name' => $project->name,
            'task_type' => $task->getType(),
            'task_link' => URL::route('project::task.edit', ['id' => $task->id]),
            'task_title' => $task->title,
            'project_pm' => $pm,
            'project_group' => $project->getTeamsString()
        ];
        $subject = Lang::get($option['subject'], [
            'titleTask' => $task->title,
            'project' => $project->name
        ]);
        $dataTemplate = [
            'dear_name' => $option['employee']->name,
        ];
        if (isset($option['feedback_content']) && $option['feedback_content']) {
            $dataTemplate['feedback_content'] = $option['feedback_content'];
        }
        $subPms = ProjectMember::getSubPmOfProject($project->id);
        $emailQueue = new EmailQueue();
        /* add cc SubPm */
        foreach ($subPms as $subPm) {
            if ($subPm->email) {
                $emailQueue->addCc($subPm->email);
                $emailQueue->addCcNotify($subPm->id);
            }
        }
        /* end add */
        $emailQueue->setTo($option['employee']->email, $option['employee']->name)
            ->setSubject($subject)
            ->setTemplate($option['template'], array_merge($dataOriginTemplate, $dataTemplate))
            ->setNotify($option['employee']->id, null, $dataOriginTemplate['task_link'], [
                    'category_id' => RkNotify::CATEGORY_PROJECT,
                    'content_detail' => RkNotify::renderSections($option['template'], array_merge($dataOriginTemplate, $dataTemplate))
            ])
            ->save();
    }

    /**
     * get assignee info of task
     * 
     * @param int $taskId
     * @return array
     */
    public static function getAssigneesInfo($taskId, $participant = false)
    {
        $taskAssignsTable = self::getTableName();
        $employeeTable = Employee::getTableName();

        return self::select($taskAssignsTable.'.employee_id', 
                $employeeTable.'.email')
            ->where('task_id', $taskId)
            ->where('role', ($participant)? TaskAssign::ROLE_PARTICIPANT : TaskAssign::ROLE_OWNER)
            ->join($employeeTable, "{$employeeTable}.id", '=',
                "{$taskAssignsTable}.employee_id")
            ->orderBy($employeeTable.'.email')
            ->orderBy($taskAssignsTable . '.employee_id')
            ->get();
    }

    /**
     * get assign of task
     *
     * @param int $taskId
     * @return array
     */
    public static function getAssignee($taskId, $participant=false)
    {
        $collection = self::select('employee_id')
            ->where('task_id', $taskId)
            ->where('role', ($participant)? TaskAssign::ROLE_PARTICIPANT: TaskAssign::ROLE_OWNER)
            ->orderBy('employee_id')
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $result[] = $item->employee_id;
        }
        return $result;
    }
    
    /**
     * get all kind of assignee
     * 
     * @param int $taskId
     * @return array
     */
    public static function getAssigneeAndRole($taskId)
    {
        $tableAssign = self::getTableName();
        $tableEmployee = Employee::getTableName();
        
        return self::select("{$tableEmployee}.id", "{$tableAssign}.role", 
                "{$tableAssign}.status", "{$tableEmployee}.name",
                "{$tableEmployee}.email", "{$tableAssign}.employee_id")
            ->where('task_id', $taskId)
            ->join($tableEmployee, "{$tableEmployee}.id", '=', "{$tableAssign}.employee_id")
            ->whereNull("{$tableEmployee}.deleted_at")
            ->orderBy("{$tableAssign}.status")
            ->orderBy("{$tableEmployee}.email")
            ->get();
    }
    
    /**
     * get assign reviewer of task
     * 
     * @param int $taskId
     * @return array
     */
    public static function getAssigneeReviewer($taskId)
    {
        $tableAssign = self::getTableName();
        $tableEmployee = Employee::getTableName();
        
        return self::select("{$tableEmployee}.id", "{$tableAssign}.status",
                "{$tableEmployee}.name", "{$tableEmployee}.email")
            ->join($tableEmployee, "{$tableEmployee}.id", '=', "{$tableAssign}.employee_id")
            ->where('task_id', $taskId)
            ->where("{$tableAssign}.role", self::ROLE_REVIEWER)
            ->get();
    }
    
    /**
     * get assign reviewer of task
     * 
     * @param int $taskId
     * @return array
     */
    public static function findAssigneeByEmail($taskId, $email, $role = self::ROLE_REVIEWER)
    {
        $tableAssign = self::getTableName();
        $tableEmployee = Employee::getTableName();
        
        return self::select("{$tableEmployee}.id", "{$tableAssign}.status",
                "{$tableEmployee}.name", "{$tableEmployee}.email")
            ->join($tableEmployee, "{$tableEmployee}.id", '=', "{$tableAssign}.employee_id")
            ->where('task_id', $taskId)
            ->where("{$tableAssign}.role", $role)
            ->where("{$tableEmployee}.email", $email)
            ->first();
    }
    
    /**
     * get assign reviewer of task
     * 
     * @param int $taskId
     * @return array
     */
    public static function findAssigneeById($taskId, $employeeId, $role = self::ROLE_REVIEWER)
    {
        return self::where('task_id', $taskId)
            ->where('role', $role)
            ->where('employee_id', $employeeId)
            ->first();
    }
    
    /**
     * find assignee
     * 
     * @param int $taskId
     * @return object
     */
    public static function findAssignee(
        $taskId, 
        $employeeId = null, 
        $role = null,
        $status = null,
        $count = false
    ) {
        $assignee = self::where('task_id', $taskId);
        if ($employeeId) {
            $assignee->where('employee_id', $employeeId);
        }
        if ($role) {
            $role = (array) $role;
            $assignee->whereIn('role', $role);
        }
        if ($status !== null) {
            $status = (array) $status;
            $assignee->whereIn('status', $status);
        }
        if ($count) {
            return $assignee->count();
        }
        return $assignee->first();
    }
    
    /**
     * find assignee
     * 
     * @param int $taskId
     * @return object
     */
    public static function deleteAssignee(
        $taskId, 
        $employeeId = null, 
        $role = null,
        $status = null
    ) {
        $assignees = self::where('task_id', $taskId);
        if ($employeeId) {
            $assignees->where('employee_id', $employeeId);
        }
        if ($role) {
            $assignees->where('role', $role);
        }
        if ($status !== null) {
            $assignees->where('status', $status);
        }
        return $assignees->delete();
    }
    
    /**
     * get assign reviewer by status of task
     * 
     * @param int $taskId
     * @return array
     */
    public static function findAssigneeFeedback($taskId, $employeeId = null)
    {
        $item = self::where('task_id', $taskId)
            ->whereIn('status', [self::STATUS_FEEDBACK, self::STATUS_FEEDBACK_NOTUNDO]);
        if ($employeeId) {
            $item->where('employee_id', $employeeId);
        }
        return $item->first();
    }
    
    /**
     * reset status feedback
     *  status => STATUS_NO
     * 
     * @param int $taskId
     */
    public static function resetFeedback($taskId)
    {
        return self::where('task_id', $taskId)
            ->whereIn('status', [self::STATUS_FEEDBACK, self::STATUS_FEEDBACK_NOTUNDO])
            ->update([
                'status' => self::STATUS_NO
            ]);
    }
    
    /**
     * get assign reviewer of task
     * 
     * @param int $taskId
     * @return object
     */
    public static function findEmployeeByRole($taskId, $role = self::ROLE_REVIEWER)
    {
        $tableAssign = self::getTableName();
        $tableEmployee = Employee::getTableName();
        
        return self::select("{$tableEmployee}.id", "{$tableAssign}.status",
                "{$tableEmployee}.name", "{$tableEmployee}.email")
            ->join($tableEmployee, "{$tableEmployee}.id", '=', "{$tableAssign}.employee_id")
            ->where('task_id', $taskId)
            ->where("{$tableAssign}.role", $role)
            ->first();
    }
    
    /**
     * check all reviewer reviewed
     * 
     * @param int $taskId
     * @return boolean
     */
    public static function isAllreviewed($taskId)
    {
        $collection = self::select('status')
            ->where('task_id', $taskId)
            ->where('role', self::ROLE_REVIEWER)
            ->get();
        foreach ($collection as $item) {
            if ($item->status != self::STATUS_REVIEWED) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * send mail noti to assign of task
     * 
     * @param object $task
     * @param array $employeeIds
     * @param array $option
     * @return boolean
     */
    public static function sendEmailToAssignee(
            $task,
            $employeeIds = [],
            $option = [],
            $participant = false
    ) {
        if (!isset($option['project'])) {
            $project = $task->getProject();
            if (!$project) {
                $project = new Project();
            }
        } else {
            $project = $option['project'];
        }
        if (!isset($option['type'])) {
            $type = self::TYPE_STATUS_NEW;
        } else {
            $type = (int) $option['type'];
        }
        if (!$task) {
            return;
        }
        $dataOriginTemplate = [
            'project_name' => $project->name,
            'task_type' => $task->getType(),
            'task_link' => URL::route('project::task.edit', ['id' => $task->id]),
            'task_title' => $task->title
        ];
        if (!$employeeIds) {
            $employeeIds = ($participant)? self::getAssignee($task->id) : self::getAssignee($task->id, true);
        }
        $users = Employee::select('id', 'email', 'name')
            ->whereIn('id', $employeeIds)
            ->get();
        if (!$users || !count($users)) {
            return;
        }
        $usersFirst = $users->first();
        $users->shift();
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($usersFirst->email, $usersFirst->name);
        if (count($users)) {
            foreach ($users as $user) {
                $emailQueue->addCc($user->email, $user->name);
                $emailQueue->addCcNotify($user->id);
            }
        }
        
        if ($project->id) { // task of project
            switch ($type) {
                case self::TYPE_STATUS_CHANGE: // changed
                    $template = 'project::emails.task_change';
                    $subject = '[Rikkeisoft] Task :title has been updated and assigned to you.';
                    break;
                default: // new 
                    $template = 'project::emails.task_create';
                    $subject = '[Rikkeisoft] Task :title has been created and assigned to you.';
                    break;
            }
            $emailQueue->setSubject(Lang::get('project::email.' . $subject, [
                'project' => $project->name,
                'type' => $task->getType(),
                'title' => $task->title
            ]))
            ->setTemplate($template, [
                'project_name' => $project->name,
                'task_type' => $task->getType(),
                'task_link' => URL::route('project::task.edit', ['id' => $task->id]),
                'task_title' => $task->title,
                'participant' => $participant,
            ]);
        } else { // task general
            switch ($type) {
                case self::TYPE_STATUS_CHANGE: // changed
                    $template = 'project::emails.task_general_change';
                    $subject = '[Rikkeisoft] Task :title has been updated and assigned to you.';
                    break;
                default: // new 
                    $template = 'project::emails.task_general_create';
                    $subject = '[Rikkeisoft] Task :title has been created and assigned to you.';
                    break;
            }
            $emailQueue->setSubject(Lang::get('project::email.' . $subject, ['title' => $task->title]))
            ->setTemplate($template, [
                'task_link' => URL::route('project::task.edit', ['id' => $task->id]),
                'task_title' => $task->title,
                'participant' => $participant,
            ]);
        }
        $emailQueue->setNotify($usersFirst->id, null, route('project::task.edit', ['id' => $task->id]), ['category_id' => RkNotify::CATEGORY_PROJECT]);
        $emailQueue->save();
        return true;
    }
    
    /**
     * send mail noti to created_by of task
     * 
     * @param object $task
     * @param array $employeeIds
     * @param array $option
     * @return boolean
     */
    public static function sendEmailToCreatedBy(
        $task,
        array $option = []
    ) {
        $creator = $task->created_by;
        if ($creator) {
            $creator = Employee::find($creator);
            if (!$creator) {
                return true;
            }
        }
        if (!isset($option['project'])) {
            $project = $task->getProject();
            if (!$project) {
                $project = new Project();
            }
        } else {
            $project = $option['project'];
        }
        if (!isset($option['type'])) {
            $type = self::TYPE_STATUS_CHANGE;
        } else {
            $type = (int) $option['type'];
        }
        if (!$task) {
            return;
        }
        $dataOriginTemplate = [
            'project_name' => $project->name,
            'task_type' => $task->getType(),
            'task_link' => URL::route('project::task.edit', ['id' => $task->id]),
            'task_title' => $task->title
        ];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($creator->email, $creator->name);
        
        if ($project->id) { // task of project
            switch ($type) {
                case self::TYPE_STATUS_CHANGE: // changed
                    $template = 'project::emails.task_change_created_by';
                    $subject = '[Rikkeisoft] Task :title has been updated.';
                    break;
                default: // new 
                    $template = 'project::emails.task_create_created_by';
                    $subject = '[Rikkeisoft] Task :title has been created.';
                    break;
            }
            $emailQueue->setSubject(Lang::get('project::email.' . $subject, [
                'project' => $project->name,
                'type' => $task->getType()
            ]))
            ->setTemplate($template, [
                'project_name' => $project->name,
                'task_type' => $task->getType(),
                'task_link' => URL::route('project::task.edit', ['id' => $task->id]),
                'task_title' => $task->title,
            ]);
        } else { // task general
            switch ($type) {
                case self::TYPE_STATUS_CHANGE: // changed
                    $template = 'project::emails.task_general_change_created_by';
                    $subject = '[Rikkeisoft] Task :title has been updated.';
                    break;
                default: // new 
                    $template = 'project::emails.task_general_create_created_by';
                    $subject = '[Rikkeisoft] Task :title has been created.';
                    break;
            }
            $emailQueue->setSubject(Lang::get('project::email.' . $subject, ['title' => $task->title]))
            ->setTemplate($template, [
                'task_link' => URL::route('project::task.edit', ['id' => $task->id]),
                'task_title' => $task->title,
            ]);
        }
        $emailQueue->setNotify($creator->id, null, route('project::task.edit', ['id' => $task->id]), ['category_id' => RkNotify::CATEGORY_PROJECT]);
        $emailQueue->save();
        return true;
    }
    
    /**
     * rewrite save model
     *     primary is array
     * 
     * @param array $options
     */
    public function save(array $options = []) {
        if( ! is_array($this->getKeyName())) {
            return parent::save($options);
        }
        // Fire Event for others to hook
        if($this->fireModelEvent('saving') === false) {
            return false;
        }
        // Prepare query for inserting or updating
        $query = $this->newQueryWithoutScopes();
        // Perform Update
        if ($this->exists) {
            if (count($this->getDirty()) > 0) {
                // Fire Event for others to hook
                if ($this->fireModelEvent('updating') === false) {
                    return false;
                }

                // Touch the timestamps
                if ($this->timestamps) {
                    $this->updateTimestamps();
                }
                ////// start fix
                // Convert primary key into an array if it's a single value
                $primary = (array) $this->getKeyName();
                // Fetch the primary key(s) values before any changes
                $unique = array_intersect_key($this->original, array_flip($primary));
                // Fetch the primary key(s) values after any changes
                $unique = !empty($unique) ? $unique : array_intersect_key($this->getAttributes(), array_flip($primary));
                // Fetch the element of the array if the array contains only a single element
                //$unique = (count($unique) <> 1) ? $unique : reset($unique);
                // Apply SQL logic
                $query->where($unique);
                ////// END FIX

                // Update the records
                $query->update($this->getDirty());
                // Fire an event for hooking into
                $this->fireModelEvent('updated', false);
            }
        } else { // Insert
            // Fire an event for hooking into
            if ($this->fireModelEvent('creating') === false) {
                return false;
            }
            // Touch the timestamps
            if($this->timestamps) {
                $this->updateTimestamps();
            }
            // Retrieve the attributes
            $attributes = $this->attributes;
            if ($this->incrementing && !is_array($this->getKeyName())) {
                $this->insertAndSetId($query, $attributes);
            } else {
                $query->insert($attributes);
            }
            // Set exists to true in case someone tries to update it during an event
            $this->exists = true;
            // Fire an event for hooking into
            $this->fireModelEvent('created', false);
        }

        // Fires an event
        $this->fireModelEvent('saved', false);
        // Sync
        $this->original = $this->attributes;
        // Touches all relations
        if (array_get($options, 'touch', true)) {
            $this->touchOwners();
        }
        return true;
    }

    /**
     * get all status of assignees
     * 
     * @return type
     */
    public static function statusLabel()
    {
        return [
            self::STATUS_NO => Lang::get('project::view.No confirm'),
            self::STATUS_REVIEWED => Lang::get('project::view.Reviewed'),
            self::STATUS_FEEDBACK => Lang::get('project::view.Feedback'),
            self::STATUS_APPROVED => Lang::get('project::view.Approved'),
            self::STATUS_FEEDBACK_NOTUNDO => Lang::get('project::view.Feedback'),
        ];
    }
    
    /**
     * get employee available to assign
     */
    public static function assigneeAvai($projectId, $taskId = null, $type = 1)
    {
        $tableMember = ProjectMember::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableProject = Project::getTableName();
        $tableSale = SaleProject::getTableName();
        $tableTeam = Team::getTableName();
        $tableAssignee = self::getTableName();
        
        $softDeleteEmployee = $softDeleteProject = $softDeleteTeam = false;
        if (Employee::isUseSoftDelete()) {
            $softDeleteEmployee = true;
        }
        if (Project::isUseSoftDelete()) {
            $softDeleteProject = true;
        }
        if (Team::isUseSoftDelete()) {
            $softDeleteTeam = true;
        }
        // get members
        $queryMember = "select `{$tableEmployee}`.`id` as `id`, "
            . "{$tableEmployee}.email, {$tableEmployee}.name "
            . "from `{$tableEmployee}` "
            . "inner join `{$tableMember}` "
            . "on `{$tableMember}`.`employee_id` = `{$tableEmployee}`.`id` "
            . "and `{$tableMember}`.`project_id` = '{$projectId}' ";
        if ($softDeleteEmployee) {
            $queryMember .= "where `{$tableEmployee}`.`deleted_at` is null ";
        }
        // get pm
        $queryPm = "select `{$tableEmployee}`.`id` as `id`, "
            . "{$tableEmployee}.email, {$tableEmployee}.name "
            . "from `{$tableEmployee}` "
            . "inner join `{$tableProject}` "
            . "on `{$tableProject}`.`manager_id` = `{$tableEmployee}`.`id` "
            . "and `{$tableProject}`.`id` = '{$projectId}' ";
        if ($softDeleteProject) {
            $queryPm .= "and `{$tableProject}`.`deleted_at` is null ";
        }
        if ($softDeleteEmployee) {
            $queryPm .= "where `{$tableEmployee}`.`deleted_at` is null ";
        }
        // get sales
        $querySales = "select `{$tableEmployee}`.`id` as `id`, "
            . "{$tableEmployee}.email, {$tableEmployee}.name "
            . "from `{$tableEmployee}` "
            . "inner join `{$tableSale}` "
            . "on `{$tableSale}`.`employee_id` = `{$tableEmployee}`.`id` "
            . "and `{$tableSale}`.`project_id` = '{$projectId}' "
            . "where 1 ";
        if ($softDeleteEmployee) {
            $querySales .= "and `{$tableEmployee}`.`deleted_at` is null ";
        }
        // get bom of company
        $queryBom = "select `{$tableEmployee}`.`id` as `id`, "
            . "{$tableEmployee}.email, {$tableEmployee}.name "
            . "from `{$tableEmployee}` "
            . "inner join `{$tableTeam}` "
            . "on `{$tableTeam}`.`leader_id` = `{$tableEmployee}`.`id` "
            . "where 1 ";
        if ($softDeleteEmployee) {
            $queryBom .= "and `{$tableEmployee}`.`deleted_at` is null ";
        }
        if ($softDeleteTeam) {
            $queryBom .= "and `{$tableTeam}`.`deleted_at` is null ";
        }
        // get assignee old
        $queryAssign = "select `{$tableEmployee}`.`id` as `id`, "
            . "{$tableEmployee}.email, {$tableEmployee}.name "
            . "from `{$tableEmployee}` "
            . "inner join `{$tableAssignee}` "
            . "on `{$tableAssignee}`.`employee_id` = `{$tableEmployee}`.`id` "
            . "where `{$tableAssignee}`.`task_id` = '{$taskId}'";
        if ($softDeleteEmployee) {
            $queryAssign .= "and `{$tableEmployee}`.`deleted_at` is null";
        }
        
        // query select all member assign able
        $query = 'select `id`, `email`, `name` FROM (' . 
            $queryMember . ' union all ' . $queryPm . ' union all ' . $querySales 
                . ' union all ' . $queryBom . ' union all ' . $queryAssign;
        $query .= ')'
            . ' as `project_member_union` ';
        $query .= 'group by `project_member_union`.`id`';
        $collection = DB::select($query);
        switch ($type) {
            case 1: // return array collection
                return $collection;
            case 2: // return id of employee
                $ids = [];
                foreach ($collection as $item) {
                    $ids[] = $item->id;
                }
                return $ids;
            default:
                return $collection;
        }
        return $collection;
    }
    
    /**
     * get assign of reward
     * 
     * @param model $task
     * @return array
     */
    public static function getAssignReward($task)
    {
        $assignees = self::getAssigneeAndRole($task->id);
        $result = [
            'role' => [
                self::ROLE_PM => null,
                self::ROLE_REVIEWER => null,
                self::ROLE_APPROVER => null
            ],
            'employee' => []
        ];
        if (!$assignees || !count($assignees)) {
            return $result;
        }
        foreach ($assignees as $assignee) {
            if (isset($result['role'][$assignee->role]['employee_id'])) {
                continue;
            }
            $result['role'][$assignee->role] = [
                'employee_id' => $assignee->employee_id,
                'email' => $assignee->email,
                'name' => $assignee->name,
                'status' => $assignee->status
            ];
            $result['employee'][] = $assignee->employee_id;
        }
        return $result;
    }

    /**
     * get assign of reward
     * 
     * @param model $task
     * @return array
     */
    public static function getAssignCusFeedback($task)
    {
        $assignees = self::getAssigneeAndRole($task->id);
        $result = [
            'role' => [
                self::ROLE_OWNER => null,
                self::ROLE_PARTICIPANT => null,
            ],
            'employee' => null
        ];
        if (!$assignees || !count($assignees)) {
            return $result;
        }
        foreach ($assignees as $assignee) {
            if (isset($result['role'][$assignee->role]['employee_id'])) {
                continue;
            }
            $result['role'][$assignee->role][] = [
                'employee_id' => $assignee->employee_id,
                'email' => $assignee->email,
                'name' => $assignee->name,
                'status' => $assignee->status
            ];
            $result['employee'][] = $assignee->employee_id;
        }
        return $result;
    }
    
    /**
     * insert members to assign
     * 
     * @param model $task
     * @param array $assigneeIds
     * @param array $option
     * @return type
     * @throws Exception
     */
    public static function insertMembers($task, $assigneeIds, $option = [], $participant = false)
    {
        if (!$task->id) {
            return;
        }
        $assigneeIds = (array) $assigneeIds;
        $oldAssigneeIds = ($participant)? self::getAssignee($task->id, true) : self::getAssignee($task->id);
        $assignOldAndNewIds = array_merge($assigneeIds, $oldAssigneeIds);
        // check employee in system
        $employeesAvai = Employee::select('id', 'email')
            ->whereIn('id', $assignOldAndNewIds)->get();
        if (!count($employeesAvai)) {
            return;
        }
        $dataInsert = [];
        $assigneeIdsInsert = [];
        $assignOldAndNewCollection = [];
        // add assign new avai
        foreach ($employeesAvai as $item) {
            $assignOldAndNewCollection[$item->id] = $item;
            if (!in_array($item->id, $assigneeIds)) {
                continue;
            }
            $dataInsert[] = [
                'task_id' => $task->id,
                'employee_id' => $item->id,
                'role' => ($participant)? TaskAssign::ROLE_PARTICIPANT : TaskAssign::ROLE_OWNER,
            ];
            $assigneeIdsInsert[] = $item->id;
        }
        if (array_diff($assigneeIdsInsert, $oldAssigneeIds) || 
            array_diff($oldAssigneeIds, $assigneeIdsInsert)
        ) {
            $old = $new = '';
            foreach ($oldAssigneeIds as $item) {
                if (isset($assignOldAndNewCollection[$item])) {
                    $old .= $assignOldAndNewCollection[$item]->email . ', ';
                }
            }
            foreach ($assigneeIdsInsert as $item) {
                if (isset($assignOldAndNewCollection[$item])) {
                    $new .= $assignOldAndNewCollection[$item]->email . ', ';
                }
            }
            // remove , and space at the end of email string
            $old = substr($old, 0, -2);
            $new = substr($new, 0, -2);
            $old = [
                ($participant)? 'task_participants' : 'task_assigns' => $old
            ];
            $new = [
                ($participant)? 'task_participants' : 'task_assigns' => $new
            ];
            $history = true;
        } else {
            $history = false;
        }
        DB::beginTransaction();
        try {
            if ($history) {
                TaskHistory::storeHistory($task, null, $old, $new);
            }
            // delete old assign
            self::where('task_id', $task->id)
                ->where('role', ($participant)? TaskAssign::ROLE_PARTICIPANT : TaskAssign::ROLE_OWNER)
                ->delete();
            self::insert($dataInsert);
            if ($history || 
                (isset($option['historyResult']) && $option['historyResult'])
            ) {
                if($participant) {
                    self::sendEmailToAssignee($task, $assigneeIdsInsert, $option, true);
                } else {
                    self::sendEmailToAssignee($task, $assigneeIdsInsert, $option);
                }
                if (isset($option['send_created_by']) && 
                    $option['send_created_by'] &&
                    $option['type'] == self::TYPE_STATUS_CHANGE
                ) {
                    self::sendEmailToCreatedBy($task, $option);
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
