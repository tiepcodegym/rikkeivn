<?php

namespace Rikkei\Project\Model;
use Rikkei\Project\View\View;

class QualityPlan extends ProjectWOBase
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_qp_strategies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['content', 'task_id'];
    
    /**
     * get quality plan of task
     * @param int
     * @param array
     */
    public static function getQualityPlanOfProject($projectId, $input = null)
    {
        $quality = self::where('project_id', $projectId)->first();
        if (!$quality) {
            $quality = new QualityPlan;
            $quality->project_id = $projectId;
            $quality->save();
        }
        if ($input) {
            $quality->fill($input);
            if ($quality->save()) {
                return true;
            }
            return false;
        }
        return $quality;
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
        $qualityPlans = Task::getListTaskQuality($project->id);
        $qualityPlan = QualityPlan::getQualityPlanOfProject($project->id);
        return view('project::components.quality-plan', [
            'permissionEdit' => $permissionEdit, 
            'checkEditWorkOrder' => $checkEditWorkOrder, 
            'qualityPlans' => $qualityPlans, 
            'qualityPlan' => $qualityPlan, 
            'project' => $project, 
            'detail' => true]
        )->render();
    }
}