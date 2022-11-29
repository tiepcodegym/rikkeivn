<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\View;
use Rikkei\ManageTime\Http\Services\WorkPlaceServices;
use URL, Session;
use Rikkei\Team\View\Permission;

class WorkPlaceController extends Controller
{
    public function index()
    {
        Breadcrumb::add(Lang::get('manage_time::view.Workplace management'));

        return view('manage_time::work-place.index', [
            'titleHeadPage' => Lang::get('manage_time::view.Workplace management'),
        ]);

    }

    public function importFile(Request $request, WorkPlaceServices $service)
    {
        $valid = Validator::make($request->all(), [
            'file' => 'required|max:8192'
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid);
        }
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['xlsx', 'xls'])) {
            return redirect()->back()->withErrors(Lang::get('manage_time::view.Only allow file csv, xls, xlsx'));
        }
        if (Permission::getInstance()->isScopeCompany(null, 'manage_time::profile.wpmanagement.import') || Permission::getInstance()->isScopeSelf(null, 'manage_time::profile.wpmanagement.import')) {
            $message = $service->insertEmployeePlace($file);
        } else {
            View::viewErrorPermission();
        }


        return redirect()->back()->with('messages', $message);
    }

    public function exportFile(WorkPlaceServices $service)
    {
        if (Permission::getInstance()->isScopeCompany(null, 'manage_time::profile.wpmanagement.export') || Permission::getInstance()->isScopeSelf(null, 'manage_time::profile.wpmanagement.export')) {
            $data = $service->export();
        } else {
            View::viewErrorPermission();
        }

        return $data;
    }
}
