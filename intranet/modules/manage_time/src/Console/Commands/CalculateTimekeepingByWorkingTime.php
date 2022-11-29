<?php

namespace Rikkei\ManageTime\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rikkei\ManageTime\Model\WorkingTimeDetail;
use Rikkei\ManageTime\Model\WorkingTimeRegister;
use Rikkei\ManageTime\View\ViewTimeKeeping;

class CalculateTimekeepingByWorkingTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timekeeping:calculate {yearmonth}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tính thời gian làm việc của nhân viên có đăng ký thay đổi thời gian làm việc';

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
        try {
            $this::info('=== Start calculate timekeeping ===');
            $date = $this->argument('yearmonth');
            if (strpos($date, '-') != 4) {
                $this->info('sai định dạng: Y-m');
                return;
            }
            $date = explode('-', $date);
            if (!is_numeric($date[0]) || !is_numeric($date[1])) {
                $this->info('năm và tháng là kiểu số.');
                return;
            }
            $cbDate = Carbon::parse($date[0] . '-' . $date[1]);
            $date = $cbDate->format('Y-m');
            $dateStart = $date . '-01';
            $dateEnd = $date . '-31';
            
            $dataEmployee = $this->getEmployeeWorkingTime($dateStart, $dateEnd);
            if ($dataEmployee) {
                $objViewTK = new ViewTimeKeeping();
                $objViewTK->exportTimeInOutByListEmp($dataEmployee);
            }
        
            $this::info('=== Ban hay an button tong hop cong sau 3 phut ===');
            $this::info('=== End calculate timekeeping ===');
        } catch (Exception $e) {
            $this->info($e->getMessage());
            Log::error($e);
        }
    }
  
    public function getEmployeeWorkingTime($dateStart, $dateEnd)
    {
        $tblWTRegister = WorkingTimeRegister::getTableName();
        $tblWTDetail = WorkingTimeDetail::getTableName();
        $empIds = WorkingTimeDetail::select([
            "{$tblWTRegister}.id",
            "{$tblWTDetail}.employee_id",
            "{$tblWTDetail}.from_date",
            "{$tblWTDetail}.to_date",
            "{$tblWTDetail}.updated_at"
        ])
        ->join($tblWTRegister, "{$tblWTRegister}.id", '=', "{$tblWTDetail}.working_time_id")
        ->whereIn("{$tblWTRegister}.status", [WorkingTimeRegister::STATUS_REJECT, WorkingTimeRegister::STATUS_APPROVE])
        ->whereNull("{$tblWTRegister}.deleted_at")
        ->where("{$tblWTDetail}.from_date", '<=', $dateEnd)
        ->where("{$tblWTDetail}.to_date", '>=', $dateStart)
        ->groupBy("{$tblWTDetail}.employee_id")
        ->lists('employee_id')->toArray();
        if (!$empIds) {
            $this::info('=== Khong co nhan vien nao duoc cap nhat');
            return [];
        }
        $result = [];
        $strEmpId = implode(',', $empIds);
        $this::info('=== Cap nhat cac nhan vien sau: ' . $strEmpId);
        foreach($empIds as $empId) {
            $result[$empId][] = [$dateStart, $dateEnd];
        }
        return $result;
    }
}
