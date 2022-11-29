<?php

use Rikkei\Core\View\Form;
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
        <td>
            {{ trans('project::view.Division') }}
        </td>
        <td>
            {{ trans('project::view.Project') }}
        </td>
        <td>
            {{ trans('project::view.Risk Type') }}
        </td>
        <td>
            {{ trans('project::view.Owner') }}
        </td>
        <td>
            {{ trans('project::view.Summary') }}
        </td>
        <td>
            {{ trans('project::view.Description') }}
        </td>
        <td>
            {{ trans('project::view.Impact') }}
        </td>
        <td>
            {{ trans('project::view.Status') }}
        </td>
        <td>
            {{ trans('project::view.Priority') }}
        </td>
        <td>
            {{ trans('project::view.Due date') }}
        </td>
        <td>
            {{ trans('project::view.Resource') }}
        </td>
        <td>
            {{ trans('project::view.PQA Suggestion') }}
        </td>
        <td>
            {{ trans('project::view.Mitigation Action') }}
        </td>
        <td>
            {{ trans('project::view.Contigency Action') }}
        </td>
        <td>
            {{ trans('project::view.Comment') }}
        </td>
        <td>
            {{ trans('project::view.Create date') }}
        </td>
        <td>
            {{ trans('project::view.Update date') }}
        </td>
    </tr>
    @foreach($dataRisk as $key => $data)
        <tr class="offset">
            <td>{{ $data->id }}</td>
            <td>{{ $data->team_name }}</td>
            <td>{{ $data->project_name }}</td>
            <td>{{ Risk::checkTypeOfRisk($data->type) ? Risk::getTypeList()[$data->type] : '' }}</td>
            <td>{{ $data->owner_name }}</td>
            <td>{{ $data->content }}</td>
            <td>{{ $data->description }}</td>
            <td>{{ isset(Risk::impactLabel()[$data->impact_backup]) ? Risk::impactLabel()[$data->impact_backup] : ''  }}</td>
            <td>{{ isset(Risk::statusLabel()[$data->status]) ? Risk::statusLabel()[$data->status] : '' }}</td>
            <td>{{ isset($data->impact_backup) && isset($data->probability_backup) ? Risk::getPriorityLabel($data->impact_backup, $data->probability_backup) : '' }}</td>
            <td>{{ $data->due_date }}</td>
            <td>{{ isset($data->source) ? Risk::getSourceList()[$data->source] : '' }}</td>
            <td>{{ $data->solution_using }}</td>
            <td>
                @if($data->miti_content)
                    @foreach(explode(",", $data->miti_content) as $miti => $valMiti)
                        <label style="color:red;">{{ trans('project::view.Mitigation Action') }} [{{$miti + 1}}] :  {{$valMiti}}</label>  <br>
                    @endforeach
                @endif
            </td>
            <td>
                @if($data->conti_content)
                    @foreach(explode(",", $data->conti_content) as $conti => $valConti)
                        <label style="color:red;">{{ trans('project::view.Contigency Action') }} [{{$conti + 1}}] :  {{$valConti}}</label>  <br>
                    @endforeach
                @endif
            </td>
            <td>
                @if($data->cmt_content)
                    @foreach(explode(",", $data->cmt_content) as $cmts => $valCmt)
                        <label style="color:red;">{{ trans('project::view.Comment') }} [{{$cmts + 1}}] :  {{$valCmt}}</label>  <br>
                    @endforeach
                @endif
            </td>
            <td>{{ $data->created_at }}</td>
            <td>{{ $data->updated_at }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>