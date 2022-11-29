<?php
use Rikkei\Core\View\View as CoreView;
?>
<tr data-id="{!!$projItem->id!!}" data-type="proj" data-lang="{{ $projItem->lang_code }}" class="row-proj hidden {{ $projItem->lang_code }}">
    <td class="editable" rowspan="2">
        <div class="tpin-inner">
            <p>
                <span class="text-bold tbl-td-left-margin wrap-text count{{$projItem->lang_code}}">{!! isset($number) ? $number : null !!}</span>
            </p>
        </div>
    </td>
    <td class="editable" rowspan="2">
        <div class="tpin-inner">
            <p>
                <span data-mode-dom="view" class="text-bold tbl-td-left-margin wrap-text"
                      data-col-fixed="proj_number" d-dom-proj="proj_number"
                      data-db-lang-view="proj_{!!$projItem->id!!}_number"></span>
                <span data-mode-dom="edit" class="hidden">
                    <input name="pro[{!!$projItem->id!!}][proj_number]" class="form-control border-none"
                           {!!$disabledInput!!} data-fg-dom="proj_number"
                           value="" data-db-lang="proj_{!!$projItem->id!!}_number" data-input-cv
                           placeholder="{!!trans('team::cv.Number project', [], null, 'en')!!}"/>
                </span>
            </p>
        </div>
    </td>
    <td class="editable" rowspan="1">
        <span data-mode-dom="view" class="text-bold tbl-td-left-margin wrap-text"
              data-text-short="50" d-dom-proj="name"
              data-db-lang-view="proj_{!!$projItem->id!!}_name"></span>
        <span data-mode-dom="edit" class="hidden">
            <input name="pro[{!!$projItem->id!!}][name]" class="form-control border-none"
                   data-valid-type='{"required":true,"maxlength":255}'{!!$disabledInput!!}
                   value="" data-db-lang="proj_{!!$projItem->id!!}_name" data-input-cv
                   placeholder="{!!trans('team::cv.Project name', [], null, 'en')!!}" />
        </span>
    </td>
    <td class="editable" rowspan="1">
        <span data-mode-dom="view" class="tbl-td-left-margin" d-dom-proj="role"></span>
        <span data-mode-dom="edit" class="hidden select2-fix-height text-center">
            <select name="pro[{!!$projItem->id!!}][role]" class="cv-tag role-select"></select>
        </span>
    </td>
    <td class="form-group-select2 editable" rowspan="2">
        <span data-mode-dom="view" class="tbl-td-left-margin wrap-text" d-dom-proj="lang"
              data-text-height="180"></span>
        <span data-mode-dom="edit" class="hidden select2-fix-height" data-view-type="tagit">
            <ul data-dom-tagui="language" name="pro[{!!$projItem->id!!}][lang][]" class="cv-tag">
                @if (isset($skillsProj[$projItem->id]['lang']) && count($skillsProj[$projItem->id]['lang']))
                    @foreach ($skillsProj[$projItem->id]['lang'] as $tagItem)
                        @if ($tagItem['id'])
                            @if (isset($tagData['language'][$tagItem['id']]))
                                <li>{{$tagData['language'][$tagItem['id']]}}</li>
                            @endif
                        @elseif ($tagItem['text'])
                            <li>{{$tagItem['text']}}</li>
                        @endif
                    @endforeach
                @endif
            </ul>
        </span>
    </td>
    <td class="form-group-select2 editable" rowspan="2">
        <span data-mode-dom="view" class="tbl-td-left-margin wrap-text" d-dom-proj="other"
              data-text-height="180"></span>
        <span data-mode-dom="edit" class="hidden select2-fix-height" data-view-type="tagit">
            <ul data-dom-tagui="dev_env" name="pro[{!!$projItem->id!!}][other][]" class="cv-tag">
                @if (isset($skillsProj[$projItem->id]['other']) && count($skillsProj[$projItem->id]['other']))
                    @foreach ($skillsProj[$projItem->id]['other'] as $tagItem)
                        @if ($tagItem['id'])
                            @if (isset($tagData['dev_env'][$tagItem['id']]))
                                <li>{{$tagData['dev_env'][$tagItem['id']]}}</li>
                            @endif
                        @elseif ($tagItem['text'])
                            <li>{{$tagItem['text']}}</li>
                        @endif
                    @endforeach
                @endif
            </ul>
        </span>
    </td>
    <td class="form-group-select2 editable" rowspan="2">
        <span data-mode-dom="view" class="tbl-td-left-margin" d-dom-proj="responsible"
              data-db-select-view="proj_{!!$projItem->id!!}_responsible"></span>
        <span data-mode-dom="edit" class="hidden select2-fix-height" data-view-type="tagit">
            @if (isset($skillsProj[$projItem->id]['res']))
                <script>
                    globalValueTrans.res[{!!$projItem->id!!}] = {!!json_encode($skillsProj[$projItem->id]['res'], JSON_HEX_TAG)!!};
                </script>
            @endif
            <ul data-dom-tagui="res" name="pro[{!!$projItem->id!!}][res][]" class="cv-tag"></ul>
        </span>
    </td>
    <td class="editable" rowspan="2">
        <div class="tpin-inner proj-edit-date">
            <p class="split-td split-date-top">
                <span data-mode-dom="view" class="tbl-td-left-margin" d-dom-proj="start_at"></span>
                <span data-mode-dom="edit" class="hidden">
                    <input name="pro[{!!$projItem->id!!}][start_at]" data-flag-type="date" class="form-control border-none height-45"
                           value="{{ $projItem->start_at }}" data-input-cv{!!$disabledInput!!}
                           placeholder="{!!trans('team::cv.start at', [], null, 'en')!!}"
                           data-fg-dom="proj-date-start" />
                </span>
            </p>
            <p>
                <span data-mode-dom="view" class="tbl-td-left-margin" d-dom-proj="end_at"></span>
                <span data-mode-dom="edit" class="hidden">
                    <input name="pro[{!!$projItem->id!!}][end_at]" data-flag-type="date" class="form-control border-none height-45"
                           value="{{ $projItem->end_at }}" data-input-cv{!!$disabledInput!!}
                           data-valid-type='{"greaterEqualThan":"[name=\"pro[{!!$projItem->id!!}][start_at]\"]"}'
                           placeholder="{!!trans('team::cv.end at', [], null, 'en')!!}"
                           data-fg-dom="proj-date-end" />
                </span>
            </p>
        </div>
    </td>
    <td class="text-right" rowspan="2">
        <div class="tbl-proj-height">
            <p>
                <span data-fg-dom="proj-period-y" d-dom-proj="period_y">{!!$projItem->period_y!!}</span>
                <span data-lang-r="year" d-dom-proj="period_y_label"></span>
            </p>
            <p>
                <span data-fg-dom="proj-period-m" d-dom-proj="period_m">{!!$projItem->period_m!!}</span>
                <span data-lang-r="month" d-dom-proj="period_m_label"></span>
            </p>
        </div>
    </td>
    @if (!$disabledInput)
    <td rowspan="2">
        <div class="overlay-dom hidden"></div>
        <p class="col-action">
            <button type="button" class="hidden btn btn-primary" d-dom-loading="tr">
                <i class="fa fa-spin fa-refresh"></i>
            </button>
            <button class="btn btn-success btn-ss-action hidden" data-btn-action="save" type="button">
                <i class="fa fa-floppy-o"></i>
            </button>
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

<tr data-id="{!!$projItem->id!!}" data-type="proj" data-lang="{{ $projItem->lang_code }}" class="row-proj hidden">
    <td class="editable" rowspan="1" style="border-top: 0">
        <span data-mode-dom="view" class="wrap-text text-italic tbl-td-left-margin"
              data-text-height="150"
              data-db-lang-view="proj_{!!$projItem->id!!}_description"></span>
        <span data-mode-dom="edit" class="hidden">
            <textarea name="pro[{!!$projItem->id!!}][description]" class="form-control resize-none border-none"
                      data-valid-type='{"maxlength":5000}' data-input-cv rows="3"
                      value="" data-db-lang="proj_{!!$projItem->id!!}_description"
                      placeholder="{!!trans('team::cv.Project description', [], null, 'en')!!}"
                      d-dom-proj="desc"></textarea>
        </span>
    </td>
    <td class="editable" rowspan="1" style="border-top: 0; border-right: 0;">
        <div class="tpin-inner">
            <p class="split-td">
                <span data-mode-dom="view" class="tbl-td-left-margin" d-dom-proj="total_member"></span>
                <span data-mode-dom="edit" class="hidden">
                    <input name="pro[{!! $projItem->id !!}][total_member]"
                           data-input-cv data-dom-input="total_member"
                           class="form-control border-none height-45 text-center"
                           type="number" data-valid-type='{"min":1}' value="{!! $projItem->total_member !!}"
                           placeholder="{!! trans('team::cv.total members', [], null, 'en') !!}" />
                </span>
            </p>
            <p>
                <span data-mode-dom="view" class="tbl-td-left-margin" d-dom-proj="total_mm"></span>
                <span data-mode-dom="edit" class="hidden">
                    <input name="pro[{!! $projItem->id !!}][total_mm]"
                           data-input-cv data-dom-input="total_mm"
                           class="form-control border-none height-45 text-center"
                           type="number" data-valid-type='{"min":0}' value="{{ $projItem->total_mm }}"
                           placeholder="{!! trans('team::cv.total mm', [], null, 'en') !!}" />
                </span>
            </p>
        </div>
    </td>
</tr>
