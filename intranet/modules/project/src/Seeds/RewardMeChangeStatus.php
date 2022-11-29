<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\Model\MeReward;
use Exception;

class RewardMeChangeStatus extends CoreSeeder
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
            MeReward::where('status', 2)
                ->update([
                    'status' => MeReward::STT_SUBMIT
                ]);
            MeReward::where('status', 3)
                ->update([
                    'status' => MeReward::STT_APPROVE
                ]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
