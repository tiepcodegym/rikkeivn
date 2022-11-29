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
            {{ trans('project::view.No.') }}
        </td>
        <td>
            {{ trans('project::view.Project') }}
        </td>
        <td>
            {{ trans('project::view.Group Leader') }}
        </td>
        <td>
            {{ trans('project::view.List members') }}
        </td>
        <td>
            {{ trans('project::view.Team') }}
        </td>
        <td>
            {{ trans('project::view.Position') }}
        </td>
        <td>
            {{ trans('project::view.Start date') }}
        </td>
        <td>
            {{ trans('project::view.End Date') }}
        </td>
        <td>
            {{ trans('project::view.Effort(%)') }}
        </td>
        <td>
            {{ trans('project::view.Calendar Eff(MM)') }}
        </td>
    </tr>
    @foreach($data as $key => $value)
        @if(count($value['member']) != 0 )
            @foreach(array_values($value['member']) as $key1 => $member)
                @if ($key1 === 0)
                    <tr class="offset">
                        <td rowspan="{!! $value['count'] !!}">{{ $key + 1 }}</td>
                        <td rowspan="{!! $value['count'] !!}">{{ $value['project_name'] }}</td>
                        <td rowspan="{!! $value['count'] !!}">{{ $value['leader_name'] }}</td>
                        <td rowspan="{!! count($member['allocate']) + 1 !!}">{{ $member['member_name'] }}</td>
                        <td rowspan="{!! count($member['allocate']) + 1 !!}">{{ $member['team_names'] }}</td>
                        <td>{{ $member['type'] }}</td>
                        <td>{{ $member['start_at'] }}</td>
                        <td>{{ $member['end_at'] }}</td>
                        <td>{{ $member['effort'] }}</td>
                        <td>{{ $member['flat_resource'] }}</td>
                    </tr>
                @else
                    <tr class="offset">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td rowspan="{!! count($member['allocate']) + 1 !!}">{{ $member['member_name'] }}</td>
                        <td rowspan="{!! count($member['allocate']) + 1 !!}">{{ $member['team_names'] }}</td>
                        <td>{{ $member['type'] }}</td>
                        <td>{{ $member['start_at'] }}</td>
                        <td>{{ $member['end_at'] }}</td>
                        <td>{{ $member['effort'] }}</td>
                        <td>{{ $member['flat_resource'] }}</td>
                    </tr>
                @endif
                @if(count($member['allocate']))
                @foreach(array_values($member['allocate']) as $key1 => $allocate)
                    <tr class="offset">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $allocate['type'] }}</td>
                        <td>{{ $allocate['start_at'] }}</td>
                        <td>{{ $allocate['end_at'] }}</td>
                        <td>{{ $allocate['effort'] }}</td>
                        <td>{{ $allocate['flat_resource'] }}</td>
                    </tr>
                @endforeach
                @endif
            @endforeach
        @endif
    @endforeach
    @if(isset($data[0]['total']))
        <tr>
            <td colspan="7"></td>
            <td style="font-weight: bold;">Total</td>
            <td>{{ $data[0]['total'] }}</td>
        </tr>
    @endif
</table>
</body>
</html>



