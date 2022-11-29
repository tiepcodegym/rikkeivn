<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        table tr td {
            border: 1px solid #0a0a0a;
            text-align: center;
        }
    </style>
</head>
<body>
<table>
    <tr class="offset">
        <td>
            {{ trans('education::view.No.') }}
        </td>
        <td>
            {{ trans('education::view.Employee code') }}
        </td>
        <td>
            {{ trans('education::view.Employee Name') }}
        </td>
        <td>
            {{ trans('education::view.Team') }}
        </td>
        <td>
            {{ trans('education::view.Project') }}
        </td>
        <td>
            {{ trans('education::view.OT in week') }}
        </td>
        <td>
            {{ trans('education::view.OT end week') }}
        </td>
        <td>
            {{ trans('education::view.OT in holidays') }}
        </td>
        <td>
            {{ trans('education::view.OT exchange') }}
        </td>
    </tr>
    @foreach($data as $key => $item)
        <tr class="offset">
            <td>{{ $key + 1 }}</td>
            <td>{{ $item->employee_code }}</td>
            <td>{{ $item->employee_name }}</td>
            <td>{{ $item->teams }}</td>
            <td>{{ $item->project_name }}</td>
            <td>{{ round($item->ot_in_week, 2) }}</td>
            <td>{{ round($item->ot_end_week, 2) }}</td>
            <td>{{ round($item->ot_holidays_week, 2) }}</td>
            <td>{{ round(($item->ot_in_week * 1.5 + $item->ot_end_week * 2 + $item->ot_holidays_week * 3), 2) }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>



