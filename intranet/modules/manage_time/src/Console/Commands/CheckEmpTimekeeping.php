<?php

namespace Rikkei\ManageTime\Console\Commands;;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\ManageTime\Http\Controllers\TimekeepingController;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\ManageTime\Model\TimekeepingAggregate;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;

class CheckEmpTimekeeping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timekeeping:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra nhân viên được insert mới và update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * calculate time working
     * // lấy trong tháng hiện tại
     * @return mixed
     */
    public function handle()
    {
        try {
            $now = Carbon::now();
            $after = clone $now;
            $startDateTime = $after->subday()->format('Y-m-d 22:30:00');
            $endDateTime = $now->format('Y-m-d H:i:s');
            // get employe update to day
            $offcialEmps = $this->getEmpTraiOffcial($startDateTime, $endDateTime);
            // update - nhân viên mùa vụ
            $teamporaryEmps = $this->getEmpTeamporaryContact($startDateTime, $endDateTime);

            Log::info('=== Start check timkeeping table ===');
            if (!count($offcialEmps) && !count($teamporaryEmps)) {
                Log::info('=== không có nhân viên mới hoặc update ===');
                Log::info('=== End check timkeeping table ===');
                return;
            }
            $arrTeamPrefix = $this->getInforCodePrefix();
            $arrCompen = $this->getCompenByTeam($arrTeamPrefix);
            $arrHoliday = $this->getHoliday($arrTeamPrefix);
            $dataTrailOffcial = [];
            //=== update or insert nhân viên công ===
            if (count($offcialEmps)) {
                $dataInsert = [];
                $empIds = $offcialEmps->lists('id')->toArray();
                $teamPrefixEmpIds = $this->getTeamPrefixByEmps($empIds);
                $timeKeepingTable = new TimekeepingTable();
                $tKTables = $timeKeepingTable->getTimekeepingTableByTimeType($now, getOptions::WORKING_OFFICIAL);
                $typesOffcial = \Rikkei\Resource\View\getOptions::typeEmployeeOfficial();
                $empIdNotTeam = [];
                $EmpIdsCalculateAgain = [];
                foreach ($tKTables as $tKTable) {
                    $tkEmps = $this->getTimeKeeping($empIds, $tKTable->id);
                    $empIdExists = [];
                    if (count($tkEmps)) {
                        $empIdExists = $tkEmps->lists('employee_id')->toArray();
                    }
                    $insertEmps = array_diff($empIds, $empIdExists);
                    $updateEmps = array_diff($empIds, $insertEmps);
                    $insertEmpsLog = [];
                    $updateEmpsLog = [];
                    if (count($insertEmps)) {
                        foreach ($offcialEmps as $emp) {
                            if (!array_key_exists($emp->id, $teamPrefixEmpIds)) {
                                $empIdNotTeam[] = $emp->id;
                                continue;
                            }
                            $teamPrefix = $teamPrefixEmpIds[$emp->id];
                            if (strpos(strtolower($tKTable->team_code), strtolower($teamPrefix)) === false || !in_array($emp->id, $insertEmps)) {
                                continue;
                            }
                            $insertEmpsLog[] = $emp->id;
                            if (!array_key_exists($tKTable->id, $dataInsert)) {
                                $dataInsert[$tKTable->id] = [
                                    'id' => $tKTable->id,
                                    'month' => $tKTable->month,
                                    'year' => $tKTable->year,
                                    'start_date' => $tKTable->start_date,
                                    'end_date' => $tKTable->end_date,
                                    'team_code' => $tKTable->team_code,
                                    'type' => $tKTable->type,
                                ];
                            }
                            $dataInsert[$tKTable->id]['emps'][$emp->id] = $emp;
                        }
                        Log::info('_insert offcial table id: ' . $tKTable->id . ' empids: '.  implode(',', $insertEmpsLog));
                    }
                    if (count($empIdNotTeam)) {
                        Log::info('employee not team: ' . implode(',', $empIdNotTeam));
                    }
                    if (count($updateEmps)) {
                        // tìm kiếm nhân viên trong bảng công để kiểm tra
                        // lễ, chính thức thử việc, công làm việc
                        foreach ($offcialEmps as $emp) {
                            if (!array_key_exists($emp->id, $teamPrefixEmpIds)) {
                                $empIdNotTeam[] = $emp->id;
                                continue;
                            }
                            $teamPrefix = $teamPrefixEmpIds[$emp->id];
                            if (strpos(strtolower($tKTable->team_code), strtolower($teamPrefix)) === false || !in_array($emp->id, $updateEmps)) {
                                continue;
                            }
                            $updateEmpsLog[] = $emp->id;
                            $EmpIdsCalculateAgain[] = $emp->id;
                            $compensationDays = $arrCompen[$teamPrefixEmpIds[$emp->id]];
                            $holiday = $arrHoliday[$teamPrefixEmpIds[$emp->id]];

                            $timekeepingTableStartDate = clone $now->firstOfMonth();
                            $timekeepingTableEndDate = clone $now->endOfMonth();

                            while (strtotime($timekeepingTableStartDate) <= strtotime($timekeepingTableEndDate)) {
                                $keyTrailOffcial = $emp->id . '_' . $timekeepingTableStartDate->format('Y-m-d');
                                $date = $timekeepingTableStartDate->format('Y-m-d');
                                $dataUpdate = [];
                                $dataUpdate['is_official'] = 0;
                                if ($emp->offcial_date && strtotime($date) >= strtotime(Carbon::parse($emp->offcial_date)->format('Y-m-d'))) {
                                    $dataUpdate['is_official'] = 1;
                                    foreach ($compensationDays["com"] as $key => $compenstaion) {
                                        if (strtotime(Carbon::parse($compenstaion)->format('Y-m-d')) === strtotime($date)) {
                                            if (strtotime(Carbon::parse($compensationDays["lea"][$key])->format('Y-m-d')) < strtotime(Carbon::parse($emp->offcial_date)->format('Y-m-d'))
                                                && strtotime(Carbon::parse($compensationDays["lea"][$key])->format('Y-m-d')) >= strtotime(Carbon::parse($emp->join_date)->format('Y-m-d'))
                                                && $emp->trial_date && strtotime(Carbon::parse($compensationDays["lea"][$key])->format('Y-m-d')) >= strtotime(Carbon::parse($emp->trial_date)->format('Y-m-d'))) {
                                                $dataUpdate['is_official'] = 0;
                                            }
                                        }
                                    }
                                }

                                // holiday
                                if ((in_array($timekeepingTableStartDate->format('m-d'), $holiday['annualHolidays']) ||
                                    in_array($date, $holiday['specialHolidays'])) &&
                                    (((empty($emp->trial_date) && (!empty($emp->offcial_date) || strtotime($date) >= strtotime($emp->offcial_date)))
                                        || (!empty($emp->trial_date) && strtotime($date) >= strtotime($emp->trial_date)))
                                    && in_array($tKTable->type, $typesOffcial))) {
                                    $dataUpdate['timekeeping'] = ManageTimeConst::HOLIDAY_TIME;
                                    $dataUpdate['timekeeping_number'] = ManageTimeConst::FULL_TIME;
                                    if ($emp->leave_date != null) {
                                        $leave = Carbon::parse($emp->leave_date)->format('Y-m-d');
                                        if ($leave < $date) {
                                            $dataUpdate['timekeeping'] = 0;
                                            $dataUpdate['timekeeping_number'] = 0;
                                        }
                                    }
                                }
                                $dataTrailOffcial[$keyTrailOffcial] = $dataUpdate;
                                $timekeepingTableStartDate = $timekeepingTableStartDate->addDay();
                            }
                        }
                        Log::info('_update trail-offcial id: ' . $tKTable->id . ' empids: '.  implode(',', $updateEmpsLog));
                        if (count($empIdNotTeam)) {
                            Log::info('employee not team: ' . implode(',', $empIdNotTeam));
                        }
                        // tính lại công của nhân viên
                        if (count($EmpIdsCalculateAgain)) {
                            $calculateTimeWokingDayAll = new CalculateTimeWokingDayAll();
                            $calculateTimeWokingDayAll->calculateAllMonth($now, $EmpIdsCalculateAgain, true);

                            // cập nhật dữ liệu liên quan.
                            TimekeepingController::setDataRelated($tKTable->id, $EmpIdsCalculateAgain);
                        }
                        if (count($dataTrailOffcial)) {
                            $this->updateData($dataTrailOffcial);
                        }
                        $EmpIdsCalculateAgain = array_unique(array_merge($EmpIdsCalculateAgain, $updateEmps));
                        if (count($EmpIdsCalculateAgain)) {
                            $this->timekeepingAggregate($tKTable->id, $EmpIdsCalculateAgain);
                        }
                    }
                }
            }
            // === insert nhân viên mùa vụ ====
            if (count($teamporaryEmps)) {
                $empIds = $teamporaryEmps->lists('id')->toArray();
                $teamPrefixEmpIds = $this->getTeamPrefixByEmps($empIds);
                $timeKeepingTable = new TimekeepingTable();
                $tKTables = $timeKeepingTable->getTimekeepingTableByTimeType($now, getOptions::WORKING_PARTTIME);
                foreach ($tKTables as $tKTable) {
                    $tkEmps = $this->getTimeKeeping($empIds, $tKTable->id);
                    $empIdExists = [];
                    if (count($tkEmps)) {
                        $empIdExists = $tkEmps->lists('employee_id')->toArray();
                    }
                    $insertEmps = array_diff($empIds, $empIdExists);
                    $updateEmps = array_diff($empIds, $insertEmps);
                    $insertEmpsLog = [];
                    if (count($insertEmps)) {
                        foreach ($teamporaryEmps as $emp) {
                            $teamPrefix = $teamPrefixEmpIds[$emp->id];
                            if (strpos(strtolower($tKTable->team_code), strtolower($teamPrefix)) === false || !in_array($emp->id, $insertEmps)) {
                                continue;
                            }
                            $insertEmpsLog[] = $emp->id;
                            if (!array_key_exists($tKTable->id, $dataInsert)) {
                                $dataInsert[$tKTable->id] = [
                                    'id' => $tKTable->id,
                                    'month' => $tKTable->month,
                                    'year' => $tKTable->year,
                                    'start_date' => $tKTable->start_date,
                                    'end_date' => $tKTable->end_date,
                                    'team_code' => $tKTable->team_code,
                                    'type' => $tKTable->type,
                                ];
                            }
                            $dataInsert[$tKTable->id]['emps'][$emp->id] = $emp;
                        }
                        Log::info('insert temporary table id: ' . $tKTable->id . ' empids: '.  implode(',', $insertEmpsLog));
                    }
                    if (count($updateEmps)) {
                        $EmpIdsCalculateAgain = [];
                        foreach ($teamporaryEmps as $emp) {
                            if (!array_key_exists($emp->id, $teamPrefixEmpIds)) {
                                $empIdNotTeam[] = $emp->id;
                                continue;
                            }
                            $teamPrefix = $teamPrefixEmpIds[$emp->id];
                            if (strpos(strtolower($tKTable->team_code), strtolower($teamPrefix)) === false || !in_array($emp->id, $updateEmps)) {
                                continue;
                            }
                            $EmpIdsCalculateAgain[] = $emp->id;
                        }
                        Log::info('_update  temporary table id: ' . $tKTable->id . ' empids: '.  implode(',', $EmpIdsCalculateAgain));
                        // tính lại công của nhân viên
                        if (count($EmpIdsCalculateAgain)) {
                            $calculateTimeWokingDayAll = new CalculateTimeWokingDayAll();
                            $calculateTimeWokingDayAll->calculateAllMonth($now, $EmpIdsCalculateAgain, true);

                            // cập nhật dữ liệu liên quan.
                            TimekeepingController::setDataRelated($tKTable->id, $EmpIdsCalculateAgain);
                            // Tổng hợp công
                            $this->timekeepingAggregate($tKTable->id, $EmpIdsCalculateAgain);
                        }
                    }
                }
            }
            if (count($dataInsert)) {
                $this->insertEmpTimeKeeping($dataInsert);
            }
            Log::info('=== End check timkeeping table ===');
        } catch (Exception $e) {
            $this->info($e->getMessage());
            Log::error($e);
        }
    }

    /**
     * get employee update and insert to day
     * @param  [datetime] $startDateTime
     * @param  [datetime] $endDateTime
     * @return [type]
     */
    public function getEmpTraiOffcial($startDateTime, $endDateTime)
    {
        $now = Carbon::now();
        $tblEmp = Employee::getTableName();
        $empWorkTbl = EmployeeWork::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();

        return Employee::select(
               "{$tblEmp}.id",
               "{$tblEmp}.name",
               "{$tblEmp}.email",
               'join_date',
               'trial_date',
               'trial_end_date',
               'offcial_date',
               "{$tblEmp}.leave_date",
               "{$empWorkTbl}.contract_type"
            )
            ->join("{$empWorkTbl}", "{$tblEmp}.id", "=", "{$empWorkTbl}.employee_id")
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", "=", "{$tblEmp}.id")
            ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
            ->whereDate("{$tblEmp}.updated_at", '<=', $endDateTime)
            ->whereDate("{$tblEmp}.updated_at", '>=', $startDateTime)
            ->where(function ($query) use ($now) {
            $query->whereNull("leave_date")
                ->orWhereDate("leave_date", '>=', $now->startOfMonth()->format('Y-m-d'));
            })
            ->where(function ($query) use ($tblEmp, $now) {
                $query->whereDate("{$tblEmp}.trial_date", '<=', $now->endOfMonth()->format('Y-m-d'))
                    ->orWhereDate("{$tblEmp}.offcial_date", '<=', $now->endOfMonth()->format('Y-m-d'));
            })
            ->whereDate("{$tblEmp}.join_date", "<=", $now->endOfMonth()->format('Y-m-d'))
            ->whereNotIn("{$tblEmp}.account_status", [getOptions::FAIL_CDD])
            ->whereNull("{$tblEmp}.deleted_at")
            ->where("{$tblTeam}.code", 'NOT LIKE', Team::CODE_PREFIX_JP . '%')
            ->groupBy("{$tblEmp}.id")
            ->get();
    }

    /**
     * get employee when employee have timekeeping
     * @param  [array] $empIds
     * @param  [int] $tkTableId
     * @return [type]
     */
    public function getTimeKeeping($empIds, $tkTableId)
    {
        return Timekeeping::whereIn('employee_id', $empIds)
            ->where('timekeeping_table_id', $tkTableId)
            ->groupBy('employee_id')
            ->get();
    }

    /**
     * Get only prefix team code of employee in array team code
     *
     * @param array $empIds
     * @return $array
     */
    public function getTeamPrefixByEmps($empIds)
    {
        $prefixByEmpCode = [];
        $sql = "(CASE
                    WHEN LOCATE('_', code) = 0 THEN code
                    ELSE SUBSTRING(teams.code, 1, LOCATE('_', code) - 1)
                    END
                ) AS `code`";
        if (!empty($empIds)) {
            $listTeam = Team::join('team_members', 'team_members.team_id', '=', 'teams.id')
                ->whereIn('team_members.employee_id', $empIds)
                ->select('team_members.team_id', 'employee_id', DB::raw($sql))
                ->groupBy(['team_members.team_id', 'team_members.employee_id'])
                ->get();
            if ($listTeam) {
                foreach ($listTeam as $item) {
                    if (!isset($prefixByEmpCode[$item->employee_id])) {
                        $prefixByEmpCode[$item->employee_id][] = $item->code;
                    }
                    if ($prefixByEmpCode[$item->employee_id] && $item->code && !in_array($item->code, $prefixByEmpCode[$item->employee_id])) {
                        $prefixByEmpCode[$item->employee_id][] = $item->code;
                    }
                }
            }
        }
        $prefixByEmpId = [];
        foreach ($prefixByEmpCode as $key => $item) {
            if (!empty($item)) {
                if (in_array(Team::CODE_PREFIX_JP, $item)) {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_JP;
                } elseif (in_array(Team::CODE_PREFIX_DN, $item)) {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_DN;
                } elseif (in_array(Team::CODE_PREFIX_HCM, $item)) {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_HCM;
                } elseif (in_array(Team::CODE_PREFIX_AI, $item)) {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_AI;
                } elseif (in_array(Team::CODE_PREFIX_RS, $item)) {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_RS;
                } else {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_HN;
                }
            } else {
                $prefixByEmpId[$key] = Team::CODE_PREFIX_HN;
            }
        }
        return $prefixByEmpId;
    }

    /**
     * get all team code prefix
     * @return [array]
     */
    public function getInforCodePrefix()
    {
        $arr = [];
        $selfTable = Team::getTableName();
        $arrTeamPrefix = [
            Team::CODE_PREFIX_JP,
            Team::CODE_PREFIX_DN,
            Team::CODE_PREFIX_HCM,
            Team::CODE_PREFIX_HN,
        ];
        $collection = Team::select("{$selfTable}.id", "{$selfTable}.name", "{$selfTable}.code")
            ->whereIn("{$selfTable}.code", $arrTeamPrefix)
            ->get();
        foreach ($collection as $item) {
            $arr[$item->code] = $item->id;
        }
        return $arr;
    }

    /**
     * get information compensation day by team
     * @param  [type] $arrTeam
     * @return [type]
     */
    public function getCompenByTeam($arrTeam)
    {
        $arr = [];
        foreach ($arrTeam as $key => $value) {
            $team = Team::getTeamById($value);
            $teamCodePrefix = Team::getTeamCodePrefix($team->code);
            $teamCodePrefix = Team::changeTeam($teamCodePrefix);
            $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);

            $arr[$key] = $compensationDays;
        }
        if (array_key_exists(Team::CODE_PREFIX_HN, $arr)) {
            $arr[Team::CODE_PREFIX_AI] = $arr[Team::CODE_PREFIX_HN];
        }
        if (array_key_exists(Team::CODE_PREFIX_HCM, $arr)) {
            $arr[Team::CODE_PREFIX_RS] = $arr[Team::CODE_PREFIX_HCM];
        }
        return $arr;
    }

    /**
     * [getHoliday description]
     * @param  [type] $arrTeam [description]
     * @return [type]          [description]
     */
    public function getHoliday($arrTeam)
    {
        $arr = [];
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        foreach ($arrTeam as $key => $value) {
            $team = Team::getTeamById($value);
            $teamCodePrefix = Team::getTeamCodePrefix($team->code);
            $teamCodePrefix = Team::changeTeam($teamCodePrefix);
            $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);

            $arr[$key] = [
                'annualHolidays' => $annualHolidays,
                'specialHolidays' => $specialHolidays,
            ];
        }
        if (array_key_exists(Team::CODE_PREFIX_HN, $arr)) {
            $arr[Team::CODE_PREFIX_AI] = $arr[Team::CODE_PREFIX_HN];
        }
        if (array_key_exists(Team::CODE_PREFIX_HCM, $arr)) {
            $arr[Team::CODE_PREFIX_RS] = $arr[Team::CODE_PREFIX_HCM];
        }
        return $arr;
    }

    /**
     * Update data into table timekeeping
     *
     * @param array $data
     * @param boolean $update
     * @return boolean
     */
    public function updateData($datas)
    {
        $now = Carbon::now();
        $startDate = clone $now->firstOfMonth();
        $endDate = clone $now->endOfMonth();

        $table = Timekeeping::getTableName();
        if (!count($datas)) {
            return false;
        }

        $final = [];
        foreach ($datas as $key => $val) {
            $emp = explode('_', $key);
            $employeeId = $emp[0];
            $timekeepingDate = $emp[1];
            foreach (array_keys($val) as $field) {
                $value = (is_null($val[$field]) ? 'NULL' : '"' . $val[$field] . '"');
                $final[$field][] = 'WHEN `employee_id` = "' . $employeeId . '" AND `timekeeping_date` = "' . $timekeepingDate . '" THEN ' . $value . ' ';
            }
        }

        $cases = '';
        foreach ($final as $k => $v) {
            $cases .=  '`'. $k.'` = (CASE '. implode("\n", $v) . "\n" . 'ELSE `'. $k .'` END), ';
        }
        DB::beginTransaction();
        try {
            $query = 'UPDATE ' . $table . ' SET '. substr($cases, 0, -2) . ' WHERE `timekeeping_date` >= "' . $startDate->toDateString() . '"' . ' AND `timekeeping_date` <= "' . $endDate->toDateString() . '"';

            DB::statement($query);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
        }
    }

    /**
     * get infor manage_time_timekeeping_tables
     * @param  [type] $teamIds
     * @param  [type] $type
     * @return [type]
     */
    public function getInforTKTable($teamIds, $type)
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        $collection = DB::table('manage_time_timekeeping_tables as tktable')
            ->select(
                'tktable.id',
                'tktable.type',
                'tktable.start_date',
                'tktable.end_date',
                'teams.code'
            )
            ->whereIn('team_id', $teamIds)
            ->join('teams', 'teams.id', '=', 'tktable.team_id')
            ->where('month', $month)
            ->where('year', $year)
            ->where('tktable.type', $type)
            ->whereNull('tktable.deleted_at')
            ->get();
        $arrCode = [];
        $arrInfor = [];
        foreach ($collection as $item) {
            $arrCode[$item->code] = $item->id;
            $arrInfor[$item->code] = (array)$item;
        }
        return [
            'arrCode' => $arrCode,
            'arrInfor' => $arrInfor,
        ];
    }

    /**
     * insert timekeeping
     * @param  [array] $dataInsert
     */
    public function insertEmpTimeKeeping($dataInsert)
    {
        $now = Carbon::now();
        DB::beginTransaction();
        $tKTableIds = [];
        try {
            $manageTimeView = new ManageTimeView();
            $dataInsertTimekeeping = [];
            $dataInsertTimekeepingAggregate = [];
            $arrEmps = [];
            foreach ($dataInsert as $tkTableId => $item) {
                $teamCodePrefix = Team::getTeamCodePrefix($item['team_code']);
                $teamCodePrefix = Team::changeTeam($teamCodePrefix);
                $annualHolidays = CoreConfigData::getAnnualHolidays(2);
                $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);
                $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);

                $startDateBU = Carbon::parse($item['start_date']);
                $endDateBU = Carbon::parse($item['end_date']);
                if (!count($item["emps"])) {
                    continue;
                }
                foreach ($item["emps"] as $empId => $emp) {
                    $arrEmps[$tkTableId][] = $empId;
                    $startDate = clone $startDateBU;
                    $endDate = clone $endDateBU;


                    $dataTimekeepingAggregate = [];
                    $dataTimekeepingAggregate['timekeeping_table_id'] = $tkTableId;
                    $dataTimekeepingAggregate['employee_id'] = $empId;
                    $dataTimekeepingAggregate['created_at'] = $now;
                    $dataTimekeepingAggregate['updated_at'] = $now;

                    while (strtotime($startDate) <= strtotime($endDate)) {
                        $isWeekend = ManageTimeCommon::isWeekend($startDate, $compensationDays);
                        $isHoliday = ManageTimeCommon::isHolidays($startDate, [$annualHolidays, $specialHolidays]);

                        $dataTimekeeping = new Timekeeping();
                        $dataTimekeeping->timekeeping_table_id = $tkTableId;
                        $dataTimekeeping->timekeeping_date = $startDate->toDateString();
                        $dataTimekeeping->employee_id = $empId;
                        $dataTimekeeping->created_at = $now;
                        $dataTimekeeping->updated_at = $now;

                        $empOffcialDate = $emp->offcial_date;
                        $empTrialDate = $emp->trial_date;
                        $empOffcialDateCarbon = Carbon::parse($empOffcialDate)->format('Y-m-d');

                        $dataTimekeeping->is_official =  0;
                        if ($empOffcialDate && strtotime($startDate->format('Y-m-d')) >= strtotime($empOffcialDateCarbon)) {
                            $dataTimekeeping->is_official =  1;
                        }

                        if (empty($emp->leave_date) || Carbon::parse($emp->leave_date)->gte(Carbon::parse($date))) {
                            $timekeepingResult = $manageTimeView->timekeepingResult($dataTimekeeping, $isWeekend, $isHoliday, $empOffcialDate, $empTrialDate, $emp->contract_type, null, null, $item['type']);
                            $dataTimekeeping->timekeeping = $timekeepingResult[0];
                            $dataTimekeeping->timekeeping_number = $timekeepingResult[1];
                        } else {
                            $dataTimekeeping->timekeeping = 0;
                            $dataTimekeeping->timekeeping_number = 0;
                        }
                        $dataTimekeeping->timekeeping_number_register = 0;
                        $dataInsertTimekeeping[] = $dataTimekeeping->toArray();
                        $startDate->addDay();
                    }
                    $dataInsertTimekeepingAggregate[] = $dataTimekeepingAggregate;
                }
            }
            if (count($dataInsertTimekeeping)) {
                foreach (collect($dataInsertTimekeeping)->chunk(1000) as $chunk) {
                    Timekeeping::insert($chunk->toArray());
                }
                foreach (collect($dataInsertTimekeepingAggregate)->chunk(1000) as $chunk) {
                    TimekeepingAggregate::insert($chunk->toArray());
                }
            }
            foreach ($arrEmps as $tkTableId => $empIds) {
                if (count($empIds)) {
                    // cập nhật dữ liệu liên quan.
                    TimekeepingController::setDataRelated($tkTableId, $empIds);

                    // Tổng hợp công
                    $this->timekeepingAggregate($tkTableId, $empIds);
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
        }
    }

    /**
     * [getEmpContact description]
     * @return [type] [description]
     */
    public function getEmpTeamporaryContact($startDateTime, $endDateTime)
    {
        $contractType = [getOptions::WORKING_PARTTIME];

        $now = Carbon::now();
        $nowEnd = clone $now;

        $tblEmp = Employee::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblContract = ContractModel::getTableName();
        $empWorkTbl = EmployeeWork::getTableName();
        $tblTeam = Team::getTableName();

        return TeamMember::select(
            "{$tblEmp}.id",
            "{$tblEmp}.join_date",
            "{$tblEmp}.trial_date",
            "{$tblEmp}.trial_end_date",
            "{$tblEmp}.offcial_date",
            "{$tblEmp}.leave_date",
            "{$empWorkTbl}.contract_type"
        )
        ->join("{$tblEmp}", "{$tblEmp}.id", "=", "{$tblTeamMember}.employee_id")
        ->join("{$empWorkTbl}", "{$tblEmp}.id", "=", "{$empWorkTbl}.employee_id")
        ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
        ->leftJoin("{$tblContract}", "{$tblEmp}.id", "=", "{$tblContract}.employee_id")
        ->where(function ($query) use ($tblEmp, $now) {
            $query->whereNull("{$tblEmp}.leave_date")
                ->orWhereDate("{$tblEmp}.leave_date", '>=', $now->startOfMonth()->format('Y-m-d'));
        })
        ->whereNull("{$tblEmp}.deleted_at")
        ->whereDate("{$tblEmp}.join_date", "<=", $nowEnd->endOfMonth()->format('Y-m-d'))
        ->whereNotIn("{$tblEmp}.account_status", [getOptions::FAIL_CDD])
        ->whereIn("{$tblContract}.type", $contractType)
        ->whereDate("{$tblContract}.start_at", '<=', $nowEnd->endOfMonth()->format('Y-m-d'))
        ->whereDate("{$tblContract}.end_at", '>=', $now->startOfMonth()->format('Y-m-d'))
        ->whereDate("{$tblContract}.updated_at", '>=', $startDateTime)
        ->whereDate("{$tblContract}.updated_at", '<=', $endDateTime)
        ->whereNull("{$tblContract}.deleted_at")
        ->where("{$tblTeam}.code", 'NOT LIKE', Team::CODE_PREFIX_JP . '%')
        ->groupBy("{$tblEmp}.id")
        ->get();
    }

    /**
     * cập nhật  tổng hợp công
     *
     * @param $tkTableId
     * @param $empIds
     */
    public function timekeepingAggregate($tkTableId, $empIds)
    {
//        $data = [
//            'timekeeping_table_id' => $tkTableId,
//        ];
//        $request = new Request($data);
//        TimekeepingController::updateTimekeepingAggregate($request, $empIds);

        // cập nhật Tổng hợp công tỉ lệ
        $dataRelate['emp_ids'] = $empIds;
        $dataRelate['timekeeping_table_id'] = $tkTableId;
        $timeKeepingTable = TimekeepingTable::find($tkTableId);
        $objView = new ManageTimeView();
        if (!$timeKeepingTable) {
            Log::info('cập nhật tổng hợp công không tìm thấy bảng công');
            return;
        }
        $objView->updateSalaryRateAgregate($timeKeepingTable, $dataRelate);
    }
}
