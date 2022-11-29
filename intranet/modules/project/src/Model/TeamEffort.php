<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\View\View as ProjView;

class TeamEffort extends CoreModel
{
    protected $table = 'team_effort_report';
    protected $fillable = ['time', 'team_id', 'effort_data', 'free_effort'];
    public $timestamps = false;

    const FREE_EFFORT = 70; //%
    const MAX_PROJ = 15; //real max project per month

    /**
     * convert value
     * @param type $value
     * @return type
     */
    public function getEffortDataAttribute($value)
    {
        if (!$value) {
            return [];
        }
        return unserialize($value);
    }

    /**
     * convert value
     * @param type $value
     * @return type
     */
    public function getFreeEffortAttribute($value)
    {
        if (!$value) {
            return [];
        }
        return unserialize($value);
    }

    /**
     * get effort data
     * @param type $data
     * @return type
     */
    public static function getEffortData($data = [])
    {
        $fromMonth = $data['from_month'];
        $toMonth = $data['to_month'];
        return self::where('time', '>=', $fromMonth->startOfMonth()->toDateString())
                ->where('time', '<=', $toMonth->endOfMonth()->toDateString())
                ->select(DB::raw('DATE_FORMAT(time, "%m-%Y") as month'), 'effort_data')
                ->get();
    }

    /**
     * get effort free data
     * @param type $data
     * @return type
     */
    public static function getFreeEffortData($data = [])
    {
        $fromMonth = $data['from_month'];
        $toMonth = $data['to_month'];
        return self::where('time', '>=', $fromMonth->startOfMonth()->toDateString())
                ->where('time', '<=', $toMonth->endOfMonth()->toDateString())
                ->select(DB::raw('DATE_FORMAT(time, "%m-%Y") as month'), 'free_effort')
                ->get();
    }

    /**
     * cron job get update data team effort
     * @return boolean
     * @throws \Exception
     */
    public static function cronUpdateTeamEffort()
    {
        //get max time in team report table
        if (app()->environment() !== 'production') {
            $fromMonth = null;
        } else {
            $fromMonth = self::max('time');
        }
        //if not get min time join date in employee table
        if (!$fromMonth) {
            $fromMonth = Employee::min('join_date');
        }
        if (!$fromMonth) {
            return;
        }
        $fromMonth = Carbon::parse($fromMonth);
        //set to month = now
        $toMonth = Carbon::now()->endOfMonth();
        if ($fromMonth->format('Y-m') === $toMonth->format('Y-m')) {
            return;
        }
        //update team free effort
        self::cronUpdateTeamFreeEffort($fromMonth, $toMonth);
        $results = [];
        $teamIds = Team::getTeamsChildest();
        //loop employee count effort
        $countMonth = clone $fromMonth;
        while ($countMonth->lte($toMonth)) {
            $firstFromMonth = clone $countMonth;
            $firstFromMonth->startOfMonth();

            $formatMonth = $firstFromMonth->format('Y-m-d');
            foreach ($teamIds as $team) {
                $teamId = $team->id;
                $numEmp = EmployeeTeamHistory::getCountEmployeeOfMonth($countMonth->month, $countMonth->year, $teamId);
                if (!isset($results[$formatMonth])) {
                    $results[$formatMonth] = [];
                }
                $results[$formatMonth][$teamId] = round($numEmp);
            }
            $countMonth->addMonthNoOverflow();
        }
        if (!$results) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach ($results as $month => $effortData) {
                $dataInsert = [
                    'time' => $month,
                    'effort_data' => serialize($effortData)
                ];
                $exists = self::where('time', $month)->first();
                if ($exists) {
                    $exists->update($dataInsert);
                } else {
                    self::create($dataInsert);
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * cron update free resource
     * @param type $fromMonth
     * @param type $toMonth
     * @return boolean
     * @throws \Exception
     */
    public static function cronUpdateTeamFreeEffort($fromMonth, $toMonth)
    {  
        $queryOrderBy = 'ORDER BY pjm.start_at DESC';
        $members = DB::select(
                'SELECT emp.id as employee_id, emp.email, tmb.team_id, tmb.start_at, tmb.end_at, '
                    . 'COUNT(DISTINCT(pjm.project_id)) as count_proj, '
                    . 'GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, ",", pjm.project_id)) '. $queryOrderBy .' SEPARATOR "|") AS projs_id, '
                    . 'GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, ",", DATE(pjm.start_at))) '. $queryOrderBy .' SEPARATOR "|") AS projs_start_at, '
                    . 'GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, ",", DATE(pjm.end_at))) '. $queryOrderBy .' SEPARATOR "|") AS projs_end_at, '
                    . 'GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, ",", pjm.type)) '. $queryOrderBy .' SEPARATOR "|") AS projs_type, '
                    . 'GROUP_CONCAT(DISTINCT(CONCAT(pjm.id, ",", pjm.effort)) '. $queryOrderBy .' SEPARATOR "|") AS projs_effort '
                . 'FROM ' . Employee::getTableName() . ' AS emp '
                . 'INNER JOIN ' . EmployeeTeamHistory::getTableName() . ' AS tmb ON emp.id = tmb.employee_id '
                    . 'AND (tmb.start_at IS NULL OR DATE(tmb.start_at) <= "'. $toMonth->endOfMonth()->toDateString() .'") '
                    . 'AND (tmb.end_at IS NULL OR DATE(tmb.end_at) >= "'. $fromMonth->startOfMonth()->toDateString() .'") '
                . 'INNER JOIN ' . Team::getTableName() . ' AS team ON tmb.team_id = team.id '
                    . 'AND team.is_soft_dev = ' . Team::IS_SOFT_DEVELOPMENT . ' '
                . 'LEFT JOIN ' . ProjectMember::getTableName() . ' AS pjm ON emp.id = pjm.employee_id '
                    . 'AND pjm.start_at <= "' . $toMonth->endOfMonth()->toDateTimeString() . '" '
                    . 'AND pjm.end_at >= "' . $fromMonth->startOfMonth()->toDateTimeString() . '" '
                . 'LEFT JOIN ' . Project::getTableName() . ' AS proj ON pjm.project_id = proj.id '
                . 'WHERE emp.deleted_at IS NULL '
                . 'AND DATE(emp.join_date) <= "'. $toMonth->endOfMonth()->toDateString() .'" '
                . 'GROUP BY tmb.employee_id, tmb.team_id '
                . 'HAVING count_proj <= ' . self::MAX_PROJ . ' '
                . 'ORDER BY pjm.employee_id ASC, pjm.start_at ASC'
            );

        if (count($members) < 1) {
            return;
        }

        $results = [];
        //loop employee count effort
        $countMonth = clone $fromMonth;
        while ($countMonth->lte($toMonth)) {
            $firstFromMonth = clone $countMonth;
            $firstFromMonth->startOfMonth();
            $lastFromMonth = clone $countMonth;
            $lastFromMonth->endOfMonth();

            $formatMonth = $firstFromMonth->format('Y-m-d');
            if (!isset($results[$formatMonth])) {
                $results[$formatMonth] = [];
            }
            $dayOfMonth = ProjView::getMM($firstFromMonth, $lastFromMonth, 2);
            foreach ($members as $member) {
                //check effort
                $teamStartAt = $member->start_at;
                if ($teamStartAt) {
                    $teamStartAt = Carbon::parse($teamStartAt);
                }
                $teamEndAt = $member->end_at;
                if ($teamEndAt) {
                    $teamEndAt = Carbon::parse($teamEndAt);
                }
                if (($teamStartAt && $teamStartAt->gt($lastFromMonth)) ||
                        ($teamEndAt && $teamEndAt->lt($firstFromMonth))) {
                    continue;
                }
                $isPm = false;
                $isBrse = false;
                if ($member->count_proj > 0) {
                    $arrProjStartAt = explode('|', $member->projs_start_at);
                    $arrProjEndAt = explode('|', $member->projs_end_at);
                    $arrProjEffort = explode('|', $member->projs_effort);
                    $arrProjRole = explode('|', $member->projs_type);
                    if (count($arrProjEffort) < 1) {
                        continue;
                    }
                    $countEffort = 0;
                    for ($i = 0; $i < count($arrProjEffort); $i++) {
                        $arrEffort = explode(',', $arrProjEffort[$i]);
                        $arrStartAt = explode(',', $arrProjStartAt[$i]);
                        $arrEndAt = explode(',', $arrProjEndAt[$i]);
                        $arrRole = explode(',', $arrProjRole[$i]);

                        $effort = (float) $arrEffort[1];
                        $startAt = Carbon::parse($arrStartAt[1]);
                        $endAt = Carbon::parse($arrEndAt[1]);
                        $role = $arrRole[1];

                        if ($startAt->gt($lastFromMonth) || $endAt->lt($firstFromMonth)) {
                            continue;
                        }
                        if ($startAt->lt($firstFromMonth)) {
                            $startAt = $firstFromMonth;
                        }
                        if ($endAt->gt($lastFromMonth)) {
                            $endAt = $lastFromMonth;
                        }
                        $countEffort += ProjView::getMM($startAt, $endAt, 2) * $effort / $dayOfMonth;
                        if ($countEffort > self::FREE_EFFORT) {
                            break;
                        }
                        if (!$isPm && $role == ProjectMember::TYPE_PM) {
                            $isPm = true;
                        }
                        if (!$isBrse && $role == ProjectMember::TYPE_BRSE) {
                            $isBrse = true;
                        }
                    }
                    //check effort < thresh hold
                    if ($countEffort > self::FREE_EFFORT) {
                        continue;
                    }
                }
                $teamId = $member->team_id;
                if (!isset($results[$formatMonth][$teamId])) {
                    $results[$formatMonth][$teamId] = [
                        'total' => 0,
                        'pm' => 0,
                        'brse' => 0
                    ];
                }
                $results[$formatMonth][$teamId]['total'] += 1;
                if ($isPm) {
                    $results[$formatMonth][$teamId]['pm'] += 1;
                }
                if ($isBrse) {
                    $results[$formatMonth][$teamId]['brse'] += 1;
                }
            }
            $countMonth->addMonthNoOverflow();
        }
        if (!$results) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach ($results as $month => $effortData) {
                $dataInsert = [
                    'time' => $month,
                    'free_effort' => serialize($effortData)
                ];
                $exists = self::where('time', $month)->first();
                if ($exists) {
                    $exists->update($dataInsert);
                } else {
                    self::create($dataInsert);
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
