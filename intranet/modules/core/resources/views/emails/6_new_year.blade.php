<!DOCTYPE html>
<html>
<head>
    <style>
        .body-email {
            max-width: 1024px;
            margin: auto
        }
        .body-email * {
            box-sizing: border-box;
        }
        img.g-img + div {display:none;}
    </style>
    @yield('css')
</head>
<?php
$contentWidth = '642px';
?>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<div class="body-email">
    <div style="width: 1024px; margin: auto; overflow: visible; ">
    <div style="background: url('{{ asset('common/images/email_newyear/03-Background.png') }}') repeat-y, url('{{ asset('common/images/email_newyear/01-Background1.png') }}') repeat-y;  background-size: 1024px auto">
            <div style="padding: 0 24px;
                    width: {{ $contentWidth }}; margin: auto;
                    background: #fff;"
                 class="section-mail-content">
                <div class="section-content" id="body_content"
                     style="padding: 0 40px 1px 40px;
                    border-left: 3px solid #E3E2E4;
                    border-right: 3px solid #E3E2E4;
                    word-wrap: break-word;
                    font-family: arial, sans-serif;
                    font-size: 13px;
                    margin-bottom: -5px;
                    text-align: justify;">
                    <center><img src="{{ asset('common/images/email_newyear/logo-rikkei.png') }}" alt="" width="160px" style="margin: 40px auto"></center>
                    @yield('content')
                </div>
            </div>
            <div style="max-height: 0; width: {{ $contentWidth }}; margin: auto; overflow: visible;">
                <div style="height: 17px; background: #fff;"></div>
                <div style="width: 0; height: 0;
                border-left: 321px solid transparent;
                border-right: 321px solid transparent;
                border-top: 147px solid #fff;
                ">
                </div>
            </div>
            <div style="max-height: 0; width: {{ $contentWidth }}; margin: auto; overflow: visible;">
                <div style="width: 0; height: 0;
                    margin: auto;
                    border-left: 298px solid transparent;
                    border-right: 298px solid transparent;
                    border-top: 138px solid #e3e2e4;">
                </div>
            </div>
            <div style="width: {{ $contentWidth }}; margin: auto; overflow: visible;">
                <div style="width: 0; height: 0;
                    margin: auto;
                    border-left: 293px solid transparent;
                    border-right: 293px solid transparent;
                    border-top: 135px solid #fff;">
                </div>
            </div>
            <div style="margin: auto;
            padding-bottom: 5%;">
                <img src="{{ asset('common/images/email_newyear/image_dog.png') }}" width="1024px">
            </div>
        <div>
            
        </div>
            @include('core::emails.include.footer_new_year')
        </div>
    </div>
</div>
</body>
</html>
