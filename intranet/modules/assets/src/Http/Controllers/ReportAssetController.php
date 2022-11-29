<?php

namespace Rikkei\Assets\Http\Controllers;


use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rikkei\Assets\Model\ReportAsset;
use Rikkei\Team\View\TeamList;

class ReportAssetController extends Controller
{
    public function _construct() {
        parent::_construct();
        Menu::setActive('admin');
        Breadcrumb::add(trans('asset::view.List hand over, lost, broken asset report'), route('asset::report.index'));
    }

    /**
     * view list report
     * @return type
     */
    public function index()
    {
        return view('asset::report.index', [
            'collectionModel' => ReportAsset::getGridData(),
            'teamList' => TeamList::toOption(null, false, false)
        ]);
    }

    /**
     * view detail report
     * @param type $id
     * @return type
     */
    public function detail($id)
    {
        $item = ReportAsset::findOrFail($id);
        $assetItems = ReportAsset::getAssetItems($item->id);

        return view('asset::report.detail', [
            'item' => $item,
            'assetItems' => $assetItems,
            'type' => $item->type,
            'employeeId' => $item->creator_id
        ]);
    }

    /**
     * confirm asset report
     * @param type $reportId
     * @param Request $request
     * @return type
     */
    public function confirm($reportId, Request $request)
    {
        $reportItem = ReportAsset::findOrFail($reportId);
        $data = $request->all();
        $status = $request->get('status');
        if (!$status) {
            return redirect()->back()->withInput()->with('messages', ['errors' => trans('asset::message.Not found item')]);
        }
        DB::beginTransaction();
        try {
            ReportAsset::confirmItem($reportItem, $status, $data);
            DB::commit();
            return redirect()->back()->with('messages', ['success' => [trans('asset::message.Save data success')]]);
        } catch (Exception $ex) {
            \Log::info($ex);
            DB::rollback();
            return redirect()->back()->with('messages', ['errors' => [trans('asset::message.System error')]]);
        }
    }

    /**
     * delete item
     * @param type $id
     * @return type
     */
    public function delete($id)
    {
        $item = ReportAsset::findOrFail($id);
        $item->delete();
        return redirect()
                ->back()
                ->with('messages', ['success' => [trans('asset::message.Delete data success')]]);
    }
}
