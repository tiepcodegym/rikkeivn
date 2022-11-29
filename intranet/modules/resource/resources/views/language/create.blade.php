@extends('layouts.default')
<?php
use Carbon\Carbon;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\Languages;
use Rikkei\Core\View\CoreUrl;
?>
@section('title')
@if(isset($languageId) && $languageId->id)
{{ trans('resource::view.Languages.Edit.Edit languages edit') }}
@else
{{ trans('resource::view.Languages.Create.Create Languages') }}
@endif
@endsection
<?php
if(isset($languageId) && $languageId->id) { 
    $urlSubmit = route('resource::languages.postCreate',['id' => $languageId->id]);
    $checkEdit = true;
} else {
    $urlSubmit = route('resource::languages.postCreate');
    $checkEdit = false;
}
?>

@section('css')
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('resource/css/resource.css') }}" rel="stylesheet" type="text/css" >
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form method="post" action="{{$urlSubmit}}" 
                      enctype="multipart/form-data" autocomplete="off" id="frm-lang-level" class="form-horizontal form-sales-module">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name" class="col-sm-4 control-label align-right">
                                {{ trans('resource::view.Name') }} 
                                <em class="required">*</em></label>
                                <div class="col-sm-8">
                                    <input name="name" class="form-control input-field" type="text" id="company" aria-required="true" aria-invalid="true"
                                        value="{{ old('name',$languageId->name) }}" placeholder="{{ trans('resource::view.Languages.List.List Languages name') }}" />
                                    @if($checkEdit) 
                                        <input type="hidden" name="id" value="{{ old('id', $languageId->id) }}" />
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="language_level" class="col-sm-4 control-label align-right">
                                {{ trans('resource::view.Language level') }} </label>
                                <div class="col-sm-8">
                                    <input name="language_level" class="form-control input-field" type="text" id="language_level" aria-required="true" aria-invalid="true"
                                        value="{{ old('language_level',$languageId->levels) }}" placeholder="{{ trans('resource::view.Language level') }}" />
                                    <p class="hint"><i>{{ trans("resource::view.The levels are separated by commas") }}</i></p>
                                    @if($checkEdit) 
                                        <input type="hidden" name="id" value="{{ old('id', $languageId->id) }}" />
                                    @endif
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="row">
                        <div class="col-md-12 align-center">
                            <button class="btn-add" type="submit">@if($checkEdit)
                            {{ trans('resource::view.Languages.Edit.Edit Languages update') }}
                            @else
                            {{ trans('resource::view.Languages.Create.Create Languages') }}
                            @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
var requiredText = '{{trans("resource::message.Required field")}}';
var levelDuplicateText = '{{trans("resource::message.Language level is duplicated")}}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('resource/js/language/create.js') }}"></script>
@endsection