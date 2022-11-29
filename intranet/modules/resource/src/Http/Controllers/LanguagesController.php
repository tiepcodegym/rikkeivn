<?php

namespace Rikkei\Resource\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller as Controller;
use Rikkei\Core\View\Menu;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Resource\Model\Languages;
use Lang;
use Illuminate\Support\Facades\Validator;
use Rikkei\Resource\Model\LanguageLevel;


/**
* 
*/
class LanguagesController  extends Controller
{

    /**
     * construct more
     */
    protected function _construct() {
        Menu::setActive('languages');
        Breadcrumb::add('languages' , route('resource::languages.create'));
    }

    /**
      *get list languages
    */
    public function index() {
        $collectionModel = Languages::getGridData();
        Breadcrumb::add(Lang::get('resource::view.Languages.List.List Languages list'));
        return view('resource::language.list',['collectionModel' => $collectionModel]);
    }

    /**
     *Create languages
    */
    public function create() {
        Breadcrumb::add(Lang::get('resource::view.Languages.Create.Create Languages'));
        $languageId = new Languages();
        return view('resource::language.create', ['languageId' => $languageId]);
    }

    /**
      * edit program languages
      *@param id languages edit
      *@return view create
    */
    public function edit($id) { 
        $languageId = Languages::getById($id);
        Breadcrumb::add(Lang::get('resource::view.Languages.Edit.Edit languages edit'));
        if($languageId) {
            return view('resource::language.create',['languageId' => $languageId]);
        } else {
            return redirect()->route('resource::languages.list');	
        }
    }

    /**
      * save language
      *@param $request
    */
    public function store(Request $request) {

        $data = $request->all();
        $tableProgramming = Languages::getTableName();
        $messages = [
            'name.required' => Lang::get('resource::message.Languages name is required field'),
            'name.max' => Lang::get('resource::view.Languages Name greater than', ['number'=> 45]),
            'name.unique' => Lang::get('resource::message.Languages name is unique field'),
            'english_name.max' => Lang::get('resource::view.Languages english name greater than', ['number'=> 45]),
            'english_name.unique' => Lang::get('resource::message.Languages english name is unique field')
        ];
        if (isset($data['id'])) {
            $rules['name'] = 'required|max:45|unique:'.$tableProgramming. ',name,'.(int)$data['id'].',id';
             $rules['english_name'] = 'max:45|unique:'.$tableProgramming. ',english_name,'.(int)$data['id'].',id';
        } else {
            $rules['name'] = 'required|max:45|unique:'.$tableProgramming. ',name';
            $rules['english_name'] = 'max:45|unique:'.$tableProgramming. ',english_name';
        }
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            if(isset($data['id']) && $data['id']) {
                return redirect()->route('resource::languages.edit',['languageId' => $data['id']])
                            ->withErrors($validator)
                            ->withInput();
            } else {
                return redirect()->route('resource::languages.create')
                            ->withErrors($validator)
                            ->withInput();
            }
        }
         if (isset($data['id']) && $data['id']) {
            $languages = Languages::find($data['id']);
        } else {
            $languages = new Languages();
        }
       	
        $languages->fill($data);

        if(isset($data['id']) && $data['id']) {
            $msg = Lang::get('resource::view.Update languages success');
        } else {
            $msg = Lang::get('resource::view.Create languages success');
        }
        $messages = [
                'success'=> [
                    $msg,
                ]
        ];
        if($languages->save()) {
            if (isset($data['language_level'])) {
                $levels = explode(',', $data['language_level']);
                if (is_array($levels)) {
                    $levels = array_map('trim', $levels);
                    LanguageLevel::insertLevels(array_filter($levels), $languages->id);
                }
            }
            return redirect()->route('resource::languages.edit',['languageId' => $languages->id])->with('messages',$messages);
        }
    }
}
