<?php

namespace Rikkei\Assets\Console\Commands;

use DB;
use Exception;
use Illuminate\Console\Command;
use Lang;
use Log;
use Rikkei\Assets\Model\AssetItem;

class ReturnCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asset:return_customer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix các tài sản ở trạng thái gửi trả khách hàng';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            // 13: la trạng thái unapproved đang insert sai
            AssetItem::where('state', 13)
                ->whereNull('employee_id')
                ->update(['state' => AssetItem::STATE_RETURN_CUSTOMER]);

            DB::commit();
        } catch (Exception $ex) {
            \Log::info($ex);
            DB::rollback();
        }
    }
}

