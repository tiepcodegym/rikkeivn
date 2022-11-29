<?php

namespace Rikkei\Project\Model;

use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class ProjectPlanResource extends CoreModel
{
    protected $table = 'project_plan_resource';
    const ATTACH_FOLDER = '/public/project/plan/';
    const URL_FOLDER = 'project/plan/';

    public static function saveData($data)
    {
        $projectPlanResource = new ProjectPlanResource();
        $projectPlanResource->project_id = $data['project_id'];
        $projectPlanResource->file_url = $data['file_url'];
        $projectPlanResource->created_by = $data['created_by'];
        $projectPlanResource->file_name = $data['file_name'];
        $projectPlanResource->save();
    }

    public static function getProjectPlanResource($projectId)
    {
        return self::where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function getNewProjectPlanResource($projectId, $urlPath)
    {
        $collection = self::where('project_id', $projectId)
            ->where('file_url', $urlPath)
            ->orderBy('created_at', 'desc')->first();
        return $collection;
    }
}