<?php
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
extract($data);
?>

@extends($layout)

@section('content')

<div style="line-height: 17px;">
    @if (isset($content))
    {!! $content !!}
    @endif
</div>

@endsection
