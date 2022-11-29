@extends('layouts.default')
@section('title')
{{ trans('resource::view.Resource utilization') }}
@endsection

<?php

use Illuminate\Support\Facades\URL;
use Rikkei\Resource\Model\Dashboard;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\CoreUrl;

$date = date('Y-m-d');
$week = date('W');
?>

@section('content')
<div class="se-pre-con"></div>
<div class="row list-css-page resource-dashboard">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-body">
                @include('resource::dashboard.include.tab_utilization')
            </div>
        </div>
    </div>
    <!-- /.col -->
    <div class="modal modal-info large" id="modal-days">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('resource::view.Dashboard.Week detail') }}</h4>
                </div>
                <div class="modal-body">
                    
                        
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ trans('resource::view.Dashboard.Close') }}</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <div class="modal apply-click-modal"><img class="loading-img" src="{{ asset('sales/images/loading.gif') }}" /></div>
</div>
<!-- /.row -->
<div class=" bottom-height"></div>
@endsection
<!-- Styles -->
@section('css')
<meta name="_token" content="{{ csrf_token() }}"/>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ CoreUrl::asset('common/css/style.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ CoreUrl::asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('resource/css/resource.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">    
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
@endsection

<!-- Script -->
@section('script')
    <script>
        strLoading = '<tr class="loading"><td colspan="{{Dashboard::MONTH_DIFF_DEFAULT + 1}}"><img class="loading-img" src="' + baseUrl + 'sales/images/loading.gif" /></td></tr>';
        urlDetail = '{{route("resource::dashboard.viewWeekDetail")}}';
        urlFilter = '{{route("resource::dashboard.ajax")}}';
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="{{ CoreUrl::asset('resource/js/dashboard/utilization.js') }}"></script>
    <script>
        var bgWhite = '{{Dashboard::BG_EFFORT_WHITE}}';
        var bgYellow = '{{Dashboard::BG_EFFORT_YELLOW}}';
        var bgGreen = '{{Dashboard::BG_EFFORT_GREEN}}';
        var bgRed = '{{Dashboard::BG_EFFORT_RED}}';
        var effortGreenMin = {{ Dashboard::EFFORT_GREEN_MIN }};
        var effortGreenMax = {{ Dashboard::EFFORT_GREEN_MAX }};
        var urlSearchEmp = '{{route("resource::utilization.emp.search.ajax")}}';
        $('#team_id').multiselect({
            numberDisplayed: 2,
            nonSelectedText: '{{ trans('resource::view.Dashboard.Choose group') }}',
            allSelectedText: '{{ trans('project::view.All') }}',
            buttonWidth: '100%',
            includeSelectAllOption: true,
        });

        $(document).ready(function() {
            $('#btn-export-utilization').click(function () {
                $('#export-utilization').submit();
            });
        });
    </script>
@endsection
