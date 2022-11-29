<?php
namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;

class TaskAction extends CoreModel
{
    protected $table = 'task_actions';
    const TYPE_ISSUE_MITIGATION = 2;
    protected $fillable = ['content', 'assignee', 'issue_id', 'status', 'type', 'duedate'];
    protected $dates = ['duedate'];

    public static function delByIssue($issueId)
    {
        self::where('issue_id', $issueId)->delete();
    }

    public static function getByType($type, $issueId)
    {
        return self::join('employees', 'employees.id', '=', 'task_actions.assignee')
            ->where('type', $type)
            ->where('issue_id', $issueId)
            ->select([
                'task_actions.*',
                'employees.name as employee_name',
            ])
            ->get();
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}