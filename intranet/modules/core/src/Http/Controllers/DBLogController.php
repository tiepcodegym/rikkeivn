<?php

namespace Rikkei\Core\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Model\DBLog;

/**
 * Description of DBLogController
 *
 * @author lamnv
 */
class DBLogController extends Controller
{
    public function _construct()
    {
        Breadcrumb::add('Database Logs');
        Menu::setActive('setting');
    }

    public function index()
    {
        $collectionModel = DBLog::getGridData();
        return view('core::db-logs', compact('collectionModel'));
    }
}
