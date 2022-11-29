<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')


<p>{{trans('project::email.Hello')}} <strong>{{ isset($data['pm_name'])?$data['pm_name']:'' }}</strong>,</p> 
<p>{{trans('project::email.Remind project report content email')}} </p>
<p>{{trans('project::email.Project need report')}}: </p>
@if(isset($data['projects']))
@foreach ($data['projects'] as $item)
<p>-&nbsp;<a href="{{ $item['route'] }}">{{$item['name']}}</a></p>
@endforeach
@endif
<p>{{ trans('project::email.Thanks')}}</p>
<br/>
-----------------------------------------

@endsection
