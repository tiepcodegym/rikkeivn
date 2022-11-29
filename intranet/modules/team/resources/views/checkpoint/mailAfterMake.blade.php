<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>{{trans('team::view.Checkpoint.Email.Hello',["name" => $data['name']])}} </p>

<p>{{trans('team::view.Checkpoint.Email.Bài checkpoint của bạn vừa được đánh giá', ['check_time' => $data['checkTime'], 'name' => $data['reviewerName']]) }} </p>

<p>{{trans('team::view.Checkpoint.Email.Checkpoint time',["time" => $data['startDate'] . ' - ' . $data['endDate']])}} </p>

<p>{!! trans('team::view.Checkpoint.Email.Point by self', ['point' => $data['totalPoint']]) !!} </p>
<p>{!! trans('team::view.Checkpoint.Email.Point by evaluator', ['point' => $data['leaderTotalPoint']]) !!} </p>
<p>{{trans('team::view.Checkpoint.Email emp.Link view detail') }} </p>

<p><a href="{{$data['href']}}" target="_blank">{{$data['href']}}</a></p>

<p>{{trans('team::view.Checkpoint.Email.Respect')}}</p>
<p>{{trans('team::view.Checkpoint.Email.Product team')}}</p>
@endsection