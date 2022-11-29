<?php

namespace Rikkei\Assets\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;

class AssetSettingController extends Controller
{
    /**
     * List warehouse
     *
     * @return $this
     */
    public function index()
    {
        Menu::setActive('admin');
        Breadcrumb::add(trans('asset::view.Asset config'));

        return view('asset::setting.index');
    }
}
