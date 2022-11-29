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
              Nhân sự {{ $data['employee_name'] }} ({{ $data['account'] }}) đã được Dlead duyệt gia hạn hợp đồng kèm thông tin cụ thể như sau:
            </p>
            <p>
              <b>Họ tên nhân sự: </b>{{ $data['employee_name'] }} ({{ $data['account'] }})
            </p>
            <p>
              <b>Mã nhân sự: </b>{{ $data['code'] }}
            </p>
            <p>
              <b>Vị trí: </b>{{ $data['role'] }}
            </p>
            <p>
              <b>Loại hợp đồng: </b>{{ $data['contract_type_name'] }}
            </p>
            <p style="margin-top: 24px;">Anh/Chị vui lòng kiểm tra thông tin và thực hiện thủ tục gia hạn hợp đồng trước ngày {{ $data['contract_end_date'] }}
              <a href="{{ config('services.hrm_url').'/hrm/accounting-administration/contract/upcoming-contract-expire/'.$data['id'] }}"
                style="color:#0054C5 ;text-decoration: none; cursor: pointer;">Tại đây</a>.
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