<!DOCTYPE html>
<html>
<head>
    <style>
        body{
            font-family: 'Roboto', sans-serif;
        }
        .body-email {
            max-width: 600px;
            margin: auto;
        }
        .body-email * {
            box-sizing: border-box;
        }
        .div-wrap{
            background-size: 600px 740px !important;
            background-position: 0 -16px !important;
        }
        .box-header{
            height: 242px;
        }
        .box-img{
            text-align: center;
        }
        .img-all{
            width: 100%;
        }

        .margin-t-7{
            margin-top: 5px;
            line-height: 1.5;
        }
        .text-yellow{
            text-transform: uppercase;
            color: #fbf18c;
            text-align: center;
            font-weight: normal;
            line-height: 30px;
        }

        .text-footer h1{
            color: #c0393e;
            text-align: center;
            text-transform: uppercase;
        }
    </style>
    @yield('css')
</head>
<?php
$contentWidth = '500px';
?>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<div class="body-email">
    <div class="div-wrap" style="background: url({{ asset('common/images/email_birthday_v2/7.jpg') }}) no-repeat">
{{--        <div class="box-header">--}}
{{--            <div class="box-img">--}}
{{--                <img class="img-all" src="{{ URL::to('common/images/email_birthday_v2/img-all.png') }}" alt="">--}}
{{--            </div>--}}
{{--        </div>--}}

        <div style="background: #c0393e;
            color: #fff;"
             class="section-mail-content">
            <div class="section-content" id="body_content"
                 style="padding: 25px;
                    border-left: 3px solid #E3E2E4;
                    border-right: 3px solid #E3E2E4;
                    word-wrap: break-word;
                    margin-bottom: -5px;
                    font-size: 18px;
                    text-align: justify;">
                <div>
                    <div>Thank You!</div>
                    <div>Dear {{ $data['employee_name'] }},</div>
                    <div class="margin-t-7">H??m nay l?? m???t ng??y ?????c bi???t - Rikkei HR's Day 10/10.</div>
                    <div class="margin-t-7">Ban L??nh ?????o c??ng ty xin g???i l???i c???m ??n s??u s???c v?? s??? c??? g???ng v?? c???ng hi???n c???a b???n
                        trong vi???c ?????m b???o ngu???n l???c, thu h??t v?? tuy???n d???ng ???????c nh???ng nh??n s??? ph?? h???p v???i t??? ch???c.
                        Ch??c b???n s???c kh???e, h???nh ph??c, v?? ti???p t???c ?????ng h??nh c??ng Rikkeisoft tr??n c??c ch???ng ???????ng ti???p theo.
                    </div>
                    <div class="margin-t-7">Rikkei t??? h??o khi c?? b???n - m???t c?? HR xinh ?????p, t???n t??m v?? nhi???t huy???t!</div>
                    <div class="margin-t-7">Happy Rikkei HR's day</div>
                </div>
            </div>
        </div>
{{--        <div class="text-footer">--}}
{{--            <h1>Best wish to you!</h1>--}}
{{--        </div>--}}
    </div>
</div>
</body>
</html>
