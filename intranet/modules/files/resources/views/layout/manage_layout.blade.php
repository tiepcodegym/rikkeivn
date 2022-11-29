@extends('layouts.default')

@section('title')
    @yield('title-manage')
@endsection

<?php 
    use Rikkei\Core\View\CoreUrl;
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">

    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}" />

    @yield('css-manage')
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @yield('content-manage')
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
@endsection

@section('script')
    @yield('script-manage')

    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.shorten.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
@endsection
