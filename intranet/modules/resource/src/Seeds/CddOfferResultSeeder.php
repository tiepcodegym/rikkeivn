<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Illuminate\Support\Facades\DB;

class CddOfferResultSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        try {
            Candidate::where('offer_result', getOptions::RESULT_WORKING)
                    ->update(['offer_result' => getOptions::RESULT_PASS]);

            $this->insertSeedMigrate();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
