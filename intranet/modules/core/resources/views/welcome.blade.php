@extends('layouts.login')
@section('title')
Login
@endsection

@section('content')
<div class="login-wrapper">
    <h1 class="login-title">
        <img src="{{ URL::asset('common/images/logo_login.png') }}" />
    </h1><!-- /.login-logo -->
    <div class="login-action">
        <p>
            <a class="login-button" href="{{ url('auth/connect', ['google']) }}" 
               role="button">
<!--                <span class="login-btn-item login-btn-head"><img src="{{ URL::asset('img/favicon-r.png') }}" /></span>-->
                <span class="login-btn-item login-btn-content">{{ trans('core::view.LOGIN WITH RIKKEISOFT ACCOUNT') }}</span>
            </a>
        </p>
    </div><!-- /.login-box-action -->
</div><!-- /.login-wrapper -->
@endsection

@section('script')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
<script src="{{ URL::asset('lib/js/jquery.backstretch.min.js') }}"></script>
<script>
    jQuery(document).ready(function($) {
        $.backstretch('{{ URL::asset('common/images/login-background.png') }}');
        
        /**
         * fix position for login block - margin height
         */
        function fixPositionLoginBlock()
        {
            windowHeight = $(window).height();
            loginHeight = $('.login-wrapper').height();
            placeHeight = windowHeight / 2 - loginHeight / 2;
            $('.login-wrapper').css('margin-top', placeHeight + 'px');
        }
        
        fixPositionLoginBlock();
        $(window).resize(function (event) {
            fixPositionLoginBlock();
        })
    });
    
</script>
@endsection
