<?php
    $layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>{{ trans('resource::view.Hello :name,', ['name' => $data['recipientName']]) }}</p>
<p>{{ trans('resource::view.Below is the list of employees who have 0% effort in :team from :start - :end in Rikkeisoft Intranet System',
            ['start' => $data['startDate'], 'end' => $data['endDate'], 'team' => $data['teamName']]) }}.</p>

<ol style="margin-top: 0; padding-left: 15px;">
    @foreach($data['listEmp'] as $emp)
    <li style="line-height: 20px;">{{ $emp['empName'] }} ({{ $emp['empEmail'] }})</li>
    @endforeach
</ol>

@endsection

