<?php
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p><b>Dear</b>{{ $data['receive_name'] }}</p>
    <p>Bạn đã trở thành  {{ $data['role'] }} của <b>{{ $data['send_name'] }}</b> trong loại hình huấn luyện <b>{{ $data['type'] }}</b> bắt đầu từ <b>{{ $data['time_start'] }}</b></p><br>
    <p>Vui lòng xem thông tin chi tiết tại đây <a href="{{ $data['link'] }}" target="_blank">{{ $data['link']}}</a> và bắt đầu công việc của quá trình huấn luyện.</p><br>
    <p>Các vướng mắc vui lòng liên hệ <b>{{ $data['send_name'] }} để cùng trao đổi, thống nhất.</b></p><br>
    <p>Liên hệ khi có lỗi hệ thống hoặc cần hướng dẫn sử dụng thêm: <b>daotao@rikkeisoft.com</b></p><br>
    <p>Chúc bạn thành công!</p>
@endsection
