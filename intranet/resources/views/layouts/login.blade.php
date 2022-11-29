<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>
            @yield('title')
            Rikkeisoft Intranet
        </title>
        <script>
            var baseUrl = '{{ url('/') }}/';
        </script>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta property="og:url" content="{{ URL::to('/') }}" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="Rikkeisoft Intranet" />
        <meta property="og:description" content="Rikkeisoft intranet, hệ thống thông tin nội bộ, tin tức nội bộ" />
        <meta property="og:image" content="{{ URL::asset('common/images/logo-share.png') }}" />
        <link rel="icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">
        <link rel="Shortcut Icon" type="image/x-icon" href="{{ URL::asset('favicon.ico') }}">
        <!-- Bootstrap 3.3.6 -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{ URL::asset('common/css/login.css') }}" />
        @yield('css')
    </head>
    <body class="hold-transition guest">
        <div class="jumbotron">
            <div class="container-fluid">
                @include('messages.success')
                @include('messages.errors')
                <section class="content">
                    @yield('content')
                </section><!-- /.content -->
            </div>
        </div>
        @yield('script')
    </body>
</html>
