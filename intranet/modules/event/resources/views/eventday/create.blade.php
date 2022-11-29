@extends('layouts.default')

@section('title')
{{ trans('event::view.Send mail event') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<style>
    .box-mail-lang{
        padding-top: 7px;
    }
    .box-mail-lang input{
        margin-left: 20px;
        margin-right: 3px;
    }
    .box-mail-lang input:first-child{
        margin-left: 0;
    }
    .download-template{
        text-align: center;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-event-create" method="post" action="{{ route('event::eventday.send.email') }}" enctype="multipart/form-data"
                      class="form-horizontal form-submit-ajax-v2 has-valid" autocomplete="off" data-callback-success="preview">
                    {!! csrf_field() !!}
                    <input type="hidden" name="preview" value="0" />
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left row">
                                <label for="email_from" class="col-sm-1 control-label required">{{ trans('event::view.Sender') }} <em>*</em></label>
                                <div class="col-sm-11">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <input name="exclude[email_from_name]" class="form-control input-field" type="text" id="email_from_name" 
                                                   value="Rikkeisoft" placeholder="{{ trans('event::view.Name') }}" />
                                        </div>
                                        <div class="col-sm-4">
                                            <input name="exclude[email_from]" class="form-control input-field" type="text" id="email_from" 
                                                   value="{{ $userEmail }}" placeholder="{{ trans('event::view.Email') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left form-group-select2 row">
                                <label for="gender" class="col-sm-1 control-label required">{{ trans('event::view.Receive') }}<em>*</em></label>
                                <div class="col-sm-11 fg-valid-custom">
                                    <div class="col-sm-11 box-mail-lang">
                                        <label for="ipt-type-send-import">Import file</label>
                                     
                                    </div>
                                    <div class="row js-type-send js-content-receive" id="type-send-form">
                                        <div class="col-sm-3">
                                            <input type="file" name="excel_file" 
                                                required accept=".xlsx, .xls, .csv" 
                                                id="excel_file" class="form-control" placeholder="File import">
                                        </div>
                                        <div class="col-sm-3 download-template">
                                            <a href="{{ route('event::eventday.download_template') }}" style="padding: 0 20px 0 0;">Download the default</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left">
                                <label for="subject" class="col-sm-1 control-label required">{{ trans('event::view.Subject') }} <em>*</em></label>
                                <div class="col-sm-11" data-lang="ja">
                                    <input name="cc[event.eventday.company.subject.vn]" class="form-control input-field" type="text" id="subject"
                                           value="" placeholder="{{ trans('event::view.Subject') }}" required />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left">
                                <label for="subject" class="col-sm-1 control-label required">Content <em>*</em></label>
                                <div class="col-sm-11">
                                    <textarea class="form-control" rows="30" id="mail_content" name="mail_content" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left">
                                <label for="subject" class="col-sm-1 control-label required">Ngôn ngữ <em>*</em></label>
                                <div class="col-sm-11" style="padding-top: 7px;">
                                    <div class="wrapper-div">
                                        <input type="radio" id="cus_vn" name="customer_lang" value="cus_vn">
                                        <label for="cus_vn" style="margin-right: 20px;">Tiếng việt</label>
                                        <input type="radio" id="cus_en" name="customer_lang" value="cus_en">
                                        <label for="cus_en" style="margin-right: 10px;">Tiếng Anh</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    

                    <div class="row">
                        <div class="col-sm-12 align-center">
                            <button type="submit" class="btn-add btn-submit-ckeditor-v2" data-preview="0">{{ trans('event::view.Send mail') }} <i class="fa fa-paper-plane"></i> <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
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
<script type="text/javascript">
jQuery(document).ready(function ($) {
    selectSearchReload();
    CKEDITOR.config.height = 600;
    RKfuncion.CKEditor.init(['editor-content-birthday-ja']);
    RKfuncion.CKEditor.init(['editor-content-birthday-en']);
    RKfuncion.CKEditor.init(['editor-content-birthday-vn']);
    RKfuncion.CKEditor.init(['mail_content']);

    $.validator.addMethod("check_ck_add_method",
        function (value, element) {
            if (CKEDITOR.instances.mail_content.getData() == '') {
                return false;
            }
            return true;
        }, 'This field is required.'
    );
    var rules = {
        'exclude[email_from]': {
            required: true,
            email: true
        },
        'exclude[email_from_name]': {
            required: true
        },
        'exclude[email_from_pass]': {
            required: true
        },
        'item[gender]': {
            required: true
        },
        'item[company]': {
            required: true
        },
        'item[name]': {
            required: true
        },
        'item[email]': {
            required: true,
            email: true
        },
        // 'customer_lang': {
        //     required: true,
        // },
        'cc[event.eventday.company.subject.ja]': {
            required: true
        },
        'mail_content': {
            check_ck_add_method: true
        }
    };
    var messages = {
        'exclude[email_from]': {
            required: '{{ trans('event::view.This field is required') }}',
            email: '{{ trans('event::view.Please enter a valid email address') }}'
        },
        'exclude[email_from_name]': {
            required: '{{ trans('event::view.This field is required') }}'
        },
        'exclude[email_from_pass]': {
            required: '{{ trans('event::view.This field is required') }}'
        },
        'item[gender]': {
            required: '{{ trans('event::view.This field is required') }}'
        },
        'item[name]': {
            required: '{{ trans('event::view.This field is required') }}'
        },
        'item[company]': {
            required: '{{ trans('event::view.This field is required') }}'
        },
        'item[email]': {
            required: '{{ trans('event::view.This field is required') }}',
            email: '{{ trans('event::view.Please enter a valid email address') }}'
        },
        'cc[event.eventday.company.subject.ja]': {
            required: '{{ trans('event::view.This field is required') }}'
            },
        'cc[event.eventday.company.content.ja]': {
            required: '{{ trans('event::view.This field is required') }}'
        }
    };
    $('#form-event-create').validate({
        ignore: [],
        rules: rules,
        messages: messages,
        errorPlacement: function(error, element) {
            if ( element.is(":radio") ) {
                error.appendTo( element.parents('.wrapper-div') );
            } else {
                error.insertAfter( element );
            }
        }
    });
    $("input[name='mail_lang']").click(function () {
        var type = $(this).data('type'),
            subject = $(this).data('subject');
        $('.js-content-mail').hide();
        $(`.js-content-mail[data-type='${type}']`).show();
        $("input[name='cc[event.eventday.company.subject.ja]']").val(subject);
    });
    // $('.btn-submit-ckeditor').click(function() {
    //     var data = $(this).data('preview');
    //     $('#form-event-create input[name="preview"]').val(data);
    // });
    // RKfuncion.formSubmitAjax.preview = function(dom, data) {
    //     if (typeof data.html == 'undefined' || !data.html) {
    //         return true;
    //     }
    //     var iframe = $('<iframe style="height: 850px;">');
    //     $('.preview-send-email').html(iframe);
    //     setTimeout(function() {
    //     var doc = iframe[0].contentWindow.document;
    //     var body = $('body', doc);
    //     body.html(data.html);
    //     }, 1);
    // };
    $('[name="item[lang]"]').on('change', function() {
        $('[data-lang]').addClass('hidden');
        $('[data-lang] input, [data-lang] select').attr('disabled', 'disabled');
        var lang = $(this).val();
        $('[data-lang="' + lang + '"]').removeClass('hidden');
        $('[data-lang="' + lang + '"] input').removeAttr('disabled');
        $('[data-lang="' + lang + '"] select').removeAttr('disabled');
    });

    //Choose type Form - Import file
    $("input[name='type_send']").click(function () {
        var type_send = $(this).val();
        g_type_send = type_send;

        var type_send_form = `<div class="col-sm-3">
                                    <input name="item[company]" required class="form-control input-field" type="text" id="company" 
                                            value="" placeholder="{{ trans('event::view.Company') }}" />
                                </div>
                                <div class="col-sm-3">
                                    <input name="item[name]" required class="form-control input-field" type="text" id="name" 
                                            value="" placeholder="{{ trans('event::view.Name') }}" />
                                </div>
                                <div class="col-sm-3">
                                    <input name="item[email]" required class="form-control input-field" type="text" id="email" 
                                            value="" placeholder="{{ trans('event::view.Email') }}" />
                                </div>`;
        var type_send_import = `<div class="col-sm-3">
                                    <input name="item[excel_file]" required accept=".xlsx, .xls, .csv" class="form-control input-field" type="file" id="excel_file" 
                                        value="" placeholder="File import" />
                                </div>`;
        if (type_send == 'type_send_form') {
            $('.js-content-receive').html(type_send_form);
        } else {
            $('.js-content-receive').html(type_send_import);
        }
    });
});

</script>
@endsection