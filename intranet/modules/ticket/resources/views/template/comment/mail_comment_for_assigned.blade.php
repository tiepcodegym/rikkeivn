<?php 
	use Carbon\Carbon;
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
	<span class="ticket-span-black">{{ trans('ticket::view.Dear') }} {{ $data['ticket_assigned_to'] }}</span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.The work you are doing has a new comment.') }}</span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Work name:') }} <b>{{ $data['ticket_subject'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Created at:') }} <b>{{ $data['ticket_created_at'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Deadline:') }} <b>{{ $data['ticket_deadline'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Priority:') }} <b>{{ $data['ticket_priority'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Status:') }} <b">{{ $data['ticket_status'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Created by:') }} <b>{{ $data['ticket_created_by'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Person comment:') }} <b>{{ $data['comment_by'] }}</b></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Content comment:') }} <span style="font-weight: bold;">{!! $data['comment_shorten_content'] !!}</span></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.You can click on the following link to keep track of this work in more detail:') }} <a href="{{ $data['ticket_link'] }}">{{ trans('ticket::view.See details') }}</a></span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Thanks!') }}</span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Intranet.') }}</span>

	<style type="text/css">
		.ticket-span-black {
			color: #000 !important;
		}
	</style>
@endsection