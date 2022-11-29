<?php
namespace Rikkei\ManageTime\Seeds;

use DB;
use Exception;
use Carbon\Carbon;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Seeds\CoreSeeder;

class UpdateTrialEndDateSeeder extends CoreSeeder
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
            $this->updateTrialEndDate();
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /*
     * Update trial end date for employee trainee
     */
    protected function updateTrialEndDate()
    {
        $tblEmployee = Employee::getTableName();
        $tblCandidate = Candidate::getTableName();
        $employees = Employee::select("{$tblEmployee}.id", "{$tblCandidate}.id as candidate_id", "{$tblCandidate}.trial_work_end_date")
            ->join("{$tblCandidate}", "{$tblCandidate}.employee_id", "=", "{$tblEmployee}.id")
            ->where("{$tblCandidate}.working_type", getOptions::WORKING_PROBATION)
            ->whereNull("{$tblEmployee}.trial_end_date")
            ->whereNotNull("{$tblCandidate}.trial_work_end_date")
            ->get();
        if (!count($employees)) {
            return;
        }
        foreach ($employees as $emp) {
            if (!$emp->trial_work_end_date) {
                continue;
            }
            $trialEndDate = Carbon::parse($emp->trial_work_end_date);
            $emp->trial_end_date = $trialEndDate->format('Y-m-d');
            $emp->save();
        }
    }
}
