<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>
{{ trans('project::view.Dear Mr/Ms') }} {{ $data['name'] }},
</p>

<p>Một risk với nội dung <strong>{{ $data['riskContent'] }}</strong> vừa được {{ $data['isCreated'] ? 'sửa' : 'tạo mới' }} trong dự án <strong>{{ $data['projectName'] }}</strong>.</p>

<p>Mời bạn truy cập vào đường link phía dưới để biết thêm thông tin.</p>
<p><a href="{{ $data['url'] }}" style="color: #15c">{{ $data['url'] }}</a></p>
@endsection