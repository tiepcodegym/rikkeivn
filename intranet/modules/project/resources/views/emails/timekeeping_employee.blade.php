<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\CoreConfigData;

$layout = EmailQueue::getLayoutConfig();
$emailContent = CoreConfigData::getValueDb('hr.email_content.timekeeping');
$emailContent = trim($emailContent);
$lineHeight = 'line-height: 1.5;';
$patternsArray = [
    '/\{\{\saccount\s\}\}/',
    '/\{\{\sname\s\}\}/',
];
$replacesArray = [
    $data['account'],
    $data['ho_ten']
];
$emailContent = preg_replace($patternsArray, $replacesArray, $emailContent);
?>
@extends($layout)

@section('content')
<div style="{{ $lineHeight }}">
<p>Dear
@if (isset($data['ho_ten']) && $data['ho_ten'])
    {{ $data['ho_ten'] }},
@endif
</p>
{!! $emailContent !!}
<br/>
<p>Thanks!</p>
</div>
@endsection
