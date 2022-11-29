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
    <p>Tháng {{ $nextMonth }}/{{ $nextYear }} có sinh nhật của các nhân viên sau:</p>
    <table>
        <tr>
            <th>Họ và Tên</th>
            <th>Ngày sinh</th>
            <th>Bộ phận</th>
        </tr>
        @foreach ($data['list_birthday'] as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['birthday'] }}</td>
            <td>{{ $item['team_name'] }}</td>
        </tr>
        @endforeach
    </table>
    <p></p>
    <p>Chi tiết vui lòng xem tại: <a href="https://hrm.rikkei.vn/hrm/dashboard/back-office">Danh sách nhân sự sắp có sinh nhật</a></p>
    <p></p>
    <p>Đây là email tự động thông báo lịch sinh nhật của nhân viên để các bộ phận liên quan có sự chuẩn bị tốt hơn, vui lòng không reply.</p>
    <p>Trân trọng!</p>
</div>
</body>
</html>
