<?php

namespace Rikkei\Api\Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\Project\Model\CronjobEmployeePoints;
use Rikkei\Project\Model\OperationOverview;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team as TeamModel;
use Rikkei\Project\View\View as ProjectView;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Team\Model\Employee;

/**
 * Description of Contact
 *
 * @author lamnv
 */
class Team extends BaseHelper
{
    public function __construct()
    {
        $this->model = TeamModel::class;
    }

    /**
     * get teams list
     * @param array $data
     * @return array
     */
    public function getList($data = [])
    {
        return parent::getList(array_merge([
            'select' => ['*'],
            's' => null,
            'page' => 1,
            'per_page' => -1,
            'fields_search' => ['name'],
            'orderby' => 'name',
            'order' => 'asc'
        ], array_filter($data)));
    }

    public function getTotalEmployee($data)
    {
        $leaveDateTable = LeaveDayRegister::getTableName();
        $teamTable = TeamModel::getTableName();
        $employeeTable = Employee::getTableName();
        $employeeTeamTable = EmployeeTeamHistory::getTableName();
        $operationOverviewTable = OperationOverview::getTableName();
        $date = Carbon::createFromFormat('Y-m', $data['month'])->format('Y-m');
        $dateFormat = '%Y-%m';
        $date = "'{$date}'";

        $subqueryTotalLeaveDay = DB::table($leaveDateTable)
            ->select(
                'creator_id',
                DB::raw('1 as leave_date')
            )
            ->whereRaw(DB::raw("date_format(date_start, '{$dateFormat}') <= {$date}"))
            ->whereRaw(DB::raw("date_format(date_end, '{$dateFormat}') >= {$date}"))
            ->whereNull('deleted_at')
            ->whereRaw('status = ' . LeaveDayRegister::STATUS_APPROVED)
            ->groupBy('creator_id');

        $employeePoints = DB::table($operationOverviewTable)
            ->select(
                'members',
                'member_main',
                'member_part_time',
                'team_id'
            )
            ->whereRaw(DB::raw("month = {$date}"));

        $mainQuery = DB::table($teamTable)
            ->select(
                "{$teamTable}.id",
                "{$teamTable}.name",
                "{$teamTable}.code",
                "{$teamTable}.branch_code",
                DB::raw('0 as totalOfficalStaff'),
                DB::raw('0 as totalPartTime'),
                DB::raw('0 as totalDivision'),
                DB::raw('sum(coalesce(leave_tbl.leave_date, 0)) as totalLeave'),
                DB::raw('0 as totalOnsite')
            );

        if (isset($data['team_id'])) {
            $mainQuery = $mainQuery->where("{$teamTable}.id", $data['team_id']);
            $employeePoints = $employeePoints->where("team_id", $data['team_id']);
        }

        $mainQuery = $mainQuery->join($employeeTeamTable, "{$employeeTeamTable}.team_id", '=', "{$teamTable}.id")
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$employeeTeamTable}.employee_id")
            ->where(function ($query) use ($employeeTable, $date, $dateFormat) {
                $query->whereNull($employeeTable . '.leave_date')
                    ->orWhereRaw(DB::raw("date_format({$employeeTable}.leave_date, '{$dateFormat}') > {$date}"));
            })
            ->where(function ($query) use ($employeeTable, $employeeTeamTable, $date, $dateFormat) {
                $query->whereNull("{$employeeTeamTable}.start_at")
                    ->orWhereRaw(DB::raw("date_format({$employeeTeamTable}.start_at, '{$dateFormat}') <=  {$date}"));
            })
            ->where(function ($query) use ($employeeTable, $employeeTeamTable, $date, $dateFormat) {
                $query->whereNull("{$employeeTeamTable}.end_at")
                    ->orWhereRaw(DB::raw("date_format({$employeeTeamTable}.end_at, '{$dateFormat}') >= {$date}"));
            })
            ->whereNull("{$employeeTeamTable}.deleted_at")
            ->leftJoin(DB::raw('(' . $subqueryTotalLeaveDay->toSql() . ') as leave_tbl'),
                function ($join) use ($subqueryTotalLeaveDay, $employeeTable) {
                    $join->on("{$employeeTable}.id", '=', 'leave_tbl.creator_id');
                }
            )
            ->where('is_soft_dev', TeamModel::IS_SOFT_DEVELOPMENT)
            ->groupBy("{$teamTable}.id")
            ->get();

        // Total onsite, main and part-time by division
        $employeeOnsite = $this->_getEmployeeOnsite($data);
        $employeePoints = $employeePoints->get();

        foreach ($mainQuery as $i) {
            $i->totalLeave = (int) $i->totalLeave;
            $totalDefault = 0;
            foreach ($employeeOnsite as $teamId => $onsiter) {
                if ($i->id == $teamId) {
                    $i->totalOnsite = $onsiter;
                    break;
                }
                $i->totalOnsite = $totalDefault;
            }
            foreach ($employeePoints as $item) {
                if ($i->id == $item->team_id) {
                    $i->totalDivision = $item->members;
                    $i->totalOfficalStaff = $item->member_main;
                    $i->totalPartTime = $item->member_part_time;
                    break;
                }
            }
        }

        return $mainQuery;
    }

    public function getHolidays($request)
    {
        $holidays = [];
        foreach ($request->branch_codes as $branchCode) {
            $holidays[$branchCode] = CoreConfigData::getSpecialHolidays(2, $branchCode);
        }

        return $holidays;
    }

    /**
     * Get employees point
    */
    public function getPointEmployees($filter)
    {
        $result = [];
        $teamIds = $filter['team_id'];
        $monthFrom = $filter['month_from'];
        $monthTo = $filter['month_to'];
        if (count($teamIds) == 0) return $result;
        $result = CronjobEmployeePoints::where('month', '>=', $monthFrom)->where('month', '<=', $monthTo)->whereIn('team_id', $teamIds)->get()->toArray();

        return $result;
    }

    private function _getEmployeeOnsite($filter)
    {
        $result = [];
        $onsiteMonth = explode("-", $filter['month']);
        $firstAndLastDayOfMonth = ResourceView::getInStance()->getFirstLastDaysOfMonth($onsiteMonth[1], $onsiteMonth[0]);
        $dateOfWorkInMonth = ProjectView::getMM($firstAndLastDayOfMonth[0], $firstAndLastDayOfMonth[1], 2);
        $bussinessTripRegisters = $this->_getEmployeeBussinessTripRegisterByMonth($filter);

        foreach ($bussinessTripRegisters as $v) {

            // Total work days in month of employee
            $dateOfOnsiteInMonth = ResourceView::getInStance()->getRealDaysOfMonth($onsiteMonth[1], $onsiteMonth[0], $v['start_at'], $v['end_at']);

            if ($dateOfOnsiteInMonth > 0) {
                $onsiteRatioInMonth = number_format($dateOfOnsiteInMonth / $dateOfWorkInMonth, 2, '.', '');

                $result[] = [
                    "team_id" => $v['team_id'],
                    "team_name" => $v['team_name'],
                    "onsite" => $onsiteRatioInMonth
                ];
            }
        }

        // predefine array
        $dataSum = [];
        foreach ($result as $value) {
            $dataSum[$value['team_id']] = 'team_id';
        }

        foreach ($result as $list) {
            $dataSum[$list['team_id']] += $list['onsite'];
        }

        return $dataSum;
    }

    private function _getEmployeeBussinessTripRegisterByMonth($filter)
    {
        $dateFormat = '%Y-%m';
        $tblTeam = TeamModel::getTableName();
        $tblBusinessTripRegisters = BusinessTripRegister::getTableName();
        $tblBusinessTripEmployees = BusinessTripEmployee::getTableName();

        $data = TeamModel::join($tblBusinessTripEmployees, "{$tblBusinessTripEmployees}.team_id", '=', "{$tblTeam}.id")
            ->join($tblBusinessTripRegisters, "{$tblBusinessTripRegisters}.id", '=', "{$tblBusinessTripEmployees}.register_id")
            ->where("{$tblBusinessTripRegisters}.status", '=', BusinessTripRegister::STATUS_APPROVED);
        if (isset($filter['team_id'])) {
            $data = $data->where("{$tblTeam}.id", $filter['team_id']);
        }
        $data = $data->whereRaw(DB::raw("date_format(date_start, '{$dateFormat}') <= '{$filter['month']}'"))
            ->whereRaw(DB::raw("date_format(date_end, '{$dateFormat}') >= '{$filter['month']}'"))
            ->whereNull("{$tblBusinessTripRegisters}.deleted_at")
            ->whereNull("{$tblBusinessTripRegisters}.parent_id")
            ->select([
                "{$tblTeam}.name as team_name", "{$tblTeam}.code as code", "{$tblTeam}.branch_code as branch_code",
                "{$tblBusinessTripEmployees}.start_at", "{$tblBusinessTripEmployees}.end_at", "{$tblBusinessTripEmployees}.team_id as team_id"
            ])->get()->toArray();

        return $data;
    }
}
