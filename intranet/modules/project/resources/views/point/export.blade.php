@extends('layouts.print')

@section('title')
<?php
use Rikkei\Core\View\View;
use Rikkei\Core\View\CookieCore;
use Rikkei\Project\View\View as ViewProject;

$allColorStatus = ViewProject::getPointColor();
?>
{{ trans('project::view.Project dashboard export') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css" />
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('project/css/print.css') }}" />
@endsection

@section('content')
<div class="row print-wrapper">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <h2 class="box-body-title">{{ trans('project::view.Overview') }}</h2>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                        <thead>
                            <tr>
                                <th class="col-id width-10" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                                <th class="col-name" style="width: 205px;">{{ trans('project::view.Project') }}</th>
                                <th>{{ trans('project::view.Summary') }}</th>
                                <th>{{ trans('project::view.Cost') }}</th>
                                <th>{{ trans('project::view.Quality') }}</th>
                                <th>{{ trans('project::view.Timeliness') }}</th>
                                <th>{{ trans('project::view.Process') }}</th>
                                <th>{{ trans('project::view.Css') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($projects) && count($projects))
                                <?php $i = 1; ?>
                                @foreach($projects as $item)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $item['project']->name }}</td>
                                        <td class="align-center">
                                            <span class="point-color summary-point">
                                                <img src="{{ $allColorStatus[$item['projectPointInformation']['summary']] }}" />
                                            </span>
                                        </td>
                                        <td class="align-center">
                                            <span class="point-color costt-point">
                                                <img src="{{ $allColorStatus[$item['projectPointInformation']['cost']] }}" />
                                            </span>
                                        </td>
                                        <td class="align-center">
                                            <span class="point-color quality-point">
                                                <img src="{{ $allColorStatus[$item['projectPointInformation']['quality']] }}" />
                                            </span>
                                        </td>
                                        <td class="align-center">
                                            <span class="point-color timeliness-point">
                                                <img src="{{ $allColorStatus[$item['projectPointInformation']['tl']] }}" />
                                            </span>
                                        </td>
                                        <td class="align-center">
                                            <span class="point-color process-point">
                                                <img src="{{ $allColorStatus[$item['projectPointInformation']['proc']] }}" />
                                            </span>
                                        </td>
                                        <td class="align-center">
                                            <span class="point-color css-point">
                                                <img src="{{ $allColorStatus[$item['projectPointInformation']['css']] }}" />
                                            </span>
                                        </td>
                                    </tr>
                                    <?php $i++; ?>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="page-print-break"></div>
        @if(isset($projects) && count($projects))
            <?php 
            $countProject = count($projects); 
            $i = 0;
            ?>
            @foreach($projects as $item)
                <?php
                    $project = $item['project'];
                    $projectPoint = $item['projectPoint'];
                    $projectPointInformation = $item['projectPointInformation'];
                    $i++;
                ?>
                <div class="box box-info">
                    <div class="box-body">
                        <h2 class="box-body-title">{{ trans('project::view.Project') }}: {{ $project->name }}</h2>
                        <div class="row">
                            <div class="col-md-12">
                                @include('project::point.tab.summary')
                                <h3>{{ trans('project::view.Cost') }}</h3>
                                @include('project::point.tab.cost')
                                <h3>{{ trans('project::view.Quality') }}</h3>
                                @include('project::point.tab.quality')
                                <h3>{{ trans('project::view.Timeliness') }}</h3>
                                @include('project::point.tab.timeliness')
                                <h3>{{ trans('project::view.Process') }}</h3>
                                @include('project::point.tab.process')
                                <h3>{{ trans('project::view.Css') }}</h3>
                                @include('project::point.tab.css')
                            </div>
                        </div>
                    </div>
                </div>
                @if ($i < $countProject)
                    <div class="page-print-break"></div>
                @endif
            @endforeach
        @endif
    </div>
</div>
@endsection

@section('script')
<script>
    window.print();
</script>
@endsection