<?php

namespace Rikkei\Sales\Console\Commands;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Sales\Model\Company;
use Rikkei\Team\Model\Employee;

class SetCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:set';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create, update, deleted company';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    protected $domain;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->domain = config('sales.api_base_url');

        DB::beginTransaction();
        try {
            Log::info("======= [CRM] Start insert, update, delete company =======");
            $dataToken = $this->getToken();
            if (!$dataToken) {
                Log::info("======= [CRM] Error get api token company =======");
                return;
            }
            $dataToken = json_decode($dataToken);
            $dataResponse = $this->getCompanyCrm($dataToken->access_token);
            $dataCompanyCrm = $dataResponse->data;
            if (!$dataCompanyCrm) {
                Log::info("======= Không tìm thấy dữ liệu đồng bộ =======");
                Log::info("======= [CRM] End set company company =======");
                return;
            }
            $crmIds = [];
            $email = [];
            foreach($dataCompanyCrm as $key => $item) {
                $crmIds[] = $item->id;
                $email[] = $item->account_manager;  //account được hiểu là email
                $email[] = $item->sale_support;
                $email[] = $item->account_creator;
            }
            $dataCompany = $this->getCompanyByCrmId($crmIds);
            $employeeEmail = $this->getEmployeeByEmail($email);
            $this->insertOrUpdateCompany($dataCompanyCrm, $dataCompany, $employeeEmail);
            Log::info("======= [CRM] End set company company =======");
            DB::commit();
            return;
        } catch (\Exception $ex) {
            DB::rollback();
            Log::info($ex);
            Log::info("======= [CRM] Error insert, update, delete company =======\n");
        }
    }

    /**
     * getToken
     *
     * @return array
     */
    public function getToken()
    {
        $client = new Client();
        $url = $this->domain . '/Api/index.php/access_token';
        $options['headers'] = [
            'Content-Type' => 'application/json',
        ];

        $options['body'] = json_encode([
            'grant_type' => 'client_credentials',
            'client_id' => config('sales.client_id'),
            'client_secret' => config('sales.client_secret'),
        ]);
        $response = $client->post($url, $options);
        return $response->getBody()->getContents();
    }

    /**
     * getCompany
     *
     * @param  string $token
     * @param  date $date (Y-m-d)
     * @return object
     */
    public function getCompanyCrm($token, $date = null)
    {
        if (!$token) return [];
        if (!$date) {
            //crm timezone 0, rikkei timezone +7
            $date = Carbon::now()->subHours(7)->format('Y-m-d');
        }

        $client = new Client();
        $url = $this->domain . '/Api/index.php/V8/custom/customer/date?date=' . $date;
        $accessToken = 'Bearer ' . $token;
        $options['headers'] = [
            'Content-Type' => 'application/json',
            'Authorization' => $accessToken,
        ];
        $response = $client->get($url, $options);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * getCompanyByCrmId
     *
     * @param  array $crmId
     * @return array
     */
    public function getCompanyByCrmId($crmIds)
    {
        $objCompany = new Company();
        $companies = $objCompany->getCompaniesByCrmId($crmIds);
        if ($companies) {
            return $companies->keyBy('crm_account_id');
        }
        return [];
    }

    
    /**
     * getEmployeeByEmail
     *
     * @param  array $emails
     * @return array
     */
    public function getEmployeeByEmail($emails)
    {
        $collection = Employee::select('email', 'id')
            ->whereIn("email", $emails)
            ->get();
        if (count($collection)) {
            return $collection->keyBy('email');
        }
        return [];
    }

    /**
     * insertOrUpdateCompany
     *
     * @param  array $dataCompanyCrm
     * @param  array $dataCompany
     * @param  array $employeeEmail
     * @return void
     */
    public function insertOrUpdateCompany($dataCompanyCrm, $dataCompany, $employeeEmail)
    {
        $dataInsert = [];
        $dataError = [];
        $errorLog = '';
        foreach ($dataCompanyCrm as $key => $item) {
            $emailManage = $item->account_manager;
            $emailSale = $item->sale_support;
            $emailCreated = $item->account_creator;
            $idManager = isset($employeeEmail[$emailManage]) ? $employeeEmail[$emailManage]->id : null;
            $idSale = isset($employeeEmail[$emailSale]) ? $employeeEmail[$emailSale]->id : null;
            $idCreatedBy = isset($employeeEmail[$emailCreated]) ? $employeeEmail[$emailCreated]->id : null;
            if (!$idSale && $emailSale) {
                $dataError[$item->id]['emailSale'] = $emailSale;
            }
            if (!$idCreatedBy && $emailCreated) {
                $dataError[$item->id]['emailEmpCreated'] = $emailCreated;
            }
            if (isset($data[$item->id]) && $idManager) {
                $dataError[$item->id]['name_companay'] = $item->name;
            }
            if (!$idManager) {
                $dataError[$item->id]['name_companay'] = $item->name;
                $dataError[$item->id]['emailManages'] = $emailManage;
                $errorLog .= $item->id . ',';
                continue;
            }
            $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $item->date_modified);
            if (isset($dataCompany[$item->id])) {
                $objCompany = $dataCompany[$item->id];
                if ($objCompany->updated_at->format('Y-m-d H:i:s') != $item->date_modified) {   
                    $objCompany->company = $item->name;
                    $objCompany->updated_at = $dateTime;
                    $objCompany->contract_security = $item->infor_security;
                    $objCompany->contract_quality = $item->quality;
                    $objCompany->contract_other = $item->other;
                    $objCompany->manager_id = $idManager;
                    $objCompany->sale_support_id = $idSale;
                    $objCompany->crm_account_id = $item->id;
                    $objCompany->created_by = $idCreatedBy;
                    if ($item->deleted) {
                        $objCompany->deleted_at = $dateTime;
                    }
                    $objCompany->save();
                }
            } else {
                if (!$item->deleted) {
                    $data = [
                        'company' => $item->name,
                        'created_at' => Carbon::createFromFormat('Y-m-d H:i:s', $item->date_entered),
                        'updated_at' => $dateTime,
                        'created_by' => $idCreatedBy,
                        'contract_security' => $item->infor_security,
                        'contract_quality' => $item->quality,
                        'contract_other' => $item->other,
                        'manager_id' => $idManager,
                        'sale_support_id' => $idSale,
                        'crm_account_id' => $item->id,
                    ];
                    $dataInsert[] = $data;
                }
            }
        }
        if ($dataError) {
            Log::info("======= [CRM] không tìm thấy người quản lý: {$errorLog} =======");
            $this->sendEmailError($dataError);
        }
        if ($dataInsert) {
            Company::insert($dataInsert);
        }
        return;
    }

    /**
     * send email when error
     *
     * @param  array $data
     * @return
     */
    public function sendEmailError($data)
    {
        // $email = Config('mail.username');
        $email = 'hungnt2@rikkeisoft.com';
        $title = '[Company][Error] Đồng bộ dữ liệu company từ crm';
        $template = 'sales::css.email.crm_company';
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($email)
            ->setSubject($title)
            ->setTemplate($template, $data)
            ->save();
        return;
    }
}
