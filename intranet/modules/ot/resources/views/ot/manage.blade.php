@extends('layouts.default')

@if (!isset($reportOT))
    @section('title', trans('ot::view.Manage OT list'))
@else
    @section('title', trans('ot::view.Report register OT'))
@endif

<?php 
    use Rikkei\Core\View\CoreUrl;
    
    $urlShowPopup = route('ot::ot.view-popup');
?>

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_ot/css/list_ot.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}" />
<style>
    .box .table-responsive {
        padding-right: 17px;
    }
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @include('ot::include.manage_list')
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->    
    <div>
        @include('ot::include.modals.approve_confirm')
    </div>
    <div>
        @include('ot::include.modals.reject_confirm')
    </div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('asset_managetime/js/jquery.shorten.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_ot/js/otlist.js') }}"></script>
<script>
    const urlEditReg = "{{ route('ot::ot.editot') }}"; 
    const urlApproveRegister = "{{ route('ot::ot.approver.approve') }}";
    const urlRejectRegister = "{{ route('ot::ot.approver.reject') }}";
    var urlShowPopup = '{{ $urlShowPopup }}';
    var pageType = "{{ $pageType }}";
    var errList = [
        "{{ trans('ot::message.Required') }}",
    ];
    jQuery(document).ready(function ($) {
        initList();
        selectSearchReload();

        $('.filter-date').datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            weekStart: 1,
            todayHighlight: true
        });

        $('.filter-date').on('keyup', function(e) {
            e.stopPropagation();
            if (e.keyCode == 13) {
                $('.btn-search-filter').trigger('click');
            }
        });
        
        $('.input-select-team-member').on('change', function(event) {
            value = $(this).val();
            window.location.href = value;
        });
    });
</script>
@endsection
