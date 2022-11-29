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
            <p>
              Bộ phận tuyển dụng gửi Anh/Chị thông tin đánh giá ứng viên {{ $data['candidate_name'] }} với vị trí ứng tuyển là {{ $data['position_level'] }} thuộc đợt tuyển
              dụng {{ $data['recruitment_name'] }} ({{ $data['start_date'] }} - {{ $data['end_date'] }}) từ Người phỏng
              vấn {{ $data['interviewer'] }} như sau:
            </p>
            <b>Điểm mạnh: </b>
            <p style="white-space: pre-line; text-align: justify; margin-top: 2px">{{ $data['advantages'] }}</p>
            <b>Điểm yếu: </b>
            <p style="white-space: pre-line; text-align: justify; margin-top: 2px">{{ $data['disadvantages'] }}</p>
            <b>Định hướng: </b>
            <p style="white-space: pre-line; text-align: justify; margin-top: 2px">{{ $data['orientation'] }}</p>
            <b>Comment: </b>
            <p style="white-space: pre-line; text-align: justify; margin-top: 2px">{{ $data['comment'] }}</p>
            <b>Đề xuất tuyển dụng: </b>
            <p style="text-align: justify; margin-top: 2px">
              {{ $data['is_agree_hire'] }}
            </p>
            <b>Lương đề xuất: </b>
            <p style="text-align: justify; margin-top: 2px">
              {{ $data['proposed_salary'] }}
            </p>
            <p>Để biết thêm thông tin chi tiết về kiến thức chuyên môn của ứng viên Anh/Chị vui lòng xem
              <a href="{{ config('services.hrm_url').'/hrm/management/candidate/detail/'.$data['candidate_id'] }}">Tại đây</a>.
            </p>
            <p>
              Trân trọng.
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