@extends('layouts.default')

@section('title')
{{ trans('event::view.Send mail event') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
@endsection

@section('content')
<div class="row div-response" style="display: none; margin-bottom: 10px;">
    <div class="flash-message">
        <div class="alert div-alert">
            <ul class="ul-res"></ul>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-event-create" method="post" action="{{ route('event::brithday.send.email') }}" enctype="multipart/form-data"
                      class="form-horizontal has-valid" autocomplete="off">
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
                                                value="Rikkeisoft Event" placeholder="{{ trans('event::view.Name') }}" />
                                        </div>
                                        <div class="col-sm-4">
                                            <input name="exclude[email_from]" class="form-control input-field" type="text" id="email_from" 
                                                value="event@rikkeisoft.com" placeholder="{{ trans('event::view.Email') }}" />
                                        </div>
                                        <div class="col-sm-4">
                                            <input name="exclude[email_from_pass]" class="form-control input-field" type="password" id="email_from_pass" 
                                                value="" placeholder="App pass" />
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
                                            <a href="{{ route('event::brithday.download_template') }}" style="padding: 0 20px 0 0;">Download template default</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left">
                                <label for="lang" class="col-sm-1 control-label required">{{ trans('event::view.Language') }}</label>
                                <div class="col-sm-11 form-group-select2">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <select name="item[lang]" class="select-search form-control" id="lang">
                                                @foreach ($langOptions as $optionValue => $optionLabel)
                                                    <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- <div class="col-md-3 checkbox" data-lang="ja">
                                            <label for="show_tour"><input type="checkbox" class="" id="show_tour" name="item[show_tour]" value="1" checked> {{ trans('event::view.show tour') }}?</label>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left">
                                <label for="subject" class="col-sm-1 control-label required">{{ trans('event::view.Subject') }} <em>*</em></label>
                                <div class="col-sm-11 hidden" data-lang="en">
                                    <input name="cc[event.bitrhday.company.subject]" class="form-control input-field" type="text" id="subject" 
                                        value="{{ $subjectEmail }}" placeholder="{{ trans('event::view.Subject') }}" disabled />
                                </div>
                                <div class="col-sm-11" data-lang="ja">
                                    <input name="cc[event.bitrhday.company.subject.ja]" class="form-control input-field" type="text" id="subject" 
                                        value="{{ $subjectEmailJa }}" placeholder="{{ trans('event::view.Subject') }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12 align-center">
                            <button type="submit" class="btn-add btn-submit-event-birthday">{{ trans('event::view.Send mail') }}</button>
                        </div>
                    </div>
                </form>
                <div class="form-group" id="process" style="display:none; margin-top: 15px;">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                            <span id="process_data">0</span> / <span id="total_data">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script type="text/javascript">
    var TEXT_SUCCESS = '{{ Lang::get('event::message.Mail sent success') }}';
    var URL_MAIL_LIST = '{{ route('event::brithday.company.email_cust.list') }}';
    var URL_CUS_LIST = '{{ route('event::brithday.company.list') }}';

    jQuery(document).ready(function ($) {
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
            'cc[event.bitrhday.company.subject]': {
                required: true
            },
            'cc[event.bitrhday.company.subject.ja]': {
                required: true
            }
        };
        var messages = {
            'exclude[email_from]': {
                required: '{{ trans('core::view.This field is required') }}',
                email: '{{ trans('core::view.Please enter a valid email address') }}'
            },
            'exclude[email_from_name]': {
                required: '{{ trans('core::view.This field is required') }}'
            },
            'exclude[email_from_pass]': {
                required: '{{ trans('core::view.This field is required') }}'
            },
            'cc[event.bitrhday.company.subject]': {
                required: '{{ trans('core::view.This field is required') }}'
            },
            'cc[event.bitrhday.company.subject.ja]': {
                required: '{{ trans('core::view.This field is required') }}'
            }
        };
        // $('#form-event-create').validate({
        //     rules: rules,
        //     messages: messages
        // });

        $('.btn-submit-event-birthday').click(function(e) {
            e.preventDefault();
            $("#form-event-create").validate({
                rules: {
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
                    'cc[event.bitrhday.company.subject]': {
                        required: true
                    },
                    'cc[event.bitrhday.company.subject.ja]': {
                        required: true
                    }
                },
                messages: {
                    'exclude[email_from]': {
                        required: '{{ trans('core::view.This field is required') }}',
                        email: '{{ trans('core::view.Please enter a valid email address') }}'
                    },
                    'exclude[email_from_name]': {
                        required: '{{ trans('core::view.This field is required') }}'
                    },
                    'exclude[email_from_pass]': {
                        required: '{{ trans('core::view.This field is required') }}'
                    },
                    'cc[event.bitrhday.company.subject]': {
                        required: '{{ trans('core::view.This field is required') }}'
                    },
                    'cc[event.bitrhday.company.subject.ja]': {
                        required: '{{ trans('core::view.This field is required') }}'
                    }
                },
            }).form();

            $(this).text('Đang gửi...').prop("disabled", true);
            let $this = $(this),
                $button = $('body').find('.btn-submit-event-birthday'),
                $form = $this.closest('form'),
                formData = new FormData($form[0]);

            if ($form.find('label.error').text().length > 0) {
                $button.text('Gửi mail').prop("disabled", false);
                return false;
            }

            $.ajax({
                type: "POST",
                url: '{{ route('event::brithday.send.email.count_file') }}',
                data: formData,
                dataType:"json",
                contentType: false,
                cache: false,
                processData: false
            }).done(function (data) {
                if (data.status == -1) {
                    let html = '';
                    jQuery.each(data.errors, function( i, val ) {
                        html += '<li>'+val+'</li>';
                    });
                    $('.div-response').show();
                    $('.div-alert').addClass("alert-danger").removeClass("alert-success");
                    $('.ul-res').html(html);
                }
                if (data.status == 1) {
                    $('#total_data').text(data.total_line);
                    start_import(formData);
                    var interval_obj = setInterval(function(){
                        $.ajax({
                            type: "POST",
                            url: '{{ route('event::brithday.send.email.process_file') }}',
                            data: formData,
                            processData: false,
                            contentType: false,
                        }).done(function (data) {
                            var number = data.number;
                            var total_data = $('#total_data').text();
                            var width = Math.round((number/total_data)*100);
                            $('#process_data').text(number);
                            $('.progress-bar').css('width', width + '%');
                            if(width >= 100) {
                                clearInterval(interval_obj);
                                $.ajax({
                                    type: "POST",
                                    url: '{{ route('event::brithday.send.email.reset_mail') }}',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                }).done(function (data) {
                                    
                                });

                                $('#process').css('display', 'none');
                                $("input[name='excel_file']").val(null);

                                $('.div-response').show();
                                $('.div-alert').addClass("alert-success").removeClass("alert-danger");
                                $('.ul-res').html('<li>'+TEXT_SUCCESS+'. </li><li>Kiểm tra danh sách gửi mail: <a target="_blank" href='+URL_MAIL_LIST+'>'+URL_MAIL_LIST+'</a>'+'</li><li>Quản lý thông tin đăng ký của khách hàng tại: <a target="_blank" href='+URL_CUS_LIST+'>'+URL_CUS_LIST+'</a>'+'</li>');
                                $('body').find('.btn-submit-event-birthday').text('Gửi mail').prop("disabled", false);
                            }
                        }).fail(function () {
                            clearInterval(interval_obj);
                        });
                    }, 2000);
                    $('#process_data').text(0);
                    $('.progress-bar').css('width', '0%');
                }
            });
        });

        $('[name="item[lang]"]').on('change', function() {
            $('[data-lang]').addClass('hidden');
            $('[data-lang] input, [data-lang] select').attr('disabled', 'disabled');
            var lang = $(this).val();
            $('[data-lang="' + lang + '"]').removeClass('hidden');
            $('[data-lang="' + lang + '"] input').removeAttr('disabled');
            $('[data-lang="' + lang + '"] select').removeAttr('disabled');
        });

        $("input[name='excel_file']").on('change', function() {
            checkExcel();
        });
        $("input[name='exclude[email_from]']").on('change', function() {
            checkExcel();
        });
        function checkExcel() {
            let $form = $('#form-event-create'),
                formData = new FormData($form[0]);
                
            $.ajax({
                type: "POST",
                url: '{{ route('event::brithday.check_email_excel') }}',
                data: formData,
                processData: false,
                contentType: false,
            }).done(function (data) {
                if (data.status == -1) {
                    let html = '<p>'+data.message+'</p>';
                    jQuery.each( data.errors, function( i, val ) {
                        html += '<li>'+val+'</li>';
                    });
                    $.confirm({
                        title: 'Confirm!',
                        content: html,
                        buttons: {
                            confirm: function () {
                                // $.alert('Yes!');
                            },
                            somethingElse: {
                                text: 'No',
                                btnClass: 'btn-red',
                                action: function(){
                                    $("input[name='excel_file']").val(null);
                                }
                            }
                        }
                    });
                }
            });
        }

        function start_import(formData) {
            $('#process').css('display', 'block');
            $.ajax({
                type: "POST",
                url: '{{ route('event::brithday.send.email') }}',
                data: formData,
                processData: false,
                contentType: false,
            }).done(function (data) {
                if (data.status == -1) {
                    $('.div-response').show();
                    $('.div-alert').addClass("alert-danger").removeClass("alert-success");
                    $('.ul-res').html('<li>'+data.message+'. Kiểm tra danh sách gửi mail: <a href='+URL_MAIL_LIST+'>'+URL_MAIL_LIST+'</a>'+'</li>');
                }
            });
        }
    });
</script>
@endsection