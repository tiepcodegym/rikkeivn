<?php use Rikkei\Core\View\View as CoreView;?>
<tr data-id="{!!$skillData->id!!}" data-type="{!!$key!!}" data-group="ski" class="ts-body">
    <td class="form-group-select2 editable">
        <span data-mode-dom="view" class="tbl-td-left-margin wrap-text" data-text-short="15"
            d-dom-skill="name"></span>
        <span data-mode-dom="edit" class="hidden select2-no-border fg-valid-custom">
            <select name="ski[{!!$skillData->id!!}][{!!$key!!}][tag_id]" data-select2-dom="1"
                placeholder="{!!trans('team::cv.' . $label['ph'], [], null, 'en')!!}"
                class="hidden input-valid-custom" data-input-cv{!!$disabledInput!!} data-select2-search="1"
                data-valid-type='{"required":true}'>
                <option value="">&nbsp;</option>
                @if (isset($tagData[$key]) && count($tagData[$key]))
                    @foreach ($tagData[$key] as $tagId => $tagName)
                        <option value="{!!$tagId!!}"{!!$tagId == $skillData->tag_id ? ' selected' : ''!!}>{{$tagName}}</option>
                    @endforeach
                @endif
            </select>
        </span>
    </td>
    @for ($i = 1; $i < 6; $i++)
        <td class="text-center editable">
            <span data-mode-dom="view" class="tbl-td-left-margin" data-html-val="checkTr"></span>
            <span data-mode-dom="edit" class="hidden">
                <input type="radio" name="ski[{!!$skillData->id!!}][{!!$key!!}][level]" 
                    value="{!!$i!!}" data-input-cv{!!$skillData->level == $i ? ' checked' : ''!!}
                    d-dom-skill="level" />
            </span>
        </td>
    @endfor
    <td class="text-right editable">
        <div class="tpin-inner split-exper">
            <span>
                <span data-mode-dom="view" class="tbl-td-left-margin" d-dom-skill="exper_y"></span>
                <span data-mode-dom="edit" class="hidden">
                    <input name="ski[{!!$skillData->id!!}][{!!$key!!}][exp_y]" class="form-control border-none"
                        value="{{ $skillData->exp_y }}" data-input-cv{!!$disabledInput!!}
                        data-valid-type='{"digits":true,"max": 50}'
                        placeholder="{!!trans('team::cv.year', [], null, 'en')!!}" />
                </span>
                <em data-lang-r="Y" d-dom-skill="exper_y_label"></em>
            </span>
            <span data-show-mode="view">-</span>
            <span>
                <span data-mode-dom="view" class="tbl-td-left-margin" d-dom-skill="exper_m"></span>
                <span data-mode-dom="edit" class="hidden">
                    <input name="ski[{!!$skillData->id!!}][{!!$key!!}][exp_m]" class="form-control border-none"
                        value="{{ $skillData->exp_m }}" data-input-cv{!!$disabledInput!!}
                        data-valid-type='{"digits":true,"max": 12}'
                        placeholder="{!!trans('team::cv.month', [], null, 'en')!!}"/>
                </span>
                <em data-lang-r="M" d-dom-skill="exper_m_label"></em>
            </span>
        </div>
    </td>
    @if (!$disabledInput)
        <td class="text-right">
            <div class="overlay-dom hidden"></div>
            <p data-col-fixed="action">
                <button type="button" class="hidden btn btn-primary" d-dom-loading="tr">
                    <i class="fa fa-spin fa-refresh"></i>
                </button>
                <button class="btn btn-success btn-ss-action hidden" data-btn-action="save" type="button">
                    <i class="fa fa-floppy-o"></i>
                </button>
                <?php /*<button class="btn btn-primary btn-ss-action hidden" data-btn-action="cancel" type="button">
                    <i class="fa fa-ban"></i>
                </button>*/ ?>
                <button class="btn btn-primary btn-ss-action" data-btn-action="edit" type="button">
                    <i class="fa fa-pencil"></i>
                </button>
                <button class="btn btn-danger btn-ss-action" data-btn-action="delete" type="button">
                    <i class="fa fa-trash"></i>
                </button>
            </p>
        </td>
    @endif
</tr>
