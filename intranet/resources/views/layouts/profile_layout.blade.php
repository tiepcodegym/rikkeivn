@extends('layouts.default')
<?php 
use Rikkei\Core\View\Form;
?>
@section('title')
     @if (Form::getData('employee.id'))
        {{ trans('team::view.Profile of :employeeName', ['employeeName' => Form::getData('employee.name')]) }}
    @else
        {{ trans('team::view.Profile') }}
    @endif
@endsection

@section('css')  
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
    @yield('css-profile')
@endsection

@section('content')
    <div class="row member-profile">
        <!-- Menu left -->
        <div class="col-lg-2 col-md-3">
            <br >
            @yield('left-menu')
        </div>
        <!-- /.col -->
        <div class="col-lg-10 col-md-9 tab-content" style="padding: 0 50px;">
            @yield('content-profile')
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ URL::asset('team/js/script.js') }}"></script>
    @yield('script-profile')
@endsection
