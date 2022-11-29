<?php

namespace Rikkei\Api\Http\Controllers;

use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Api\Models\ApiToken;
use Rikkei\Api\Helper\Helper;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Validator;

class SettingController extends Controller
{
    public function _construct() {
        Menu::setActive(null, null, 'setting');
        Breadcrumb::add(trans('api::view.Api access token setting'));
    }

    /*
     * view page list api token
     */
    public function apiToken()
    {
        return view('api::setting.api-token.index', [
            'collectionModel' => ApiToken::getData()->groupBy('route'),
            'routes' => Helper::listRoutes(Helper::TYPE_LABEL),
        ]);
    }

    /*
     * view page edit api token
     */
    public function editApiToken($id = null)
    {
        return view('api::setting.api-token.edit', [
            'item' => $id ? ApiToken::find($id) : null
        ]);
    }

    /*
     * save api token
     */
    public function saveApiToken(Request $request)
    {
        $data = $request->except(['_token', 'id']);
        $id = $request->get('id');
        $valid = Validator::make($data, [
            'route' => 'required|unique:api_tokens,route' . ($id ? ',' . $id : ''),
            'token' => 'max:128',
            'expired_at' => 'date_format:Y-m-d H:i',
        ]);
        $valid->setAttributeNames([
            'route' => trans('api::view.Api route'),
            'token' => trans('api::view.Api token'),
            'expired_at' => trans('api::view.Expired at'),
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $routes = Helper::listRoutes();
        if (!isset($routes[$request->get('route')])) {
            return redirect()->back()->withInput()->withErrors(Lang::get('api::message.Route does not exits!'));
        }
        if ($id) {
            if (null === $item = ApiToken::find($id)) {
                return redirect()->back()->withInput()->withErrors(Lang::get('api::message.Route does not exits!'));
            }
            $item->update($data);
            CacheHelper::forget('token_' . str_slug($item->route));
        } else {
            $item = ApiToken::create($data);
        }
        return redirect()->route('api-web::setting.tokens.edit', $item->id)
                ->with('messages', ['success' => [trans('core::message.Save success')]]);
    }
}
