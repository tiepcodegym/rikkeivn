<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();

extract($data);
?>
@extends($layout)

@section('content')

@if(isset($dear_name) && $dear_name)
<p>Dear <strong>{{ $dear_name }}</strong>,</p>
@endif

<p>&nbsp;</p>
<p>Tags of project(s) <strong>[{{ $project_name }}]</strong> 
    @if ($is_approved)
    changed and 
    @endif
    approved</p>

<p>&nbsp;</p>

<p><a href="{{ route('tag::object.project.index', ['project_ids' => $project_id]) }}" style="color: #15c">View detail</a></p>

<p>&nbsp;</p>
<p>Thanks and regard,</p>
<div><strong>{{ $submit_name }}</strong></div>

@endsection
