<?php
use Rikkei\Core\View\View as CoreView; ?>
<tr data-id="{!!$projItem->id!!}" data-type="proj">
    <td rowspan="2">
        <p>
            <a href="#" data-name="pro[{!!$projItem->id!!}][name]" 
                data-placement="bottom" 
                data-type="text"
                data-edit-dom="submit"
                data-edit-type="normal"
                class="xeditor-label"
                data-valid-type='{"required":true,"maxlength":255}'
                data-db-lang="proj_{!!$projItem->id!!}_name"
                data-placeholder="Name"
                ></a>
        </p>
        <p>
            <a href="#" data-name="pro[{!!$projItem->id!!}][description]" 
                data-placement="bottom" 
                data-type="textarea"
                data-edit-dom="submit"
                data-edit-type="normal"
                class="xeditor-label"
                data-valid-type='{"maxlength":5000}'
                data-db-lang="proj_{!!$projItem->id!!}_description"
                data-placeholder="Description"
                ></a>
        </p>
    </td>
    <td rowspan="2" class="form-group-select2 td-dom-editable" data-s2-edit="os">
        <a href="#" data-name="pro[{!!$projItem->id!!}][os][]" 
            data-placement="bottom"
            data-type="select2"
            data-edit-dom="submit"
            data-edit-type="select2-multi"
            class="xeditor-label"
            data-value="{!!implode(',', Coreview::getValueArray($skillsProj, [$projItem->id, 'os'], []))!!}"
            data-edit-value-default="-1"
            ></a>
    </td>
    <td rowspan="2">
        <a href="#" data-name="pro[{!!$projItem->id!!}][env]" 
            data-placement="bottom" 
            data-type="textarea"
            data-edit-dom="submit"
            data-edit-type="normal"
            class="xeditor-label"
            data-valid-type='{"maxlength":5000}'
            data-db-lang="proj_{!!$projItem->id!!}_env"
            ></a>
    </td>
    <td rowspan="2" class="form-group-select2 td-dom-editable" data-s2-edit="lang">
        <a href="#" data-name="pro[{!!$projItem->id!!}][lang][]" 
            data-placement="bottom"
            data-type="select2"
            data-edit-dom="submit"
            data-edit-type="select2-multi"
            class="xeditor-label"
            data-value="{!!implode(',', Coreview::getValueArray($skillsProj, [$projItem->id, 'lang'], []))!!}"
            data-edit-value-default="-1"
            ></a>
    </td>
    <td rowspan="2">
        <a href="#" data-name="pro[{!!$projItem->id!!}][responsible]" 
            data-placement="bottom" 
            data-type="textarea"
            data-edit-dom="submit"
            data-edit-type="normal"
            class="xeditor-label"
            data-valid-type='{"maxlength":5000}'
            data-db-lang="proj_{!!$projItem->id!!}_responsible"
            ></a>
    </td>
    <td rowspan="2">
        <a href="#" data-name="pro[{!!$projItem->id!!}][start_at]" 
            data-placement="bottom" 
            data-type="date"
            data-edit-dom="submit"
            data-edit-type="normal"
            class="xeditor-label"
            data-valid-type='{"date":true}'
            data-mode="popup"
            >{{ $projItem->start_at }}</a>
    </td>
    <td rowspan="2">
        <a href="#" data-name="pro[{!!$projItem->id!!}][end_at]" 
            data-placement="bottom" 
            data-type="date"
            data-edit-dom="submit"
            data-edit-type="normal"
            class="xeditor-label"
            data-valid-type='{"date":true,"greaterEqualThan":"[name=\"pro[{!!$projItem->id!!}][start_at]\"]"}'
            data-mode="popup"
            >{{ $projItem->end_at }}</a>
    </td>
    <?php $projItem->loadPeriod(); ?>
    <td class="text-right">
        <a href="#" data-name="pro[{!!$projItem->id!!}][period_y]" 
            data-placement="bottom" 
            data-type="text"
            data-edit-dom="submit"
            data-edit-type="normal"
            class="xeditor-label"
            data-valid-type='{"digits":true,"max": 100}'
            >{{ $projItem->period_y }}</a>
    </td>
    <td>
        <span data-lang-r="year"></span>
        <span data-row-action class="row-action hidden" data-access-active="1">
            <button class="btn btn-danger" data-btn-action="delete" type="button">
                <i class="fa fa-trash"></i>
            </button>
            <button class="btn btn-primary btn-break" data-btn-row-add="proj" type="button">
                <i class="fa fa-plus"></i>
            </button>
        </span>
    </td>
</tr>
<tr data-id="{!!$projItem->id!!}" data-type="proj">
    <td class="text-right">
        <a href="#" data-name="pro[{!!$projItem->id!!}][period_m]" 
            data-placement="bottom" 
            data-type="text"
            data-edit-dom="submit"
            data-edit-type="normal"
            class="xeditor-label"
            data-valid-type='{"digits":true,"max": 12}'
            >{{ $projItem->period_m }}</a>
    </td>
    <td data-lang-r="month"></td>
</tr>
