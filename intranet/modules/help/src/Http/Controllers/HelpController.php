<?php

namespace Rikkei\Help\Http\Controllers;

use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Help\Model\Help;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CacheHelper;

class HelpController extends Controller{    
    
    /**
     * after construct
     */
    public function _construct() {
        Menu::setActive('admin', 'help');
    }

    /**
     * create help
     */
    public function create()
    {
        Breadcrumb::add('Create Help');        
        $helps = Help::getAllHelp();     
        return view('help::manage.edit', [
            'pageType' => Help::TYPE_CREATE,    
            'menu' => Help::buildMenuTree(Help::TYPE_CREATE),
            'helpOption' => $helps,
            'helpItem' => new Help(),
            'titleHeadPage' => Lang::get('help::view.Manage help item'),
            'optionStatus' => Help::getAllStatus(),
            'noti' => false,
        ]);
    }
    
    /**
     * edit help
     */
    public function edit($id)
    { 
        Breadcrumb::add('Edit Help');
        $helps = Help::getAllHelp();    
        $help = Help::find($id);
        $noti = false;
        if (!$help) {
            $help = new Help();
            $noti = Lang::get('help::view.No object found');
        }
        return view('help::manage.edit', [
            'pageType' => Help::TYPE_EDIT,    
            'menu' => Help::buildMenuTree(Help::TYPE_EDIT),
            'helpOption' => $helps,    
            'helpItem' => $help,
            'titleHeadPage' => Lang::get('help::view.Manage help item'),
            'optionStatus' => Help::getAllStatus(),
            'noti' => $noti,
        ]);
    }     
       
    /**
     * return help by id
     * @param Request $request
     * @return Help $help
     */
    public function getHelpbyID(Request $request)
    {         
        //content
        $help = Help::find($request->get('id'));
        return $help;        
    }       
    
    /**
     * save help
     */
    public function save(Request $request)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $dataHelp = json_decode($request->get('item'),true);
        $id = isset($dataHelp['id']) ? $dataHelp['id'] : null;
        if ($id) {
            $help = Help::find($id);
            if (!$help) {
                $response['error'] = 1;
                $response['message'] = Lang::get('core::message.Not found item');
                return response()->json($response);
            }
        } else {
            $help = new Help();
        }
        if ($dataHelp['parent'] == '#'){       
            $dataHelp['parent'] = null;
        }
        $allStatus = implode(',',array_keys(Help::getAllStatus()));
        $validator = Validator::make($dataHelp, [
            'title' => 'required|max:255',
            'active' => 'required|in:' . $allStatus,
            'order' => 'numeric|min:0'        
        ]);
        
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['notification'] = Lang::get('help::view.Notification');
            $response['message'] = Lang::get('core::message.Error input data!');
            return response()->json($response);
        }
        
        $help->setData($dataHelp);
        try {
            $help->save();            
            
            CacheHelper::forget(Help::ALL_HELPS);
            
            $response['success'] = 1;
            $response['notification'] = Lang::get('help::view.Notification');
            $response['message'] = Lang::get('core::message.Save success');
            $response['data'] = $help;
            $route = route('help::display.help.view', ['id'=>$help->id]);
            if ($help->active == Help::STATUS_INACTIVE) {
                $route = route('help::display.help.view', ['id'=>null]);
            }
            $response['routerView'] = $route;
            return response()->json($response);           
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['notification'] = Lang::get('help::view.Notification');
            $response['message'] = Lang::get('core::message.Error system, please try later!');
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * delete help
     */
    public function delete(Request $request)
    {   
        $response = [];
        $help = Help::find($request->id);    
    
        if (!$help) {
            $response['error'] = 1;
            $response['notification'] = Lang::get('help::view.Notification');
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }        
        try {
            $help->delete();                
            
            CacheHelper::forget(Help::ALL_HELPS);
            
            $response['success'] = 1;
            $response['notification'] = Lang::get('help::view.Notification');
            $response['message'] = Lang::get('help::view.Delete successfully');
            $response['routerView'] = route('help::display.help.view');
            return response()->json($response);
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['notification'] = Lang::get('help::view.Notification');
            $response['message'] = Lang::get('core::message.Error system, please try later!');
            Log::info($ex);
            return response()->json($response);
        }         
    }
}
