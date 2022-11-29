<?php

namespace Rikkei\TestOld\Http\Controllers;

use Illuminate\Http\Request;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\TestOld\Models\Test;
use Rikkei\Team\View\TeamList;
use Rikkei\TestOld\Models\Pass;
use Validator;
use Session;

class TestController extends Controller
{
    protected $test;

    public function __construct(Test $test) {
        $this->test = $test;
    }
    
    public function passed() {
        return (!Session::has('test_auth') && !Session::get('test_auth'));
    }
    
    public function index() {
        if (Session::has('test_auth') && Session::get('test_auth')) {
            Session::forget('test_auth');
        }

        return view('test_old::test.index');
    }
    
    public function checkAuth(Request $request) {
        $valid = Validator::make($request->all(), [
            'password' => 'required'
        ], [
            'password.required' => trans('test_old::validate.please_enter_password')
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid->errors());
        }
        $password = $request->input('password');

        $t_pass = Pass::find(config('test.pass_id', 10));
        if ($t_pass) {
            $t_pass = $t_pass->password;
        }
        if ($password != $t_pass) {
            return redirect()->back()->with('mess_error', trans('test_old::test.auth_failure'));
        }
        Session::set('test_auth', bcrypt($password));
        return redirect()->route('test_old::select_test');
    }
    
    public function selectTest() {
        if ($this->passed()) {
            return redirect()->route('test_old::index');
        }
        
        $cats = TeamList::toOption(null, true, false);
        $gmats = $this->test->where('type', 2)->orderBy('created_at', 'asc')->get();
        
        Session::forget('test_auth');
        return view('test_old::test.tests_select', compact('cats', 'gmats'));
    }
    
    public function getTests(Request $request) {
        return $this->test->getAll($request->all());
    }
    
    public function show($id, Request $request) {        
        $test = $this->test->find($id);
        $test_time = ($test ? $test->time : 0);
        if ($request->has('q_id') && ($q_id = $request->get('q_id'))) {
            $with_test = $this->test->find($q_id);
            $test_time = $with_test ? $with_test->time + $test_time : $test_time;
        }
        return view('test_old::test.show', compact('test', 'test_time'));
    }
    
    public function finish() {
        return view('test_old::test.finish');
    }
    
}
