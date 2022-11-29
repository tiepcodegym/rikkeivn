<?php

namespace Rikkei\Assets\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Assets\Model\AssetItem;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\Model\Employee;
use Rikkei\Assets\View\AssetConst;

class RequestAssetItem extends CoreModel
{
    protected $table = 'request_asset_items';
    public $timestamps = false;
    protected $fillable = ['request_id', 'asset_category_id', 'quantity', 'quantity_allocated'];

    /**
     * Get request asset item by request
     * @param [int] $requestId
     * @return [array]
     */
    public static function getRequestAssetItems($request, $countAsset = false)
    {
        $requestId = $request;
        if (is_object($request)) {
            $requestId = $request->id;
        }
        $tblRequestAssetItem = self::getTableName();
        $tblRequestAsset = RequestAsset::getTableName();
        $tblAssetCategory = AssetCategory::getTableName();
        $joinCat = $tblAssetCategory;
        $select = ["{$tblRequestAssetItem}.asset_category_id", "{$tblRequestAssetItem}.quantity", "{$tblAssetCategory}.name as asset_category_name"];
        if ($countAsset) {
            $teamCode = Employee::getNewestTeamCode($request->employee_id);
            $prefix = AssetConst::getAssetPrefixByCode($teamCode);
            $select[] = $tblAssetCategory . '.count_asset';
            $sql = 'SELECT cat.*, COUNT(asset.id) as count_asset FROM ' . $tblAssetCategory . ' AS cat '
                . 'LEFT JOIN ' . AssetItem::getTableName() . ' asset '
                . 'ON cat.id = asset.category_id '
                . 'AND asset.state = ' . AssetItem::STATE_NOT_USED . ' '
                . 'AND asset.deleted_at IS NULL ';

            $sql .= 'AND asset.prefix = "' . $prefix . '" GROUP BY cat.id';
            $joinCat = DB::raw('(' . $sql . ') AS ' . $tblAssetCategory);
        }

        return self::select($select)
            ->join("{$tblRequestAsset}", "{$tblRequestAsset}.id", "=", "{$tblRequestAssetItem}.request_id")
            ->join($joinCat, "{$tblAssetCategory}.id", "=", "{$tblRequestAssetItem}.asset_category_id")
            ->where("{$tblRequestAssetItem}.request_id", $requestId)
            ->get();
    }

    /**
     * increment quantity allocated
     * @param integer $requestId
     * @param integer $categoryId
     * @param integer $quantity
     * @return void
     */
    public static function updateQtyAllocated($requestId, $categoryId, $quantity)
    {
        return self::where('request_id', $requestId)
                ->where('asset_category_id', $categoryId)
                ->update(['quantity_allocated' => DB::raw('quantity_allocated + ' . $quantity)]);
    }

    /**
     * check all category is allocated
     * @param integer $requestId
     * @return boolean
     */
    public static function checkRequestAllocated($requestId)
    {
        $collect = self::where('request_id', $requestId)->get();
        if ($collect->isEmpty()) {
            return true;
        }
        foreach ($collect as $item) {
            if ($item->quantity > $item->quantity_allocated) {
                return false;
            }
        }
        return true;
    }

    /**
     * get total quantity of request
     * @param type $reuqestId
     * @return integer
     */
    public static function getCatsQty($reuqestId)
    {
        return self::where('request_id', $reuqestId)
                ->get()
                ->lists('quantity', 'asset_category_id')
                ->toArray();
    }

     /**
     * update request asset quantity
     * @param type $requestId
     * @param type $catId
     * @param type $quantity
     * @return type
     */
    public static function updateQuantity($requestId, $catId, $quantity)
    {
        return self::where('request_id', $requestId)
                ->where('asset_category_id', $catId)
                ->update(['quantity' => $quantity]);
    }

    /**
     * update request asset category id
     * @param type $requestId
     * @param type $catId
     * @param type $catIdNew
     * @return type
     */
    public static function updateCategoryId($requestId, $catId, $catIdNew)
    {
        $result = null;
        //$catId > 0 then update
        if ($catId && $catIdNew > 0) {
            $result = self::where('request_id', $requestId)
                    ->where('asset_category_id', $catId)
                    ->update(['asset_category_id' => $catIdNew]);
            $actionUpdate = RequestAssetHistory::ACTION_UPDATE_CAT;
        } elseif ($catId == 0 && $catIdNew > 0) { //$catId == 0 then add new
            $result = self::create([
                'request_id' => $requestId,
                'asset_category_id' => $catIdNew,
                'quantity' => 1
            ]);
            $actionUpdate = RequestAssetHistory::ACTION_ADD_CAT;
        } elseif ($catId > 0 && $catIdNew < 0) { //$catIdNew < 0 then remove
            self::where('request_id', $requestId)
                    ->where('asset_category_id', $catId)
                    ->delete();
            $result = $requestId;
            $actionUpdate = RequestAssetHistory::ACTION_DEL_CAT;
        } else {
            $actionUpdate = RequestAssetHistory::ACTION_UPDATE_CAT;
        }
        //update history
        $history = RequestAssetHistory::create([
            'request_id' => $requestId,
            'employee_id' => auth()->id(),
            'action' => $actionUpdate
        ]);
        $history->content = RequestAssetHistory::getContentHistory($history->action);
        return [
            'request' => $result,
            'history' => $history
        ];
    }
}
