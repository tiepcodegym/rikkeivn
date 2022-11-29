<?php
use Carbon\Carbon;
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
    <p><strong>Dear anh/chị,</strong></p>
    <p>Nhân viên [{{ $data['data']['global_creator'] }}] đã yêu cầu đào tạo [{{ $data['data']['global_title'] }}]</p>
    <p>Anh/chị có thể xem chi tiết tại link sau: [<a href="{{ $data['data']['global_link'] }}">{{ $data['data']['global_title'] }}</a>]</p>
    <p>Xin cảm ơn.</p>
@endsection