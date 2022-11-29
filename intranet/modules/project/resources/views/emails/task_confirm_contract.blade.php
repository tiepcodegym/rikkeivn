<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>
{{ trans('project::view.Dear Mr/Ms') }} {{ $data['name'] }},
</p>
@if (count($data['fieldsChanged']))
<p>Nội dung bảo mật thông tin và chất lượng trong dự án <strong>{{ $data['projectName'] }}</strong> vừa được thay đổi. Mời bạn vào link bên dưới để xác nhận lại.</p>
<p>Nội dung thay đổi: </p>
@foreach ($data['fieldsChanged'] as $field)
<p>{{ trans('project::email.' . $field['field']) }}: {!! nl2br($field['old']) !!} -> {!! nl2br($field['new']) !!}</p>
@endforeach
@else
<p>
    Mời bạn vào link bên dưới để xác nhận nội dung về bảo mật thông tin và chất lượng của khách hàng trong dự án <strong>{{ $data['projectName'] }}</strong>
</p>
@endif
<p>&nbsp;</p>
@if (isset($data['link']))
<p><a href="{{ $data['link'] }}" style="color: #15c">View detail</a></p>
@endif
@endsection