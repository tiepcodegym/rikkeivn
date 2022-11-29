<?php
use Rikkei\Core\Model\EmailQueue;
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p>Dear {{ $data['dear_name'] }},</p>
<p>Nhân viên {{ $data['employee_name'] . ' (' . $data['employee_account'] . ')'}} vừa thêm / thay đổi
mong muốn onsite của họ, bạn vui lòng vào review nó.</p>

<p>Link: <a href="{{ $data['link'] }}">{{ $data['link'] }}</a></p>
@endsection
