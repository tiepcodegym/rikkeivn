<?php

namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Action;
use Rikkei\Core\Model\MenuItem;
use Rikkei\Core\Model\Menu;

class HrWeeklyReportMenuSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $action = Action::where('name', 'hr.weekly.report')->first();
        if (!$action) {
            return;
        }
        $menu = Menu::getMenuDefault();
        if (!$menu) {
            return;
        }
        $parent = MenuItem::where('menu_id', $menu->id)
                ->where('name', 'Resource')
                ->first();
        if (!$parent) {
            return;
        }
        $dataItem = [
            'name' => 'Hr weekly report',
            'url' => 'resource/hr-weekly-report',
            'state' => 1,
            'menu_id' => $menu->id,
            'sort_order' => 8,
            'parent_id' => $parent->id,
            'action_id' => $action->id
        ];

        $menuItem = MenuItem::where('menu_id', $menu->id)
            ->where('name', $dataItem['name'])
            ->first();
        if (!$menuItem) {
            $menuItem = new MenuItem();
            $menuItem->setData($dataItem);
            $menuItem->save();
        }

        $this->insertSeedMigrate();
    }

}

