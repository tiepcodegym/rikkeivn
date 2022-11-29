<?php

namespace Rikkei\Resource\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Resource\Model\TeamFeature;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Team\View\TeamList;
use Validator;

class TeamFeatureController extends Controller {
    
    /**
     * construct
     */
    public function _construct() {
        Breadcrumb::add(trans('resource::view.Recruitment'), route('resource::recruit.index'));
        Breadcrumb::add(trans('resource::view.Team'), route('resource::plan.team.index'));
        Menu::setActive('resource');
    }
    
    /**
     * validate rules
     * @param type $request
     * @param type $teamId
     * @return type
     */
    public function validateRule($request, $teamId = null) {
        $valid = Validator::make($request->all(), [
            'name' => 'required|max:255|unique:teams_feature,name' . ($teamId ? ','.$teamId : ''),
            'team_alias' => 'unique:teams_feature,team_alias' . ($teamId ? ','.$teamId : ''),
            'sort_order' => 'numeric|min:0'
        ], [
            'name.required' => trans('resource::message.The field is required', ['field' => trans('resource::view.Team name')]),
            'name.max' => trans('resource::message.The field may not be greater characters', ['field' => trans('resource::view.Team name'), 'max' => 255]),
            'name.unique' => trans('resource::message.The field has already been taken', ['field' => trans('resource::view.Team name')]),
            'team_alias' => trans('resource::message.The field has already been taken', ['field' => trans('resource::view.Team alias')]),
            'sort_order.numeric' => trans('resource::message.The field must be a number', ['field' => trans('resource::view.Sort order')]),
            'sort_order.min' => trans('resource::message.The field must be at least number', ['field' => trans('resource::view.Sort order'), 'min' => 0])
        ]);
        return $valid;
    }
    
    /**
     * list data
     * @return type
     */
    public function index() {
        $collectionModel = TeamFeature::getGridData();
        $teamList = TeamList::toOption(null, false, false);
        return view('resource::team.index', compact('collectionModel', 'teamList'));
    }
    
    /**
     * view create
     * @return type
     */
    public function create() {
        Breadcrumb::add(trans('resource::view.Add new'));
        $skipIds = TeamFeature::getTeamAliasIds();
        $teamList = TeamList::toOption($skipIds, false, false);
        $suggestOrder = TeamFeature::max('sort_order') + 1;
        return view('resource::team.create', compact('teamList', 'suggestOrder'));
    }
    
    /**
     * save new data
     * @param Request $request
     * @return type
     */
    public function store(Request $request) {
        $valid = $this->validateRule($request);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $data = $request->all();
        if (!isset($data['team_alias']) || !$data['team_alias']) {
            $data['team_alias'] = null;
        }
        $data['is_soft_dev'] = isset($data['is_soft_dev']) ? 1 : null;
        TeamFeature::create($data);
        return redirect()->route('resource::plan.team.index')->with('messages', ['success' => [trans('resource::message.Add new successful')]]);
    }
    
    /**
     * edit item
     * @param type $id
     * @return type
     */
    public function edit($id) {
        $item = TeamFeature::find($id);
        if (!$item) {
            abort(404);
        }
        Breadcrumb::add(trans('resource::view.Edit'));
        $skipIds = TeamFeature::getTeamAliasIds($item->team_alias);
        $teamList = TeamList::toOption($skipIds, false, false);
        return view('resource::team.edit', compact('item', 'teamList'));
    }
    
    /**
     * update item
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function update($id, Request $request) {
        $valid = $this->validateRule($request, $id);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $item = TeamFeature::find($id);
        if (!$item) {
            abort(404);
        }
        $fillable = $item->getFillable();
        $data = array_only($request->all(), $fillable);
        $data['is_soft_dev'] = isset($data['is_soft_dev']) ? 1 : null;
        if (!isset($data['team_alias']) || !$data['team_alias']) {
            $data['team_alias'] = null;
        }
        $item->update($data);
        $item->save();
        return redirect()->route('resource::plan.team.index')->with('messages', ['success' => [trans('resource::message.Save successful')]]);
    }
    
    /**
     * remove item
     * @param type $id
     * @return type
     */
    public function destroy($id) {
        $item = TeamFeature::find($id);
        if (!$item) {
            abort(404);
        }
        $item->delete();
        return redirect()->back()->with('messages', ['success' => [trans('resource::message.Delete successful')]]);
    }
}
