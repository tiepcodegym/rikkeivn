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
                <p>Chào Anh/Chị,</p>
                <p>
                    Bộ phận hỗ trợ đã giải quyết yêu cầu đổi quà của anh/chị. Anh/Chị nhận được quà tặng:
                </p>
                <p>
                    <b>Phần quà: {{ $data['gift_name'] }}</b>
                </p>
                <p>
                    <b>Số lượng: </b>{{ $data['quantity'] }}
                </p>
                <p>
                    <b>Chi tiết: </b>
                </p>
                <p style="white-space: pre-line;">{{ $data['detail'] }}</p>
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