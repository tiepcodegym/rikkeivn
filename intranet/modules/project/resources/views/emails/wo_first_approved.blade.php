<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>
Chào {{ $data['saleName'] }},
</p>
<p>Dự án <strong>{{ $data['projectName'] }}</strong> vừa được tạo.</p>
<p>Mời bạn truy cập vào đường link phía dưới để xác nhận các thông tin về customer requirement trong hợp đồng  (trong tab Scope & Object), risk (trong tab Risk).</p>
<p><a href="{{ $data['projectUrl'] }}" style="color: #15c">{{ $data['projectUrl'] }}</a></p>
@endsection