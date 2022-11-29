<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Event\View\ViewEvent;
use Rikkei\Core\Model\CoreConfigData;

$layout = EmailQueue::getLayoutConfig();
$content = CoreConfigData::getValueDb('event.send.email.to_male.content');
if(isset($data['reg_replace']) && $data['reg_replace'] &&
    isset($data['reg_replace']['patterns']) && $data['reg_replace']['patterns'] && 
    isset($data['reg_replace']['replaces']) && $data['reg_replace']['replaces']
) {
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
