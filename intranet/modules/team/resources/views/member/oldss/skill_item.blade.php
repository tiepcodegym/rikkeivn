<?php use Rikkei\Core\View\View as CoreView; ?>
<tr data-id="{!!$skillData->id!!}" data-type="ski" class="form-group-select2">
    <td class="form-group-select2 td-dom-editable" data-s2-edit="{!!$key!!}">
        <a href="#" data-name="ski[{!!$skillData->id!!}][{!!$key!!}][tag_id]" 
            data-placement="bottom"
            data-type="select2"
            data-edit-dom="submit"
            data-edit-type="select2"
            class="xeditor-label"
            data-value="{!!$skillData->tag_id!!}"
            data-valid-type='{"required":true}'
            ></a>
    </td>
    @for ($i = 1; $i < 6; $i++)
        <td class="text-center"
            data-edit-name="ski[{!!$skillData->id!!}][{!!$key!!}][level]"
            data-edit-value="{!!$i!!}">
            <a href="#" data-name="ski[{!!$skillData->id!!}][{!!$key!!}][level]" 
                data-placement="top" 
                data-type="radiolist"
                data-edit-dom="submit"
                data-edit-type="radiolist"
                class="xeditor-label"
                data-mode="popup"
                data-flag-val="{!!$i!!}"
                data-value="{!!$skillData->level!!}"
                >
            </a>
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
    <td class="text-right">
        <a href="#" data-name="ski[{!!$skillData->id!!}][{!!$key!!}][exp_y]" 
            data-placement="bottom" 
            data-type="text"
            data-edit-dom="submit"
            data-edit-type="normal"
            class="xeditor-label"
            data-valid-type='{"digits":true,"max": 100}'
            data-inputclass="xeditor-min"
            >{{ $expValue }}</a>
    </td>
    <td>
        <a href="#" data-name="ski[{!!$skillData->id!!}][{!!$key!!}][exp_type]" 
            data-placement="bottom" 
            data-type="select"
            data-edit-type="normal"
            class="xeditor-label"
            data-edit-dom="submit"
            data-edit-label="select-lang"
            data-value="{!!$expType!!}"
            data-trans-values='{"year": "year", "month": "month"}'
            data-inputclass="xeditor-select"
            ></a>
        <span data-row-action class="row-action hidden" data-access-active="1">
            <button class="btn btn-danger" data-btn-action="delete" type="button">
                <i class="fa fa-trash"></i>
            </button>
            <button class="btn btn-primary btn-break" data-btn-row-add="{!!$key!!}" type="button">
                <i class="fa fa-plus"></i>
            </button>
        </span>
    </td>
</tr>
