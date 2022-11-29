<html>

<head>
</head>

<body style="font-family: Arial, Helvetica, sans-serif;">
  <div style="background: #F4F4F4; min-height: 100vh;">
    <div style="height: 100px"></div>
    <div style="width: 930px; margin: auto;">
      <table cellpadding="0" cellspacing="0">
        <tr>
          <td>
            <div style="background: #FFFFFF; padding: 10px 40px;">
              <p>Xin chào Anh/Chị,</p>
              <p>
                Hệ thống gửi đến Anh/Chị thông tin nhân sự sắp hết hạn hợp đồng chưa được xử lý trong tháng {{ $data['month'] }}.
              </p>
              <p>
                Anh/Chị vui lòng kiểm tra thông tin và xét duyệt gia hạn hợp đồng theo danh sách trước ngày kết thúc hợp
                đồng bằng cách truy cập trang Quản lý hợp đồng sắp hết hạn 
                <a href="{{ config('services.hrm_url').'/hrm/accounting-administration/contract/upcoming-contract-expire' }}"
                  style="color: #0054C5; text-decoration: none; cursor: pointer;">Tại đây</a>.
              </p>
              <table style="font-size: 14px; width: 100% ; border: 0.5px solid #999999; border-collapse: collapse;">
                <tr style="background-color:#f3f3f3;">
                  <th style="text-align: center; border-collapse: collapse; padding: 10px 5px 10px 10px;">STT</th>
                  <th style="min-width: 120px; text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">Họ tên nhân sự</th>
                  <th style="min-width: 100px; text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">
                    <p style="margin: 0px;">Account</p>
                    <p style="margin-top: 3px; margin-bottom: 0px;">Mã nhân sự</p>
                  </th>
                  <th style="width: 60px; text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">Vị trí</th>
                  <th style="min-width: 100px; text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">Ngày bắt đầu</th>
                  <th style="min-width: 100px; text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">Ngày kết thúc</th>
                  <th style="min-width: 100px; text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">Loại & trạng thái HĐ</th>
                </tr>
                @foreach ($data['employee_contracts'] as $index => $employee_contract)
                <tr style="border-top: 0.5px solid #999999;">
                  <td style="text-align: center; border-collapse: collapse; padding: 10px 5px 10px 10px;">{{ $index + 1 }}</td>
                  <td style="text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">{{ $employee_contract['employee_name'] }}</td>
                  <td style="text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">
                    <p style="margin: 0px;">{{ $employee_contract['account'] }}</p>
                    <p style="margin-top: 3px; margin-bottom: 0px;">{{ $employee_contract['code'] }}</p>
                  </td>
                  <td style="text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">{{ $employee_contract['role'] }}</td>
                  <td style="text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">{{ $employee_contract['contract_start_date'] }}</td>
                  <td style="text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">{{ $employee_contract['contract_end_date'] }}</td>
                  <td style="text-align: left; border-collapse: collapse; padding: 10px 5px 10px 10px;">
                    <p style="margin: 0px;">{{ $employee_contract['contract_type_name'] }}</p>
                    <p style="margin-top: 3px; margin-bottom: 0px;">{{ $employee_contract['contract_type_year'] }}</p>
                  </td>
                </tr>
                @endforeach
              </table>
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