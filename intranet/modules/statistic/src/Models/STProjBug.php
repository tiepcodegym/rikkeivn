<?php

namespace Rikkei\Statistic\Models;

use Rikkei\Core\Model\CoreModel;
use Carbon\Carbon;

/**
 * total bug or project
 */
class STProjBug extends CoreModel
{
    const TYPE_BUG_DEFECT = 1; // all bug
    const TYPE_BUG_LEAKAGE = 2; // bug type leakage
    const TYPE_BUG_DR = 3; // bug type defect for reward
    const TYPE_FIX_BUG_DE = 20; // fix bug defect
    const TYPE_FIX_BUG_LE = 21; // fix bug leakage
    const CORRECTION_COST = 4; // correction cost
    const LOG_TIME = 5; // correction cost

    protected $table = 'st_proj_bug';
    public $timestamps = false;
    protected $fillable = ['created_at', 'proj_id', 'value', 'team_id', 'type'];

    /**
     * get bug of employee follow a period
     *
     * @param Carbon $fromTime
     * @param Carbon $toTime
     * @param array $filters
     * @return \self
     */
    public static function getTotalBugPeriod($fromTime = null, $toTime = null, $filters = [])
    {
        if (!$toTime) {
            $toTime = Carbon::now();
        }
        if (!$fromTime) {
            $fromTime = clone $toTime;
            $fromTime->modify('-30 days');
        }
        if (!isset($filters['type']) || !$filters['type']) {
            $filters['type'] = TYPE_BUG_DEFECT::TYPE_BUG_DEFECT;
        }
        $collection = self::select(['created_at', 'value', 'type', 'proj_id', 'team_id'])
            ->whereDate('created_at', '<=', $toTime->format('Y-m-d'))
            ->whereDate('created_at', '>=', $fromTime->format('Y-m-d'))
            ->where('type', '=', $filters['type']);
        return $collection->get();
    }
}
