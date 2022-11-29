<?php

use Rikkei\Core\View\Form;
use Rikkei\Project\Model\CommonRisk;
use Rikkei\Project\Model\Risk;

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
            {{ trans('project::view.ID risk') }}
        </td>
        <td style="width: 30">
            {{ trans('project::view.Risk Source') }}
        </td>
        <td style="width: 60">
            {{ trans('project::view.LBL_RISK_DESCRIPTION') }}
        </td>
        <td style="width: 30">
            {{ trans('project::view.Process') }}
        </td>
        <td style="width: 60">
            {{ trans('project::view.LBL_SUGGEST_ACTION') }}
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
            <td>{{ Risk::getSourceList()[$value->risk_source] }}</td>
            <td>{{ $value->risk_description }}</td>
            <td>{{ CommonRisk::getSourceListProcess()[$value->process] }}</td>
            <td>{{ $value->suggest_action }}</td>
            <td>{{ substr($value->created_at, 0, 10) }}</td>
            <td>{{ substr($value->updated_at, 0, 10) }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>