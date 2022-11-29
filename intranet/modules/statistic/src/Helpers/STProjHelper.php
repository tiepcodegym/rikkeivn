<?php

namespace Rikkei\Statistic\Helpers;

use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\SourceServer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class STProjHelper
{
    public static function processAllSTProj($nowDate = null)
    {
        if (!$nowDate) {
            $nowDate = Carbon::now();
        }
        $projects = self::getAllProject($nowDate);
        try {
            STProjBugHelper::getInstance()->processBugAll($projects, $nowDate);
        } catch (Exception $ex) {
            Log::error($ex);
        }
        try {
            STProjLocHelper::getInstance()->processLocAll($projects, $nowDate);
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    /**
     * process count loc project
     */
    public static function getAllProject($nowDate = null)
    {
        if (!$nowDate) {
            $nowDate = Carbon::now();
        }
        $date = $nowDate->format('Y-m-d');
        $tblProj = Project::getTableName();
        $tblSourceServer = SourceServer::getTableName();
        $projects = Project::select(['t_ss.project_id', 't_ss.id_redmine', 't_ss.id_git',
            't_ss.id_redmine_external', 't_ss.id_git_external', 't_ss.is_check_redmine',
            't_ss.is_check_git'])
            ->join($tblSourceServer . ' as t_ss', function ($join) use ($tblProj) {
                $join->on($tblProj . '.id', '=', 't_ss.project_id')
                    ->whereNull('t_ss.deleted_at');
            })
            ->where($tblProj.'.status', Project::STATUS_APPROVED)
            ->where(function ($query) use ($tblProj, $date) {
                $query->orwhere($tblProj.'.state', '=', Project::STATE_PROCESSING)
                    ->orwhere(function ($query2) use ($tblProj, $date) {
                        $query2->where($tblProj.'.state', '=', Project::STATE_CLOSED)
                            ->whereDate($tblProj.'.end_at', '=', $date);
                    });
            })
            ->get();
        if (!count($projects)) {
            return false;
        }
        $result = [];
        foreach ($projects as $project) {
            $result[$project->project_id] = $project;
        }
        return $result;
    }
}
