<?php

namespace Rikkei\ManageTime\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\ManageTime\Http\Controllers\TimekeepingController;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\Team\Model\Team;

class CreateTableTimekeeping extends Command
{
    const CONTRACT_TYPE = 4; // chính thức, thử việc
    const CONTRACT_TYPE_PART_TIME = 3; // nhân viên thời vụ

	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tktable:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo bảng công cho nhân viên đầu tháng';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(TimekeepingController $timeKeeping)
    {
        try {
            Log::info('=== Start cron create table timekeeping ===');
            $cateTK = [
                self::CONTRACT_TYPE => 'NV CHÍNH THỨC - THỬ VIỆC',
                self::CONTRACT_TYPE_PART_TIME => 'NV MÙA VỤ',
            ];
            // $allBranchs = Team::getListBranchMainTeam();
            $arrCodeBranch = [
                Team::CODE_PREFIX_HN,
                Team::CODE_PREFIX_AI,
                Team::CODE_PREFIX_HCM,
                Team::CODE_PREFIX_DN,
                Team::CODE_PREFIX_ACADEMY,
                Team::CODE_PREFIX_DIGITAL,
            ];
            $selfTable = Team::getTableName();
            $branches = Team::select("{$selfTable}.id", "{$selfTable}.name", "{$selfTable}.code")
                ->whereIn("{$selfTable}.code", $arrCodeBranch)
                ->get();
            if (!count($branches)) {
                return;
            }

            $now = Carbon::now();
            $strTime = ' từ ' . $now->firstOfMonth()->format("d-m-Y") . ' đến ' . $now->endOfMonth()->format("d-m-Y");
            // Cấu hình user nhận thông báo từ bảng công.
            $arrIdEmp = [
                Team::CODE_PREFIX_HN => 1290,
                Team::CODE_PREFIX_AI => 1290,
                Team::CODE_PREFIX_ACADEMY => 1290,
                Team::CODE_PREFIX_DIGITAL => 1290,
                Team::CODE_PREFIX_HCM => 1559,
                Team::CODE_PREFIX_DN => 2058,
            ];

            DB::beginTransaction();
            try {
                foreach ($branches as $item) {
                    foreach ($cateTK as $key => $text) {
                        $strNameTable = 'Bảng chấm công ' . $text . ' '. $strTime . ' - ' . $item->name;
                        $timekeepingTable = new TimekeepingTable();
                        $timekeepingTable->creator_id = $arrIdEmp[$item->code];
                        $timekeepingTable->timekeeping_table_name = $strNameTable;
                        $timekeepingTable->team_id = $item->id;
                        $timekeepingTable->month = $now->month;
                        $timekeepingTable->year = $now->year;
                        $timekeepingTable->start_date = $now->firstOfMonth()->toDateString();
                        $timekeepingTable->end_date = $now->endOfMonth()->toDateString();
                        $timekeepingTable->type = $key;
                        if ($timekeepingTable->save()) {
                            $timeKeeping->storeTimeKeeping($timekeepingTable);
                            // cập nhật dữ liệu liên quan.
                            TimekeepingController::setDataRelated($timekeepingTable->id);
                            //Tổng hợp công
                            $data = [
                               'timekeeping_table_id' => $timekeepingTable->id,
                            ];
                            $request = new Request($data);
                            TimekeepingController::updateTimekeepingAggregate($request);
                        }
                    }
                }

                DB::commit();
                Log::info('=== End cron create table timekeeping timekeeping === ');
            } catch (Exception $ex) {
                DB::rollBack();
                Log::info($ex);
            }
        } catch (Exception $e) {
            $this->info($e->getMessage());
            Log::error($e);
        }
    }
}
