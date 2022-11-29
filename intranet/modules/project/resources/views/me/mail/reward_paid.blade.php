<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
use Rikkei\Project\Model\MeReward;
extract($data);
?>
@extends($layout)

@section('css')
<style>
    table._me_reward_table {
        border-collapse: collapse;
        width: 100%;
    }
    table._me_reward_table tr td, table._me_reward_table tr th {
        padding: 7px 12px;
        border: 1px solid #ddd;
    }
</style>
@endsection

@section('content')

@if(isset($dear_name) && $dear_name)
<p>Dear <strong>{{ $dear_name }}</strong>,</p>
@endif

<p>&nbsp;</p>
<p>Reward ME OSDC of 
    @if ($is_team_leader)
        team <strong>{{ $proj_team_name }}</strong>
    @else
        project(s) <strong>[{{ $proj_team_name }}]</strong>
    @endif
    in <strong>{{ $month_format }}</strong> is paid.</p>

@if ($data_change)
<p>Items detail:</p>
<table class="_me_reward_table">
    <thead>
        <tr>
            <th>No.</th>
            <th>Acount</th>
            <th>Project</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data_change as $order => $item)
        <tr>
            <td>{{ ($order + 1) }}</td>
            <td>{{ $item['account'] }}</td>
            <td style="text-align: right;">{{ $item['project_name'] }}</td>
            <td>Paid</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if ($is_team_leader)
<p><a href="{{ $detail_link }}" style="color: #15c">View detail</a></p>
@endif

<p>&nbsp;</p>
<p>Thanks and regard,</p>
<div><strong>{{ $submit_name }}</strong></div>

@endsection
