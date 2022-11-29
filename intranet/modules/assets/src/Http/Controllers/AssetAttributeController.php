<?php

namespace Rikkei\Assets\Http\Controllers;

use DB;
use Log;
use Lang;
use Exception;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Assets\Model\AssetAttribute;
use Rikkei\Assets\Model\AssetCategory;
use Rikkei\Assets\View\AssetPermission;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class AssetAttributeController extends Controller
{
    /**
     * Construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Asset');
        Breadcrumb::add('Attribute');
        Menu::setActive('HR');
    }

    /**
     * Asset attribute list
     * @return [view]
     */
    public function index()
    {
        if (!AssetPermission::viewListPermision()) {
            View::viewErrorPermission();
        }
        $params = [
            'collectionModel' => AssetAttribute::getGridData(),
            'assetCategoriesList' => AssetCategory::getAssetCategoriesList(),
        ];
        return view('asset::attribute.index')->with($params);
    }

    /**
     * Save asset attribute
     */
    public function save()
    {
        if (!AssetPermission::createAndEditPermision()) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetAttributeId = Input::get('id');
        if ($assetAttributeId) {
            $assetAttribute = AssetAttribute::find($assetAttributeId);
            if (!$assetAttribute) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
        } else {
            $assetAttribute = new AssetAttribute();
        }
        $rules = [
            'name' => 'required|max:100',
            'category_id' => 'required',
        ];
        $messages = [
            'name.required' => Lang::get('asset::message.Asset attribute name is field required'),
            'name.max' => Lang::get('asset::message.Asset attribute name not be greater than :number characters', ['number' => 100]),
            'name.unique' => Lang::get('asset::message.Asset attribute name has exist'),
            'category_id.required' => Lang::get('asset::message.Asset category is field required'),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $assetAttribute->setData($dataItem);
            $assetAttribute->save();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withErrors($ex->getMessage());
        }
        $messages = [
            'success'=> [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        return redirect()->route('asset::asset.attribute.index')->with('messages', $messages);
    }

    /**
     * Delete asset attribute
     */
    public function delete()
    {
        if (!AssetPermission::deletePermision()) {
            View::viewErrorPermission();
        }
        $assetAttributeId = Input::get('id');
        DB::beginTransaction();
        try {
            $assetAttribute = AssetAttribute::find($assetAttributeId);
            if (!$assetAttribute) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            $countDataRelatedToAttribute = AssetAttribute::countDataRelatedToAttribute($assetAttributeId);
            if ($countDataRelatedToAttribute) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Cannot delete asset attribute have asset item'));
            }
            $assetAttribute->delete();
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('asset::message.Delete data success'),
                ]
            ];
            return redirect()->route('asset::asset.attribute.index')->with('messages', $messages);
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
     * Check unique asset attribute name
     * @return [json]
     */
    public function checkExistAssetAttributeName()
    {
        $valid = false;
        if (Input::get('assetAttributeName')) {
            $assetAttributeId = Input::get('assetAttributeId');
            $assetAttributeName = Input::get('assetAttributeName');
            if (!AssetAttribute::checkExistAssetAttributeName($assetAttributeId, $assetAttributeName)) {
                $valid = true;
            }
        }
        return response()->json($valid);
    }
}
