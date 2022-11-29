<?php
namespace Rikkei\ManageTime\Seeds;

use DB;
use Exception;
use Carbon\Carbon;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Seeds\CoreSeeder;

class UpdateTrialDateSeeder extends CoreSeeder
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
            $this->updateTrialDate();
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /*
     * Update trial date for employee trainee
     */
    protected function updateTrialDate()
    {
        $tblEmployee = Employee::getTableName();
        $tblCandidate = Candidate::getTableName();
        $employees = Employee::select("{$tblEmployee}.id", "{$tblCandidate}.id as candidate_id", "{$tblCandidate}.contract_length", "{$tblCandidate}.trial_work_end_date")
            ->join("{$tblCandidate}", "{$tblCandidate}.employee_id", "=", "{$tblEmployee}.id")
            ->where("{$tblCandidate}.working_type", getOptions::WORKING_PROBATION)
            ->whereNull("{$tblEmployee}.trial_date")
            ->whereNull("{$tblCandidate}.trial_work_start_date")
            ->whereNotNull("{$tblCandidate}.contract_length")
            ->get();
        if (!count($employees)) {
            return;
        }
        foreach ($employees as $emp) {
            if (!$emp->contract_length || !$emp->trial_work_end_date) {
                continue;
            }
            $contractLength = (int) $emp->contract_length;
            $trialDate = Carbon::parse($emp->trial_work_end_date)->subMonths($contractLength);
            $emp->trial_date = $trialDate->format('Y-m-d');
            $emp->save();
            $candidate = Candidate::where('id', $emp->candidate_id)->update(['trial_work_start_date' => $trialDate]);
        }
    }
}
