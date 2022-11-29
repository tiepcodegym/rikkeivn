@extends('layouts.default')

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\Form;
    use Rikkei\Core\View\View;
    use Rikkei\Team\View\Config;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Assets\Model\AssetWarehouse;
    $tblWarehouse = AssetWarehouse::getTableName();
?>

@section('title')
    {{ trans('asset::view.Assets warehouse list') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.9/css/dataTables.checkboxes.css" />
    <style>
        .select2{
            width: 100% !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-success btn-reset-validate" id="btn_add_asset_warehouse"><i class="fa fa-plus" aria-hidden="true"></i> {{ trans('asset::view.Add new') }}</button>
                        </div>
                        <div class="col-md-6">
                            <div class="pull-right">
                                @include('team::include.filter', ['domainTrans' => 'asset'])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table_asset">
                            <thead>
                                <tr>
                                    <th class="width-10">{{ trans('core::view.NO.') }}</th>
                                    <th class="width-70 sorting {{ Config::getDirClass('code') }}" data-order="code" data-dir="{{ Config::getDirOrder('code') }}">{{ trans('asset::view.Asset code warehouse') }}</th>
                                    <th class="width-100 sorting {{ Config::getDirClass('name') }}" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('asset::view.Asset name warehouse') }}</th>
                                    <th class="width-110 sorting {{ Config::getDirClass('address') }}" data-order="address" data-dir="{{ Config::getDirOrder('address') }}">{{ trans('asset::view.Address warehouse') }}</th>
                                    <th class="width-85"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblWarehouse }}.code]" value='{{ Form::getFilterData("{$tblWarehouse}.code") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblWarehouse }}.name]" value='{{ Form::getFilterData("{$tblWarehouse}.name") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblWarehouse }}.address]" value='{{ Form::getFilterData("{$tblWarehouse}.address") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                                <?php $i = 0; ?>
                                @if(isset($collectionModel) && count($collectionModel))
                                    @foreach($collectionModel as $item)
                                        <?php $i ++; ?>
                                        <tr class="bt-item" data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-code="{{ $item->code }}" data-address="{{ $item->address }}"
                                            data-manager-id="{{ $item->manager_id }}" data-branch="{{ $item->branch }}">
                                            <td>{{ $i }}</td>
                                            <td>{{ $item->code }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->address }}</td>
                                            <td>
                                                <button class="btn btn-success btn-reset-validate btn-edit-warehouse" title="{{ trans('asset::view.View detail') }}" value="{{ $item->id }}">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                </button>
                                                <form action="{{ route('asset::asset.warehouse.delete') }}" class="form-inline" method="post">
                                                    {!! csrf_field() !!}
                                                    {!! method_field('delete') !!}
                                                    <input type="hidden" name="id" value="{{ $item->id }}" />
                                                    <button href="" class="btn-delete delete-confirm" title="{{ trans('asset::view.Delete') }}" disabled>
                                                        <span><i class="fa fa-trash"></i></span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <h2 class="no-result-grid">{{ trans('asset::view.No results data') }}</h2>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        <!-- /.table -->
                    </div>
                </div>
                <div class="box-footer">
                    @include('team::include.pager')
                </div>
                @include('asset::warehouse.include.modal_add_asset_warehouse')
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/warehouse/index.js') }}"></script>
    <script>
        var checkExist = '{{ route('asset::asset.warehouse.check-exist') }}';
        var requiredText = '{{ trans('asset::message.The field is required') }}';
        var uniqueName = '{{ trans('asset::message.Asset warehouse name has exist') }}';
        var uniqueCode = '{{ trans('asset::message.Asset warehouse code has exist') }}';
        var titleAdd = '{{ trans('asset::view.Add a new warehouse') }}';
        var titleEdit = '{{ trans('asset::view.Edit a warehouse') }}';
        var token = '{{ csrf_token() }}';

        $(function() {
            $('.select-search-employee').select2();
            $('#warehouse_branch').select2();
            $('body').on('change', '#warehouse_manager_id', function () {
                $('body').find('#warehouse_manager_id-error').remove();
            });
            $('body').on('change', '#warehouse_branch', function () {
                $('body').find('#warehouse_branch-error').remove();
            });
        });
    </script>
@endsection