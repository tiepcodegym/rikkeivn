<?php
use Rikkei\Core\View\CoreUrl;
?>
<!DOCTYPE html>
<html ng-app="RkApp" @yield('ng-controller')>
    <head>
        <title ng-bind="varGlobal.titlePage + ' - Rikkeisoft Intranet'"></title>
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
    <body class="hold-transition skin-blue layout-top-nav">
        <div class="wrapper">
            @include('layouts.include.header')
            <!-- Full Width Column -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <!-- Content Header (Page header) -->
                    <section class="content-header">
                        <h1>
                            <span ng-bind="varGlobal.titlePage"></span>
                            @yield('after_title')
                        </h1>
                        <!-- Breadcrumb -->
                            @include('include.breadcrumb')
                        <!-- end Breadcrumb -->
                        <div class="clearfix"></div>
                    </section>
                    @include('messages.success')
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
        <div class="modal fade" id="modal-delete-confirm" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ Lang::get('core::view.Are you sure delete item(s)?') }}</p>
                        <p class="text-change"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                        <button type="button" class="btn btn-outline btn-ok">{{ Lang::get('core::view.OK') }}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div> <!-- modal delete cofirm -->
        
        <!-- modal warn cofirm -->
        <div class="modal fade" id="modal-warn-confirm" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
                        <h4 class="modal-title-change"></h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ Lang::get('core::view.Are you sure to do this action?') }}</p>
                        <p class="text-change"></p>
                        <ul class="ul-wraning">
                            
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                        <button type="button" class="btn btn-outline btn-ok">{{ Lang::get('core::view.OK') }}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div> <!-- modal delete cofirm -->
        @include('layouts.include.js_default')
        <!-- Add custom script follow page -->
        @yield('script')
    </body>
</html>

