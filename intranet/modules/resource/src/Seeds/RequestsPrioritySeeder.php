<?php
namespace Rikkei\Resource\Seeds;

use Carbon\Carbon;
use DB;
use Rikkei\Resource\Model\RequestPriority;

class RequestsPrioritySeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
            $data = [
                [
                    'name' => 'Gấp',
                    'name_en' => 'Urgent',
                    'name_jp' => '緊急',
                    'state' => '1',
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ],
                [
                    'name' => 'Bình thường',
                    'name_en' => 'Normal',
                    'name_jp' => '正常',
                    'state' => '1',
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ],
                [
                    'name' => 'Chưa xác định',
                    'name_en' => 'Undefined',
                    'name_jp' => '未定義',
                    'state' => '1',
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ],
            ];
            RequestPriority::insert($data);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            $ex.getMessage();
        }
    }
}
