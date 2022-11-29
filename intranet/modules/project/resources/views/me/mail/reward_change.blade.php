<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
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
<p>Reward ME OSDC in <strong>{{ $month_format }}</strong> 
    @if ($proj_name)
    of project <strong>[{{ $proj_name }}]</strong> 
    @endif
    <?php 
    $actionText = ' changed and submited'; 
    if (isset($is_approved)) {
        $actionText = ' approved';
        if ($is_change) {
            $actionText = 'changed and approved';
        }
    }
    echo $actionText;
    ?>.</p>

@if ($data_change)
<p>Reward changed:</p>
<table class="_me_reward_table">
    <thead>
        <tr>
            <th>No.</th>
            <th>Acount</th>
            <th>Project</th>
            <th>Old reward (đ)</th>
            <th>New reward (đ)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data_change as $order => $item)
        <tr>
            <td>{{ ($order + 1) }}</td>
            <td>{{ $item['account'] }}</td>
            <th>{{ $item['proj_name'] }}</th>
            <td style="text-align: right;">{{ $item['old_reward'] }}</td>
            <td style="text-align: right;">{{ $item['new_reward'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<!--<p><a href="{{ route('project::me.reward.review', ['team_id' => '', 'time' => $month]) }}" style="color: #15c">View detail</a></p>-->

<p>&nbsp;</p>
<p>Thanks and regard,</p>
<div><strong>{{ $submit_name }}</strong></div>

@endsection
