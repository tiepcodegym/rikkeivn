@extends('layouts.default')

<?php
    use Rikkei\Core\View\Form;
    use Rikkei\Core\View\View;
    use Rikkei\Team\View\Config;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Assets\Model\AssetAttribute;
    use Rikkei\Assets\Model\AssetCategory;
    use Rikkei\Assets\View\AssetPermission;

    $tblAssetAttribute= AssetAttribute::getTableName();
    $tblAssetCategory= AssetCategory::getTableName();
    $valueDefaultAssetCategory = '';
    if (count($assetCategoriesList)) {
        foreach ($assetCategoriesList as $item) {
            $valueDefaultAssetCategory = $item->id;
            break;
        }
    }
    $allowAddAndEdit = AssetPermission::createAndEditPermision();
    $allowDelete = AssetPermission::deletePermision();
    $allowViewDetail = AssetPermission::viewDetailPermision();
    
    $showColumnAction = ($allowAddAndEdit || $allowDelete || $allowViewDetail);
    $colColspan = $showColumnAction ? 5 : 4;
?>

@section('title')
    {{ trans('asset::view.Asset attributes list') }}
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
                                <button class="btn btn-success btn-reset-validate" id="btn_add_asset_attribute"><i class="fa fa-plus" aria-hidden="true"></i> {{ trans('asset::view.Add new') }}</button>
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
                                    <th class="width-90 sorting {{ Config::getDirClass('name') }}" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('asset::view.Asset attribute name') }}</th>
                                    <th class="width-90 sorting {{ Config::getDirClass('category_name') }}" data-order="category_name" data-dir="{{ Config::getDirOrder('category_name') }}">{{ trans('asset::view.Asset category') }}</th>
                                    <th class="width-140 sorting {{ Config::getDirClass('note') }}" data-order="note" data-dir="{{ Config::getDirOrder('note') }}">{{ trans('asset::view.Note') }}</th>
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
                                                <input type="text" name="filter[{{ $tblAssetAttribute }}.name]" value='{{ Form::getFilterData("{$tblAssetAttribute}.name") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <?php
                                            $filterCategory = Form::getFilterData('number', "{$tblAssetAttribute}.category_id");
                                        ?>
                                        <select name="filter[number][{{ $tblAssetAttribute }}.category_id]" class="form-control select-grid filter-grid select-search" style="width: 100%;" autocomplete="off">
                                            <option>&nbsp;</option>
                                            @if (count($assetCategoriesList))
                                                @foreach($assetCategoriesList as $item)
                                                    <option value="{{ $item->id }}" <?php if ($filterCategory !== null && $item->id == $filterCategory): ?> selected<?php endif; ?>>{{ $item->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblAssetAttribute }}.note]" value='{{ Form::getFilterData("{$tblAssetAttribute}.note") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
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
                                        <tr asset-attribute-id="{{ $item->id }}" asset-attribute-name="{{ $item->name }}" asset-category-id="{{ $item->category_id }}" asset-attribute-note="{{ $item->note }}">
                                            <td>{{ $i }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->category_name }}</td>
                                            <td class="read-more">{!! View::nl2br($item->note) !!}</td>
                                            @if ($showColumnAction)
                                                <td>
                                                    @if ($allowViewDetail || $allowAddAndEdit)
                                                        <button class="btn btn-success btn-reset-validate btn-edit-asset-attribute" title="{{ trans('asset::view.View detail') }}" value="{{ $item->id }}">
                                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                        </button>
                                                    @endif
                                                    @if ($allowDelete)
                                                        <form action="{{ route('asset::asset.attribute.delete') }}" method="post" class="form-inline">
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
        <!-- Modal add/edit asset attribute -->
        @include('asset::attribute.include.modal_add_asset_attribute')
    </div>
@endsection

@section('script')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.shorten.js') }}"></script>
    <script type="text/javascript">
        var urlCheckExistAttributeName = '{{ route('asset::asset.attribute.check-exist-attribute-name') }}';
        var requiredText = '{{ trans('asset::message.The field is required') }}';
        var rangelengthText = '{{ trans('asset::message.The field not be greater than :number characters', ['number' => 100]) }}';
        var uniqueAssetAttributeName = '{{ trans('asset::message.Asset attribute name has exist') }}';
        var valueDefaultAssetCategory = '{{ $valueDefaultAssetCategory }}';
        var titleAddAttribute = '{{ trans('asset::view.Add asset attribute') }}';
        var titleInfoAttribute = '{{ trans('asset::view.Info asset attribute') }}';

        // Call function init select2
        selectSearchReload();
        $('.select-search-multiple').select2();

        $(".read-more").shorten({
            "showChars" : 200,
            "moreText"  : "See more",
            "lessText"  : "Less",
        });

        var validator = $('#form_add_asset_attribute').validate({
            rules: {
                'item[name]': {
                    required: true,
                    rangelength: [1, 100],
                },
                'item[category_id]': {
                    required: true,
                },
            },
            messages: {
                'item[name]': {
                    required: requiredText,
                    rangelength: rangelengthText,
                },
                'item[category_id]': {
                    required: requiredText,
                },
            },
        });
        $('.btn-reset-validate').click(function() {
            validator.resetForm();
            $('#form_add_asset_attribute').find('.error').removeClass('error');
        });
        $('#btn_add_asset_attribute').click(function() {
            $('#asset_attribute_id').val('');
            $('#asset_attribute_name').val('');
            $('#asset_attribute_note').val('');
            $('#asset_category_id').val(valueDefaultAssetCategory).trigger('change');
            $('#modal_add_asset_attribute .modal-title').html(titleAddAttribute);
            $('#modal_add_asset_attribute').modal('show');
        });
        $('.btn-edit-asset-attribute').click(function() {
            var data = $(this).closest('tr');
            $('#asset_attribute_id').val(data.attr('asset-attribute-id'));
            $('#asset_attribute_name').val(data.attr('asset-attribute-name'));
            $('#asset_attribute_note').val(data.attr('asset-attribute-note'));
            $('#asset_category_id').val(data.attr('asset-category-id')).trigger('change');
            $('#modal_add_asset_attribute .modal-title').html(titleInfoAttribute);
            $('#modal_add_asset_attribute').modal('show');
        });
        $('#asset_attribute_name').keyup(function() {
            $('.btn-submit').attr('disabled', false);
        });
    </script>
@endsection
