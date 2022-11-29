<?php
use Rikkei\Assets\View\AssetConst;

$pageTitle = trans('asset::view.View detail') . ': '
        . AssetConst::getAssetActionLabel($item->type, AssetConst::assetActionsList());
?>
@include('asset::item.profile.asset', ['reportItem' => $item])