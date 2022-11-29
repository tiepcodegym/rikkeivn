<?php
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Project\View\MeView;
use Rikkei\Team\View\Config;

$tblEmp = 'employees';
?>

@extends('layouts.default')

@section('title', trans('project::me.Monthly Evaluation Member Activities'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<style>
    .tooltip-inner {
        min-width: 350px;
    }
</style>
@endsection

@section('content')

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-5">
                <form class="form-inline no-validate" method="get" action="{{ request()->url() }}" id="form_month_filter">
                    <div class="form-group">
                        <strong>{{ trans('project::me.Month') }}: </strong>&nbsp;&nbsp;&nbsp;
                        <input type="text" id="activity_month" name="month" class="form-control form-inline month-picker maxw-230" value="{{ $month }}" autocomplete="off">
                    </div>
                </form>
            </div>
            <div class="col-md-7">
                @include('team::include.filter', ['domainTrans' => 'project'])
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped dataTable table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('project::me.Member') }}</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('email') }} col-name" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('project::me.Account') }}</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('proj_names') }} col-name" data-order="proj_names" data-dir="{{ Config::getDirOrder('proj_names') }}">{{ trans('project::me.Project') }}</th>
                        @foreach ($activityFields as $field)
                        <th class="minw-350">{{ $field->label }}</th>
                        @endforeach
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input type="text" name="filter[{{ $tblEmp }}.name]" class="form-control filter-grid"
                                   placeholder="{{ trans('project::me.Search') }}..." value="{{ CoreForm::getFilterData($tblEmp.'.name') }}">
                        </td>
                        <td>
                            <input type="text" name="filter[{{ $tblEmp }}.email]" class="form-control filter-grid"
                                   placeholder="{{ trans('project::me.Search') }}..." value="{{ CoreForm::getFilterData($tblEmp.'.email') }}">
                        </td>
                        <td>
                            <input type="text" name="filter[proj.name]" class="form-control filter-grid"
                                   placeholder="{{ trans('project::me.Search') }}..." value="{{ CoreForm::getFilterData('proj.name') }}">
                        </td>
                        @foreach ($activityFields as $field)
                        <td></td>
                        @endforeach
                        <td></td>
                    </tr>
                    @if (!$collectionModel->isEmpty())
                        <?php
                        $currentPage = $collectionModel->currentPage();
                        $perPage = $collectionModel->perPage();
                        ?>
                        @foreach ($collectionModel as $order => $item)
                        <?php
                        $activities = $item->meActivities;
                        if ($activities) {
                            $activities = $activities->groupBy('attr_id');
                        } else {
                            $activities = [];
                        }
                        ?>
                        <tr>
                            <td>{{ $order + 1 + ($currentPage - 1) * $perPage }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ CoreView::getNickName($item->email) }}</td>
                            <td>{!! MeView::renderListProjects($item->proj_names) !!}</td>
                            @foreach ($activityFields as $field)
                            <td>
                                <div class="ws-pre-line el-shorten">{{ isset($activities[$field->id]) ? $activities[$field->id]->first()->content : null }}</div>
                            </td>
                            @endforeach
                            <td></td>
                        </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="{{ 5 + $activityFields->count() }}"><h4 class="text-center">{{ trans('project::me.Not found items') }}</h4></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-body">
        @include('team::include.pager')
    </div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script>
    $('.month-picker').datepicker({
        format: 'yyyy-mm',
        viewMode: "months", 
        minViewMode: "months",
        autoclose: true
    }).on('changeDate', function (e) {
        $('#form_month_filter').submit();
    });

    $('.el-shorten').shortedContent({
        showChars: 120,
    });
</script>
@endsection


