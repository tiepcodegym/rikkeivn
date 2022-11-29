<?php
use Rikkei\Core\Model\EmailQueue;
$member = $data['member'];
$projName = $data['projName'];
$comment = $data['comment'];
$linkDetail = $data['link'];
$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p>{{ trans('project::email.dear') }} <b>{{ $member }}</b>,</p>
    <p>{{ trans('project::email.contentToMember', ['projName' => $projName])}}</p>
    <div style="margin-top: 20px">
        <p>{!! $comment  !!}</p>
    </div>
    <p>{{ trans('project::email.view_detail', ['link' => $linkDetail]) }}</p>
    <p><b>Intranet team.</b></p>
@endsection
