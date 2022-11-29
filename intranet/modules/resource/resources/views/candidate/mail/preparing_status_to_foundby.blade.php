<?php
use Rikkei\Test\View\ViewTest;
use Rikkei\Team\Model\Team;

$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')
<p>Xin chào {{ $data['foundbyName'] }},</p>
<p>Ứng viên {{ $data['candidateName'] .' - '. $data['candidateEmail'] }} do bạn giới thiệu đã được offer thành công.</p>
<p>Một lần nữa cảm ơn bạn rất nhiều.</p>
<p>Bạn có thể giới thiệu ứng viên tiếp theo tại đây</p>
<p><a href="{{ $data['link'] }}" target="_blank">{{ $data['link'] }}</a></p>
@endsection
