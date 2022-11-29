<?php

namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Employee;

class EmployeeCardIdSeeder extends CoreSeeder
{
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $employee = Employee::where('email', 'nhantv@rikkeisoft.com')->first();
        if ($employee) {
            if ($employee->employee_code == 'DN0000073') {
                return;
            }
            $employee->update([
                'employee_card_id' => '73',
                'employee_code' => 'DN0000073'
            ]);
        }
        $this->insertSeedMigrate();
    }
}

