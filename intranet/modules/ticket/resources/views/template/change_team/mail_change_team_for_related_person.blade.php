<?php 
	use Carbon\Carbon;
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
	<span class="ticket-span-black">{{ trans('ticket::view.Dear') }} {{ $data['ticket_related_person_name'] }}</span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.The work related to you changed the IT department that made the request.') }}</span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Work name:') }} <b>{{ $data['ticket_subject'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Created at:') }} <b>{{ $data['ticket_created_at'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Deadline:') }} <b>{{ $data['ticket_deadline'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Priority:') }} <b>{{ $data['ticket_priority'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Status current:') }} <b>{{ $data['ticket_status'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Created by:') }} <b>{{ $data['ticket_created_by'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Assigned to department:') }} <b>{{ $data['ticket_team_name'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.You can click on the following link to keep track of this work in more detail:') }} <a href="{{ $data['ticket_link'] }}">{{ trans('ticket::view.See details') }}</a></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Thanks!') }}</span> <br>
	
	<span class="ticket-span-black">{{ trans('ticket::view.Intranet.') }}</span>

	<style type="text/css">
		.ticket-span-black {
			color: #000 !important;
		}
	</style>
@endsection