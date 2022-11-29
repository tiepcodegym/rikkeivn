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
                        <img src="{{ asset('common/images/email_hrm/logo-thanks-header.png') }}">
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="background: #FFFFFF; padding: 10px 40px;">
                            <p>Thân gửi {{ $data['employee_name'] }},</p>
                            <p>Cảm ơn Anh/Chị đã tham gia hoạt động: {{ $data['activity_name'] }} dưới sự dẫn dắt của giảng viên {{ $data['host_name'] }}.</p>
                            <p>Anh/Chị vui lòng đánh giá hoạt động <a href="{{ config('services.hrm_url').'/hrm/learning/g-point/activities/evaluation/'.$data['evaluation_id'] }}">Tại đây</a> trước ngày {{ $data['date'] }} để được ghi nhận tham gia và nhận ngay {{ $data['point'] }} G-Point.</p>
                            <p>Link tài liệu chia sẻ vui lòng theo dõi và cập nhật <a href="{{ config('services.hrm_url').'/hrm/learning/g-point/activities/'.$data['activity_id'] }}">Tại đây</a>.</p>
                            <p>Hẹn gặp Anh/Chị tại các hoạt động tiếp theo.</p>
                            <p>Trân trọng!</p>
                        </div>
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
