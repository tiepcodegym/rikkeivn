<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Core\View\CacheHelper;

class ProjPointFlat extends CoreModel
{
    protected $table = 'proj_point_flat';
    
    /**
     * flat all project
     */
    public static function flatAllProject()
    {

        $collection = Project::where('state', Project::STATE_PROCESSING)
            ->where('status', Project::STATUS_APPROVED)
            ->get();
        if (!count($collection)) {
            return;
        }
        foreach ($collection as $item) {
            self::flatItemProject($item);
        }
    }
    
    /**
     * flat item project
     * 
     * @param object $item
     */
    public static function flatItemProject(
        $project,
        $projectPoint = null,
        $projectPointInformation = null,
        $isChangeColor = true,
        array $optionMore = []
    ) {
        CacheHelper::forget(Project::KEY_CACHE, $project->id);
        CacheHelper::forget(ProjectPoint::KEY_CACHE, $project->id);
        $projectPoint = ProjectPoint::findFromProject($project->id);
        $projectPointInformation = ViewProject::getProjectPointInfo(
            $project, 
            $projectPoint
        );
        $projPointFlat = self::where('project_id', $project->id)->first();
        if (!$projPointFlat) {
            $projPointFlat = new self();
            $projPointFlat->project_id = $project->id;
        }
        $arrayData = ['point_total' => $projectPointInformation['point_total']];
        if (isset($optionMore['onsite_color']) && $optionMore['onsite_color']) {
            $arrayData += $optionMore['onsite_color'];
        } elseif ($isChangeColor) {
            $arrayData += [
                'summary' => $projectPointInformation['summary'],
                'cost' => $projectPointInformation['cost'],
                'quality' => $projectPointInformation['quality'],
                'tl' => $projectPointInformation['tl'],
                'proc' => $projectPointInformation['proc'],
                'css' => $projectPointInformation['css']
            ];
        } else {
            //nothing
        }
        switch ($project->type) {
            case Project::TYPE_TRAINING:
                $arrayData['point_total'] = Project::POINT_PROJECT_TYPE_TRANING;
                break;
            case Project::TYPE_RD:
                $arrayData['point_total'] = Project::POINT_PROJECT_TYPE_RD;
                break;
            case Project::TYPE_ONSITE:
                $arrayData['point_total'] = Project::POINT_PROJECT_TYPE_ONSITE;
                break;
            default:
            // nothing.
                break;
        }
        $projPointFlat->setData($arrayData)->save();
        return $projPointFlat;
    }
    
    /**
     * get flat item from project id
     * 
     * @param int $id
     * @return \self
     */
    public static function findFlatFromProject($id)
    {
        $projPointFlat = self::where('project_id', $id)->first();
        if ($projPointFlat) {
            return $projPointFlat;
        }
        $projPointFlat = new self();
        $projPointFlat->setData([
            'summary' => ProjectPoint::COLOR_STATUS_BLUE,
            'cost' => ProjectPoint::COLOR_STATUS_BLUE,
            'quality' => ProjectPoint::COLOR_STATUS_BLUE,
            'tl' => ProjectPoint::COLOR_STATUS_BLUE,
            'proc' => ProjectPoint::COLOR_STATUS_BLUE,
            'css' => ProjectPoint::COLOR_STATUS_BLUE,
            'point_total' => 0,
            'project_id' => $id
        ])->save();
        return $projPointFlat;
    }
}
