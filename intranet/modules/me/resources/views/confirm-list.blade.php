<?php
use Rikkei\Core\View\CoreUrl;

$request = request();
?>

@extends('layouts.default')

@section('title', trans('me::view.Monthly Evaluation'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_team_style.css') }}" />
@endsection

@section('content')

<div class="box box-rikkei _me_create_page">
    <div id="me_confirm_list_container"></div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ asset('lib/fixed-table/tableHeadFixer.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
@include('me::templates.script')
<script>
    pageParams = $.extend({}, pageParams, {
        urlOldMe: "{{ route('project::project.profile.confirm') }}",
        urlUpdatestatus: "{{ route('me::profile.confirm.update_status') }}",
    });
</script>
<script type="text/javascript" src="{{ CoreUrl::asset('/me/js/me-confirm-list.js') }}"></script>
<script>
    (function ($) {
        $(document).ready(function () {
            $('#btn_init_confirm').click();
        });
    })(jQuery);
</script>
@endsection
