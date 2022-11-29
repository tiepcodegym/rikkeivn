<?php
/** 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Rikkei\Core\View;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rikkei\Core\Model\Menu as MenuModel;
use Rikkei\Core\Model\MenuItem;
use Rikkei\Team\View\Permission;
use URL;

class Menu
{
    /*
     * active menu flag
     */
    protected static $active;
    protected static $userId;
    protected static $menuHtml;


    /**
     * -- not use
     * set active menu
     * 
     * @param string $name
     * @param string $path
     * @param string $flag
     */
    public static function setActive($name = null, $path = null, $flag = null)
    {
        return null;
        /*if ($flag) {
            self::$active = $flag;
            return;
        }
        self::$active = MenuItem::getIdMenuevel0($name, $path);*/
    }

    /**
     * set active menu
     *
     * @param string $name
     */
    public static function setFlagActive($name)
    {
        self::$active = $name;
    }

    /**
     * set active menu
     *
     * @param string $name
     */
    public static function getFlagActive()
    {
        return self::$active;
    }

    /**
     * remove active menu
     */
    public static function removeActive()
    {
        self::$active = null;
    }


    /**
     * get active menu
     * 
     * @return string
     */
    public static function getActive()
    {
        return null;
        //return self::$active;
    }
    
    /**
     * check menu is active
     * 
     * @param string $id
     * @return boolean
     */
    public static function isActive()
    {
        return false;
        /*if($id == self::$active) {
            return true;
        }
        return false;*/
    }

    /**
     * get user id logged
     */
    public static function getUserLogged()
    {
        if (self::$userId) {
            return true;
        }
        self::$userId = Auth::id();
        if (!self::$userId) {
            self::$userId = -1;
        }
        return true;
    }

    /**
     * get menu html
     *
     * @param int $menuId id of menus
     * @return string
     */
    public static function get($menuId = null, $level = 0)
    {
        $lang = Session::get('locale');
        $nameMenu = 'name';
        if ($lang == 'jp') {
            $nameMenu = 'ja_name';
        }
        if ($lang == 'en') {
            $nameMenu = 'en_name';
        }

        // get menu of user form cache
        if (CacheBase::hasFile(CacheBase::MENU_USER, Auth::id() . $lang . '-' . $menuId)) {
            return CacheBase::getFile(CacheBase::MENU_USER, Auth::id() . $lang . '-' . $menuId);
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
        self::menuPathTree($allMenuItem, $menuItems, null);
        CacheHelper::put(MenuItem::KEY_CACHE, $menuItems, $menuIdDb);

        $htmlMenu = self::getMenuUser($menuItems, null, $level);
        CacheBase::putFile(CacheBase::MENU_USER, Auth::id() . $lang . '-' . $menuId, $htmlMenu);
        return $htmlMenu;
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
