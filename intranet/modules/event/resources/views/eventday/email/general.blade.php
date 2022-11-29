<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig(7);
?>
@extends($layout)

@section('css')
<style>
    @font-face {
        font-family: 'Montserrat';
        src: url('{{ asset("common/fonts/montserrat/Montserrat-VariableFont_wght.ttf") }}');
    }
    @font-face {
        font-family: 'Montserrat-Light';
        src: url('{{ asset("common/fonts/montserrat/Montserrat-Light.ttf") }}');
    }
    #content {
        font-family: Montserrat;
    }
    p {
        font-size: 18px;
    }
</style>
@endsection

@section('content')
<section class="container clearfix" id="content" style="background-color: #fff;">
    <table class="container header" width="650" style="background: #f4f4f4;">
        <tr>
            <td style="padding-left: 60px;">
                <img src="{{ asset('common/images/email/logo.png') }}" class="logo-img" style="width: 72px;
                    height: 37px;
                    margin-top: auto;
                    margin-bottom: auto;" > 
            </td>
            <td style="text-align: right; padding-right: 60px">
                <p class="header-text" style="color: #6C6C6C;
                    font-weight: 500;
                    font-size: 16px;
                    margin-bottom: 36px;
                    font-family: Montserrat-Light;
                    margin-top: 36px;">HAPPY LUNAR NEW YEAR 2022</p>
            </td>
        </tr>
        

            
    </table>

    <!-- <div class="iframe-container">
      <iframe src="https://www.youtube.com/embed/Vi5Gy_61Aug" allowfullscreen></iframe>
    </div> -->
    <div class="header-img">
        <img src="{{ asset('common/images/email/Header.gif') }}" style="height: auto; width: 100%;">
    </div>
    <div style="text-align: center; margin-top: 20px;"> 
        <a href="https://www.youtube.com/watch?v=Vi5Gy_61Aug" target="" style="
           color: #fff;
           text-decoration: none;
           padding: 10px;
           border-radius: 5px;
           text-align: center;
           font-family: Montserrat;
           "><img src="{{ asset('common/images/email/play.png') }}" style="width: 164px; border-radius: 6px;" >
        </a>
    </div>
    <div class="logo-1">
        <img src="{{ asset('common/images/email/asset1.png') }}" width="251px" height="149px" style="margin-left: auto;
             margin-right: auto;
             display: block;
             margin-top: 38px;
             margin-bottom: 10px;">
            <p class="text-center english" style="text-align: center;font-size: 13px;
               font-weight: 500;">(English Below)</p>
            <h3 class="text-center red-title-1" style="text-align: center;color: #BC2228;
                font-weight: 700;
                font-size: 23px;
                line-height: 28.04px;">
                RIKKEISOFT<br>
                    MỪNG XUÂN NHÂM DẦN 2022
            </h3>
    </div>
    <div class="container description-1" style="padding:20px 60px; text-align: justify;">
        <b>Kính gửi Quý Khách hàng và Đối tác,</b>

        <p style="margin: 22px auto;">Tết Nguyên đán Nhâm Dần 2022 đang kề cận, là dịp để Rikkeisoft gửi lời cảm ơn tới Quý Khách hàng một năm qua đã luôn đặt niềm tin và đồng hành cùng Công ty. </p>
        <p>Ban Lãnh đạo cùng tập thể cán bộ nhân viên Rikkeisoft xin chúc Quý Khách hàng và Đối tác năm mới:</p>

        <ul class="text-center ul-desktop" style="text-align: center; padding-left:0">
            <li style="list-style: none;
                margin-bottom: 15px;
                width: auto;
                font-size: 16px;
                color: #BC2228;
                font-weight: 700;">AN KHANG <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dot" viewBox="0 0 16 16">
                    <path d="M8 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>
                </svg>
                </i>
            </li>
            <li style="list-style: none;
                margin-bottom: 15px;
                width: auto;
                font-size: 16px;
                color: #BC2228;
                font-weight: 700;">THỊNH VƯỢNG <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dot" viewBox="0 0 16 16">
                    <path d="M8 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>
                </svg>
                </i>
            </li>
            <li style="list-style: none;
                width: auto;
                font-size: 16px;
                color: #BC2228;
                font-weight: 700;">VẠN SỰ NHƯ Ý</li>
        </ul>

        <p style="margin: 22px auto;">2022 tiếp tục là năm đầy triển vọng khi Rikkeisoft đạt mốc 10 năm thành lập. Rikkeisoft tin rằng, sự hợp tác và giúp đỡ của Quý Khách hàng và Đối tác sẽ là động lực để Công ty đạt được những mục tiêu đặt ra trong năm 2022.</p>

        <p style="margin: 22px auto;">Cùng chào đón một khởi đầu mới - thành công mới! </p>
        <p>Chân thành cảm ơn và Chúc mừng Xuân Nhâm Dần,</p>
        <b>Rikkeisoft Corporation</b>
        <div class="text-en" style="font-style: italic;">
            <h3 class="text-center red-title-2" style="text-align: center;color: #BC2228;
                font-weight: 700;
                font-size: 23px;
                line-height: 28.04px;
                font-style: italic;
                padding: 20px;">
                RIKKEISOFT<br>
                    HAPPY NEW YEAR 2022
            </h3>
            <b>Dear Valued Customers and Partners,
            </b>

            <p style="margin: 22px auto;">The Lunar year of The Tiger 2022 is coming, also is an opportunity for Rikkeisoft to send thank you to our customers for trusting and accompanying us.
            </p>

            <p style="margin: 22px auto;">Rikkeisoft would like to wish all Customers and Partners a new year of:
            </p>
            <ul class="text-center ul-desktop" style="text-align: center;padding-left:0"">
                <li style="list-style: none;
                    margin-bottom: 15px;
                    width: auto;
                    font-size: 16px;
                    color: #BC2228;
                    font-weight: 700;">GREAT SUCCESS <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dot" viewBox="0 0 16 16">
                        <path d="M8 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>
                    </svg>
                    </i>
                </li>
                <li style="list-style: none;
                    margin-bottom: 15px;
                    width: auto;
                    font-size: 16px;
                    color: #BC2228;
                    font-weight: 700;">GOOD HEALTH <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dot" viewBox="0 0 16 16">
                        <path d="M8 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>
                    </svg>
                    </i>
                </li>
                <li style="list-style: none;
                    margin-bottom: 15px;
                    width: auto;
                    font-size: 16px;
                    color: #BC2228;
                    font-weight: 700;">LASTING PROSPERITY</li>
            </ul>

            <p style="margin: 22px auto;">Doing business with you is a pleasure beyond measure. 2022 continues to be a good year when Rikkeisoft reaches the 10th milestone Anniversary. We believe that the cooperation and support of Customers and Partners will drive the company to achieve the goals set out in 2022.
            </p>

            <p style="margin: 22px auto;">Let's welcome a new beginning - new success!
            </p>
            <p>
                Thank you very much and Happy Lunar New Year,
            </p>
            <b>Rikkeisoft Corporation
            </b>
        </div>
        <div class="logo-2">
            <img src="{{ asset('common/images/email/logo10_en.png') }}" style="margin-left: auto;
                 width: 140px;
                 margin-right: auto;
                 display: block;
                 margin-top: 34px;
                 margin-bottom: 17px;" >
        </div>


    </div>

    <footer style="text-align: center;background-color: #E8E8E8;
            padding-top: 30px;
            padding-bottom: 15px;">
        <div class="container" style="padding: 0;">
            <hr style="width: 600px;">
                <div class="social-link text-center">
                    <a href="https://www.facebook.com/rikkeisoft" target="_blank"><img src="{{ asset('common/images/email/facebook.png') }}"></a>
                    <a href="https://www.linkedin.com/company/rikkeisoft/" target="_blank"><img src="{{ asset('common/images/email/in.png') }}"></a>
                    <a href="https://www.youtube.com/c/Rikkeisoft" target="_blank"><img src="{{ asset('common/images/email/youtube_1.png') }}"></a>
                </div>
                <div class="footer-text text-center">
                    <p style="font-size: 13px;">@Rikkeisoft Corporation ·  All Rights Reserved  </p>
                </div>
        </div>
    </footer>
</section>
@endsection

