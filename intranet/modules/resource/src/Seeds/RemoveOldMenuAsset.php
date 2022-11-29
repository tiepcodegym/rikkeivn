<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;
use Rikkei\Core\Model\MenuItem;

class RemoveOldMenuAsset extends CoreSeeder
{
    protected $tbl = 'menu_items';

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
            $menu = MenuItem::where('name', 'Request asset')->first();
            if ($menu) {
                MenuItem::where('parent_id', $menu->id)->delete();
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
