
<?php
use Rikkei\Team\Model\Team
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        table tr th {
            border: 1px solid #0a0a0a;
            text-align: center;
        }
    </style>
</head>
<body>
<table>
    <tr class="offset">
        <th>
            Tháng
        </th>
        <th>
            Division
        </th>
{{--        <th>--}}
{{--            Role--}}
{{--        </th>--}}
{{--        <th>--}}
{{--            Số MM--}}
{{--        </th>--}}
{{--        <th>--}}
{{--            Đơn giá--}}
{{--        </th>--}}
{{--        <th>--}}
{{--            Doanh thu--}}
{{--        </th>--}}
{{--        <th>--}}
{{--            Tổng MM--}}
{{--        </th>--}}
{{--        <th>--}}
{{--            Tổng doanh thu--}}
{{--        </th>--}}
    </tr>
        @foreach($data as $row)
        <tr style="text-align: center; vertical-align: middle" >

                <td @if(count($row['detail']) > 1) rowspan="{{ count($row['detail']) }}" @endif>{{ count($row['detail']) }}</td>
           
            <td>{{ Team::getTeamNameById($row["team_id"]) }}</td>
{{--            <td>{{ $item["role"] }}</td>--}}
{{--            <td>{{ $item["approved_production_cost"] }}</td>--}}
{{--            <td>{{ $item["price"] }}</td>--}}
{{--            <td>{{ $item["approved_production_cost"]*$item["price"] }}</td>--}}
{{--            <td>Tổng MM</td>--}}
{{--            <td>Tổng doanh thu</td>--}}
        </tr>
        @endforeach
</table>
</body>
</html>