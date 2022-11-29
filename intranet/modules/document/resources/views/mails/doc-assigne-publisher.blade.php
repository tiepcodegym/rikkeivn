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
<p>Tài liệu <strong>{{ $docTitle }}</strong> tạo bởi <strong>{{ $author }}</strong> đã được review và assigne cho bạn, vui lòng publish tài liệu.</p>
<p>&nbsp;</p>

@if (isset($detailLink))
    <p><a href="{{ $detailLink }}" style="color: #15c">Xem chi tiết</a></p>
@endif

@endsection
