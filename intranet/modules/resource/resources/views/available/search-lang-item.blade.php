<?php
$values = isset($searchItem) ? $searchItem : [];
?>

@if (!isset($searchItem))
<table id="filter_lang_item">
@endif
    <tr class="tr-search">
        <td width="200">{!! Form::select(
                '',
                $languages,
                isset($values['tag_id']) ? $values['tag_id'] : null,
                ['class' => 'form-control filter-grid field has-search', 'data-name' => 'tag_id']
            ) !!}</td>
        <td width="60">{!! Form::select(
                '',
                $compares,
                isset($values['compare']) ? $values['compare'] : null,
                ['class' => 'form-control filter-grid field', 'data-name' => 'compare']
            ) !!}</td>
        <td>{!! Form::select(
                '',
                $rangeYears,
                isset($values['year']) ? $values['year'] : null,
                ['class' => 'form-control filter-grid field', 'data-name' => 'year']
            ) !!}</td>
        <td class="text-right">
            <button type="button" class="btn btn-danger btn-del-filter-item"><i class="fa fa-close"></i></button>
        </td>
    </tr>
@if (!isset($searchItem))
</table>
@endif
