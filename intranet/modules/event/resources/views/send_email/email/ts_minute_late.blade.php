<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();

?>
@extends($layout)
@section('content')
<div>
    <p>Dear {{$data['dear_name']}},</p>
    <p>Hệ thống xin gửi tới bảng tính số phút đi muộn của nhân viên 
        {{(isset($data['date']) ? ('tháng ' . $data['date']) : '')}}</p>
    <p>Thanks</p>
</div>
@endsection