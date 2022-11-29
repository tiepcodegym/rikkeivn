<?php
use Rikkei\Core\Model\EmailQueue;
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p>Dear {{ $data['dear_name'] }},</p>
<p>Skills sheet của bạn vừa được approve bởi {{$data['approver']}}</p>

<p>Link: <a href="{{ $data['link'] }}">{{ $data['link'] }}</a></p>
@endsection
