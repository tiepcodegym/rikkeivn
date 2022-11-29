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
                            <p>
                                Bạn chưa hoàn thành Quá trình Hội nhập. Bạn vui lòng xem thông tin
                                <a href="{{ config('services.hrm_url').'/hrm/profile/general?isIntegration=1' }}">Tại đây</a> và hoàn thành đầy đủ chậm nhất trong {{ $data['evaluate_deadline'] }} để được ghi nhận hoàn thành,
                                nhận {{ $data['gpoint'] }} G-Point và các đánh giá công việc từ Quản lý trực tiếp.
                            </p>
                            <p>
                                Các thắc mắc vui lòng liên hệ QLTT, người hướng dẫn hoặc Phòng Đào tạo để được trợ giúp.
                            </p>
                            <p>
                                Trân trọng cảm ơn!
                            </p>
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