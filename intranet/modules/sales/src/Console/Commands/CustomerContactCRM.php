<?php

namespace Rikkei\Sales\Console\Commands;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Sales\Model\Customer;
use Rikkei\Sales\Model\Company;

class CustomerContactCRM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer-contact:set';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create, update, deleted cust_contact';

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
            Log::info("======= [CRM] Start insert, update, delete cust_contacts =======");
            $dataToken = $this->getToken();
            if (!$dataToken) {
                Log::info("======= [CRM] Error get api token company =======");
                return;
            }
            $dataToken = json_decode($dataToken);

            $dataResponse = $this->getCustContactCrm($dataToken->access_token);
            $dataContactCrm = $dataResponse->data;
            if (!$dataContactCrm) {
                Log::info("======= Không tìm thấy dữ liệu đồng bộ =======");
                Log::info("======= [CRM] End set cust_contacts =======");
                return;
            }

            foreach($dataContactCrm as $key => $item) {
                if (!$item->contact_id) {
                    break;
                }
                $customer = DB::table('cust_contacts')->where('crm_contact_id', $item->contact_id)->first();
                if (!$customer) {
                    #  insert
                    $dataInsert = [
                        'crm_contact_id' => $item->contact_id,
                        'name' => $item->first_name . ' ' . $item->last_name,
                        'company_id' => null,
                    ];
                    if ($item->email_address) {
                        $dataInsert['email'] = $item->email_address;
                    }
                    if ($item->deleted == 1) {
                        $dataInsert['deleted_at'] = Carbon::now();
                    }
                    $company = Company::where('crm_account_id', $item->customer_id)->first();
                    if ($company) {
                        $dataInsert['company_id'] = $company->id;
                    }
                    Customer::create($dataInsert);
                } else {
                    if ($item->deleted == 1) {
                        if ($customer->deleted_at == null) {
                            # delete
                            $customerDelete = Customer::where('crm_contact_id', $item->contact_id)->first();
                            $customerDelete->delete();
                        }
                    } else {
                        # update
                        $dataUpdate = [
                            'name' => $item->first_name . ' ' . $item->last_name,
                            'company_id' => null,
                            'deleted_at' => null,
                        ];
                        if ($item->email_address) {
                            $dataUpdate['email'] = $item->email_address;
                        }
                        $company = Company::where('crm_account_id', $item->customer_id)->first();
                        if ($company) {
                            $dataUpdate['company_id'] = $company->id;
                        }
                        $customerUpdate = Customer::where('crm_contact_id', $item->contact_id)->first();
                        $customerUpdate->update($dataUpdate);
                    }
                }
            }
            
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
     * getContact
     *
     * @param  string $token
     * @param  date $date (Y-m-d)
     * @return object
     */
    public function getCustContactCrm($token)
    {
        if (!$token) return [];
        $date = Carbon::now()->subHours(7)->format('Y-m-d');

        $client = new Client();
        $url = $this->domain . '/Api/index.php/V8/custom/contact/list?date_modified=' . $date;
        $accessToken = 'Bearer ' . $token;
        $options['headers'] = [
            'Content-Type' => 'application/json',
            'Authorization' => $accessToken,
        ];
        $response = $client->get($url, $options);
        return json_decode($response->getBody()->getContents());
    }
}
