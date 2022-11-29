<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p>{{ trans('asset::view.Dear :receiver_name,', ['receiver_name' => $data['name']]) }}</p>

    <?php
	    if ($data['isCreated']) {
	        $htmlFeedback = Lang::get("project::view.Customer feedback negative has been created");
	    } else {
	        $htmlFeedback = Lang::get("project::view.Customer feedback negative has been edit");
	    }
    ?>
    <p>{{ trans('project::view.Project name')}}: <b> {{$data['projectName']}} </b> {{ $htmlFeedback }}</p>
    <p>{{ trans('resource::view.Title:') }} <b> {{ $data['taskTitle'] }} </b></p>
    <p><a href="{{ $data['url'] }}" style="color: #15c">{{ trans('asset::view.View detail') }}</a></p>
@endsection