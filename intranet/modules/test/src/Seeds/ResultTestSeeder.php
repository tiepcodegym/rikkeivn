<?php
namespace Rikkei\Test\Seeds;

use DB;
use Rikkei\Test\Models\Result;
use Rikkei\Test\Models\Test;
use Rikkei\Resource\Model\Candidate;

class ResultTestSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     * Transfer data to new table request team
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('ResultTestSeeder-v2')) {
            return true;
        }
        $resultTbl = Result::getTableName();
        $testTbl = Test::getTableName();

        DB::beginTransaction();
        try {
            $allTestNotAuth = Result::join("{$testTbl}", "{$testTbl}.id", "=", "{$resultTbl}.test_id")
                ->where('is_auth', Test::IS_NOT_AUTH)->select("{$resultTbl}.id", 'employee_email')->get();
            foreach ($allTestNotAuth as $result) {
                $candidate = Candidate::where('email', $result->employee_email)->first();
                if ($candidate) {
                    $update = Result::find($result->id);
                    $update->candidate_id = $candidate->id;
                    $update->save();
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            $ex.getMessage();
        }
    }
}
