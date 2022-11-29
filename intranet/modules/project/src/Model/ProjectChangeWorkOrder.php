<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Project\View\View as ViewHelper;

class ProjectChangeWorkOrder extends CoreModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_change_wos';

    /* version workorder default*/
    const VERSION_WORKORDER_DEFAULT = 1.0;
    
    const KEY_CACHE_CHANGE_WO = 'change_workorder';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['project_id', 'version', 'change',
                            'reason'];

    /**
     * get Version Lastest of wo
     * @param  int
     * @return string
     */
    public static function getVersionLastest($projectId, $nextVersion = false)
    {
        $count = Task::select(DB::raw('count(*) as count'))
            ->where('project_id', $projectId)
            ->where('type', Task::TYPE_WO)
            ->where('status', Task::STATUS_APPROVED)
            ->first();
        if ($count && $count->count) {
            $version = round($count->count / 10, 1);
            if ($nextVersion === true) { // view next version
                return '' . ($version + 1);
            } else if ($nextVersion === null) { // view prev version
                return '' . ($version + 0.9);
            } else {
                return '' . ($version + 1); // view current version
            }
        }
        return '1.0';
    }

    /**
     * insert version workorder
     * @param int
     */
    public static function insertVersionWorkorder($projectId)
    {
        $version = new ProjectChangeWorkOrder;
        $version->change = Task::getIdTaskChangeWoByProjectId($projectId);
        $version->project_id = $projectId;
        $version->version = self::getVersionLastest($projectId, null);
        $version->save();
        CacheHelper::forget(self::KEY_CACHE_CHANGE_WO, $projectId);
    }

    /**
     * get all change workorder by project id
     * @param  int
     * @return collection
     */
    public static function getAllChangeWorkorderByProjectId($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE_CHANGE_WO, $projectId)) {
            return $item;
        }
        $item = self::where('project_id', $projectId)
              ->get();
        CacheHelper::put(self::KEY_CACHE_CHANGE_WO, $item, $projectId);
        return $item;
    }

    /**
     * update reason change workorder
     * @param array
     * @param array
     * @return boolean
     */
    public static function updateReason($changeWorkorder, $input)
    {
        $changeWorkorder->fill($input);
        if ($changeWorkorder->save()) {
            CacheHelper::forget(self::KEY_CACHE_CHANGE_WO, $input['project_id']);
            return true;
        }
        return false;
    }

    /**
     * get content table after submit
     * @param array
     * @return string
     */
    public static function getContentTable($project)
    {
        $isCoo = ViewHelper::isCoo();
        $allChangeWorkorder = self::getAllChangeWorkorderByProjectId($project->id);
        return view('project::components.change-workorder', ['allChangeWorkorder' => $allChangeWorkorder,'isCoo' => $isCoo, 'detail' => true])->render();
    }

    /**
     * check has version workorder
     * @param array
     * @return int
     */
    public static function checkHasVersion($project)
    {
        return self::where('project_id', $project->id)->count() ? true : false;
    }

}