<?php

use Rikkei\Core\View\Form;
use Rikkei\Project\Model\CommonRisk;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\Task;

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        table tr td {
            border: 1px solid #0a0a0a;
            text-align: center;
        }
    </style>
</head>
<body>
<table>
    <tr class="offset" style="background-color: #0b97c4">
        <td>
            {{ trans('project::view.ID risk') }}
        </td>
        <td style="width: 30">
            {{ trans('project::view.Issue Type') }}
        </td>
        <td style="width: 30">
            {{ trans('project::view.LBL_ISSUE_SOURCE') }}
        </td>
        <td style="width: 60">
            {{ trans('project::view.LBL_ISSUE_DESCRIPTION') }}
        </td>
        <td style="width: 60">
            {{ trans('project::view.LBL_CAUSE') }}
        </td>
        <td style="width: 30">
            {{ trans('project::view.Action') }}
        </td>
        <td style="width: 30">
            {{ trans('project::view.Create date') }}
        </td>
        <td style="width: 30">
            {{ trans('project::view.Update date') }}
        </td>
    </tr>
    @foreach($data as $key => $value)
        <tr class="offset">
            <td>{{ $value->id }}</td>
            <td>{{ Task::typeLabelForIssue()[$value->issue_type] }}</td>
            <td>{{ Risk::getSourceList()[$value->issue_source] }}</td>
            <td>{{ $value->issue_description }}</td>
            <td>{{ $value->cause }}</td>
            <td>{{ $value->action }}</td>
            <td>{{ substr($value->created_at, 0, 10) }}</td>
            <td>{{ substr($value->updated_at, 0, 10) }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>