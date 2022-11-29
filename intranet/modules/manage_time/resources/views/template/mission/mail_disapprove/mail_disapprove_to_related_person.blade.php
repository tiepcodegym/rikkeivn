<?php 
	use Carbon\Carbon;
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
	<span class="managetime-span-black">{{ trans('manage_time::view.Dear') }} {{ $data['related_person_name'] }}</span> <br>

	<span class="managetime-span-black">{{ trans('manage_time::view.The register of business trip of :registrant_name, :team_name related to you is considered:', ['registrant_name' => $data['registrant_name'], 'team_name' => $data['team_name']]) }} <b>{{ $data['status'] }}</b></span> <br>

	<span class="managetime-span-black">{{ trans('manage_time::view.Detailed registration information is:') }} </span><br>

	<ul class="managetime-span-black">
		<li class="managetime-span-black">{{ trans('manage_time::view.Out date:') }} {{ $data['start_date'] }} {{ $data['start_time'] }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.On date:') }} {{ $data['end_date'] }} {{ $data['end_time'] }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Location:') }} {{ $data['location'] }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Purpose:') }} {!! $data['purpose'] !!}</li>
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