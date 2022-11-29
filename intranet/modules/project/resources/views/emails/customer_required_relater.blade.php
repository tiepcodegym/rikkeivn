<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>
Chào {{ $data['name'] }},
</p>

<p>Customer contract requirement vừa được cập nhật trong dự án <strong>{{ $data['projectName'] }}</strong>.</p>

<p>Mời bạn truy cập vào đường link phía dưới để biết thêm thông tin.</p>
<p><a href="{{ $data['url'] }}" style="color: #15c">{{ $data['url'] }}</a></p>
@endsection