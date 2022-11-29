<?php
use Rikkei\Core\View\View as CoreView; ?>
<tr data-id="{!!$projItem->id!!}" data-type="proj">
    <td rowspan="2" colspan="2" data-dom-edit="dbclick" data-edit-name="pro[{!!$projItem->id!!}][name]">
        {!!$flagEditable!!}
        <span data-edit-label="text" data-valid-type='{"required":true,"maxlength":255}'>{{ $projItem->name }}</span>
    </td>
    <td rowspan="2" data-dom-edit="dbclick" data-edit-name="pro_s[{!!$projItem->id!!}][os][]" class="form-group-select2 td-dom-editable">
        {!!$flagEditable!!}
        <span data-edit-label="os">
        <?php
        $tagIds = Coreview::getValueArray($skillsProj, [$projItem->id, 'os']);
        if ($tagIds && count($tagIds)) {
            $tagLabel = '';
            foreach ($tagIds as $tagId) {
                $label = Coreview::getValueArray($skillsProj, ['tag', $tagId]);
                $tagLabel .= $label . ', ';
                $projTag[$projItem->id]['os'][$tagId] = $label;
            }
            $tagLabel = substr($tagLabel, 0, -2);
            echo e($tagLabel);
        }
        ?>
        </span>
    </td>
    <td rowspan="2" data-dom-edit="dbclick" data-edit-name="pro[{!!$projItem->id!!}][env]">
        {!!$flagEditable!!}
        <span data-edit-label="text" data-valid-type='{"maxlength":255}'>{{ $projItem->env }}</span>
    </td>
    <td rowspan="2" data-dom-edit="dbclick" data-edit-name="pro_s[{!!$projItem->id!!}][lang][]" class="form-group-select2 td-dom-editable">
        {!!$flagEditable!!}
        <span data-edit-label="lang">
        <?php
        $tagIds = Coreview::getValueArray($skillsProj, [$projItem->id, 'lang']);
        if ($tagIds && count($tagIds)) {
            $tagLabel = '';
            foreach ($tagIds as $tagId) {
                $label = Coreview::getValueArray($skillsProj, ['tag', $tagId]);
                $tagLabel .= $label . ', ';
                $projTag[$projItem->id]['lang'][$tagId] = $label;
            }
            $tagLabel = substr($tagLabel, 0, -2);
            echo e($tagLabel);
        }
        ?>
        </span>
    </td>
    <td rowspan="2" data-dom-edit="dbclick" data-edit-name="pro[{!!$projItem->id!!}][responsible]"
        data-valid-type='{"maxlength":5000}'>
        {!!$flagEditable!!}
        <span data-edit-label="textarea" class="white-space-pre">{{ $projItem->responsible }}</span>
    </td>
    <td rowspan="2" data-dom-edit="dbclick" data-edit-name="pro[{!!$projItem->id!!}][start_at]"
         data-valid-type='{"date":true}'>
        {!!$flagEditable!!}
        <span data-edit-label="date">{{ $projItem->start_at }}</span>
    </td>
    <td rowspan="2" data-dom-edit="dbclick" data-edit-name="pro[{!!$projItem->id!!}][end_at]">
        {!!$flagEditable!!}
        <span data-edit-label="date" data-valid-type='{"date":true,"greaterEqualThan":"[name=\"pro[{!!$projItem->id!!}][start_at]\"]"}'>{{ $projItem->end_at }}</span>
    </td>
    <?php $projItem->loadPeriod(); ?>
    <td class="text-right" data-dom-edit="dbclick" data-edit-name="pro_m[{!!$projItem->id!!}][period_y]">
        {!!$flagEditable!!}
        <span data-edit-label="input" data-valid-type='{"digits":true,"max": 100}'>{{ $projItem->period_y }}</span>
    </td>
    <td data-lang-r="year"></td>
</tr>
<tr data-id="{!!$projItem->id!!}" data-type="proj">
    <td class="text-right" data-dom-edit="dbclick" data-edit-name="pro_m[{!!$projItem->id!!}][period_m]"
        data-valid-type='{"digits":true,"max": 12}'>
        {!!$flagEditable!!}
        <span data-edit-label="input">{{ $projItem->period_m }}</span>
    </td>
    <td data-lang-r="month"></td>
</tr>
