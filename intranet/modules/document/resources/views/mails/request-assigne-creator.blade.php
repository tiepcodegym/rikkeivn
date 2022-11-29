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
<p>Yêu cầu tài liệu <strong>{{ $requestName }}</strong> được tạo bởi <strong>{{ $author }}</strong> đã được assigne cho bạn, vui lòng tạo tài liệu theo yêu cầu.</p>
<p>&nbsp;</p>

@if (isset($detailLink))
    <p><a href="{{ $detailLink }}" style="color: #15c">Xem chi tiết</a></p>
@endif

@endsection
