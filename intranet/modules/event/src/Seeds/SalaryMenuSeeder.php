<?php

namespace Rikkei\Event\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Action;
use Rikkei\Core\Model\MenuItem;
use Rikkei\Core\Model\Menu;

class SalaryMenuSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $action = Action::where('name', 'event.send.mail.salary')->first();
        if (!$action) {
            return;
        }
        $menu = Menu::getMenuDefault();
        if (!$menu) {
            return;
        }
        $parent = MenuItem::where('menu_id', $menu->id)
                ->where('name', 'Send email')
                ->first();
        if (!$parent) {
            return;
        }
        $dataItem = [
            'name' => 'Thông tin lương',
            'url' => 'event/send/email/employees/salary',
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
