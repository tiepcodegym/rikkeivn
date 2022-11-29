<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Exception;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Rikkei\Team\View\Permission;
use Rikkei\Project\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Project\Model\TaskNcmRequest;
use Rikkei\Project\View\GeneralProject;

class TaskHistory extends CoreModel
{
    const TYPE_COMMENT = 1;
    const TYPE_HISTORY = -1;
    
    
    protected $table = 'task_histories';
    
    /**
     * overwrite save model
     * 
     * @param array $options
     */
    public function save(array $options = array()) {
        try {
            if (Permission::getInstance()->getEmployee()) {
                $this->created_by = Permission::getInstance()->getEmployee()->id;
            }
            return parent::save($options);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * 
     * @param object $task
     * @return object
     */
    public static function getHistoryAndComment($task)
    {
        $tableEmployee = Employee::getTableName();
        $tableComment = TaskComment::getTableName();
        $tableNCM = TaskNcmRequest::getTableName();
        $tableHistory = self::getTableName();
        
        $taskCommentQuery = "select `{$tableComment}`.`created_at`, "
            . "`content`, `{$tableEmployee}`.`name`, `{$tableEmployee}`.`email`, "
            . "`{$tableComment}`.`type` "
            . "FROM `{$tableComment}`"
            . "LEFT JOIN `{$tableEmployee}` ON `{$tableEmployee}`.`id` = "
            . "`{$tableComment}`.`created_by` "
            . "WHERE `task_id` = '{$task->id}' ";
        $taskHistoryQuery = "select `{$tableHistory}`.`created_at`, " 
            . "`content`, `{$tableEmployee}`.`name`, `{$tableEmployee}`.`email`, "
            . "'" . self::TYPE_HISTORY . "' as `type` "
            . "FROM `{$tableHistory}` "
            . "LEFT JOIN `{$tableEmployee}` ON `{$tableEmployee}`.`id` = "
            . "`{$tableHistory}`.`created_by` "
            . "WHERE `task_id` = '{$task->id}' ";
        $taskHistory = DB::select("select `created_at`, `content`, "
                . "`name`, `email`, `type` "
                . "FROM ({$taskCommentQuery} union {$taskHistoryQuery}) "
                . "AS table_hiscmt "
                . "ORDER BY `created_at` desc");
        return $taskHistory;
    }
    
    /**
     * get grid data comment of task
     */
    public static function getGridData($taskId)
    {
        $pager = Config::getPagerDataQuery();
        $tableEmployee = Employee::getTableName();
        $tableHistory = self::getTableName();
        
        $collection = self::select("{$tableHistory}.created_at", 'content',
                "{$tableEmployee}.name", "{$tableEmployee}.email")
                ->leftJoin($tableEmployee, "{$tableEmployee}.id", '=',
                    "{$tableHistory}.created_by")
                ->orderBy("{$tableHistory}.created_at", 'desc')
                ->where("{$tableHistory}.task_id", $taskId);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * store history
     * 
     * @param object $task
     * @param object $project
     * @return object
     */
    public static function storeHistory(
            $task, 
            $project = null, 
            $originData = false, 
            $changedData = null,
            array $option = []
    ) {
        if (!$task || !$originData) {
            return true;
        }
        $formatHistory = self::formatHistory();
        if (!isset($option['text_change_custom'])) {
            $formatHeader = '%s - %s'; //email - name
            if (!$changedData) {
                $changedData = $task->getAttributes();
            }
            $statusTask = Task::statusLabel();
            $priorityTask = Task::priorityLabel();
            $typesTaskLabel = Task::typeLabel();
            $resultNCMTest = TaskNcmRequest::getTestResultLabels();
            if (isset($originData['duedate'])) {
                $originData['duedate'] = Carbon::parse($originData['duedate'])->format('Y-m-d');
            }
            if (isset($changedData['duedate'])) {
                $changedData['duedate'] = Carbon::parse($changedData['duedate'])->format('Y-m-d');
            }
            if (isset($originData['actual_date'])) {
                $originData['actual_date'] = Carbon::parse($originData['actual_date'])->format('Y-m-d');
            }
            if (isset($changedData['actual_date'])) {
                $changedData['actual_date'] = Carbon::parse($changedData['actual_date'])->format('Y-m-d');
            }
            if (isset($originData['ncm_request_date'])) {
                $originData['ncm_request_date'] = Carbon::parse($originData['ncm_request_date'])->format('Y-m-d');
            }
            if (isset($changedData['ncm_request_date'])) {
                $changedData['ncm_request_date'] = Carbon::parse($changedData['ncm_request_date'])->format('Y-m-d');
            }
            if (isset($originData['ncm_evaluate_date'])) {
                $originData['ncm_evaluate_date'] = Carbon::parse($originData['ncm_evaluate_date'])->format('Y-m-d');
            }
            if (isset($changedData['ncm_evaluate_date'])) {
                $changedData['ncm_evaluate_date'] = Carbon::parse($changedData['ncm_evaluate_date'])->format('Y-m-d');
            }
            if (isset($option['ncm'])) {
                $dataChangeNcm = [];
                foreach ($option['ncm'] as $key => $value) {
                    $dataChangeNcm['ncm_' . $key] = $value;
                }
                $changedData = array_merge($changedData, $dataChangeNcm);
            }
            if (isset($option['task_assign'])) {
                $dataChangeTaskAssign = [];
                foreach ($option['task_assign'] as $key => $value) {
                    $dataChangeTaskAssign['task_assign_' . $key] = $value;
                }
                $changedData = array_merge($changedData, $dataChangeTaskAssign);
            }
            if (isset($option['is_create']) && $option['is_create']) {
                $changedData['create_new'] = 'create new item';
            }
            if (isset($option['task_team'])) {
                $changedData['task_team'] = $option['task_team'];
            }
            $diffs = View::diffArray($changedData, $originData, isset($option['is_create']) && $option['is_create']);
            if (!count($diffs)) {
                return true;
            }
            $textChanged = '';
            foreach ($diffs as $keyDiff => $valueDiff) {
                if (!isset($formatHistory[$keyDiff])) {
                    continue;
                }
                if ($keyDiff == 'status') {
                    if (!isset($statusTask[$originData[$keyDiff]])) {
                        $originData[$keyDiff] = ' ';
                    } else {
                        $originData[$keyDiff] = $statusTask[$originData[$keyDiff]];
                    }
                    $valueDiff = $statusTask[$valueDiff];

                } elseif ($keyDiff == 'priority') {
                    if (!isset($priorityTask[$originData[$keyDiff]])) {
                        $originData[$keyDiff] = ' ';
                    } else {
                        $originData[$keyDiff] = $priorityTask[$originData[$keyDiff]];
                    }
                    $valueDiff = $priorityTask[$valueDiff];
                } elseif ($keyDiff == 'type') {
                    if (!isset($typesTaskLabel[$originData[$keyDiff]])) {
                        $originData[$keyDiff] = ' ';
                    } else {
                        $originData[$keyDiff] = $typesTaskLabel[$originData[$keyDiff]];
                    }
                    $valueDiff = $typesTaskLabel[$valueDiff];
                } elseif ($keyDiff == 'ncm_test_result') {
                    if (!isset($resultNCMTest[$originData[$keyDiff]])) {
                        $originData[$keyDiff] = ' ';
                    } else {
                        $originData[$keyDiff] = $resultNCMTest[$originData[$keyDiff]];
                    }
                    $valueDiff = $resultNCMTest[$valueDiff];
                } elseif ($keyDiff == 'ncm_requester') {
                    $ncmRequesterOld = Employee::where('id', $originData[$keyDiff])->first();
                    $originData[$keyDiff] = 'NULL';
                    if ($ncmRequesterOld) {
                        $originData[$keyDiff] = $ncmRequesterOld['name'] . ' (' . GeneralProject::getNickNameNormal($ncmRequesterOld['email']) . ')';
                    }
                    $ncmRequesterNew = Employee::where('id', $valueDiff)->first();
                    $valueDiff = $ncmRequesterNew['name'] . ' (' . GeneralProject::getNickNameNormal($ncmRequesterNew['email']) . ')';
                } elseif ($keyDiff == 'task_assign_depart_represent' || $keyDiff == 'task_assign_tester' || $keyDiff == 'task_assign_evaluater') {
                    $ncmTaskAssignOld = Employee::where('id', $originData[$keyDiff])->first();
                    $originData[$keyDiff] = 'NULL';
                    if ($ncmTaskAssignOld) {
                        $originData[$keyDiff] = $ncmTaskAssignOld['name'] . ' (' . GeneralProject::getNickNameNormal($ncmTaskAssignOld['email']) . ')';
                    }
                    $ncmTaskAssignNew = Employee::where('id', $valueDiff)->first();
                    $valueDiff = $ncmTaskAssignNew['name'] . ' (' . GeneralProject::getNickNameNormal($ncmTaskAssignNew['email']) . ')';
                } else {}
                $textChanged .= '- ';
                $textChanged .= sprintf($formatHistory[$keyDiff], 
                        $originData[$keyDiff], $valueDiff);
                $textChanged .= PHP_EOL;
            }
        } else {
            if (!isset($formatHistory[$option['text_change_custom']])) {
                return true;
            }
            $textChanged = sprintf(
                    $formatHistory[$option['text_change_custom']],
                    $originData[$option['text_change_custom']],
                    $changedData[$option['text_change_custom']]);
        }
        if (!$textChanged) {
            return true;
        }
        $item = new self();
        $item->setData([
            'task_id' => $task->id,
            'content' => $textChanged,
        ])->save();
        return $item;
    }
    
    /**
     * format text history task
     * 
     * @param type $key
     * @return string
     */
    public static function formatHistory($key = null)
    {
        $format = [
            'task_assigns' => 'Assigned: %s -> %s',
            'task_participants' => 'Participants: %s -> %s',
            'status' => 'Changed status: %s -> %s',
            'priority' => 'Changed priority: %s -> %s',
            'title' => 'Changed title: %s -> %s',
            'content' => 'Changed content: %s -> %s',
            'solution' => 'Changed solution: %s -> %s',
            'duedate' => 'Changed due date: %s -> %s',
            'actual_date' => 'Changed actual date: %s -> %s',
            'created_at' => 'Changed created at: %s -> %s',
            'type' => 'Changed type: %s -> %s',
            // add ncm data
            'ncm_request_standard' => 'Changed request standard: %s -> %s',
            'ncm_document' => 'Changed document: %s -> %s',
            'ncm_fix_reason' => 'Changed reason: %s -> %s',
            'ncm_fix_content' => 'Changed fix content: %s -> %s',
            'ncm_next_measure' => 'Changed next measure: %s -> %s',
            'ncm_evaluate_effect' => 'Changed evaluate effect: %s -> %s',
            'ncm_evaluate_date' => 'Changed evaluate date: %s -> %s',
            'ncm_request_date' => 'Changed request date: %s -> %s',
            'ncm_requester' => 'Changed requester: %s -> %s',
            'ncm_test_result' => 'Changed test result: %s -> %s',
            'task_assign_depart_represent' => 'Changed  depart represent: %s -> %s',
            'task_assign_tester' => 'Changed task assign tester: %s -> %s',
            'task_assign_evaluater' => 'Changed task assign evaluater: %s -> %s',
            'task_team' => 'Changed team: %s -> %s',
            'new_created_user' => 'Create new: %s -> %s',
            'new_created_time' => 'Time Create: %s -> %s',
            'create_new' => 'Created New item',

            'wo_submit_custom' => 'Submitted workorder',
            'wo_submit_reivewed' => 'Reviewed workorder',
            'wo_submit_approved' => 'Approved workorder',
            'wo_submit_feedback' => 'Feedback workorder',
            'wo_submit_undo_feedback' => 'Undo feedback workorder',
            'wo_change_approver' => 'Change approver: %s -> %s',
            'wo_remove_reviewer' => 'Remove reviewer: %s',
            'wo_add_reviewer' => 'Add reviewer: %s',
            'reward_submit' => 'Submitted reward',
            'reward_confirm' => 'Verified reward',
            'reward_approve' => 'Confirmed reward',
            'reward_feedback' => 'Feedback reward',
            'reward_feedback_reason' => 'Feedback reward: %s',
            'reward_edit_number' => 'Change number: %s',
        ];
        if (isset($format[$key])) {
            return $format[$key];
        }
        return $format;
    }
}
