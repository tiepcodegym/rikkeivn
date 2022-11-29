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
            <p>Xin chào {{ $data['hr_account'] }},</p>
            <p>
              Có một ứng viên vừa pass phỏng vấn và được yêu cầu tuyển dụng
            </p>
            <p>
              Thông tin người yêu cầu
            </p>
            <p>
              Họ và tên: {{ $data['requester_name'] }}
            </p>
            <p>
              Email: {{ $data['requester_email'] }}
            </p>
            <p>
              Thông tin ứng viên
            </p>
            <p>
              Họ và tên: {{ $data['candidate_name'] }}
            </p>
              <p>
              Email: {{ $data['candidate_email'] }}
            </p>
            <p>
              Test GMAT: {{ $data['candidate_email'] }}
            </p>
            <p>
              Test Chuyên môn: {{ $data['candidate_email'] }}
            </p>
            <p>
              Thời gian nhận việc: {{ $data['candidate_email'] }}
            </p>
            <p>
              Thời hạn hợp đồng: {{ $data['candidate_email'] }}
            </p>
            <p>
              Loại hợp đồng: {{ $data['candidate_email'] }}
            </p>
            <p>
              Vị trí tuyển dụng: {{ $data['candidate_email'] }}
            </p>
            <p>
                <a href="{{ config('services.hrm_url').'hrm/management/candidate/detail/'.$data['id'] }}"
                 style="color:#0054C5 ;text-decoration: none; cursor: pointer;">Xem chi tiết</a>
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