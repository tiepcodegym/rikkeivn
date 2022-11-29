<?php

namespace Rikkei\ManageTime\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\ManageTime\View\ViewTimeKeeping;

class ExportTimeInOutCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timekeeping:export_time_in_out {date=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export giờ vào ra của bảng công theo ngày';

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
            Log::useFiles(storage_path() . '/logs/timekeeping.log');
            $date = $this->argument('date');
            $this->info('=== Start export time in out with date = ' .$date);
            Log::info('=== Start export time in out with date = ' .$date);

            if ($date) {
                $arrDateTime = explode("-", $date);
                if (count($arrDateTime) < 3) {
                    $this->info('-- The time is not a valid. --');
                    Log::info('-- The time is not a valid. --');
                    return false;
                }
                if (!is_numeric($arrDateTime[0]) || !is_numeric($arrDateTime[1]) || !is_numeric($arrDateTime[2])) {
                    $this->info('-- The time is not a valid. --');
                    Log::info('-- The time is not a valid. --');
                    return false;
                }
            } else {
                $date = date('Y-m-d');
            }

            $objViewKeeping = new ViewTimeKeeping();
            $objViewKeeping->cronExportTimeInOut($date);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            $this->info($e->getMessage());
        } finally {
            $this->info('=== End export time in out with date = ' .$date);
            Log::info('=== End export time in out with date = ' .$date);
        }
    }
}
