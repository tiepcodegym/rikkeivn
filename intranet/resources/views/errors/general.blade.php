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
        <link rel="icon" href="{{ URL::asset('img/favicon.ico') }}" type="image/x-icon">
        <link rel="Shortcut Icon" type="image/x-icon" href="{{ URL::asset('img/favicon.ico') }}">
        <link rel="stylesheet" href="{{ URL::asset('common/css/login.css') }}" />
        @yield('css')
    </head>
    <body class="hold-transition guest">
        <div class="jumbotron">
            <div class="container">
                <section class="content">
                    @if (isset($errors) && count($errors) > 0)
                        <div class="page-messages flash-message">
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    @if (isset($message))
                    <div class="page-messages flash-message">
                        <div class="alert alert-danger">
                            {{ $message }}
                        </div>
                    </div>
                    @endif
                    @if (isset($testedId))
                    <div class="page-messages flash-message">
                        <div class="alert alert-danger">
                            <span>{{ trans('test::test.you_had_done_this_test') }} </span>
                            <a class="link" href="{{ route('test::result', ['id' => $testedId]) }}">{{ trans('test::test.view_results') }}</a>
                        </div>
                    </div>
                    @endif
                </section><!-- /.content -->
            </div>
        </div>
        @yield('script')
    </body>
</html>
