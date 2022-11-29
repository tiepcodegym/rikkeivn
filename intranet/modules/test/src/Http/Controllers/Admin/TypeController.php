<?php

namespace Rikkei\Test\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Test\Models\Type;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Validator;
use Rikkei\Test\Models\TypeMeta;
use Rikkei\Core\View\CoreLang;
use Illuminate\Support\Facades\DB;

class TypeController extends Controller
{
    /**
     * construct
     */
    public function _construct() {
        Breadcrumb::add(trans('test::test.test_type'), route('test::admin.type.index'));
        Menu::setActive('hr');
    }
    
    /**
     * list types
     * @return type
     */
    public function index() {
        $collectionModel = Type::getGridData();
        return view('test::manage.type.index', compact('collectionModel'));
    }
    
    /**
     * create type page
     */
    public function create()
    {
        $allLang = CoreLang::allLang();
        $groupTypes = Type::getGroupType();
        return view('test::manage.type.create', compact('groupTypes', 'allLang'));
    }
    
    /**
     * insert new type
     * @param Request $request
     * @return type
     */
    public function store(Request $request) {
//        $valid = Validator::make($request->all(), [
//            'name' => 'required|max:255|unique:ntest_types,name'
//        ]);
//        if ($valid->fails()) {
//            return redirect()->back()->withErrors($valid->errors())->withInput();
//        }
        $typeData = $request->all();
        if (isset($typeData['parent_id']) && !$typeData['parent_id']) {
            $typeData['parent_id'] = null;
        }
        try {
            Type::saveType($typeData);
            return redirect()->route('test::admin.type.index')
                ->with('messages', ['success' => [trans('test::test.add_new_successful')]]);
        } catch (Exception $ex) {
            \Log::info($ex);
            return redirect()->back()->withInput()->withErrors(['msg', trans('test::validate.na_error')]);
        }
    }
    
    /**
     * show edit type
     * @param type $id
     * @return type
     */
    public function edit($id) {
        $allLang = CoreLang::allLang();
        $item = Type::findOrFail($id);
        $listMeta = TypeMeta::getByTypeId($id);
        $typesMeta = [];
        foreach ($listMeta as $meta) {
            $typesMeta[$meta->lang_code] = $meta;
        }
        $groupTypes = Type::getGroupType($id);

        Breadcrumb::add(trans('test::test.edit'));

        return view('test::manage.type.edit', compact('item', 'groupTypes', 'itemsMeta', 'allLang', 'typesMeta'));
    }
    
    /**
     * update type
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function update($id, Request $request) {
//        $valid = Validator::make($request->all(), [
//            'name' => 'required|max:255|unique:ntest_types,name,' . $id
//        ]);
//        if ($valid->fails()) {
//            return redirect()->back()->withErrors($valid->errors())->withInput();
//        }
        $typeData = $request->all();
        Type::saveType($typeData, $id);
        return redirect()->route('test::admin.type.index')->with('messages', ['success' => [trans('test::test.update_success')]]);
    }
    
    public function tests()
    {
        return $this->hasMany('\Rikkei\Test\Models\Test', 'type_id');
    }
    
    /**
     * delete type
     * @param type $id
     * @return type
     */
    public function destroy($id) {
        $item = Type::findOrFail($id);
        if (!$item->tests->isEmpty()) {
            return redirect()->back()->with('messages', ['errors' => [
                trans('test::test.cant_delete_because_has_test', ['type' => $item->name])
                ]]);
        }
        Type::removeParent($item->id);
        $item->delete();
        return redirect()->back()->with('messages', ['success' => [trans('test::test.delete_successful')]]);
    }
}
