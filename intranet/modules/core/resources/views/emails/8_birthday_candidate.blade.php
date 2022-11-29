<!DOCTYPE html>
<html>
<head>
    <style>
        .body-email {
            max-width: 1000px;
            width: 1000px;
            margin: auto;
        }
        .body-email * {
            box-sizing: border-box;
        }
        .body-email p {
            margin-top: 14px;
            margin-bottom: 14px;
            line-height: 14px;
        }
        ._bg {
            background-image: url({{ asset('common/images/email_birthday_candidate/background.jpg') }});
            background-size: contain;
            background-repeat: no-repeat;
            height: calc(1000px * 2512/3751);
        }
        .section-mail-content {
            padding-top: 170px;
            width: 534px;
            margin-left: 398px;
        }
        .section-content {
            padding: 0 76px;
            word-wrap: break-word;
            text-align: justify;
        }
    </style>
    @yield('css')
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    <div class="body-email">
        <div class="_bg">
            <div class="section-mail-content">
                <div class="section-content">
                    @yield('before_content')
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
