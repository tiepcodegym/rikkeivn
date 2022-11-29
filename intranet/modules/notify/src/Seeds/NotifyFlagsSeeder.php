<?php

namespace Rikkei\Notify\Seeds;

use Exception;
use Rikkei\Core\Seeds\CoreSeeder;
use DB;
use Rikkei\Notify\Model\NotifyFlag;
use Rikkei\Team\Model\Employee;

class NotifyFlagsSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return bool
     * @throws \Exception
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }
        DB::beginTransaction();
        try {
            NotifyFlag::truncate();
            $employeeIds = Employee::select('id as employee_id')->get()->toArray();
            NotifyFlag::insert($employeeIds);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
