<?php

namespace Rikkei\Event\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Event\View\MailEmployee;

class SendMailHRday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:send_mail_HRday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send mail HR day';

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
            Log::info("======= Start send mail birthday =======\n");
            MailEmployee::sendMailHRday('core::emails.11_HRday');
            EmailQueue::sentAll();
            Log::info("======= End send mail birthday =======\n");
        } catch (\Exception $ex) {
            Log::info($ex);
            Log::info("======= Error send mail birthday =======\n");
        }
    }
}

