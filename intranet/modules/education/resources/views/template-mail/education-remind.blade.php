<?php
use Rikkei\Core\Model\EmailQueue;

$layout = EmailQueue::getLayoutConfig();
$content = $data['content'];
if (isset($data['reg_replace'])) {
    $content = preg_replace(
        $data['reg_replace']['patterns'],
        $data['reg_replace']['replaces'],
        $content
    );
}

?>

@extends($layout)
@section('content')
    {!! $content !!}
@endsection