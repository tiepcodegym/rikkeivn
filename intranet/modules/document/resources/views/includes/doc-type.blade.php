<?php
use Rikkei\Document\View\DocConst;
?>

<div class="form-group">
    <?php
    $checkedTypes = old('type_ids');
    $checkedTypeItems = null;
    if ($item) {
        $checkedTypeItems = $item->types;
    }
    if (!$checkedTypes) {
        $checkedTypes = $checkedTypeItems ? $checkedTypeItems->lists('id')->toArray() : [];
    }
    ?>
    <label>
        {{ trans('doc::view.Document types') }} &nbsp;&nbsp;
        @if ($checkedTypeItems && !$checkedTypeItems->isEmpty())
        <span>({{ $checkedTypeItems->implode('name', ', ') }})</span>
        @endif
    </label>
    <label class="inline"><input type="checkbox" class="checkbox-all" {{ $disabled }}> {{ trans('doc::view.Select all') }}</label>
    <div class="checkbox-group margin-bottom-5" id="type_checkbox">
        <ul class="list-unstyled">
            {!! DocConst::toNestedCheckbox($listTypes, $checkedTypes, 'type_ids[]', !$permisEdit) !!}
        </ul>
    </div>
    @if ($permisEdit)
        <div class="add-group">
            <button class="btn btn-default margin-bottom-5" type="button" data-toggle="collapse" data-target="#add_type_box">
                <i class="fa fa-plus"></i>
            </button>
            <div class="collapse form-add-type" id="add_type_box" data-url="{{ route('doc::admin.type.save') }}">
                <div class="well well-sm">
                    <div class="form-group">
                        <label>{{ trans('doc::view.Name') }} <em class="required">*</em></label>
                        <input type="text" class="form-control type-name">
                    </div>
                    <div class="form-group">
                        <label>{{ trans('doc::view.Parent') }}</label>
                        <select class="select-search form-control type-parent" style="width: 100%;">
                            <option value="">&nbsp;</option>
                            {!! DocConst::toNestedOptions($listTypes, []) !!}
                        </select>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn-add-type btn btn-success">{{ trans('doc::view.Create') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

