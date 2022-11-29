<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="robots" content="noindex, nofollow" />
        <title>
            @yield('title')
            Rikkeisoft Intranet
        </title>
        <script>
            var baseUrl = '{{ url('/') }}/',
                ckeditorBaseUrl = baseUrl + 'public/media/ckeditor/';
        </script>
    </head>
    <body>
        @yield('content')
        <!-- Add custom script follow page -->
        @yield('script')
        @yield('scriptCode')
    </body>
</html>