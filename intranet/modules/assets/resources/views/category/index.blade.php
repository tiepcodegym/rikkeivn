@extends('layouts.default')

<?php
    use Rikkei\Core\View\Form;
    use Rikkei\Core\View\View;
    use Rikkei\Team\View\Config;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Assets\Model\AssetCategory;
    use Rikkei\Assets\Model\AssetGroup;
    use Rikkei\Assets\View\AssetPermission;

    $tblAssetCategory = AssetCategory::getTableName();
    $tblAssetGroup= AssetGroup::getTableName();
    $valueDefaultAssetGroup = '';
    if (count($assetGroupsList)) {
        foreach ($assetGroupsList as $item) {
            $valueDefaultAssetGroup = $item->id;
            break;
        }
    }
    $allowAddAndEdit = AssetPermission::createAndEditPermision();
    $allowDelete = AssetPermission::deletePermision();
    $allowViewDetail = AssetPermission::viewDetailPermision();
    
    $showColumnAction = ($allowAddAndEdit || $allowDelete || $allowViewDetail);
    $colColspan = $showColumnAction ? 7 : 6;
?>

@section('title')
    {{ trans('asset::view.Asset categories list') }}
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
                                <button class="btn btn-success btn-reset-validate" id="btn_add_asset_category"><i class="fa fa-plus" aria-hidden="true"></i> {{ trans('asset::view.Add new') }}</button>
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
                                    <th class="width-90 sorting {{ Config::getDirClass('name') }}" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('asset::view.Asset category name') }}</th>
                                    <th class="width-90 sorting {{ Config::getDirClass('group_name') }}" data-order="group_name" data-dir="{{ Config::getDirOrder('group_name') }}">{{ trans('asset::view.Asset group') }}</th>
                                    <th class="width-90 sorting {{ Config::getDirClass('prefix_asset_code') }}" data-order="prefix_asset_code" data-dir="{{ Config::getDirOrder('prefix_asset_code') }}">{{ trans('asset::view.Asset code prefix') }}</th>
                                    <th class="width-140 sorting {{ Config::getDirClass('note') }}" data-order="note" data-dir="{{ Config::getDirOrder('note') }}">{{ trans('asset::view.Note') }}</th>
                                    <th class="width-90 sorting {{ Config::getDirClass('is_default') }}" data-order="is_default" data-dir="{{ Config::getDirOrder('is_default') }}">{{ trans('asset::view.Default') }}</th>
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
                                                <input type="text" name="filter[{{ $tblAssetCategory }}.name]" value='{{ Form::getFilterData("{$tblAssetCategory}.name") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <?php
                                            $filterGroup = Form::getFilterData('number', "{$tblAssetCategory}.group_id");
                                        ?>
                                        <select name="filter[number][{{ $tblAssetCategory }}.group_id]" class="form-control select-grid filter-grid select-search" style="width: 100%;" autocomplete="off">
                                            <option>&nbsp;</option>
                                            @if (count($assetGroupsList))
                                                @foreach($assetGroupsList as $item)
                                                    <option value="{{ $item->id }}" <?php if ($filterGroup !== null && $item->id == $filterGroup): ?> selected<?php endif; ?>>{{ $item->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblAssetCategory }}.prefix_asset_code]" value='{{ Form::getFilterData("{$tblAssetCategory}.prefix_asset_code") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblAssetCategory }}.note]" value='{{ Form::getFilterData("{$tblAssetCategory}.note") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php $filterDefault = Form::getFilterData('excerpt', $tblAssetCategory . '.is_default'); ?>
                                        <select name="filter[excerpt][{{ $tblAssetCategory }}.is_default]" class="form-control select-grid filter-grid select-search" style="width: 100%">
                                            <option value="">&nbsp;</option>
                                            <option value="1" {{ is_numeric($filterDefault) && (int) $filterDefault === 1 ? 'selected' : '' }}>{{ trans('asset::view.Ans_Yes') }}</option>
                                            <option value="0" {{ is_numeric($filterDefault) && (int) $filterDefault === 0 ? 'selected' : '' }}>{{ trans('asset::view.Ans_No') }}</option>
                                        </select>
                                    </td>
                                    @if ($showColumnAction)
                                        <td>&nbsp;</td>
                                    @endif
                                </tr>

                                @if(isset($collectionModel) && count($collectionModel))
                                    <?php $i = View::getNoStartGrid($collectionModel); ?>
                                    @foreach($collectionModel as $item)
                                        <tr asset-category-id="{{ $item->id }}" asset-category-name="{{ $item->name }}" asset-group-id="{{ $item->group_id }}"
                                            asset-category-note="{{ $item->note }}" asset-code-prefix="{{ $item->prefix_asset_code }}" is-default="{{ $item->is_default }}">
                                            <td>{{ $i }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->group_name }}</td>
                                            <td>{{ $item->prefix_asset_code }}</td>
                                            <td class="read-more">{!! View::nl2br($item->note) !!}</td>
                                            <td>
                                                @if ($item->is_default)
                                                <i class="fa fa-check-square-o"></i>
                                                @endif
                                            </td>
                                            @if ($showColumnAction)
                                                <td>
                                                    @if ($allowViewDetail || $allowAddAndEdit)
                                                        <button class="btn btn-success btn-reset-validate btn-edit-asset-category" title="{{ trans('asset::view.View detail') }}" value="{{ $item->id }}">
                                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                        </button>
                                                    @endif
                                                    @if ($allowDelete)
                                                        <form action="{{ route('asset::asset.category.delete') }}" method="post" class="form-inline">
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
        <!-- Modal add/edit asset category -->
        @include('asset::category.include.modal_add_asset_category')

        @include('asset::include.modal_import', ['route' => 'asset::asset.category.importFile'])
    </div>
@endsection

@section('script')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.shorten.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/category/index.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/common_asset.js') }}"></script>
    <script type="text/javascript">
        var requiredText = "{{ trans('asset::message.The field is required') }}";
        var rangelengthText = "{{ trans('asset::message.The field not be greater than :number characters', ['number' => 100]) }}";
        var rangelengthText20 = "{{ trans('asset::message.The field not be greater than :number characters', ['number' => 20]) }}";
        var uniqueAssetCategoryName = "{{ trans('asset::message.Asset category name has exist') }}";
        var uniqueAssetCodePrefix = "{{ trans('asset::message.Asset code prefix has exist') }}";
        var invalidExFile = "{{ trans('asset::message.File not invalid') }}";
        var valueDefaultAssetGroup = '{{ $valueDefaultAssetGroup }}';
        var checkExitCate = "{{ route('asset::asset.category.checkExist') }}";
        var titleAddCate = "{{ trans('asset::view.Add asset category') }}";
        var titleInfoCate = "{{ trans('asset::view.Info asset category') }}";
        // Call function init select2
        selectSearchReload();
        readMore();
        checkValidFile();
    </script>
@endsection