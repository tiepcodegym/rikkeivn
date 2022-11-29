<?php

namespace Rikkei\Assets\Http\Controllers;

use DB;
use Log;
use Lang;
use Exception;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Assets\Model\AssetSupplier;
use Rikkei\Assets\View\AssetView;
use Rikkei\Assets\View\AssetPermission;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class AssetSupplierController extends Controller
{
    /**
     * Construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Asset');
        Breadcrumb::add('Supplier');
        Menu::setActive('HR');
    }

    /**
     * Asset supplier list
     * @return [view]
     */
    public function index()
    {
        if (!AssetPermission::viewListPermision()) {
            View::viewErrorPermission();
        }
        $maxSupplierCode = AssetSupplier::getMaxSupplierCode();
        if (isset($maxSupplierCode)) {
            $maxSupplierCode = intval($maxSupplierCode->code_int);
        } else {
            $maxSupplierCode = AssetView::generateCode('NCC', $maxSupplierCode);
        }
        $supplierCode = AssetView::generateCode('NCC', $maxSupplierCode);

        $params = [
            'collectionModel' => AssetSupplier::getGridData(),
            'supplierCode' => $supplierCode,
            'type' => AssetSupplier::TYPE,
            'importGuide' => trans('asset::view.import supplier guide')

        ];
        return view('asset::supplier.index')->with($params);
    }

    /**
     * Save asset supplier
     */
    public function save()
    {
        if (!AssetPermission::createAndEditPermision()) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetSupplierId = Input::get('id');
        if ($assetSupplierId) {
            $assetSupplier = AssetSupplier::find($assetSupplierId);
            if (!$assetSupplier) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
        } else {
            $assetSupplier = new AssetSupplier();
        }
        $rules = [
            'code' => 'required|max:100|unique:manage_asset_suppliers,code,' . $assetSupplierId,
            'name' => 'required|max:100|unique:manage_asset_suppliers,name,' . $assetSupplierId,
            'address' => 'required|max:255',
            'phone' => 'max:20',
            'email' => 'email|max:100',
            'website' => 'max:100',
        ];
        $messages = [
            'code.required' => Lang::get('asset::message.Supplier code is field required'),
            'code.max' => Lang::get('asset::message.Supplier code not be greater than :number characters', ['number' => 100]),
            'code.unique' => Lang::get('asset::message.Supplier code has exist'),
            'name.required' => Lang::get('asset::message.Supplier name is field required'),
            'name.max' => Lang::get('asset::message.Supplier name not be greater than :number characters', ['number' => 100]),
            'name.unique' => Lang::get('asset::message.Supplier name has exist'),
            'address.required' => Lang::get('asset::message.Address is field required'),
            'address.max' => Lang::get('asset::message.Address not be greater than :number characters', ['number' => 255]),
            'phone.max' => Lang::get('asset::message.Phone not be greater than :number characters', ['number' => 20]),
            'email.max' => Lang::get('asset::message.Email not be greater than :number characters', ['number' => 100]),
            'email.email' => Lang::get('asset::message.Please enter a valid email address'),
            'website.max' => Lang::get('asset::message.Website not be greater than :number characters', ['number' => 100]),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $assetSupplier->setData($dataItem);
            $assetSupplier->save();
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
        return redirect()->route('asset::asset.supplier.index')->with('messages', $messages);
    }

    /**
     * Delete asset supplier
     */
    public function delete()
    {
        if (!AssetPermission::deletePermision()) {
            View::viewErrorPermission();
        }
        $assetSupplierId = Input::get('id');
        DB::beginTransaction();
        try {
            $assetSupplier = AssetSupplier::find($assetSupplierId);
            if (!$assetSupplier) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            $countDataRelatedToSupplier = AssetSupplier::countDataRelatedToSupplier($assetSupplierId);
            if ($countDataRelatedToSupplier) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Cannot delete asset supplier have asset item'));
            }
            $assetSupplier->delete();
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('asset::message.Delete data success'),
                ]
            ];
            return redirect()->route('asset::asset.supplier.index')->with('messages', $messages);
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
     * Import supplier
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
            //check valid file import
            if (!AssetView::checkHeading(AssetSupplier::defineHeadingFile(), $excel->getHeading())) {
                return redirect()
                    ->back()
                    ->withErrors(Lang::get('asset::message.Format not invalid'));
            }

            $check = AssetSupplier::importFile($excel->toArray());
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

    /**
     * Check exist in supplier
     *
     * @param Request $request
     * @return string
     */
    public function checkExist(Request $request)
    {
        $inputData = $request->all();
        return AssetSupplier::checkExist($inputData);
    }
}
