<?php
namespace Rikkei\Recruitment\Seeds;

use DB;
use Rikkei\Recruitment\Model\RecruitmentCampaign;
use Rikkei\Recruitment\Model\RecruitmentApplies;
use Rikkei\Team\Model\Employee;

class RecruitmentAppliesSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        
        $dataDemo = [
            [
                'campaign_code' => 'rc1',
                'email' => 'r1@gmail.com',
                'name' => 'r1 name',
                'phone' => '0912345678',
            ],
            [
                'campaign_code' => 'rc2',
                'email' => 'r2@gmail.com',
                'name' => 'r2 name',
                'phone' => '0922345678',
            ],
            [
                'campaign_code' => 'rc3',
                'email' => 'r3@gmail.com',
                'name' => 'r3 name',
                'phone' => '0932345678',
            ],
            [
                'campaign_code' => 'rc4',
                'email' => 'r4@gmail.com',
                'name' => 'r4 name',
                'phone' => '0942345678',
            ],
        ];
        DB::beginTransaction();
        try {
            foreach ($dataDemo as $data) {
                $recruitmentApllies = RecruitmentApplies::where('phone', $data['phone'])->first();
                if (! $recruitmentApllies) {
                    $recruitmentCampaign = RecruitmentCampaign::where('code', $data['campaign_code'])->first();
                    if (! $recruitmentCampaign) {
                        continue;
                    }
                    // get employee random to insert presenter_id
                    $employeeRandom = Employee::select('id')
                        ->orderBy(DB::raw('RAND()'))
                        ->first();
                    if (! $employeeRandom) {
                        return;
                    }
                    unset($data['campaign_code']);
                    $recruitmentApllies = new RecruitmentApplies();
                    $recruitmentApllies->setData($data);
                    $recruitmentApllies->campaign_id = $recruitmentCampaign->id;
                    $recruitmentApllies->presenter_id = $employeeRandom->id;
                    $recruitmentApllies->save();
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
