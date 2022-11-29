<?php
use Rikkei\Core\Model\EmailQueue;
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p>Dear {{ $data['dear_name'] }},</p>
<p>Nhân viên {{ $data['employee_name'] . ' (' . $data['employee_account'] . ')'}} vừa submit
    skill sheet, bạn vui lòng vào review.</p>

<p>Link: <a href="{{ $data['link'] }}">{{ $data['link'] }}</a></p>
@endsection
