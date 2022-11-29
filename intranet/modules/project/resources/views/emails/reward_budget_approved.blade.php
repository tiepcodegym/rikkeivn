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
    <strong>Project:</strong> {{ isset($data['project_name']) ? $data['project_name'] : '' }}<br />
    <strong>PM:</strong> {{ isset($data['project_pm']) ? $data['project_pm'] : '' }}<br />
    <strong>Group:</strong> {{ isset($data['project_group']) ? $data['project_group'] : '' }}
</p>
<p>
Project reward budget approved, please view it.
</p>
<p>&nbsp;</p>
@if (isset($data['reward_link']))
<p><a href="{{ $data['reward_link'] }}" style="color: #15c">View detail</a></p>
@endif
@endsection
