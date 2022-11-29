<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>
    Dear Mr/Ms {{ $data['name'] }},
</p>

<p>Một NC với tiêu đề <strong>{{ $data['ncTitle'] }}</strong> vừa được cập nhật trạng thái từ <strong>{{ $data['statusOld'] }}</strong> thành <strong>{{ $data['statusNew'] }}</strong> @if ($data['projectName']) trong dự án <strong>{{ $data['projectName'] }}</strong> @endif .</p>

<p>Mời bạn truy cập vào đường link phía dưới để biết thêm thông tin.</p>
<p><a href="{{ $data['url'] }}" style="color: #15c">{{ $data['url'] }}</a></p>
@endsection