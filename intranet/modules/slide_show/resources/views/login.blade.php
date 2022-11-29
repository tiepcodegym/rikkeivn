<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>
            Login
            Rikkeisoft Intranet
        </title>
        <script>
            var baseUrl = '{{ url('/') }}/';
        </script>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">
        <link rel="Shortcut Icon" type="image/x-icon" href="{{ URL::asset('favicon.ico') }}">
        <!-- Bootstrap 3.3.6 -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{ URL::asset('common/css/login.css') }}" />
    </head>
    <body class="hold-transition guest">
        <div class="jumbotron">
            <div class="container-fluid">
                <section class="content">
                    <div class="login-wrapper">
                        <h1 class="login-title">
                            <img src="{{ URL::asset('common/images/logo_login.png') }}" />
                        </h1><!-- /.login-logo -->
                        <div class="login-action">
                            <div class="col-sm-6 col-sm-offset-3">
                                <div class="form-group col-sm-offset-2">
                                    <span class="col-sm-7">
                                        <input type="password" class="form-control" id="password" name="password"placeholder="{{trans('slide_show::view.Password')}}">
                                    </span>
                                    <div class="col-sm-1">
                                        <button type="button" class="btn btn-primary" id="typing-password">{{trans('slide_show::view.Submit')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.login-wrapper -->
                </section>
                <!-- /.content -->
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
        <script src="{{ URL::asset('lib/js/jquery.backstretch.min.js') }}"></script>
        <script>
            var urlCheckPasswordSlider = '{{route('slide_show::check-password-slider')}}';
            var token = '{{ csrf_token() }}'
            jQuery(document).ready(function($) {
                $.backstretch('{{ URL::asset('common/images/login-background.png') }}');
                
                /**
                 * fix position for login block - margin height
                 */
                function fixPositionLoginBlock()
                {
                    windowHeight = $(window).height();
                    loginHeight = $('.login-wrapper').height();
                    placeHeight = windowHeight / 2 - loginHeight / 2;
                    $('.login-wrapper').css('margin-top', placeHeight-80 + 'px');
                }
                
                fixPositionLoginBlock();
                $(window).resize(function (event) {
                    fixPositionLoginBlock();
                })

                $(document).on('click', '#typing-password', function (event) {
                    $('.error-validate-password').remove();
                    $password = $('#password').val();
                    data = {
                        _token:token,
                        password: $password,
                    }
                    url = urlCheckPasswordSlider;
                    if ($(this).data('requestRunning')) {
                        return;
                    }
                    $(this).data('requestRunning', true);
                    $.ajax({
                        url: url,
                        type: 'post',
                        data: data,
                        dataType: 'json',
                        success: function(data) {
                            if(data.status) {
                                window.location.href = data['url'];
                            } else {
                                if(data.message_error) {
                                    if(data.message_error.password) {
                                        $('#password').after('<p class="word-break error-validate-password error" for="password" style="font-size:14px; color:red;">' + data.message_error.password[0] + '</p>')
                                    }
                                }
                                if(data.password_error) {
                                    console.log(data.password_error);
                                    $('#password').after('<p class="word-break error-validate-password error" for="password" style="font-size:14px; color:red;">' + data.password_error + '</p>')
                                }
                            }
                        },
                        complete:function () {
                            $('#typing-password').data('requestRunning', false);
                        }
                    });
                });
            });
            
        </script>
    </body>
</html>