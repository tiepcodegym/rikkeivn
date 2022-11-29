@extends('layouts.default')

@section('title')
{{ $titleHeadPage }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('content')
<?php
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;

$buttonAction['create'] = [
    'label' => 'Create category', 
    'class' => 'btn btn-primary',
    'disabled' => false, 
    'url'=> URL::route('news::manage.category.create'),
    'type' => 'link'
];
?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                @include('team::include.filter', ['domainTrans' => 'news', 'buttons' => $buttonAction])
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id width-10" style="width: 20px;">{{ trans('news::view.No.') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('title') }} col-title" data-order="title" data-dir="{{ TeamConfig::getDirOrder('title') }}">{{ trans('news::view.Title') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('slug') }} col-slug" data-order="slug" data-dir="{{ TeamConfig::getDirOrder('slug') }}">{{ trans('news::view.Slug') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('status') }} col-status" data-order="status" data-dir="{{ TeamConfig::getDirOrder('status') }}">{{ trans('news::view.Status') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('sort_order') }} col-sort_order" data-order="sort_order" data-dir="{{ TeamConfig::getDirOrder('sort_order') }}">{{ trans('news::view.Order') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[title]" value="{{ CoreForm::getFilterData("title") }}" placeholder="{{ trans('news::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[slug]" value="{{ CoreForm::getFilterData("slug") }}" placeholder="{{ trans('news::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select class="form-control select-grid filter-grid select-search" name="filter[number][status]">
                                            <option value="">&nbsp;</option>
                                            @foreach($optionStatus as $key => $value)
                                                <option value="{{ $key }}" {{ CoreForm::getFilterData('number', 'status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>
                                        <a href="{{ route('news::manage.category.edit', ['id' => $item->id ]) }}">{{ $item->title }}</a>
                                    </td>
                                    <td>{{ $item->slug }}</td>
                                    <td>{{ $item->getLabelStatus($optionStatus) }}</td>
                                    <td>{{ $item->sort_order }}</td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('news::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="box-body">
                @include('team::include.pager')
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        selectSearchReload();
    });
</script>
@endsection

