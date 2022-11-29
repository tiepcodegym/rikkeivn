<?php

namespace Rikkei\Statistic\Models;

use Rikkei\Core\Model\CoreModel;
use Carbon\Carbon;
use Rikkei\Project\Model\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * new addtion line of code of employee
 */
class STEmplLoc extends CoreModel
{
    protected $table = 'st_empl_loc';
    public $timestamps = false;
    protected $fillable = ['created_at', 'proj_id', 'value', 'team_id', 'empl_id'];

    /**
     * get line of code of all employee in a period
     *
     * @param Carbon $fromTime
     * @param Carbon $toTime
     * @param array $filters
     * @return \self
     */
    public static function getEmplLocPeriod($fromTime = null, $toTime = null, $filters = [])
    {
        if (!$toTime) {
            $toTime = Carbon::now();
        }
        if (!$fromTime) {
            $fromTime = clone $toTime;
            $fromTime->modify('-30 days');
        }
        $collection = self::select(['created_at', 'value', 'proj_id', 'team_id'])
            ->whereDate('created_at', '<=', $toTime->format('Y-m-d'))
            ->whereDate('created_at', '>=', $fromTime->format('Y-m-d'));
        if (isset($filters['employeeId']) && $filters['employeeId']) {
            $collection->where('empl_id', $filters['employeeId']);
        }
        if (isset($filters['team']) && $filters['team']) {
            $collection->where(function ($query) use ($filters) {
                foreach ($filters['team'] as $team) {
                    $query->orWhere('team_id', 'like', "%{$team}%");
                }
            });
        }
        return $collection->get();
    }

    /**
     * get project name
     *
     * @param array $collections array collection
     * @return collection
     */
    public static function getProjName(array $collections)
    {
        if (!$collections || !count($collections)) {
            return [];
        }
        $projIds = '';
        $arrayProjsCheck = [];
        foreach ($collections as $collection) {
            if (!$collection || !count($collection)) {
                continue;
            }
            foreach ($collection as $project) {
                if (in_array($project->proj_id, $arrayProjsCheck)) {
                    continue;
                }
                $arrayProjsCheck[] = $project->proj_id;
                $projIds .= $project->proj_id . ',';
            }
        }
        $projIds = substr($projIds, 0, -1);
        try {
            return DB::select('select `id`, `name` from `projs` '
                . 'where status = ' . Project::STATUS_APPROVED
                . ' and deleted_at is null '
                . 'and id in ('.$projIds.')');
            /*return DB::table('projs')
                ->select(['id', 'name'])
                ->whereIn('id', $arrayProjsCheck)
                ->where('status', Project::STATUS_APPROVED)
                ->whereNull('deleted_at')
                ->get();*/
        } catch (Exception $ex) {
            Log::error($ex);
            return [];
        }
    }
}
