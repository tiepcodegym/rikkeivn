<?php

namespace Rikkei\Team\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Rikkei\Team\Model\Team;
use Lang;
use Rikkei\Team\View\TeamList;
use Validator;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Breadcrumb;
use URL;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Permission;
use Rikkei\Core\View\Menu;
use Rikkei\Team\View\SendMailCron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Rikkei\Core\View\PublishQueueToJob;
use Rikkei\Team\View\Acl;

class TeamController extends \Rikkei\Core\Http\Controllers\Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Setting');
        Breadcrumb::add('Team', URL::route('team::setting.team.index'));
        Menu::setActive(null, null, 'setting');
    }

    /**
     * view team
     * 
     * @param int $id
     */
    public function view($id)
    {
        $isAjax = request()->ajax() || request()->wantsJson();
        $model = Team::find($id);
        if (! $model) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found item.')]);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        Form::setData($model);
        $positions = $teamPermissions = $permissionAs = null;
        if ($model->is_function) {
            $positions = Role::getAllPosition('desc');
            $permissionAs = $model->getTeamPermissionAs();
            if (! $permissionAs) {
                $teamPermissions = Permission::getTeamPermission($id);
            }
        }

        if ($isAjax) {
            $returnData = [
                'team' => $model,
                'rolesPosition' => $positions,
                'teamPermissions' => $teamPermissions,
                'permissionAs' => $permissionAs,
            ];
            if (Input::get('acl')) {
                $returnData['acl'] = Acl::getAclList();
            }
            if (Input::get('guides')) {
                $returnData['guides'] = Acl::getGuideAcl();
            }
            if (Input::get('scopeIcons')) {
                $returnData['scopeIcons'] = Permission::scopeIconArray();
            }
            if (Input::get('scopeOptions')) {
                $returnData['scopeOptions'] = Permission::toOption();
            }
            if (Input::get('transAcl')) {
                $returnData['transAcl'] = trans('acl');
            }
            return $returnData;
        }

        return view('team::setting.index', [
            'rolesPosition' => $positions,
            'teamPermissions' => $teamPermissions,
            'permissionAs' => $permissionAs
        ]);
    }

    public function edit($id)
    {
        $model = Team::find($id);
        if (!$model) {
            return response()->json(['message' => Lang::get('team::messages.Not found item.')]);
        }
        return [
            'team' => $model
        ];
    }
    
    /**
     * save team
     */
    public function save()
    {
        $isAjax = request()->ajax() || request()->wantsJson();
        if ($id = Input::get('item.id')) {
            $model = Team::find($id);
        } else {
            $model = new Team();
        }
        $dataItem = Input::get('item');
        if ($dataItem['code']) {
            $dataItem['code'] = strip_tags($dataItem['code']);
        }
        if (! Input::get('item.is_function')) {
            $dataItem['is_function'] = 0;
            $dataItem['follow_team_id'] = 0;
        } elseif (! Input::get('permission_same')) {
            $dataItem['follow_team_id'] = 0;
        }
        if (! Input::get('item.is_soft_dev')) {
            $dataItem['is_soft_dev'] = null;
        }

        if ($model->id) {
            if ($model->code !== $dataItem['code']) {
                if (Team::where('code', $dataItem['code'])->first()) {
                    Form::setData($dataItem);
                    Form::setData();
                    return redirect()->route('team::setting.team.view', [
                        'id' => $model->id
                    ])->withErrors(Lang::get('team::view.unique code'))->withInput();
                }
            }
        } else {
            if (Team::where('code', $dataItem['code'])->first()) {
                Form::setData($dataItem);
                Form::setData();
                return redirect()->back()->withErrors(Lang::get('team::view.unique code'))->withInput();
            }
        }
        if (! Input::get('item.is_branch')) {
            $dataItem['is_branch'] = 0;
        }
        $validator = Validator::make($dataItem, [
            'name' => 'required|max:255|unique:teams,name,' . ($id ? $id : 'NULL') . ',id,deleted_at,NULL',
            'code' => 'required|max:255|unique:teams,code,' . ($id ? $id : 'NULL') . ',id,deleted_at,NULL',
        ]);
        $validator->setAttributeNames([
            'name' => trans('team::view.Team name'),
            'code' => trans('team::view.Team code'),
        ]);
        if ($validator->fails()) {
            if ($isAjax) {
                return response()->json(['message' => $validator->messages()], 422);
            }
            Form::setData($dataItem);
            Form::setData();
            if ($model->id) {
                return redirect()->route('team::setting.team.view', [
                            'id' => $model->id
                        ])->withErrors($validator);
            }
             return redirect()->route('team::setting.team.index')	
                ->withErrors($validator);
        }
        //calculate position
        if (! $model->id) { //team new
            $parentId = 0;
            $teamSameParent = Team::select('id', 'sort_order')
                    ->where('parent_id', $parentId)
                    ->orderBy('sort_order', 'desc')
                    ->first();
            if (count($teamSameParent)) {
                $dataItem['sort_order'] = $teamSameParent->sort_order + 1;
            } else {
                $dataItem['sort_order'] = 0;
            }
        }

        try {
            $model->setData($dataItem);
            $result = $model->save();
            if (!$result) {
                if ($isAjax) {
                    return response()->json(['message' => Lang::get('team::messages.Error save data, please try again!')], 500);
                }
                return redirect()->route('team::setting.team.index')
                    ->withErrors(Lang::get('team::messages.Error save data, please try again!'));
            }
            //reset team list
            with(new TeamList())->resetCacheTeamList();
            PublishQueueToJob::makeInstance()->cacheRole(0, 0, $model->id);
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Save data success!')]);
            }
            return redirect()->route('team::setting.team.view', [
                    'id' => $model->id
                ])->with('messages', [
                    'success' => [
                        Lang::get('team::messages.Save data success!')
                    ]
                ]);
        } catch (\Exception $ex) {
            if ($isAjax) {
                return response()->json(['message' => $ex->getMessage()], 500);
            }
            return redirect()->route('team::setting.team.index')->withErrors($ex);
        }
    }
    
    /**
     * move team
     */
    public function move()
    {
        $id = Input::get('id');
        $isAjax = request()->ajax() || request()->wantsJson();
        if (!$id) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found item.')], 404);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $model = Team::find($id);
        if (!$model) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found item.')], 404);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        try {
            if (Input::get('move_up')) {
                $model->move(true);
            } else {
                $model->move(false);
            }

            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Move item success!')]);
            }
            return redirect()->route('team::setting.team.view', [
                    'id' => $id
                ])->with('messages', [
                    'success' => [
                        Lang::get('team::messages.Move item success!')
                    ]
                ]);
        } catch (\Exception $ex) {
            if ($isAjax) {
                return response()->json(['message' => $ex->getMessage()], 500);
            } else {
                return redirect()->route('team::setting.team.view', [
                        'id' => $id
                    ])->withErrors($ex);
            }
        }
    }
    
    /**
     * Delete team
     */
    public function delete()
    {
        $id = Input::get('id');
        $isAjax = request()->ajax() || request()->wantsJson();
        if (!$id) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found item.')], 404);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $model = Team::find($id);
        if (!$model) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found item.')], 404);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        try {
            $model->delete();
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Delete item success!')]);
            }
            return redirect()->route('team::setting.team.index')
                ->with('messages', [
                    'success' => [
                        Lang::get('team::messages.Delete item success!')
                    ]
                ]);
        } catch (\Exception $ex) {
            if ($isAjax) {
                return response()->json(['message' => $ex->getMessage()], 500);
            } else {
                return redirect()->route('team::setting.team.view', [
                        'id' => $id
                    ])->withErrors($ex);
            }
        }
    }
    
    /**
     * search team by ajax
     */
    public function listSearchAjax($type = null)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            Team::searchAjax(Input::get('q'), [
                'page' => Input::get('page'),
                'typeExclude' => $type,
            ])
        );
    }

    /**
     * search team origin by ajax
     */
    public function listSearchAjaxOrigin($type = null)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            Team::searchAjaxOrigin(Input::get('q'), [
                'page' => Input::get('page'),
                'typeExclude' => $type,
            ])
        );
    }

    public function initTeamSetting()
    {
        return [
            'teams' => \Rikkei\Team\View\TeamList::getList(),
            'roleAll' => Role::getAllRole(),
            'positionAll' => Role::getAllPosition(),
        ];
    }
}
