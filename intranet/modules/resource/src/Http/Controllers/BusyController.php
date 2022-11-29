<?php

namespace Rikkei\Resource\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Resource\View\BusyHelper;
use Rikkei\Tag\Model\Tag;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class BusyController extends Controller
{
    public function index()
    {
        if (!Permission::getInstance()->isAllow('team::member.profile.index')) {
            return View::viewErrorPermission();
        }
        if (app('request')->ajax()) {
            return $this->filterAjax();
        }
        return view('resource::busy.index', [
            'tagCollection' => Tag::getTagDataSkills(),
        ]);
    }

    /**
     * filter employee busy result
     */
    protected function filterAjax()
    {
        if (!Permission::getInstance()->isAllow('team::member.profile.index')) {
            return response()->json(['status' => 0], 403);
        }
        $response = [];
        $filters = (array) Input::get();
        $validator = Validator::make($filters, [
            'start' => 'required|date|date_format:Y-m-d',
            'end' => 'required|date|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return response()->json($response);
        }
        $busyHelper = new BusyHelper($filters);
        $response['status'] = 1;
        $response['data'] = $busyHelper->exec();
        $response['count'] = $busyHelper->getCount();
        return response()->json($response);
    }
}
