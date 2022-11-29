<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();

?>
@extends($layout)
@section('content')
<div>
    <p>Dear Phòng HCTH,</p>
    <p>Hệ thống xin gửi tới bảng tính số phút đi muộn của nhân viên</p>
    <p>Thanks</p>
</div>
@endsection