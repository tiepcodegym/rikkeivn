<?php
use Carbon\Carbon;
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
    <p><strong>Kính gửi: Phòng Đào tạo và Phát triển năng lực,</strong></p>
    <p>Đã có một yêu cầu giảng dạy từ Anh/Chị: {{ $data['data']['global_name'] }}</p>
    <p>Đào tạo về nội dung: {{ $data['data']['global_content'] }}</p>
    <p>Bộ phận: {{ $data['data']['global_team'] }}</p>
    <p>Thời gian đề xuất:</p>
    <ul>
    @foreach($data['data']['global_time'] as $item)
        <li>{{ $item }}</li>
    @endforeach
    </ul>
    <strong>Trân trọng!</strong>
@endsection
