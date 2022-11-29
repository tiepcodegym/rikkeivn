<?php

namespace Rikkei\Sales\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Rikkei\Sales\Model\Css;
use Rikkei\Team\Model\Employee;
use Rikkei\Sales\Model\CssMail;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Project;

class RemindCssTimeReplyOver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'css:remind_time_reply';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Send mail remind to PM, PQA when the time_reply field is overdue, but the customer has not replied yet";

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
            Log::info("======= [CSS] Start css:remind_time_reply =======");
            
            $month1 = Carbon::now()->format('Y-m-d');
            $month2 = Carbon::now()->subMonth(1)->format('Y-m-d');
            $listCss = Css::whereDate('time_reply', '>=', $month2)->whereDate('time_reply', '<', $month1)->get();
            if (count($listCss)) {
                $dataEmail = [];
                foreach ($listCss as $itemCss) {
                    if(CssMail::getCssIdClosed($itemCss->id)) {
                        $cssResult = CssResult::where('css_id', $itemCss->id)->first();
                        if (!$cssResult) {
                            $urlCss = url("/css/preview/".$itemCss->token."/".$itemCss->id);
                            $pqa = Project::getEmpByRoleInProject($itemCss->projs_id, ProjectMember::TYPE_PQA);
                            foreach ($pqa as $itemPQA) {
                                if (!isset($dataEmail[$itemPQA->email])) {
                                    $dataEmail[$itemPQA->email]['emp_name'] = $itemPQA->name;
                                }
                                $dataEmail[$itemPQA->email]['css'][] = $urlCss;
                            }
        
                            $pm = Project::getEmpByRoleInProject($itemCss->projs_id, ProjectMember::TYPE_PM);
                            foreach ($pm as $itemPM) {
                                if (!isset($dataEmail[$itemPM->email])) {
                                    $dataEmail[$itemPM->email]['emp_name'] = $itemPM->name;
                                }
                                $dataEmail[$itemPM->email]['css'][] = $urlCss;
                            }
                        }
                    }
                }
                $this->sendMail($dataEmail);
            }
            
            Log::info("======= [CRM] End css:remind_time_reply =======");
            DB::commit();
            return;
        } catch (\Exception $ex) {
            DB::rollback();
            Log::info($ex);
            Log::info("======= [CRM] Error css:remind_time_reply =======\n");
        }
    }

    public function sendMail($dataEmail)
    {
        foreach ($dataEmail as $email => $data) {
            $subject = '【CSS】Có CSS đã quá thời gian mong muốn trả lời nhưng chưa được khách hàng đánh giá';
            $template = 'sales::css.email.cssMailTimeReply';
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($email)
                    ->setFrom('intranet@rikkeisoft.com', 'Rikkeisoft')
                    ->setSubject($subject)
                    ->setTemplate($template, $data)
                    ->save();
        }
    }
}
