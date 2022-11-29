<?php
use Rikkei\Document\View\DocConst;

$assigneStatus = $emp->pivot->status;
?>

<tr data-emp="{{ $emp->id }}">
    <td class="col-name">
        {{ $emp->name }} ({{ DocConst::getAccount($emp->email) }})
    </td>
    <td class="col-status">
        @if ($assigneStatus != DocConst::STT_NEW)
        {!! DocConst::renderStatusHtml($assigneStatus, $docStatuses, 'label') !!}
        @endif
        <input type="hidden" name="assignees[{{ $typeAssignee }}][{{ $emp->id }}]" value="{{ $assigneStatus }}" />
    </td>
    @if ($permiss)
    <td class="text-right col-action">
        <button type="button" class="btn btn-xs btn-danger btn-del-assignee"
                data-assigne-status="{{ $assigneStatus }}" data-type="{{ $typeAssignee }}">
            <i class="fa fa-minus"></i>
        </button>
    </td>
    @endif
</tr>
