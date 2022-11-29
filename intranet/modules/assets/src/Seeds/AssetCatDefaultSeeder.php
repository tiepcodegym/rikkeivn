<?php

namespace Rikkei\Assets\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Assets\Model\AssetGroup;
use Rikkei\Assets\Model\AssetCategory;
use Illuminate\Support\Facades\DB;

class AssetCatDefaultSeeder extends CoreSeeder
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
        $dataGroups = [
            ['name' => 'Máy tính để bàn'],
        ];
        $dataCats = [
            [
                'name' => 'Case máy tính',
                'prefix_asset_code' => 'PC',
                'is_default' => 1
            ],
            [
                'name' => 'Màn hình máy tính',
                'prefix_asset_code' => 'MH',
                'is_default' => 1
            ],
            [
                'name' => 'Chuột máy tính',
                'prefix_asset_code' => 'CH',
                'is_default' => 1
            ],
            [
                'name' => 'Bàn phím máy tính',
                'prefix_asset_code' => 'BP',
                'is_default' => 1
            ]
        ];
        DB::beginTransaction();
        try {
            $defaultGroup = null;
            foreach ($dataGroups as $index => $dataGroup) {
                $group = AssetGroup::create($dataGroup);
                if ($index == 0) {
                    $defaultGroup = $group;
                }
            }
            foreach ($dataCats as $dataCat) {
                $cat = AssetCategory::where('name', $dataCat['name'])->first();
                if ($cat) {
                    $cat->is_default = 1;
                    $cat->save();
                } else {
                    $dataCat['group_id'] = $defaultGroup->id;
                    AssetCategory::create($dataCat);
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
