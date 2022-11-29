<?php
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p>hành trinh kết nối giữa <b>{{ $data['receive_name'] }} </b> và <b>{{ $data['mentee_name'] }}</b> sắp kết thúc</p>
    <p>Vui lòng đánh giá người đồng hành cùng tại <a href="{{ $data['link'] }}" target="_blank">tại đây</a>.</p><br>
    <p>Liên hệ khi có lỗi hệ thống hoặc cần hướng dẫn sử dụng thêm: <b>daotao@rikkeisoft.com</b></p><br>
@endsection
