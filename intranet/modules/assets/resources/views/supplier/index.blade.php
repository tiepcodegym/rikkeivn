@extends('layouts.default')

<?php
    use Rikkei\Core\View\Form;
    use Rikkei\Core\View\View;
    use Rikkei\Team\View\Config;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Assets\Model\AssetSupplier;
    use Rikkei\Assets\View\AssetPermission;

    $tblAssetSupplier = AssetSupplier::getTableName();
    $allowAddAndEdit = AssetPermission::createAndEditPermision();
    $allowDelete = AssetPermission::deletePermision();
    $allowViewDetail = AssetPermission::viewDetailPermision();
    
    $showColumnAction = ($allowAddAndEdit || $allowDelete || $allowViewDetail);
    $colColspan = $showColumnAction ? 8 : 7;
?>

@section('title')
    {{ trans('asset::view.Asset suppliers list') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <div class="row">
                        <div class="col-md-6">
                            @if ($allowAddAndEdit)
                                <button class="btn btn-success btn-reset-validate" id="btn_add_asset_supplier"><i class="fa fa-plus" aria-hidden="true"></i> {{ trans('asset::view.Add new') }}</button>
                                <button class="btn btn-primary import-file" data-toggle="modal" data-target="#importFile">{{ trans('asset::view.Import file') }}</button>
                            @endif
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
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                            <thead>
                                <tr>
                                    <th class="width-25">{{ trans('core::view.NO.') }}</th>
                                    <th class="width-100 sorting {{ Config::getDirClass('code') }}" data-order="code" data-dir="{{ Config::getDirOrder('code') }}">{{ trans('asset::view.Supplier code') }}</th>
                                    <th class="width-100 sorting {{ Config::getDirClass('name') }}" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('asset::view.Supplier name') }}</th>
                                    <th class="width-140 sorting {{ Config::getDirClass('address') }}" data-order="address" data-dir="{{ Config::getDirOrder('address') }}">{{ trans('asset::view.Address') }}</th>
                                    <th class="width-80 sorting {{ Config::getDirClass('phone') }}" data-order="phone" data-dir="{{ Config::getDirOrder('phone') }}">{{ trans('asset::view.Phone') }}</th>
                                    <th class="width-100 sorting {{ Config::getDirClass('email') }}" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('asset::view.Email') }}</th>
                                    <th class="width-90 sorting {{ Config::getDirClass('website') }}" data-order="website" data-dir="{{ Config::getDirOrder('website') }}">{{ trans('asset::view.Website') }}</th>
                                    @if ($showColumnAction)
                                        <th class="width-85"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblAssetSupplier }}.code]" value='{{ Form::getFilterData("{$tblAssetSupplier}.code") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblAssetSupplier }}.name]" value='{{ Form::getFilterData("{$tblAssetSupplier}.name") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblAssetSupplier }}.address]" value='{{ Form::getFilterData("{$tblAssetSupplier}.address") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblAssetSupplier }}.phone]" value='{{ Form::getFilterData("{$tblAssetSupplier}.phone") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblAssetSupplier }}.email]" value='{{ Form::getFilterData("{$tblAssetSupplier}.email") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblAssetSupplier }}.website]" value='{{ Form::getFilterData("{$tblAssetSupplier}.website") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    @if ($showColumnAction)
                                        <td>&nbsp;</td>
                                    @endif
                                </tr>

                                @if(isset($collectionModel) && count($collectionModel))
                                    <?php $i = View::getNoStartGrid($collectionModel); ?>
                                    @foreach($collectionModel as $item)
                                        <tr asset-supplier-id="{{ $item->id }}" asset-supplier-code="{{ $item->code }}" asset-supplier-name="{{ $item->name }}" asset-supplier-address="{{ $item->address }}" asset-supplier-phone="{{ $item->phone }}" asset-supplier-email="{{ $item->email }}" asset-supplier-website="{{ $item->website }}">
                                            <td>{{ $i }}</td>
                                            <td>{{ $item->code }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->address }}</td>
                                            <td>{{ $item->phone }}</td>
                                            <td>{{ $item->email }}</td>
                                            <td>{{ $item->website }}</td>
                                            @if ($showColumnAction)
                                                <td>
                                                    @if ($allowViewDetail || $allowAddAndEdit)
                                                        <button class="btn btn-success btn-reset-validate btn-edit-asset-supplier" title="{{ trans('asset::view.View detail') }}" value="{{ $item->id }}">
                                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                        </button>
                                                    @endif
                                                    @if ($allowDelete)
                                                        <form action="{{ route('asset::asset.supplier.delete') }}" method="post" class="form-inline">
                                                            {!! csrf_field() !!}
                                                            {!! method_field('delete') !!}
                                                            <input type="hidden" name="id" value="{{ $item->id }}" />
                                                            <button href="" class="btn-delete delete-confirm" title="{{ trans('asset::view.Delete') }}" disabled>
                                                                <span><i class="fa fa-trash"></i></span>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                        <?php $i++; ?>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ $colColspan }}" class="text-center">
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
            </div>
        </div>
        <!-- Modal add/edit asset supplier -->
        @include('asset::supplier.include.modal_add_asset_supplier')

        <!-- Modal import asset supplier from excel -->
        @include('asset::include.modal_import', ['route' => 'asset::asset.supplier.importFile'])
    </div>
@endsection

@section('script')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/supplier/index.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/common_asset.js') }}"></script>
    <script type="text/javascript">
        var requiredText = '<?php trans('asset::message.The field is required') ?>';
        var numberDigit = '<?php trans('asset::message.Please enter only digits') ?>';
        var rangelength20 = '<?php trans('asset::message.The field not be greater than :number characters', ['number' => 20]) ?>';
        var rangelength100 = '<?php trans('asset::message.The field not be greater than :number characters', ['number' => 100]) ?>';
        var rangelength255 = '<?php trans('asset::message.The field not be greater than :number characters', ['number' => 255]) ?>';
        var uniqueAssetSupplierCode = '<?php trans('asset::message.Supplier code has exist') ?>';
        var uniqueAssetSupplierName = '<?php trans('asset::message.Supplier name has exist') ?>';
        var invalidEmail = '<?php trans('asset::message.Please enter a valid email address') ?>';
        var titleAddSupplier = '<?php trans('asset::view.Add asset supplier') ?>';
        var titleInfoSupplier = '<?php trans('asset::view.Info asset supplier') ?>';
        var invalidExFile = '<?php trans('asset::message.File not invalid') ?>';
        var supplierCode = '{{ $supplierCode }}';
        var urlCheckExits = '{{ route('asset::asset.supplier.checkExist') }}';
        checkValidFile();
    </script>
@endsection