<?php
namespace Rikkei\ManageTime\Seeds;

use DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Employee;
use Rikkei\ManageTime\Model\TimekeepingNotLate;

class TimekeepingDataNotLateSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return;
        }
        $data = [
            'hoa.dang@rikkeisoft.com',
            'sonbx@rikkeisoft.com',
            'luanhh@rikkeisoft.com',
            'thanhlvt@rikkeisoft.com',
            'anhptl@rikkeisoft.com',
            'dung.phan@rikkeisoft.com',
            'tung.ta@rikkeisoft.com',
            'manhlk@rikkeisoft.com',
            'bauhm@rikkeisoft.com',
            'hoannv@rikkeisoft.com',
            'quynhnh@rikkeisoft.com',
            'quangnv@rikkeisoft.com',
            'dungtm@rikkeisoft.com',
            'minhln@rikkeisoft.com',
            'longnh2@rikkeisoft.com',
            'tannm@rikkeisoft.com',
            'manhnt@rikkeisoft.com',
            'tungvt2@rikkeisoft.com',
            'hainv@rikkeisoft.com',
            'lamnv3@rikkeisoft.com',
            'trangntq@rikkeisoft.com',
        ];

        $dataWednesday = [
            'hoant2@rikkeisoft.com',
        ];

        DB::beginTransaction();
        try {
            $dataEmail = array_merge($data, $dataWednesday);
            $employee = Employee::whereIn('email', $dataEmail)->get();
            $dataUpdate = [];
            $obj = new TimekeepingNotLate();
            $strFullWeek = $obj->getStrFullWeek();
            foreach ($employee as $item) {
                if (in_array($item->email, $data)) {
                    $dataUpdate[] = [
                        'employee_id' => $item->id,
                        'weekdays' => $strFullWeek,
                    ];
                } else {
                     $dataUpdate[] = [
                        'employee_id' => $item->id,
                        'weekdays' => TimekeepingNotLate::WEDNESDAY,
                    ];
                }
            }

            if ($dataUpdate) {
                foreach ($dataUpdate as $item) {
                    TimekeepingNotLate::updateOrCreate([
                        'employee_id' => $item['employee_id']
                    ], $item);
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
