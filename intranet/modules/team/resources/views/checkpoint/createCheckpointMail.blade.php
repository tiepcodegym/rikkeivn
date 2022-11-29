<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<div style="line-height: 20px;">
    <p>{{ trans('team::view.Checkpoint.Create.Mail hello') }}</p>
    <p>{!!trans('team::view.Checkpoint.Create.Mail description', ['checkpoint_time' => $data['checkTime']])!!}</p>
    <p>{!!trans('team::view.Checkpoint.Create.Mail description 2', ['team' => $data['team'], 'start' => $data['start'], 'end' => $data['end']])!!}</p>
    <a href="{{$data['urlWelcome']}}">{{$data['urlWelcome']}}</a>
    <p>{!!trans('team::view.Checkpoint.Create.Mail guide')!!}</p>
    <p>{!!trans('team::view.Checkpoint.Create.Mail best regards')!!}</p>
</div>
@endsection
