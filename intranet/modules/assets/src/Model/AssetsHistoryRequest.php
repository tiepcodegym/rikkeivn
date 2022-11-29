<?php

namespace Rikkei\Assets\Model;

use Lang;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Assets\Model\RequestAssetHistory;

class AssetsHistoryRequest extends CoreModel
{
    protected $table = 'assets_histories';
    protected $fillable = ['request_asset_history_id', 'asset_id', 'code', 'name', 'allocation_confirm', 'warehouse_id'];
    public $timestamps = true;

    public static function getAssetHistoriesByRequestId($id)
    {
        $tblAssetsHistoryRequest = self::getTableName();
        $tblRequestAssetHistory = RequestAssetHistory::getTableName();
        return self::select("{$tblAssetsHistoryRequest}.id", "{$tblAssetsHistoryRequest}.name as asset_name", "{$tblAssetsHistoryRequest}.allocation_confirm", "{$tblAssetsHistoryRequest}.code")
            ->join("{$tblRequestAssetHistory}", "{$tblAssetsHistoryRequest}.request_asset_history_id", "=", "{$tblRequestAssetHistory}.id")
            ->where("{$tblAssetsHistoryRequest}.request_asset_history_id", "=", $id)
            ->orderBy("{$tblAssetsHistoryRequest}.created_at", 'desc')
            ->get();
    }
}
