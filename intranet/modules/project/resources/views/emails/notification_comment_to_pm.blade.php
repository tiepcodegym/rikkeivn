<?php
use Rikkei\Core\Model\EmailQueue;
$pm = $data['pm'];
$projName = $data['projName'];
$comment = $data['comment'];
$linkDetail = $data['link'];
$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p>{{ trans('project::email.dear') }} <b>{{ $pm }}</b>,</p>
    <p>{{ trans('project::email.contentToPM', ['projName' => $projName])}}</p>
    <div style="margin-top: 20px">
        <p>{!! $comment  !!}</p>
    </div>
    <p>{{ trans('project::email.view_detail', ['link' => $linkDetail]) }}</p>
    <p><b>Intranet team.</b></p>
@endsection
