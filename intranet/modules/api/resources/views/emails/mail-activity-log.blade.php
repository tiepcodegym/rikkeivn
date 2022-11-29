<!DOCTYPE html>
<html>

<head>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
    </style>
</head>

<body>
<h2>Thông tin Activity log của nhân viên: {{ $data['employeeName'] }}</h2>
<table style="width:100%">
    <tr>
        <th>Họ và Tên</th>
        <th>email</th>
        <th>Mentor</th>
        <th>Thời gian diễn ra</th>
        <th>Nội dung trao đổi</th>
        <th>Next action</th>
    </tr>
    <tr>
        <td>{{ $data['employeeName'] }}</td>
        <td>{{ $data['employeeEmail'] }}</td>
        <td>{{ $data['mentor'] }}</td>
        <td>{{ date('Y-m-d g:i a', strtotime($data['action_time'])) }} - {{ date('Y-m-d g:i a', strtotime($data['end_time'])) }}</td>
        <td>{!! str_replace("\n", '<br>', $data['content']) !!}</td>
        <td>{!! str_replace("\n", '<br>', $data['description']) !!}</td>
    </tr>
</table>
</body>
</html>
