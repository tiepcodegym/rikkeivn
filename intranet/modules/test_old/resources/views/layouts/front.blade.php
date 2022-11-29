<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        
        <title>@yield('title')</title>
        
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
        <link rel="stylesheet" href="{{ URL::asset('tests_old/css/main.css') }}">
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
        @yield('head')
    </head>
    <body class="@yield('body_class')">
        <header id="header">
            <div class="logo-box">
                <img id="logo" class="img-responsive" src="{{ asset('/common/images/logo_login.png') }}" alt="Rikkei.vn">
            </div>
        </header>
        
        <section id="main_body">
            <div class="container">
                @yield('content')
            </div>
        </section>
        
        <footer id="footer">
            
        </footer>
        
        <script>
            var _home_url = '<?php echo URL('/'); ?>';
            var _get_test_url = '<?php echo route('test_old::get_tests') ?>';
            var text_selection = '<?php echo trans('test_old::test.selection') ?>';
            var text_start = '<?php echo trans('test_old::test.start') ?>';
            var text_testing = '<?php echo trans('test_old::test.testing') ?>';
            var text_finish = '<?php echo trans('test_old::test.finish') ?>';
        </script>
        
         @yield('foot')
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
        <script src="{{ URL::asset('tests_old/js/main.js') }}"></script>

    </body>
</html>
