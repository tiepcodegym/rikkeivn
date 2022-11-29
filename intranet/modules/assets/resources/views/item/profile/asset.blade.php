@extends('layouts.default')

<?php
use Rikkei\Assets\Model\AssetWarehouse;
use Rikkei\Core\View\CoreUrl;
use Carbon\Carbon;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Assets\View\AssetConst;

$warehouseList = AssetWarehouse::getGridData();
$labelStates = AssetItem::labelStates();
$arrCheck = [AssetItem::STATE_LOST, AssetItem::STATE_LIQUIDATE, AssetItem::STATE_BROKEN, AssetItem::STATE_NOT_USED];
?>

@section('title', isset($pageTitle) ? $pageTitle : trans('asset::view.Assets list'))

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.9/css/dataTables.checkboxes.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
@endsection

@section('content')
<form action="{{ route('asset::report.confirm', ['id' => isset($reportItem) ? $reportItem->id : 0]) }}" method="post" id="confirm-profile">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="box box-primary">

        <div class="box-header">
            <div class="row">
                <div class="col-sm-8">
                    <h3 class="box-title">
                        @if (isset($reportItem))
                        {{ trans('asset::view.Employee name: ') }}
                        <span>{{ $reportItem->getCreatorName() }}</span>
                        @endif
                    </h3>
                </div>
                <div class="col-sm-4 col-md-2 col-md-offset-2 text-right">
                    @if (isset($reportItem))
                    {!! $reportItem->renderStatusHtml(AssetConst::listStatusReport()) !!}
                    @endif
                </div>
            </div>
        </div>

        <div class="table-responsive" >
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data asset-profile-confirm">
                <thead>
                <tr class="info">
                    <th class="width-20 text-center checkbox-all">
                        @if(isset($assetItems) && count($assetItems))
                            <input type="checkbox" class="check-all-asset">
                        @endif
                    </th>
                    <th class="width-25">{{ trans('core::view.NO.') }}</th>
                    <th class="width-70">{{ trans('asset::view.Asset code') }}</th>
                    <th class="width-100">{{ trans('asset::view.Asset name') }}</th>
                    <th class="width-120">{{ trans('asset::view.Asset category') }}</th>
                    <th class="width-100">{{ trans('asset::view.State') }}</th>
                    <th class="width-90">{{ trans('asset::view.Notification date') }}</th>
                    <th class="width-100">{{ trans('asset::view.User using the assets') }}</th>
                    <th class="width-100">{{ trans('asset::view.Status using') }}</th>
                </tr>
                </thead>
                <tbody class="table-body">
                <input type="hidden" name="type" value="{{ (isset($type) && $type) ? $type : '' }}">
                <input type="hidden" name="employeeId" value="{{ (isset($employeeId) && $employeeId) ? $employeeId : '' }}">
                @if(isset($assetItems) && count($assetItems))
                    <?php $i = 1; ?>
                    @foreach($assetItems as $item)
                        <?php
                        $changeDate = '';
                        if ($item->change_date) {
                            $changeDate = Carbon::parse($item->change_date)->format('d-m-Y');
                        }
                        ?>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="checkSingle" name="item[]" value="{{ $item->id }}" {{ in_array($item->state, $arrCheck) ? 'disabled' : '' }}>
                            </td>
                            <td>{{ $i }}</td>
                            <td><a target="_blank" href="{{ route('asset::asset.view', ['id' => $item->id]) }}">{{ $item->code }}</a></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category_name }}</td>
                            <td>{{ $labelStates[$item->state] }}</td>
                            <td>{{ $changeDate }}</td>
                            <td>{{ $item->user_name }}</td>
                            <td>{{ $labelStates[$item->state_using] }}</td>
                        </tr>
                        <?php $i++; ?>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="text-center">
                            <h3 class="no-result-grid">{{ trans('asset::view.No results data or data already confirmed') }}</h3>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
        @if (isset($assetItems) && count($assetItems))
        <div class="box-footer text-center">
            <p></p>
            <input type="hidden" name="status" value="" id="item_status">
            @if (isset($reportItem) && $reportItem->status == AssetConst::STT_RP_PROCESSING)
            <button type="button" class="btn btn-danger btn-status" data-status="{{ AssetConst::STT_RP_REJECTED }}"
                    data-noti="{{ trans('asset::message.Are you sure want to reject?') }}">{{ trans('asset::view.Confirm reject') }}</button>
            <button type="button" class="btn btn-primary btn-status" data-status="{{ AssetConst::STT_RP_CONFIRMED }}"
                    data-noti="{{ trans('asset::message.Are you sure want to confirm?') }}">{{ trans('asset::view.Confirm') }}</button>
            @endif
            <p></p>
        </div>
        @endif
    </div>

    <div id="itConfirm" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('asset::message.Are you sure want to') . trans('asset::view.Confirm') }}</h4>
                </div>
                @if (isset($type) && $type == AssetConst::TYPE_HANDING_OVER)
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label required">{{ trans('asset::view.Warehouse') }} <em>*</em></label>
                            <div class="input-box">
                                <select name="warehouse_id" class="form-control" style="width: 100%" id="warehouse_id">
                                    <option>&nbsp;</option>
                                    @if (count($warehouseList))
                                        @foreach($warehouseList as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <label class="asset-error" id="warehouse-error">{{ trans('asset::message.The field is required') }}</label>
                        </div>
                    </div>
                @endif
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@section('script')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.9/js/dataTables.checkboxes.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script>
        var requiredText = '{{ trans('asset::message.The field is required') }}';
        $('.check-all-asset').prop('checked', true);
        $('.asset-profile-confirm tbody input[type=checkbox]').prop('checked', true);
        var selectItemText = '{{ trans("asset::message.Please select assets") }}';
        $('body').on('change', '.check-all-asset', function () {
            $('.asset-profile-confirm tbody input[type=checkbox]').prop('checked', $(this).is(':checked'));
        });

        $(document).on('click', '.btn-status', function () {
            if ($('.asset-profile-confirm input[type=checkbox]:checked').length === 0) {
                bootbox.alert({
                    message: selectItemText,
                    className: 'modal-warning',
                });
                return false;
            }
            $('#item_status').val($(this).data('status'));
            var itModal = $('#itConfirm');
            itModal.find('.modal-title strong').text($(this).text());
            if ($(this).data('status') == 3) {
                itModal.find('.modal-body').addClass('hidden');
            } else {
                itModal.find('.modal-body').removeClass('hidden');
            }
            itModal.modal('show');
        });

        $('#itConfirm').on('hidden.bs.modal', function () {
            $('#item_status').val('');
        });

        $(".checkSingle").click(function () {
            $('.check-all-asset').prop('checked', $('.checkSingle:checked').length === $('.checkSingle').length);
        });
        if ($('.asset-profile-confirm tbody input[type=checkbox]:enabled').length === 0) {
            $('.check-all-asset').attr('disabled', true);
        } else {
            $('.check-all-asset').removeAttr('disabled');
        }
        $('#confirm-profile').validate({
            rules: {
                'warehouse_id': {
                    required: true,
                },
            },
            message: {
                'warehouse_id': {
                    required: requiredText,
                },
            },
        });

    </script>
@endsection