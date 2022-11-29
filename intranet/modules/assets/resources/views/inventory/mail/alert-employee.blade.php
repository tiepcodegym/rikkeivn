<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
extract($data);
$invHelpId = 109;
$helpLink = route('help::display.help.view', ['id' => $invHelpId]);
?>
@extends($layout)

@section('content')

<p>Xin chào <strong>{{ $dearName }}</strong>, </p>
<p>&nbsp;</p>
<p>Bạn chưa thực hiện <strong>{{ $inventoryName }}</strong>, hãy truy cập <a href="{{ $detailLink }}">{{ $detailLink }}</a> để tiến hành kiểm kê.</p>
<p>&nbsp;</p>
<p>Tham khảo hướng dẫn kiểm kê tại <a href="{{ $helpLink }}">{{ $helpLink }}</a></p>

@endsection