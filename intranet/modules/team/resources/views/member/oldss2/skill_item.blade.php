<?php use Rikkei\Core\View\View as CoreView; ?>
<tr data-id="{!!$skillData->id!!}" data-type="ski" class="form-group-select2">
    <td data-dom-edit="dbclick" data-edit-name="ski[{!!$skillData->id!!}][{!!$key!!}][tag_id]" class="fg-valid-custom td-dom-editable min-250">
        {!!$flagEditable!!}
        <span data-edit-label="{!!$key!!}" data-edit-multi="no" data-id="{!!$skillData->tag_id!!}"
            data-valid-type='{"required":true,"isChecked":true}'>{{ CoreView::getValueArray($tagData, [$skillData->tag_id]) }}</span>
    </td>
    @for ($i = 1; $i < 6; $i++)
        <td class="text-center" data-dom-edit="dbclick" 
            data-edit-name="ski[{!!$skillData->id!!}][{!!$key!!}][level]"
            data-edit-value="{!!$i!!}">
            {!!$flagEditable!!}
            <span data-edit-label="check-tr"
                data-dom-error='[name="ski[{!!$skillData->id!!}][{!!$key!!}][tag_id]"]'>
            @if ($skillData->level == $i)
                <i class="fa fa-circle"></i>
            @endif
            </span>
        </td>
    @endfor
    <?php
    $skillData->loadExper();
    if ($skillData->exp_y > 0) {
        $expValue = $skillData->exp_y;
        $expType = 'year';
    } else {
        $expValue = $skillData->exp_m;
        $expType = 'month';
    }
    ?>
    <td class="text-right" data-dom-edit="dbclick"
        data-edit-name="ski_m[{!!$skillData->id!!}][{!!$key!!}][exp_y]">
        {!!$flagEditable!!}
        <span data-edit-label="input">{{ $expValue }}</span>
    </td>
    <td data-dom-edit="dbclick"
        data-edit-name="ski_m[{!!$skillData->id!!}][{!!$key!!}][exp_type]"
        data-edit-value="{!!$expType!!}" data-edit-values="year|month">
        {!!$flagEditable!!}
        <span data-edit-label="select-lang" data-lang-r="{!!$expType!!}"></span>
    </td>
</tr>
