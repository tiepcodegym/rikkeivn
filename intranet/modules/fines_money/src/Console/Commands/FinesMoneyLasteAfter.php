<?php

namespace Rikkei\FinesMoney\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\FinesMoney\Model\FinesMoney;
use Rikkei\ManageTime\Model\TimekeepingAggregate;

class FinesMoneyLasteAfter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finesMoney:laste_after';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cộng tiền phạt cuối tháng trước vào tháng sau';

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
    public function handle()
    {
        DB::beginTransaction();
        try {
            Log::info('start fines money laste after');
            $data = [
                '2018-08' => '2018-09',
                '2018-12' => '2019-01',
                '2019-01' => '2019-02',
                '2019-04' => '2019-05',
                '2019-08' => '2019-09',
            ];

            foreach ($data as $keyData => $valueData) {
                $tktableLast = TimekeepingAggregate::getEmpTimekeepingLastFirst($keyData);
                if ($tktableLast) {
                    $finesLaste = FinesMoney::getFinesMoney($tktableLast);
                    $finesAfter = [];
                    $finesMoney = [];
                    if (count($finesLaste)) {
                        $tktableFirst = TimekeepingAggregate::getEmpTimekeepingLastFirst($valueData, false);
                        $finesAfter = FinesMoney::getFinesMoney($tktableFirst);

                        foreach ($finesLaste as $key => $value) {
                            if (count($finesAfter) && array_key_exists($key, $finesAfter)) {
                                $finesAfter[$key]['amount'] = $finesAfter[$key]['amount'] + $finesLaste[$key]['amount'];
                            } else {
                                $time = explode('-', $valueData);
                                $cpFinesLaste = $finesLaste[$key];
                                $cpFinesLaste['year'] = $time[0];
                                $cpFinesLaste['month'] = $time[1];
                                $finesAfter[$key] = $cpFinesLaste;
                            }
                            // tiền phạt tháng trước từ đầu tháng đến ngày kết thúc bảng công
                            $tktable = TimekeepingAggregate::getEmpTimekeepingLastFirst($keyData, false);
                            $finesMoney = FinesMoney::getFinesMoney($tktable);
                        }
                        if (count($finesAfter)) {
                            FinesMoney::saveFinesMoneyLate($finesAfter);
                        }
                        // update tiền phạt tháng trước
                        if (count($finesMoney)) {
                            FinesMoney::saveFinesMoneyLate($finesMoney);
                        }
                        // delete nếu tháng trước có tiền phạt - update lại tháng trước ko có thì xóa tiền phạt tháng trước
                        foreach ($finesLaste as $key => $value) {
                            if (!array_key_exists($key, $finesMoney)) {
                                FinesMoney::where('month', $value["month"])
                                ->where('year', $value["year"])
                                ->where('type', FinesMoney::TYPE_LATE)
                                ->where('status_amount', FinesMoney::STATUS_UN_PAID)
                                ->where('employee_id', $key)
                                ->delete();
                            }
                        }
                    }
                }
            }
            Log::info('end fines money laste after');
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e);
        }
    }
}
