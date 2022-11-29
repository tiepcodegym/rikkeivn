<?php
namespace Rikkei\Core\Seeds;

use Rikkei\Core\Model\MenuItem;
use DB;

class MenuItemsRemoveSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }
        if (!file_exists(RIKKEI_CORE_PATH . 'data-sample/menu-remove.php')) {
            return;
        }
        $dataDemo = require RIKKEI_CORE_PATH . 'data-sample/menu-remove.php';
        if (!$dataDemo || !count($dataDemo)) {
            return;
        }
        $collection = MenuItem::whereIn('name', $dataDemo)->get();
        DB::beginTransaction();
        try {
            foreach ($collection as $item) {
                $item->delete();
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
