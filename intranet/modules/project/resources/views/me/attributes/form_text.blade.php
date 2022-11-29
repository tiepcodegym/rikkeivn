<?php
    use Rikkei\Project\Model\MeAttribute;
    use Rikkei\Core\View\CoreUrl;
    
    if (!isset($langCode)) {
        $langCode = 'vi';
    }
    $itemLang = $item->attribuiteLangByLang($langCode);
?>

<div class="col-sm-12">
    <div class="form-group">
        <label>{{trans('project::me.Label')}}<sup>*</sup></label>
        {!! Form::text('lang['. $langCode .'][label]', $itemLang->label, ['class' => 'form-control', 'placeholder' => trans('project::me.Label')]) !!}
    </div>
    
    <div class="form-group">
        <label>{{trans('project::me.Name')}}<sup>*</sup></label>
        {!! Form::text('lang['. $langCode .'][name]', $itemLang->name, ['class' => 'form-control', 'placeholder' => trans('project::me.Name')]) !!}
    </div>

    <div class="form-group">
        <label>{{trans('project::me.Description')}} </label>
         {!! Form::textarea('lang['. $langCode .'][description]', $itemLang->description, ['class' => 'form-control', 'rows' => 3]) !!}
    </div>
</div>
