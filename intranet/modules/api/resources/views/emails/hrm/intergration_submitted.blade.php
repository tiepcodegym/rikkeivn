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
                            <p>
                                Nhân viên mới {{ $data['employee_name'] }} đã hoàn thành các bước trong quá trình Hội nhập. Vui lòng xem thông tin, đánh giá nhân sự hội nhập và phê duyệt tại đây: 
                                <a href="{{ config('services.hrm_url').'/hrm/management/employees/integration/edit/'.$data['integration_id'] }}">Link</a>
                            </p>
                            <p>Trước khi thực hiện đánh giá nhân sự hội nhập, hãy đảm bảo chắc chắn rằng Anh/Chị đã trao đổi cụ thể với người hướng dẫn và nhân sự về các nội dung đánh giá.</p>
                            <p>Anh/Chị vui lòng hoàn thành trước ngày {{ $data['evaluate_deadline'] }} để Bộ phận Nhân sự tiếp tục các bước tiếp theo.</p>
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