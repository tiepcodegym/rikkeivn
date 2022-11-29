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
        <link rel="icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">
        <link rel="Shortcut Icon" type="image/x-icon" href="{{ URL::asset('favicon.ico') }}">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{ URL::asset('tests_old/css/main.css') }}">
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
        @yield('foot')
    </body>
</html>

