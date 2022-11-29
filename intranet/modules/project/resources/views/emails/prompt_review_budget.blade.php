<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>Dear {{ $data['leaderInfo']['leaderName'] }},</p>
<p>&nbsp;</p>

<p>The projects below have been started for over a week ago, but have not been reviewed reward budget. Please review them!</p>
<div style="line-height: 20px;">
    <ol>
    @foreach ($data['projInfo'] as $project)
        <li>
            <a href="{{ $project['urlProject'] }}" target="_blank" style="color: #15c">{{ $project['projectName'] }}</a>
        </li>
    @endforeach
    </ol>
</div>
@endsection
