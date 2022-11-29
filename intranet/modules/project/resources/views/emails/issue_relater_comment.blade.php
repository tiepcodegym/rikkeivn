<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>
{{ trans('project::view.Dear Mr/Ms') }} {{ $dataComment['name'] }},
</p>

<p>Một issue với nội dung <strong>{{ $dataComment['issueContent'] }}</strong> vừa được {{ $dataComment['creator'] }} bình luận trong dự án <strong>{{ $dataComment['projectName'] }}</strong>.</p>

<p>Mời bạn truy cập vào đường link phía dưới để biết thêm thông tin.</p>
<p><a href="{{ $dataComment['url'] }}" style="color: #15c">{{ $dataComment['url'] }}</a></p>
@endsection