<?php

namespace Rikkei\Team\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Resource\View\getOptions;

class UpdateEmployeeWorkSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(3)) {
            return true;
        }
        DB::beginTransaction();
        try {
            /*
             * update contract type
             * 1 -> 5
             * 2 -> 4
             * 3 -> 3
             * 4 -> 1
             * 5 -> 2
             */
            //list
            /*
             * dao nguoc:
             * 5 -> 1
             * 4 -> 2
             * 1 -> 4
             * 2 -> 5
             */
            $list1 = EmployeeWork::where('contract_type', 1)->lists('employee_id')->toArray();
            $list2 = EmployeeWork::where('contract_type', 2)->lists('employee_id')->toArray();
            $list4 = EmployeeWork::where('contract_type', 4)->lists('employee_id')->toArray();
            $list5 = EmployeeWork::where('contract_type', 5)->lists('employee_id')->toArray();
            //update
            /*EmployeeWork::whereIn('employee_id', $list1)->update(['contract_type' => getOptions::WORKING_UNLIMIT]);
            EmployeeWork::whereIn('employee_id', $list2)->update(['contract_type' => getOptions::WORKING_OFFICIAL]);
            EmployeeWork::whereIn('employee_id', $list4)->update(['contract_type' => getOptions::WORKING_PROBATION]);
            EmployeeWork::whereIn('employee_id', $list5)->update(['contract_type' => getOptions::WORKING_INTERNSHIP]);*/

            EmployeeWork::whereIn('employee_id', $list2)->update(['contract_type' => getOptions::WORKING_UNLIMIT]); //5 2->5
            EmployeeWork::whereIn('employee_id', $list1)->update(['contract_type' => getOptions::WORKING_OFFICIAL]); //4 1->4
            EmployeeWork::whereIn('employee_id', $list5)->update(['contract_type' => getOptions::WORKING_PROBATION]); //1 5->1
            EmployeeWork::whereIn('employee_id', $list4)->update(['contract_type' => getOptions::WORKING_INTERNSHIP]); //2 4->2

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
