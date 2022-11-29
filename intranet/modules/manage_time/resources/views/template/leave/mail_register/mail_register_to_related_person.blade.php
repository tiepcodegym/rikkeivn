<?php 
	use Carbon\Carbon;
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
	<span class="managetime-span-black">{{ trans('manage_time::view.Dear') }} {{ $data['related_person_name'] }}</span> <br>

	<span class="managetime-span-black">{{ trans('manage_time::view.From :start_time of date :start_date to :end_time of date :end_date, there was a :registrant_name employee, :team_name, registered leave day has related to you.', ['start_time' => $data['start_time'], 'start_date' => $data['start_date'], 'end_time' => $data['end_time'], 'end_date' => $data['end_date'], 'registrant_name' => $data['registrant_name'], 'team_name' => $data['team_name']]) }}</span> <br>

	<span class="managetime-span-black">{{ trans('manage_time::view.Detailed registration information is:') }} </span><br>

	<ul class="managetime-span-black">
		<li class="managetime-span-black">{{ trans('manage_time::view.From date:') }} {{ $data['start_date'] }} {{ $data['start_time'] }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.End date:') }} {{ $data['end_date'] }} {{ $data['end_time'] }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Number of days off:') }} {{ $data['number_days_off'] }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Leave day type:') }} {!! $data['reason'] !!}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Leave day reason:') }} {!! $data['note'] !!}</li>
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