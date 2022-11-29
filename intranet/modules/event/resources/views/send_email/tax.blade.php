@extends('layouts.default')

@section('title')
{{ trans('event::view.Send mail tax infomation') }}
@endsection

@section('css')
@endsection

@section('content')
<div class="box box-info">
    <div class="box-body">
        @include('event::tax.links')
    </div>
    <div class="box-body">
        <form id="form-event-create" method="post" action="{{ route('event::send.email.employees.post.tax') }}" 
              class="form-horizontal has-valid" autocomplete="off" enctype="multipart/form-data">
            {!! csrf_field() !!}

            <div class="form-group row">
                <label for="csv_tet" class="col-md-2 col-lg-1 control-label required">{{ trans('event::view.File(excel, csv)') }} <em>*</em></label>
                <div class="col-md-10 col-lg-11">
                    <input class="form-control" type="file" name="csv_file" id="csv_tet">
                </div>
            </div>
            
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="subject" class="col-sm-1 control-label required">{{ trans('event::view.Subject') }} <em>*</em></label>
                        <div class="col-sm-11">
                            <input name="subject" class="form-control input-field" type="text" id="subject" 
                                value="{{ $subjectEmail }}" placeholder="{{ trans('event::view.Subject') }}" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="col-sm-1 control-label required" style="padding-top: 0;">{{ trans('event::view.Format number') }}</label>
                        <div class="col-sm-11">
                            <label>
                                {{ trans('event::view.Yes') }} &nbsp;
                                <input name="format_number" id="format_number" type="radio" value="1" checked style="vertical-align: text-bottom;" />
                            </label>
                            {!! str_repeat('&nbsp;', 8) !!}
                            <label>
                                {{ trans('event::view.No') }} &nbsp;
                                <input name="format_number" id="format_number" type="radio" value="0" style="vertical-align: text-bottom;" />
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-2 col-lg-1 control-label required">{{ trans('event::view.Content') }} <em>*</em></label>
                <div class="col-md-10 col-lg-11 iframe-full-width">
                    <textarea id="editor-content-event" class="text-editor" name="content">{{ $contentEmail }}</textarea>
                </div>
                <div class="col-sm-12 col-sm-offset-1 hint-note">
                    <p>&#123;&#123; {{ trans('event::view.Name') }} &#125;&#125;: {{ trans('event::view.Name') }}</p>
                    <p>&#123;&#123; {{ trans('event::view.Account') }} &#125;&#125;: {{ trans('event::view.Account') }}</p>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn-add btn-submit-ckeditor">{{ trans('event::view.Send mail') }} <i class="fa fa-paper-plane"></i> <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="box box-info">
    <div class="box-body">
        <h4><a href="{{ asset('event/files/mau_phieu_thue.xlsx') }}"> {{ trans('event::view.Download CSV/Excel templates') }} <i class="fa fa-download"></i></a></h4>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        RKfuncion.CKEditor.init(['editor-content-event']);
        var rules = {
            'csv_file': {
                required: true,
            },
            'subject': {
                required: true
            },
            'content': {
                required: true
            }
        },
        messages = {
            'csv_file': {
                required: '{{ trans('event::view.This field is required') }}',
            },
            'subject': {
                required: '{{ trans('event::view.This field is required') }}',
            },
            'content': {
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
