@extends('layouts.default')

@section('title')
{{ trans('slide_show::view.Birthday slide') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('content')
<?php
use Rikkei\Core\View\View as CoreView;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-create-birthday-pattern" method="post" action="{{ route('slide_show::admin.slide.birthday.save') }}" class="form-horizontal" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group row">
                                <label for="title" class="col-sm-12 required">{{ trans('slide_show::view.Slide title') }} <em>*</em></label>
                                <div class="col-sm-12">
                                    <input name="birthday[title]" class="form-control input-field" type="text" id="title" 
                                        value="{{ ($title)? $title : trans('slide_show::view.Happy birthday') }}" placeholder="{{ trans('slide_show::view.Slide title') }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group row">
                            <label class="col-sm-12 required">{{ trans('slide_show::view.Content pattern') }} <em>*</em></label>
                                <div class="col-sm-12">
                                    <textarea name="birthday[content]" class="ckedittor-text" id="slideBirthdayContent">
                                    @if($content)
                                        {{ $content }}
                                    @endif
                                    </textarea>
                                    <div class="col-md-6" style="margin-top:10px">
                                        <p>&#123;&#123; name &#125;&#125;: {{trans('slide_show::view.Employee name')}}</p>
                                        <p>&#123;&#123; email &#125;&#125;: {{trans('slide_show::view.Employee email')}}</p>
                                        <p>&#123;&#123; account &#125;&#125;: {{trans('slide_show::view.Employee account')}}</p>
                                    </div>
                                    <div class="col-md-6" style="margin-top:10px">
                                        <p>&#123;&#123; old &#125;&#125;: {{trans('slide_show::view.Employee old')}}</p>
                                        <p>&#123;&#123; birthday &#125;&#125;: {{trans('slide_show::view.Employee date of birth')}}</p>
                                        <p>&#123;&#123; team &#125;&#125;: {{trans('slide_show::view.Employee team')}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left form-group-select2 row">
                                <div class="col-md-2 col-md-offset-5">
                                    <div class="form-group">
                                        <a  class="btn-add" target="_blank" href="{{route('slide_show::admin.slide.birthday.preview')}}"">{{trans('slide_show::view.Preview')}}</a>
                                        <button type="submit" class="btn-add" id="button-save">{{ trans('slide_show::view.Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ URL::asset('slide_show/js/edit.js') }}"></script>
<script type="text/javascript">
    var labelCompany = '{{ trans('slide_show::view.Company name') }}';
    var requiredCompany = '{{ trans('slide_show::message.Company name field is required') }}';
    var labelTitle = '{{ trans('slide_show::view.Title') }}';
    var requiredTitle = '{{ trans('slide_show::message.Title field is required') }}';
    jQuery(document).ready(function ($) {
        CKEDITOR.config.height = 300;
        RKfuncion.select2.init();
        CKEDITOR.replace('slideBirthdayContent');
        // validate title and content of slide
        var rules = {
            'birthday[title]': {
                required: true
            },
        };
        var messages = {
            'birthday[title]': {
                required: '{{ trans('core::view.This field is required') }}'
            },
        };
        $('#form-create-birthday-pattern').validate({
            rules: rules,
            messages: messages
        });
        $( "#button-save" ).on( "click", function(e) {
            e.preventDefault();
            if(!CKEDITOR.instances.slideBirthdayContent.getData().length) {
                $('#form-create-birthday-pattern').find("#cke_slideBirthdayContent").after('<p style="margin-top:5px" class="word-break error-validate error">' + '{{ trans('core::view.This field is required') }}' + '</p>');
            } else {
                $('#form-create-birthday-pattern').find(".error-validate").remove();
                $("#form-create-birthday-pattern").submit(); 
            }
        });
    });
</script>
@endsection
