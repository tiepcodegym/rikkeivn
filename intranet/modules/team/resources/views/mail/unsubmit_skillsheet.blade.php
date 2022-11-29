<?php
use Rikkei\Core\Model\EmailQueue;
$idUser = $data['id'];
$link = url("profile/$idUser/cv");
$name = $data['name'];
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p>{{ trans('team::email.Name unsubmit skillsheet') }} <b>{{ $name }}</b>,</p>
<p>{{ trans('team::email.Content unsubmit skillsheet') }}</p>
<p><a href="{{ $link  }}">{{ trans('team::email.Link to skillsheet') }}</a></p>
<br />
-----------------------------------------
@endsection
