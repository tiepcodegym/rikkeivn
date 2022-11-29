<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')
<p>{{ trans('resource::view.Dear: :name', ['name' => $data['interviewerName']]) }}</p>
<p>Có ứng viên <b>{{ $data['candidateName'] }}</b> sẽ đến Rikkeisoft phỏng vấn vào vị trí <b>{{ $data['positionOfCandidate'] }}</b>.</p>
<p>Thời gian: {{ $data['startDate'] }} - {{ $data['endDate'] }}</p>
<p>Địa điểm: {!! $data['location'] !!}</p>
<p><a href="{{ $data['urlToCandidate'] }}" target="_blank">Xem chi tiết ứng viên</a></p>
<p>Rất mong anh/chị sẽ dành chút thời gian tới tham gia phỏng vấn.</p>
<p>{{trans('resource::view.Thanks & best regards')}}</p>
<div style="font-family: monospace;">
    <p style="font-size: 12px;margin:0px;"> -- </p>
    <p style="font-size: 12px;margin:0px;">--------------------------------------------</p>
    <p style="font-size: 12px;margin:0px;">Thanks & Best Regards,</p>
    <p style="font-size: 12px;margin:0px;">{{ $data['hrName'] }} | HR</p>
    <p style="font-size: 12px;margin:0px;">Rikkeisoft Co,. Ltd.</p>
    <p style="font-size: 12px;margin:0px;">Mobile: {{ $data['hrPhone'] }}</p>
    <p style="font-size: 12px;margin:0px;">Skype: {{ $data['hrSkype'] }}</p>
    <p style="font-size: 12px;margin:0px;">Email: {{ $data['hrEmail'] }}</p>
    <p style="font-size: 12px;margin:0px;">--------------------------------------------</p>
    <p style="font-size: 12px;margin:0px;">Head Office: 21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi</p>
    <p style="font-size: 12px;margin:0px;">Tel: (+84) 243 623 1685</p>
    <p style="font-size: 12px;margin:0px;">Page: https://www.facebook.com/rikkeisoft?fref=ts</p>
    <p style="font-size: 12px;margin:0px;">Website: http://rikkeisoft.com/</p>
</div>
@endsection
