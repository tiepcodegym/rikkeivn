<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>
Chào {{ $data['name'] }},
</p>

<p>Một customer feedback với tiêu đề <strong>{{ $data['taskTitle'] }}</strong> vừa được {{ $data['isCreated'] ? 'tạo mới' : 'sửa' }} trong dự án <strong>{{ $data['projectName'] }}</strong>.</p>
@if (count($data['changed']))
@foreach ($data['changed'] as $changed)
<p>{!! '<strong>' . trans('project::view.' . strtolower($changed['field'])) . '</strong>: ' . nl2br($changed['old']) . ' => ' . nl2br($changed['new']) !!}</p>
@endforeach
@endif
<p>Mời bạn truy cập vào đường link phía dưới để biết thêm thông tin.</p>
<p><a href="{{ $data['url'] }}" style="color: #15c">{{ $data['url'] }}</a></p>
@endsection