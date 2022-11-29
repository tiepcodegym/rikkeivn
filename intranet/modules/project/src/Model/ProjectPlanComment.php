<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Collection;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;

class ProjectPlanComment extends CoreModel
{
    protected $table = 'project_plan_comment';

    public static function saveData($data)
    {
        $projectPlanComment = new ProjectPlanComment();
        $projectPlanComment->project_id = $data['project_id'];
        $projectPlanComment->content = $data['content'];
        $projectPlanComment->created_by = auth()->id();
        $projectPlanComment->save();
    }

    public static function getCommentData($projectId)
    {
        $pager = Config::getPagerDataQuery();
        $collection = self::where('project_id', $projectId)
                        ->orderBy('created_at', 'desc');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    public static function getNewComment($projectId)
    {
        $collection = self::where('project_id', $projectId)
            ->orderBy('created_at', 'desc')->first();
        return $collection;
    }

    /**
     * get employees list
     */
    public function getListEmployeeInfo($empIds)
    {
        // get all to show comment
        $collection = Employee::whereIn('id', $empIds)
            ->select(['id', 'name', 'email'])
            ->get();
        $employees = [];
        foreach ($collection as $item) {
            $employees[$item->id] = $item;
        }
        return $employees;
    }

    /**
     * processing data before render view
     *
     * @param Collection|array $collectionModel
     * @return Collection
     */
    public function processingBeforeRender($collectionModel)
    {
        if (count($collectionModel) > 0) {
            $commenterIds = [];
            // get the list of commenter id
            foreach ($collectionModel as $item) {
                if (!isset($commenterIds[$item->created_by])) {
                    $commenterIds[$item->created_by] = $item->created_by;
                }
            }
            $employees = $this->getListEmployeeInfo($commenterIds);
            foreach ($collectionModel as $item) {
                if (isset($employees[$item->created_by])) {
                    $item->name = $employees[$item->created_by]->name;
                    $item->email = $employees[$item->created_by]->email;
                } else {
                    $item->name = 'unknown';
                    $item->email = 'unknown';
                }
            }
        }
        return $collectionModel;
    }
}