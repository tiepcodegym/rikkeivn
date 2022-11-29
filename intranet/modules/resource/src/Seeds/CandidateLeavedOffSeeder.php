<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Resource\Model\Candidate;
use DB;

class CandidateLeavedOffSeeder extends CoreSeeder
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

            Candidate::cronUpdateLeavedOff();

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

}
