<?php

namespace Rikkei\Api\Http\Controllers\Asset;

use Rikkei\Api\Helper\Asset;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lang;
use Illuminate\Support\Facades\Validator;
use Rikkei\Assets\Model\AssetCategory;

class AssetController extends Controller
{
    /**
     * get asset info
     * @params asset code
     */
    public function getInfo(Request $request)
    {
        try {
            $assetInfo = Asset::getInstance()->getInfo($request->code);
            return [
                'success' => 1,
                'data' => $assetInfo,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => $ex->getMessage()
            ];
        }
    }

    /**
     * get all assets
     */
    public function getAll()
    {
        try {
            $assets = Asset::getInstance()->getAll();
            return [
                'success' => 1,
                'data' => $assets,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => $ex->getMessage()
            ];
        }
    }

    /**
     * get assets list
     * @params asset codes
     */
    public function getList(Request $request)
    {
        try {
            $assets = Asset::getInstance()->getAssetsList($request->codes);
            return [
                'success' => 1,
                'data' => $assets,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => $ex->getMessage()
            ];
        }
    }

    /**
     * get assets list of employee
     * @params employee code
     */
    public function getAssetsOfEmployee(Request $request)
    {
        try {
            $assets = Asset::getInstance()->getAssetsOfEmployee($request->code);
            return [
                'success' => 1,
                'data' => $assets,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => $ex->getMessage()
            ];
        }
    }

    public function requestAssetCandidate(Request $request)
    {
        $data = $request->all();
        $rules = [
            'employee_email' => 'required|exists:employees,email',
            'employee_skype' => 'required',
            'reviewer_email' => 'required|exists:employees,email',
            'request_name' => 'required|max:250',
            'request_date' => 'required|date_format:Y-m-d',
            'request_reason' => 'required',
            'asset' => 'required|array',
            'creator' => 'required|exists:employees,email',
        ];
        $messages = [
            'employee_email.required' => Lang::get('asset::message.Petitioner is field required'),
            'request_date.required' => Lang::get('asset::message.Request date is field required'),
            'reviewer_email.required' => Lang::get('asset::message.Request approver is field required'),
            'request_reason.required' => Lang::get('asset::message.Note is field required'),
            'skype.required' => Lang::get('asset::message.Skype is field required'),
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        //Check validate array asset
        $vldAssetId = [];
        $vldNumber = [];
        $assets = $data['asset'];
        $arrayCheck = [];
        $arrAssets = [];
        foreach ($assets as $key => $asset) {
            if (empty($asset['asset_code'])) {
                $vldAssetId[] = 'Asset ['.$key.'] id is required.';
                continue;
            }
            if (empty($asset['number'])) {
                $vldNumber[] = 'Number ['.$key.'] is required.';
                continue;
            }
            $dtAsset = AssetCategory::where('prefix_asset_code', $asset['asset_code'])->first();
            if (!$dtAsset) {
                $vldAssetId[] = 'The selected Asset id ['.$key.'] is invalid.';
                continue;
            }
            if (!preg_match('/^[1-9]{1}[0-9]{0,4}$/', $asset['number'])) {
                $vldNumber[] = "Number [".$key."] must be greater than 0 and can't be larger than 5 characters";
                continue;
            }
            //check duplicate asset
            if (!in_array($dtAsset->id, $arrayCheck)) {
                array_push($arrayCheck, $dtAsset->id);
            } else {
                $vldAssetId[] = 'Asset code ['.$key.'] is different field.';
            }
            $arrAssets[] = [
                'name' => $dtAsset->id,
                'number' => $asset['number']
            ];
        }
        if ($vldAssetId) {
            $validator = validator()->make([], []);
            $validator->after(function ($vld) use ($vldAssetId) {
                $vld->errors()->add('asset_code', $vldAssetId);
            });
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()
            ]);
        }
        if ($vldNumber) {
            $validator = validator()->make([], []);
            $validator->after(function ($vld) use ($vldNumber) {
                $vld->errors()->add('number', $vldNumber);
            });
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()
            ]);
        }
        $data['asset'] = $arrAssets;

        try {
            return Asset::getInstance()->requestAssetCandidate($data);
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => Asset::getInstance()->errorMessage($ex)
            ];
        }
    }

}
