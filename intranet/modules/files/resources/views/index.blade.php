@extends('layouts.default')

<?php
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Assets\Model\AssetWarehouse;
use Rikkei\Assets\View\AssetConst;
use Rikkei\Team\View\Permission;

$tblAssetItem = AssetItem::getTableName();
$tblEmployeeAsUse = 'tbl_employee_use';
$labelStates = AssetItem::labelStates();
$labelAllocationConfirm = AssetItem::labelAllocationConfirm();
$listWarehouse = AssetWarehouse::listWarehouse();
$arrCheck = [AssetItem::STATE_LOST, AssetItem::STATE_LOST_NOTIFICATION, AssetItem::STATE_LIQUIDATE];
?>

@section('title')
    {{ trans('asset::view.Assets list is granted') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header with-border" id="profile_table">
                    <div class="pull-left">
                        <button type="button" class="btn btn-default btn-asset-profile" value="{{ AssetConst::TYPE_HANDING_OVER }}">{{ trans('asset::view.Handing over') }}</button>
                        <button type="button" class="btn btn-default btn-asset-profile" value="{{ AssetConst::TYPE_LOST_NOTIFY }}">{{ trans('asset::view.Lost notification') }}</button>
                        <button type="button" class="btn btn-default btn-asset-profile" value="{{ AssetConst::TYPE_BROKEN_NOTIFY }}">{{ trans('asset::view.Broken notification') }}</button>
                        @if ($inventory)
                        <?php
                        $textInvStt = trans('asset::view.Not done');
                        $doneInv = false;
                        if ($inventory->item_status != AssetConst::INV_RS_NOT_YET) {
                            $textInvStt = trans('asset::view.Done inventory');
                            $doneInv = true;
                        }
                        ?>
                        <button type="button" class="btn btn-info" id="btn_inventory" data-url="{{ route('asset::profile.personal_asset_ajax') }}"
                                data-toggle="tooltip" title="{{ $textInvStt }}">
                            @if ($doneInv)<i class="fa fa-check"></i> @endif {{ trans('asset::view.Click here to inventory') }} <i class="fa fa-spin fa-refresh hidden"></i>
                        </button>
                        @endif
                    </div>
                    <div class="pull-right">
                        <a href="{{ route('asset::resource.request.edit') }}" class="btn btn-success"><i class="fa fa-plus"></i> {{ trans('asset::view.Create request') }}</a>
                        <div class="form-inline">
                            @include('team::include.filter')
                        </div>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table_asset_profile">
                            <thead>
                                <tr>
                                    <th class="width-20">&nbsp;</th>
                                    <th class="width-25">{{ trans('core::view.NO.') }}</th>
                                    <th class="width-70 {{ Config::getDirClass('asset_code') }}" data-order="asset_code" data-dir="{{ Config::getDirOrder('asset_code') }}">{{ trans('asset::view.Asset code') }}</th>
                                    <th class="width-100 {{ Config::getDirClass('asset_name') }}" data-order="asset_name" data-dir="{{ Config::getDirOrder('asset_name') }}">{{ trans('asset::view.Asset name') }}</th>
                                    <th class="width-90 {{ Config::getDirClass('category_name') }}" data-order="category_name" data-dir="{{ Config::getDirOrder('category_name') }}">{{ trans('asset::view.Asset category') }}</th>
                                    <th class="width-120 {{ Config::getDirClass('state') }}" data-order="state" data-dir="{{ Config::getDirOrder('state') }}">{{ trans('asset::view.State') }}</th>
                                    <th class="width-70 {{ Config::getDirClass('received_date') }}" data-order="received_date" data-dir="{{ Config::getDirOrder('received_date') }}">{{ trans('asset::view.Received date') }}</th>
                                    <th class="width-160 {{ Config::getDirClass('note') }}" data-order="note" data-dir="{{ Config::getDirOrder('note') }}">{{ trans('asset::view.Note') }}</th>
                                    <th class="{{ Config::getDirClass('note_of_emp') }}" data-order="note" data-dir="{{ Config::getDirOrder('note_of_emp') }}">{{ trans('asset::view.Note of employee') }}</th>
                                    <th class="width-20 {{ Config::getDirClass('allocation_confirm') }}" data-order="allocation_confirm" data-dir="{{ Config::getDirOrder('allocation_confirm') }}">{{ trans('asset::view.Had allocation') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>

                        <table class="hidden" id="table_asset_profile_2">
                            <thead>
                                <tr class="row-filter">
                                    <th></th>
                                    <th></th>
                                    <th>
                                        <input type="hidden" id="employee_id" value="{{ Permission::getInstance()->getEmployee()->id }}">
                                        <input type="text" class="form-control filter-asset_code" />
                                    </th>
                                    <th>
                                        <input type="text" class="form-control filter-asset_name" id="test" />
                                    </th>
                                    <th>
                                        <select class="form-control filter-category_name">
                                            <option>&nbsp;</option>
                                            @if (count($assetCategoriesList))
                                                @foreach($assetCategoriesList as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            @endif

                                        </select>
                                    </th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
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
            </div>
        </div>
    </div>

    <!-- modal cofirm -->
    <div class="modal fade" id="modal_confirm" tabindex="-1" role="dialog" data-backdrop="true" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('asset::view.Confirm') }}</h4>
                </div>
                <form action="{{ route('asset::profile.confirm-allocation') }}" method="post" class="no-validate">
                    <div class="modal-body">
                        {!! csrf_field() !!}
                        <input type="hidden" name="id" id="asset_id_confirm" />
                        <p class="text-default">{{ trans('asset::view.Have you been given this asset?') }}</p>
                        <label class="radio-inline">
                            <input type="radio" name="confirm" value="{{ AssetItem::ALLOCATION_CONFIRM_TRUE }}" checked>{{ trans('asset::view.True') }}
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="confirm" value="{{ AssetItem::ALLOCATION_CONFIRM_FALSE }}">{{ trans('asset::view.False') }}
                        </label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                        <button type="submit" class="btn btn-primary pull-right">{{ trans('asset::view.Confirm') }}</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div> <!-- modal delete cofirm -->

    @if ($inventory)
    <!--modal inventory confirm-->
    <div class="modal fade modal-body-auto" id="modal_inventory" tabindex="-1" role="dialog" data-backdrop="true" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $inventory->name }}</h4>
                </div>
                <form action="{{ route('asset::profile.confirm_inventory') }}" method="post" id="profile_inventory_form">
                    {!! csrf_field() !!}
                    <div class="modal-body">
                        @if ($doneInv)
                        <p><i class="fa fa-check text-green"></i> {{ trans('asset::view.Done inventory at') . ': ' . $inventory->ivted_at }}</p>
                        @endif
                        <table class="table" id="confirm_asset_table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>{{ trans('asset::view.Asset code') }}</th>
                                    <th>{{ trans('asset::view.Asset name') }}</th>
                                    <th>{{ trans('asset::view.Allocated') }}?</th>
                                    <th>{{ trans('asset::view.Note') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="hidden" id="confirm_asset_item">
                                    <td class="col-no"></td>
                                    <td class="col-code"></td>
                                    <td class="col-name"></td>
                                    <td class="col-confirm">
                                        <input type="checkbox" name="asset_ids[]" value="">
                                    </td>
                                    <td class="col-note">
                                        <textarea class="text-resize-y form-control" rows="2" name="employee_notes[]" placeholder="{{ trans('asset::view.Broken or other reason') }}"></textarea>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr id="confirm_asset_none_item" class="hidden">
                                    <td colspan="5" class="text-center">{{ trans('asset::message.None item found') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                        <div id="confirm_asset_more" class="text-center hidden">
                            <a href="#"><i class="fa fa-spin fa-refresh hidden"></i> {{ trans('asset::view.Load more') }}</a>
                        </div>
                        <div>
                            <label>{{ trans('asset::view.Addtional') }} - <i>{{ trans('asset::view.Note assets you have but not list on there') }}</i></label>
                            <textarea name="extra_asset" class="form-control resize-vertical-only" rows="3" placeholder="(code1, name1), (code2, name2), ...">{{ $inventory->note }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="inventory_id" value="{{ $inventory->id }}">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                        <button type="submit" class="btn btn-primary pull-right">{{ trans('asset::view.Confirm') }}</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div> <!-- modal delete cofirm -->
    @endif
@endsection

@section('script')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.script.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/asset/profile.js') }}"></script>

    <script type="text/javascript">
        var urlAssetProfile = '{{ route('asset::asset.getAssetProfile') }}';
        var urlSaveNoteOfEmp = '{{ route('asset::asset.saveNoteOfEmp') }}';
        var urlConfirm = '{{ route('asset::profile.confirm-handover') }}';
        var _token = '{{ csrf_token() }}';
        var textConfirm = '{{ trans('asset::message.Are you sure confirm asset') }}';
        var validAsset = '{{ trans('asset::message.Please choose item asset') }}';
        var STATE_SUGGEST_HANDOVER = '{{ AssetItem::STATE_SUGGEST_HANDOVER }}';
        var ALLOCATION_CONFIRM_NONE = '{{ AssetItem::ALLOCATION_CONFIRM_NONE }}';
        var arrAssetCheck = <?php echo json_encode($arrCheck)?>;
        var confirmHandover = '{{ trans('asset::message.Are you sure want to handover asset?') }}';
        var confirmLost = '{{ trans('asset::message.Are you sure asset lost notification?') }}';
        var confirmBroken = '{{ trans('asset::message.Are you sure asset broken notification?') }}';
        var typeHanding = '{{ AssetConst::TYPE_HANDING_OVER }}';
        var typeLost = '{{ AssetConst::TYPE_LOST_NOTIFY  }}';
        var typeBroken = '{{ AssetConst::TYPE_BROKEN_NOTIFY }}';
    </script>
@endsection
