@extends('layouts.default')

<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Assets\Model\AssetItem;
    use Rikkei\Assets\View\AssetConst;
    use Rikkei\Assets\View\AssetPermission;
    use Rikkei\Team\View\TeamList;
    use Rikkei\Assets\Model\AssetWarehouse;
    use Rikkei\Assets\View\AssetView;

    $labelAllocationConfirm = AssetItem::labelAllocationConfirm();
    $teamsOptionAll = TeamList::toOption(null, true, false);

    $tblAssetItem = AssetItem::getTableName();
    $tblEmployeeAsManage = 'tbl_employee_manage';
    $tblEmployeeAsUse = 'tbl_employee_use';
    $tblWarehouse = AssetWarehouse::getTableName();
    $labelStates = AssetItem::labelStates();

    $allowAddAndEdit = AssetPermission::createAndEditPermision();
    $allowDelete = AssetPermission::deletePermision();
    $allowViewDetail = AssetPermission::viewDetailPermision();
    $allowApprove = AssetPermission::approvePermision();
    $allowAllocation = AssetPermission::allocationAndRetrievalPermision();
    $allowRport = AssetPermission::reportPermision();

    $showColumnAction = ($allowAddAndEdit || $allowDelete || $allowViewDetail);
    $colColspan = 14;
    if ($showColumnAction && $allowAllocation) {
        $colColspan = 14;
    } else if ((!$showColumnAction && $allowAllocation) || ($showColumnAction && !$allowAllocation)) {
        $colColspan = 13;
    } else {
        $colColspan = 12;
    }

?>

@section('title')
    {{ trans('asset::view.Assets list') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.9/css/dataTables.checkboxes.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <div class="row">
                        <div class="col-md-7 asset-btns">
                            @if ($allowAddAndEdit)
                                <a href="{{ route('asset::asset.add') }}"><button class="btn btn-success"><i class="fa fa-plus" aria-hidden="true"></i> {{ trans('asset::view.Add new') }}</button></a>
                                <button class="btn btn-primary import-file" data-toggle="modal" data-target="#importFile">{{ trans('asset::view.Import file') }}</button>
                            @endif
                            @if ($allowAllocation)
                                <button class="btn btn-default btn-get-asset-information btn-allocation" value="{{ AssetConst::MODAL_ALLOCATION }}">{{ trans('asset::view.Allocation') }}</button>
                                <button class="btn btn-default btn-get-asset-information btn-retrieval" value="{{ AssetConst::MODAL_RETRIEVAL }}">{{ trans('asset::view.Retrieval') }}</button>
                                <button class="btn btn-default btn-get-asset-information btn-lost-notification" value="{{ AssetConst::MODAL_LOST_NOTIFICATION }}" >{{ trans('asset::view.Lost notification') }} - {{ AssetView::countAssetByState(AssetItem::STATE_LOST_NOTIFICATION) }}</button>
                                <button class="btn btn-default btn-get-asset-information btn-broken-notification" value="{{ AssetConst::MODAL_BROKEN_NOTIFICATION }}" >{{ trans('asset::view.Broken notification') }}  - {{ AssetView::countAssetByState(AssetItem::STATE_BROKEN_NOTIFICATION) }}</button>
                                <button class="btn btn-default btn-get-asset-information btn-suggest-liquidate" value="{{ AssetConst::MODAL_SUGGEST_LIQUIDATE }}" >{{ trans('asset::view.Suggest liquidate') }}  - {{ AssetView::countAssetByState(AssetItem::STATE_SUGGEST_LIQUIDATE) }}</button>
                                <button class="btn btn-default btn-get-asset-information btn-suggest-repair-maintenance" value="{{ AssetConst::MODAL_SUGGEST_REPAIR_MAINTENACE }}" >{{ trans('asset::view.Suggest repair, maintenance') }}   - {{ AssetView::countAssetByState(AssetItem::STATE_SUGGEST_REPAIR_MAINTENANCE) }}</button>
                                <button class="btn btn-default btn-get-asset-information btn-return-customer" value="{{ AssetConst::MODAL_RETURN_CUSTOMER }}" >{{ trans('asset::view.Return customer') }}</button>
                                <button class="btn btn-default btn-import-configure" data-toggle="modal" data-target="#importFileConfigure" >Import cấu  hình máy</button>
                                <input type="hidden" name="" id="asset_item_id">
                                <button class="btn btn-default btn-import-serial-number" data-toggle="modal" data-target="#importSerialNumber" >Import serial</button>
                                @endif
                        </div>
                        <div class="col-md-5">
                            <div class="pull-right">
                                @include('asset::item.include.filter')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table dataTable table-bordered table-hover table-grid-data" id="table_asset">
                            <thead>
                                <tr>
                                    <th class="width-20"><input type="checkbox" class="check-all"></th>
                                    <th class="width-10 ">{{ trans('core::view.NO.') }}</th>
                                    <th class="width-50">{{ trans('asset::view.Asset code') }}</th>
                                    <th class="width-100">{{ trans('asset::view.Asset name') }}</th>
                                    <th class="width-90">{{ trans('asset::view.Asset category') }}</th>
                                    <th class="width-110">{{ trans('asset::view.Address warehouse') }}</th>
                                    <th class="width-80">{{ trans('asset::view.Asset manager account') }}</th>
                                    <th class="width-80">{{ trans('asset::view.Email') }}</th>
                                    <th class="width-150">{{ trans('asset::view.Position') }}</th>
                                    <th class="width-70">{{ trans('asset::view.Received date') }}</th>
                                    <th class="width-120">{{ trans('asset::view.State') }}</th>
                                    <th class="width-120">{{ trans('asset::view.Approver') }}</th>
                                    <th class="width-70">{{ trans('asset::view.Had allocation') }}</th>
                                    @if ($showColumnAction)
                                        <th class="width-85"></th>
                                    @endif
                                </tr>
                            </thead>
                        </table>

                        <table class="hidden" id="tbl_asset_2">
                            <thead>
                                <tr class="row-filter">
                                    <input type="hidden" id="id_asset_profile" value="{{ request()->get('ids') ? implode(",",request()->get('ids')) : '' }}">
                                    <th></th>
                                    <th>
                                        <input type="text" class="form-control filter-configure_additional hidden"/>
                                        <input type="text" class="form-control filter-serial_additional hidden"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control filter-asset_code" placeholder="code asset"/>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control filter-asset_name" />
                                    </th>
                                    <th>
                                        <select class="form-control filter-category_name select-search has-search"
                                                style="width: 140px;">
                                            <option>&nbsp;</option>
                                            @if (count($assetCategoriesList))
                                                @foreach($assetCategoriesList as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            @endif

                                        </select>
                                    </th>
                                    <th>
                                        <select class="form-control filter-warehouse_name select-search has-search" style="width: 100%">
                                            <option>&nbsp;</option>
                                            @if (count($warehouseList))
                                                @foreach($warehouseList as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control filter-manager_name" />
                                    </th>
                                    <th>
                                        <input type="text" class="form-control filter-user_name"/>
                                    </th>
                                    <th></th>
                                    <th></th>
                                    <th>
                                        <select class="form-control filter-state select-search">
                                            <option>&nbsp;</option>
                                            @if (count($labelStates))
                                                @foreach($labelStates as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>
                                    <th></th>
                                    <th>
                                        <select class="form-control filter-allocation_confirm">
                                            <option>&nbsp;</option>
                                            @if (count($labelAllocationConfirm))
                                                @foreach($labelAllocationConfirm as $key => $value)
                                                    <option value="{{ $key }}" >{{ $value }}</option>
                                                @endforeach
                                            @endif

                                        </select>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal fade in" data-backdrop="static" id="modal_get_asset_to_approve">
                    <div class="modal-dialog modal-lg modal-asset">
                        <div class="modal-content" id="form_get_asset_to_approve">
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
                <div class="modal fade in" data-backdrop="true" id="modal_get_asset_information">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content" id="form_get_asset_information">
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            </div>
        </div>
        @include('asset::include.modal_import', ['route' => 'asset::asset.importFile'])
        <div id="modal_report" class="modal fade" role="dialog"></div>
        @include('asset::item.modal.modal_report_detail_on_asset_use_process')
        @include('asset::item.modal.modal_report_lost_and_broken')
        @include('asset::item.modal.modal_report_asset_detail_by_employee')
        @include('asset::item.modal.modal_asset_export')
        @include('asset::item.modal.modal_import_configure')
        @include('asset::item.modal.modal_import_serial_number')
    </div>
@endsection

@section('script')
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.22/datatables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.9/js/dataTables.checkboxes.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.script.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.report.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.shorten.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/common_asset.js') }}"></script>
    <script src="{{ asset('assets/help/report/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/help/report/vfs_fonts.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/asset/report_pdf.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/asset/report_process.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/asset/index.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/asset/report_by_asset_lost.js') }}"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/xlsx.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/asset/xlsx-func.js') }}"></script>
    <script>
        $(function () {
            $('.list_export_cols').sortable({
                stop: function (event, ui) {
                    $('.list_export_cols li input').each(function (index) {
                        $(this).attr('name', 'columns['+ index +']');
                    });
                },
            });
        });
    </script>
    <script type="text/javascript">
        <?php
            $teamDefault = $teamsOptionAll[0]['value'];
        ?>
        var urlAjaxGetAssetInformation = '{{ route('asset::asset.ajax-get-asset-information') }}';
        var urlAjaxGetAssetToApprove= '{{ route('asset::asset.ajax-get-asset-to-approve') }}';
        var urlAjaxGetEmployeeToReport= '{{ route('asset::asset.ajax-get-employee-to-report') }}';
        var urlAjaxGetAssetToReport= '{{ route('asset::asset.ajax-get-asset-to-report') }}';
        var urlAjaxGetModalReport= '{{ route('asset::asset.ajax-get-modal-report') }}';
        var urlAjaxGetRequestAsset = '{{ route('asset::resource.request.ajax-get-request-asset-to-allocation') }}';
        var STATE_NOT_USED = '{{ AssetItem::STATE_NOT_USED }}';
        var STATE_USING = '{{ AssetItem::STATE_USING }}';
        var STATE_BROKEN_NOTIFICATION = '{{ AssetItem::STATE_BROKEN_NOTIFICATION }}';
        var STATE_BROKEN = '{{ AssetItem::STATE_BROKEN }}';
        var STATE_SUGGEST_REPAIR_MAINTENANCE = '{{ AssetItem::STATE_SUGGEST_REPAIR_MAINTENANCE }}';
        var STATE_REPAIRED_MAINTAINED = '{{ AssetItem::STATE_REPAIRED_MAINTAINED }}';
        var STATE_LOST_NOTIFICATION = '{{ AssetItem::STATE_LOST_NOTIFICATION }}';
        var STATE_LOST = '{{ AssetItem::STATE_LOST }}';
        var STATE_SUGGEST_LIQUIDATE = '{{ AssetItem::STATE_SUGGEST_LIQUIDATE }}';
        var STATE_LIQUIDATE = '{{ AssetItem::STATE_LIQUIDATE }}';
        var STATE_SUGGEST_HANDOVER = '{{ AssetItem::STATE_SUGGEST_HANDOVER }}';
        var ALLOCATION_CONFIRM_NONE = '{{ AssetItem::ALLOCATION_CONFIRM_NONE }}';
        var teamDefault = '{{ $teamDefault }}';
        var urlAsset = '{{ route('asset::asset.getAsset') }}';
        var urlAssetProfile = '{{ route('asset::asset.getAssetProfile') }}';
        var urlReport = '{{ route('asset::asset.view-report') }}';
        var _token = '{{ csrf_token() }}';
        var requiredText = '{{ trans('asset::message.The field is required') }}';
        var invalidExFile = '{{ trans('asset::message.File not invalid') }}';
        initCheckbox();
        readMore();
        checkValidFile();
    </script>
@endsection