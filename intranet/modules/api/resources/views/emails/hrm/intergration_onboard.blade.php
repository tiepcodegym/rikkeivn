<html>

<head>
</head>

<body style="font-family: Arial, Helvetica, sans-serif;">
    <div style="background: #F4F4F4; min-height: 100vh;">
        <div style="height: 100px"></div>
        <div style="width: 750px; margin: auto;">
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <div style="background: #FFFFFF; padding: 10px 40px;">
                            <p>Thân chào {{ $data['employee_name'] }},</p>
                            <p>Chào mừng Bạn gia nhập Rikkeisoft!</p>
                            <p>Chúng tôi rất vui mừng vì có Bạn đồng hành trên chặng đường sắp tới, đồng thời tin tưởng rằng KỶ LUẬT và NỖ LỰC sẽ giúp bản thân Bạn phát triển và viết nên những câu chuyện của riêng mình tại đây.</p>
                            <p>Hãy cùng bắt đầu một hành trình mới tràn đầy hứng khởi và đừng quên chia sẻ cho Chúng tôi những trải nghiệm lần đầu của Bạn tại mái nhà Rikkeisoft nhé.</p>
                            <p>
                                Cùng khám phá ngay lộ trình hội nhập của bạn
                                <a href="{{ config('services.hrm_url').'/hrm/profile/general?isIntegration=1' }}">Tại đây</a>
                            </p>
                            <p>Một lần nữa, chào mừng người anh em về nhà!</p>
                            <p>Trân trọng.</p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 40px; background: #FFFFFF;">
                        <div style="background: #FFFFFF; border: 1px solid #F1F1F1;"></div>
                        <div style="height: 10px; background: #FFFFFF"></div>
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #FFFFFF;">
                        <table style="width: 100%">
                            <tr>
                                <td style="text-align: left;padding: 0px 0px 15px 40px;">
                                    <img src="{{ asset('common/images/email_hrm/rikkei.png') }}">
                                </td>
                                <td style="text-align: right;padding: 0px 40px 15px 0px;">
                                    <img src="{{ asset('common/images/email_hrm/hrm-sign.png') }}">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>