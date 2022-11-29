<?php

namespace Rikkei\TestOld\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Rikkei\Core\Http\Controllers\Controller;
use Validator;
use Rikkei\TestOld\Models\Pass;

class PasswordController extends Controller
{
    public function index() {
        $password = Pass::find(config('test.pass_id', 10));
        if ($password) {
            $password = $password->password;
        }
        return view('test_old::manage.password.index', compact('password'));
    }
    
    public function update(Request $request) {
        $valid = Validator::make($request->all(), [
            'password' => 'required'
        ], [
            'password.required' => trans('test_old::validate.please_enter_password')
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }     
        $password = $request->input('password');
        Pass::where('id', config('test.pass_id', 10))->update(['password' => $password]);
        return redirect()->back()->with('mess_succ', trans('test_old::validate.update_success'));
    }
}
