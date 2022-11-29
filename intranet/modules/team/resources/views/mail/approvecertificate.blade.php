<?php
use Rikkei\Core\Model\EmailQueue;
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
    @if(!empty($data['status']))
        <p>Dear {{ $data['dear_name'] }},</p>
        <p>{{trans('team::view.Your certificate allowance has just been')}} {{ $data['status'] }} {{trans('team::view.by')}} {{$data['approved']}}</p>
        @if(!empty($data['reason']))
            <p>Lý do: {!! nl2br($data['reason']) !!}</p>
        @endif
    @else
        <p>Dear {{ $data['dear_name'] }},</p>
        <p>{{trans('team::view.The certificate allowance is required to be approved by')}} {{$data['from_name']}}</p>
    @endif

    <p>Link: <a href="{{ $data['link'] }}">{{ $data['link'] }}</a></p>
@endsection
