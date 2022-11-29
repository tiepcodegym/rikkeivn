@extends('layouts.default')

@section('title')
    {{ trans('news::view.List post') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('asset_news/css/news.css') }}" />
@endsection

@section('content')
<?php
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\Model\Team;

$buttonAction['create'] = [
    'label' => 'Create post', 
    'class' => 'btn btn-primary',
    'disabled' => false, 
    'url'=> URL::route('news::manage.post.create'),
    'type' => 'link'
];
$listBranch = Team::listRegion();
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
                            <th>{{ trans('news::view.Image') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('title') }} col-title" data-order="title" data-dir="{{ TeamConfig::getDirOrder('title') }}">{{ trans('news::view.Title') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('slug') }} col-slug" data-order="slug" data-dir="{{ TeamConfig::getDirOrder('slug') }}">{{ trans('news::view.Slug') }}</th>
                            <th class="col-slug">{{ $listBranch[Team::TYPE_REGION_HN] }}</th>
                            <th class="col-slug">{{ $listBranch[Team::TYPE_REGION_DN] }}</th>
                            <th class="col-slug">{{ $listBranch[Team::TYPE_REGION_HCM] }}</th>
                            <th class="col-slug">{{ $listBranch[Team::TYPE_REGION_JP] }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('status') }} col-status" data-order="status" data-dir="{{ TeamConfig::getDirOrder('status') }}">{{ trans('news::view.Status') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('is_video') }} col-status" data-order="is_video" data-dir="{{ TeamConfig::getDirOrder('is_video') }}">{{ trans('project::view.Type') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
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
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select class="form-control select-grid filter-grid select-search" name="filter[number][status]">
                                            <option value="">&nbsp;</option>
                                            <?php $filterStatus = CoreForm::getFilterData('number','status');?>
                                            @foreach($optionStatus as $key => $value)
                                                <option value="{{ $key }}" {{ is_numeric($filterStatus) && (intval($filterStatus) === $key) ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select class="form-control select-grid filter-grid select-search" name="filter[number][is_video]">
                                            <option value="">&nbsp;</option>
                                            <?php $filterType = CoreForm::getFilterData('number','is_video');?>
                                            @foreach($optionType as $key => $value)
                                                <option value="{{ $key }}" {{ is_numeric($filterType) && (intval($filterType) === $key) ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $i }}</td>

                                        <td class="news-grid-image">
                                            <div class="news-manage-image">
                                                <img src="{{ $item->getThumbnail(true) }}" />
                                            </div>
                                        </td>

                                    <td>
                                        <a href="{{ route('news::manage.post.edit', ['id' => $item->id ]) }}">{{ $item->title }}</a>
                                    </td>
                                    <td>{{ $item->slug }}</td>
                                    <td>{{ isset($hanoiView[$item->id]) ? $hanoiView[$item->id] : null }}</td>
                                    <td>{{ isset($dnView[$item->id]) ? $dnView[$item->id] : null }}</td>
                                    <td>{{ isset($hcmView[$item->id]) ? $hcmView[$item->id] : null }}</td>
                                    <td>{{ isset($japanView[$item->id]) ? $japanView[$item->id] : null }}</td>
                                    <td>{{ $item->getLabelStatus($optionStatus) }}</td>
                                    <td>{{ $optionType[$item->is_video]}}</td>
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

