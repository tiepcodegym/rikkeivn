<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig(3);
?>
@extends($layout)

@section('css')
<style>
    p {
        margin: 8px auto;
    }
</style>
@endsection

@section('content')
{!! $content !!}
@endsection
