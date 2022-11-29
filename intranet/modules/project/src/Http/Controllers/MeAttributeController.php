<?php

namespace Rikkei\Project\Http\Controllers;

/**
 * Description of MeEvalController
 *
 * @author lamnv
 */

use Illuminate\Support\Facades\Session;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Project\Model\MeAttributeLang;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Validator;


class MeAttributeController extends Controller {

    public function _construct() {
        Breadcrumb::add(trans('project::me.Evaluation attributes'), route('project::eval.attr.index'));
        Menu::setActive('project');
    }
    
    /**
     * render create view
     * @return type
     */
    public function index() {
        $collectionModel = MeAttribute::getGridData(); 
        return view('project::me.attributes.index', compact('collectionModel'));
    }
    
    public function create() {
        Breadcrumb::add(trans('project::me.Create'));
        return view('project::me.attributes.create');
    }
    
    public function store(Request $request) {
        $valid = Validator::make($request->all(), [
            'label' => 'required',
            'name' => 'required',
            'weight' => 'required'
        ]);
        
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid->errors());
        }
        $data = $request->all();
        if (isset($data['can_fill']) && $data['can_fill'] == 'on') {
            $data['can_fill'] = 1;
        } else {
            $data['can_fill'] = 0;
        }
        $item = MeAttribute::create($data);
        return redirect()->route('project::eval.attr.edit', $item->id);
    }

    public function edit($id)
    {
        $lang = Session::get('locale');
        $item = MeAttribute::where('me_attributes.id', '=', $id)
            ->join('me_attribute_lang', 'me_attributes.id', '=', 'me_attribute_lang.attr_id')
            ->where('lang_code', '=', $lang)
            ->select('me_attributes.id', 'weight', 'order', 'range_min', 'range_max', 'range_step', 'group', 'can_fill',
                'default', 'has_na', 'type', 'lang_code', 'attr_id', 'name', 'label', 'description')
            ->first();

        if (!$item) {
            abort(404);
        }
        Breadcrumb::add(trans('project::me.Edit'));

        return view('project::me.attributes.edit', compact('item', 'lang'));
    }

    public function update($id, Request $request) {
        $valid = Validator::make($request->all(), [
            'lang.*.label' => 'required',
            'lang.*.name' => 'required',
            'weight' => 'required'
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid->errors());
        }
        
        $item = MeAttribute::find($id);
        if (!$item) {
            abort(404);
        }
        $fillable = $item->getFillable();
        $data = array_only($request->all(), $fillable);
        if (isset($data['can_fill']) && $data['can_fill'] == 'on') {
            $data['can_fill'] = 1;
        } else {
            $data['can_fill'] = 0;
        }
        $item->update($data);
        $dataLangs = $request->get('lang');
        if (!empty($dataLangs)) {
            foreach ($dataLangs as $langCode => $dataLang) {
                $meLang = MeAttributeLang::where('attr_id', $item->id)->where('lang_code', $langCode)->first();
                if ($meLang) {
                    $meLang->update($dataLang);
                }
            }
        }

        return redirect()->back()->with('messages', ['success' => [trans('project::me.Updated successful')]]);
    }

    public function destroy($id) {
        $item = MeAttribute::find($id);
        if (!$item) {
            abort(404);
        }
        $item->delete();
        
        return redirect()->back()->with('messages', ['success' => [trans('project::me.Remove successful')]]);
    }
    
}
