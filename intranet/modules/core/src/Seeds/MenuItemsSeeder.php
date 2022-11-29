<?php
namespace Rikkei\Core\Seeds;

use Rikkei\Core\Model\Menu;
use Rikkei\Core\Model\MenuItem;
use Rikkei\Team\Model\Action;
use DB;

class MenuItemsSeeder extends CoreSeeder
{
    protected $menuDefaultId = null;
    protected $menuSettingId = null;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(251)) {
            return true;
        }
        $menu = Menu::getMenuDefault();
        if ($menu) {
            $this->menuDefaultId = $menu->id;
        }
        $menu = Menu::getMenuSetting();
        if ($menu) {
            $this->menuSettingId = $menu->id;
        }
        if (!file_exists(RIKKEI_CORE_PATH . 'config/menu.php')) {
            return;
        }
        $dataDemo = require RIKKEI_CORE_PATH . 'config/menu.php';
        if (!$dataDemo || !count($dataDemo)) {
            return;
        }
        DB::beginTransaction();
        try {
            MenuItem::truncate();
            $this->createMenuItemsRecurive($dataDemo, null, 0, $this->menuDefaultId);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * create menu items recurive tree
     *
     * @param array $data
     * @param int $parentId
     * @param int $sortOrder
     * @param int $menuId
     */
    protected function createMenuItemsRecurive($data, $parentId, $sortOrder, $menuId)
    {
        foreach ($data as $key => $item) {
            if ($key == 'setting') {
                $menuId = $this->menuSettingId;
            }
            if (!$menuId) {
                continue;
            }
            $dataChild = null;
            if (isset($item['child']) && count($item['child']) > 0) {
                $dataChild = $item['child'];
                unset($item['child']);
            }
            $dataItem = [
                'name' => '',
                'url' => '',
                'state' => 1,
                'menu_id' => $menuId,
                'sort_order' => $sortOrder,
                'parent_id' => $parentId,
            ];
            if (isset($item['label']) && $item['label']) {
                $dataItem['name'] = $item['label'];
            }
            if (isset($item['label_en']) && $item['label_en']) {
                $dataItem['en_name'] = $item['label_en'];
            }
            if (isset($item['label_ja']) && $item['label_ja']) {
                $dataItem['ja_name'] = $item['label_ja'];
            }
            if (isset($item['path']) && $item['path']) {
                $dataItem['url'] = $item['path'];
            }
            if (isset($item['active']) && $item['active']) {
                $dataItem['state'] = $item['active'];
            }
            if (isset($item['action_code']) && $item['action_code']) {
                $actionPermission = Action::where('name', $item['action_code'])->first();
                if ($actionPermission) {
                    $dataItem['action_id'] = $actionPermission->id;
                }
            }
            if ($key == 'setting') {
                $menuItem = new MenuItem();
            } else {
                $menuItem = MenuItem::where('menu_id', $menuId)
                    ->where('name', $dataItem['name'])
                    ->where('parent_id', $dataItem['parent_id'])
                    ->first();
                if (!$menuItem) {
                    $menuItem = new MenuItem();
                }
                $menuItem->setData($dataItem);
                $menuItem->save();
            }
            if ($dataChild) {
                $this->createMenuItemsRecurive($dataChild, $menuItem->id, 0, $menuId);
            }
            $sortOrder++;
        }
    }
}
