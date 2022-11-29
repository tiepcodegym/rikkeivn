 <div class="filter-action">
     @if (isset($collectionModel) && count($collectionModel) && (!isset($showButton) || $showButton))
        <button class="btn btn-success approve-submit managetime-margin-bottom-5" disabled>
            <span><i class="fa fa-check"></i> {{ trans('manage_time::view.Approve') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
        </button>
        <button class="btn btn-danger disapprove-submit managetime-margin-bottom-5" disabled>
            <span><i class="fa fa-minus-circle"></i> {{ trans('manage_time::view.Not approve') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
        </button>
    @endif
    <button class="btn btn-primary btn-reset-filter managetime-margin-bottom-5">
        <span>{{ trans('manage_time::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
    <button class="btn btn-primary btn-search-filter managetime-margin-bottom-5">
        <span>{{ trans('manage_time::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
</div>