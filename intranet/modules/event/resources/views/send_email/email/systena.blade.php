<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();

?>
@extends($layout)
@section('content')
<div>
    <p>Dear {{ $data['userCurrent']->name }},</p>
    @if (isset($data['error']))
    	<p>Bảng công systena của nhân viên trong tháng {{ $data['month'] }} {{ $data['error']}}
    @else
    	<p>Hệ thống xin gửi tới bảng công systena của nhân viên trong tháng {{ $data['month'] }}
    @endif
    <p>Xin cảm ơn.</p>
</div>
@endsection