<?php

namespace Rikkei\Statistic\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Team\Model\Team;
use Rikkei\Statistic\Helpers\STProjConst;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class STProjPoint extends CoreModel
{
    protected $table = 'st_proj_points';
    public $timestamps = false;

    protected static function checkProjOpen($date, $type, $state)
    {
        $tblProj = Project::getTableName();
        $tblTeamProj = TeamProject::getTableName();
        $tblTeam = Team::getTableName();
        $dateFormat = $date->format('Y-m-d');
        $collection = TeamProject::select([$tblTeamProj.'.team_id', $tblTeamProj.'.project_id'])
            ->join($tblProj . ' as t_proj', function ($join) use ($tblTeamProj, $state, $type, $dateFormat) {
                $join->on('t_proj.id', '=', $tblTeamProj . '.project_id')
                    ->whereNull('t_proj.deleted_at')
                    ->where('t_proj.state', '=', $state)
                    ->where('t_proj.status', '=', Project::STATUS_APPROVED);
                if ($type == STProjConst::TYPE_CLOSE) { // close project
                    $join->where('t_proj.end_at', '=', $dateFormat);
                }
            })
            ->join($tblTeam . ' as t_team', function ($join) use ($tblTeamProj) {
                $join->on('t_team.id', '=', $tblTeamProj . '.team_id')
                    ->whereNull('t_team.deleted_at');
            })
            ->get();
        // delete data in date
        self::where('type', $type)
            ->whereDate('created_at', '=', $dateFormat)
            ->delete();
        if (!count($collection)) {
            return false;
        }
        $teamData = [];
        foreach ($collection as $item) {
            $teamId = $item->team_id;
            if (!isset($teamData[$teamId])) {
                $teamData[$teamId] = [
                    'created_at' => $dateFormat,
                    'type' => $type,
                    'value' => 0,
                    'team_id' => $teamId,
                    'detail' => '',
                ];
            }
            $teamData[$teamId]['value']++;
            $teamData[$teamId]['detail'] .= $item->project_id . '-';
        }
        self::insert($teamData);
    }

    /**
     * store history data project status
     *
     * @param datetime $date
     */
    public static function storeProjOpen($date = null)
    {
        if (!$date) {
            $date = Carbon::now();
        }
        self::checkProjOpen($date, STProjConst::TYPE_PROCESS, Project::STATE_PROCESSING);
        self::checkProjOpen($date, STProjConst::TYPE_NEW, Project::STATE_NEW);
        self::checkProjOpen($date, STProjConst::TYPE_CLOSE, Project::STATE_CLOSED);
    }
}
