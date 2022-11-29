<?php

namespace Rikkei\Api\Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Collection;
use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\ManageTime\Model\Timekeeping as TkModel;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Rikkei\Ot\Model\OtEmployee;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\ManageTime\View\View as ViewManage;
use Rikkei\ManageTime\View\View as ManageTimeView;

class Timekeeping extends BaseHelper
{
    public function __construct() {
        $this->model = TkModel::class;
    }

    /**
     * Lấy bảng công nhân viên theo project và thời gian tương ứng
     *
     * @param $proIds
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     */
    public function getTkWithProject($proIds, $timeStart, $timeEnd)
    {
        $tblTk = TkModel::getTableName();
        $tblProj = Project::getTableName();
        $tblProjMember = ProjectMember::getTableName();
        $tblEmp = Employee::getTableName();
        $tblTkTable = TimekeepingTable::getTableName();
        $tblEmpTeamHistory = EmployeeTeamHistory::getTableName();
        return Project::select(
            "{$tblProj}.id as proj_id",
            "{$tblProj}.name as proj_name",
            'projMember.type as proj_member_type',
            'empTH.team_id',
            'empTH.role_id',
            'tblEmp.id as employee_id',
            'tblEmp.name as employee_name',
            'tblEmp.email as employee_email',
            DB::raw('date(tblEmp.join_date) as join_date'),
            DB::raw('date(tblEmp.trial_date) as trial_date'),
            DB::raw('date(tblEmp.offcial_date) as offcial_date'),
            DB::raw('date(tblEmp.leave_date) as leave_date'),
            'tk.*',
            'tblTkTable.id as tkTable_id',
            'tblTkTable.team_id as tkTable_team_id',
            'tblTkTable.type as contract_type'
        )
        ->leftJoin("{$tblProjMember} as projMember", 'projMember.project_id', '=', "{$tblProj}.id")
        ->leftJoin("{$tblTk} as tk", function($join) use ($tblProj) {
            $join->on('tk.employee_id', '=', "projMember.employee_id")
                ->on('tk.timekeeping_date', '>=', "projMember.start_at")
                ->on('tk.timekeeping_date', '<=', "projMember.end_at")
                ->on('tk.timekeeping_date', '>=', "{$tblProj}.start_at")
                ->on('tk.timekeeping_date', '<=', "{$tblProj}.end_at");
        })
        ->join("{$tblTkTable} as tblTkTable", 'tblTkTable.id', '=', 'tk.timekeeping_table_id')
        ->join("{$tblEmp} as tblEmp", 'tblEmp.id', '=', 'projMember.employee_id')
        ->leftJoin("{$tblEmpTeamHistory} as empTH", 'empTH.employee_id', '=', 'projMember.employee_id')
        ->whereIn("{$tblProj}.id", $proIds)
        ->where("tk.timekeeping_date", '>=', $timeStart)
        ->where("tk.timekeeping_date", '<=', $timeEnd)
        ->where("projMember.status", ProjectMember::STATUS_APPROVED)
        ->where("empTH.is_working", EmployeeTeamHistory::IS_WORKING)
        ->whereNull("projMember.deleted_at")
        ->whereNull("tk.deleted_at")
        ->whereNull("{$tblProj}.deleted_at")
        ->whereNull("tblTkTable.deleted_at")
        ->orderBy('tk.employee_id')
        ->orderBy('tk.timekeeping_date')
        ->groupBy("{$tblProj}.id", 'projMember.employee_id', 'projMember.type', 'tk.id')
        ->get();
    }

    /**
     * return information needed from $collections
     *
     * @param $collections
     * @return array
     */
    public function getJsonTkProj($collections, $registerOt, $registerBusiness, $registerLeaveDays)
    {
        $comDays = []; //$compensationDays
        $holidays = [];
        $employees = [];
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        foreach ($collections as $key => $item) {
            $projId = (int) $item->proj_id;
            $empId = (int) $item->employee_id;
            $date = $item->timekeeping_date;
            $key = $empId . '-' . $projId;
            if (!array_key_exists($item->tkTable_id, $comDays)) {
                $team = Team::getTeamById($item->tkTable_team_id);
                $teamCodePre = Team::getTeamCodePrefix($team->code);
                $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePre);
                $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePre);
                $comDays[$item->tkTable_id] = $compensationDays;
                $holidays[$item->tkTable_id] = [$annualHolidays, $specialHolidays];
            }
            $signFines = ManageTimeCommon::getTimekeepingSign($item, '', $comDays[$item->tkTable_id], $holidays[$item->tkTable_id]);
            $timekeeping = $this->changeSignToTime($signFines[0]);
            if (isset($registerBusiness[$empId][$date])) {
                if (isset($registerLeaveDays[$empId][$date])) {
                    $timekeeping['time'] += $this->calculationTimeBusinessLeaveDay($registerBusiness[$empId][$date], $registerLeaveDays[$empId][$date]);
                } else {
                    $timekeeping['time'] += $this->calculationTimeBusiness($signFines[0]);
                }
            }
            if ($timekeeping['time'] > 8) {
                $timekeeping['time'] = 8; // 1 day have 8 hour
            }
            if (!isset($employees[$key])) {
                $user = [
                    'employee_id' => (int) $item->employee_id,
                    'employee_name' => $item->employee_name,
                    'email' => $item->employee_email,
                    'division' => (int) $item->team_id,
                    'roles' => $item->proj_member_type, // id of: dev, PQA,...
                    'project_id' => $projId,
                ];
                $employees[$key]['user'] = $user;
            } else {
                if (strpos($employees[$key]['user']['roles'], $item->proj_member_type) === false) {
                    $employees[$key]['user']['roles']=
                        $employees[$key]['user']['roles'] . ',' . $item->proj_member_type;
                }
            }
            if (!empty($timekeeping['OT']) && isset($registerOt[$projId][$empId][$item->timekeeping_date])) {
                $regOT = $registerOt[$projId][$empId][$item->timekeeping_date];
                $timekeeping['OT'] = $this->calculationOt($item, $regOT);
            } else {
                $timekeeping['OT'] = 0;
            }
            if (!empty($employees[$key]['timekeeping'][$date])) {
                if (!empty($timekeeping['time'])) {
                    $employees[$key]['timekeeping'][$date]['time'] = $timekeeping['time'];
                }
                if (!empty($timekeeping['OT'])) {
                    $employees[$key]['timekeeping'][$date]['OT'] = $timekeeping['OT'];
                }
                if (!empty($timekeeping['L'])) {
                    $employees[$key]['timekeeping'][$date]['L'] = $timekeeping['L'];
                }
                if (!empty($timekeeping['time']) ||
                    !empty($timekeeping['L']) ||
                    !empty($timekeeping['OT'])) {
                    if (strpos($employees[$key]['timekeeping'][$date]['tk_table_id'], $item->tkTable_id) === false) {
                        $employees[$key]['timekeeping'][$date]['tk_table_id'] =
                            $employees[$key]['timekeeping'][$date]['tk_table_id'] . ',' . $item->tkTable_id;
                    }
                }
            } else {
                $timekeeping['tk_table_id'] = $item->tkTable_id;
                $employees[$key]['timekeeping'][$date] = $timekeeping;
            }
        }
        return $employees;
    }

    /**
     * chuyển đổi từ ký hiệu ra thời gian tương ứng
     *
     * @param $sign
     * @return array
     */
    public function changeSignToTime($sign)
    {
        $data = [
            'time' => 0,
            'OT' => 0,
            'L' => '',
        ];
        if ($sign == '' || $sign == 'V' || $sign == '-') {
            return $data;
        }
        $arrSign = explode(',', $sign);
        foreach ($arrSign as $sign) {
            if (strpos($sign, 'OT') !== false) {
                $arrTime = explode(':', $sign);
                $data['OT'] = trim($arrTime[1]);
            }
            if (strpos($sign, 'OTKL') !== false) {
                $arrTime = explode(':', $sign);
                $data['OT'] = $data['OT'] + number_format($arrTime[1], 1);
            }
            if ($sign == 'L') {
                $data['L'] = 'L';
            }
            if (strpos($sign, 'X') !== false) {
                switch (trim($sign)) {
                    case 'X':
                        $data['time'] = 8;
                        break;
                    case 'X/2':
                        $data['time'] += 4;
                        break;
                    default:
                        $arrTime = explode(':', $sign);
                        $data['time'] += number_format($arrTime[1] * 8, 1);
                }
            }
            if (strpos($sign, 'BS') !== false) {
                switch (trim($sign)) {
                    case 'BS':
                        $data['time'] = 8;
                        break;
                    case 'BS/2':
                        $data['time'] += 4;
                        break;
                    default:
                        $arrTime = explode(':', $sign);
                        $data['time'] += number_format($arrTime[1] * 8, 1);
                }
            }
        }
        return $data;
    }

    /**
     * @param $comDays
     * @param $holidays
     * @param $annualHolidays
     * @param $teamId
     * @param $tkTableId
     */
    private function getComDaysHolidays(&$comDays, &$holidays, $annualHolidays, $teamId, $tkTableId)
    {
        if (!array_key_exists($tkTableId, $comDays)) {
            $team = Team::getTeamById($teamId);
            $teamCodePre = Team::getTeamCodePrefix($team->code);
            $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePre);
            $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePre);
            $comDays[$tkTableId] = $compensationDays;
            $holidays[$tkTableId] = [$annualHolidays, $specialHolidays];
        }
        return;
    }

    /**
     * return information needed of report timekeeping aggregate from $collections
     *
     * @param $collection
     * @param $registerOt
     * @param $supplementOt
     * @return array
     */
    public function getJsonReportTkProj($collections, $registerOt)
    {
        $comDays = []; //$compensationDays
        $holidays = [];
        $projects = [];
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        foreach ($collections as $key => $item) {
            $tkTableId = $item->tkTable_id;
            $projId = $item->proj_id;
            $empId = $item->employee_id;
            $key = $empId . '_' . $item->proj_member_type;

            $this->getComDaysHolidays($comDays, $holidays, $annualHolidays, $item->tkTable_team_id, $tkTableId);
            $signFines = ManageTimeCommon::getTimekeepingSign($item, '', $comDays[$tkTableId], $holidays[$tkTableId]);
            $timekeeping = $this->changeSignToTime($signFines[0]);
            $timeOT = 0;
            if (!empty($timekeeping['OT']) && isset($registerOt[$projId][$empId][$item->timekeeping_date])) {
                $regOT = $registerOt[$projId][$empId][$item->timekeeping_date];
                $timeOT = $this->calculationOt($item, $regOT);
            }
            if (!isset($projects[$projId])) {
                $proj = [
                    'proj_id' => (int) $projId,
                    'proj_name' => $item->proj_name,
                ];
                $projects[$projId] = $proj;
            }
            if (!isset($projects[$projId][$key])) {
                $user = [
                    'employee_id' => (int) $empId,
                    'employee_name' => $item->employee_name,
                    'role' => $item->proj_member_type,
                    'role_name' => ProjectMember::getType($item->proj_member_type),
                    'level' => '',
                ];
                $projects[$projId][$key] = $user;
            }
            if (isset($projects[$projId][$key]['total_time_ot'])) {
                $projects[$projId][$key]['total_time_ot'] += $timeOT;
            } else {
                $projects[$projId][$key]['total_time_ot'] = $timeOT;
            }
            if (isset($projects[$projId][$key]['total_time_working'])) {
                if (!empty($timekeeping['time'])) {
                    $projects[$projId][$key]['total_time_working'] += $timekeeping['time'];
                    if (strpos($projects[$projId][$key]['tk_table_id'], $tkTableId) === false) {
                        $projects[$projId][$key]['tk_table_id'] =
                        $projects[$projId][$key]['tk_table_id'] . ',' . $tkTableId;
                    }
                }
            } else {
                $projects[$projId][$key]['total_time_working'] = $timekeeping['time'];
                $projects[$projId][$key]['tk_table_id'] = $tkTableId;
            }
        }
        return $projects;
    }

    /**
     * @param $empIds
     * @param $proIds
     * @param $timeStart
     * @param $timeEnd
     * @return mixed
     */
    public function getRegisterOTByProject($empIds, $proIds, $timeStart, $timeEnd)
    {
        $tblOt = OtRegister::getTableName();
        $tblOtEmp = OtEmployee::getTableName();

        return OtEmployee::select(
            "{$tblOtEmp}.ot_register_id",
            "{$tblOtEmp}.employee_id",
            "{$tblOtEmp}.start_at",
            "{$tblOtEmp}.end_at",
            "{$tblOtEmp}.is_paid",
            "{$tblOtEmp}.time_break",
            'tblOt.projs_id',
            'tblOt.is_onsite'
        )
        ->leftJoin("{$tblOt} as tblOt", 'tblOt.id', '=', "{$tblOtEmp}.ot_register_id")
        ->whereIn('tblOt.projs_id', $proIds)
        ->whereIn("{$tblOtEmp}.employee_id", $empIds)
        ->whereDate("{$tblOtEmp}.start_at", '>=', $timeStart)
        ->whereDate("{$tblOtEmp}.end_at", '<=', $timeEnd)
        ->where('tblOt.status', OtRegister::DONE)
        ->whereNull('tblOt.deleted_at')
        ->get();
    }

    /**
     * @param $empIds
     * @param $proIds
     * @param $timeStart
     * @param $timeEnd
     * @return array
     */
    public function getRegisterOTByProjectKeyProj($empIds, $proIds, $timeStart, $timeEnd)
    {
        $collection = $this->getRegisterOTByProject($empIds, $proIds, $timeStart, $timeEnd);

        $arrOt = [];
        if (!count($collection)) {
            return $arrOt;
        }
        foreach ($collection as $key => $item) {
            $date = Carbon::parse($item->start_at); //ot chỉ đăng ký dk 1 ngày
            $arrOt[$item->projs_id][$item->employee_id][$date->format('Y-m-d')][] = [
                'start_at' => $item->start_at,
                'end_at' => $item->end_at,
                'time_break' => $item->time_break,
                'is_onsite' => $item->is_onsite,
                'is_paid' => $item->is_paid,
            ];
        }
        return $arrOt;
    }

    /**
     * @param $empIds
     * @param $proIds
     * @param $timeStart
     * @param $timeEnd
     * @return array
     */
    public function getRegisterOTByProjectKeyEmp($empIds, $proIds, $timeStart, $timeEnd)
    {
        $collection = $this->getRegisterOTByProject($empIds, $proIds, $timeStart, $timeEnd);

        $arrOt = [];
        if (!count($collection)) {
            return $arrOt;
        }
        foreach ($collection as $key => $item) {
            $date = Carbon::parse($item->start_at); //ot chỉ đăng ký dk 1 ngày
            $arrOt[$item->employee_id][$date->format('Y-m-d')][] = [
                'start_at' => $item->start_at,
                'end_at' => $item->end_at,
                'time_break' => $item->time_break,
                'is_onsite' => $item->is_onsite,
                'is_paid' => $item->is_paid,
            ];
        }
        return $arrOt;
    }

    /**
     * @param $empIds
     * @param $timeStart
     * @param $timeEnd
     * @return array
     */
    public function getSupplementOt($empIds, $timeStart, $timeEnd)
    {
        $collection = SupplementRegister::select(
            'creator_id as employee_id',
            'date_start',
            'date_end',
            'number_days_supplement',
            'is_ot'
        )
        ->where('status', '=', SupplementRegister::STATUS_APPROVED)
        ->where('is_ot', SupplementRegister::IS_OT)
        ->whereIn('creator_id', $empIds)
        ->whereDate('date_start', '>=', $timeStart)
        ->whereDate('date_end', '<=', $timeEnd)
        ->get();

        $arrSupp = [];
        if (!count($collection)) {
            return $arrSupp;
        }
        foreach ($collection as $key => $item) {
            $date = Carbon::parse($item->date_start); //BSC ot chỉ đăng ký dk 1 ngày
            $arrSupp[$item->employee_id][$date->format('Y-m-d')][] = [
                'date_start' => $item->date_start,
                'date_end' => $item->date_end,
                'number_days_supp' => $item->number_days_supplement,
                'is_ot' => $item->is_ot,
            ];
        }
        return $arrSupp;
    }

    /**
     * @param $item
     * @param $arrRegOT
     * @return float|int
     */
    public function calculationOt($item, $arrRegOT)
    {
        $timeOt = 0;
        if (!$arrRegOT) {
            return $timeOt;
        }
        if (!empty($item->start_time_morning_shift)) {
            $timeIn = $item->start_time_morning_shift;
        } elseif (!empty($item->start_time_afternoon_shift)) {
            $timeIn = $item->start_time_afternoon_shift;
        } else {
            $timeIn = '';
        }

        if (!empty($item->end_time_afternoon_shift)) {
            $timeOut = $item->end_time_afternoon_shift;
        } elseif (!empty($item->end_time_morning_shift)) {
            $timeOut = $item->end_time_morning_shift;
        } else {
            $timeOut = '';
        }
        foreach ($arrRegOT as $regOT) {
            $timeInOT = Carbon::parse($regOT['start_at'])->format('H:i');
            $timeOutOT = Carbon::parse($regOT['end_at'])->format('H:i');
            if ($regOT['is_onsite'] == OtRegister::IS_ONSITE) {
                $timeOt += $this->calculationTime($timeInOT, $timeOutOT, $regOT['time_break']);
                continue;
            }
            if ($timeIn == '' || $timeOut == '') {
                continue;
            }
            if ($timeInOT < $timeIn) {
                $timeInOT = $timeIn;
            }
            if ($timeOutOT > $timeOut) {
                $timeOutOT = $timeOut;
            }
            $timeOt += $this->calculationTime($timeInOT, $timeOutOT, $regOT['time_break']);
        }
        return $timeOt;
    }

    /**
     * calculation time
     * @param $timeStart
     * @param $timeEnd
     * @param $timeBreak
     * @return float|int
     */
    public function calculationTime($timeStart, $timeEnd, $timeBreak)
    {
        $time1 = Carbon::parse($timeStart);
        $time2 = Carbon::parse($timeEnd);
        $diff = $time1->diffInSeconds($time2);
        $diff = Carbon::createFromFormat('H:i', gmdate('H:i', $diff));
        $time =  $diff->hour * 60 + $diff->minute;
        $time = $time - ($timeBreak * 60);
        if ($time <= 0) {
            return 0;
        }
        return $time / 60;
    }

    /**
     * @param $empIds
     * @param $dateStart
     * @param $dateEnd
     * @return Collection
     */
    public function getRegisterLeaveDay($empIds, $dateStart, $dateEnd)
    {
        $team = Team::getTableName();
        $leaveDay = LeaveDayRegister::getTableName();
        $teamMember = TeamMember::getTableName();
        return LeaveDayRegister::select(
            "{$leaveDay}.id",
            "{$leaveDay}.creator_id",
            "{$leaveDay}.date_start",
            "{$leaveDay}.date_end",
            "{$leaveDay}.status",
            "team.branch_code"
        )
        ->leftJoin("{$teamMember} as tm", 'tm.employee_id', '=',  "{$leaveDay}.creator_id")
        ->leftJoin("{$team} as team", 'team.id', '=', 'tm.team_id')
        ->where('status', '=', LeaveDayRegister::STATUS_APPROVED)
        ->whereIn('creator_id', $empIds)
        ->whereDate('date_start', '<', $dateEnd)
        ->whereDate('date_end', '>', $dateStart)
        ->groupBy("{$leaveDay}.id")
        ->get();
    }

    /**
     * @param $empIds
     * @param $dateStart
     * @param $dateEnd
     * @return Collection
     */
    public function getRegisterBusiness($empIds, $dateStart, $dateEnd)
    {
        $team = Team::getTableName();
        $business = BusinessTripRegister::getTableName();
        $businessEmp = BusinessTripEmployee::getTableName();
        $teamMember = TeamMember::getTableName();

        return BusinessTripRegister::select(
            "{$business}.id",
            "busEmp.employee_id as creator_id",
            "busEmp.start_at as date_start",
            "busEmp.end_at as date_end",
            "{$business}.status",
            "team.branch_code"
        )
        ->leftJoin("{$businessEmp} as busEmp", 'busEmp.register_id', '=', "{$business}.id")
        ->leftJoin("{$teamMember} as tm", 'tm.employee_id', '=', "busEmp.employee_id")
        ->leftJoin("{$team} as team", 'team.id', '=', 'tm.team_id')
        ->where('status', '=', BusinessTripRegister::STATUS_APPROVED)
        ->whereIn("busEmp.employee_id", $empIds)
        ->whereDate('start_at', '<', $dateEnd)
        ->whereDate('end_at', '>', $dateStart)
        ->groupBy("busEmp.employee_id")
        ->groupBy("{$business}.id")
        ->get();
    }

    /**
     * @param $empIds
     * @param $dateStart
     * @param $dateEnd
     * @return Collection
     */
    public function getRegisterSupplement($empIds, $dateStart, $dateEnd)
    {
        $team = Team::getTableName();
        $sup = SupplementRegister::getTableName();
        $teamMember = TeamMember::getTableName();
        return SupplementRegister::select(
            "{$sup}.id",
            "{$sup}.creator_id",
            "{$sup}.date_start",
            "{$sup}.date_end",
            "{$sup}.status",
            "team.branch_code"
        )
            ->leftJoin("{$teamMember} as tm", 'tm.employee_id', '=',  "{$sup}.creator_id")
            ->leftJoin("{$team} as team", 'team.id', '=', 'tm.team_id')
            ->where('status', '=', SupplementRegister::STATUS_APPROVED)
            ->whereIn('creator_id', $empIds)
            ->whereDate('date_start', '<', $dateEnd)
            ->whereDate('date_end', '>', $dateStart)
            ->get();
    }

    /**
     * xử lý chuyển các đơn về dạng mảng tương ứng
     *
     * @param $registers
     * @return array
     */
    public function getArrayRegisterKeyEmp($registers)
    {
        $employees = [];
        if (!count($registers)) {
            return $employees;
        }
        foreach ($registers as $item) {
            $dateStart = Carbon::parse($item->date_start);
            $dateEnd = Carbon::parse($item->date_end);
            $team = $this->getTeamByBranch($item->branch_code);
            $arrDate = $this->getArrayDateRegister($dateStart, $dateEnd, $team);
            if (isset($employees[$item->creator_id])) {
                $empId = $item->creator_id;
                foreach ($arrDate as $date => $values) {
                    if (isset($employees[$empId][$date])) {
                        foreach ($values as $value) {
                            $employees[$empId][$date][] = $value;
                        }
                    } else {
                        $employees[$empId][$date] = $values;
                    }
                }
            } else {
                $employees[$item->creator_id] = $arrDate;
            }
        }
        return $employees;
    }

    /**
     * get array employee register leave day
     * @param $empIds
     * @param $dateStart
     * @param $dateEnd
     * @return array
     */
    public function getRegisterLeaveDayKeyEmp($empIds, $dateStart, $dateEnd)
    {
        $registers = $this->getRegisterLeaveDay($empIds, $dateStart, $dateEnd);
        return $this->getArrayRegisterKeyEmp($registers);
    }

    /**
     * get array employee register supplement
     * @param $empIds
     * @param $dateStart
     * @param $dateEnd
     * @return array
     */
    public function getRegisterSuppKeyEmp($empIds, $dateStart, $dateEnd)
    {
        $registers = $this->getRegisterLeaveDay($empIds, $dateStart, $dateEnd);
        return $this->getArrayRegisterKeyEmp($registers);
    }

    /**
     * get array employee business
     * @param $empIds
     * @param $dateStart
     * @param $dateEnd
     * @return array
     */
    public function getRegisterBusinessKeyEmp($empIds, $dateStart, $dateEnd)
    {
        $registers = $this->getRegisterBusiness($empIds, $dateStart, $dateEnd);
        return $this->getArrayRegisterKeyEmp($registers);
    }

    /**
     * get team
     * get time working for team
     * @param $branch
     * @return string
     */
    public function getTeamByBranch($branch)
    {
        switch ($branch) {
            case Team::CODE_PREFIX_HCM:
                $team = Team::CODE_PREFIX_HCM;
                break;
            case Team::CODE_PREFIX_JP:
                $team = Team::CODE_PREFIX_JP;
                break;
            default:
                $team = Team::CODE_PREFIX_HN;
                break;
        }
        return $team;
    }

    /**
     * Thời gian đăng ký cho từng ngày
     * @param $dateStart
     * @param $dateEnd
     * @param $team
     * @return array
     */
    public function getArrayDateRegister($dateStart, $dateEnd, $team)
    {
        $cpStart = clone $dateStart;
        $array = [];
        $objView = new ViewManage();
        while ($dateStart <= $dateEnd) {
            $working = $objView->getTimeWorkingQuater([], $team, $dateStart->format("Y-m-d"));
            $lunch = $working['timeInAfter'][0]->diffInMinutes($working['timeOutMor'][1]);
            $data = [];
            if ($cpStart->format("Y-m-d") == $dateStart->format("Y-m-d") &&
                $dateStart->format("Y-m-d") == $dateEnd->format("Y-m-d")) {
                $keyDate = $dateStart->format("Y-m-d");
                $data = [
                    'start' => clone $dateStart,
                    'end' => $dateEnd,
                ];
            } elseif ($cpStart->format("Y-m-d") == $dateStart->format("Y-m-d") &&
                $dateStart->format("Y-m-d") < $dateEnd->format("Y-m-d")) {
                $keyDate = $dateStart->format("Y-m-d");
                $data = [
                    'start' => clone $dateStart,
                    'end' => $working['timeOutAfter'][1],
                ];
            } elseif ($cpStart->format("Y-m-d") < $dateStart->format("Y-m-d") &&
                $dateStart->format("Y-m-d") < $dateEnd->format("Y-m-d")) {
                $keyDate = $dateStart->format("Y-m-d");
                $data = [
                    'start' => $working['timeInMor'][0],
                    'end' => $working['timeOutAfter'][1],
                ];
            } elseif ($cpStart->format("Y-m-d") < $dateStart->format("Y-m-d") &&
                $dateStart->format("Y-m-d") == $dateEnd->format("Y-m-d")) {
                $keyDate = $dateStart->format("Y-m-d");
                $data = [
                    'start' => $working['timeInMor'][0],
                    'end' => clone $dateEnd,
                ];
            } else {

            }
            if ($data) {
                $data['lunch'] = 0;
                if ($data['start']->format('H:i') < $working['timeOutMor'][1]->format('H:i') &&
                    $data['end']->format('H:i') > $working['timeInAfter'][0]->format('H:i')) {
                    $data['lunch'] = $lunch;
                }
                $array[$keyDate][] = $data;
            }
            $dateStart->addDay();
        }
        return $array;
    }

    /**
     * tinh lai cong tac khi co don phep
     * @param $registerBusiness
     * @param $registerLeaveDays
     * @return int|string
     */
    public function calculationTimeBusinessLeaveDay($registerBusiness, $registerLeaveDays)
    {
        $time = 0;
        foreach ($registerBusiness as $business) {
            $time += ($business['end']->diffInMinutes($business['start']) - $business['lunch']);
            $busStart = $business['start']->format('H:i');
            $busEnd = $business['end']->format('H:i');
            foreach ($registerLeaveDays as $leaveDay) {
                $leaveStart = $leaveDay['start']->format('H:i');
                $leaveEnd = $leaveDay['end']->format('H:i');
                if ($time != 0 && $leaveStart < $busEnd && $leaveEnd > $busStart) {
                    if ($leaveStart >= $busStart && $leaveEnd >= $busEnd) {
                        $time = 0;
                    } elseif ($leaveStart >= $busStart &&
                        $busEnd < $leaveEnd &&
                        $leaveEnd <= $busEnd) {
                        $time -= $leaveStart['start']->$busStart($leaveDay['start']);
                    } elseif ($leaveStart <= $busStart &&
                        $busStart < $leaveEnd &&
                        $leaveEnd <= $busEnd) {
                        $time -= $business['start']->diffInMinutes($leaveDay['end']);
                    } elseif ($leaveStart >= $busStart &&
                        $leaveEnd <= $busEnd) {
                        $time -= $leaveDay['start']->diffInMinutes($business['start']);
                        $time -= $business['end']->diffInMinutes($leaveDay['end']);
                    } else {

                    }
                }
            }
        }
        $time = number_format($time / 60, 1);
        return $time <= 0 ? 0 : $time;
    }

    /**
     * tinh cong tac khi khong co phep
     * @param $sign
     * @return int|string
     */
    public function calculationTimeBusiness($sign)
    {
        $time = 0;
        $arrSign = explode(',', $sign);
        foreach ($arrSign as $sign) {
            if (strpos($sign, 'CT') !== false) {
                switch (trim($sign)) {
                    case 'CT':
                        $time = 8;
                        break;
                    case 'CT/2':
                        $time += 4;
                        break;
                    default:
                        $arrTime = explode(':', $sign);
                        $time += number_format($arrTime[1] * 8, 1);
                        break;
                }
            }
        }
        return $time;
    }

    public function updateRelatedPerson($params)
    {
        $userId = $params['current_user_id'];
        $userCurrent = Employee::find($userId);
        if ($userCurrent) {
            foreach ($params as $key => $item) {
                if (in_array($key, ['p', 'bsc', 'ot', 'ct'])) {
                    switch ($key) {
                        case 'p':
                            $listRegisterIdApprove = LeaveDayRegister::getRegisterByStatus($item, null);
                            $model = '\Rikkei\ManageTime\Model\LeaveDayRegister';
                            break;
                        case 'bsc':
                            $listRegisterIdApprove = SupplementRegister::getRegisterByStatus($item, null);
                            $model = '\Rikkei\ManageTime\Model\SupplementRegister';
                            break;
                        case 'ot':
                            $listRegisterIdApprove = OtRegister::whereIn('id', $item)->get()->pluck('id')->toArray();
                            $model = '\Rikkei\Ot\Model\OtRegister';
                            break;
                        case 'ct':
                            $listRegisterIdApprove = BusinessTripRegister::getRegisterByStatus($item, null);
                            $model = '\Rikkei\ManageTime\Model\BusinessTripRegister';
                            break;
                    }
                    if (count($listRegisterIdApprove)) {
                        $dataTK = [];
                        foreach ($listRegisterIdApprove as $registerId) {
                            $registerRecord = $model::getInformationRegister($registerId);
                            if ($registerRecord) {
                                $dataTK[] = $registerRecord;
                            }
                        };
                        $this->insertTimekeeping($userCurrent, $dataTK);
                    }
                }
            }
            return [
                'success' => 1,
            ];
        }
    }

    public function insertTimekeeping($userCurrent, $dataLeaveDayTK)
    {
        $objView = new ManageTimeView();
        return $objView->insertTimekeeping($userCurrent, $dataLeaveDayTK);
    }
}
