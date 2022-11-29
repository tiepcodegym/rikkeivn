<?php

namespace Rikkei\Team\Model;

use Illuminate\Database\Eloquent\Model;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\Model\Checkpoint;

class CheckpointTime extends Model
{
    protected $table = 'checkpoint_time';

    /**
     * get grid of checkpoint time.
     */
    public static function gridCheckPointPeriod()
    {
        $pager = Config::getPagerData();
        $pagerFilter = (array) Form::getFilterPagerData();
        $pagerFilter = array_filter($pagerFilter);

        $collection = self::select('id', 'check_time', 'created_at', DB::raw('SUBSTRING_INDEX(`check_time`, "/", -1)  as year_filter'), DB::raw('SUBSTRING_INDEX(`check_time`, "/", 1)  as month_filter'));
        if ($pagerFilter) {
            if ($pager['order'] == 'checkpoint_time.check_time') {
                $collection->orderBy('year_filter', $pager['dir'])
                            ->orderBy('month_filter', $pager['dir']);
            } else {
                $collection->orderBy($pager['order'], $pager['dir']);
            }
        } else {
            $collection->orderBy('id', 'desc');
        }
        CoreModel::filterGrid($collection);
        CoreModel::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;

    }

    /*
     * check checkpoint time not yet use  follow id.
     *
     * @param int $idPeriod
     * @return boolean.
     */
    public static function isCheckpointTimeNotYetUse($idPeriod)
    {
        $item = Checkpoint::where('checkpoint_time_id', '=', $idPeriod)
                    ->select('id')
                    ->first();
        if ($item) {
            return false;
        }
        return true;
    }
}
