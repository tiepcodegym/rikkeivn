<?php
namespace Rikkei\Sales\Seeds;

use DB;
use Rikkei\Sales\Model\Company;
use Rikkei\Sales\Model\Customer;

class ContractSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $allCompanies = Company::all();
        foreach ($allCompanies as &$company) {
            $customers = Customer::where('company_id', $company->id)->get();
            $contract = '';
            foreach ($customers as $customer) {
                $contract .= $customer->contract;
            }
            $company->contract_security = $contract;
            $company->save();
        }
        $this->insertSeedMigrate();
    }
}
