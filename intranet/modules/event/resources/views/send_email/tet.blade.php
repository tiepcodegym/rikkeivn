@extends('layouts.default')

@section('title')
{{ trans('event::view.Send mail tet bonus') }}
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-event-create" method="post" action="{{ route('event::send.email.employees.tet.bonuses.post') }}" 
                      class="form-horizontal has-valid" autocomplete="off" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group row">
                                <label for="csv_tet" class="col-sm-1 control-label required">{{ trans('event::view.File(excel, csv)') }}<em>*</em></label>
                                <div class="col-sm-11">
                                    <input class="form-control" type="file" name="csv_tet" id="csv_tet">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="subject" class="col-sm-1 control-label required">{{ trans('event::view.Subject') }}<em>*</em></label>
                                <div class="col-sm-11">
                                    <input name="cc[event.send.email.tet.subject]" class="form-control input-field" type="text" id="subject" 
                                        value="{{ $subjectEmail }}" placeholder="{{ trans('event::view.Subject') }}" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="col-sm-1 control-label required">{{ trans('event::view.Content') }}<em>*</em></label>
                                <div class="col-sm-11 iframe-full-width">
                                    <textarea id="editor-content-event" class="text-editor" name="cc[event.send.email.tet.content]">{{ $contentEmail }}</textarea>
                                </div>
                                <div class="col-sm-11 col-sm-offset-1 hint-note">
                                    <p>&#123;&#123; {{ trans('event::view.Name') }} &#125;&#125;: {{ trans('event::view.Name') }}</p>
                                    <p>&#123;&#123; {{ trans('event::view.Account') }} &#125;&#125;: {{ trans('event::view.Account') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12 align-center">
                            <button type="submit" class="btn-add btn-submit-ckeditor">{{ trans('event::view.Send mail') }} <i class="fa fa-paper-plane"></i> <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="box box-info">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4>{{ trans('event::view.Format excel file') }}</h4>
                        <img src="{!!asset('event/images/template/bonus-tet.png')!!}" class="img-responsive">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        RKfuncion.CKEditor.init(['editor-content-event']);
        var rules = {
            'csv_tet': {
                required: true,
            },
            'cc[event.send.email.tet.subject]': {
                required: true
            },
            'cc[event.send.email.tet.content]': {
                required: true
            }
        };
        var messages = {
            'csv_tet': {
                required: '{{ trans('event::view.This field is required') }}',
            },
            'cc[event.send.email.tet.subject]': {
                required: '{{ trans('event::view.This field is required') }}',
            },
            'cc[event.send.email.tet.content]': {
                required: '{{ trans('event::view.This field is required') }}',
            }
        };
        $('#form-event-create').validate({
            rules: rules,
            messages: messages
        });
    });
</script>
@endsection
