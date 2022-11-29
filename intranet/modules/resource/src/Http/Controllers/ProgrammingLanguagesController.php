<?php
namespace Rikkei\Resource\Http\Controllers;
use Rikkei\Resource\Model\Programs;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Illuminate\Http\Request;
use Rikkei\Core\Http\Controllers\Controller as Controller;
use Lang;
use Illuminate\Support\Facades\Validator;
use Session;
use Illuminate\Support\Facades\Input;
use Rikkei\Core\View\CacheHelper;

class ProgrammingLanguagesController extends Controller
{
    /**
     * construct more
     */
    protected function _construct() {
        Menu::setActive('programminglanguages');
        Breadcrumb::add('programminglanguages' , route('resource::programminglanguages.list'));
    }
    
    /**
      *get list programming languages
    */
    public function index() {
        $collectionModel = Programs::getGridData();
        Breadcrumb::add(Lang::get('resource::view.Programminglanguages.List.List Programminglanguages list'));
        return view('resource::programminglanguages.list',['collectionModel' => $collectionModel]);
    }
    /**
      * create programming languages view
      *@return view 
    */
    public function create() {
        Breadcrumb::add(Lang::get('resource::view.Programminglanguages.Create.Create Programminglanguages'));
        if(empty(Input::old())) {
            $programId = new Programs();
        } else {
            $programId = Input::old();
        }
        return view('resource::programminglanguages.create',['programId' => $programId]);
    }
    /**
      * save programminglanguage
      *@param $request
    */
    public function store(Request $request) {
        $data = $request->all();
        $tableProgramming = Programs::getTableName();
        $messages = [
            'name.required' => Lang::get('resource::message.Programming name is required field'),
            'name.max' => Lang::get('sales::view.Channel.Create.Name greater than', ['number'=> 255]),
        ];
        if (isset($data['id'])) {
            $rules['name'] = 'required|max:255|:'.$tableProgramming. ',name,'.(int)$data['id'].',id';
        } else {
            $rules['name'] = 'required|max:255|:'.$tableProgramming. ',name';
        }
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            if(isset($data['id']) && $data['id']) {
                return redirect()->route('resource::programminglanguages.edit',['programId' => $data['id']])
                            ->withErrors($validator)
                            ->withInput();
            } else {
               return redirect()->route('resource::programminglanguages.create')
                            ->withErrors($validator)
                            ->withInput();
            }
        }

        if (isset($data['id']) && $data['id']) {
            $program = Programs::find($data['id']);
            if(empty($program)) {
                return redirect()
                    ->route('resource::programminglanguages.create')
                    ->withErrors(Lang::get('resource::view.not found item'));;
            } else {
                if(Programs::checkExist($data['name'], $data['id']) > 0) {
                    return redirect()
                        ->route('resource::programminglanguages.edit',['programId' => $program->id])
                        ->withErrors(Lang::get('resource::message.Programming name is unique field'))
                        ->withInput();
                }
            }
        } else {
            $program = new Programs();
            if(Programs::checkExist($data['name']) > 0) {
                return redirect()
                    ->route('resource::programminglanguages.create')
                    ->withErrors(Lang::get('resource::message.Programming name is unique field'))
                    ->withInput();
            }
        }
        $program->name = trim($data['name']);
        $program->primary_chart = $data['primary_chart'];
        if(isset($data['id']) && $data['id']) {
            $msg = Lang::get('resource::view.Programminglanguages.Edit.Programminglanguages Update Programminglanguages success');
        } else {
            $msg = Lang::get('resource::view.Programminglanguages.Edit.Programminglanguages create Programminglanguages success');
        }
        $messages = [
            'success'=> [
                $msg,
            ]
        ];
        if($program->save()) {
            return redirect()->route('resource::programminglanguages.edit',['programId' => $program->id])->with('messages',$messages);
        }
    }
    /**
      * edit program languages
      *@param id program languages edit
      *@return view create
    */
    public function edit($id) {
        $programId = Programs::find($id);
        Breadcrumb::add(Lang::get('resource::view.Programminglanguages.Edit.Edit Programminglanguages edit'));
        if($programId) {
            return view('resource::programminglanguages.create',['programId' => $programId]);
        } else {
            return redirect()->route('resource::programminglanguages.list');	
        }
    }

    /**
     * delete program language
    */
    public function delete($id) {

        $data[] = $id;
        $program = Programs::find($id);
        $messages = [
            'success'=> [
                Lang::get('resource::view.delete success'),
            ]
        ];
        if(!empty($program)) {
            if (Programs::checkPro($data)) {
                $program->delete();
                CacheHelper::forget(Programs::KEY_CACHE);
                return redirect()->route('resource::programminglanguages.list')->with('messages',$messages);
            } else {
                return redirect()->route('resource::programminglanguages.list')->withErrors(Lang::get('resource::view.Do not delete item'));
            }
        }
        return redirect()->route('resource::programminglanguages.list')
                ->withErrors(Lang::get('resource::view.not found item'));
    }

    /**
     * delete mutil program language
    */
    public function ajaxDelete(Request $request) {
        $data = $request->data;
        $check = true;
        if(empty($data)) {
            Session::flash('messages', ['errors' => [trans('resource::view.not found item')]]);
                    return response()->json(true);    
        }
            if (Programs::checkPro($data)) {
                Programs::destroy($data);
                CacheHelper::forget(Programs::KEY_CACHE);
            } else {
                $check = false;
            }
        if ($check == false) {
            Session::flash('messages', ['errors' => [trans('resource::view.Do not delete item')]]);
           return response()->json(true); 
        } else {
            Session::flash('messages', ['success' => [trans('resource::view.delete success')]]);
            return response()->json(true);
        }
    }
}
