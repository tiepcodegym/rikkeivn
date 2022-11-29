<?php
    use Rikkei\Assets\View\AssetConst;
    use Rikkei\Assets\Model\AssetItem;
?>
<div class="filter-asset">
     <button class="btn btn-success" type="button" data-toggle="modal" data-target="#modal_asset_export">{{ trans('team::view.Export') }}</button>
    @if (isset($allowApprove) && $allowApprove)
        <div class="btn-group btn-group-approve">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">{{ trans('asset::view.Approve') }}
            <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li class="dropdown-item"><a class="btn-get-asset-approve" state="{{ AssetItem::STATE_LOST_NOTIFICATION }}">{{ trans('asset::view.Asset lost notification') }}</a></li>
                <li class="dropdown-item"><a class="btn-get-asset-approve" state="{{ AssetItem::STATE_BROKEN_NOTIFICATION }}">{{ trans('asset::view.Asset broken notification') }}</a></li>
                <li class="dropdown-item"><a class="btn-get-asset-approve" state="{{ AssetItem::STATE_SUGGEST_LIQUIDATE }}">{{ trans('asset::view.Asset suggest liquidate') }}</a></li>
                <li class="dropdown-item"><a class="btn-get-asset-approve" state="{{ AssetItem::STATE_SUGGEST_REPAIR_MAINTENANCE }}">{{ trans('asset::view.Asset suggest repair, maintenance') }}</a></li>
                <li class="dropdown-item"><a class="btn-get-asset-approve" state="{{ AssetItem::STATE_REPAIRED_MAINTAINED }}">{{ trans('asset::view.Asset repaired, maintained') }}</a></li>
            </ul>
        </div>
    @endif
    @if (isset($allowRport) && $allowRport)
        <div class="btn-group btn-group-report">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">{{ trans('asset::view.Print') }}
            <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li class="dropdown-item"><a class="asset-report" id="show_modal_report_lost_and_broken" data-toggle="modal" data-target="#report_lost_and_broken">{{ trans('asset::view.Assets lost and broken list') }}</a></li>
                <li class="dropdown-item">
                    <a data-toggle="modal" data-target="#modalReport-{{ AssetConst::REPORT_TYPE_DETAIL_BY_EMPLOYEE }}" id="report_by_employee">{{ trans('asset::view.Report asset detail by employee') }}</a>
                </li>
                <li class="dropdown-item">
                    <a  data-toggle="modal" data-target="#modalReport-{{ AssetConst::REPORT_TYPE_DETAIL_ON_ASSET_USE_PROCESS }}" id="report_by_process">{{ trans('asset::view.Report of asset movements') }}</a>
                </li>
            </ul>
        </div>
    @endif
    <button class="btn btn-primary btn-reset-filter">
        <span>{{ trans('asset::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
    <br>
    <div class="filter-additional">
        <div class="dropdown">
            <button onclick="myFunction()" class="dropbtn">Filter <i class="fa fa-filter" aria-hidden="true"></i></button>
            <div id="myDropdown" class="dropdown-content">
                <div class="form-check item">
                    <input type="checkbox" class="form-check-input" id="checkConfigureNull">
                    <label class="form-check-label" for="checkConfigureNull">Cấu hình trống</label>
                </div>
                <div class="form-check item">
                    <input type="checkbox" class="form-check-input" id="checkSerialNull">
                    <label class="form-check-label" for="checkSerialNull">Số seri trống</label>
                </div>
            </div>
          </div>
    </div>
</div>