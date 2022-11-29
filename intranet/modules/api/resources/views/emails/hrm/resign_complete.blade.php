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
                            <p>Hệ thống Rikkei HRM thông báo nhân sự có thông tin dưới đây đã hoàn tất các thủ tục bàn giao nghỉ việc:</p>
                            <table>
                                <tr>
                                    <td style="padding-bottom: 1em;">
                                        Họ và tên:
                                    </td>
                                    <td style="padding-bottom: 1em;">
                                        <p style="font-weight: bold; color: #293042; margin: 0;">{{ $data['employee_name'] }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 1em;">
                                        Mã nhân viên:
                                    </td>
                                    <td style="padding-bottom: 1em;">
                                        <p style="font-weight: bold; color: #293042; margin: 0;">{{ $data['employee_code'] }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 1em;">
                                        Email:
                                    </td>
                                    <td style="padding-bottom: 1em;">
                                        <p style="font-weight: bold; color: #293042; margin: 0;">{{ $data['employee_email'] }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 1em;">
                                        Đơn vị:
                                    </td>
                                    <td style="padding-bottom: 1em;">
                                        <p style="font-weight: bold; color: #293042; margin: 0;">{{ $data['employee_division'] }} - {{ $data['employee_branch'] }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 1em;">
                                        Vị trí (role):
                                    </td>
                                    <td style="padding-bottom: 1em;">
                                        <p style="font-weight: bold; color: #293042; margin: 0;">{{ $data['employee_role'] }}</p>
                                    </td>
                                </tr>
                            </table>
                            <p>Nhân sự {{ $data['employee_name'] }} chính thức nghỉ việc từ ngày {{ $data['end_date'] }}</p>
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
