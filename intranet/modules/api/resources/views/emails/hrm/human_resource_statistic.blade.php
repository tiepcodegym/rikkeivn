<?php
use Carbon\Carbon;
$previousMonth = Carbon::now()->subMonth()->month;
$year = Carbon::now()->subMonth()->year;
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
    <p>Hệ thống HRM gửi đến Anh/Chị Báo cáo nhân sự trong tháng {{ $previousMonth }}/{{ $year }}:</p>
    <ul>
        <li>Số lượng nhân viên tuyển mới: {{ $data['total_join'] }}</li>
        <li>Số lượng nhân viên nghỉ việc: {{ $data['total_leave'] }}</li>
        <li>Tỉ lệ biến động nhân sự (Turnover rate): {{ $data['ratio_leave'] }}</li>
    </ul>
    <p></p>
    <p>Danh sách nhân viên tuyển mới trong tháng {{ $previousMonth }}/{{ $year }}:</p>
    <table>
        <tr>
            <th>Họ và Tên</th>
            <th>Bộ phận</th>
            <th>Ngày vào</th>
        </tr>
        @foreach ($data['list_new_employee'] as $new_employee)
        <tr>
            <td>{{ $new_employee['name'] }}</td>
            <td>{{ $new_employee['team_name'] }}</td>
            <td>{{ $new_employee['join_date'] }}</td>
        </tr>
        @endforeach
    </table>

    <p>Danh sách nhân viên nghỉ việc trong tháng {{ $previousMonth }}/{{ $year }}:</p>
    <table>
        <tr>
            <th>Họ và Tên</th>
            <th>Bộ phận</th>
            <th>Ngày nghỉ</th>
        </tr>
        @foreach ($data['list_leave_employee'] as $leave_employee)
        <tr>
            <td>{{ $leave_employee['name'] }}</td>
            <td>{{ $leave_employee['team_name'] }}</td>
            <td>{{ $leave_employee['leave_date'] }}</td>
        </tr>
        @endforeach
    </table>
    <p>Chi tiết vui lòng xem tại: <a href="https://hrm.rikkei.vn/hrm/dashboard/back-office">https://hrm.rikkei.vn/hrm/dashboard/back-office</a></p>
    <p>Trân trọng!</p>
</div>
</body>
</html>
