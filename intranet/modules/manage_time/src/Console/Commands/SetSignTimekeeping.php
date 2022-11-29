<?php

namespace Rikkei\ManageTime\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Rikkei\Team\Model\Team;

class SetSignTimekeeping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timekeeping:set_sign {idTables} {idEmp_0_all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set ký hiệu công làm việc cho bảng chấm công';

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
     *
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $this::info('=== Start set sign timekeeping ===');
            Log::info('=== Start set sign timekeeping ===');

            $idTables = $this->argument('idTables');
            $idEmp = $this->argument('idEmp_0_all');
            $idTables = str_replace('[', '', $idTables);
            $idTables = str_replace(']', '', $idTables);
            if ($idTables == '') {
                $this->info('id bảng công rỗng');
                Log::info('=== End set sign timekeeping ===');
                return;
            } else {
                $idTables = explode(',', $idTables);
            }
            if (!is_numeric($idEmp)) {
                $this->info($idTable . ' idEmp phải là kiểu số. chạy all idEmp = 0');
                Log::info('=== End set sign timekeeping ===');
                return;
            }
            foreach ($idTables as $idTable) {
                if (!is_numeric($idTable)) {
                    $this->info($idTable . ' id bảng công phải là kiểu số.');
                    Log::info('=== End set sign timekeeping ===');
                    return;
                }
            }

            foreach ($idTables as $idTable) {
                $this->SetSignFinesTimekeeping($idTable, $idEmp);
            }
            
            $this::info('=== End set sign timekeeping ===');
            Log::info('=== End set sign timekeeping ===');
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            $this->info($e->getMessage());
        }
    }

    /**
     * @param $idTable
     */
    public function SetSignFinesTimekeeping($idTable, $idEmp)
    {
        $tables = TimekeepingTable::findOrFail($idTable);
        if (!$tables) {
            $this::info( $idTable . ' Table do not exist');
            Log::info( $idTable . ' Table do not exist');
            return;
        }
        $this->SetSignTimekeepingCron($idTable, $idEmp);
        return;
    }

    /**
     * update sign after table timekeeping update 3 minutes
     *
     */
    public function SetSignTimekeepingCron($idTable = null, $idEmp = null)
    {
        $now = Carbon::now();
        $dateEnd = $now->format('Y-m-d H:i:59');
        $dateStart = $now->subMinutes(4)->format('Y-m-d H:i:0');
        $lastDate = Carbon::now()->addMonth()->format('Y-m-31');
        $firstDate = Carbon::now()->subMonth()->format('Y-m-01');
 
        $tk = Timekeeping::select(
            "manage_time_timekeepings.timekeeping_table_id as tkTable_id",
            "manage_time_timekeepings.*",
            DB::raw('date(employees.join_date) as join_date'),
            DB::raw('date(employees.trial_date) as trial_date'),
            DB::raw('date(employees.offcial_date) as offcial_date'),
            DB::raw('date(employees.leave_date) as leave_date'),
            "mtkTabke.team_id",
            'mtkTabke.type as contract_type',
            'mtkTabke.date_max_import'
        )
        ->leftJoin('manage_time_timekeeping_tables as mtkTabke', 'mtkTabke.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
        ->join('employees', 'employees.id', '=', 'manage_time_timekeepings.employee_id');
        
        if ($idEmp) {
            $tk->where('manage_time_timekeepings.employee_id', '=', $idEmp);
        }

        if ($idTable) {
            $tk->where('mtkTabke.id', $idTable);
        } else {
            $tk->where('manage_time_timekeepings.updated_at', '>=', $dateStart)
            ->where('manage_time_timekeepings.updated_at', '<=', $dateEnd)
            ->where(function($query) use ($now) {
                $dateFirst = $now->format('Y-m-01 07:00:00');
                $query->where('manage_time_timekeepings.updated_at', '>=', $dateFirst)
                    ->orWhere(function($sub) {
                        $sub->orWhere('register_business_trip_number', '<>', 0)
                        ->orWhere('register_leave_has_salary', '<>', 0)
                        ->orWhere('register_leave_no_salary', '<>', 0)
                        ->orWhere('register_supplement_number','<>', 0)
                        ->orWhere('register_leave_basic_salary', '<>', 0)
                        ->orWhere('timekeeping_number_register', '<>', 0)
                        ->orWhere('no_salary_holiday', '<>', 0)
                        ->orWhere('register_ot', '<>', 0);
                    });
            })
            ->whereRaw("mtkTabke.id IN (SELECT id 
                FROM manage_time_timekeeping_tables as mtkTb
                WHERE DATE(mtkTb.start_date) <= '{$lastDate}'
                    AND DATE(mtkTb.end_date) >= '{$firstDate}'
                    AND mtkTb.deleted_at IS NULL)");
        }
        $tk = $tk->groupBy('manage_time_timekeepings.id', 'manage_time_timekeepings.employee_id')
            ->orderBy('manage_time_timekeepings.employee_id')
            ->orderBy('manage_time_timekeepings.timekeeping_date')
            ->get();

        if (!count($tk)) {
            return;
        }
        $compensationHoliday = $this->getAllCompensationHoliday();
        $arrTeam = $this->getArrayTeam();
        $dataInsertSignTK = [];
        $data = [];
        foreach ($tk as $item) {
            $key = $item->employee_id . '-' . $item->timekeeping_date;
            $dataInsert['employee_id'] = $item->employee_id;
            $dataInsert['timekeeping_date'] = $item->timekeeping_date;
            $compensationDays = $compensationHoliday['teamCompensationDays'][$arrTeam[$item->team_id]];
            $arrHolidays = $compensationHoliday['teamHolidays'][$arrTeam[$item->team_id]];
            $signFines = ManageTimeCommon::getTimekeepingSign($item, '', $compensationDays, $arrHolidays);
            if ($item->sign_fines == '-' &&
                ($signFines[0] == ' -' || $signFines[0] == '-')) {
                continue;
            }
            $jsonSF['sign'] = $signFines[0];
            $jsonSF['fines'] = $signFines[1];
            $dataInsert['sign_fines'] = json_encode($jsonSF);
            $dataInsertSignTK[$item->timekeeping_table_id][$key] = $dataInsert;
        }
        if (!$dataInsertSignTK) return;
        DB::beginTransaction();
        try {
            Log::info('Start cron update sign timekeeping');
            foreach ($dataInsertSignTK as $tkTableId => $data) {
                $this->updateDataCron($data, $tkTableId);
            }
            Log::info('End cron update sign timekeeping');
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
        }
        return;
    }

    /**
     * lây từ viewTimekeeping và chỉnh sửa lại - ko muốn ảnh hưởng nên tách ra đây
     *  ko update column updated_at
     *  
     * @param array $data
     * @param int $timekeepingTableId
     * @param boolean $update
     * @return boolean
     */
    public function updateDataCron($data, $timekeepingTableId)
    {
        $table = Timekeeping::getTableName();
        if (!count($data)) {
            return false;
        }
        $final = [];
        $column = [];
        $employeeIds = [];
        $arrDate = [];
        $now = Carbon::now();
        $subNow = clone $now;
        $subNow = $subNow->subDay();
        foreach ($data as $key => $val) {
            $employeeId = $val['employee_id'];
            $employeeIds[$val['employee_id']]= $val['employee_id'];
            $timekeepingDate = $val['timekeeping_date'];
            $arrDate[] = "'" .  $val['timekeeping_date'] . "'";
            foreach (array_keys($val) as $field) {
                if ($field == 'employee_id' || $field == 'timekeeping_date' || $field == 'updated_at') {
                    continue;
                }
                $column[] = $field;
                $value = (is_null($val[$field]) ? 'NULL' : "'" . $val[$field] . "'");
                $final[$field][] = 'WHEN `timekeeping_table_id` = "' . $timekeepingTableId . '" AND `employee_id` = "' . $employeeId . '" AND `timekeeping_date` = "' . $timekeepingDate . '" THEN ' . $value . ' ';
            }
        }
        $strEmployeeId = implode(',', $employeeIds);
        $srtDate = implode(',', array_unique($arrDate));

        $cases = '';
        foreach ($final as $k => $v) {
            $cases .=  '`'. $k.'` = (CASE '. implode("\n", $v) . "\n" . 'ELSE `'.$k.'` END), ';
        }
        $query = 'UPDATE ' . $table . ' SET '. substr($cases, 0, -2) . ' WHERE `timekeeping_table_id` = "' . $timekeepingTableId 
            . '" AND employee_id IN (' . $strEmployeeId . ')'
            . ' AND timekeeping_date IN (' . $srtDate . ')'
            . ' AND date(manage_time_timekeepings.updated_at) between "' . $subNow->format('Y-m-d') . '" and "' .  $now->format('Y-m-d') . '"';
        Log::info('Update column: ' . implode(',', array_unique($column)) . ' Table: ' . $timekeepingTableId . ': ' . $strEmployeeId);
        DB::statement($query);
        return true;
    }

    public function getAllCompensationHoliday()
    {
        $dataTeam = [
            Team::CODE_PREFIX_HN,
            Team::CODE_PREFIX_DN,
            Team::CODE_PREFIX_HCM,
        ];
        $teamHolidays = [];
        $teamCompensationDays = [];
        foreach ($dataTeam as $teamCodePrefix) {
            $teamCompensationDays[$teamCodePrefix] = CoreConfigData::getCompensatoryDays($teamCodePrefix);
            $teamHolidays[$teamCodePrefix] = CoreConfigData::getHolidayTeam($teamCodePrefix);
        }
        return [
            'teamCompensationDays' => $teamCompensationDays,
            'teamHolidays' => $teamHolidays,
        ];
    }

    public function getArrayTeam()
    {
        $teams = Team::getAllTeam();
        $result = [];
        foreach ($teams as $item) {
            $result[$item->id] = $item->branch_code;
        }
        return $result;
    }
}