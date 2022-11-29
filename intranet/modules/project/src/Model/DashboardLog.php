<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\ProjDeliverable;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\Config;
use DateTime;

class DashboardLog extends CoreModel
{
    protected $table = 'dashboard_logs';
    protected $fillable = ['project_id', 'content', 'author_name'];

    /**
     * get log fields and labels
     * @return type
     */
    public static function logFields() {
        return [
            'cost_plan_effort_current' => trans('project::view.Plan Effort - current'), 
            'cost_actual_effort' => trans('project::view.Actual Effort'), 
            'qua_leakage_errors' => trans('project::view.Leakage'),
            'qua_defect_errors' => trans('project::view.Defect rate'),
            'qua_defect_reward_errors' => trans('project::view.IT/ST Defects'),
            'tl_schedule' => trans('project::view.Late Schedule'), 
            'css_css' => trans('project::view.Customer satisfactions'),
        ];
    }
    
    /**
     * get deliverable fields
     * @return type
     */
    public static function deliverableFields() {
        return [
            'actual_date' => trans('project::view.Actual date'),
        ];
    }


    /**
     * get all dashboard logs by projectId
     * @param type $projectId
     * @param bool $getAll
     * @return type
     */
    public static function getAllLogs($projectId, $getAll = false)
    {
        $pager = Config::getPagerDataQuery();
        $collection = self::select(['id', 'project_id', 'content', 'author_name as author', 'created_at'])
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc');
        if ($getAll) {
            return $collection->get();
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * insert logs
     * @param type $projectPoint
     * @param type $oldData
     * @return type
     */
    public static function insertLog($projectPoint, $oldData) {
        if (!is_object($projectPoint)) {
            $projectPoint = ProjectPoint::findFromProject($projectPoint);
        }
        if (!$projectPoint) {
            return;
        }
        $logFields = self::logFields();
        $content = '';
        foreach ($logFields as $key => $label) {
            if ($projectPoint->{$key} !== $oldData[$key]) {
                $content .= 'Changed ' . $label . ': ' . 
                    ($oldData[$key] === null ? 'NULL' : $oldData[$key]) . ' => ' . 
                    ($projectPoint->{$key} === null ? 'NULL' : $projectPoint->{$key}) . 
                    PHP_EOL;
            }
        }
        if ($content) {
            $author = Permission::getInstance()->getEmployee();
            self::create([
                'project_id' => $projectPoint->project_id,
                'content' => trim($content),
                'author_name' => $author->email
            ]);
        }
    }
    
    /**
     * insert deliverable logs
     * @param type $deliverable
     * @param type $oldData
     * @return type
     */
    public static function insertDeliverableLog($deliverable, $oldData) {
        if (!is_object($deliverable)) {
            $deliverable = ProjDeliverable::find($deliverable);
        }
        if (!$deliverable) {
            return;
        }
        $logFields = self::deliverableFields();
        $content = '';
        foreach ($logFields as $key => $label) {
            if ($deliverable->{$key} != $oldData[$key]) {
                $content .= 'Changed deliver "'.$deliverable->title.'" - ' . 
                    $label . ': ' . ($oldData[$key] ? $oldData[$key] : 'NULL') . ' => ' . 
                    ($deliverable->{$key} ? $deliverable->{$key} : 'NULL') . PHP_EOL;
            }
        }
        if ($content) {
            $author = Permission::getInstance()->getEmployee();
            self::create([
                'project_id' => $deliverable->project_id,
                'content' => trim($content),
                'author_name' => $author->email
            ]);
        }
    }
    
    /**
     * insert raise logs
     * @param object $projectPoint
     * @param int $oldRaise
     * @return type
     */
    public static function insertLogRaise($projectPoint, $oldRaise, $baseline = null) {
        if (!$projectPoint) {
            return;
        }
        $content = '';
        if ($projectPoint->raise !== $oldRaise) {
            if ($projectPoint->raise ==  ProjectPoint::RAISE_UP) {
                if ($baseline) {
                    $dateCreated = new DateTime($projectPoint->created_at);
                    $content .= trans('project::view.Inserted Raise at baseline', 
                            ['week' => $dateCreated->format("W")]) . PHP_EOL;
                } else {
                    $content .= trans('project::view.Inserted Raise') . PHP_EOL;
                }
            } else {
                if ($baseline) {
                    $dateCreated = new DateTime($projectPoint->created_at);
                    $content .= trans('project::view.Destroyed Raise at baseline', 
                            ['week' => $dateCreated->format("W")]) . PHP_EOL;
                } else {
                    $content .= trans('project::view.Destroyed Raise') . PHP_EOL;
                }
            }
        }
        if ($content) {
            $author = Permission::getInstance()->getEmployee();
            self::create([
                'project_id' => $projectPoint->project_id,
                'content' => trim($content),
                'author_name' => $author->email
            ]);
        }
    }
}
