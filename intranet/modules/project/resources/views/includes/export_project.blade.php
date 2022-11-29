<?php
    use Rikkei\Project\Model\Project;
    use Rikkei\Project\Model\ProjectMember;
    use Rikkei\Project\Model\ProjectCategory;
    use Rikkei\Project\Model\ProjectMetaScope;

    $lablelState = Project::lablelState();
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
            {{ trans('project::view.Project Name') }}
        </td>
        <td>
            {{ trans('project::view.D leader') }}
        </td>
        <td>
            {{ trans('project::view.PM of project') }}
        </td>
        <td>
            {{ trans('project::view.Email of PM') }}
        </td>
        <td>
            {{ trans('project::view.Team_in_charge') }}
        </td>
        <td>
            {{ trans('project::view.Division') }}
        </td>
        <td>
            {{ trans('project::view.Project Type') }}
        </td>
        <td>
            {{ trans('project::view.State') }}
        </td>
        <td>
            {{ trans('project::view.Status of project') }}
        </td>
        <td>
            {{ trans('project::view.Start Date') }}
        </td>
        <td>
            {{ trans('project::view.End Date') }}
        </td>
        <td>
            {{ trans('project::view.Billable Effort') }}
        </td>
        <td>
            {{ trans('project::view.Plan Effort') }}
        </td>
        <td>
            {{ trans('project::view.cost_approved_production') }}
        </td>
        <td>
            {{ trans('project::view.sum(pm.flat_resource)') }}
        </td>
        <td>
            {{ trans('project::view.Scope') }}
        </td>
        <td>
            {{ trans('project::view.Programming language') }}
        </td>
        <td>
            {{ trans('project::view.New Scope') }}
        </td>
        <td>
            {{ trans('project::view.Project category') }}
        </td>
        <td>
            {{ trans('project::view.Duration') }}
        </td>
        <td>
            {{ trans('project::view.Team size') }}
        </td>
        <td>
            {{ trans('project::view.Team size - current') }}
        </td>
    </tr>
    @foreach($data as $key => $project)
        <tr class="offset">
            <td>{{ $key + 1 }}</td>
            <td>{{ $project['Name'] }}</td>
            <td>{{ $project['D_Leader'] }}</td>
            <td>{{ $project['PM'] }}</td>
            <td>{{ $project['PM_email'] }}</td>
            <td>{{ $project['team_charge'] }}</td>
            <td>{{ $project['Division'] }}</td>
            <td>{{ $project['Type'] }}</td>
            <td>{{ in_array($project['state'], array_keys($lablelState)) ? $lablelState[$project['state']] : '' }}</td>
            <td>{{ $project['status'] == \Rikkei\Project\Model\Project::STATUS_ENABLE ? trans('project::view.Status Enabled') : trans('project::view.Status disabled') }}</td>
            <td>{{ \Carbon\Carbon::parse($project['start_at'])->format('Y-m-d') }}</td>
            <td>{{ \Carbon\Carbon::parse($project['end_at'])->format('Y-m-d') }}</td>
            <td>{{ $project['Billable_effort'] }}</td>
            <td>{{ $project['Plan_effort'] }}</td>
            <td>{{ $project['cost_approved_production'] }}</td>
            <td>{{ $project['SUM(pm.flat_resource)'] }}</td>
            <td>{{ $project['Scope'] }}</td>
            <td>{{ $project['proj_prog_lang'] }}</td>
            <td>{{ ProjectMetaScope::getLabelScope($project['scope_id']) ? implode(', ', ProjectMetaScope::getLabelScope($project['scope_id'])) : '' }}</td>
            <td>{{ $project['category_id'] ? ProjectCategory::getCateById($project['category_id']) : ''}}</td>
            <td>{{ Project::getDayOfProjectWork($project['start_at'], $project['end_at']) }}</td>
            <td>{{ (ProjectMember::countMemberProj($project['id']) > 1) ? ProjectMember::countMemberProj($project['id']) : '1' }}</td>
            <td>{{ (ProjectMember::countMemberProj($project['id'], true) > 0) ? ProjectMember::countMemberProj($project['id'], true) : '0' }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>



