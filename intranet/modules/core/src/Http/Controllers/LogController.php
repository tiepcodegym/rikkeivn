<?php

namespace Rikkei\Core\Http\Controllers;

use File;
use Rikkei\Team\View\Permission;

class LogController extends Controller
{
    /**
     * Display all file in folder storage/logs
     *
     * @return Response view
     */
    public function index()
    {
        if (!$this->hasPermission()) {
            return view('core::errors.permission_denied');
        }
        return view('core::logs.list', [
            'files' =>  File::allFiles(storage_path('logs'))
        ]);
    }

    /**
     * Download file log
     *
     * @param type $filename
     * @return type
     */
    public function download($filename)
    {
        if (!$this->hasPermission()) {
            return view('core::errors.permission_denied');
        }
        $path = storage_path('logs') . '/' . $filename;
        return response()->download($path, $filename);
    }

    /**
     * Check has permission download log
     *
     * @return boolean
     */
    public function hasPermission()
    {
        $curUser = Permission::getInstance()->getEmployee();
        $emailHasPermission = [
            'giangnt2@rikkeisoft.com',
            'hungnt2@rikkeisoft.com',
        ];
        return in_array($curUser->email, $emailHasPermission) || Permission::getInstance()->isRoot();
    }
}
