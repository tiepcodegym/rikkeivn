

<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>Không đồng bộ được Purchase Order ID từ dự án {{ $data['projectName'] }} sang CRM.</p>
<p>Trang chi tiết dự án. <a href="{{ $data['url'] }}" style="color: #15c">Link</a></p>
@endsection