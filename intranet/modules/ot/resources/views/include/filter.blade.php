<?php
    use Rikkei\Ot\Model\OtRegister;
?>
<div class="filter-action">
    @if (isset($collectionModel) && count($collectionModel) && $empType == OtRegister::APPROVER)
        <button class="btn btn-success approve-submit ot-margin-bottom-5 btn-approve" disabled>
            <span><i class="fa fa-check"></i> {{ trans('ot::view.Approve') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
        </button>
        <button class="btn btn-danger disapprove-submit ot-margin-bottom-5 btn-reject" disabled>
            <span><i class="fa fa-minus-circle"></i> {{ trans('ot::view.Not approve') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
        </button>
    @endif
    <button class="btn btn-primary btn-reset-filter ot-margin-bottom-5">
        <span>{{ trans('ot::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
    <button class="btn btn-primary btn-search-filter ot-margin-bottom-5">
        <span>{{ trans('ot::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
</div>