<?php
namespace Rikkei\Resource\Seeds;

use DB;
use Rikkei\Resource\View\getOptions;

class EmployeeContractPointSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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

        $dataDemo = [
            [
                'contract_type' => '0',
                'point' => '0.00',
            ],
            [
                'contract_type' => getOptions::WORKING_PROBATION,
                'point' => '1.00',
            ],
            [
                'contract_type' => getOptions::WORKING_INTERNSHIP,
                'point' => '0.00',
            ],
            [
                'contract_type' => getOptions::WORKING_PARTTIME,
                'point' => '0.50',
            ],
            [
                'contract_type' => getOptions::WORKING_OFFICIAL,
                'point' => '1.00',
            ],
            [
                'contract_type' => getOptions::WORKING_UNLIMIT,
                'point' => '1.00',
            ],
            [
                'contract_type' => getOptions::WORKING_BORROW,
                'point' => '0.00',
            ]
        ];
        foreach ($dataDemo as $data) {
            if (! DB::table('employee_contract_point')->select('id')->where('contract_type', $data['contract_type'])->get()) {
                DB::table('employee_contract_point')->insert($data);
            }
        }
        $this->insertSeedMigrate();
    }
}
