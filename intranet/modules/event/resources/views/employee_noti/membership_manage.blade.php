@extends('layouts.default')

@section('title')
Mail chúc mừng ngày nhân viêc chính thức
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
                <p><b>{{ trans('event::view.auto send email official') }}</b></p>
                <form id="form-event-create" method="post" action="{{ route('event::mail.membership.employee.save') }}" 
                      class="form-horizontal form-submit-ajax has-valid" autocomplete="off" data-callback-success="preview">
                    {!! csrf_field() !!}
                    <input type="hidden" name="preview" value="0" />
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group row">
                                <label for="subject" class="col-sm-12 required">{{ trans('event::view.Subject') }} <em>*</em></label>
                                <div class="col-sm-12">
                                    <input name="cc[event.mail.membership.employee.subject]" class="form-control input-field" type="text" id="subject" 
                                        value="{{ $subjectEmail }}" placeholder="{{ trans('event::view.Subject') }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="col-sm-12 required">{{ trans('event::view.Content') }} <em>*</em></label>
                                <div class="col-sm-12 iframe-full-width">
                                    <textarea id="editor-content-birthday" class="text-editor" name="cc[event.mail.membership.employee.content]">{{ $contentEmail }}</textarea>
                                </div>
                                <div class="col-md-6 hint-note">
                                    <p>&#123;&#123; {{ trans('event::view.Name') }} &#125;&#125;: {{ trans('event::view.name send email celebration') }}</p>
                                    <p>&#123;&#123; {{ trans('event::view.Email') }} &#125;&#125;: {{ trans('event::view.email send email celebration') }}</p>
                                    <p>&#123;&#123; {{ trans('event::view.Account') }} &#125;&#125;: {{ trans('event::view.account send email celebration') }}</p>
                                </div>
                                <div class="col-md-6 hint-note">
                                    <p>&#123;&#123; {{ trans('event::view.year') }} &#125;&#125;: {{ trans('event::view.year worked') }}</p>
                                    <p>&#123;&#123; {{ trans('event::view.official_date') }} &#125;&#125;: {{ trans('event::view.official date') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12 align-center">
                            <div class="form-group">
                                <button type="submit" class="btn-add btn-submit-ckeditor" data-preview="1">{{ trans('event::view.Preview') }}</button>
                                <button type="submit" class="btn-add btn-submit-ckeditor" data-preview="0">{{ trans('event::view.Save') }}</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left form-group-select2 row">
                                <label for="demo" class="col-md-2 control-label">{{ trans('event::view.Demo Receive') }}</label>
                                <div class="col-md-8 fg-valid-custom">
                                    <select name="demo[employee]" class="select-search"
                                        data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                        <option value="{{ $userCurrent->id }}">{{ CoreView::getNickName($userCurrent->email) }}</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn-add btn-submit-ckeditor" data-preview="2">{{ trans('event::view.Send mail') }} <i class="fa fa-paper-plane"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- modal success cofirm -->
<div class="modal fade" id="modal-preview-email">
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('event::view.Preview') }}</h4>
            </div>
            <div class="modal-body">
                <p><b>Subject: </b><span class="preview-send-email-subject"></span></p>
                <div class="preview-send-email"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-close" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div><!-- end modal warning cofirm -->
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        CKEDITOR.config.height = 600;
        RKfuncion.CKEditor.init(['editor-content-birthday']);
        var rules = {
            'cc[event.bitrhday.company.subject]': {
                required: true
            },
            'cc[event.bitrhday.company.subject.ja]': {
                required: true
            }
        };
        var messages = {
            'cc[event.bitrhday.company.subject]': {
                required: '{{ trans('core::view.This field is required') }}'
            },
            'cc[event.bitrhday.company.content]': {
                required: '{{ trans('core::view.This field is required') }}'
            }
        };
        $('#form-event-create').validate({
            rules: rules,
            messages: messages
        });
        $('.btn-submit-ckeditor').click(function() {
            var data = $(this).data('preview');
            $('#form-event-create input[name="preview"]').val(data);
        });
        RKfuncion.formSubmitAjax.preview = function(dom, data) {
            if (typeof data.html == 'undefined' || !data.html) {
                return true;
            }
            var iframe = $('<iframe style="height: 500px; width: 100%;">');
            $('.preview-send-email').html(iframe);
            $('.preview-send-email-subject').html(data.subject);
            setTimeout( function() {
                var doc = iframe[0].contentWindow.document;
                var body = $('body', doc);
                body.replaceWith(data.html);
                $('#modal-preview-email').modal('show');
            }, 1 );
        };
        RKfuncion.select2.init();
    });
</script>
@endsection
