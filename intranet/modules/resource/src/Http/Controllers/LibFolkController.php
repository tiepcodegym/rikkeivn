<?php

namespace Rikkei\Resource\Http\Controllers;

use Illuminate\Http\Request;
use Lang;
use Rikkei\Core\Http\Controllers\Controller as Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Team\Model\LibsFolk;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class LibFolkController extends Controller 
{

    /**
     * contruct more
     */
    protected function _construct()
    {
        Menu::setActive('languages');
        Breadcrumb::add('languages', route('resource::languages.create'));
    }

    /**
     * list grid folk record
     */
    public function index()
    {
        $collectionModel = LibsFolk::gridViewData();
        Breadcrumb::add(Lang::get('resource::view.Languages.List.List Languages list'));
        return view('resource::libs.folk.list', ['collectionModel' => $collectionModel]);
    }

    /**
     * edit folk record by id
     * @param type $id
     */
    public function edit($id)
    {
        $model = LibsFolk::getItemById($id);
        Breadcrumb::add(Lang::get('resource::view.Languages.Edit.Edit languages edit'));
        if ($model) {
            return view('resource::libs.folk.create', ['model' => $model]);
        } else {
            return redirect()->route('resource::libfolk.list');
        }
    }

    /**
     * create new record list
     * @return view
     */
    public function create()
    {
        $model = new LibsFolk();
        return view('resource::libs.folk.create', ['model' => $model]);
    }

    /**
     * save record folk
     * @param $request
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $tbl = LibsFolk::getTableName();
        $messages = [
            'name.required' => Lang::get('resource::message.Languages name is required field'),
            'name.max' => Lang::get('resource::view.Languages Name greater than', ['number' => 255]),
        ];
        if (isset($data['id']) && $data['id']) {
            $rules['name'] = 'required|max:255|unique:' . $tbl . ',name,' . (int) $data['id'] . ',id';
        } else {
            $rules['name'] = 'required|max:255|unique:' . $tbl . ',name';
        }

        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                            'success' => false,
                            'messages' => $validator->messages(),
                ]);
            }
            if (isset($data['id']) && $data['id']) {
                return redirect()->route('resource::folk.edit', ['id' => $data['id']])
                                ->withErrors($validator)
                                ->withInput();
            } else {
                return redirect()->route('resource::folk.create')
                                ->withErrors($validator)
                                ->withInput();
            }
        }


        if (isset($data['id']) && $data['id']) {
            $folk = LibsFolk::find($data['id']);
        } else {
            $folk = new LibsFolk();
        }
        unset($data['_token']);
        $folk->setData($data);
        
        if (isset($data['id']) && $data['id']) {
            $msg = Lang::get('resource::view.Update languages success');
        } else {
            $msg = Lang::get('resource::view.Create languages success');
        }
        $messages = [
            'success' => [
                $msg,
            ]
        ];
        if ($folk->save()) {
            if ($request->ajax()) {
                $request->session()->flash('message', $msg);
                $request->session()->flash('message-type', 'success');
                return response()->json([
                       'success' => true,
                ]);
            }
            return redirect()->route('resource::folk.list')->with('messages', $messages);
        }
    }

}
