<?php 
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
	<span class="ticket-span-black">{{ trans('ticket::view.Dear') }} {{ $data['name_created_by'] }}</span><br>
	
	<span class="ticket-span-black">{{ trans('ticket::view.Currently there is a request below that you created IT has completed but not switch status to Closed.') }}</span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Please check the content of the work, then turn status to Closed if this job has been completed.') }}</span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Work name:') }} <b>{{ $data['content'] }}</b></span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Created by:') }} <b>{{ $data['name_created_by'] }}</b></span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Created at:') }} <b>{{ $data['created_at'] }} </b></span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Deadline:') }} <b>{{ $data['deadline'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Assigned to:') }} <b>{{ $data['name_as'] }}</b></span><br>

	<span class="ticket-span-black"><a href="{{ $data['link'] }}">{{ trans('ticket::view.See details') }}</a>.</span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Thanks!') }}</span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Intranet.') }}</span>

	<style type="text/css">
		.ticket-span-black {
			color: #000 !important;
		}
	</style>
@endsection