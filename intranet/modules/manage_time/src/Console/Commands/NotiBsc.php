<?php

namespace Rikkei\ManageTime\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\Team\Model\Team;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Core\Model\EmailQueue;
use DB;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\ManageTime\Model\TimekeepingNotLate;
use Rikkei\Team\Model\EmployeeContact;
use Rikkei\ManageTime\Model\LeaveDayReason;
use Rikkei\ManageTime\Model\LeaveDayRegister;

class NotiBsc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timekeeping:notibsc {year=0} {month=0} {date=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi mail noti thông báo cho nhân viên thiếu công làm đơn bsc';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get param from command
        $year = $this->argument('year');
        $month = $this->argument('month');
        $date = $this->argument('date');
        if (!$year) {
            $year = Carbon::now()->subDay()->year;
        }
        if (!$month) {
            $month = Carbon::now()->subDay()->month;
        }
        if (!$date) {
            $date = Carbon::now()->subDay()->format('Y-m-d');
        }

        $tableIds = $this->getTablesList($year, $month, $this->branchesApply());
        $employees = [];
        foreach ($tableIds as $tableId) {
            $employees = array_merge($employees, $this->getTimekeepingAggregateInDate($tableId, $date));
        }
        if (count($employees)) {
            $this->sendMailNoti($employees, $date);
        }
    }

    /**
     * Chi nhánh áp dụng gửi thông báo
     *
     * @return array    mảng mã chi nhánh: hanoi, danang, hcm
     */
    private function branchesApply()
    {
        return ['hanoi'];
    }

    /**
     * Function gửi mail thiếu công
     *
     * @param array $employees  Mảng thông tin các nhân viên bị thiếu công
     * @param date $date    format Y-m-d. Ngày bị thiếu công
     *
     * @return void
     */
    private function sendMailNoti($employees, $date)
    {
        $dataInsert = [];
        $recieverIds = [];
        foreach ($employees as $emp) {
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($emp['email'], $emp['name'])
                ->setSubject('[Rikkei.vn] Mail thông báo thiếu công ngày ' . Carbon::parse($date)->format('d-m-Y'))
                ->setTemplate('manage_time::timekeeping.mail.mail_timekeeping_noti', [
                    'name' => $emp['name'],
                    'email' => $emp['email'],
                    'date' => $date,
                    'total_working' => $emp['total_working'],
                    'url_timekeeping_profile' => route('manage_time::profile.timekeeping', ['id' => $emp['timekeeping_table_id']]),
                ]);
            $dataInsert[] = $emailQueue->getValue();
            $recieverIds[] = $emp['employee_id'];
        }
        if (!empty($dataInsert)) {
            DB::beginTransaction();
            try {
                EmailQueue::insert($dataInsert);
                \RkNotify::put(
                    $recieverIds,
                    'Thông báo thiếu công ngày ' . Carbon::parse($date)->format('d-m-Y') . '. Bạn vui lòng vào bảng chi tiết công để kiểm tra lại và làm đơn BSC nếu cần thiết.',
                    route('manage_time::profile.timekeeping', ['id' => $emp['timekeeping_table_id']]),
                    [
                        'category_id' => RkNotify::CATEGORY_TIMEKEEPING
                    ]
                );
                DB::commit();
            } catch (Exception $ex) {
                DB::rollback();
            }
            
        }
    }

    /**
     * Kiểm tra date truyền vào có phải là ngày cuối tuần hoặc ngày nghỉ lễ
     *
     * @param date $date    format: Y-m-d
     * @param string $teamCodePre     mã code của Chi nhánh kiểm tra
     * @return boolean  true: ngày nghỉ lễ, false: ko phải
     */
    private function isHolidayOrWeekend($date, $teamCodePre)
    {
        $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePre);
        
        return in_array($date, $specialHolidays) || $this->isWeekend($date);
    }

    /**
     * Kiểm tra date truyền vào có phải là ngày cuối tuần
     * đúng return true
     * sai return false
     *
     * @param date $date  format: Y-m-d
     * @return boolean
     */
    private function isWeekend($date)
    {
        $weekDay = date('w', strtotime($date));
        return ($weekDay == 0 || $weekDay == 6);
    }

    /**
     * Hàm lấy ra danh sách các nhân viên không làm đủ công
     * Nếu công < 1 thì là thiếu công.
     * Chưa check được cho các nhân viên có hợp đồng làm nửa ngày, hoặc làm 3 ngày / tuần.
     *
     * @param int $tableId  id của bảng công
     * @param date $date    format Y-m-d. Ngày cần kiểm tra
     *
     * @return array    mảng các nhân viên bị thiếu công
     */
    private function getTimekeepingAggregateInDate($tableId, $date)
    {
        $timeKeepingTable = TimekeepingTable::select('id', 'timekeeping_table_name', 'team_id', 'start_date', 'end_date', 'type')
            ->where('id', $tableId)
            ->first();
        if (!$timeKeepingTable) {
            return;
        }
        $employees = [];
        $team = Team::getTeamById($timeKeepingTable->team_id);
        $teamCodePre = Team::getTeamCodePrefix($team->code);
        $teamCodePre = Team::changeTeam($teamCodePre);

        // Nếu là ngày nghỉ thì bỏ qua
        if ($this->isHolidayOrWeekend($date, $teamCodePre)) {
            return $employees;
        }

        $objNotLate = new TimekeepingNotLate();
        $empNotLate = $objNotLate->getEmployeeNotLate()->pluck('emp_id')->toArray();
        $empNotNoti = EmployeeContact::getEmployeeNotNoti()->pluck('employee_id')->toArray();
        $timekeepingAggregate = Timekeeping::getTimekeepingAggregate($tableId, '', [], $date);
        if (count($timekeepingAggregate)) {
            $now = Carbon::now();
            $employeeIds = [];
            $dataUpdate = [];
            if ($timeKeepingTable->start_date && $timeKeepingTable->end_date) {
                $timeKeepingTable->start_date = Carbon::parse($timeKeepingTable->start_date);
                $timeKeepingTable->end_date = Carbon::parse($timeKeepingTable->end_date);
            } else {
                $timeKeepingTable->start_date = null;
                $timeKeepingTable->end_date = null;
            }
            $compensation = CoreConfigData::getComAndLeaveDays($teamCodePre);
            $compInTime = ManageTimeCommon::getCompInTime(
                $timeKeepingTable,
                $compensation,
                'com'
            );
            $leavComInTime = ManageTimeCommon::getCompInTime(
                $timeKeepingTable,
                $compensation,
                'lea'
            );

            foreach ($timekeepingAggregate as $item) {
                // Nếu là nhân viên trong danh sách ko tính đi muộn thì bỏ qua
                if (in_array($item['employee_id'], $empNotLate)) {
                    continue;
                }
                // Nếu nhân viên không muốn nhận noti thì bỏ qua
                if (in_array($item['employee_id'], $empNotNoti)) {
                    continue;
                }
                // Nếu nhân viên nghỉ thai sản, nghỉ không phép thì bỏ qua
                if (in_array($item['id'], $this->checkLeaveDayNotNoti($date))) {
                    continue;
                }
                $arrayWork = [];
                $cpCompenstaion = $compensation;
                $cpCompInTime = $compInTime;
                $cpleavComInTime = $leavComInTime;

                $item["join_date"] = Carbon::parse($item["join_date"])->format("Y-m-d");
                if ($timeKeepingTable->type == TimekeepingTable::OFFICIAL) {
                    foreach ($cpCompenstaion['com'] as $key => $compen) {
                        if (strtotime($cpCompenstaion['lea'][$key]) < strtotime($item["join_date"])
                            || ((($item["trial_date"] && strtotime($compen) <= strtotime($item["trial_date"]))
                            || (!$item["trial_date"] && strtotime($compen) <= strtotime($item["offcial_date"]))
                            || (($item["trial_date"] && strtotime($compen) >= strtotime($item["trial_date"]) && strtotime($cpCompenstaion['lea'][$key]) < strtotime($item["trial_date"]))
                                || (!$item["trial_date"] && strtotime($compen) >= strtotime($item["offcial_date"]) && strtotime($cpCompenstaion['lea'][$key]) < strtotime($item["offcial_date"]))
                                && strtotime($cpCompenstaion['lea'][$key]) >= strtotime($item["join_date"])))
                            && strtotime($compen) >= strtotime($timeKeepingTable->start_date)
                            && strtotime($compen) <= strtotime($timeKeepingTable->end_date))) {
                            unset($cpCompenstaion['com'][$key]);
                            unset($cpCompenstaion['lea'][$key]);
                            $cpCompInTime = ManageTimeCommon::getCompInTime($timeKeepingTable, $cpCompenstaion, 'com');
                            $cpleavComInTime = ManageTimeCommon::getCompInTime($timeKeepingTable, $cpCompenstaion, 'lea');
                        }
                    }
                } elseif ($timeKeepingTable->type == TimekeepingTable::TRIAL) {
                    $cpCompInTime = ['check' => [], 'big' => []];
                    $cpleavComInTime = ['check' => [], 'big' => []];
                } else {
                    //do not some thing
                }

                $itemCom = ManageTimeCommon::calComDayEmpInTime($timeKeepingTable, $item['offcial_date'], $item['join_date'], $item['leave_date'], $cpCompInTime, true);
                $itemLea = ManageTimeCommon::calComDayEmpInTime($timeKeepingTable, $item['offcial_date'], $item['join_date'], $item['leave_date'], $cpleavComInTime);
                $itemCom['number_com_tri'] = $itemCom['number_com_tri'] - $itemLea['number_com_tri'];
                $itemCom['number_com_off'] = $itemCom['number_com_off'] - $itemLea['number_com_off'];
                $item['updated_at'] = $now;
                $item = array_merge($itemCom, $item);

                $item = (object) $item;
                $daysOffInTimeBusiness = ManageTimeView::daysOffInTimeBusiness($item, $timeKeepingTable, $teamCodePre, $date, $date);
                $totalWorkingToSalary = ManageTimeView::totalWorkingDayObject($item, $daysOffInTimeBusiness);
                $item = (array) $item;

                $totalWorking = $totalWorkingToSalary['offcial'] + $totalWorkingToSalary['trial'];
                if ($totalWorking < 1) {
                    $employees[] = [
                        'employee_id' => $item['employee_id'],
                        'email' => $item['email'],
                        'name' => $item['name'],
                        'timekeeping_table_id' => $tableId,
                        'total_working' => $totalWorking,
                    ]; 
                }
            }

        }
        return $employees;
    }

    /**
     * Lấy ra danh sách id các bảng công trong tháng, năm cần kiểm tra
     *
     * @param int $year     năm kiểm tra
     * @param int $month    tháng kiểm tra
     * @param array $branches   mảng chi nhánh kiểm tra
     *
     * @return array    mảng id bảng công
     */
    private function getTablesList($year, $month, $branches)
    {
        return TimekeepingTable::where('month', $month)
                ->where('year', $year)
                ->join('teams', 'teams.id', '=', 'manage_time_timekeeping_tables.team_id')
                ->whereIn('teams.branch_code', $branches)
                ->select(
                    'manage_time_timekeeping_tables.id'
                )
                ->get()->pluck('id')->toArray();
    }

    private function checkLeaveDayNotNoti($date)
    {
        $date = Carbon::parse($date)->format("Y-m-d");
        $listLeaveDayReasons = LeaveDayReason::whereIn("code", [LeaveDayReason::CODE_UNPAID_LEAVE, LeaveDayReason::CODE_MATERNITY])
            ->where('team_type', '=', LeaveDayReason::TEAM_TYPE_VN)
            ->get()->pluck("id")->toArray();
        $ids = LeaveDayRegister::whereIn("reason_id", $listLeaveDayReasons)
            ->whereDate('date_start', '<=', $date)
            ->whereDate('date_end', '>=', $date)
            ->where('status', LeaveDayRegister::STATUS_APPROVED)
            ->get()->pluck("creator_id")->toArray();
        return $ids;
    }
}
