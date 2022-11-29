<?php

namespace Rikkei\Project\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Project\View\View;

class CMPlan extends ProjectWOBase
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_cm_plans';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['content', 'project_id'];
    
    /**
     * get all cm plan by project id
     * @param int
     * @return collection
     */
    public static function getAllCMPlan($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $item;
        }
        $item = self::select(['id', 'content'])->where('project_id', $projectId)->get();
        CacheHelper::put(self::KEY_CACHE_WO, $item, $projectId);
        return $item;
    }

    /**
     * inser cm plan
     * @param array
     * @return boolean
     *
     */
    public static function insertCMPlan($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_CM_PLAN];
        if (isset($input['isDelete'])) {
            $status = self::STATUS_DELETE;
            $cm = self::find($input['id']);
            if ($cm->delete()) {
                CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                $statusText = $arrayLablelStatus[$status];
                View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                return true;
            }
            return false;
        } else {
            if (isset($input['isAddNew'])) {
                $status = self::STATUS_ADD;
                $cm = new CMPlan;
                $cm->fill($input);

                if ($cm->save()) {
                    $statusText = $arrayLablelStatus[$status];
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                    CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                    return $cm->id;
                }
                return false;
            } else if(isset($input['isEdit'])) {
                $cm = self::find($input['id']);
                $cm->fill($input);
                $status = self::STATUS_EDIT;
                $cmAttributes = $cm->attributes;
                $cmOrigin = $cm->original;
                if ($cm->save()) {
                    $statusText = $arrayLablelStatus[$status];
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $cmAttributes, $cmOrigin);
                    CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                    return true;
                }
                return false;
            }
        }
    }

    /**
     * get conten table after submit
     * @param array
     * @return string
     */
    public static function getContentTable($project)
    {
        $permission = View::checkPermissionEditWorkorder($project);
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        $checkEditWorkOrder = Task::checkEditWorkOrder($project->id);
        $cmPlans = self::getAllCMPlan($project->id);
        $sourceServer = SourceServer::getSourceServer($project->id);
        return view('project::components.cm-plan', ['project' => $project, 'checkEditWorkOrder' => $checkEditWorkOrder, 'cmPlans' => $cmPlans, 'detail' => true, 'permissionEdit' => $permissionEdit, 'sourceServer' => $sourceServer])->render();
    }

}