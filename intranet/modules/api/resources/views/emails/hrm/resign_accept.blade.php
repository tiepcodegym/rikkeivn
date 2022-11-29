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
                            <p>Cảm ơn các bạn đã gắn bó với Rikkei suốt {{ $data['day_work_on_rikkei'] }} ngày vừa qua. Để hoàn thành quy trình nghỉ việc, bạn vui lòng hoàn thành các tác vụ dưới đây trước ngày làm việc cuối cùng:</p>
                            <p>
                                + Kiểm tra lại bảng công chi tiết và tiến hành bổ sung công/bổ sung đơn OT/đơn công tác nếu cần thiết. 
                            </p>
                            <p>
                                + Kiểm tra lại tài sản hiện có trên hệ thống Rikkei.vn và thông báo nếu thất lạc tài sản trước cho bộ phần IT chuyên trách.
                            </p>
                            <p>
                                + Trong trường hợp bạn đã có bảo hiểm xã hội và chưa nộp lại cho Công ty, vui lòng liên hệ
                                <a href="{{ config('services.hrm_url').'/profile/general/'.$data['hr_account'] }}">{{ $data['hr_account'] }}</a> để nộp lại hoặc nộp vào ngày cuối cùng làm việc khi bàn giao.
                            </p>
                            <p>+ Vào ngày làm việc cuối cùng, bạn cần thực hiện bàn giao với các cá nhân sau:</p>
                            <p>- Bàn giao công việc: với Quản lý trực tiếp và được xác nhận trên hệ thống HRM.</p>
                            <p>- Bàn giao tài sản: với bộ phận IT.</p>
                            <p>- Bàn giao thẻ Nhân viên, sổ BHXH (nếu có): với
                            <a href="{{ config('services.hrm_url').'/profile/general/'.$data['admin_account'] }}">{{ $data['admin_account'] }}</a> - Phòng Tuyển dụng/HCTH.
                            </p>
                            <p>- Kiểm tra về phân công nợ:
                            <a href="{{ config('services.hrm_url').'/profile/general/'.$data['hr_account'] }}">{{ $data['hr_account'] }}</a> - Phòng TCKT/HCTH.
                            </p>
                            <p>Bất kỳ thắc mắc hoặc cần hỗ trợ, vui lòng liên hệ
                            <a href="{{ config('services.hrm_url').'/profile/general/'.$data['hr_account'] }}">{{ $data['hr_account'] }}</a>
                            </p>
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
