<?php
use Rikkei\Core\Model\EmailQueue;
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p>Dear {{ $data['dear_name'] }},</p>
<p>Skillsheet của nhân viên {{ $data['employee_name'] . ' (' . $data['employee_account'] . ')'}}
đã được {{ $data['username'] . ' (' . $data['useraccount'] . ')'}} 
assign cho bạn để review và approve.</p>

<p>Link: <a href="{{ $data['link'] }}">{{ $data['link'] }}</a></p>
@endsection
