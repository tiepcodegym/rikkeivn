<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
extract($data);
?>
@extends($layout)

@section('content')

@if (isset($dearName))
<p>Xin chào <strong>{{ $dearName }}</strong>,</p>
@endif

<p>&nbsp;</p>
<p>Tài liệu <strong>{{ $docTitle }}</strong> đã được assigne cho bạn chỉnh sửa, vui lòng cập nhật tài liệu.</p>
<p>&nbsp;</p>

@if (isset($detailLink))
    <p><a href="{{ $detailLink }}" style="color: #15c">Xem chi tiết</a></p>
@endif

@endsection