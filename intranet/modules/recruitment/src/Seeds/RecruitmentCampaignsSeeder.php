<?php
namespace Rikkei\Recruitment\Seeds;

use Illuminate\Database\Seeder;
use DB;
use Rikkei\Recruitment\Model\RecruitmentCampaign;

class RecruitmentCampaignsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataDemo = [
            [
                'code' => 'rc1',
                'request_id' => null,
                'description' => 'description rc1',
            ],
            [
                'code' => 'rc2',
                'request_id' => null,
                'description' => 'description rc2',
            ],
            [
                'code' => 'rc3',
                'request_id' => null,
                'description' => 'description rc3',
            ],
            [
                'code' => 'rc4',
                'request_id' => null,
                'description' => 'description rc4',
            ],
        ];
        DB::beginTransaction();
        try {
            foreach ($dataDemo as $data) {
                $recruitmentCampaign = RecruitmentCampaign::where('code', $data['code'])->first();
                if (! $recruitmentCampaign) {
                    $recruitmentCampaign = new RecruitmentCampaign();
                    $recruitmentCampaign->setData($data);
                    $recruitmentCampaign->save();
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
