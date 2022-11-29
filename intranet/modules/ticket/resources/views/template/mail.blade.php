<?php 
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
	<span class="ticket-span-black">{{ trans('ticket::view.Dear') }} {{ $data['CreateBy'] }}</span><br>
	
	<span class="ticket-span-black">{{ trans('ticket::view.At :ticket_time on :ticket_date, you have made a request to IT as follows:', ['ticket_time' => $data['time'], 'ticket_date' => $data['date']]) }} <b>{{ $data['subject'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Currently this job has been assigned to :ticket_assigned_to of the :ticket_team_name department handle.', ['ticket_assigned_to' => $data['EmpName'], 'ticket_team_name' => 'IT']) }}</span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.We will try to handle your work soon.') }}</span><br>
	
	<span class="ticket-span-black">{{ trans('ticket::view.You can click on the following link to keep track of this work in more detail:') }} <a href="{{ $data['link'] }}">{{ trans('ticket::view.See details') }}</a></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Thanks!') }}</span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Deparment IT.') }}</span>

	<style type="text/css">
		.ticket-span-black {
			color: #000 !important;
		}
	</style>
@endsection