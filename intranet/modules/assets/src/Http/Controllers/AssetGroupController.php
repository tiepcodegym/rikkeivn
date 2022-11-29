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
use Rikkei\Assets\View\AssetPermission;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Rikkei\Assets\View\AssetView;
use Maatwebsite\Excel\Facades\Excel;

class AssetGroupController extends Controller
{
    /**
     * Construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Asset');
        Breadcrumb::add('Group');
        Menu::setActive('HR');
    }

    /**
     * Asset group list
     * @return [view]
     */
    public function index()
    {
        if (!AssetPermission::viewListPermision()) {
            View::viewErrorPermission();
        }
        $params = [
            'collectionModel' => AssetGroup::getGridData(),
            'type' => AssetGroup::TYPE,
            'importGuide' => trans('asset::view.import asset group guide')
        ];
        return view('asset::group.index')->with($params);
    }

    /**
     * Save asset group
     */
    public function save()
    {
        if (!AssetPermission::createAndEditPermision()) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetGroupId = Input::get('id');
        if ($assetGroupId) {
            $assetGroup = AssetGroup::find($assetGroupId);
            if (!$assetGroup) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
        } else {
            $assetGroup = new AssetGroup();
        }
        $rules = [
            'name' => 'required|max:100|unique:manage_asset_groups,name,' . $assetGroupId,
        ];
        $messages = [
            'name.required' => Lang::get('asset::message.Asset group name is field required'),
            'name.max' => Lang::get('asset::message.Asset group name not be greater than :number characters', ['number' => 100]),
            'name.unique' => Lang::get('asset::message.Asset group name has exist'),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $assetGroup->setData($dataItem);
            $assetGroup->save();
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
        return redirect()->route('asset::asset.group.index')->with('messages', $messages);
    }

    /**
     * Delete asset group
     */
    public function delete()
    {
        if (!AssetPermission::deletePermision()) {
            View::viewErrorPermission();
        }
        $assetGroupId = Input::get('id');
        DB::beginTransaction();
        try {
            $assetGroup = AssetGroup::find($assetGroupId);
            if (!$assetGroup) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            $countDataRelatedToGroup = AssetGroup::countDataRelatedToGroup($assetGroupId);
            if ($countDataRelatedToGroup) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Cannot delete asset group have asset item'));
            }
            $assetGroup->delete();
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('asset::message.Delete data success'),
                ]
            ];
            return redirect()->route('asset::asset.group.index')->with('messages', $messages);
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
     * Check unique asset group name
     * @return [json]
     */
    public function checkExistAssetGroupName()
    {
        $valid = false;
        if (Input::get('assetGroupName')) {
            $assetGroupId = Input::get('assetGroupId');
            $assetGroupName = Input::get('assetGroupName');
            if (!AssetGroup::checkExistAssetGroupName($assetGroupId, $assetGroupName)) {
                $valid = true;
            }
        }
        return response()->json($valid);
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
            if (!AssetView::checkHeading(AssetGroup::defineHeadingFile(), $excel->getHeading())) {
                return redirect()
                    ->back()
                    ->withErrors(Lang::get('asset::message.Format not invalid'));
            }
            AssetGroup::importFile($excel->toArray());
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
