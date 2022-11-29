<?php
namespace Rikkei\Team\Seeds;

use Carbon\Carbon;
use Rikkei\Core\Seeds\CoreSeeder;
use DB;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Team\Model\EmplCvAttrValueText;
use Rikkei\Team\Model\EmployeeProjExper;

class AddEmplAttrValueProjectSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception $ex
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return;
        }
        DB::beginTransaction();
        try {
            $allProjects = EmployeeProjExper::select(['id', 'employee_id', 'lang_code', 'proj_number'])
                ->get();
            $empCvAttrValues = EmplCvAttrValue::where('code', 'LIKE', 'proj_%')
                ->pluck('code', 'code');
            $empCvAttrValueTexts = EmplCvAttrValueText::where('code', 'LIKE', 'proj_%')
                ->pluck('code', 'code');
            $aryEmpCvAttrValuesInsert = [];
            $aryEmpCvAttrValueTextsInsert = [];
            $now = Carbon::now()->toDateTimeString();
            foreach ($allProjects->groupBy('employee_id') as $employee) { // array project of employee
                foreach ($employee->groupBy('proj_number') as $groupProjects) { // array project same project number
                    if ($groupProjects->count() !== 2) {
                        continue;
                    }
                    foreach ($groupProjects as $project) {
                        $keyName = "proj_{$project->id}_name_{$project->lang_code}";
                        $keyDesc = "proj_{$project->id}_description_{$project->lang_code}";
                        if (!isset($empCvAttrValues[$keyName])) {
                            $aryEmpCvAttrValuesInsert[] = [
                                'employee_id' => $project->employee_id,
                                'code' => $keyName,
                                'value' => '',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                        if (!isset($empCvAttrValueTexts[$keyDesc])) {
                            $aryEmpCvAttrValueTextsInsert[] = [
                                'employee_id' => $project->employee_id,
                                'code' => $keyDesc,
                                'value' => '',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }
            }
            EmplCvAttrValue::insert($aryEmpCvAttrValuesInsert);
            EmplCvAttrValueText::insert($aryEmpCvAttrValueTextsInsert);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log($ex->getMessage());
            throw $ex;
        }
    }
}
