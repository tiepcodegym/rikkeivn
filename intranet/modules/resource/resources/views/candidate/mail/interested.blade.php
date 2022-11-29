<?php

use Rikkei\Core\Model\EmailQueue;
use Rikkei\Recruitment\Model\CddMailSent;

$layout = $data['type'] === CddMailSent::TYPE_MAIL_BIRTHDAY ? EmailQueue::getLayoutConfig(8) : EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')
    {!! $data['content'] !!}
@endsection
