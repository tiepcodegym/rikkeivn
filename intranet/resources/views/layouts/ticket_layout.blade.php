@extends('layouts.default')

@section('title')
    @yield('title-ticket')
@endsection

@section('css')  
    @yield('css-ticket')
@endsection

@section('content')
    <div class="row">
        <!-- Menu left -->
        <div class="col-lg-2 col-md-3">
            @include('ticket::include.menu_left')
        </div>
        <!-- /.col -->

        <div class="col-lg-10 col-md-9">
            @yield('content-ticket')
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
@endsection

@section('script')
    @yield('script-ticket')
@endsection
