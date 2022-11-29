<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        table tr td {
            border: 1px solid #0a0a0a;
            text-align: center;
        }
        table tr:first-child {
            font-weight: bold;
        }
    </style>
</head>
<body>
<table>
    <tr class="offset">
        <td>
            {{ trans('project::view.No.') }}
        </td>
        <td>
            {{ trans('project::view.Employee') }}
        </td>
        @foreach($months as $month => $val)
            <td>
                {{ $val }}
            </td>
        @endforeach
        <td style="font-weight: bold;">
            {{ trans('project::view.Sum') }}
        </td>
    </tr>
    <?php $i = 0; ?>
    @foreach ($data as $mm => $val)
        <tr class="offset">
            <td>{{ $i++ }}</td>
            @foreach ($collection as $key => $value)
                @if (strval($mm) === $value['employee_id'])
                    <td>{{ $value['name'] }}</td>
                    @foreach ($val as $mm)
                    <td>{{ $mm }}</td>
                    @endforeach
                    <td>{{ $sum[(int)$value['employee_id']] }}</td>
                @endif
            @endforeach
        </tr>
    @endforeach
    <tr>
    <td style="font-weight: bold;" colspan=2>Sum</td>
    @foreach($sumMonth as $sum => $key)
        <td style="font-weight: bold;">{{ $key }}</td>
    @endforeach
    </tr>
</table>
</body>
</html>