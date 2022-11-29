<?php 
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
	<span class="ticket-span-black">{{ trans('ticket::view.Dear department IT') }}</span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.At :ticket_time on :ticket_date, there is a request to IT as follows:', ['ticket_time' => $data['time'], 'ticket_date' => $data['date']]) }} <b>{{ $data['subject_mail'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Deadline for this job: :ticket_deadline and has been assigned to the :ticket_team_name department handle.', ['ticket_deadline' => $data['deadline'], 'ticket_team_name' => 'IT']) }}</span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.You can click on the following link to keep track of this work in more detail:') }} <a href="{{ $data['link'] }}">{{ trans('ticket::view.See details') }}</a></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Thanks!') }}</span> <br>
	
	<span class="ticket-span-black">{{ trans('ticket::view.Intranet.') }}</span>

	<style type="text/css">
		.ticket-span-black {
			color: #000 !important;
		}
	</style>
@endsection