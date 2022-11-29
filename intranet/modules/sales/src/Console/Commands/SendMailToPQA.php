<?php

namespace Rikkei\Sales\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Sales\Model\CssMail;

class SendMailToPQA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'css:send_mail_pqa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Send mail to PQA khi customer don't make CSS";

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
            Log::info("======= [CSS] Start send mail to PQA when khi customer don't make CSS =======");
            CssMail::sendMail2();
            Log::info("======= [CRM] End send mail to PQA =======");
            DB::commit();
            return;
        } catch (\Exception $ex) {
            DB::rollback();
            Log::info($ex);
            Log::info("======= [CRM] Error send mail to PQA =======\n");
        }
    }

}
