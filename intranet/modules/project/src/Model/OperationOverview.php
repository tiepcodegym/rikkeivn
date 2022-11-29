<?php

namespace Rikkei\Project\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;

class OperationOverview extends CoreModel
{
    protected $table = 'operation_overview';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['base', 'members', 'member_main', 'member_part_time', 'month', 'onsite', 'osdc', 'project', 'team_id', 'branch_code', 'is_collapse'];


    public function getOperationReportsTeam($monthStart, $monthEnd, $teamIds = [])
    {
        $collection = static::select(
            'operation_overview.*',
            'teams.name as team_name'
        )
        ->leftJoin('teams', 'teams.id', '=', 'operation_overview.team_id')
        ->where('month','>=', $monthStart)
        ->where('month','<=', $monthEnd);
        if ($teamIds) {
            $collection->whereIn('team_id', $teamIds);
        }
        return $collection->orderBy('month')->get();
    }
}
