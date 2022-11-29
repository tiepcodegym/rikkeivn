<?php
use Rikkei\Core\Model\EmailQueue;
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p>Dear {{ $data['dear_name'] }},</p>
<p>Skillsheet của bạn vừa được feedback bởi {{$data['assigner']}}</p>

<p>Link: <a href="{{ $data['link'] }}">{{ $data['link'] }}</a></p>
@endsection
