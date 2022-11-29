<?php
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p><b>Dear </b>{{ $data['receive_name'] }}</p>
    <p>Mentee <b>{{ $data['send_name'] }}</b> đã gửi lại kết quả cho nhiệm vụ: <b>{{ $data['mission_issues'] }}</b></p><br>
    <p>Vui lòng xem thông tin chi tiết  <a href="{{ $data['link'] }}" target="_blank">tại đây</a>.</p><br>
    <p>Liên hệ khi có lỗi hệ thống hoặc cần hướng dẫn sử dụng thêm: <b>daotao@rikkeisoft.com</b></p><br>
@endsection
