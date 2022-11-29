<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>{{ trans('project::view.Dear Mr/Ms') }} {{ $data['name'] }},</p>
<p>Bạn vừa được nhắc đến trong 1 bình luận: "{{ $data['cmt_content'] }}"</p>
<p>Mời bạn truy cập vào đường link phía dưới để xem chi tiết.</p>
<p><a href="{{ $data['link'] }}" style="color: #15c">{{ $data['link'] }}</a></p>
@endsection