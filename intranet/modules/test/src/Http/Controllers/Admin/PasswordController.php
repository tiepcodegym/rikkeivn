<?php

namespace Rikkei\Test\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Validator;

class PasswordController extends Controller
{
    protected $passKey = 'test.config.password';
    
    /**
      * show view
      * @return type
      */
    public function index() {
        $password = CoreConfigData::getValueDb('test.config.password');
        if (!$password) {
            $passItem = new CoreConfigData();
            $passItem->key = $this->passKey;
            $passItem->value = 'admin@123';

            $passItem->save();
            $password = $passItem->value;
            $passItem->save();
        }
        Breadcrumb::add(trans('test::test.test'), route('test::admin.test.index'));
        Breadcrumb::add(trans('test::test.password'), route('test::admin.test.passwords'));
        Menu::setActive('hr');
        return view('test::manage.password.index', compact('password'));
    }
    
    /**
      * update password
      * @param Request $request
      * @return type
      */
    public function update(Request $request) {
        $valid = Validator::make($request->all(), [
            'password' => 'required'
        ], [
            'password.required' => trans('test::validate.please_input_field', ['field' => trans('test::test.password')])
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $password = $request->input('password');
        $passItem = CoreConfigData::getItem($this->passKey);
        if (!$passItem) {
            $passItem = new CoreConfigData();
            $passItem->key = $this->passKey;
        }
        $passItem->value = $password;
        $passItem->save();
        return redirect()->back()->with('messages', ['success' => [trans('test::validate.update_success')]]);
    }
}
