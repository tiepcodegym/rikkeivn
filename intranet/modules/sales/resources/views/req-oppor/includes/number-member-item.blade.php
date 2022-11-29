<?php
if (!isset($memberItem)) {
    $memberItem = null;
    $itemOrder = null;
    $progIds = [];
} else {
    $progIds = $memberItem->programsIds();
}
?>
@if (!$memberItem)
<table id="num_emp_tpl">
@endif

<tr class="emp-item {{ !$memberItem ? 'new-emp-item' : '' }}">
    <td>
        <input type="text" name="members[numbers]{{ $itemOrder !== null ? '['.$itemOrder.']' : '' }}" min="1" class="form-control num-emp"
               value="{{ $memberItem ? $memberItem->number : null }}">
    </td>
    <td>
        <select name="members[roles]{{ $itemOrder !== null ? '['.$itemOrder.']' : '' }}" class="form-control new-select2 {{ $memberItem ? 'select-search has-search' : '' }}" style="width: 100%;">
            <option value="">&nbsp;</option>
            @foreach ($roles as $value => $label)
            <option value="{{ $value }}" {{ $memberItem && $memberItem->role == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </td>
    <td width="280">
        <select name="members[prog_ids]{{ $itemOrder !== null ? '['.$itemOrder.'][]' : '' }}" multiple class="form-control new-multiselect {{ $memberItem ? 'bootstrap-multiselect' : '' }}" style="width: 100%;">
            @foreach ($programs as $value => $label)
            <option value="{{ $value }}" {{ in_array($value, $progIds) ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="members[member_exps]{{ $itemOrder !== null ? '['.$itemOrder.']' : '' }}" class="form-control new-select2 {{ $memberItem ? 'select-search has-search' : '' }}" style="width: 100%;">
            <option value="">&nbsp;</option>
            @foreach ($typeOptions as $value => $label)
            <option value="{{ $value }}" {{ $memberItem && $memberItem->member_exp == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="members[english_levels]{{ $itemOrder !== null ? '['.$itemOrder.']' : '' }}" class="form-control new-select2 {{ $memberItem ? 'select-search has-search' : '' }}" style="width: 100%;">
            <option value="">&nbsp;</option>
            @foreach ($langLevels['en'] as $enLevel)
            <option value="{{ $enLevel }}" {{ $memberItem && $memberItem->english_level == $enLevel ? 'selected' : '' }}>{{ $enLevel }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="members[japanese_levels]{{ $itemOrder !== null ? '['.$itemOrder.']' : '' }}" class="form-control new-select2 {{ $memberItem ? 'select-search' : '' }}" style="width: 100%;">
            <option value="">&nbsp;</option>
            @foreach ($langLevels['ja'] as $jpLevel)
            <option value="{{ $jpLevel }}" {{ $memberItem && $memberItem->japanese_level == $jpLevel ? 'selected' : '' }}>{{ $jpLevel }}</option>
            @endforeach
        </select>
    </td>
    <td class="text-right">
        <input type="hidden" name="members[ids]{{ $itemOrder !== null ? '['.$itemOrder.']' : ''}}" value="{{ $memberItem ? $memberItem->id : '' }}">
        <button type="button" class="btn btn-danger btn-del-item"><i class="fa fa-minus"></i></button>
    </td>
</tr>

@if (!$memberItem)
</table>
@endif
