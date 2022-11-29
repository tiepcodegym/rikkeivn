<?php

namespace Rikkei\Assets\Http\Controllers;

use Carbon\Carbon;
use Rikkei\Team\Model\Employee;
use DB;
use Log;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Assets\Model\AssetWarehouse;
use Illuminate\Support\Facades\Lang;
use Validator;
use Rikkei\Assets\View\AssetPermission;
use Rikkei\Assets\View\AssetView;

class WarehouseController extends Controller
{
    /**
     * List warehouse
     *
     * @return $this
     */
    public function index()
    {
        if (!AssetPermission::viewListPermision()) {
            View::viewErrorPermission();
        }
        $now = Carbon::now();
        $employees = Employee::select("id", "employee_code", "name", "email")
        ->where(function ($query) use ($now) {
            $query->orWhereNull('leave_date')
                ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
        })->get();

        $params = [
            'collectionModel' => AssetWarehouse::getGridData(),
            'branchs' => AssetView::getAssetBranch(),
            'employees' => $employees,
        ];
        return view('asset::warehouse.index')->with($params);
    }

    /**
     * Check exist
     *
     * @param Request $request
     * @return string
     */
    public function checkExist(Request $request)
    {
        $inputData = $request->all();
        return AssetWarehouse::checkExist($inputData);
    }

    public function save()
    {
        if (!AssetPermission::createAndEditPermision()) {
            View::viewErrorPermission();
        }
        $input = Input::get('item');
        $input['id'] = !empty($input['id']) ? $input['id'] : null;
        if ($input['id']) {
            $warehouse = AssetWarehouse::find($input['id']);
            if (!$warehouse) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
        } else {
            $warehouse = new AssetWarehouse();
        }
        $rules = [
            'name' => 'required|max:100|unique:manage_asset_warehouse,name,' . $input['id'],
            'code' => 'required|unique:manage_asset_warehouse,code,' . $input['id'],
        ];
        $messages = [
            'name.required' => Lang::get('asset::message.Asset warehouse name is field required'),
            'name.max' => Lang::get('asset::message.Asset warehouse name not be greater than :number characters', ['number' => 100]),
            'name.unique' => Lang::get('asset::message.Asset warehouse name has exist'),
            'code.required' => Lang::get('asset::message.Asset warehouse code is field required'),
            'code.unique' => Lang::get('asset::message.Asset warehouse code has exist'),
        ];
        $validator = Validator::make($input, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $warehouse->setData($input);
            $warehouse->save();
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
        return redirect()->route('asset::asset.warehouse.index')->with('messages', $messages);
    }

    /**
     * Delete asset category
     */
    public function delete()
    {
        if (!AssetPermission::deletePermision()) {
            View::viewErrorPermission();
        }
        $warehouseId = Input::get('id');
        DB::beginTransaction();
        try {
            $assetWarehouse = AssetWarehouse::find($warehouseId);
            if (!$assetWarehouse) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            $countAssetByWarehouse = AssetItem::countAssetByWarehouse($warehouseId);
            if ($countAssetByWarehouse > 0) {
                return redirect()->back()->withErrors(Lang::get('asset::message.The warehouse has asset, can not be deleted!'));
            }
            $assetWarehouse->delete();
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('asset::message.Delete data success'),
                ]
            ];
            return redirect()->route('asset::asset.warehouse.index')->with('messages', $messages);
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

}
