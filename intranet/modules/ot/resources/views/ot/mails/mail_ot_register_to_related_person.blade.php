<?php 
	use Carbon\Carbon;
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
	<span class="managetime-span-black">Dear {{ $data['related_person_name'] }}</span> <br>

	<span class="managetime-span-black">Từ {{ $data['start_time'] }} ngày {{ $data['start_date'] }} đến {{ $data['end_time'] }} ngày {{ $data['end_date'] }}, có nhân viên {{ $data['registrant_name'] }}, là {{ $data['team_name'] }} đăng ký làm thêm liên quan tới anh/chị.</span> <br>

	<span class="managetime-span-black">Chi tiết thông tin đăng ký là: </span><br>

	<ul class="managetime-span-black">
		<li class="managetime-span-black">Làm thêm từ: {{ $data['start_time'] .' '. $data['start_date'] }}</li>
		<li class="managetime-span-black">Làm thêm đến: {{ $data['end_time'] .' '. $data['end_date'] }}</li>
		<li class="managetime-span-black">Dự án: {{ $data['projs_name'] }}</li>
		<li class="managetime-span-black">Có phải là OT onsite? {{ $data['is_onsite'] }}</li>
		<li class="managetime-span-black">Lý do làm thêm: {!! $data['reason'] !!}</li>
		<li class="managetime-span-black">
			Danh sách nhân viên đăng ký làm thêm:
			@if (count($data['otEmps']))
				<ul>
					@foreach ($data['otEmps'] as $emp)
						<li>{{ $emp->employee_code .' - '. $emp->name }}</li>
					@endforeach
				</ul>
			@endif
		</li>
	</ul>

	<span class="managetime-span-black">Anh/chị có thể bấm vào link sau để theo dõi chi tiết hơn: <a href="{{ $data['link'] }}">{{ trans('manage_time::view.See details') }}</a></span> <br>

	<span class="managetime-span-black">Xin cám ơn!</span> <br>
	
	<span class="managetime-span-black">Intranet.</span>

	<style type="text/css">
		.managetime-span-black {
			color: #000;
		}
	</style>
@endsection