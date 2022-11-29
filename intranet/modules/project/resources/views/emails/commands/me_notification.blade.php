<?php 
	use Rikkei\Core\Model\EmailQueue;
    use Carbon\Carbon;
    use Rikkei\Core\View\Form;

	$layout = EmailQueue::getLayoutConfig();
    $number = count($data['employees']);
?>

@extends($layout)
@section('content')
    <p>Dear {{ $data['dlead_name'] }}</p>
    <p>Hệ thống Rikkei.vn xin thông báo: số nhân viên chưa được {{ $data['type'] == 'evaluated' ? 'duyệt' : 'đánh giá' }} ME tháng {{ $data['date'] }} của {{ $data['team_name'] }} là <strong>{{ $number }}</strong>.</p>
    <p>Danh sách nhân viên: {{ $data['link'] }}</p>

    <br>
    <p>Trân trọng,</p>
    <p>Product team.</p>
@endsection