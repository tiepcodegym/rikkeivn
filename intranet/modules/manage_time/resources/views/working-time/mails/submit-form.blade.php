<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
extract($data);
?>
@extends($layout)

@section('content')

<p>Xin chào <strong>{{ $dearName }}</strong>,</p>

<p>&nbsp;</p>
<p>Đơn đăng ký thay đổi giờ làm việc của nhân viên {{ $employeeName . ' (' . $employeeAccount . ')' }} 
    {{ $isUpdate ? 'đã được cập nhật' : 'đã được tạo' }} vui lòng xét duyệt</p>
<p>&nbsp;</p>

@if (isset($detailLink))
    <p><a href="{{ $detailLink }}" style="color: #15c">Xem chi tiết</a></p>
@endif

@endsection
