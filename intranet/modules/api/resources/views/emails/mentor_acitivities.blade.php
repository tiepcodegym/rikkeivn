<?php
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p><b>Dear </b>{{ $data['receive_name'] }}</p>
    <p>Mentor <b>{{ $data['send_name'] }}</b> đã giao 1 nhiệm vụ cho bạn.</p><br>
    <p>Vui lòng xem thông tin chi tiết <a href="{{ $data['link'] }}" target="_blank">tại đây</a>.</p><br>
    <p>Liên hệ khi có lỗi hệ thống hoặc cần hướng dẫn sử dụng thêm: <b>daotao@rikkeisoft.com</b></p><br>
    <p>Chúc bạn thành công!</p>
@endsection
