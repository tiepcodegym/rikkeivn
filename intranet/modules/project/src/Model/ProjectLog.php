<?php

namespace Rikkei\Project\Model;

use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form;
use Rikkei\Team\View\Config;

class ProjectLog extends ProjectWOBase
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['project_id', 'content'];

    public $timestamps  = false;

    /*
     * get all project log by project id
     * @param int
     * @return collection
     */
    public static function getAllProjectLog($projectId)
    {
        $pager = Config::getPagerDataQuery();
        $collection = self::select(['id', 'project_id', 'content', 'author', 'created_at'])
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * insert project log
     * 
     * @param int $projectId
     * @param string $content
     * @param string $nameCreated
     */
    public static function insertProjectLog($projectId, $content, $nameCreated)
    {
        $projectLogs = new ProjectLog;
        $projectLogs->project_id = $projectId;
        $projectLogs->content = $content;
        $projectLogs->author = $nameCreated;
        $projectLogs->created_at = date('Y-m-d H:i:s');
        $projectLogs->save();
    }
    
    /**
     * inser log type quality plan
     * 
     * @param object $task
     */
    public static function insertLogTaskQualityPlan($task)
    {
        $content = self::getLabelStatusForProjectLog();
        if (!isset($content[self::STATUS_CREATE_QUALITY_PLAN])) {
            return;
        }
        $content = $content[self::STATUS_CREATE_QUALITY_PLAN] . ': ' .
                $task->title;
        $author = Permission::getInstance()->getEmployee()->email;
        return self::insertProjectLog($task->project_id, $content, $author);
    }
}