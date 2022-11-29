<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;

class TaskWoChange extends CoreModel
{
    const FLAG_BASIC_INFO = 'base_info';
    const FLAG_STAGE = 'stage';
    const FLAG_DELIVER = 'deliver';
    const FLAG_TEAM_ALLOCATION = 'team_allocation';
    const FLAG_TRAINING_PLAN = 'train_plan';
    const FLAG_CRITICAL_DEPENDENCIES = 'critical';
    const FLAG_ASSUMPTION_CONSTRAINS = 'assumption';
    const FLAG_RISK = 'risk';
    const FLAG_EXTERNAL_INTERFACE = 'external';
    const FLAG_COMMUNICATION = 'communication';
    const FLAG_TOOL_INFRASTRUCTURE = 'tool';
    
    const FLAG_STATUS_ADD = 'add';
    const FLAG_STATUS_DELETE = 'delete';
    const FLAG_STATUS_EDIT = 'edit';
    
    const FLAG_STATUS_EDIT_OLD = 'old';
    const FLAG_STATUS_EDIT_NEW = 'new';
    
    const FLAG_TYPE_TEXT = 'type';
    const FLAG_TYPE_SINGLE = 1;
    const FLAG_TYPE_MULTI = 2;
    
    protected $table = 'task_wo_changes';
    public $timestamps = false;
    
    /**
     * get contents of changes after submit
     * 
     * @param int $taskId
     * @return object
     */
    public static function getWoChanges($taskId)
    {
        return self::select('id', 'content', 'created_by', 'created_at')
            ->where('task_id', $taskId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * get all label flag
     * 
     * @return array
     */
    public static function getAllLabelFlag()
    {
        return [
            self::FLAG_BASIC_INFO => 'Basic info',
            self::FLAG_STAGE => 'Stage',
            self::FLAG_DELIVER => 'Deliverable',
            self::FLAG_TEAM_ALLOCATION => 'Team Allocation',
            self::FLAG_TRAINING_PLAN => 'Train Plan',
            self::FLAG_CRITICAL_DEPENDENCIES => 'Critical',
            self::FLAG_ASSUMPTION_CONSTRAINS => 'Assumption',
            self::FLAG_RISK => 'Risk',
            self::FLAG_EXTERNAL_INTERFACE => 'External',
            self::FLAG_COMMUNICATION => 'Communication',
            self::FLAG_TOOL_INFRASTRUCTURE => 'Tool',
        ];
    }
    
    /**
     * get label flag
     * 
     * @param string $key
     * @param array $allFlag
     * @return string
     */
    public static function getLabelFlag($key, array $allFlag = [])
    {
        if (!$allFlag) {
            $allFlag = self::getAllLabelFlag();
        }
        if (isset($allFlag[$key])) {
            return $allFlag[$key];
        }
        return null;
    }
    
    /**
     * map between flag wo change and name tab
     * 
     * @return array
     */
    public static function getMapHashUrlTab()
    {
        return [
            self::FLAG_BASIC_INFO => Task::TYPE_WO_BASIC_INFO_PROJECT,
            self::FLAG_STAGE => Task::TYPE_WO_STAGE_MILESTONE,
            self::FLAG_DELIVER => Task::TYPE_WO_DELIVERABLE,
            self::FLAG_TEAM_ALLOCATION => Task::TYPE_WO_PROJECT_MEMBER,
            self::FLAG_TRAINING_PLAN => Task::TYPE_WO_TRANING,
        ];
    }
    
    /**
     * get name tab wo from flag type
     * 
     * @param int $flag
     * @param array $maps
     * @param array $namesTab
     * @return strig
     */
    public static function getHashUrlTab(
        $flag, 
        array $maps = [], 
        array $namesTab = []
    ) {
        if (!$maps) {
            $maps = self::getMapHashUrlTab();
        }
        if (!isset($maps[$flag])) {
            return null;
        }
        if (!$namesTab) {
            $namesTab = Task::getAllNameTabWorkorder();
        }
        return Task::getNameTabWOItem($maps[$flag], $namesTab);
    }
    
    /**
     * get content changed wo last version
     * 
     * @param object $task
     * @return string
     */
    public static function getContentChangedLast($task)
    {
        $item = self::select('content')
            ->where('task_id', $task->id)
            ->orderBy('id', 'desc')
            ->first();
        if (!$item) {
            return null;
        }
        return $item->content;
    }
}
