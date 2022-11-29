<?php

namespace Rikkei\Assets\Seeds;

use DB;
use Rikkei\Assets\Model\AssetItem;

class UpdatePrefixAsset extends \Rikkei\Core\Seeds\CoreSeeder
{
    protected $tbl = 'manage_asset_items';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        DB::beginTransaction();
        try {
            $data = AssetItem::select('code', 'id')->get();
            foreach ($data as $item) {
                $prefix = substr($item->code, 0, 2);
                if (!in_array($prefix, ['DN', 'NB'])) {
                    $prefix = 'HN';
                }
                $item->update([
                    'prefix' => $prefix
                ]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
