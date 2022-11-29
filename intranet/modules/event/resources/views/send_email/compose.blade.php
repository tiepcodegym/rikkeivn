@extends('layouts.default')

@section('title')
{{ trans('event::view.Email compose') }}
@endsection

@section('css')
<?php use Rikkei\Core\View\CoreUrl; ?>
<link rel="stylesheet" href="{!!CoreUrl::asset('event/css/event_mail.css')!!}" />
<link rel="stylesheet" href="{{ URL::asset('lib/tag-it/css/jquery.tagit.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('lib/tag-it/css/tagit.ui-zendesk.min.css') }}" />
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-event-create" method="post" action="{!! route('event::send.email.employees.compose') !!}"
                      class="form-horizontal has-valid no-disabled" autocomplete="off" enctype="multipart/form-data"
                      data-form-submit="ajax" data-form-file="1"
                      data-submit-noti="{!!trans('event::view.are you sure send mail?')!!}"
                      data-delay-noti="0">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="to" class="col-sm-1 control-label required">{!! trans('event::view.To') !!}<em>*</em></label>
                                <div class="col-sm-11">
                                    <input name="to" class="form-control input-field" type="text" id="to" 
                                        value="" placeholder="to" />
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="to" class="col-sm-1 control-label required">{!! trans('event::view.Reply-to') !!}</label>
                                <div class="col-sm-11">
                                    <input name="reply" class="form-control input-field" type="text" id="reply" 
                                        value="pqa@rikkeisoft.com" placeholder="Reply" />
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="subject" class="col-sm-1 control-label required">{!! trans('event::view.Subject') !!}<em>*</em></label>
                                <div class="col-sm-11">
                                    <input name="subject" class="form-control input-field" type="text" id="subject" 
                                        value="" placeholder="{{ trans('event::view.Subject') }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="col-sm-1 control-label required">{{ trans('event::view.Content') }}<em>*</em></label>
                                <div class="col-sm-11 iframe-full-width">
                                    <textarea id="editor-content-event" class="text-editor" name="content"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group row">
                                <label for="attach" class="col-sm-1 control-label">{{ trans('event::view.File (max: 10MB)') }}</label>
                                <div class="col-sm-5" d-mail-files>
                                    <p class="mail-file-item" d-mail-file>
                                        <input class="form-control" type="file" name="file[]" d-mail-input="file">
                                        <button type="button" class="btn mail-remove-file" d-mail-btn="remove">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 align-center">
                            <button type="submit" class="btn-add btn-submit-ckeditor">{{ trans('event::view.Send mail') }}
                                <i class="fa fa-paper-plane loading-hidden-submit"></i>
                                <i class="fa fa-spin fa-refresh hidden loading-submit"></i>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/tag-it/js/tag-it.min.js') }}"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        RKfuncion.CKEditor.init(['editor-content-event']);
        $("#to").tagit();
        $("#reply").tagit();
        var rules = {
            'subject': {
                required: true,
            },
            'content': {
                required: true,
            }
        };
        jQuery.extend(jQuery.validator.messages, {
            required: requiredText,
        });
        $('#form-event-create').validate({
            rules: rules,
            messages: messages
        });
        var htmlFile = $('[d-mail-files]').html();
        $(document).on('change', '[d-mail-input="file"]', function () {
            var input = $(this);
            if (!input.val()) {
                return true;
            }
            $('[d-mail-files]').append(htmlFile);
        });
        $(document).on('click', '[d-mail-btn="remove"]', function () {
            if ($('[d-mail-input="file"]').length > 1) {
                $(this).closest('[d-mail-file]').remove();
            } else {
                $('[d-mail-input="file"]').val('');
            }
        });
    });
</script>
@endsection
