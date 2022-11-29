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
                            <p>Xin chào Anh/Chị,</p>
                            <p>Bộ phận đào tạo gửi lời mời Anh/Chị tham dự chủ đề {{ $data['name'] }} vào lúc {{ $data['start_time'] }} ngày {{ $data['date'] }} dưới sự dẫn dắt của giảng viên {{ $data['mentor'] }}.</p>
                            <ul style="padding: 0;">
                                <li>Thông tin chi tiết đào tạo:
                                    <i style="display: block; white-space: pre-line;">{{ $data['detail'] }}</i>
                                </li>
                            </ul>
                            <p>Anh/Chị vui lòng xác nhận tham gia <a href="{{ config('services.hrm_url').'/hrm/learning/g-point/activities/sign-up-activity/'.$data['activity_id'] }}">Tại đây</a> trong vòng 24h sau khi nhận được mail này.</p>
                            <p>Các thắc mắc vui lòng liên hệ HR / L&D đầu mối tại các chi nhánh.</p>
                            <p>Hẹn gặp Anh/Chị tại hoạt động {{ $data['name'] }}.</p>
                            <p>Trân trọng!</p>
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
