<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<p>Chào <strong>{{ $data['name'] }}</strong>,</p>
<p></p>
<p>Hệ thống vừa thay đổi username trên <a href="https://git.rikkei.org">https://git.rikkei.org</a> của bạn.</p>
<p>Username cũ: {{ $data['old_user'] }}</p>
<p>Username mới: <strong>{{ $data['new_user'] }}</strong></p>
<p>Lý do thay đổi: username sai chuẩn, chưa trùng với email.</p>
<p></p>
<p>Intranet team trân trọng thông báo!</p>
@endsection
