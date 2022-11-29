<?php

namespace Rikkei\Team\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;

class SettingController extends \Rikkei\Core\Http\Controllers\Controller
{
    /**
     * setting index
     * 
     * @return view
     */
    public function index()
    {
        Breadcrumb::add('Setting');
        Breadcrumb::add('Team');
        Menu::setActive(null, null, 'setting');
        return view('team::setting.index');
    }
}
