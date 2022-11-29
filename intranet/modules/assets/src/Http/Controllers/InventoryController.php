<?php

namespace Rikkei\Assets\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Rikkei\Assets\Model\Inventory;
use Rikkei\Assets\Model\InventoryItem;
use Rikkei\Assets\Model\InventoryItemHistory;
use Rikkei\Assets\View\AssetConst;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;

class InventoryController extends Controller
{
    /**
     * Construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Admin');
        Breadcrumb::add(trans('asset::view.Asset manager'));
        Breadcrumb::add(trans('asset::view.Inventory assets'));
        Menu::setActive('Admin');
    }

    /**
     * Asset category list
     * @return [view]
     */
    public function index()
    {
        $collectionModel = Inventory::getGridData();
        $teamList = TeamList::toOption(null, true, false);
        return view('asset::inventory.index', compact('collectionModel', 'teamList'));
    }

    /**
     * render view edit item
     * @param type $id
     * @return type
     */
    public function edit($id = null)
    {
        $item = null;
        if ($id) {
            $item = Inventory::findOrFail($id);
        }
        $teams = null;
        if ($item) {
            $teams = $item->groups;
        }
        $teamList = TeamList::getList();
        $mailContent = CoreConfigData::getValueDb('inventory_asset_mail_content');
        if (!$mailContent) {
            $mailContent = trans('asset::view.inventory_asset_mail_content', ['link' => route('asset::profile.view-personal-asset')]);
        }
        return view('asset::inventory.edit', compact('item', 'teams', 'teamList', 'mailContent'));
    }

    /**
     * save item
     * @param Request $request
     * @return type
     */
    public function save(Request $request)
    {
        $data = $request->except('_token');
        $id = $request->get('id');
        $valid = Validator::make($data, [
            'name' => 'required|max:255|unique:' . Inventory::getTableName() . ',name' . ($id ? ',' . $id : ''),
            'time' => 'required|date_format:Y-m-d H:i',
            'team_ids' => 'required'
        ], [
            'name.required' => trans('asset::validate.required', ['field' => trans('asset::view.Name')]),
            'name.unique' => trans('asset::validate.unique', ['field' => trans('asset::view.Name')]),
            'time.required' => trans('asset::validate.required', ['field' => trans('asset::view.Time')]),
            'time.date_format' => trans('asset::validate.date_format', ['field' => trans('asset::view.Time')]),
            'team_ids.required' => trans('asset::validate.required', ['field' => trans('asset::view.Department')])
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $dataMail = $request->get('mail');
        //check exists
        $existsTeam = Inventory::checkExists($data);
        if ($existsTeam) {
            return redirect()
                ->back()
                ->withInput()
                ->with('is_render', true)
                ->with('messages', ['errors' => [trans('asset::validate.inventory_exists_team', [
                    'teams' => $existsTeam->team_names,
                    'time' => $existsTeam->time,
                    'link' => route('asset::inventory.edit', ['id' => $existsTeam->id])
                ])]]);
        }
        DB::beginTransaction();
        try {
            //create or edit inventory
            $item = Inventory::createOrUpdate($data);
            //save mail data
            $inventoryMailContent = CoreConfigData::getItem('inventory_asset_mail_content');
            $inventoryMailContent->value = $dataMail['content'];
            $inventoryMailContent->save();

            DB::commit();
            return redirect()
                    ->route('asset::inventory.edit', ['id' => $item->id])
                    ->with('messages', ['success' => [trans('asset::message.Save data success')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }

    /*
     * view detail inventory
     */
    public function detail($id)
    {
        $inventory = Inventory::findOrFail($id);
        $collectionModel = InventoryItem::getGridData($id);
        $teamList = TeamList::toOption(null, true, false);
        return view('asset::inventory.detail', compact('collectionModel', 'inventory', 'teamList'));
    }

    /*
     * delete inventory item
     */
    public function delete($id)
    {
        $item = Inventory::findOrFail($id);
        $item->delete();
        return redirect()->back()->withInput()->with('messages', ['success' => [trans('asset::message.Delete data success')]]);
    }

    /**
     * delete inventory detail item
     * @param type $itemId
     */
    public function deleteItem($itemId)
    {
        $item = InventoryItem::findOrFail($itemId);
        $item->delete();
        return redirect()->back()->with('messages', ['success' => [trans('asset::message.Delete data success')]]);
    }

    /*
     * export data
     */
    public function export(Request $request)
    {
        $inventoryId = $request->get('inventory_id');
        if (!$inventoryId) {
            return response()->json(trans('asset::message.Not found item'), 404);
        }
        $inventory = Inventory::find($inventoryId);
        if (!$inventory) {
            return response()->json(trans('asset::message.Not found item'), 404);
        }
        $urlFilter = route('asset::inventory.item_detail', ['id' => $inventoryId]) . '/';
        $collection = InventoryItem::getGridData($inventoryId, $urlFilter, true);

        return [
            'colsHead' => AssetConst::exportInventoryCols(),
            'sheetsData' => [
                'kiem_ke_tai_san' => $collection
            ],
            'fileName' => ucfirst(str_slug($inventory->name)) . '_' . Carbon::now()->now()->format('Y_m_d')
        ];
    }

    /**
     * alert do inventory
     * @param type $inventoryId
     * @return type
     */
    public function mailAlert($inventoryId)
    {
        $inventory = Inventory::find($inventoryId);
        if (!$inventory) {
            return response()->json(trans('asset::message.Not found item'), 404);
        }
        $result = InventoryItem::alertDoInventory($inventory);
        if ($result['status']) {
            return response()->json($result['message']);
        }
        return response()->json($result['message'], 500);
    }

     /*
     * get persional asset item ajax
     */
    public function getPersonalAssetAjax(Request $request)
    {
        $employeeId = $request->get('employeeId');
        $inventoryId = $request->get('inventoryId');
        $userCurrent = Permission::getInstance()->getEmployee();
        $employeeId = $employeeId ? $employeeId : $userCurrent->id;

        $dataFilter = [];
        $page = $request->get('page');
        $dataFilter['page'] = $page && $page > 0 ? $page : 1;
        return InventoryItemHistory::getGridDataAjax($dataFilter, $employeeId, $inventoryId);
    }
}
