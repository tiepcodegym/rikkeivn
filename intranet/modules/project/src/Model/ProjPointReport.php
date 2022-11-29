<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\View\View;
use Exception;
use Carbon\Carbon;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View as CoreView;

class ProjPointReport extends CoreModel
{
    
    const POINT_YES = 3;
    const POINT_DELAY = 2;
    const POINT_NO = 1;
    const POINT_NULL = 0;
    
    const KEY_CACHE = 'proj_point_report';
    
    protected $table = 'proj_point_reports';
    
    /**
     * report all project follow week
     */
    public static function reportAllProject($isUpdateFlat = true)
    {
        list ($now, $firstWeek, $lastWeek) = View::getFirstLastDayOfWeek();
        $ontimeWeek = View::getDateProjectOntime($now);
        if (!$ontimeWeek) {
            return null;
        }
        $nowClone = clone $now;
        $nowClone->modify('-1 days');
        // project process, start_at < now < end_at
        $projects = Project::select('id', 'type')
            ->where('state', Project::STATE_PROCESSING)
            ->where('status', Project::STATUS_APPROVED)
            ->whereDate('end_at', '>', $nowClone->format('Y-m-d'))
            ->whereDate('start_at', '<', $nowClone->modify('+2 days')->format('Y-m-d'))
            ->get();
        if (!count($projects)) {
            return null;
        }
        foreach ($projects as $project) {
            self::reportItem([
                $project,
                $now,
                $firstWeek,
                $lastWeek,
                $ontimeWeek,
                null,
                $isUpdateFlat
            ]);
        }
        return true;
    }
    
    /**
     * repport item project
     * 
     * @param object|int $project
     * @param datetime $now
     * @param datetime $firstWeek
     * @param datetime $lastWeek
     * @param datetime $ontimeWeek
     * @return object
     * @throws Exception
     */
    public static function reportItem($arrayOptions = [], $reportCron = true) {
        //init parameter
        if (!$arrayOptions) {
            $arrayOptions = [null, null, null, null, null, null, true];
        }
        list($project, $now, $firstWeek, $lastWeek, $ontimeWeek, $projectPoint, $isUpdateFlat) = $arrayOptions;
        if (is_numeric($project)) {
            $project = Project::find($project);
        }
        if (!$project) {
            return null;
        }
        if (!$now || !$firstWeek || !$lastWeek) {
            list ($now, $firstWeek, $lastWeek) = View::getFirstLastDayOfWeek();
        }
        if (!$ontimeWeek) {
            $ontimeWeek = View::getDateProjectOntime($now);
        }
        //find report in week
        $reportItem = self::where('project_id', $project->id)
            ->whereDate('created_at', '>=', $firstWeek->format('Y-m-d H:i:s'))
            ->whereDate('created_at', '<=', $lastWeek->format('Y-m-d H:i:s'))
            ->first();
        if (!$projectPoint) {
            $projectPoint = ProjectPoint::findFromProject($project->id);
        }
        if (!$reportItem) {
            $reportItem = new self();
            $reportItem->setData([
                'project_id' => $project->id,
                'changed_by' => $projectPoint->changed_by,
                'point' => self::POINT_NULL,
                'note' => null,
                'changed_at' => null,
                'last_report' => null
            ]);
        } else {
            if ($reportItem->changed_by == null) {
                $reportItem->changed_by = $projectPoint->changed_by;
            }
        }
        // now > 15hfriday => check
        $diffOntimeNow = $ontimeWeek->diff($now);
        if ($diffOntimeNow->invert == 0 || !$reportCron) {
            if ($reportItem->changed_at == null && $projectPoint->report_last_at) {
                $updatedAtLast = Carbon::parse($projectPoint->report_last_at);
                $diffFirst = $updatedAtLast->diff($firstWeek);
                $diffLast = $updatedAtLast->diff($lastWeek);
                // report in week
                if (($diffFirst->invert == 1 || $diffFirst->days == 0) &&
                    ($diffLast->invert == 0) &&
                    $projectPoint->report_last_at
                ) {
                    //report in ontime
                    if ($updatedAtLast->diff($ontimeWeek)->invert == 0) {
                        $reportItem->point = self::POINT_YES;
                    } else { // report delayed
                        $reportItem->point = self::POINT_DELAY;
                    }
                    $reportItem->changed_at = $updatedAtLast->format('Y-m-d H:i:s');
                } else { //not report in week
                    $reportItem->point = self::POINT_NO;
                }
            } elseif ($reportItem->changed_at && $projectPoint->report_last_at) {
                $reportItem->last_report = $projectPoint->report_last_at;
                $updatedAtLast = Carbon::parse($reportItem->changed_at);
                if ($updatedAtLast->diff($ontimeWeek)->invert == 0) {
                    $reportItem->point = self::POINT_YES;
                } else { // report delayed
                    $reportItem->point = self::POINT_DELAY;
                }
            } elseif (!$projectPoint->report_last_at) {
                $reportItem->point = self::POINT_NO;
            } else {
                // nothing
            }
        }
        try {
            $reportItem->save([], [
                'project' => $project,
                'is_update_flat' => $isUpdateFlat
            ]);
            return $reportItem;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * get point of item
     * 
     * @return int
     */
    public function getPoint()
    {
        switch ($this->point) {
            case self::POINT_YES:
                return self::POINT_YES;
            case self::POINT_DELAY:
                return self::POINT_DELAY;
            case self::POINT_NO:
                return self::POINT_NO;
            default:
                return self::POINT_NULL;
        }
    }
    
    /**
     * get label of point
     * 
     * @return array
     */
    public static function pointLabel()
    {
        return [
            self::POINT_NULL => '',
            self::POINT_NO => 'No',
            self::POINT_DELAY => 'Delayed',
            self::POINT_YES => 'Yes',
        ];
    }
    
    /**
     * get list report
     * 
     * @param int $projectId
     * @param bool $getAll
     * @return object
     */
    public static function getList($projectId, $getAll = false)
    {
        $pager = Config::getPagerDataQuery();
        $collection = self::select('id', 'point', 'changed_at', 'created_at' , 'last_report')
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc');
        if ($getAll) {
            return $collection->get();
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * get list report
     *
     * @param int $projectId
     * @return object
     */
    public static function getCountSplit($projectId)
    {
        if ($collection = CacheHelper::get(self::KEY_CACHE, $projectId)) {
            return $collection;
        }
        $collection = self::select('point')
            ->where('project_id', $projectId)
            ->whereIn('point', [self::POINT_NO, self::POINT_DELAY, self::POINT_YES])
            ->orderBy('created_at', 'ASC')
            ->get();
        $result = [
            self::POINT_NO => 0,
            self::POINT_DELAY => 0,
            self::POINT_YES => 0,
            'point' => 0,
        ];
        if (!$collection) {
            return $result;
        }
        foreach ($collection as $item) {
            $result[$item->point]++;
            if ($item->point == self::POINT_NO) {
                $result['point'] -= 1;
            } elseif ($item->point == self::POINT_DELAY) {
                $result['point'] -= 0.5;
            } elseif ($item->point == self::POINT_YES) { // point yes
                $result['point'] += 0.5;
            } else {
                // nothing
            }
            if ($result['point'] > 2) {
                $result['point'] = 2;
            } elseif ($result['point'] < -2) {
                $result['point'] = -2;
            } else {
                // nothing
            }
        }
        CacheHelper::put(self::KEY_CACHE, $result, $projectId);
        return $result;
    }
    
    /**
     * overwrite save model
     * 
     * @param array $options
     */
    public function save(array $options = array(), $project = null) {
        try {
            $result = parent::save($options);
            CacheHelper::forget(self::KEY_CACHE, $this->project_id);
            CacheHelper::forget(ProjectPoint::KEY_CACHE, $this->project_id);
            if (is_array($project)) {
                $project = $project['project'];
            }
            if (!$project) {
                $project = Project::find($this->project_id);
            }
            $project->refreshFlatDataPoint();
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * report all project follow week
     */
    public static function checkReportInWeek()
    {
        list ($now, $firstWeek, $lastWeek) = View::getFirstLastDayOfWeek();
        $timeCheckWeek = View::getDateProjectOntime($now, CoreConfigData::getProjectCheckReportInWeek());
        if (!$timeCheckWeek) {
            return null;
        }
        $nowClone = clone $now;
        $nowClone->modify('-1 days');
        $projects = Project::select('id', 'created_at', 'end_at')
            ->where('state', Project::STATE_PROCESSING)
            ->where('status', Project::STATUS_APPROVED)
            ->whereIn('type', [Project::TYPE_BASE, Project::TYPE_OSDC])
            ->whereDate('end_at', '>', $nowClone->format('Y-m-d'))
            ->whereDate('start_at', '<', $nowClone->modify('+2 days')->format('Y-m-d'))
            ->get();
        if (!count($projects)) {
            return null;
        }
        foreach ($projects as $project) {
            self::checkReportItemInWeek($project, $now, $firstWeek, $lastWeek, $timeCheckWeek);
        }
        return true;
    }
    
    /**
     * repport item project
     * 
     * @param object|int $project
     * @param datetime $now
     * @param datetime $firstWeek
     * @param datetime $lastWeek
     * @param datetime $timeCheckWeek
     * @return object
     * @throws Exception
     */
    public static function checkReportItemInWeek(
            $project, 
            $now = null,
            $firstWeek = null, 
            $lastWeek = null,
            $timeCheckWeek = null
    ) {
        if (is_numeric($project)) {
            $project = Project::find($project);
        }
        if (!$project) {
            return null;
        }
        if (!$now || !$firstWeek || !$lastWeek) {
            list ($now, $firstWeek, $lastWeek) = View::getFirstLastDayOfWeek();
        }
        if (!$timeCheckWeek) {
            $timeCheckWeek = View::getDateProjectOntime($now, CoreConfigData::getProjectCheckReportInWeek());
        }
        //check report last week
        $diffNowTimeCheck = $now->diff($timeCheckWeek);
        $numberInWeekYear = $now->format("W");
        $year = $now->format('Y');
        $nowClone = clone $now;
        if ($diffNowTimeCheck->invert == 0) { //before check in week => check last week
            // check last week but created at in this week => not check
            $createdAt = Carbon::parse($project->created_at);
            $mondayCreatedAt = CoreView::getDateLastWeek($createdAt)->setTime(0,0,0);
            $mondayNow = CoreView::getDateLastWeek($now)->setTime(0,0,0);
            if ($mondayCreatedAt != $mondayNow) {
                $nowClone->setISODate($year, $numberInWeekYear - 1, 1);
                $from = clone $nowClone;
                $nowClone->setISODate($year, $numberInWeekYear - 1, 7);
                $to = clone $nowClone;
                return self::checkReportItemFolowtime($project, $from, $to, 
                    $firstWeek, $lastWeek);
            }
            return false;
        } else { // after check in week => check from $timeCheckWeek => sunday
            $from = $firstWeek;
            $to = $lastWeek;
            return self::checkReportItemFolowtime($project, $from, $to, 
                $firstWeek, $lastWeek);
        }
    }
    
    /**
     * check report item follow time From => To
     * 
     * @param object $project
     * @param Datetime $from
     * @param Datetime $to
     * @param Datetime $firstWeek
     * @param Datetime $lastWeek
     * @return boolean|object
     */
    public static function checkReportItemFolowtime(
            $project, 
            $from, 
            $to, 
            $firstWeek, 
            $lastWeek
    ) {
        // check project over end date
        $endDate = $project->end_at;
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }
        $diffEnddateWithNow = $endDate->diff(Carbon::now());
        // project over end date but not close
        if ($diffEnddateWithNow->invert == 0 &&
            $diffEnddateWithNow->days >= 1
        ) {
            $projectPointFlat = ProjPointFlat::findFlatFromProject($project->id);
            /*$projectPointFlat->setData([
                'summary' => ProjectPoint::COLOR_STATUS_GREY,
                'cost' => ProjectPoint::COLOR_STATUS_GREY,
                'quality' => ProjectPoint::COLOR_STATUS_GREY,
                'tl' => ProjectPoint::COLOR_STATUS_GREY,
                'proc' => ProjectPoint::COLOR_STATUS_GREY,
                'css' => ProjectPoint::COLOR_STATUS_GREY,
            ]);
            $projectPointFlat->save();*/
            return $projectPointFlat;
        }
        
        $projectPoint = ProjectPoint::findFromProject($project->id);
        
        $createdAt = Carbon::parse($projectPoint->report_last_at);
        //report in week
        if ($projectPoint->report_last_at &&
            (($createdAt->diff($from)->invert == 1 &&
            $createdAt->diff($to)->invert == 0) ||
            ($createdAt->diff($firstWeek)->invert == 1 &&
            $createdAt->diff($lastWeek)->invert == 0))
        ) {
            return true;
        }
        $projectPointFlat = ProjPointFlat::findFlatFromProject($project->id);
        $projectPointFlat->setData([
            'summary' => ProjectPoint::COLOR_STATUS_WHITE,
            'cost' => ProjectPoint::COLOR_STATUS_WHITE,
            'quality' => ProjectPoint::COLOR_STATUS_WHITE,
            'tl' => ProjectPoint::COLOR_STATUS_WHITE,
            'proc' => ProjectPoint::COLOR_STATUS_WHITE,
            'css' => ProjectPoint::COLOR_STATUS_WHITE,
        ]);
        $projectPointFlat->save();
        return $projectPointFlat;
    }
}
