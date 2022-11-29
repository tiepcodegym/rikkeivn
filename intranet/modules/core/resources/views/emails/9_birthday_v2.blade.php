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
        <div class="box-header">
            <div class="box-img">
                <img class="img-all" src="imgAllbirthday.png" alt="">
            </div>
        </div>

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
                    <div>Dear {{ $data['employee_name'] }},</div>
                    <div class="margin-t-7">Hôm nay là ngày {{ $data['employee_birthday'] }} - một ngày rất đặc biệt với bạn và đại gia đình Rikkeisoft.</div>
                    <div class="margin-t-7">Chúc bạn bước sang một tuổi mới với:</div>
                    <div class="text-yellow margin-t-7">
                        Sức khoẻ dẻo dai <br>
                        Hạnh phúc đường dài <br>
                        Thành công tiến tới <br>
                        Vững vàng tương lai
                    </div>
                    <div class="margin-t-7">Cảm ơn bạn đã đồng hành cùng Rikkeisoft trong thời gian qua. Hãy luôn mỉm cười và tự tin cho chặng đường phát triển phía trước nhé!</div>
                    <div class="margin-t-7">Trân trọng!</div>
                </div>
            </div>
        </div>
        <div class="text-footer">
            <h1>Best wish to you!</h1>
        </div>
    </div>
</div>
</body>
</html>