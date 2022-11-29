<?php

use Rikkei\Core\View\Form;
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
    <tr class="offset">
        <td>
            {{ trans('project::view.No.') }}
        </td>
        <td>
            {{ trans('project::view.Project') }}
        </td>
        <td>
            {{ trans('project::view.Issue Type') }}
        </td>
        <td>
            {{ trans('project::view.Summary') }}
        </td>
        <td>
            {{ trans('project::view.Status') }}
        </td>
        <td>
            {{ trans('project::view.Priority') }}
        </td>
        <td>
            {{ trans('project::view.Assignee') }}
        </td>
        <td>
            {{ trans('project::view.Duedate') }}
        </td>
        <td>
            {{ trans('project::view.Create date') }}
        </td>
        <td>
            {{ trans('project::view.Update date') }}
        </td>
    </tr>
    @foreach($dataIssue as $key => $data)
        <tr class="offset">
            <td>{{ $key + 1 }}</td>
            <td>{{ $data->project_name }}</td>
            <td>{{ Task::checkTypeOfIssue($data->type) ? Task::typeLabelForIssue()[$data->type] : '' }}</td>
            <td>{{ $data->title }}</td>
            <td>{{ Task::getStatusOfIssue($data->status, $data->status_backup) }}</td>
            <td>{{ Task::priorityLabel()[$data->priority] }}</td>
            <td>{{ $data->email }}</td>
            <td>{{ Task::getDuedateOfIssue($data->task_duedate, $data->task_duedate_backup) }}</td>
            <td>{{$data->created_at}}</td>
            <td>{{$data->updated_at}}</td>
        </tr>
    @endforeach
</table>
</body>
</html>