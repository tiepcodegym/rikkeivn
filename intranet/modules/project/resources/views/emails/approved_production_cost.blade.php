<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>
    Dự án <strong>{{ $data['projectName'] }}</strong> vừa có thay đổi về đơn giá.
    <br>
    Xem chi tiết tại <a href="{{ $data['link'] }}">Link</a>
</p>
@endsection