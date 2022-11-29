<?php

namespace Rikkei\Statistic\Models;

use Rikkei\Core\Model\CoreModel;
use Carbon\Carbon;

/**
 * new bug change in a day of employee
 */
class STEmplBug extends CoreModel
{
    protected $table = 'st_empl_bug';
    public $timestamps = false;
    protected $fillable = ['created_at', 'proj_id', 'value', 'team_id', 'empl_id', 'type'];

    /**
     * get bug of employee follow a period
     *
     * @param Carbon $fromTime
     * @param Carbon $toTime
     * @param array $filters
     * @return \self
     */
    public static function getEmplBugPeriod($fromTime = null, $toTime = null, $filters = [])
    {
        if (!$toTime) {
            $toTime = Carbon::now();
        }
        if (!$fromTime) {
            $fromTime = clone $toTime;
            $fromTime->modify('-30 days');
        }
        if (!isset($filters['type']) || !$filters['type']) {
            $filters['type'] = STProjBug::TYPE_BUG_DEFECT;
        }
        $collection = self::select(['created_at', 'value', 'type', 'proj_id', 'team_id'])
            ->whereDate('created_at', '<=', $toTime->format('Y-m-d'))
            ->whereDate('created_at', '>=', $fromTime->format('Y-m-d'))
            ->where('type', '=', $filters['type']);
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
}
