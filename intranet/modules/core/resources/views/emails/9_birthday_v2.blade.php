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
                    <div class="margin-t-7">H??m nay l?? ng??y {{ $data['employee_birthday'] }} - m???t ng??y r???t ?????c bi???t v???i b???n v?? ?????i gia ????nh Rikkeisoft.</div>
                    <div class="margin-t-7">Ch??c b???n b?????c sang m???t tu???i m???i v???i:</div>
                    <div class="text-yellow margin-t-7">
                        S???c kho??? d???o dai <br>
                        H???nh ph??c ???????ng d??i <br>
                        Th??nh c??ng ti???n t???i <br>
                        V???ng v??ng t????ng lai
                    </div>
                    <div class="margin-t-7">C???m ??n b???n ???? ?????ng h??nh c??ng Rikkeisoft trong th???i gian qua. H??y lu??n m???m c?????i v?? t??? tin cho ch???ng ???????ng ph??t tri???n ph??a tr?????c nh??!</div>
                    <div class="margin-t-7">Tr??n tr???ng!</div>
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