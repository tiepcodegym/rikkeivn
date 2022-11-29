<!DOCTYPE html>
<html>
    <head>
        @include('layouts.include.css_default')
        @yield('css')
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <meta name="_token" content="{{ csrf_token() }}"/>
    </head>
    <body class="hold-transition skin-blue layout-top-nav @yield('body_class')">
        <?php
        if(!isset($langDomain) || !$langDomain) {
            $langDomain = 'core::view.';
        }
        ?>
        <div class="wrapper">
            <!-- Full Width Column -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    
                    <section class="content-header">
                        <h1>
                            @yield('title')
                        </h1>
                        <div class="clearfix"></div>
                    </section>
                    
                    @include('messages.success')
                    @include('messages.errors')

                    <!-- Main content -->
                    <section class="content">
                        <div class="content-container">
                            @yield('content')
                        </div>
                    </section><!-- /.content -->
                </div><!-- /.container -->
            </div>
        </div><!-- ./wrapper -->
        
        @include('layouts.include.js_default')
        <!-- Add custom script follow page -->
        @yield('script')
        @yield('scriptCode')
    </body>
</html>

