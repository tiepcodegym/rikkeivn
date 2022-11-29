<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>
            @yield('title')
            - Rikkeisoft
        </title>
        <script>
            var siteConfigGlobal = {
                url: '{{ url('/') }}/',
                currentUrl: '{{ app('request')->url() }}/',
                token: '{{ csrf_token() }}'
            };
        </script>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">
        <link rel="Shortcut Icon" type="image/x-icon" href="{{ URL::asset('favicon.ico') }}">
        <!-- Bootstrap 3.3.6 -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
        <!-- common style -->
        <link rel="stylesheet" href="{{ URL::asset('common/css/guest.css') }}" />
        @yield('css')
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <div id="wrapper">
            <div id="wrapper-inner">
                <section id="page-header">
                    @yield('header')
                </section>
                
                <section id="page-content">
                    @yield('content')
                </section>
                
                </section>
                
                <section id="page-footer">
                    @yield('footer')
                </section>
            </div>
            
            <div class="wrapper-after">
                @yield('wrapper_after')
            </div>
        </div>
        
        <!-- modal warning cofirm -->
        <div class="modal fade modal-warning" id="modal-warning-notification">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ 'Notification' }}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ 'Not activity' }}</p>
                        <p class="text-change"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ 'Close' }}</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div><!-- end modal warning cofirm -->
        
        <!-- jQuery 2.2.0 -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <script src="{{ URL::asset('common/js/script.js') }}"></script>
        <!-- Add custom script follow page -->
        <script>
            jQuery(document).ready(function($) {
                RKfuncion.fixHeightWindow.init('#wrapper');
            });
        </script>
        @yield('script')
    </body>
</html>

