<?php
use Rikkei\Core\Model\EmailQueue;

extract($data);
$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('css')
<style>
    #body_content img, .section-content img {
        max-width: 100%!important;
        height: auto!important;
    }
</style>
@stop

@section('content')

<div style="line-height: 20px;">
    {!! $content !!}
</div>

@endsection
