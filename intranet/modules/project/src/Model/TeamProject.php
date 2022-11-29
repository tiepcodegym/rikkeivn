<?php

namespace Rikkei\Project\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;
class TeamProject extends CoreModel
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'team_projs';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * get team ids of project
     *
     * @param int $projectId
     * @return array
     */
    public static function getTeamIds($projectId)
    {
        $collection = self::where('project_id', $projectId)
            ->select('team_id')
            ->orderBy('team_id')
            ->get();
        $result = [];
        if (!count($collection)) {
            return $result;
        }
        foreach ($collection as $item) {
            $result[] = $item->team_id;
        }
        return $result;
    }

    /**
     * get team ids of project
     *
     * @param int $projectId
     * @return array
     */
    public static function getProjTeamIdJoin($projectId)
    {
        $tblProjTeam = self::getTableName();
        $tblTeam = Team::getTableName();
        $collection = DB::table($tblProjTeam . ' AS t_pt')
            ->select('t_pt.team_id')
            ->join($tblTeam . ' AS t_t', function ($join) {
                $join->on('t_t.id', '=', 't_pt.team_id')
                    ->whereNull('t_t.deleted_at');
            })
            ->where('t_pt.project_id', '=', $projectId)
            ->whereNull('t_pt.deleted_at')
            ->orderBy('team_id', 'asc')
            ->get();
        $result = '';
        if (!count($collection)) {
            return $result;
        }
        foreach ($collection as $item) {
            $result .= $item->team_id . '-';
        }
        return substr($result, 0, -1);
    }
}

