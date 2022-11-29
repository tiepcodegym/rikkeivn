<?php

namespace Rikkei\Team\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\PublishQueueToJob;
use URL;
use Rikkei\Team\Model\Role;
use Rikkei\Core\View\Form;
use Lang;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Rikkei\Team\Model\Permission;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Team\View\Acl;
use Rikkei\Team\View\TeamList;

class RoleController extends \Rikkei\Core\Http\Controllers\Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Setting');
        Breadcrumb::add('Role');
        Menu::setActive(null, null, 'setting');
    }

    /**
     * view/edit role
     *
     * @param int $id
     */
    public function view($id)
    {
        $isAjax = request()->ajax() || request()->wantsJson();
        $model = Role::find($id);
        if (! $model || ! $model->isRole()) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found item.')], 404);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        Breadcrumb::add($model->role, URL::route('team::setting.role.view', ['id' => $id]));
        Form::setData($model, 'role');
        $rolePermissions = Permission::getRolePermission($id);
        if ($isAjax) {
            $returnData = [
                'roleModel' => $model,
                'rolePermissions' => $rolePermissions,
                'rolesPosition' => [
                    0 => [
                        'id' => $id,
                        'role' => $model->role
                    ]
                ],
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
            if (Input::get('teamOptions')) {
                $returnData['teamOptions'] = TeamList::toOption(null, false, false);
            }
            return $returnData;
        }
        return view('team::setting.index', [
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * save role
     */
    public function save()
    {
        $isAjax = request()->ajax() || request()->wantsJson();
        $id = Input::get('role.id');
        $dataItem = Input::get('role');
        if ($id) {
            $model = Role::find($id);
            if (! $model) {
                if ($isAjax) {
                    return response()->json(['message' => Lang::get('team::messages.Not found item.')], 404);
                }
                return redirect()->route('team::setting.team.index')
                    ->withErrors(Lang::get('team::messages.Not found item.'));
            }
        } else {
            $model = new Role();
        }

        $validator = Validator::make($dataItem, [
            'role' => 'required|max:255|unique:roles,role' . ($id ? ',' . $id : ',NULL') . ',id,deleted_at,NULL',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            if ($isAjax) {
                return response()->json(['message' => $validator->messages()], 422);
            }
            Form::setData($dataItem);
            if ($model->id) {
                return redirect()->route('team::setting.role.view', [
                        'id' => $model->id
                    ])->withErrors($validator);
            }
            return redirect()->route('team::setting.team.index')->withErrors($validator);
        }
        $model->setData($dataItem);
        $model->special_flg = Role::FLAG_ROLE;
        try {
            $result = $model->save();
            CacheHelper::forget(Role::KEY_CACHE_ROLE);
            PublishQueueToJob::makeInstance()->cacheRole(0, $id);
            if (! $result) {
                if ($isAjax) {
                    return response()->json(['message' => Lang::get('team::messages.Error save data, please try again!')], 500);
                }
                return redirect()->route('team::setting.team.index')
                    ->withErrors(Lang::get('team::messages.Error save data, please try again!'));
            }
            $messages = [
                    'success'=> [
                        Lang::get('team::messages.Save data success!'),
                    ]
            ];
            if ($isAjax) {
                return response()->json([
                    'id' => $model->id,
                    'message' => Lang::get('team::messages.Save data success!')
                ]);
            }
            return redirect()->route('team::setting.role.view', ['id' => $model->id])->with('messages', $messages);
        } catch (\Exception $ex) {
            if ($isAjax) {
                return response()->json(['message' => $ex->getMessage()], 500);
            }
            return redirect()->route('team::setting.team.index')->withErrors($ex);
        }
    }

    /**
     * delete role
     */
    public function delete()
    {
        $isAjax = request()->ajax() || request()->wantsJson();
        $id = Input::get('id');
        $model = Role::find($id);
        if (! $model) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found item.')], 404);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        try {
            $model->delete();
            $messages = [
                    'success'=> [
                        Lang::get('team::messages.Delete item success!'),
                    ]
            ];
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Delete item success!')]);
            }
            return redirect()->route('team::setting.team.index')->with('messages', $messages);
        } catch (\Exception $ex) {
            if ($isAjax) {
                return response()->json(['message' => $ex->getMessage()], 500);
            }
            return redirect()->route('team::setting.team.index')->withErrors($ex);
        }
    }
}

