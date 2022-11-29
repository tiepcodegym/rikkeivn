<?php

namespace Rikkei\Project\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rikkei\Project\View\TimesheetHelper;
use Rikkei\Core\View\CurlHelper;
use Illuminate\Support\Facades\Storage;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Project\Model\Project;

class SyncPoIdToCRM extends Command
{
    const URI_SAVE_PURCHASE_ID_TO_CRM = '/Api/V8/custom/po/update_project';
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:sync_poid_to_crm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync po_id to CRM';

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
            $this->info("======= Start Sync po_id to CRM =======\n");
            $projects = Project::where('status', 1)->whereNotNull('po_id')->get();
            foreach ($projects as $key => $project) {
                $ids = $project->po_id;
                $arrPoIds = explode(',', $ids);
                $pmActive = $project->getPMActive();
                $params = [
                    'po_id' => $arrPoIds,
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'pm_name' => $pmActive->name,
                ];
                
                $result = $this->savePurchaseIdToCRM($params);
                if (!$result) {
                    $emailQueue = new EmailQueue();
                    $subject = 'Lỗi không đồng bộ được Purchase Order ID sang CRM';
                    $data = [
                        'projectName' => $request->get('projectName'),
                        'url' => route('project::project.edit', ['id' => $project->id])
                    ];
                    $emailQueue->setTo('hungnt2@rikkeisoft.com', 'hungnt2')
                        ->setTemplate('project::emails.notify_error_sync_poid_crm', $data)
                        ->setSubject($subject)
                        ->save();
                }
            }
            $this->info("======= End Sync po_id to CRM =======\n");
        } catch (\Exception $ex) {
            Log::info($ex);
            $this->info("======= Error Sync po_id to CRM =======\n");
        }
    }

    public function savePurchaseIdToCRM($param){
        $tokenHelper = new TimesheetHelper();
        $token = $tokenHelper->getToken();
        $header = [
            "Authorization: Bearer {$token}",
            "Content-Type: application/x-www-form-urlencoded",
        ];
        $url = config('sales.api_base_url') . self::URI_SAVE_PURCHASE_ID_TO_CRM;        

        $response = CurlHelper::httpPost($url, $param, $header);
        $response = json_decode($response, true);
        if (!isset($response['data']['success']) || $response['data']['success'] == false || $response['data']['success'] == 'false') {
            Storage::put('sale_token.json', '');
            Log::info('Không có data');
            Log::info(print_r($response, true));
            return false;
        }
        return $response['data']['success'];
    }
}
