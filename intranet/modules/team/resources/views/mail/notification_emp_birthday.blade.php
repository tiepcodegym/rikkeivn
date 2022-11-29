<?php
use Rikkei\Core\Model\EmailQueue;
$name = $data['admin'];
$employeeArr = $data['employees'];
$layout = EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
    <p>{{ trans('admin_setting::view.dear') }} <b>{{ $name }}</b>,</p>
    <p>{{ trans('admin_setting::view.content') }}</p>
    <div style="margin-top: 20px">
        @foreach($employeeArr as $key => $item)
            <div style="display: flex">
                <p>{{ $key + 1 }}.</p>
                <p style="margin-left: 2px">{{ $item['name'] }}</p>
                <p style="margin-left: 10px">{{ $item['email'] }}</p>
            </div>
        @endforeach
    </div>
    <p><b>Intranet team.</b></p>
@endsection
