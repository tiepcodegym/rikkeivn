<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>
{{ trans('project::view.Dear Mr/Ms') }} {{ $data['name'] }},
</p>

<p>Một issue với nội dung <strong>{{ $data['issueContent'] }}</strong> vừa được {{ $data['isCreated'] ? 'tạo mới' : 'sửa' }} bởi {{ $data['creator'] }} trong dự án <strong>{{ $data['projectName'] }}</strong>.</p>

<p>Mời bạn truy cập vào đường link phía dưới để biết thêm thông tin.</p>
<p><a href="{{ $data['url'] }}" style="color: #15c">{{ $data['url'] }}</a></p>
@endsection