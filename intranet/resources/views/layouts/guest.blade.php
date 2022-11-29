<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>
            @yield('title')
            <?php 
                $routeName = Request::route()->getName();
            ?>
        </title>
        <script>
            var baseUrl = '{{ url('/') }}/';
        </script>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">
        <link rel="Shortcut Icon" type="image/x-icon" href="{{ URL::asset('favicon.ico') }}">
        <!-- Bootstrap 3.3.6 -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
        <!-- common style -->
        <link rel="stylesheet" href="{{ URL::asset('common/css/style.css') }}" />
        @include('layouts.include.css_default')
        @yield('css')
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="hold-transition guest">
        <div class="jumbotron">
            <div class="container">
                @yield('messages', View::make('messages.success') . View::make('messages.errors'))
                
                <section class="content">
                    @yield('content')
                </section><!-- /.content -->
            </div>
            <div class="welcome-footer @yield('footer-class', 'col-md-12')">
                <div class="row">
                    @if (strpos($routeName, 'checkpoint') !== false) 
                        <div class="col-md-12"><p class="float-left copyright">Copyright © 2016 <span>Rikkeisoft</span>. All rights reserved.</p></div>
                    @else 
                        <div class="col-md-6 policy-mobile-container"><p class="float-right policy"><a href="http://rikkeisoft.com/privacypolicy/" target="_blank"><span class="policy-link">プライバシーポリシー</span></a> &nbsp; | &nbsp; Version 1.0.0</p></div>
                        <div class="col-md-6"><p class="float-left copyright">Copyright © 2016 <span>Rikkeisoft</span>. All rights reserved.</p></div>
                        <div class="col-md-6 policy-container"><p class="float-right policy">@if(isset($lang) && $lang == "ja")<a href="http://rikkeisoft.com/privacypolicy/" target="_blank"><span class="policy-link">プライバシーポリシー</span></a> &nbsp; | &nbsp; @endif Version 1.0.0</p></div>
                    @endif
                </div>
            </div>
        </div>
        <!-- jQuery 2.2.3 -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
        <!-- Bootstrap 3.3.6 -->
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fastclick/1.0.6/fastclick.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.3.8/js/app.min.js"></script>
        <!-- Add custom script follow page -->
        @yield('script')
        @yield('scriptCode')
    </body>
</html>
