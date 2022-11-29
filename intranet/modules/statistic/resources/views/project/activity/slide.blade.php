<?php
use Rikkei\Core\View\CoreUrl;
?>
@extends('layouts.full-screen')
@section('title', 'Project production dashboard')

@section('css')
<link rel="stylesheet" href="{!!CoreUrl::asset('assets/statistic/css/statistic.css')!!}" />
@endsection

@section('content')
<form class="form-inline hidden" autocomplete="off"
    action="{!!route('statistic::project.activity.get.info')!!}"
    method="get" id="form-proj-activity-filter" d-dom-form="proj-activity-filter">
    <div class="form-group margin-left-10">
        <label for="from" class="required">{!!trans('statistic::view.From')!!} <em>*</em></label>
        <input name="from" class="form-control input-field"
            type="text" id="from" value="" data-date-picker d-filter-input>
    </div>
    <div class="form-group">
        <label for="to" class="required">{!!trans('statistic::view.To')!!} <em>*</em></label>
        <input name="to" class="form-control input-field" type="text"
            id="to" value="" data-date-picker d-filter-input>
    </div>
    <div class="form-group">
        <label for="unit" class="required">{!!trans('statistic::view.Unit')!!}</label>
        <select name="unit" class="form-control input-field" id="unit" d-dom-flag="unit">
            <option value="d" selected="">{!!trans('statistic::view.Day')!!}</option>
            <option value="m">{!!trans('statistic::view.Month')!!}</option>
            <option value="y">{!!trans('statistic::view.Year')!!}</option>
        </select>
    </div>
    <div class="form-group form-group-select2">
        <label for="employee">{!!trans('statistic::view.Employee')!!}</label>
        <div class="form-group" style="width: 250px">
            <select name="employee" class="form-control input-field"
                id="employee" d-filter-input
                data-select2-dom="1"
                data-select2-url="{!!route('team::employee.list.search.external.ajax')!!}">
                <option>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary" d-dom-btn="submit-filter">
        {!!trans('statistic::view.View')!!}
        <i class="fa fa-spin fa-refresh hidden loading-submit" d-dom-i="ajax-load"></i>
    </button>
</form>
<div class="swiper-container" d-dom-slider>
    <div class="swiper-wrapper sta-slide" d-slider-inner>
        @include('statistic::project.activity.chart_slide_item', ['typeChart' => 'loc'])
        @include('statistic::project.activity.chart_slide_item', ['typeChart' => 'bug'])
        @include('statistic::project.activity.chart_slide_item', ['typeChart' => 'buglea'])
        @include('statistic::project.activity.chart_slide_item', ['typeChart' => 'bugdefix'])
        @include('statistic::project.activity.chart_slide_item', ['typeChart' => 'buglefix'])
        @include('statistic::project.activity.tab.deliver_slide')
    </div>
</div>
@endsection

@section('script')
<script>
    var globVarPass = {
        urlGetInfo: '{!!route('statistic::project.activity.get.info')!!}/',
        team: {!!json_encode($teamPathTree)!!},
        teamFilter: '{!!isset($team) && $team ? $team : ''!!}',
        isSlide: 1,
    };
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
<script src="{!!CoreUrl::asset('lib/chartjs/utils.js')!!}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<script src="{!!CoreUrl::asset('assets/statistic/js/proj_activity.js')!!}"></script>
@endsection
