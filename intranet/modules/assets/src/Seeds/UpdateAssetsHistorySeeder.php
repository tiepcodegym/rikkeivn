<?php

namespace Rikkei\Assets\Seeds;

use DB;
use Exception;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Assets\Model\AssetsHistoryRequest;
use Rikkei\Assets\Model\RequestAssetHistory;
use Rikkei\Core\Seeds\CoreSeeder;

class UpdateAssetsHistorySeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        DB::beginTransaction();
        try {
            $tblAssetsHistoryRequest = AssetsHistoryRequest::getTableName();
            $tblAssetItem = AssetItem::getTableName();
            $tblRequestAssetHistory = RequestAssetHistory::getTableName();
            $assetRequestId =  AssetsHistoryRequest::select("{$tblRequestAssetHistory}.request_id")
                ->join("{$tblRequestAssetHistory}", "{$tblAssetsHistoryRequest}.request_asset_history_id", "=", "{$tblRequestAssetHistory}.id")
                ->groupBy("{$tblRequestAssetHistory}.request_id")
                ->get();
            $listAssetIteam = AssetItem::select(
                "{$tblAssetItem}.id as asset_id",
                "{$tblAssetItem}.code",
                "{$tblAssetItem}.name",
                "{$tblAssetItem}.allocation_confirm",
                "{$tblRequestAssetHistory}.id as request_asset_history_id",
                "{$tblAssetItem}.warehouse_id",
                "{$tblRequestAssetHistory}.created_at",
                "{$tblRequestAssetHistory}.updated_at"
            )
            ->leftJoin("{$tblRequestAssetHistory}", "{$tblAssetItem}.request_id", "=", "{$tblRequestAssetHistory}.request_id")
            ->whereNotIn("{$tblRequestAssetHistory}.request_id", $assetRequestId)
            ->whereNotNull("{$tblAssetItem}.allocation_confirm")
            ->where("{$tblRequestAssetHistory}.action", '=', RequestAssetHistory::ACTION_ALLOCATE)
            ->get()
            ->toArray();
            AssetsHistoryRequest::insert($listAssetIteam);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
