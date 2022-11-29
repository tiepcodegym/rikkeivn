@extends('layouts.default')

<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Assets\View\AssetConst;

$listStatus = AssetConst::listInventoryStatus();
unset($listStatus[AssetConst::INV_RS_EXCESS]);
?>

@section('title', trans('asset::view.Inventory detail'))

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
@endsection

@section('content')
<div class="box box-primary">
    <div class="box-header">
        <div class="row">
            <div class="col-md-6">
                <h4>{{ $inventory->name }}</h4>
            </div>
            <div class="col-md-6 text-right">
                <button type="button" class="btn btn-info btn-noti-inventory"
                        data-noti="{{ trans('asset::message.Alert will send email, are you sure?') }}"
                        data-url="{{ route('asset::inventory.alert', $inventory->id) }}">
                    {{ trans('asset::view.Alert inventory') }} 
                    <i class="fa fa-refresh fa-spin hidden"></i>
                </button>
                <button type="button" id="btn_inventory_export" class="btn btn-success"
                        data-url="{{ route('asset::inventory.export') }}" data-id="{{ $inventory->id }}"
                        data-mess-none="{{ trans('asset::view.No results data') }}">
                    {{ trans('asset::view.Export') }} <i class="fa fa-spin fa-refresh hidden"></i>
                </button>
                <div class="form-inline">
                    @include('team::include.filter', ['domainTrans' => 'asset'])
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->

    <div class="box-body no-padding">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th>{{ trans('core::view.NO.') }}</th>
                        <th class="sorting {{ Config::getDirClass('name') }} col-title" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('asset::view.Fullname') }}</th>
                        <th class="sorting {{ Config::getDirClass('email') }} col-title" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('asset::view.Email') }}</th>
                        <th class="sorting {{ Config::getDirClass('status') }} col-title" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('asset::view.Status') }}</th>
                        <th class="sorting {{ Config::getDirClass('note') }} col-title" data-order="note" data-dir="{{ Config::getDirOrder('note') }}">{{ trans('asset::view.Addtional') }}</th>
                        <th class="sorting {{ Config::getDirClass('team_names') }} col-title" data-order="team_names" data-dir="{{ Config::getDirOrder('team_names') }}">{{ trans('asset::view.Department') }}</th>
                        <th class="sorting {{ Config::getDirClass('updated_at') }} col-title" data-order="updated_at" data-dir="{{ Config::getDirOrder('updated_at') }}">{{ trans('asset::view.Created time') }}</th>
                        <th class="detail-col" data-url="{{ route('asset::inventory.personal_asset_ajax') }}"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input type="text" name="filter[emp.name]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                   value="{{ CoreForm::getFilterData('emp.name') }}">
                        </td>
                        <td>
                            <input type="text" name="filter[emp.email]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                   value="{{ CoreForm::getFilterData('emp.email') }}">
                        </td>
                        <td>
                            <select class="form-control select-grid filter-grid select-search" style="min-width: 100px;"
                                    name="filter[excerpt][status]">
                                <option value="">&nbsp;</option>
                                @if ($listStatus)
                                <?php
                                $filterItemStatus = CoreForm::getFilterData('excerpt', 'status');
                                ?>
                                @foreach ($listStatus as $value => $label)
                                <option value="{{ $value }}" {{ $filterItemStatus == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                                @endif
                            </select>
                        </td>
                        <td></td>
                        <td>
                            <select class="form-control select-grid filter-grid select-search" style="min-width: 120px;"
                                    name="filter[excerpt][team_id]">
                                <option value="">&nbsp;</option>
                                @if ($teamList)
                                    <?php $filterTeamId = CoreForm::getFilterData('excerpt', 'team_id'); ?>
                                    @foreach ($teamList as $option)
                                    <option value="{{ $option['value'] }}" {{ $filterTeamId == $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    @if (!$collectionModel->isEmpty())
                        <?php
                        $currentPage = $collectionModel->currentPage();
                        $perPage = $collectionModel->perPage();
                        ?>
                        @foreach ($collectionModel as $order => $item)
                        <tr>
                            <td>{{ $order + 1 + ($currentPage - 1) * $perPage }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->getStatusLabel($listStatus) }}</td>
                            <td>
                                <div class="pre-text el-short-content">{{ $item->note }}</div>
                            </td>
                            <td>{{ $item->team_names }}</td>
                            <td>{{ $item->updated_at }}</td>
                            <td class="white-space-nowrap">
                                <a href="#" class="btn btn-info btn-detail-inventory" data-employee="{{ $item->emp_id }}"
                                   title="{{ trans('asset::view.View detail') }}" data-inventory="{{ $inventory->id }}"><i class="fa fa-eye"></i></a>
                                {!! Form::open(['method' => 'delete', 'class' => 'form-inline', 'route' => ['asset::inventory.item_delete', $item->id]]) !!}
                                <button type="submit" class="btn btn-danger btn-delete-item" data-noti="{{ trans('asset::message.Are you sure want to delete?') }}"
                                        title="{{ trans('asset::view.Delete') }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="7"><h4 class="text-center">{{ trans('asset::message.None item found') }}</h4></td>
                    </tr>
                    @endif
                </tbody>
            </table>
            <!-- /.table -->
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->

    <div class="box-body">
        @include('team::include.pager')
    </div>
</div>
<!-- /. box -->

<!--modal inventory confirm-->
<div class="modal fade" id="modal_inventory" tabindex="-1" role="dialog" data-backdrop="true" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('asset::view.Inventory detail') }}</h4>
            </div>
            <div class="modal-body">
                <table class="table" id="confirm_asset_table">
                    <thead>
                        <tr>
                            <th>{{ trans('core::view.NO.') }}</th>
                            <th>{{ trans('asset::view.Asset code') }}</th>
                            <th>{{ trans('asset::view.Asset name') }}</th>
                            <th>{{ trans('asset::view.Allocated') }}</th>
                            <th>{{ trans('asset::view.Note') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hidden" id="confirm_asset_item">
                            <td class="col-no"></td>
                            <td class="col-code"></td>
                            <td class="col-name"></td>
                            <td class="col-confirm">
                                <input type="checkbox" name="asset_ids[]" value="" disabled>
                            </td>
                            <td class="col-note">
                                <textarea class="text-resize-y form-control" rows="2" name="employee_notes[]" disabled></textarea>
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/xlsx.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.script.js') }}"></script>
    <script>
        selectSearchReload();
    </script>
@endsection
