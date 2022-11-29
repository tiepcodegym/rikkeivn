<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Event\View\ViewEvent;
use Rikkei\Core\Model\CoreConfigData;

extract($data);

$layout = EmailQueue::getLayoutConfig();
$salaryIndex = ViewEvent::getSalaryRowIndex();
$keysEmail = ViewEvent::getKeysEmailBranch($branch, 'salary');

$content = CoreConfigData::getValueDb($keysEmail['content']);
$content = preg_replace(
    [
        '/\{\{\sname\s\}\}/',
        '/\{\{\saccount\s\}\}/',
    ],
    [
        $emailData[$salaryIndex['fullname']],
        preg_replace('/\@.*/', '', $emailData[$salaryIndex['email']])
    ],
    $content
);
$totalRow = count($emailData);
?>

@extends($layout)

@section('css')
<style>
    .salary_table {
        border-collapse: collapse;
        border: 2px solid #767676;
        width: 100%;
    }
    .salary_table tr th, .salary_table tr td {
        padding: 8px 10px;
        border: 1px solid #767676;
        text-align: left;
    }
</style>
@stop

@section('content')

<div style="line-height: 17px;">
    {!! $content !!}
</div>

@endsection



 



