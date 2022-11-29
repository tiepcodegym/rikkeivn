<?php 
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
	<span class="ticket-span-black">{{ trans('ticket::view.Dear department IT') }}</span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Currently the request below is about to expire but deadline has not been finalized.') }}</span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Please department IT coordinate with :name to resolve the matter in the shortest possible time.', ['name' => $data['name_created_by']]) }}</span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Work name:') }} <b>{{ $data['content'] }}</b></span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Created by:') }} <b>{{ $data['name_created_by'] }}</b></span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Created at:') }} <b>{{ $data['timecreate'] }}</b></span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Assigned to:') }} <b>{{ $data['name_sent_to'] }}</b></span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Deadline:') }} <b>{{ $data['deadline'] }}</b></span><br>

	<span class="ticket-span-black"><a href="{{ $data['link'] }}">{{ trans('ticket::view.See details') }}</a>.</span><br>

	<span class="ticket-span-black">{{ trans('ticket::view.Thanks!') }}</span> <br>

	<span class="ticket-span-black">{{ trans('ticket::view.Intranet.') }}</span>

	<style type="text/css">
		.ticket-span-black {
			color: #000 !important;
		}
	</style>
@endsection