<?php

namespace Rikkei\Core\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Rikkei\Core\Model\FinesMoney;
use Rikkei\Core\Model\ForgotTurnOff;

class StatsTurnOff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:turnoff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Thống kê những nhân viên quên không tắt máy trong tháng trước';

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
        try {
            $employees = ForgotTurnOff::getFinesLastMonth();
            $month = Carbon::now()->subMonth(1)->month;
            $year = Carbon::now()->subMonth(1)->year;

            if (empty($employees)) {
                return;
            }

            \Log::info('=== Start crontabCalculateFinesForgotTurnOff ===');
            $this->info('=== Start crontabCalculateFinesForgotTurnOff ===');
            foreach ($employees as $employee) {
                $data = [
                    'employee_id' => $employee->employee_id,
                    'amount' => $employee->total_amount,
                    'count' => $employee->count,
                    'status_amount' => FinesMoney::STATUS_UN_PAID,
                    'type' => FinesMoney::TYPE_TURN_OFF,
                    'month' => $month,
                    'year' => $year,
                ];

                FinesMoney::updateOrCreate(
                    [
                        'month' => $month,
                        'year' => $year,
                        'employee_id' => $employee->employee_id,
                        'type' => FinesMoney::TYPE_TURN_OFF,
                    ],
                    $data);
            }
            $this->info('=== End crontabCalculateFinesForgotTurnOff ===');
            \Log::info('=== End crontabCalculateFinesForgotTurnOff === ');
        } catch (\Exception $e) {
            $this->info($e->getMessage());
            \Log::error($e);
        }
    }
}
