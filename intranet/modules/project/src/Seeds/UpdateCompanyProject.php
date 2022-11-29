<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\Project;
use Illuminate\Support\Facades\DB;

class UpdateCompanyProject extends CoreSeeder
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
        DB::beginTransaction();
        try {
            $companyIdByCusId = DB::table('cust_contacts')->pluck('company_id', 'id');
            foreach ($companyIdByCusId as $customerId => $companyId) {
                Project::where('cust_contact_id', $customerId)
                    ->update([
                        'company_id' => $companyId
                    ]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
