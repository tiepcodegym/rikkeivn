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
Project reward <strong>{{ isset($data['project_name']) ? $data['project_name'] : '' }}</strong> is approved,
go to project reward and update pay reward's state.
</p>
<p>&nbsp;</p>
@if (isset($data['reward_link']))
<p><a href="{{ $data['reward_link'] }}" style="color: #15c">View detail</a></p>
@endif
@endsection
