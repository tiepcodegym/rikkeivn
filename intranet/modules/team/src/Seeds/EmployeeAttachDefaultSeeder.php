<?php
namespace Rikkei\Team\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\EmployeeAttach;
use Rikkei\Team\Model\Employee;

class EmployeeAttachDefaultSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(2)) {
            return true;
        }

        DB::beginTransaction();
        try {
            $dataDefault = [
                ['title' => 'Chứng minh thư', 'required' => 1],
                ['title' => 'Bằng đại học', 'required' => 1],
                ['title' => 'Hộ chiếu', 'required' => 0],
                ['title' => 'Tài liệu khác', 'required' => 0]
            ];

            $employees = Employee::select('id')->get();
            if (!$employees->isEmpty()) {
                $timeNow = \Carbon\Carbon::now()->toDateTimeString();
                $dataInsert = [];
                foreach ($employees as $emp) {
                    foreach ($dataDefault as $data) {
                        $attach = EmployeeAttach::where('employee_id', $emp->id)
                                ->where('title', $data['title'])
                                ->first();
                        if (!$attach) {
                            $data['employee_id'] = $emp->id;
                            $data['created_at'] = $timeNow;
                            $data['updated_at'] = $timeNow;
                            $dataInsert[] = $data;
                        }
                    }
                }
                if ($dataInsert) {
                    EmployeeAttach::insert($dataInsert);
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
