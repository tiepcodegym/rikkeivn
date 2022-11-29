<html>

<head>
</head>

<body style="font-family: Arial, Helvetica, sans-serif;">
<div>
  <div>
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td>
          <div>
            <p>Chào Anh/Chị {{ $data['candidate_name'] }},</p>
            <p>
              Đầu tiên, Bộ phận Tuyển dụng - Công Ty cổ phần Rikkeisoft rất cảm ơn Anh/Chị vì đã dành sự quan tâm và nộp hồ sơ ứng tuyển vào vị trí Dev.
            </p>
            <p>
              Sau thời gian xem xét, Chúng tôi trân trọng mời Anh/Chị tới tham gia phỏng vấn vị trí này tại Công ty với các thông tin chi tiết như sau:
            </p>
            <p>
              - Thời gian: {{ $data['interview_first_date'] }}
            </p>
			@if ($data['is_online'])
            <p>
              - Hình thức Phỏng Vấn: Online
            </p>
			<p>
              - Link tham dự: {{ $data['interview_link'] }}
            </p>
			@else
			<p>
              - Hình thức Phỏng Vấn: Offline
            </p>
			<p>
              - Địa điểm: {{ $data['interview_address'] }}
            </p>
			@endif
            <p>- Anh/Chị vui lòng hoàn thành link thông tin ứng viên trước tham gia phỏng vấn 
                <a href="https://hrm.rikkei.vn/candidate-information"
                 style="color:#0054C5 ;text-decoration: none; cursor: pointer;">https://hrm.rikkei.vn/candidate-information</a>
            </p>
            <p style="margin-top: 24px;">
                Anh/Chị vui lòng trả lời lại email này trước 01 ngày để xác nhận sự có mặt tại buổi phỏng vấn.
            </p>
            <p>
                Nếu có bất kỳ vấn đề gì, Anh/Chị có thể phản hồi ngay qua email này hoặc qua 
            </p>
            <p>
                Người liên hệ:
            </p>
            <p>
                {{ $data['hr_name'] }} - {{ $data['hr_phone_number'] }}
            </p>
            <p>Để tham khảo thêm thông tin Công ty vui lòng truy cập 
                <a href="https://tuyendung.rikkeisoft.com/"
                 style="color:#0054C5 ;text-decoration: none; cursor: pointer;">Tại đây</a>
            </p>
            <p>
                Chúng tôi rất mong được chào đón Anh/Chị.
            </p>
            <p>
                Trân trọng cảm ơn!
            </p>
            <p style="margin-top: 24px;">
                --------------------------------------------
            </p>
            <p style="margin-top: 24px;">
                Thanks & Best Regards,
            </p>
            <p>
                {{ $data['hr_name'] }} | {{ $data['hr_role'] }}
            </p>
            <p>
                Rikkeisoft Co,. Ltd.
            </p>
            <p>
                Mobile: {{ $data['hr_phone_number'] }}
            </p>
            <p>
                Skype: {{ $data['hr_skype'] }}
            </p>
            <p>
                Email: {{ $data['hr_email'] }}
            </p>
            <p>
                --------------------------------------------
            </p>
            <p style="margin-top: 24px;">
                Head Office: 21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi
            </p>
            <p>
                Tel: (+84) 243 623 1685
            </p>
            <p> Page: 
                <a href="https://www.facebook.com/rikkeisoft?fref=ts"
                 style="color:#0054C5 ;text-decoration: none; cursor: pointer;">https://www.facebook.com/rikkeisoft?fref=ts</a>
            </p>
            <p> Website: 
                <a href="https://rikkeisoft.com/"
                 style="color:#0054C5 ;text-decoration: none; cursor: pointer;">https://rikkeisoft.com/</a>
            </p>
          </div>
        </td>
      </tr>
    </table>
  </div>
</div>
</body>

</html>