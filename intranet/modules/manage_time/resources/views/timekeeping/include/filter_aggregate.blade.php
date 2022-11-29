<?php
    $routeExport = isset($detail) && $detail ?
            route('manage_time::timekeeping.export-detail', ['id' => $timeKeepingTable->id])
            : route('manage_time::timekeeping.export-aggregate', ['id' => $timeKeepingTable->id]);
?>
<div class="filter-action">
    <a href="{{ route('manage_time::timekeeping.export-late-minutes', ['id' => $timeKeepingTable->id]) }}" class="btn btn-primary managetime-margin-bottom-5">
        <span>{{ trans('manage_time::view.Export late minutes') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </a>
    <a href="{{ $routeExport }}" class="btn btn-primary managetime-margin-bottom-5">
        <span>{{ trans('manage_time::view.Export') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </a>
    <button class="btn btn-primary btn-reset-filter managetime-margin-bottom-5">
        <span>{{ trans('manage_time::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
    <button class="btn btn-primary btn-search-filter managetime-margin-bottom-5">
        <span>{{ trans('manage_time::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
</div>