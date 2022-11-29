<?php

namespace Rikkei\Core\Console\Commands;

use URL;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Rikkei\Core\Model\Menu;
use Rikkei\Core\Model\MenuItem;
use Rikkei\Core\View\CacheBase;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Permission;

class MenuSaveCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:save_cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menu save cache';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $langMenu = Menu::getLang();
        $nameMenu = 'name';
        $menuId = '';
        $level = 0;
        try {
            $employees = Employee::getAllEmployee();
            foreach ($langMenu as $key => $lang) {
                if ($lang == 'jp') {
                    $nameMenu = 'ja_name';
                }
                if ($lang == 'en') {
                    $nameMenu = 'en_name';
                }
                $menuIdDb = Menu::getMenuDefault();

                $allMenuItem = MenuItem::where('menu_id', $menuIdDb)
                    ->select(['id', 'parent_id', 'action_id', 'url', 'sort_order', "{$nameMenu} as name"])
                    ->where('state', MenuItem::STATE_ENABLE)
                    ->orderBy('sort_order', 'asc')
                    ->get();
                if (!count($allMenuItem)) {
                    return null;
                }
                foreach($employees as $employee) {
                    if (CacheBase::hasFile(CacheBase::MENU_USER, $employee->id . $lang . '-' . $menuId)) {
                        return CacheBase::getFile(CacheBase::MENU_USER, $employee->id . $lang . '-' . $menuId);
                    }
                    $menuItems = null;
                    self::menuPathTree($allMenuItem, $menuItems, null);
                    CacheHelper::put(MenuItem::KEY_CACHE, $menuItems, $menuIdDb);

                    $htmlMenu = self::getMenuUser($menuItems, null, $level);
                    CacheBase::putFile(CacheBase::MENU_USER, $employee->id . $lang . '-' . $menuId, $htmlMenu);
                }
            }
        } catch (\Exception $ex) {
            Log::error($ex);
        }
    }

    /**
     * call recursive of menu path tree
     *
     * @param collection $collection
     * @param array $result
     * @param int $idParentCheck
     * @return boolean
     */
    protected static function menuPathTree (
        &$collection,
        &$result,
        $idParentCheck,
        $level = 0
    ) {
        if (!count($collection)) {
            return true;
        }
        foreach ($collection as $keyIndex => $item) {
            // init element result
            $item->id = (int) $item->id;
            if (!isset($result[$item->id])) {
                $result[$item->id] = [
                    'parent' => [],
                    'child' => [],
                    'data' => []
                ];
            }
            $result[$item->id]['data'] = $item->toArray();
            if ($item->parent_id != $idParentCheck) {
                continue;
            }
            if (!isset($result[$idParentCheck])) {
                $result[$idParentCheck] = [
                    'parent' => [],
                    'child' => [],
                    'data' => []
                ];
            }
            // insert array: parent in db + array parent of parent
            $result[$item->id]['parent'] =
                array_merge([$idParentCheck], $result[$idParentCheck]['parent']);
            // insert child element
            $result[$idParentCheck]['child'][] = $item->id;
            $collection->forget($keyIndex);
            self::menuPathTree($collection, $result, $item->id, $level);
        }
    }

    /**
     * get html menu tree
     *  call recursive
     *
     * @param array $menuItems
     * @return string
     */
    protected static function getMenuUser($menuItems, $parentId, $level = 0)
    {
        $html = '';
        if (!count($menuItems) || !isset($menuItems[$parentId])) {
            return;
        }
        $itemParent = $menuItems[$parentId];
        if (!$itemParent['child']) {
            return;
        }
        $permission = Permission::getInstance();
        foreach ($itemParent['child'] as $itemId) {
            if (!isset($menuItems[$itemId])) {
                continue;
            }
            $item = $menuItems[$itemId];
            //check permission menu of current user logged}
            if ($item['data']['action_id']) {
                if (!$permission->isAllow($item['data']['action_id'])) {
                    continue;
                }
            }
            $classA = '';
            $optionA = '';
            $classLi = '';
            if ($item['child']) {
                $htmlMenuChild = self::getMenuUser($menuItems, $itemId, 1);
                $classLi .= ' dropdown';
                $classA .= 'dropdown-toggle';
                $optionA .= ' data-toggle="dropdown"';
                if ($level > 0) {
                    $classLi .= ' dropdown-submenu';
                }
            }
            $classLi = $classLi ? " class=\"{$classLi}\"" : '';
            $classA = $classA ? " class=\"{$classA}\"" : '';
            if($item['data']['url'] && $item['data']['url'] != '#') {
                if (preg_match('/^http(s)?:\/\//', $item['data']['url'])) {
                    $urlMenu = e($item['data']['url']);
                } else {
                    $urlMenu = URL::to(e($item['data']['url']));
                }
            } else {
                $urlMenu = '#';
            }
            $flagShow = false;
            if (!$item['child'] || ($item['child'] && $htmlMenuChild)) {
                $nameMenu = e($item['data']['name']);
                $html .= "<li{$classLi}>";
                $html .= "<a href=\"{$urlMenu}\"{$classA}{$optionA} data-menu-slug=\"".Str::slug($nameMenu)."\">";
                $html .= $nameMenu;
                $html .= '</a>';
                $flagShow = true;
            }
            if ($item['child'] && $htmlMenuChild) {
                $html .= '<ul class="dropdown-menu" role="menu">';
                $html .= $htmlMenuChild;
                $html .= '</ul>';
            }
            if ($flagShow) {
                $html .= '</li>';
            }
        }
        return $html;
    }
}

