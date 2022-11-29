<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<?php
extract($data);
?>

@if (isset($project_name))
<p><strong>{{ $project_name }}</strong></p>
@endif
<p>New Assumption And Constrains Assignee assign to you</p> 
<br />
<a href="{{route('project::project.edit', ['id' => $project_id])}}#others">View detail</a>
@endsection