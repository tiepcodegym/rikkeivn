<?php

namespace Rikkei\Project\Model;

use Exception;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\Employee;

class TaskComment extends ProjectWOBase
{
    const TYPE_COMMENT_NORMAL = 1;
    const TYPE_COMMENT_WO = 2;
    const TYPE_COMMENT_SOURCE_SERVER = 3;
    const TYPE_COMMENT_FEEDBACK = 4;
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'task_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['content', 'task_id'];
    
    /**
     * overwrite save model
     * 
     * @param array $options
     */
    public function save(array $options = array()) {
        try {
            $this->created_by = Permission::getInstance()->getEmployee()->id;
            return parent::save($options);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * get comment of task
     * @param int
     * @return array
     */
    public static function getCommentOfTask($taskId)
    {
        $comments = self::where('task_id', $taskId)->lists('content');
        $result = '';
        if(count($comments) > 0) {
            foreach ($comments as $comment) {
                $result .= View::nl2br($comment);
                $result .= '<br />';
            }
            return $result;
        }
        return;
    }
    
    /**
     * get grid data comment of task
     */
    public static function getGridData($taskId)
    {
        $pager = Config::getPagerDataQuery();
        $tableEmployee = Employee::getTableName();
        $tableComment = self::getTableName();
        
        $collection = self::select("{$tableComment}.id",
                                "{$tableComment}.created_at",
                                'content',
                                "{$tableEmployee}.name",
                                "{$tableEmployee}.email",
                                "{$tableComment}.type"
                                )
                ->leftJoin($tableEmployee, "{$tableEmployee}.id", '=',
                    "{$tableComment}.created_by")
                ->orderBy("{$tableComment}.created_at", 'desc')
                ->where("{$tableComment}.task_id", $taskId)
                ->where(function($query) use ($tableComment) {
                    $query->orWhere($tableComment.'.type', self::TYPE_COMMENT_NORMAL)
                    ->orWhere($tableComment.'.type', self::TYPE_COMMENT_FEEDBACK)
                    ->orWhere($tableComment.'.type', self::TYPE_COMMENT_WO)
                    ->orWhereNull($tableComment.'.type');
                });
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    public static function delByTaskId($taskId)
    {
        self::where('task_id', $taskId)->delete();
    }
}
