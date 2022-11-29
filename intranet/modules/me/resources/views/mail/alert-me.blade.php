<?php
use Rikkei\Core\Model\EmailQueue;
$name = $data['name'];
$projectsArr = $data['projects'];
$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p>{{ trans('admin_setting::view.dear') }} <b>{{ $name }}</b>,</p>
    <p>Danh sách dự án chưa được submit ME point:</p>
    <div style="margin-top: 20px">
        @foreach($projectsArr as $key => $item)
            <div style="display: flex">
                <p><b>{{ $key + 1 }}.</b></p>                
                <p style="margin-left: 2px">{{ $item['project_name'] }}</p>
            </div>
        @endforeach
    </div>
    <p>Xin vui lòng submit thưởng Me point <a href="https://me.rikkei.vn/me-point/all/time" style="text-decoration: none">Tại đây</a></p>
    <p><b>Intranet team.</b></p>
@endsection
