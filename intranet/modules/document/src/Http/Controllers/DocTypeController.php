<?php

namespace Rikkei\Document\Http\Controllers;

use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Document\Models\Type;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Validator;

class DocTypeController extends Controller
{
    public function _construct() {
        Menu::setActive('document');
        Breadcrumb::add(trans('doc::view.Document types'), route('doc::admin.type.index'));
    }

    /**
     * list types
     * @return type
     */
    public function index()
    {
        $collectionModel = Type::getGridData();
        return view('doc::type.index', compact('collectionModel'));
    }

    /**
     * edit type
     * @param type $id
     * @return type
     */
    public function edit($id = null)
    {
        $title = trans('doc::view.Create');
        $item = null;
        if ($id) {
            $item = Type::findOrFail($id);
        }
        if ($item) {
            $title = trans('doc::view.Edit');
        }
        Breadcrumb::add($title);

        $listTypes = Type::getList($id ? [$id] : []);
        return view('doc::type.edit', compact('item', 'title', 'listTypes'));
    }

    /**
     * save/update type
     * @param Request $request
     * @return type
     */
    public function save(Request $request)
    {
        $id = $request->get('id');
        $data = $request->except('_token');
        $messages = [
            'name.required' => Lang::get('doc::message.The name field is required'),
        ];
        $valid = Validator::make($data, [
            'name' => 'required'
        ], $messages);
        $isJson = $request->ajax() || $request->wantsJson();
        if ($valid->fails()) {
            if ($isJson) {
                return response()->json($valid->messages()->first('name'), 422);
            }
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $item = Type::insertOrUpdate($data);
        if (!$item) {
            if ($isJson) {
                return response()->json(trans('doc::message.An error occurred'), 500);
            }
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [trans('doc::message.An error occurred')]]);
        }
        if ($isJson) {
            return $item;
        }
        return redirect()
                ->route('doc::admin.type.edit', $item->id)
                ->withInput()
                ->with('messages', ['success' => [trans('doc::message.Update successful')]]);
    }

    /**
     * delete type
     * @param type $id
     * @return type
     */
    public function delete($id)
    {
        $item = Type::findOrFail($id);
        $item->delete();
        return redirect()
                ->back()
                ->with('messages', ['success' => [trans('doc::message.Delete successful')]]);
    }
}
