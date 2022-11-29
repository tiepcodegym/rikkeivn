<?php
namespace Rikkei\Assets\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;

class AssetSC extends CoreSeeder
{
    public function run()
    {
        // $this->call(Rikkei\Assets\Seeds\AssetCatDefaultSeeder::class);
        // $this->call(\Rikkei\Assets\Seeds\InvCloseTaskSeeder::class);
        $this->call(UpdateAssetsHistorySeeder::class);
        // $this->call(Rikkei\Assets\Seeds\UpdatePrefixAsset::class);
    }
}
