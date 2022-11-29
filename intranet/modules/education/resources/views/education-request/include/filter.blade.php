<?php
if (!isset($domainTrans) || !$domainTrans) {
    $domainTrans = 'team';
}
?>
<div class="filter-action">
    @if($isScopeHrOrCompany)
        <button id="export_list" class="btn btn-success" data-url="{{ URL::route('education::education.request.hr.export') }}">
            <i class="fa fa-download"></i>
            {{ trans('education::view.Export') }}
        </button>
        <a href="{{ route('education::education.request.hr.create') }}" class="btn btn-primary">{{trans('education::view.Create')}}</a>
    @else
        <a href="{{ route('education::education.request.create') }}" class="btn btn-primary">{{trans('education::view.Create')}}</a>
    @endif
    <button class="btn btn-primary btn-reset-filter">
        <span>{{ trans($domainTrans . '::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
    <button class="btn btn-primary btn-search-filter">
        <span>{{ trans($domainTrans . '::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
</div>
