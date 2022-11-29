<?php

namespace Rikkei\Resource\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;
use Rikkei\Resource\Model\TestSchedule;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\View\getOptions;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Team\View\Permission;

class TestScheduleController extends Controller {
    
    /**
     * construct
     */
    public function _construct() {
        Breadcrumb::add(trans('resource::view.Test history'), route('resource::test.history.index'));
        Menu::setActive('resource');
    }
    
    /**
     * list data
     * @return type
     */
    public function index (Request $request) {
        Breadcrumb::add(trans('resource::view.History list'));
        
        $collectionModel = TestSchedule::getGridData($request->all());
        $positionOptions = getOptions::getInstance()->getRoles();
        $statusOptionsAll = getOptions::getInstance()->getCandidateStatusOptionsAll();
        $typeOptions = TestSchedule::getTypeOptions();
        $programList = Programs::all(['id', 'name']);
        $hasPermissDetail = Permission::getInstance()->isAllow('resource::candidate.detail');
        return view('resource::test_history.index', 
                compact('collectionModel', 'positionOptions', 'statusOptionsAll', 'typeOptions', 'programList', 'hasPermissDetail'));
    }
    
}
