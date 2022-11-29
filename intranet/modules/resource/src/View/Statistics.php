<?php

namespace Rikkei\Resource\View;

use Carbon\Carbon;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Employee;
use Rikkei\Resource\Model\RecruitPlan;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\EmplCvAttrValue;
use Illuminate\Support\Facades\DB;

class Statistics
{
    const MIN_DATE = '1970-01-01';
    const MAX_DATE = '9999-12-31';

    public static function getTotal($contractTypes, $time, $timeType = 'month', $isExact = false)
    {
        $empTbl = Employee::getTableName();
        $cddTbl = Candidate::getTableName();
        $tmbTbl = TeamMember::getTableName();
        $teamTbl = Team::getTableName();
        if ($isExact === true) {
            $rangeTime = [
                'start' => Carbon::now()->firstOfYear()->format('Y-m-d'),
                'end' => Carbon::now()->format('Y-m-d'),
            ];
        } else {
            $rangeTime = RecruitPlan::getRangeTime($time, $timeType);
        }

        $total = Employee::select(
            $empTbl.'.id',
            DB::raw('DATE('.$empTbl.'.join_date) as date_join'),
            'tmb.team_id',
            'attr.value as roles',
            'empw.contract_type'
        )
            ->leftJoin($cddTbl . ' as cdd', function ($join) use ($empTbl) {
                $join->on($empTbl . '.id', '=', 'cdd.employee_id')
                        ->whereNull('cdd.deleted_at');
            })
            ->leftJoin(EmployeeWork::getTableName() . ' as empw', $empTbl . '.id', '=', 'empw.employee_id')
            ->leftJoin($tmbTbl . ' as tmb', 'tmb.employee_id', '=', $empTbl . '.id')
            ->leftJoin($teamTbl . ' as team', 'tmb.team_id', '=', 'team.id')
            ->leftJoin(EmplCvAttrValue::getTableName() . ' as attr', function ($join) use ($empTbl) {
                $join->on('attr.employee_id', '=', $empTbl . '.id')
                        ->where('attr.code', '=', 'role');
            })
            ->leftJoin(
                DB::raw(
                    '(SELECT tmb1.employee_id, GROUP_CONCAT(team1.code) AS team_codes, '
                        . 'SUM(CASE WHEN team1.code LIKE "'. Team::CODE_PREFIX_JP .'%" THEN 1 ELSE 0 END) AS num_team_jp,'
                        . 'SUM(CASE WHEN team1.code NOT LIKE "'. Team::CODE_PREFIX_JP .'%" THEN 1 ELSE 0 END) AS num_team_vn '
                    . 'FROM '. $teamTbl .' AS team1 '
                    . 'INNER JOIN '. $tmbTbl .' AS tmb1 ON team1.id = tmb1.team_id '
                    . 'GROUP BY tmb1.employee_id) AS emp_teams'
                ),
                'emp_teams.employee_id',
                '=',
                $empTbl.'.id'
            )
            ->where(function ($query) {
                $query->whereNull('cdd.id')
                        ->orWhereIn('cdd.status', [getOptions::WORKING, getOptions::LEAVED_OFF]);
            })
            ->where(function ($query) use ($rangeTime) {
                $query->where(DB::raw('DATE(leave_date)'), '>', $rangeTime['end'])
                ->orWhereNull('leave_date');
            })
            ->where(DB::raw('DATE(join_date)'), '<=', $rangeTime['end'])
            ->where(function ($query) {
                //if employee in both JP and VN team then take VN team
                $query->where('team.code', 'NOT LIKE', Team::CODE_PREFIX_JP . '%')
                        ->orWhere('emp_teams.num_team_vn', '=', 0);
            })
            ->groupBy('tmb.employee_id', 'tmb.team_id')
            ->get();

        return [
            'total' => $total,
            'rangeTime' => $rangeTime
        ];
    }
}
