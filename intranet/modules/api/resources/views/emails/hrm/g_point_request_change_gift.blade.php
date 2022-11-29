<html>

<head>
<style>
</style>
</head>

<body style="font-family: Arial, Helvetica, sans-serif;">
    <div style="background: #F4F4F4; min-height: 100vh;">
        <div style="height: 100px"></div>
        <div style="width: 600px; margin: auto;">
            <table cellpadding="0" cellspacing="0">
                <tr style="background:#ffffff;">
                    <td>
                        <img src="{{ asset('common/images/email_hrm/logo-header.png') }}">
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="background: #FFFFFF; padding: 10px 40px;">
                            <p>Thân gửi {{ $data['admin_name'] }}, </p>
                            <p>Vừa có 1 lượt đổi quà mới.</p>
                            <p>Chi tiết vui lòng xem tại: <a href="{{ config('services.hrm_url').'/hrm/learning/management-gift' }}">Danh sách quà</a></p>
                            <p>Trân trọng!</p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="background: #FFFFFF; text-align: right; padding: 16px 40px 40px 40px;">
                            <img src="{{ asset('common/images/email_hrm/hrm-sign.png') }}">
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
