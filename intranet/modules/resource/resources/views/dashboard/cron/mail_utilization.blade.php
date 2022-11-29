<?php
    $layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')
<p>{{ trans('resource::view.Hello :name,', ['name' => 'bod']) }}</p>
<p>{{ trans('resource::view.Below is the list of employees who have 0% effort from :start - :end in Rikkeisoft Intranet System', ['start' => $data['startDate'], 'end' => $data['endDate']]) }}.</p>

@foreach ($data['dataTeam'] as $teamName => $listEmp)
<p style="margin-bottom: 0;">- <b>{{ $teamName }}</b></p>
<ol style="margin-top: 0; padding-left: 15px;">
    @foreach($listEmp as $emp)
    <li style="line-height: 20px;">{{ $emp['empName'] }} ({{ $emp['empEmail'] }})</li>
    @endforeach
</ol>
@endforeach

@endsection

