<?php

namespace Rikkei\Assets\Console\Commands;

use DB;
use Exception;
use Illuminate\Console\Command;
use Lang;
use Log;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Assets\Model\ReportAsset;
use Rikkei\Assets\View\AssetConst;

class SynchronizedReportAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'synchronize_ra';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ hóa tài sản';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $build = $this->getBuilderReportAssets();
        $buildRA = clone $build;
        $buildRA2 = clone $build;
        $reportAsset = $buildRA2->groupBy('report_assets.id')->get();
        $reportAssets = $buildRA->get();
        $assetsReportItem = [];
        foreach ($reportAssets as $item) {
            $assetsReportItem[$item->id][] = $item->asset_id;
        }

        DB::beginTransaction();
        try {
            $this->updateStatusReportAssetsItem($reportAsset, $assetsReportItem);
            $this->updateStatusReportAssets($reportAsset, $assetsReportItem);

            DB::commit();
        } catch (Exception $ex) {
            \Log::info($ex);
            DB::rollback();
        }
    }

    public function getBuilderReportAssets()
    {
        return ReportAsset::select(
                'report_assets.id',
                'report_assets.creator_id',
                'report_assets.type',
                'report_assets.status as status_report',
                'rai.asset_id',
                'rai.status as status_report_assets',
                'ash.state as status_assets'
            )
            ->leftJoin('report_asset_items as rai', 'report_assets.id', '=', 'rai.report_id')
            ->join("manage_asset_histories as ash", function($join) {
                $join->on('ash.asset_id', '=', 'rai.asset_id')
                    ->on('ash.employee_id', '=', 'report_assets.creator_id')
                    ->on('ash.created_at', '>=', 'report_assets.updated_at');
            })
            ->where("report_assets.status", AssetConst::STT_RP_PROCESSING)
            ->whereIn("report_assets.type", [
                AssetConst::TYPE_HANDING_OVER,
                AssetConst::TYPE_LOST_NOTIFY,
                AssetConst::TYPE_BROKEN_NOTIFY,
            ])
            ->whereIn("ash.state", [
                AssetItem::STATE_NOT_USED,
                AssetItem::STATE_LOST,
                AssetItem::STATE_BROKEN,
                AssetItem::STATE_UNAPPROVE,
            ]);
    }

    public function updateStatusReportAssetsItem($reportAssets, $dataAssets)
    {
        foreach ($reportAssets as $key => $reportAsset) {
            $dataSync = [];
            foreach ($dataAssets[$reportAsset->id] as $value) {
                $dataSync[$value] = ['status' => $this->getStatusReportAssetsItem($reportAsset->type, $reportAsset->status_assets)];
            }
            $reportAsset->items()->syncWithoutDetaching($dataSync);
        }
        return;
    }

    public function updateStatusReportAssets($reportAssets, $dataAssets)
    {
        foreach ($reportAssets as $reportItem) {
            if ($reportItem->items->count() == count($dataAssets[$reportItem->id])) {
                $reportItem->status = $this->getStatusReportAssets($reportItem->type, $reportItem->status_assets);
                $reportItem->save();
            } else {
                $listAssetId = $reportItem->items()->get()->pluck("id")->toArray();
                $listAsset = AssetItem::whereIn('id', $listAssetId)
                    ->whereIn("state", [
                        AssetItem::STATE_BROKEN_NOTIFICATION,
                        AssetItem::STATE_LOST_NOTIFICATION,
                        AssetItem::STATE_SUGGEST_HANDOVER,
                    ])->get();
                if ($listAsset && count($listAsset) == 0) {
                        $reportAssetIteam = DB::table('report_asset_items')
                            ->where('report_id', $reportItem->id)
                            ->whereIn('asset_id', $listAssetId)
                            ->groupBy('status')
                            ->get();
                    if (count($reportAssetIteam) == 2) {
                        $reportItem->status = AssetConst::TYPE_APPROVALS;
                        $reportItem->save();
                    } else {
                        $reportItem->status = $this->getStatusReportAssets($reportItem->type, $reportItem->status_assets);
                        $reportItem->save();
                    }
                }
            }
        }
    }
    public function getStatusReportAssetsItem($type, $status)
    {
        $str = $type . '-' . $status;
        switch ($str) {
            case AssetConst::TYPE_HANDING_OVER . '-' . AssetItem::STATE_NOT_USED:
                $status = AssetItem::STATE_NOT_USED;
                break;
            case AssetConst::TYPE_LOST_NOTIFY . '-' . AssetItem::STATE_LOST:
                $status = AssetItem::STATE_LOST;
                break;
            case AssetConst::TYPE_BROKEN_NOTIFY . '-' . AssetItem::STATE_BROKEN:
                $status = AssetItem::STATE_BROKEN;
                break;
            case AssetConst::TYPE_BROKEN_NOTIFY . '-' . AssetItem::STATE_UNAPPROVE:
            case AssetConst::TYPE_LOST_NOTIFY . '-' . AssetItem::STATE_UNAPPROVE:
                $status = AssetItem::STATE_USING;
                break;
            default:
                break;
        }
        return $status;
    }

    public function getStatusReportAssets($type, $status)
    {
        $str = $type . '-' . $status;
        switch ($str) {
            case AssetConst::TYPE_HANDING_OVER . '-' . AssetItem::STATE_NOT_USED:
            case AssetConst::TYPE_LOST_NOTIFY . '-' . AssetItem::STATE_LOST:
            case AssetConst::TYPE_BROKEN_NOTIFY . '-' . AssetItem::STATE_BROKEN:
                $status = AssetConst::STT_RP_CONFIRMED;
                break;
            case AssetConst::TYPE_BROKEN_NOTIFY . '-' . AssetItem::STATE_UNAPPROVE:
            case AssetConst::TYPE_LOST_NOTIFY . '-' . AssetItem::STATE_UNAPPROVE:
                $status = AssetConst::STT_RP_REJECTED;
                break;
            default:
                break;
        }
        return $status;
    }
}

