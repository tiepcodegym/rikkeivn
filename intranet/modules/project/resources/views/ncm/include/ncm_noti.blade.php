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
</p>
<p>
{{ isset($data['isEdit']) && $data['isEdit'] == true ? trans('project::view.A non-compliant process has been edited, please review and approve it') : trans('project::view.A non-compliant process has been created, please review and approve it') }}
</p>
<p>&nbsp;</p>
@if (isset($data['route']))
<p><a href="{{ $data['route'] }}" style="color: #15c">{{ trans('project::view.View detail') }}</a></p>
@endif
@endsection
