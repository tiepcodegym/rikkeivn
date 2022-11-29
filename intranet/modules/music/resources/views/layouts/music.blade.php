<!DOCTYPE html>
<html>
    <head>
        <title>Order Music</title>
        @include('layouts.include.css_default')
        <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
        @yield('css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/square/red.css" />
    </head>
    <body class="hold-transition skin-blue layout-top-nav">
        <?php
        if(!isset($langDomain) || !$langDomain) {
            $langDomain = 'core::view.';
        }
        ?>
        <div class="wrapper">
            <header>
                @include('music::layouts.include.header_music')
            </header>
            <!-- Full Width Column -->
            <div class=" music-content" url = "{{URL::asset('common/images/music_bgr.jpg')}}">
                    
                    
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
                <div class="content-footer"><span>Copyright © 2017 RikkeiSoft. All rights reserved.</span></div>
                <!-- /.container -->
            </div>
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
        <div class="modal fade modal-warning" id="modal-warn-confirm" tabindex="-1" role="dialog">
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
        
        <!-- modal success cofirm -->
        <div class="modal fade modal-success" id="modal-success-notification">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ 'Notification' }}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ 'Success' }}</p>
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
        @include('layouts.include.js_default')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
        <!-- Add custom script follow page -->
        @yield('script')
        @yield('scriptCode')
    </body>
</html>
