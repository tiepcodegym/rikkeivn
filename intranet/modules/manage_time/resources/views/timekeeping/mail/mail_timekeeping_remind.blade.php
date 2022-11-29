<?php 
	use Carbon\Carbon;
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
<p>Dear {{ $data['name'] }},</p>
<p>Hệ thống Rikkei.vn xin thông báo: Số phút làm thiếu vào tháng {{ $data['month'] }}-{{ $data['year'] }} của bạn là <strong>{{ $data['hours'] }} giờ  {{ $data['minutes'] }} phút.</strong> </p>
<p>Bạn vui lòng kiểm tra lại công trên hệ thống <a href="https://rikkei.vn/">RIKKEI.VN</a></p>
<p><a href="{{ $data['route'] }}">https://rikkei.vn/profile/timekeeping-list</a></p>
<p>Nếu bạn bị thiếu công, xin hãy làm đơn bổ sung công ngay để hoàn thiện công của mình.</p>
<p>Trân trọng!</p>
@endsection