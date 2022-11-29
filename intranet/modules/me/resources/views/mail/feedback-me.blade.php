<?php
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p>Anh/Chị {{ $data['dlead_name'] }} có feedback về điểm ME của bạn <b>{{ $data['employee_name'] }}</b> trong dự án <b>{{ $data['project_name'] }}:</b></p>
    <div style="margin-top: 20px">
            <div style="display: flex">
                <p><b>1.</b></p>                
                <p style="margin-left: 2px">{{ $data['feedback'] }}</p>
            </div>
    </div>
    <p>Xin vui lòng review <a href={{ $data['url'] }} style="text-decoration: none">Tại đây</a></p>
    <p><b>Intranet team.</b></p>
@endsection
