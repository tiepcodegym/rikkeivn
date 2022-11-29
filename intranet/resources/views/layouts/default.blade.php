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
    <body class="hold-transition skin-blue layout-top-nav @yield('body_class')" @yield('body_attrs')>
        <?php
        if(!isset($langDomain) || !$langDomain) {
            $langDomain = 'core::view.';
        }
        ?>
        <div class="wrapper">
            @include('layouts.include.header')
            <!-- Full Width Column -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <!-- Content Header (Page header) -->
                    <section class="content-header">
                        <h1>
                            @yield('title')
                            @yield('after_title')
                        </h1>
                        <!-- Breadcrumb -->
                            @include('include.breadcrumb')
                        <!-- end Breadcrumb -->
                        <div class="clearfix"></div>
                    </section>
                    
                    @include('messages.success')
                    @if (!isset($createProject))
                    @include('messages.errors')
                    @endif

                    <!-- Main content -->
                    <section class="content">
                        <div class="content-container">
                            @yield('content')
                        </div>
                    </section><!-- /.content -->
                </div><!-- /.container -->
            </div><!-- /.content-wrapper -->
            @include('layouts.include.footer')
            @include("include.menu_mobile")
        </div><!-- ./wrapper -->

        <!-- modal delete cofirm -->
        <div class="modal fade @yield('confirm_class', 'modal-danger')" id="modal-delete-confirm" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ Lang::get($langDomain.'Confirm') }}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ Lang::get($langDomain.'Are you sure delete item(s)?') }}</p>
                        <p class="text-change"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get($langDomain.'Close') }}</button>
                        <button type="button" class="btn btn-outline btn-ok">{{ Lang::get($langDomain.'OK') }}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div> <!-- modal delete cofirm -->
        
        <!-- modal warn cofirm -->
        <div class="modal fade @yield('warn_confirn_class', 'modal-warning')" id="modal-warn-confirm" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ Lang::get($langDomain.'Confirm') }}</h4>
                        <h4 class="modal-title-change"></h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ Lang::get($langDomain.'Are you sure to do this action?') }}</p>
                        <p class="text-change"></p>
                        <ul class="ul-wraning">
                            
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get($langDomain.'Close') }}</button>
                        <button type="button" class="btn btn-outline btn-ok">{{ Lang::get($langDomain.'OK') }}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div> <!-- modal delete cofirm -->
        
        <!-- modal warning cofirm -->
        <div class="modal fade modal-warning" id="modal-warning-notification">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ trans('core::view.Notification') }}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ 'Not activity' }}</p>
                        <p class="text-change"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div><!-- end modal warning cofirm -->
        
        <!-- modal success cofirm -->
        <div class="modal fade modal-success" id="modal-success-notification">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ trans('core::view.Notification') }}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ 'Success' }}</p>
                        <p class="text-change"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div><!-- end modal warning cofirm -->
        <a href="#" class="top-up">
            <i class="fa fa-arrow-circle-up"></i>
        </a>
        @include('layouts.include.js_default')
        <!-- Add custom script follow page -->
        @yield('script')
        @yield('scriptCode')
    </body>
</html>
