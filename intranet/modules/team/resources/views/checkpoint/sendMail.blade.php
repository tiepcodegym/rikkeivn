<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>{{trans('team::view.Checkpoint.Email.Hello',["name" => $data['empName']])}} </p>

<p>{{trans('team::view.Checkpoint.Email.Notifical') }} </p>
<p>{{trans('team::view.Checkpoint.Email.Make name', ['name' => $data['name']]) }} </p>
<p>{{trans('team::view.Checkpoint.Email.Make date',["date" => date('d/m/Y')])}} </p>

<p>{{trans('team::view.Checkpoint.Email.Checkpoint time',["time" => $data['startDate'] . ' - ' . $data['endDate']])}} </p>

<p>{!! trans('team::view.Checkpoint.Email.Point', ['point' => $data['totalPoint']]) !!} </p>
<p></p>
<p>{{trans('team::view.Checkpoint.Email.Link view detail') }} </p>
<p><a href="{{$data['href']}}" target="_blank">{{$data['href']}}</a></p>
<p style="line-height: 20px;">{!! trans('team::view.Checkpoint.Email.Review guide') !!}</p>

<p>{{trans('team::view.Checkpoint.Email.Respect')}}</p>
<p>{{trans('team::view.Checkpoint.Email.Product team')}}</p>
@endsection