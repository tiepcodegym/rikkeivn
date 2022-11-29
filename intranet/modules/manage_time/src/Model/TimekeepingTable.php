<?php

namespace Rikkei\ManageTime\Model;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use mysql_xdevapi\Collection;
use Rikkei\Core\Model\CoreModel;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\TeamConst;

class TimekeepingTable extends CoreModel
{
    use SoftDeletes;

    protected $table = 'manage_time_timekeeping_tables';

    protected $fillable = [
        'date_max_import',
    ];

    const OFFICIAL = 4;
    const TRIAL = 3;
    const TYPE_ALL = 1;
    const OPEN_LOCK_UP = 1;
    const CLOSE_LOCK_UP = 2;

    /**
     * Get all of the post's comments.
     */
    public function timekeepingLock()
    {
        return $this->hasMany(TimekeepingLock::class, 'timekeeping_table_id');
    }
    /*
     * Get information of creator timekeeping table
     */
    public function getCreatorInfo()
    {
        return Employee::select('name', 'email')
            ->where('id', $this->creator_id)
            ->first();
    }

    /**
     * Get timekeeping table collection for manager
     * @param  array  $teamIds
     * @param  array  $dataFilter
     * @return [type]
     */
    public static function getTimekeepingTableCollection($teamIds = [], $dataFilter = [])
    {
        $timekeepingTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        $teamTable = Team::getTableName();

        $collection = self::select(
            "{$timekeepingTable}.id as timekeeping_table_id",
            "{$timekeepingTable}.timekeeping_table_name",
            "{$timekeepingTable}.month",
            "{$timekeepingTable}.year",
            "{$timekeepingTable}.start_date",
            "{$timekeepingTable}.end_date",
            "{$employeeTable}.name as creator_name",
            "{$teamTable}.code as team_code",
            "{$teamTable}.name as team_name",
            "{$timekeepingTable}.type",
            "{$timekeepingTable}.lock_up",
            "{$timekeepingTable}.lock_up_time"
        );

        // join employee
        $collection->join("{$employeeTable}",
            function ($join) use ($employeeTable, $timekeepingTable)
            {
                $join->on("{$employeeTable}.id", '=', "{$timekeepingTable}.creator_id");
            }
        );

        // join team
        $collection->join("{$teamTable}",
            function ($join) use ($teamTable, $timekeepingTable)
            {
                $join->on("{$teamTable}.id", '=', "{$timekeepingTable}.team_id");
            }
        );

        try {
            if (isset($dataFilter['manage_time_timekeeping_tables.start_date'])) {
                $startDateFilter = Carbon::parse($dataFilter['manage_time_timekeeping_tables.start_date'])->toDateString();
                $collection->whereDate("{$timekeepingTable}.start_date", "=", $startDateFilter);
            }

            if (isset($dataFilter['manage_time_timekeeping_tables.end_date'])) {
                $endDateFilter = Carbon::parse($dataFilter['manage_time_timekeeping_tables.end_date'])->toDateString();
                $collection->whereDate("{$timekeepingTable}.end_date", "=", $endDateFilter);
            }
        } catch (Exception $e) {
            return null;
        }

        if ($teamIds) {
            $collection->whereIn("{$timekeepingTable}.team_id", $teamIds);
        }
        $collection->whereNull("{$timekeepingTable}.deleted_at")
            ->groupBy("{$timekeepingTable}.id");

        $pager = Config::getPagerData(null, ['order' => "{$timekeepingTable}.id", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    public function getTablesList($year, $month)
    {
        return self::where('month', $month)
                ->where('year', $year)
                ->join('teams', 'teams.id', '=', 'manage_time_timekeeping_tables.team_id')
                ->select(
                    'manage_time_timekeeping_tables.id',
                    'timekeeping_table_name',
                    'team_id',
                    'start_date',
                    'end_date',
                    'month',
                    'year',
                    'manage_time_timekeeping_tables.type',
                    'teams.code as team_code',
                    'teams.name as team_name'
                )
                ->get();
    }

    /**
     * Get timekeeping table list
     * @param  array  $teamIds
     * @param  [int] $year
     * @return [type]
     */
    public static function getTimekeepingTablesList($teamIds = [], $year = null, $type = null)
    {
        $timekeepingTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        $teamTable = Team::getTableName();

        $collection = self::select(
            "{$timekeepingTable}.id as timekeeping_table_id",
            "{$timekeepingTable}.timekeeping_table_name",
            "{$timekeepingTable}.month",
            "{$timekeepingTable}.year",
            "{$timekeepingTable}.type",
            "{$timekeepingTable}.start_date",
            "{$timekeepingTable}.end_date",
            "{$employeeTable}.name as creator_name",
            "{$teamTable}.name as team_name"
        );
        // join employee
        $collection->join("{$employeeTable}",
            function ($join) use ($employeeTable, $timekeepingTable)
            {
                $join->on("{$employeeTable}.id", '=', "{$timekeepingTable}.creator_id");
            }
        );
        // join team
        $collection->join("{$teamTable}",
            function ($join) use ($teamTable, $timekeepingTable)
            {
                $join->on("{$teamTable}.id", '=', "{$timekeepingTable}.team_id");
            }
        );
        if ($year) {
            $collection->where("{$timekeepingTable}.year", $year);
        }

        if ($type && $type != self::TYPE_ALL) {
            $collection->where("{$timekeepingTable}.type", $type);
        }
        if ($teamIds) {
            $collection->whereIn("{$timekeepingTable}.team_id", $teamIds);
        }
        $collection = $collection->whereNull("{$timekeepingTable}.deleted_at")
            ->orderBy("{$timekeepingTable}.id", 'DESC')
            ->get();

        return $collection;
    }

    /**
     * Get all member of team and team child to timekeeping
     *
     * @param [int] $teamId
     * @param [array] $contractType
     *
     * @return Employee Collection
     */
    public static function getEmployeeTimekeepingByTeam($timekeepingTable, $contractType = null)
    {
        $teamId = $timekeepingTable->team_id;
        $employeeTable = Employee::getTableName();
        $teamTable = Team::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $empWorkTbl = EmployeeWork::getTableName();

        $teamIds = [];
        $teamIds[] = (int) $teamId;
        ManageTimeCommon::getTeamChildRecursive($teamIds, $teamId);
        $teamHN = Team::select('id')->where('code', TeamConst::CODE_HANOI)->first();

        if ($teamHN && $teamHN->id == $teamId) {
            // Add team BOD
            $teamBOD = Team::select('id')->where('code', TeamConst::CODE_BOD)->first();
            if ($teamBOD) {
                $teamIds[] = (int) $teamBOD->id;
            }
            // Add team PQA
            $teamPQAs = Team::getTeamTypePqa();
            if ($teamPQAs) {
                $teamIds = array_unique(array_merge($teamIds, $teamPQAs));
            }
        }
        $teamIds = array_values($teamIds);
        $monthOfKeeping = $timekeepingTable->month;
        $yearOfKeeping = $timekeepingTable->year;
        $month = Carbon::parse($yearOfKeeping . '-' . $monthOfKeeping . '-01');
        $collection = TeamMember::select([
                "{$employeeTable}.id as employee_id",
                "{$employeeTable}.offcial_date",
                "{$employeeTable}.trial_date",
                "{$employeeTable}.leave_date",
            ])
            ->join("{$teamTable}", "{$teamTable}.id", "=", "{$teamMemberTable}.team_id")
            ->join("{$employeeTable}", "{$employeeTable}.id", "=", "{$teamMemberTable}.employee_id")
            ->whereIn("{$teamMemberTable}.team_id", $teamIds)
            ->where(function ($query) use ($employeeTable, $timekeepingTable, $month) {
                $query->whereNull("{$employeeTable}.leave_date")
                    ->orWhereDate("{$employeeTable}.leave_date", '>=', $month->startOfMonth()->format('Y-m-d'));
            })
            ->whereNull("{$employeeTable}.deleted_at")
            ->whereDate("{$employeeTable}.join_date", "<=", $timekeepingTable->end_date)
            ->whereNotIn("{$employeeTable}.account_status", [getOptions::FAIL_CDD]);
        if ($contractType) {
            $collection->join("{$empWorkTbl}", "{$employeeTable}.id", "=", "{$empWorkTbl}.employee_id");
            $collection->whereIn("{$empWorkTbl}.contract_type", $contractType);
            $collection->addSelect("employee_works.contract_type");
        }

        $collection->groupBy("{$employeeTable}.id");

        return $collection->get();
    }

    /**
     * Get timekeeping table by id
     * @param  [int] $timekeepingTableId
     * @return [object]
     */
    public static function getTimekeepingTable($timekeepingTableId)
    {
        return self::select(
                'manage_time_timekeeping_tables.id',
                'timekeeping_table_name',
                'team_id',
                'start_date',
                'end_date',
                'month',
                'year',
                'manage_time_timekeeping_tables.type',
                'teams.code as team_code',
                'teams.name as team_name'
            )
            ->join('teams', 'teams.id', '=', 'manage_time_timekeeping_tables.team_id')
            ->where('manage_time_timekeeping_tables.id', $timekeepingTableId)
            ->first();
    }


    /**
     * Get timekeeping collection by employee
     * @param  [int] $employeeId
     * @param  [int|null] $year
     * @return [colleciotn]
     */
    public static function getCollectionTimekeepingByEmp($employeeId, $year = null)
    {
        $tblTimekeepingTable = self::getTableName();
        $tblTimekeepingAggregate = TimekeepingAggregate::getTableName();

        if (!$year) {
            $year = Carbon::now()->year;
        }
        $timekeepingThisPeriod = self::getTimekeepingThisPeriod($employeeId);
        $collection = self::select(
                "{$tblTimekeepingTable}.id as timekeeping_table_id",
                "{$tblTimekeepingTable}.month",
                "{$tblTimekeepingTable}.start_date",
                "{$tblTimekeepingTable}.end_date",
                "{$tblTimekeepingAggregate}.*",
                "teams.code as team_code",
                "employees.offcial_date"
            )
            ->join("{$tblTimekeepingAggregate}", "{$tblTimekeepingAggregate}.timekeeping_table_id", "=", "{$tblTimekeepingTable}.id")
            ->join('employees', 'employees.id', '=', "{$tblTimekeepingAggregate}.employee_id")
            ->join('teams', 'teams.id', '=', 'manage_time_timekeeping_tables.team_id')
            ->where("{$tblTimekeepingAggregate}.employee_id", $employeeId)
            ->where("{$tblTimekeepingTable}.year", $year);
        if ($timekeepingThisPeriod) {
            $collection->where("{$tblTimekeepingTable}.id", "!=", $timekeepingThisPeriod->timekeeping_table_id);
        }

        $pager = Config::getPagerData(null, ['order' => "{$tblTimekeepingTable}.month", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy("{$tblTimekeepingTable}.id", 'DESC');
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * Get timekeeping this period
     * @param  [int] $employeeId
     * @return [model]
     */
    public static function getTimekeepingThisPeriod($employeeId, $year = null)
    {
        if (!$year) {
            $year = self::getMaxYear();
        }
        $tblTimekeepingTable = self::getTableName();
        $tblTimekeepingAggregate = TimekeepingAggregate::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();
        return self::select(
            "{$tblTimekeepingTable}.id as timekeeping_table_id",
            "{$tblTimekeepingTable}.month",
            "{$tblTimekeepingTable}.year",
            "{$tblTimekeepingTable}.start_date",
            "{$tblTimekeepingTable}.end_date",
            "{$tblTimekeepingTable}.type",
            "{$tblTeam}.code",
            "{$tblTimekeepingTable}.lock_up",
            "{$tblTimekeepingTable}.lock_up_time"
        )
        ->join("{$tblTimekeepingAggregate}", "{$tblTimekeepingAggregate}.timekeeping_table_id", "=", "{$tblTimekeepingTable}.id")
        ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblTimekeepingAggregate}.employee_id")
        ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTimekeepingTable}.team_id")
        ->where("{$tblTimekeepingAggregate}.employee_id", $employeeId)
        ->where("{$tblTimekeepingTable}.year", $year)
        ->orderBy("{$tblTimekeepingTable}.month", "DESC")
        ->orderBy("{$tblTimekeepingTable}.id", "DESC")
        ->first();
    }

    /**
     * check date get timekeeping
     * @return [int|null]
     */
    public function checkDateGetTimekeeping($employeeId)
    {
        $year = self::getMaxYear();
        $now = Carbon::now();
        $tblTimekeepingTable = self::getTableName();
        $tblTimekeepingAggregate = TimekeepingAggregate::getTableName();
        $tblEmployee = Employee::getTableName();

        if ($now->day < 6) {
            $timekeeping = self::select("{$tblTimekeepingTable}.id as timekeeping_table_id")
                ->join("{$tblTimekeepingAggregate}", "{$tblTimekeepingAggregate}.timekeeping_table_id", "=", "{$tblTimekeepingTable}.id")
                ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblTimekeepingAggregate}.employee_id")
                ->where("{$tblTimekeepingAggregate}.employee_id", $employeeId)
                ->where("{$tblTimekeepingTable}.year", $year)
                ->where("{$tblTimekeepingTable}.month", $now->subMonth()->month)
                ->orderBy("{$tblTimekeepingTable}.month")
                ->orderBy("{$tblTimekeepingTable}.id", "DESC")
                ->first();
            if ($timekeeping) {
                return $timekeeping->timekeeping_table_id;
            }
        }
        return;
    }

    /**
     * Get timekeeping detail by employee
     * @param  [int] $timekeepingId
     * @param  [int] $employeeId
     * @return [model]
     */
    public static function getTimekeepingDetailByEmp($timekeepingId, $employeeId)
    {
        $tblTimekeepingTable = self::getTableName();
        $tblTimekeepingAggregate = TimekeepingAggregate::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();

       return self::select(
            "{$tblTimekeepingTable}.id as timekeeping_table_id",
            "{$tblTimekeepingTable}.month",
            "{$tblTimekeepingTable}.year",
            "{$tblTimekeepingTable}.start_date",
            "{$tblTimekeepingTable}.end_date",
            "{$tblTimekeepingTable}.lock_up",
            "{$tblTimekeepingTable}.lock_up_time",
            "{$tblTimekeepingTable}.type",
            "{$tblTeam}.code",
            "{$tblTeam}.code as team_code"
        )
        ->join("{$tblTimekeepingAggregate}", "{$tblTimekeepingAggregate}.timekeeping_table_id", "=", "{$tblTimekeepingTable}.id")
        ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblTimekeepingAggregate}.employee_id")
        ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTimekeepingTable}.team_id")
        ->where("{$tblTimekeepingAggregate}.employee_id", $employeeId)
        ->where("{$tblTimekeepingTable}.id", $timekeepingId)
        ->orderBy("{$tblTimekeepingTable}.month", "DESC")
        ->orderBy("{$tblTimekeepingTable}.id", "DESC")
        ->first();
    }

    /**
     * Get maxx year timekeeping table
     * @return [int]
     */
    public static function getMaxYear()
    {
        $tblTimekeepingTable = self::getTableName();
        $tblTimekeepingAggregate = TimekeepingAggregate::getTableName();
        return self::join("{$tblTimekeepingAggregate}", "{$tblTimekeepingAggregate}.timekeeping_table_id", "=", "{$tblTimekeepingTable}.id")
            ->max("{$tblTimekeepingTable}.year");
    }

    /**
     * check mont exist timesheet
     * @param mixed $time
     * @param boolean $prev
     * @return boolean
     */
    public static function checkExistsMonth($time, $prev = false)
    {
        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }
        if ($prev) {
            $time->subMonthNoOverflow();
        }
        $exits = self::where('month', $time->month)
                ->where('year', $time->year)
                ->first();
        if ($exits) {
            return true;
        }
        return false;
    }

    /**
     * [getTimekeepingTableByTimeType description]
     * chỉ áp dụng chi nhánh hà nội
     * @param  [carbon] $date
     * @param  [init] $type
     * @return [type]
     */
    public function getTimekeepingTableByTimeType($date, $type)
    {
        $teamTable = Team::getTableName();
        $tkTable = self::getTableName();
        return self::select(
            "{$tkTable}.id",
            "{$tkTable}.month",
            "{$tkTable}.year",
            "{$tkTable}.type",
            "{$tkTable}.start_date",
            "{$tkTable}.end_date",
            "{$teamTable}.code as team_code",
            "{$teamTable}.name as team_name"
        )
        ->join("{$teamTable}", "{$teamTable}.id", "=", "{$tkTable}.team_id")
        ->where("{$tkTable}.month", $date->month)
        ->where("{$tkTable}.year", $date->year)
        ->where("{$tkTable}.type", $type)
        ->where(function ($query) use ($teamTable) {
            $query->where("{$teamTable}.code", 'LiKE', Team::CODE_PREFIX_HN . '%')
                ->orWhere("{$teamTable}.code", '=', Team::CODE_PREFIX_AI);
        })
        ->whereNull("{$tkTable}.deleted_at")
        ->get();
    }

    /**
     * Get timekeeping table by year and teams
     *
     * @param $yearCurrent
     * @param $teamAll
     * @param $type
     * @return Collection
     */
    public function getTkTableByYearTeamsType($yearCurrent, $teamAll, $type)
    {
        return self::select(
            'manage_time_timekeeping_tables.id',
            'timekeeping_table_name',
            'team_id',
            'start_date',
            'end_date',
            'month',
            'year',
            'manage_time_timekeeping_tables.type',
            'teams.code as team_code',
            'teams.name as team_name'
        )
        ->join('teams', 'teams.id', '=', 'manage_time_timekeeping_tables.team_id')
        ->where('year', $yearCurrent)
        ->where('manage_time_timekeeping_tables.type', $type)
        ->whereIn('team_id', $teamAll)
        ->orderBy('month', 'DESC')
        ->orderBy('id', 'DESC')
        ->first();
    }

    /**
     * get label category timekeeping table
     * @return array
     */
    public function getArrLabelTypeTKTable()
    {
       return [
            self::OFFICIAL => trans('manage_time::view.Official - Trial'),
            self::TRIAL => trans('manage_time::view.Part time'),
            self::TYPE_ALL => trans('manage_time::view.All'),
       ];
    }
}