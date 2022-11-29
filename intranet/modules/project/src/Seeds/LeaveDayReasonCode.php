<?php
namespace Rikkei\Project\Seeds;

use DB;
use Rikkei\ManageTime\Model\LeaveDayReason;

class LeaveDayReasonCode extends \Rikkei\Core\Seeds\CoreSeeder
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
        $dataCode = [
            [
                'name' => 'Nghỉ thai sản',
                'code' => LeaveDayReason::CODE_MATERNITY,
            ],
            [
                'name' => 'Nghỉ không phép',
                'code' => LeaveDayReason::CODE_UNPAID_LEAVE,
            ]
        ];

        foreach ($dataCode as $data) {
            LeaveDayReason::where([
                ['name', $data['name']]
            ])->update(['code' => $data['code']]);
        }

        $this->insertSeedMigrate();
    }
}
