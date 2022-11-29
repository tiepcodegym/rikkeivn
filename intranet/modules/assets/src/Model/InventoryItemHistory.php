<?php
namespace Rikkei\Assets\Model;

use Illuminate\Support\Facades\DB;
use Rikkei\Assets\View\AssetConst;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;


class InventoryItemHistory extends CoreModel
{
    protected $table = 'inventory_asset_item_histories';
    protected $fillable = ['inventory_asset_item_id', 'asset_id', 'asset_code', 'asset_name', 'status'];

    public $timestamps = true;

    /**
    * ajax get asset item of employee
    * @param [array] $dataFilter
    * @param [int|null] $employeeId
    * @param [int|null] $inventoryId
    * @return [collection]
    */
    public static function getGridDataAjax($dataFilter, $employeeId = null, $inventoryId = null)
    {
        $tblInventoryItemHistory = self::getTableName();
        $tblInventoryItem = InventoryItem::getTableName();

        $listConfirmLabels = AssetItem::labelAllocationConfirm();
        $collection = self::select(
            "{$tblInventoryItemHistory}.asset_id as id",
            "{$tblInventoryItemHistory}.asset_code",
            "{$tblInventoryItemHistory}.asset_name",
            "{$tblInventoryItemHistory}.status as allocation_confirm",
            "{$tblInventoryItemHistory}.note as employee_note",
            DB::raw(AssetConst::selectCase("{$tblInventoryItemHistory}.status", $listConfirmLabels) . ' AS label')
        )
        ->leftjoin("{$tblInventoryItem}", "{$tblInventoryItem}.id", "=", "{$tblInventoryItemHistory}.inventory_asset_item_id");
        if ($employeeId) {
            $collection = $collection->where('employee_id', $employeeId);
        }
        if ($inventoryId) {
            $collection->where("{$tblInventoryItem}.inventory_id", $inventoryId);
        }
        $pagerData = ['dir' => 'DESC'];
        if (isset($dataFilter['page'])) {
            $pagerData['page'] = $dataFilter['page'];
        }
        $pager = Config::getPagerData(null, $pagerData);
        $collection->orderBy($pager['order'], $pager['dir']);
        self::filterGrid($collection, [], null, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * update inventory_asset_item_histories when employee confirmation
     * @param [int] $inventoryId, $employeeId, $assetIds
     * @param  [array] $employeeNotes
     * @return  void
     */
    public static function updateHistoryConfirm($inventoryId, $employeeId, $assetIds, $employeeNotes)
    {
        $inventoryItemId = InventoryItem::where("inventory_id", $inventoryId)
            ->where("employee_id", $employeeId)
            ->first()
            ->id;
        $listAssetItems = self::whereIn('asset_id', $assetIds)->where('inventory_asset_item_id', $inventoryItemId)->pluck('asset_id')->toArray();
        $assetIdHisNew = array_diff($assetIds, $listAssetItems);

        //insert if have not
        $dataInsert = [];
        $listAssetNews = AssetItem::getAssetByIds($assetIdHisNew, ['id', 'code', 'name', 'allocation_confirm', 'employee_note', 'created_at', 'updated_at']);
        foreach ($listAssetNews as $item) {
            $dataInsert[$item->id]['inventory_asset_item_id'] = $inventoryItemId;
            $dataInsert[$item->id]['asset_id'] = $item->id;
            $dataInsert[$item->id]['asset_code'] = $item->code;
            $dataInsert[$item->id]['asset_name'] = $item->name;
            $dataInsert[$item->id]['status'] = $item->allocation_confirm;
            $dataInsert[$item->id]['note'] = $item->employee_note;
            $dataInsert[$item->id]['created_at'] = $item->created_at;
            $dataInsert[$item->id]['updated_at'] = $item->updated_at;
        }
        self::insert($dataInsert);
        //update not confirmed
        self::where("inventory_asset_item_id", $inventoryItemId)
                ->whereNotIn('asset_id', $assetIds)
                ->update(['status' => AssetItem::ALLOCATION_CONFIRM_FALSE]);
        //update confirmed
        self::where("inventory_asset_item_id", $inventoryItemId)
                ->whereIn('asset_id', $assetIds)
                ->update(['status' => AssetItem::ALLOCATION_CONFIRM_TRUE]);

        foreach ($employeeNotes as $assetId => $note) {
            self::where("inventory_asset_item_id", $inventoryItemId)
                ->where("asset_id", $assetId)
                ->update(["note" => $note]);
        }
    }
}
