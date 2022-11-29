<?php
namespace Rikkei\Team\Seeds;

use DB;
use Exception;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\RelationNames;

class RelationsNameSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(5)) {
            return true;
        }
        $dataDemo = [
            [
                'id' => 1,
                'name' => 'Anh',
            ],
            [
                'id' => 2,
                'name' => 'Bà',
            ],
            [
                'id' => 3,
                'name' => 'Bố',
            ],
            [
                'id' => 4,
                'name' => 'Chị',
            ],
            [
                'id' => 5,
                'name' => 'Chồng',
            ],
            [
                'id' => 6,
                'name' => 'Con Gái',
            ],
            [
                'id' => 7,
                'name' => 'Con Trai',
            ],
            [
                'id' => 8,
                'name' => 'Em Gái',
            ],
            [
                'id' => 9,
                'name' => 'Em Trai',
            ],
            [
                'id' => 10,
                'name' => 'Vợ',
            ],
            [
                'id' => 11,
                'name' => 'Mẹ',
            ],
            [
                'id' => 30,
                'name' => 'Khác',
            ],
        ];
        if (! $dataDemo || ! count($dataDemo)) {
            return;
        }
        DB::beginTransaction();
        try {
            RelationNames::truncate();
            foreach ($dataDemo as $item) {
                $model = RelationNames::withTrashed()->find($item['id']);
                if (!$model) {
                    $model = new RelationNames();
                }
                $model->setData([
                    'id' => $item['id'],
                    'name'  => $item['name'],
                    'deleted_at' => null,
                ]);
                $model->save();
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
