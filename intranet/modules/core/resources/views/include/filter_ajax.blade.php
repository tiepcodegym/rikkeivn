<?php
$dataTableFilter = isset($dataTableFilter) && $dataTableFilter ? $dataTableFilter : null;
?>
<div class="filter-action filter-action-ajax" data-table="{{ $dataTableFilter }}">
    <button class="btn btn-primary btn-reset-filter-ajax">
        <span>{{ trans('team::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
    <button class="btn btn-primary btn-search-filter-ajax">
        <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
</div>

