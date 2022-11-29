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
<p>Tags of project(s) <strong>[{{ $project_names }}]</strong> 
    @if ($is_submited)
    resubmited
    @else
    submitted
    @endif
    , please review.</p>

<p>&nbsp;</p>

<p><a href="{{ route('tag::object.project.index', ['project_ids' => $project_ids]) }}" style="color: #15c">View detail</a></p>

<p>&nbsp;</p>
<p>Thanks and regard,</p>
<div><strong>{{ $submit_name }}</strong></div>

@endsection
