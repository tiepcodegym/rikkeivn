<?php

namespace Rikkei\Tag\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Project;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TagEmployee extends CoreModel
{
    public static function getBusyRate($employeeIds, $now = null)
    {
        $tableProjMember = ProjectMember::getTableName();
        $tableProj = Project::getTableName();
        if (!$now) {
            $now = Carbon::now();
        }
        $monthCurrent = $now->format('m');
        $yearCurrent = $now->format('Y');
        $startCheck = "{$yearCurrent}-{$monthCurrent}-01";
        $endCheck = $now->setDate($yearCurrent, $monthCurrent + 2, 1)
            ->lastOfMonth()->format('Y-m-d');
        $collection = DB::table($tableProjMember . ' AS t_proj_member')
            ->select(['t_proj_member.employee_id', 't_proj_member.start_at', 
                't_proj_member.end_at', 't_proj_member.effort'])
            ->join($tableProj . ' AS t_proj', 't_proj.id', '=', 't_proj_member.project_id')
            ->whereIn('t_proj.status', [Project::STATUS_APPROVED, Project::STATUS_OLD])
            ->where('t_proj_member.status', ProjectMember::STATUS_APPROVED)
            ->whereIn('t_proj_member.employee_id', $employeeIds)
            ->orderBy('t_proj_member.start_at', 'ASC');
        if (Project::isUseSoftDelete()) {
            $collection->whereNull('t_proj.deleted_at');
        }
        // where month current and 2 month continue
        $collection->where(function($query) use ($startCheck, $endCheck) {
            $query->orWhere(function($query) use ($startCheck, $endCheck) {
                $query->whereDate('t_proj_member.start_at', '>=', $startCheck)
                    ->whereDate('t_proj_member.start_at', '<=', $endCheck);
            })->orWhere(function($query) use ($startCheck, $endCheck) {
                $query->whereDate('t_proj_member.end_at', '>=', $startCheck)
                    ->whereDate('t_proj_member.end_at', '<=', $endCheck);
            });
        });
        return $collection->get();
    }
}
