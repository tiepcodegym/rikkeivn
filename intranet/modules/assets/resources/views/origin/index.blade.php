@extends('layouts.default')

<?php
    use Rikkei\Core\View\Form;
    use Rikkei\Core\View\View;
    use Rikkei\Team\View\Config;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Assets\Model\AssetOrigin;
    use Rikkei\Assets\View\AssetPermission;

    $tblAssetOrigin = AssetOrigin::getTableName();
    $allowAddAndEdit = AssetPermission::createAndEditPermision();
    $allowDelete = AssetPermission::deletePermision();
    $allowViewDetail = AssetPermission::viewDetailPermision();
    
    $showColumnAction = ($allowAddAndEdit || $allowDelete || $allowViewDetail);
    $colColspan = $showColumnAction ? 4 : 3;
?>

@section('title')
    {{ trans('asset::view.Asset origins list') }}
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
                                <button class="btn btn-success btn-reset-validate" id="btn_add_asset_origin"><i class="fa fa-plus" aria-hidden="true"></i> {{ trans('asset::view.Add new') }}</button>
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
                                    <th class="width-100 sorting {{ Config::getDirClass('name') }}" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('asset::view.Asset origin name') }}</th>
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
                                                <input type="text" name="filter[{{ $tblAssetOrigin }}.name]" value='{{ Form::getFilterData("{$tblAssetOrigin}.name") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="filter[{{ $tblAssetOrigin }}.note]" value='{{ Form::getFilterData("{$tblAssetOrigin}.note") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
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
                                        <tr asset-origin-id="{{ $item->id }}" asset-origin-name="{{ $item->name }}" asset-origin-state="{{ $item->state }}" asset-origin-note="{{ $item->note }}">
                                            <td>{{ $i }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td class="read-more">{!! View::nl2br($item->note) !!}</td>
                                            @if ($showColumnAction)
                                                <td>
                                                    @if ($allowViewDetail || $allowAddAndEdit)
                                                        <button class="btn btn-success btn-reset-validate btn-edit-asset-origin" title="{{ trans('asset::view.View detail') }}" value="{{ $item->id }}">
                                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                        </button>
                                                    @endif
                                                    @if ($allowDelete)
                                                        <form action="{{ route('asset::asset.origin.delete') }}" method="post" class="form-inline">
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
        <!-- Modal add/edit asset origin -->
        @include('asset::origin.include.modal_add_asset_origin')
    </div>
@endsection

@section('script')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.shorten.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/origin/index.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/common_asset.js') }}"></script>
    <script type="text/javascript">
        var urlCheckExistOriginName = '{{ route('asset::asset.origin.check-exist-origin-name') }}';
        var requiredText = '<?php trans('asset::message.The field is required') ?>';
        var rangelengthText = '<?php trans('asset::message.The field not be greater than :number characters', ['number' => 100]) ?>';
        var uniqueAssetOriginName = '<?php trans('asset::message.Asset origin name has exist') ?>}';
        var titleAddOrigin = '<?php trans('asset::view.Add asset origin') ?>';
        var titleInfoOrigin = '<?php trans('asset::view.Info asset category') ?>';
        // Call function init select2
        selectSearchReload();
        readMore();
    </script>
@endsection