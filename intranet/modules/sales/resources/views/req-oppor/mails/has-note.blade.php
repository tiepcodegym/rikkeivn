<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
extract($data);
?>
@extends($layout)

@section('content')

<p>Xin chào <strong>{{ $dearName }},</strong></p>
<p>&nbsp;</p>
<p>{{ trans('sales::view.mail_subject_has_cv_note', ['author' => $authorName, 'name' => $opporName]) }}: </p>
<p style="white-space: pre-line;">{{ $comment }}</p>
<p>&nbsp;</p>

<p><a href="{{ $detailLink }}" style="color: #15c">{{ trans('sales::view.View detail') }}</a></p>

@endsection
