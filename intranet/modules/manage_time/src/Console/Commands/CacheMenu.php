<?php

namespace Rikkei\ManageTime\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\Menu as MenuModel;
use Rikkei\Core\Model\MenuItem;
use URL;
use Illuminate\Support\Str;
use Rikkei\Core\View\CacheBase;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\CacheHelper;

class CacheMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache-menu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache menu for all employee';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            Log::info('=== Start cache menu ===');
            $this->info('=== Start cache menu ===');
            
            $employees = Employee::select(['id', 'name', 'email'])
                ->whereNull('deleted_at')
                ->where(function ($query) {
                    $query->whereNull('leave_date')
                        ->orWhereRaw('DATE(leave_date) > CURDATE()');
                })
                ->orderBy("id", 'desc')
                ->get();
            $arrlang = ['vi', 'en', 'jp'];

            $menuSetting = MenuModel::where('state', MenuModel::FLAG_SETTING)->first();
            foreach ($employees as $emp) {
                foreach ($arrlang as $lang) {
                    //cahe menu main
                    $this->cacheMenu($emp, $lang);
                    if ($menuSetting && $menuSetting->id) {
                        //cache menu right
                        $this->cacheMenu($emp, $lang, $menuSetting->id, 1);
                    }
                }
            }
            
            Log::info('=== End cache menu ===');
            $this->info('=== End cache menu ===');
        } catch (Exception $e) {
            $this->info($e->getMessage());
            Log::error($e);
        }
    }

    public function cacheMenu($emp, $lang, $menuId = null, $level = 0)
    {
        $nameMenu = 'name';
        if ($lang == 'jp') {
            $nameMenu = 'ja_name';
        }
        if ($lang == 'en') {
            $nameMenu = 'en_name';
        }

        if (!$menuId) {
            $menuIdDb = MenuModel::getMenuDefault();
            if (!$menuIdDb) {
                return;
            }
            $menuIdDb = $menuIdDb->id;
        } else {
            $menuIdDb = $menuId;
        }

        $allMenuItem = MenuItem::where('menu_id', $menuIdDb)
            ->select(['id', 'parent_id', 'action_id', 'url', 'sort_order', "{$nameMenu} as name"])
            ->where('state', MenuItem::STATE_ENABLE)
            ->orderBy('sort_order', 'asc')
            ->get();
        if (!count($allMenuItem)) {
            return null;
        }
        $menuItems = null;
        $this->menuPathTree($allMenuItem, $menuItems, null);
        if (!CacheHelper::get(MenuItem::KEY_CACHE)) {
            CacheHelper::put(MenuItem::KEY_CACHE, $menuItems, $menuIdDb);
        }

        $htmlMenu = self::getMenuUser($emp, $menuItems, null, $level);
        CacheBase::putFile(CacheBase::MENU_USER, $emp->id . $lang . '-' . $menuId, $htmlMenu);
    }

    public function menuPathTree (
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

    public static function getMenuUser($emp, $menuItems, $parentId, $level = 0)
    {
        $html = '';
        if (!count($menuItems) || !isset($menuItems[$parentId])) {
            return;
        }
        $itemParent = $menuItems[$parentId];
        if (!$itemParent['child']) {
            return;
        }
        $employee = Employee::where('email', $emp->email)->first();
        // $permission = Permission::getInstance($employee);
        $permission = new Permission($employee);
        foreach ($itemParent['child'] as $key => $itemId) {
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
                $htmlMenuChild = self::getMenuUser($emp, $menuItems, $itemId, 1);
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
