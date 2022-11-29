<table>
    <thead>
        <tr bgcolor="#C4C4C4">
            <th>{{ trans('project::view.Team') }}</th>
            <th>{{ trans('project::view.ID Employee') }}</th>
            <th>{{ trans('project::view.Name Employee') }}</th>
            <th>{{ trans('project::view.Reward Level (k)') }}</th>
            <th>{{ trans('project::view.Reward Reason') }}</th>
            <th>{{ trans('project::view.Comment') }}</th>
            <th>{{ trans('project::view.Reward Total') }} (K)</th>
        </tr>
    </thead>
    <tbody>
    @if($result)
        <?php $totalReward = 0; ?>
        @foreach ($result as $team)
        <?php 
            $flteam = true; 
            $totalReward += $team['team_reward'];
        ?>
            @foreach ($team['emp'] as $emp)
            <?php $flemp = true; ?>
                @foreach ($emp['reason'] as $reason)
                <tr>
                    @if ($flteam)
                        <td rowspan="{{$team['team_line']}}">{{$team['team_name']}}</td>
                    @endif
                    @if ($flemp)
                    <td rowspan="{{$emp['emp_line']}}">{{ $emp['emp_code'] }}</td>
                    <td rowspan="{{$emp['emp_line']}}">{{ $emp['emp_name'] }}</td>
                    <td rowspan="{{$emp['emp_line']}}"><strong>{{ $emp['emp_reward'] }}</strong></td>
                    <?php $flemp = false; ?>
                    @endif
                    <td>{{$reason['reason']}}</td>
                    <td>{{$reason['comment']}}</td>
                    @if ($flteam)
                        <td rowspan="{{$team['team_line']}}"><strong>{{ $team['team_reward'] }}</strong></td>
                        <?php $flteam = false; ?>
                    @endif
                </tr>
                @endforeach
            @endforeach
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ trans('project::view.Total (k):') }}</td>
            <td><strong>{{$totalReward}}</strong></td>
        </tr>
    @endif
    </tbody>
</table>