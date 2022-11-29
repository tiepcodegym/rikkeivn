<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Event\View\ViewEvent;

$fileMaxSize = ViewEvent::getPostMaxSize();
?>

@extends('layouts.default')

@section('title', trans('event::view.send_mail_off_title'))

@section('content')

<div class="box box-info">
    <div class="box-body">
        
    </div>
    <div class="box-body">
        <form id="form-mail-template" method="post" action="{{ route('event::mailoff.confirm_mail') }}" 
              class="form-horizontal has-valid" autocomplete="off" enctype="multipart/form-data">
            {!! csrf_field() !!}

            <div class="form-group row">
                <label for="csv_tet" class="col-sm-1 control-label required">{{ trans('event::view.File(excel, csv)') }} <em>*</em></label>
                <div class="col-sm-11">
                    <input class="form-control" type="file" name="csv_file" id="file_upload">
                </div>
            </div>

            <div class="form-group row">
                <label for="subject" class="col-sm-1 control-label required">{{ trans('event::view.Subject') }} <em>*</em></label>
                <div class="col-sm-11">
                    <input name="subject" class="form-control input-field" type="text" id="subject" 
                        value="{{ old('subject') ? old('subject') : ($subjectEmail ? $subjectEmail : 'Xác nhận xóa mail nhân viên đã nghỉ việc') }}" placeholder="{{ trans('event::view.Subject') }}" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-1 control-label required">{{ trans('event::view.Content') }} <em>*</em></label>
                <div class="col-sm-11 iframe-full-width">
                    <?php $contentMail = old('content') ? old('content') : $contentEmail; ?>
                    <textarea id="editor-content" class="text-editor" name="content">
                        @if ($contentEmail)
                            {{ $contentEmail }}
                        @else
                            {!! e('<p>Dear Anh/Chị <b>&#123;&#123; name &#125;&#125;,</b></p><p></p><p>Anh/Chị check giúp em (các) account sau nhé:</p>') !!}
                        @endif
                    </textarea>
                </div>
                <div class="col-sm-11 col-sm-offset-1 hint-note">
                    <p>&#123;&#123; {{ trans('event::view.Name') }} &#125;&#125;: {{ trans('event::view.Name') }}</p>
                    <p>&#123;&#123; {{ trans('event::view.Account') }} &#125;&#125;: {{ trans('event::view.Account') }}</p>
                </div>
            </div>

            <div class="align-center">
                <button type="submit" class="btn-add btn-submit-ckeditor">{{ trans('event::view.Send mail') }} <i class="fa fa-paper-plane"></i> <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="box box-info">
    <div class="box-body">
        <h4> <a href="{{ asset('event/files/ds_mail_nghi_viec.xlsx') }}"> {{ trans('event::view.Download CSV/Excel templates') }} <i class="fa fa-download"></i></a></h4>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        RKfuncion.CKEditor.init(['editor-content']);
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
        };
        var messages = {
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
    var MAX_FILE_SIZE = {{ $fileMaxSize }};
    var textErrorMaxFileSize = '<?php echo trans('event::message.file_max_size', ['max' => $fileMaxSize]) ?>';
</script>
@endsection
