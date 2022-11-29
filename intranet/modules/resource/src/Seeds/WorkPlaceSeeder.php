<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;

class WorkPlaceSeeder extends CoreSeeder
{
    protected $tbl = 'work_places';

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
                'name' => 'Hà Nội',
            ],
            [
                'name' => 'Đà Nẵng',
            ],
            [
                'name' => 'Hồ Chí Minh',
            ],
            [
                'name' => 'Nhật Bản',
            ],
        ];

        DB::beginTransaction();
        try {
            foreach ($dataDemo as $data) {
                if (! DB::table($this->tbl)->select('id')->where('name', $data['name'])->get()) {
                    DB::table($this->tbl)->insert($data);
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
