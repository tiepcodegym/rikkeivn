<?php
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p><b>Dear </b>{{ $data['receive_name'] }}</p>
    <p>Đã quá 7 ngày chưa có nhiệm vụ mới cho Mentee <b>{{ $data['mentee_name'] }}</b></p>
    <p>Vui lòng xem thông tin chi tiết  <a href="{{ $data['link'] }}" target="_blank">tại đây</a>.</p><br>
    <p>Liên hệ khi có lỗi hệ thống hoặc cần hướng dẫn sử dụng thêm: <b>daotao@rikkeisoft.com</b></p><br>
@endsection
