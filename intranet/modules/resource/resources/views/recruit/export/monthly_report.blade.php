<?php
use Rikkei\Core\View\CoreUrl;
?>
<html>
<head>
    <meta content="charset=utf-8">
    <link rel="stylesheet" href="{!! CoreUrl::asset('resource/css/monthly_report.css') !!}">
    <style>
        .text-center {text-align: center}
        .text-left {text-align: left}
        .text-bold {font-weight: bold}
    </style>
</head>
<body>
<table>
    <thead>
    <tr>
        <th rowspan="2" class="text-center">{!! trans('resource::view.Employee') !!}</th>
        @foreach ($selectedChannels as $channel)
            <th colspan="4" class="text-center bg-header-table">{{ $channel->name }}</th>
        @endforeach
    </tr>
    <tr>
        <th></th>
        @foreach ($selectedChannels as $channel)
            @php
                if (!isset($groupChannels[$channel->id])) {
                    $groupChannels[$channel->id] = [
                        'id' => $channel->id,
                        'name' => $channel->name,
                        'color' => $channel->color,
                        'recruiters' => [],
                        'total' => 0,
                        'fail' => 0,
                        'pass' => 0,
                        'offer' => 0,
                    ];
                }
            @endphp
            <th class="text-center" height="35">
                <span>{!! trans('resource::view.Total') !!}</span><br>
                <span>({!! $groupChannels[$channel->id]['total'] !!})</span>
            </th>
            <th class="text-center" height="35">
                <span>{!! trans('resource::view.Fail') !!}</span><br>
                <span>({!! $groupChannels[$channel->id]['fail'] !!})</span>
            </th>
            <th class="text-center" height="35">
                <span>{!! trans('resource::view.Pass') !!}</span><br>
                <span>({!! $groupChannels[$channel->id]['pass'] !!})</span>
            </th>
            <th class="text-center" height="35">
                <span>{!! trans('resource::view.Offer') !!}</span><br>
                <span>({!! $groupChannels[$channel->id]['offer'] !!})</span>
            </th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach ($recruiters as $recruiter)
        <tr>
            <td class="text-left">{{ $recruiter->name }}</td>
            @foreach ($selectedChannels as $channel)
                @php
                    if (!isset($groupChannels[$channel->id]['recruiters'][$recruiter->email])) {
                        $groupChannels[$channel->id]['recruiters'][$recruiter->email] = [
                            'total' => 0,
                            'fail' => 0,
                            'pass' => 0,
                            'offer' => 0,
                        ];
                    }
                    $data = $groupChannels[$channel->id]['recruiters'][$recruiter->email];
                @endphp
                <td class="text-center text-bold">{!! $data['total'] !!}</td>
                <td class="text-center text-bold color-red">{!! $data['fail'] !!}</td>
                <td class="text-center text-bold color-blue">{!! $data['pass'] !!}</td>
                <td class="text-center text-bold color-green">{!! $data['offer'] !!}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
