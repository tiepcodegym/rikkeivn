<!DOCTYPE html>
<html lang="en" >
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <title>@yield('title')</title>
        @yield('css')
        <style>
            @font-face {
                font-family: times-regular-font;
                src: url("{{ asset('fonts/times.ttf') }}");
                font-weight: normal;
            }
            @font-face {
                font-family: times-bold-font;
                src: url("{{ asset('fonts/timesbd.ttf') }}");
                font-weight: bold;
            }
            @font-face {
                font-family: times-italic-font;
                src: url("{{ asset('fonts/timesi.ttf') }}");
                font-style: italic;
            }
            @font-face {
                font-family: times-bold-italic-font;
                src: url("{{ asset('fonts/timesbi.ttf') }}");
                font-weight: bold;
                font-style: italic;
            }
        </style>
    </head>
    <body>
        @yield('content')
    </body>
</html>