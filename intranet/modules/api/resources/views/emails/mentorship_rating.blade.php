<?php
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p><b>Dear </b>{{ $data['receive_name'] }}</p>
    <p>Người đồng hàn cùng bạn <b>{{ $data['send_name'] }}</b> đã đánh giá bạn<b> {{ $data['rating'] }}/5 sao</b> trong cuộc hành trình của 2 người.</p>
    <p>Lời nhắn: {{ $data['feedback'] }}
    @if($data['feedback_custom'] == 0)
    @else
    <p>Nội dung trao đổi: {{ $data['feedback_custom'] }}</p>
    @endif
    <p>Liên hệ khi có lỗi hệ thống hoặc cần hướng dẫn sử dụng thêm: <b>daotao@rikkeisoft.com</b></p><br>
    <p>Chúc bạn thành công!</p>
@endsection
