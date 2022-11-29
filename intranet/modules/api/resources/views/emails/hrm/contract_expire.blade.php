<?php
use Carbon\Carbon;
$nextMonth = Carbon::now()->addMonth()->month;
$nextYear = Carbon::now()->addMonth()->year;
?>
<html>

<head>
    <style>
        table, th, td {
		  border: 1px solid black;
		}

		table {
		  width: 100%;
		}
    </style>
</head>

<body>
<div class="body">
    <p>Dear Anh/Chị,</p>
    <p>Danh sách Hợp đồng lao động sẽ hết hạn trong tháng {{ $nextMonth }}/{{ $nextYear }} bao gồm các nhân viên sau:</p>
    <table>
        <tr>
            <th>Họ và Tên</th>
            <th>Ngày hết hạn hợp đồng</th>
            <th>Bộ phận</th>
            <th>Loại hợp đồng</th>
        </tr>
        @foreach ($data['list_expired_contract'] as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['contract_end_at'] }}</td>
            <td>{{ $item['team_name'] }}</td>
            <td>{{ $item['contract_type'] }}</td>
        </tr>
        @endforeach
    </table>
    <p>Chi tiết vui lòng xem tại: <a href="https://hrm.rikkei.vn/hrm/dashboard/back-office">Danh sách nhân sự sắp hết hạn hợp đồng</a></p>
    <p></p>
    <p>Anh/Chị vui lòng trao đổi với nhân viên về việc gia hạn HĐLĐ và xác nhận với bộ phận nhân sự, kế toán theo thời gian như sau:</p>
    <p>1. HĐLĐ xác định thời hạn: trước ngày 15/{{ $nextMonth }}/{{ $nextYear }}.</p>
    <p>2. HĐLĐ Thử việc & học việc: trước 5 ngày hết hạn của HĐ hiện tại.</p>
    <p>Trân trọng!</p>
</div>
</body>
</html>
