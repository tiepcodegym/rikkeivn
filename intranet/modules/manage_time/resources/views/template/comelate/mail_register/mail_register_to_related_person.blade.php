<?php 
	use Carbon\Carbon;
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
	<span class="managetime-span-black">{{ trans('manage_time::view.Dear') }} {{ $data['related_person_name'] }}</span> <br>

	<span class="managetime-span-black">{{ trans('manage_time::view.From :start_date to :end_date, there was a :registrant_name employee, :team_name, registered late in early out has related to you.', ['start_date' => $data['start_date'], 'end_date' => $data['end_date'], 'registrant_name' => $data['registrant_name'], 'team_name' => $data['team_name']]) }}</span> <br>

	<span class="managetime-span-black">{{ trans('manage_time::view.Detailed registration information is:') }} </span><br>

	<ul class="managetime-span-black">
		<li class="managetime-span-black">{{ trans('manage_time::view.Late start shift:') }} {{ $data['late_start_shift'] }} {{ trans('manage_time::view.(min)') }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Early mid shift:') }} {{ $data['early_mid_shift'] }} {{ trans('manage_time::view.(min)') }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Late mid shift:') }} {{ $data['late_mid_shift'] }} {{ trans('manage_time::view.(min)') }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Early end shift:') }} {{ $data['early_end_shift'] }} {{ trans('manage_time::view.(min)') }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Reason:') }} {!! $data['reason'] !!}</li>
	</ul>

	<span class="managetime-span-black">{{ trans('manage_time::view.You can click on the following link for more details:') }} <a href="{{ $data['link'] }}">{{ trans('manage_time::view.See details') }}</a></span> <br>

	<span class="managetime-span-black">{{ trans('manage_time::view.Thanks!') }}</span> <br>
	
	<span class="managetime-span-black">{{ trans('manage_time::view.Intranet.') }}</span>

	<style type="text/css">
		.managetime-span-black {
			color: #000;
		}
	</style>
@endsection