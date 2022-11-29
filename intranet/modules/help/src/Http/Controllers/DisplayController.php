<?php

namespace Rikkei\Help\Http\Controllers;

use Rikkei\Core\View\Menu;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Help\Model\Help;
use Illuminate\Support\Facades\Lang;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;

class DisplayController extends Controller {

    /**
     * after construct
     */
    public function _construct() {
        Menu::setActive('admin', 'help');
    }

    /**
     * view help   
     */
    public function display($id = null) {
        Breadcrumb::add('View Help', URL::route('help::display.help.view'));
        $noti = false;
        if ($id != null) {
            $noti = Lang::get('help::view.No object found');
        }
        return view('help::frontend.front', [
            'pageType' => Help::TYPE_VIEW,
            'menu' => Help::buildMenuTree(Help::TYPE_VIEW),
            'helpItem' => Help::getHelpItemByIdSlug($id),
            'titleHeadPage' => Lang::get('help::view.View help item'),
            'noti' => $noti,
        ]);
    }
    
    /**
     * return help content by id
     * @param Request $request
     * @return String $help
     */
    public static function getHelpContentbyID(Request $request) {        
        $help = Help::find($request->get('id'));
        $content = $help->content;
        $help->content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
        return $help;
    }   
    
    /**
     * search and return help id by keyword
     * @param Request $request
     * @return array $help->id
     */
    public function searchHelp(Request $request) {
        $keyword = trim($request->all()["id"]);
        $keywordArr = preg_split('/[\s:;,\.\/(")\[\]\{\}\'\?\!]+/', htmlspecialchars_decode($keyword));
 
        for ($i=0; $i<count($keywordArr); $i++){
            $keywordArr[$i] = '%'.$keywordArr[$i].'%';
        }
        
        $nodeId = [];
        if($request->all()['type'] == Help::TYPE_VIEW){
            $results = Help::where(function($query) use ($keywordArr){
                                    foreach($keywordArr as $key){
                                         $query = $query->where('content', 'LIKE', "%$key%")
                                                        ->orWhere('title', 'LIKE', "%$key%");
                                    }
                                    return $query;
                                })->where('active', Help::STATUS_ACTIVE)  
                                  ->orderBy('parent')                
                                  ->get();                
        }
        else{
            $results = Help::where(function($query) use ($keywordArr){
                                    foreach($keywordArr as $key){
                                         $query = $query->where('content', 'LIKE', "%$key%")
                                                        ->orWhere('title', 'LIKE', "%$key%");
                                    }
                                    return $query;
                                })->orderBy('parent')                
                                  ->get();     
        }      
       
        for ($i=0; $i<count($results); $i++){     
            if (stripos(strip_tags($results[$i]->content), $keyword)> -1 || stripos($results[$i]->title, $keyword) > -1){     
                $nodeId[] = ['id' => $results[$i]->id];
            }
        }     
        
        return $nodeId;
    }
}
