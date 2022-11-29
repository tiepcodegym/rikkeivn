<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
extract($data);
?>

@extends($layout)

@section('content')

<p>Chào bạn <strong>{{ $name }},</strong></p>

<p>&nbsp;</p>

<p>{{ $subject }}:</p>

<p>
    <span style="white-space: pre-line; line-height: 22px;">{{ $comment }}</span>
</p>

<p>&nbsp;</p>

<p><a href="{{ $urlToCandidate }}#tab_interview" target="_blank">Xem chi tiết</a></p>

@endsection
