<?php
use Carbon\Carbon;
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
    <p><strong>Dear anh/chị,</strong></p>
    <p>Đăng ký giảng viên thành công</p>
    <p>Xin cảm ơn.</p>
@endsection
