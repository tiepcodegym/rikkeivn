<?php

namespace Rikkei\ManageTime\Model;

use Exception;
use Carbon\Carbon;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Employee;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryTable extends CoreModel
{
    use SoftDeletes;

    protected $table = 'manage_time_salary_tables';

    public static function getSalaryTableList($teamIds = [], $dataFilter = [])
    {
        $tblSalaryTable = self::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();

        $collection = self::select(
            "{$tblSalaryTable}.id as salary_table_id",
            "{$tblSalaryTable}.timekeeping_table_id",
            "{$tblSalaryTable}.salary_table_name",
            "{$tblSalaryTable}.month",
            "{$tblSalaryTable}.year",
            "{$tblSalaryTable}.start_date",
            "{$tblSalaryTable}.end_date",
            "{$tblEmployee}.name as creator_name",
            "{$tblTeam}.name as team_name"
        );

        // join employee
        $collection->join("{$tblEmployee}", 
            function ($join) use ($tblEmployee, $tblSalaryTable)
            {
                $join->on("{$tblEmployee}.id", '=', "{$tblSalaryTable}.creator_id");
            }
        );

        // join team
        $collection->join("{$tblTeam}", 
            function ($join) use ($tblTeam, $tblSalaryTable)
            {
                $join->on("{$tblTeam}.id", '=', "{$tblSalaryTable}.team_id");
            }
        );

        try {
            if (isset($dataFilter['manage_time_salary_tables.start_date'])) {
                $startDateFilter = Carbon::parse($dataFilter['manage_time_salary_tables.start_date'])->toDateString();
                $collection->whereDate("{$tblSalaryTable}.start_date", "=", $startDateFilter);
            }

            if (isset($dataFilter['manage_time_salary_tables.end_date'])) {
                $endDateFilter = Carbon::parse($dataFilter['manage_time_salary_tables.end_date'])->toDateString();
                $collection->whereDate("{$tblSalaryTable}.end_date", "=", $endDateFilter);
            }
        } catch (Exception $e) {
            return null;
        }

        if ($teamIds) {
            $collection->whereIn("{$tblSalaryTable}.team_id", $teamIds);
        }
        $collection->whereNull("{$tblSalaryTable}.deleted_at")
            ->groupBy("{$tblSalaryTable}.id");

        $pager = Config::getPagerData(null, ['order' => "{$tblSalaryTable}.year", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy("{$tblSalaryTable}.month", 'DESC')->orderBy("{$tblSalaryTable}.id", 'DESC');
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }



    /**
     * Get all member of team and team child to timekeeping
     * 
     * @param [int] teamId
     * 
     * @return array
     */
    public static function getEmployeeSalaryByTeam($teamId)
    {
        if (!$teamId) {
            return null;
        }
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $teamIds = [];
        $teamIds[] = (int) $teamId;
        ManageTimeCommon::getTeamChildRecursive($teamIds, $teamId);
        $teamBOD = Team::select('id')->where('code', TeamConst::CODE_BOD)->first();
        if ($teamBOD && $teamBOD->id == $teamId) {
            $teamUnset = [];
            $teamDN = Team::select('id')->where('code', TeamConst::CODE_DANANG)->first();
            if ($teamDN) {
                $teamUnset[] = (int) $teamDN->id;
                ManageTimeCommon::getTeamChildRecursive($teamUnset, $teamDN->id);
                if (count($teamIds) && count($teamUnset)) {
                    $teamIds = array_values(array_diff($teamIds, $teamUnset));
                }
            }
        }

        $collection = TeamMember::select("{$tblEmployee}.id as employee_id", "{$tblEmployee}.offcial_date")
            ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
            ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblTeamMember}.employee_id")
            ->whereIn("{$tblTeamMember}.team_id", $teamIds)
            ->whereNull("{$tblEmployee}.leave_date")
            ->groupBy("{$tblEmployee}.id")
            ->get();

        return $collection;
    }
}