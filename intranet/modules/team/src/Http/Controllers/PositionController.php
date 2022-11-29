<?php

namespace Rikkei\Team\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Lang;
use Validator;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Breadcrumb;
use URL;
use Rikkei\Team\Model\Role;
use Rikkei\Core\View\Menu;

class PositionController extends \Rikkei\Core\Http\Controllers\Controller
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
     * view team position
     * 
     * @param int $id
     */
    public function view($id)
    {
        $model = Role::find($id);
        if (! $model || $model->special_flg != Role::FLAG_POSITION) {
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        Form::setData($model, 'position');
        return view('team::setting.index');
    }
    
    /**
     * save team positon
     */
    public function save()
    {
        $isAjax = request()->ajax() || request()->wantsJson();
        $id = Input::get('position.id');
        $dataItem = Input::get('position');
        if ($id) {
            $model = Role::find($id);
            if (! $model || $model->special_flg != Role::FLAG_POSITION) {
                if ($isAjax) {
                    return response()->json(['message' => Lang::get('team::messages.Please choose team to do this action')], 422);
                }
                return redirect()->route('team::setting.team.index')
                    ->withErrors(Lang::get('team::messages.Please choose team to do this action'));
            }
        } else {
            $model = new Role();
        }
        
        $validator = Validator::make($dataItem, [
            'role' => 'required|max:255|unique:roles,role,' . ($id ? $id : 'NULL') . ',id,deleted_at,NULL',
        ]);
        $validator->setAttributeNames([
            'role' => trans('team::view.Position name')
        ]);
        if ($validator->fails()) {
            Form::setData($dataItem);
            Form::setData();
            if ($isAjax) {
                return response()->json(['message' => $validator->messages()], 422);
            }
            if ($model->id) {
                return redirect()->route('team::setting.team.position.view', [
                        'id' => $model->id
                    ])->withErrors($validator);
            }
            return redirect()->route('team::setting.team.index')->withErrors($validator);
        }
        
        //calculate position level
        if (! $id) { //position new
            $positionLast = Role::select('sort_order')
                ->where('special_flg', Role::FLAG_POSITION)
                ->orderBy('sort_order', 'desc')
                ->first();
            if (count($positionLast)) {
                $dataItem['sort_order'] = $positionLast->sort_order + 1;
            } else {
                $dataItem['sort_order'] = 1;
            }
        }
        
        try {
            $model->setData($dataItem);
            $model->special_flg = Role::FLAG_POSITION;
            $result = $model->save();
            if (! $result) {
                if ($isAjax) {
                    return response()->json(['message' => Lang::get('team::messages.Error save data, please try again!')], 500);
                }
                return redirect()->route('team::setting.team.index')
                    ->withErrors(Lang::get('team::messages.Error save data, please try again!'));
            }
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Save data success!')]);
            }
            return redirect()->route('team::setting.team.position.view', [
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
            return redirect()->route('team::setting.team.index')
                    ->withErrors($ex);
        }
    }
    
    /**
     * Delete team position
     * @return type
     */
    public function delete()
    {
        $isAjax = request()->ajax() || request()->wantsJson();
        $id = Input::get('id');
        if (!$id) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found item.')], 404);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $model = Role::find($id);
        if (! $model || ! $model->isPosition()) {
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
            }
            return redirect()->route('team::setting.team.position.view', [
                    'id' => $id
                ])->withErrors($ex);
        }
    }
    
    /**
     * move team
     */
    public function move()
    {
        $isAjax = request()->ajax() || request()->wantsJson();
        $id = Input::get('id');
        if (!$id) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found item.')], 404);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $model = Role::find($id);
        if (!$model || $model->special_flg != Role::FLAG_POSITION) {
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
            return redirect()->route('team::setting.team.position.view', [
                    'id' => $id
                ])->with('messages', [
                    'success' => [
                        Lang::get('team::messages.Move item success!')
                    ]
                ]);
        } catch (\Exception $ex) {
            if ($isAjax) {
                return response()->json(['message' => $ex->getMessage()], 500);
            }
            return redirect()->route('team::setting.team.position.view', [
                    'id' => $id
                ])->withErrors($ex);
        }
    }
}
