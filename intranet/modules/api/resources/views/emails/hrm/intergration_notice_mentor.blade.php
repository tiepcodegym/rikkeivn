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
                            <p>Thân chào Anh/Chị,</p>
                            <p>Anh/Chị sẽ là người hướng dẫn của nhân sự mới {{ $data['employee_name'] }} sẽ gia nhập vào ngày {{ $data['onboard_date'] }}.</p>
                            <p>
                                Vui lòng xem các thông tin về Nhân viên mới tại: 
                                <a href="{{ config('services.hrm_url').'/hrm/management/employees/integration/edit/'.$data['integration_id'] }}">Link</a>
                                và chuẩn bị các nội dung hướng dẫn cần thiết.
                            </p>
                            <p>Anh/Chị vui lòng hoàn thành trước ngày Nhân sự gia nhập để đảm bảo Nhân viên mới được đón tiếp chu đáo, nhanh chóng hòa nhập và có các trải nghiệm tuyệt vời tại {{ $data['department_name'] }}.</p>
                            <p>Trân trọng cảm ơn!</p>
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