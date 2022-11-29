<?php

namespace Rikkei\TestOld\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\TestOld\Models\Test;
use Rikkei\Team\View\TeamList;
use Validator;
use Session;

class TestController extends Controller
{
    protected $test;

    public function __construct(Test $test) {
        $this->test = $test;
    }
    
    public function validator($data) {
        $valid = Validator::make($data, [
            'name' => 'required',
            'link' => 'required',
            'time' => 'required'
        ], [
            'name.required' => trans('test_old::validate.please_enter_name'),
            'link.required' => trans('test_old::validate.please_enter_link'),
            'time.required' => trans('test_old::validate.please_enter_time')
        ]);
        return $valid;
    }
    
    public function index() {
        return view('test_old::manage.test.index', [
            'collectionModel' => Test::getGridData()
        ]);
    }
    
    public function create() {
        $cats = TeamList::toOption(null, true, false);
        return view('test_old::manage.test.create', compact('cats'));
    }
    
    public function store(Request $request) {
        $valid = $this->validator($request->all());
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $name = $request->input('name');
        $cat_id = $request->input('cat_id');
        $type = $request->input('type');
        if (!$cat_id || $cat_id == 0 || $type == 2) {
            $cat_id = null;
        }
        $this->test->create([
            'name' => $name,
            'slug' => str_slug($name),
            'type' => $type,
            'link' => $request->input('link'),
            'time' => $request->input('time'),
            'cat_id' => $cat_id
        ]);
        return redirect()->route('test_old::admin.test.index');
    }
    
    public function edit($id) {
        $item = $this->test->find($id);
        $cats = TeamList::toOption(null, true, false);
        return view('test_old::manage.test.edit', compact('item', 'cats'));
    }
    
    public function update($id, Request $request) {
        $valid = $this->validator($request->all());
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $item = $this->test->find($id);
        $cat_id = $request->input('cat_id');
        $type = $request->input('type');
        if (!$cat_id || $cat_id == 0 || $type == 2) {
            $cat_id = null;
        }
        if ($item) {
            $name = $request->input('name');
            $item->name = $name;
            $item->slug = str_slug($name);
            $item->link = $request->input('link');
            $item->type = $type;
            $item->time = $request->input('time');
            $item->cat_id = $cat_id;
            $item->save();
        }
        return redirect()->back()->with('mess_succ', trans('test_old::validate.update_success'));
    }
    
    public function mAction(Request $request) {
        if (!$request->has('action')) {
            Session::flash('mess_error', trans('test_old::validate.na_error'));
            return response()->json(false, 422);
        }
        if (!$request->has('item_ids')) {
            Session::flash('mess_error', trans('test_old::validate.no_item_selected'));
            return response()->json(false, 422);
        }
        $item_ids = $request->input('item_ids');
        $action = $request->input('action');
        if ($action == 'delete') {
            $this->test->destroy($item_ids);
        }
        Session::flash('mess_succ', trans('test_old::validate.action_success'));
        return response()->json(true);
    }
    
}
