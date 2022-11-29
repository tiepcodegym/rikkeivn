<?php 
	use Carbon\Carbon;
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();

        $dayOfTheWeek = Carbon::parse($data['date'])->dayOfWeek;
        $addDays = 3;
        if ($dayOfTheWeek == 5) {
            $addDays += 2; 
        }
?>

@extends($layout)
@section('content')
<p>Dear {{ $data['name'] }},</p>
<p>Hệ thống Rikkei.vn xin thông báo: số công vào ngày {{ Carbon::parse($data['date'])->format('d-m-Y') }} của bạn là <strong>{{ $data['total_working'] }}</strong>. </p>
<p>Bạn vui lòng kiểm tra lại công tại <a href="{{ $data['url_timekeeping_profile'] }}">{{ $data['url_timekeeping_profile'] }}</a></p>
<p>Nếu bạn bị thiếu công, xin hãy làm đơn bổ sung công trước ngày {{ Carbon::parse($data['date'])->addDays($addDays)->format('d-m-Y') }}</p>
<p>Trân trọng!</p>
@endsection
