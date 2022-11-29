<?php

namespace Rikkei\Assets\Http\Controllers;

use DB;
use Log;
use Lang;
use Exception;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Assets\Model\AssetGroup;
use Rikkei\Assets\Model\AssetCategory;
use Rikkei\Assets\View\AssetPermission;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Assets\View\AssetView;

class AssetCategoryController extends Controller
{
    /**
     * Construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Asset');
        Breadcrumb::add('Category');
        Menu::setActive('HR');
    }

    /**
     * Asset category list
     * @return [view]
     */
    public function index()
    {
        if (!AssetPermission::viewListPermision()) {
            View::viewErrorPermission();
        }
        $params = [
            'collectionModel' => AssetCategory::getGridData(),
            'assetGroupsList' => AssetGroup::getAssetGroupsList(),
            'type' => AssetCategory::TYPE,
            'importGuide' => trans('asset::view.import asset type guide')
        ];
        return view('asset::category.index')->with($params);
    }

    /**
     * Save asset category
     */
    public function save()
    {
        if (!AssetPermission::createAndEditPermision()) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetCategoryId = Input::get('id');
        if ($assetCategoryId) {
            $assetCategory = AssetCategory::find($assetCategoryId);
            if (!$assetCategory) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
        } else {
            $assetCategory = new AssetCategory();
        }
        $rules = [
            'name' => 'required|max:100|unique:manage_asset_categories,name,' . $assetCategoryId,
            'group_id' => 'required',
            'prefix_asset_code' => 'required|max:20|unique:manage_asset_categories,prefix_asset_code,' . $assetCategoryId,
        ];
        $messages = [
            'name.required' => Lang::get('asset::message.Asset category name is field required'),
            'name.max' => Lang::get('asset::message.Asset category name not be greater than :number characters', ['number' => 100]),
            'name.unique' => Lang::get('asset::message.Asset category name has exist'),
            'group_id.required' => Lang::get('asset::message.Asset group is field required'),
            'prefix_asset_code.required' => Lang::get('asset::message.Asset code prefix is field required'),
            'prefix_asset_code.max' => Lang::get('asset::message.Asset code prefix not be greater than :number characters', ['number' => 20]),
            'prefix_asset_code.unique' => Lang::get('asset::message.Asset code prefix has exist'),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            if (!isset($dataItem['is_default'])) {
                $dataItem['is_default'] = 0;
            }
            $assetCategory->setData($dataItem);
            $assetCategory->save();
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
        return redirect()->route('asset::asset.category.index')->with('messages', $messages);
    }

    /**
     * Delete asset category
     */
    public function delete()
    {
        if (!AssetPermission::deletePermision()) {
            View::viewErrorPermission();
        }
        $assetCategoryId = Input::get('id');
        DB::beginTransaction();
        try {
            $assetCategory = AssetCategory::find($assetCategoryId);
            if (!$assetCategory) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            $countDataRelatedToCategory = AssetCategory::countDataRelatedToCategory($assetCategoryId);
            if ($countDataRelatedToCategory) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Cannot delete asset category have asset item'));
            }
            $assetCategory->delete();
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('asset::message.Delete data success'),
                ]
            ];
            return redirect()->route('asset::asset.category.index')->with('messages', $messages);
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
     * Check exits category (prefix, name)
     * @param Request $request
     * @return string
     */
    public function checkExist(Request $request)
    {
        $dataForm = $request->all();
        return AssetCategory::checkExit($dataForm);
    }

    /**
     * Import excel
     * @return $this
     */
    public function importFile()
    {
        \PHPExcel_Settings::setZipClass(\PHPExcel_Settings::PCLZIP);
        $file = Input::file('file_upload');
        $exFile = $file->getClientOriginalExtension();

        if (!in_array($exFile, ['xlsx', 'xls'])) {
            return redirect()->back()->withErrors(Lang::get('asset::message.File not invalid'));
        }

        DB::beginTransaction();
        try {
            $excel = Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader) {
            })->get();

            //check format file
            if (!AssetView::checkHeading(AssetCategory::defineHeadingFile(), $excel->getHeading())) {
                return redirect()
                    ->back()
                    ->withErrors(Lang::get('asset::message.Format not invalid'));
            }
            $check = AssetCategory::importFile($excel->toArray());
            if (!empty($check)) {
                return redirect()->back()->withErrors($check);
            }

            DB::commit();
            return redirect()
                ->back()
                ->with('messages', ['success'=> [Lang::get('asset::message.Import success')]]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()
                ->back()
                ->withErrors(Lang::get('asset::message.Error read file excel, please try again'));
        }
    }
}
