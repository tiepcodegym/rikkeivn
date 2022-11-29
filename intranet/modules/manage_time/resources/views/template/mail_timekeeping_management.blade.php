<?php 
	use Carbon\Carbon;
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')

	<span class="managetime-span-black">{{ trans('manage_time::view.Dear') }} {{ $data['admin_name'] }}</span> <br>

	<span class="managetime-span-black">{{ trans('manage_time::view.There is an employee :name has just been deleted from the rikkei.vn system', ['name' => $data['name']]) }}</span> <br>

	<span class="managetime-span-black">{{ trans('manage_time::view.Employee information is as follows:') }}</span><br>

	<ul class="managetime-span-black">
		<li class="managetime-span-black">{{ trans('manage_time::view.Full name:') }} {{ $data['name'] }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Employee code:') }} {{ $data['employee_code'] }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Email Rikkei:') }} {{ $data['employee_email'] }}</li>
		<li class="managetime-span-black">{{ trans('manage_time::view.Contract type:') }} {{ $data['contract_type'] }}</li>
	</ul>

	<span class="managetime-span-black">{{ trans('manage_time::view.Best regards') }}</span> <br>
	
	<span class="managetime-span-black">{{ trans('manage_time::view.Product team.') }}</span>

	<style type="text/css">
		.managetime-span-black {
			color: #000;
		}
	</style>
@endsection