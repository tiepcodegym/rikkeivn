<!DOCTYPE html>
<html>
<head>
    <style>
        .body-email {
            max-width: 600px;
            margin: auto
        }
        .body-email * {
            box-sizing: border-box;
        }
    </style>
    @yield('css')
</head>
<?php
$contentWidth = '500px';
?>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<div class="body-email">
    <div style="background: url({{ asset('common/images/email_christmas/background.png') }}) repeat-y; ">
        <div style="padding: 0 24px; 
             width: {{ $contentWidth }}; margin: auto; 
             background: #fff; 
             position: relative;" 
             class="section-mail-content">
            <div class="section-content" id="body_content"
                 style="padding: 0 24px 2px 24px;
                    border-left: 3px solid #E3E2E4;
                    border-right: 3px solid #E3E2E4;
                    word-wrap: break-word;
                    margin-bottom: -5px;
                    text-align: justify;">
                <center><img src="{{ URL::to('common/images/email_christmas/logo1.png') }}" alt=""></center>
                @yield('before_content')
                @yield('content')
            </div>
        </div>
        <!--<div style="height: 17px; width: {{ $contentWidth }}; margin: auto; background: #fff;"></div>-->
        <div style="max-height: 0; width: {{ $contentWidth }}; margin: auto; overflow: visible;">
            <div style="height: 17px; background: #fff;"></div>
            <div style="width: 0; height: 0;
                border-left: 250px solid transparent;
                border-right: 250px solid transparent;
                border-top: 150px solid #fff;"></div>
        </div>
        <div style="max-height: 0; width: {{ $contentWidth }}; margin: auto; overflow: visible;">
            <div style="width: 0; height: 0;
                margin: auto;
                border-left: 227px solid transparent;
                border-right: 227px solid transparent;
                border-top: 136px solid #E3E2E4;"></div>
        </div>
        <div style="width: {{ $contentWidth }}; margin: auto; overflow: visible;">
            <div style="width: 0; height: 0; 
                margin: auto;
                border-left: 221px solid transparent;
                border-right: 221px solid transparent;
                border-top: 133px solid #fff;"></div>
        </div>
                
        <center>
            <img src="{{ URL::to('common/images/email_christmas/patern4.png') }}" alt="" style="margin-top: 25px;">
        </center>
        <div style="background: url( {{ asset('common/images/email_christmas/patern3.png') }}) no-repeat; height: 100px; margin-top: -50px">
        </div>
        @include('core::emails.include.1_footer')
    </div>
</div>
</body>
</html>
