<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\CoreLang;

$allLangs = CoreLang::allLang();
$currentLang = request()->get('lang');
if (!$currentLang) {
    $currentLang = Session::get('locale');
}
if (!$currentLang) {
    $currentLang = CoreLang::DEFAULT_LANG;
}
?>

<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">
        <link rel="Shortcut Icon" type="image/x-icon" href="{{ URL::asset('favicon.ico') }}">
        
        <title>@yield('title') - Rikkeisoft</title>
        
        <link href="{{ URL::asset('lib/ckeditor/plugins/codesnippet/lib/highlight/styles/vs.css') }}" rel="stylesheet">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.3.8/css/AdminLTE.min.css" />
        <link rel="stylesheet" href="{{ CoreUrl::asset('common/css/style.css') }}" />
        <link rel="stylesheet" href="{{ CoreUrl::asset('tests/css/main.css') }}">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
        @yield('head')
    </head>
    <body class="test-front @yield('body_class')">
        <header id="header">
            <div class="container">
                <div class="logo-box">
                    <img id="logo" class="img-responsive" src="{{ asset('/common/images/logo-rikkei.png') }}" alt="Rikkei.vn">
                </div>
                <form class="switch-lang-form" method="get" action="{{ route('test::switch_lang') }}">
                    <select class="form-control select-search select-lang" name="lang">
                        @foreach($allLangs as $langCode => $langName)
                        <option value="{{ $langCode }}" {{ $currentLang == $langCode ? 'selected' : '' }}>{{ $langName }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </header>
        
        <section id="main_body">
            <div class="container">
                @yield('messages', View::make('messages.success') . View::make('messages.errors'))
                
                <section class="content">
                    @yield('content')
                </section>
            </div>
        </section>

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
        
        <footer id="footer">
            <div class="container">
                @yield('footer')
            </div>
        </footer>
        
        <script>
            var _token = '{{ csrf_token() }}';
            var _home_url = '<?php echo URL('/'); ?>';
            var view_test_url = '<?php echo route('test::view', ['code' => '']) ?>';
            var url_load_candidate = "{{ route('test::candidate.load_data') }}";
            var text_selection = '<?php echo trans('test::test.selection') ?>';
            var text_start = '<?php echo trans('test::test.start') ?>';
            var text_testing = '<?php echo trans('test::test.testing') ?>';
            var text_finish = '<?php echo trans('test::test.finish') ?>';
            var text_please_select_test = '<?php echo trans('test::validate.please_select_test') ?>';
            var text_please_select_candidate = '<?php echo trans('test::validate.please_select_candidate') ?>';
            var text_field_required = '<?php echo trans('test::validate.this_field_is_required') ?>';
            var text_email_format = '<?php echo trans('test::validate.this_field_is_email') ?>';
            var text_required_answers = '<?php echo trans('test::validate.please_input_field', ['field' => trans('test::test.answers')]) ?>';
            var text_questions_not_answer = "<?php echo trans('test::validate.you_has_questions_not_answer') ?>";
            var text_test_time_over = '<?php echo trans('test::validate.the_test_time_is_over') ?>';
            var text_max_length = '<?php echo trans('test::validate.this_field_max_character', ['max' => 255]) ?>';
            var text_max_length_11 = '<?php echo trans('test::validate.this_field_max_character', ['max' => 11]) ?>';
        </script>
        
        @yield('foot')
        @include('test::template.audio')
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="{{ URL::asset('lib/ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js') }}"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
        <script src="{{ URL::asset('/lib/js/jquery.validate.min.js') }}"></script>
        <script src="{{ CoreUrl::asset('tests/js/main.js') }}"></script>
        <script src="{{ CoreUrl::asset('tests/js/audio.js') }}"></script>
        <script>
            if (typeof hljs != 'undefined') {
                hljs.initHighlightingOnLoad();
            }
        </script>
        <script type="text/x-mathjax-config">
            MathJax.Hub.Config({
              tex2jax: {inlineMath: [["$$","$$"],["\\(","\\)"]]}
            });
        </script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.0/MathJax.js?config=TeX-AMS_HTML"></script>

    </body>
</html>
