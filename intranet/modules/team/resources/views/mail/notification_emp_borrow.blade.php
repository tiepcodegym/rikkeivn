<?php
use Rikkei\Core\Model\EmailQueue;

extract($data);
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p><strong>Dear {{ $dear_name }},</strong></p>
@if($titleMail)
<p>Bạn có một số dự án sắp hết thời gian thuê!</p>
@else
<p>Có một số nhân viên thuê ngoài sắp hết thời gian thuê!</p>
@endif
@foreach($dataEmail as $key => $data)
<p></p>
<p><strong>{{ ($key+1) }}.</strong>Nhân viên: {{ $data->name }}</p>
<p>&emsp; <strong>Project:</strong><a href="{!! route('project::project.edit', ['id' => $data->id_project]) . '#team-allocation' !!}" target="_blank"> {{ $data->name_project }}</a></p>
<p>&emsp; <strong>PM:</strong> {{ $data->name_manager }} ( {{ preg_replace('/@.*/', '', $data->email_manager) }} )</p>
<p>&emsp; <strong>Date Leave:</strong> {{ date('d-m-Y', strtotime($data->leave_date)) }}</p>
@endforeach
@endsection
