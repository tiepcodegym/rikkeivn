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
<p><strong>{{ $feedbacker }}</strong> đã feedback về request tài liệu <strong>{{ $requestName }}</strong>, vui lòng chỉnh sửa lại.</p>
<p>&nbsp;</p>

@if (isset($detailLink))
    <p><a href="{{ $detailLink }}" style="color: #15c">Xem chi tiết</a></p>
@endif

@endsection
