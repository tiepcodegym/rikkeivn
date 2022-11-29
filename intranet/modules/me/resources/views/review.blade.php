@extends('layouts.default')

@section('title', trans('project::me.Monthly Evaluation'))
@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<!--<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />-->
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
<style>
    table.dataTable thead .sorting_desc,
    table.dataTable thead .sorting_asc,
    table.dataTable thead .sorting {
        background-image: none;
    }
    .modalListComment label {
        margin-top: 15px; 
    }
</style>
@endsection

@section('content')

<div class="box box-info _me_review_page">
    <div id="me_review_container"></div>
</div>

@endsection

@section('script')
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="{{ asset('lib/fixed-table/tableHeadFixer.js') }}"></script>
@include('me::templates.script')
<script>
    pageParams = $.extend({}, pageParams, {
        urlOldMe: "{{ route('project::project.eval.list_by_leader') }}",
        isReviewPage: true,
    });
</script>
<script type="text/javascript" src="{{ CoreUrl::asset('me/js/me-review.js') }}"></script>
<script>
    (function ($) {
        $(document).ready(function () {
            $('#btn_init_review').click();
        });
    })(jQuery);
</script>
@endsection

