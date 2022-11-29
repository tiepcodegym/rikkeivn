<?php

namespace Rikkei\Assets\Http\Controllers;

use DB;
use Log;
use Lang;
use Exception;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Assets\Model\AssetOrigin;
use Rikkei\Assets\View\AssetPermission;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class AssetOriginController extends Controller
{
    /**
     * Construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Asset');
        Breadcrumb::add('Origin');
        Menu::setActive('HR');
    }

    /**
     * Asset origin list
     * @return [view]
     */
    public function index()
    {
        if (!AssetPermission::viewListPermision()) {
            View::viewErrorPermission();
        }
        $params = [
            'collectionModel' => AssetOrigin::getGridData(),
        ];
        return view('asset::origin.index')->with($params);
    }

    /**
     * Save asset origin
     */
    public function save()
    {
        if (!AssetPermission::createAndEditPermision()) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetOriginId = Input::get('id');
        if ($assetOriginId) {
            $assetOrigin = AssetOrigin::find($assetOriginId);
            if (!$assetOrigin) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
        } else {
            $assetOrigin = new AssetOrigin();
        }
        $rules = [
            'name' => 'required|max:100|unique:manage_asset_origins,name,' . $assetOriginId,
        ];
        $messages = [
            'name.required' => Lang::get('asset::message.Asset origin name is field required'),
            'name.max' => Lang::get('asset::message.Asset origin name not be greater than :number characters', ['number' => 100]),
            'name.unique' => Lang::get('asset::message.Asset origin name has exist'),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $assetOrigin->setData($dataItem);
            $assetOrigin->save();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors'=> [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
        $messages = [
            'success'=> [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        return redirect()->route('asset::asset.origin.index')->with('messages', $messages);
    }

    /**
     * Delete asset origin
     */
    public function delete()
    {
        if (!AssetPermission::deletePermision()) {
            View::viewErrorPermission();
        }
        $assetOriginId = Input::get('id');
        DB::beginTransaction();
        try {
            $assetOrigin = AssetOrigin::find($assetOriginId);
            if (!$assetOrigin) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            $countDataRelatedToOrigin = AssetOrigin::countDataRelatedToOrigin($assetOriginId);
            if ($countDataRelatedToOrigin) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Cannot delete asset origin have asset item'));
            }
            $assetOrigin->delete();
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('asset::message.Delete data success'),
                ]
            ];
            return redirect()->route('asset::asset.origin.index')->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors'=> [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
    }

    /**
     * Check unique asset origin name
     * @return [json]
     */
    public function checkExistAssetOriginName()
    {
        $valid = false;
        if (Input::get('assetOriginName')) {
            $assetOriginId = Input::get('assetOriginId');
            $assetOriginName = Input::get('assetOriginName');
            if (!AssetOrigin::checkExistAssetOriginName($assetOriginId, $assetOriginName)) {
                $valid = true;
            }
        }
        return response()->json($valid);
    }
}
