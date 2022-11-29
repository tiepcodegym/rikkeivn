<?php
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Assets\View\AssetConst;

$assetActionList = AssetConst::assetActionsList();
$statuses = AssetConst::listStatusReport();
?>

@extends('layouts.default')

@section('title', trans('asset::view.List hand over, lost, broken asset report'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
@stop

@section('content')

<div class="box box-primary">
    <div class="box-body">
        @include('team::include.filter', ['domainTrans' => 'asset'])
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover table-striped dataTable table-bordered">
            <thead>
                <tr>
                    <th>{{ trans('core::view.NO.') }}</th>
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('creator_email') }} col-name" data-order="creator_email" data-dir="{{ Config::getDirOrder('creator_email') }}">{{ trans('asset::view.Creator name') }}</th>
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('team_names') }} col-name" data-order="team_names" data-dir="{{ Config::getDirOrder('team_names') }}">{{ trans('asset::view.Team') }}</th>
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('type') }} col-name" data-order="type" data-dir="{{ Config::getDirOrder('type') }}">{{ trans('asset::view.Report type') }}</th>
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('status') }} col-name " data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('asset::view.Status') }}
                        <div class="tooltip2"><i class="fa fa-question-circle" aria-hidden="true"></i>
                          <span class="tooltiptext">
                                {!! trans('asset::view.report_tooltip_status') !!}
                          </span>
                        </div>
                    </th>
                    <th class="sorting white-space-nowrap {{ Config::getDirClass('created_time') }} col-name" data-order="created_time" data-dir="{{ Config::getDirOrder('created_time') }}">{{ trans('asset::view.Created time') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>
                        <input type="text" name="filter[creator.email]" class="form-control filter-grid" 
                               placeholder="{{ trans('asset::view.Search') }}..." value="{{ CoreForm::getFilterData('creator.email') }}">
                    </td>
                    <td>
                        <select name="filter[excerpt][team_id]" class="form-control select-grid filter-grid select-search"
                                style="min-width: 230px;">
                            <option value="">&nbsp;</option>
                            @foreach ($teamList as $option)
                            <option value="{{ $option['value'] }}" {{ $option['value'] == CoreForm::getFilterData('excerpt', 'team_id') ? 'selected' : '' }}>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="filter[number][report.type]" class="form-control select-grid filter-grid select-search"
                                style="width: 140px;">
                            <option value="">&nbsp;</option>
                            @foreach ($assetActionList as $value => $label)
                            <option value="{{ $value }}" {{ $value == CoreForm::getFilterData('number', 'report.type') ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="filter[number][report.status]" class="form-control select-grid filter-grid select-search"
                                style="width: 110px;">
                            <option value="">&nbsp;</option>
                            @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" {{ $value == CoreForm::getFilterData('number', 'report.status') ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" name="filter[report.created_at]" class="form-control filter-grid" 
                               placeholder="{{ trans('asset::view.Search') }}..." value="{{ CoreForm::getFilterData('report.created_at') }}">
                    </td>
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
                        <td>{{ CoreView::getNickName($item->creator_email) }}</td>
                        <td>{{ $item->team_names }}</td>
                        <td>{{ $item->getAttrLabel('type', $assetActionList) }}</td>
                        <td>{!! $item->renderStatusHtml($statuses, 'label') !!}</td>
                        <td>{{ $item->created_at }}</td>
                        <td class="white-space-nowrap">
                            <a href="{{ route('asset::report.detail', ['id' => $item->id]) }}" class="btn btn-primary"
                                    title="{{ trans('asset::view.View detail') }}">
                                <i class="fa fa-eye"></i>
                            </a>
                            {!! Form::open([
                                'method' => 'delete',
                                'route' => ['asset::report.delete', $item->id],
                                'class' => 'form-inline'
                            ]) !!}
                            <button type="submit" class="btn btn-danger btn-delete-item" title="{{ trans('asset::view.Delete') }}"
                                    data-noti="{{ trans('asset::message.Are you sure want to delete?') }}">
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
    </div>
    
    <div class="box-body">
        @include('team::include.pager')
    </div>
</div>

@stop

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
    (function ($) {
        RKfuncion.select2.init();
        $('.btn-delete-item').click(function () {
            var btn = $(this);
            bootbox.confirm({
                className: 'modal-danger',
                message: btn.data('noti'),
                callback: function (result) {
                    if (result) {
                        btn.closest('form')[0].submit();
                    }
                },
            });
            return false;
        });
    })(jQuery);
</script>
@stop

