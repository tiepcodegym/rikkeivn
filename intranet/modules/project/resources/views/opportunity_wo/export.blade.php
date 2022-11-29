<?php
    use Rikkei\Core\View\Form;
    use Rikkei\Project\Model\Risk;
    use Carbon\Carbon;
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
            ID
        </td>
        <td>
            Opportunity source
        </td>
        <td>
            Division
        </td>
        <td>
            Project
        </td>
        <td>
            Status
        </td>
        <td>
            Cost
        </td>
        <td>
            Expected Benefit
        </td>
        <td>
            Priority
        </td>
        <td>
            Assignee
        </td>
        <td>
            Plan end date
        </td>
        <td>
            Actual  end date
        </td>
        <td>
            Create date
        </td>
        <td>
            Update date
        </td>
        <td>
            Content
        </td>
        <td>
            Action plan
        </td>
        <td>
            Action Status
        </td>
    </tr>
    @if(count($collectionModel) > 0)
        @foreach($collectionModel as $key => $item)
            <tr class="offset">
                <td>{{ $item->id }}</td>
                <td>{{ $item->getOpportunitySource() }}</td>
                <td>{{ $item->team_name }}</td>
                <td>{{ $item->projs_name }}</td>
                <td>{{ $item->getStatusOpportunity() }}</td>
                <td>{{ $item->getCostOpportunity() }}</td>
                <td>{{ $item->getBenefitOpportunity() }}</td>
                <td>{{ $item->getPriority() }}</td>
                <td>{{ $item->assign_email }}</td>
                <td>{{ Carbon::parse($item->duedate)->format('Y-m-d') }}</td>
                <td>{{ Carbon::parse($item->actual_date)->format('Y-m-d') }}</td>
                <td>{{ Carbon::parse($item->created_at)->format('Y-m-d') }}</td>
                <td>{{ Carbon::parse($item->updated_at)->format('Y-m-d') }}</td>
                <td>{!! $item->content !!}</td>
                <td>{!! $item->action_plan !!}</td>
                <td>{{ $item->getStatusActionOpportunity() }}</td>
            </tr>
        @endforeach
    @endif
</table>
</body>
</html>