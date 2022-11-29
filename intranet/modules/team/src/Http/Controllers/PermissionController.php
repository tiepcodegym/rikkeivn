<?php

namespace Rikkei\Team\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Rikkei\Core\View\PublishQueueToJob;
use Rikkei\Team\Model\Team;
use Lang;
use Rikkei\Team\Model\Permission;
use Url;
use Rikkei\Team\Model\Role;

class PermissionController extends \Rikkei\Core\Http\Controllers\Controller
{    
    /**
     * save team rule
     */
    public function saveTeam()
    {
        $isAjax = request()->ajax() || request()->wantsJson();
        $teamId = Input::get('team.id');
        if (!$teamId) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found team.')], 404);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found team.'));
        }
        $team = Team::find($teamId);
        if (!$team) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found team.')], 404);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found team.'));
        }
        if (! $team->is_function) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::view.Team is not function')], 422);
            }
            return redirect()->route('team::setting.team.view', ['id' => $teamId])
                ->withErrors(Lang::get('team::view.Team is not function'));
        }
        if ($teamAs = $team->getTeamPermissionAs()) {
            $message = Lang::get('team::view.Team permisstion as team') .' ';
            $message .= '<a href="' . Url::route('team::setting.team.view', ['id' => $teamAs->id]) . '">';
            $message .= $teamAs->name;
            $message .= '</a>';
            if ($isAjax) {
                return response()->json(['message' => $message], 422);
            }
            return redirect()->route('team::setting.team.view', ['id' => $teamId])
                ->withErrors($message);
        }
        $permissions = Input::get('permission');
        try {
            Permission::saveRule((array) $permissions, $teamId);
            PublishQueueToJob::makeInstance()->cacheRole(0, 0 ,$teamId);
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Save data success!')]);
            }
            return redirect()->route('team::setting.team.view', ['id' => $teamId])->with('messages', [
                    'success' => [
                        Lang::get('team::messages.Save data success!')
                    ]
                ]);
        } catch (\Exception $ex) {
            if ($isAjax) {
                return response()->json(['message' => $ex->getMessage()], 500);
            }
            return redirect()->route('team::setting.team.view', ['id' => $teamId])->withErrors($ex);
        }
    }
    
    /**
     * save role permission action
     * 
     * @return type
     */
    public function saveRole()
    {
        $isAjax = request()->ajax() || request()->wantsJson();
        $id = Input::get('role.id');
        $model = Role::find($id);
        if (! $model || ! $model->isRole()) {
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Not found item.')], 404);
            }
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $permissions = Input::get('permission');
        try {
            Permission::saveRule((array) $permissions, $id, false);
            PublishQueueToJob::makeInstance()->cacheRole(0, $id ,0);
            $messages = [
                    'success'=> [
                        Lang::get('team::messages.Save data success!'),
                    ]
            ];
            if ($isAjax) {
                return response()->json(['message' => Lang::get('team::messages.Save data success!')]);
            }
            return redirect()->route('team::setting.role.view', ['id' => $model->id])->with('messages', $messages);
        } catch (\Exception $ex) {
            if ($isAjax) {
                return response()->json(['message' => $ex->getMessage()], 500);
            }
            return redirect()->route('team::setting.role.view', ['id' => $model->id])->withErrors($ex);
        }
    }
}
