<?php

namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Action;
use Rikkei\Core\Model\MenuItem;
use Rikkei\Core\Model\Menu;

class OpportunityMenuSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $actionCode = 'admin.monthly.report.edit';
        $menuParentName = 'Project';
        $action = Action::where('name', $actionCode)->first();
        if (!$action) {
            return;
        }
        $menu = Menu::getMenuDefault();
        if (!$menu) {
            return;
        }
        $parent = MenuItem::where('menu_id', $menu->id)
                ->where('name', $menuParentName)
                ->first();
        if (!$parent) {
            return;
        }
        $dataItem = [
            'name' => 'Opportunity',
            'url' => 'opportunity/index',
            'state' => 1,
            'menu_id' => $menu->id,
            'sort_order' => 7,
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

