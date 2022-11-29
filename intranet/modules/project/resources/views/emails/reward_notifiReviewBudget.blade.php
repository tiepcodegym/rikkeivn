<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
@if(isset($data['dear_name']))
<p>{{ trans('project::view.Dear Mr/Ms') }} {{ $data['dear_name'] }},</p>
@endif
<p>&nbsp;</p>
<p>
    <strong>{{ trans('project::view.Project') }}:</strong> {{ isset($data['project_name']) ? $data['project_name'] : '' }}<br />
    <strong>{{ trans('project::view.PM') }}:</strong> {{ isset($data['project_pm']) ? $data['project_pm'] : '' }}<br />
    <strong>{{ trans('project::view.Group') }}:</strong> {{ isset($data['project_group']) ? $data['project_group'] : '' }}<br />
</p>
<p>
{{ trans('project::view.reward_notifiReviewBudget') }}
</p>
<p>&nbsp;</p>
@if (isset($data['point_link']))
<p><a href="{{ $data['point_link'] }}" style="color: #15c">{{ trans('project::view.View detail') }}</a></p>
@endif
@endsection
