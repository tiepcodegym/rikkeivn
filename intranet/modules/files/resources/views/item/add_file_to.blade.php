@extends('files::layout.file_layout')

@section('title')
    {{ trans('files::view.File text') }}
@endsection
<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Files\Model\ManageFileText;
use Rikkei\Team\View\TeamList;

$urlCheckCodeFileExist = route('file::file.check-code-file-exist');
$typeCvdi = ManageFileText::CVDI;
$typeCvden = ManageFileText::CVDEN;
$teamsOptionAll = TeamList::toOption(null, true, false);
?>
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <style type="text/css">
        .displayNone {
            display: none;
        }
    </style>
@endsection

@section('sidebar-common')
@include('files::include.sidebar_leave')
@endsection

@section('content-common')
    <div class="row">
        <div class="col-md-12">
            <!-- Box register -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title managetime-box-title">{{ trans('files::view.Register File Text') }}</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    <div class="col-lg-10 col-lg-offset-1">
                        <form role="form" method="post" action="{{ route('file::file.postAddFile') }}" class="managetime-form-register" id="form-register-file" enctype="multipart/form-data" autocomplete="off">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label">{{ trans('files::view.Type') }}</label>
                                                    <div class="input-box">
                                                        <select id="type" class="form-control" name="type">
                                                            <option value="2">{{ trans('files::view.Công văn đi') }}</option>
                                                            <option value="1" selected="">{{ trans('files::view.Công văn đến') }}</option>
                                                        </select>
                                                    </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Số kí hiệu') }} <em> *</em></label>
                                                <div class="input-box">
                                                    <input type="text" name="codeText" class="form-control codeText" value="{{ old('codeText') }}" />
                                                    <label id="codeText-error" class="error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                    <p class='codeError'></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row option1 displayNone">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Số đến') }} <em> *</em></label>
                                                <div class="input-box">
                                                    <input type="text" name="numberTo" class="form-control" value="{{ !empty($numberTo) ? $numberTo + 1 : '1' }}" readonly />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label">{{ trans('files::view.Loại văn bản') }}</label>
                                                <div class="input-box">
                                                    <select class="form-control type_file" name="type_file">
                                                            <option value="2">{{ trans('files::view.Công văn') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row option1 displayNone">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Ngày văn bản đến') }}<em> *</em></label>
                                                <div class="input-box">
                                                    <div class='input-group date' id="datetimepicker-date_file_send">
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                        <input type="text" name="date_file_send" class="form-control" id="dateOption1" value="" />
                                                    </div>
                                                    <label id="date_file_send-error" class="date_file_send-error error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                              <label class="control-label required">{{ trans('files::view.Ngày văn bản') }}<em> *</em></label>
                                                <div class="input-box">
                                                    <div class='input-group date' id='datetimepicker-date_file'>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                        <input type="text" name="date_file" class="form-control dateOption2" value="" />
                                                    </div>
                                                    <label id="date_file-error" class="date_file-error error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row option1 displayNone">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label">{{ trans('files::view.Nơi gửi') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="file_to" class="form-control" value="{{ old('file_to') }}" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Đơn vị nhận văn bản') }} <em> *</em></label>

                                                <div class="team-select-box">
                                                    <div class="input-box">
                                                        <select name="groupTeam" id="groupTeam"
                                                            class="form-control select-search"
                                                            autocomplete="off">
                                                                <option value="0">&emsp;</option>
                                                                @foreach($teamsOptionAll as $option)
                                                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                                @endforeach
                                                        </select>
                                                        <label id="groupTeam-error" class="groupTeam-error error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-sm-6 form-group form-group-select2 option2">
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required managetime-label">{{ trans('files::view.Nơi lưu bản gốc') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="save_file" class="form-control" value="{{ old('save_file') }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="form-group">
                                            <label class="col-sm-12 control-label required">{{ trans('files::view.Trích Yếu') }} <em>*</em></label>
                                                <div class="col-sm-12 control-label">
                                                    <textarea id="quote_text" class="text-editor" name="quote_text"></textarea>
                                                    <label id="quote-error" class="quote-error error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <br/>

                                        <div class="form-group">
                                            <label class="control-label required">{{ trans('files::view.Ghi chú') }}</label>
                                            <div class="input-box">
                                                <textarea name="note_text" id="note_text" class="form-control">{{ old('note_text') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12 form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Tệp nội dung') }}</label>
                                                <div class="input-box">
                                                    <input type="file" name="file_content" id="file_content">
                                                    <p class="fileError"></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row displayNone showFormMail">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('manage_time::view.Related persons need notified') }} <em> *</em></label>
                                                <div class="input-box">
                                                    <select name="related_persons_list[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple>
                                                    </select>
                                                    <p class="search-employee-error error displayNone">{{ trans('files::view.The field is required') }}</p>
                                                </div>
                                            </div>

                                            @if (isset($groupEmail) && count($groupEmail))
                                                <div class="col-sm-6 form-group form-group-select2">
                                                    <label class="control-label">{{ trans('manage_time::view.Group email need notified') }}</label>
                                                    <div class="input-box">
                                                        <select name="group_email[]" class="form-control group-email" multiple>
                                                            @foreach($groupEmail as $value)
                                                                <option value="{{$value}}">{{substr($value, 0, strrpos($value, '@'))}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <div class="input-box">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" name="sendMail" id="sendMail" value="1">{{ trans('files::view.Gửi Mail') }} 
                                                        </label>    
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary submit-save" onclick="return checkFileForm('{{ $urlCheckCodeFileExist }}');"><i class="fa fa-floppy-o"></i> {{ trans('files::view.Save') }} </button>
                                        <input type="hidden" id="check_submit" name="" value="0">
                                        <button type="submit" class="btn btn-danger displayNone showFormMail" onclick="return checkClickEmail('{{ $urlCheckCodeFileExist }}');" ><i class="fa fa-envelope-square"></i> {{ trans('files::view.Send Mail') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /. box -->
        </div>
    </div>
    <!-- /.row -->
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script src="https://cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>
    <script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            CKEDITOR.replace('quote_text').on('key',
                function(e){
                    setTimeout(function(){
                        quote_text = document.getElementById('quote_text').value = e.editor.getData();
                        if (quote_text.length == '') {
                            $('#quote-error').show();
                        } else {
                            $('#quote-error').hide();
                        }
                    },10);
                }
            );;

            var error = "{{ trans('files::view.The field is required') }}";
            var idTeam = $('#groupTeam').val();
            var code = $('#groupTeam option:selected').attr("code");
            var type = $('#type').val();
            var valSelectEmp = $('#selectEmp :selected').attr('attrnew');
            var numberTo = $("input[name=numberTo]").val();
            var nameTeam = $('#groupTeam :selected').text();
            $("#tickSinger").html(valSelectEmp)

            if ($('#type').val() == 2) {
                $('input[name="numberTo"]').val('');
            } else {
                $('input[name="numberGo"]').val('');
            }

            if (type == 1) {
                $('div.option2').addClass('displayNone');
                $('div.option1').removeClass('displayNone');
                $('input[name="numberGo"]').val('');
                $('input[name="numberTo"]').val('{{ !empty($numberTo) ? $numberTo + 1 : '1' }}');
            } else {
                $('div.option1').addClass('displayNone');
                $('div.option2').removeClass('displayNone');
                $('input[name="numberTo"]').val('');
                $('input[name="numberGo"]').val('{{ !empty($numberGo) ? $numberGo + 1 : '1' }}');
            }

            $("#form-register-file").validate({
                rules: {
                    codeText: "required"
                },
                messages: {
                    codeText: error
                }
            });

            var date = new Date();
            date.setDate(date.getDate());
            $('#dateRelease').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                defaultDate: date,
                weekStart: 1,
                todayHighlight: true,
                onSelect: function () {
                    selectedDate = $.datepicker.formatDate("yyyy-mm-dd", $(this).datepicker('getDate'));
                }
            });
            $("#dateRelease").datepicker("setDate", date);

            $('.dateOption2').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                weekStart: 1,
                todayHighlight: true,
                onSelect: function () {
                    selectedDate = $.datepicker.formatDate("yyyy-mm-dd", $(this).datepicker('getDate'));
                }
            });
            $(".dateOption2").datepicker("setDate", date);

            $('#dateOption1').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                weekStart: 1,
                todayHighlight: true,
                onSelect: function () {
                    selectedDate = $.datepicker.formatDate("yyyy-mm-dd", $(this).datepicker('getDate'));
                }
            });

            $("#dateOption1").datepicker("setDate", date);

            $('#groupTeam').change(function() {
                checkGroupTeam = $('#groupTeam :selected').val();
                if (checkGroupTeam == 0) {
                    nameTeam = $('#groupTeam :selected').text().trim();
                    numberTo = $("input[name=numberTo]").val();
                    $('#groupTeam-error').show();
                } else {
                    nameTeam = $('#groupTeam :selected').text().trim();
                    numberTo = $("input[name=numberTo]").val();
                    $('#groupTeam-error').hide();
                }
               
            });

            $('#type').change(function() {
                var valType = $(this).val();
                if (valType == 2) {
                    window.location.href = '{{ route("file::file.add", $typeCvdi) }}';
                    $('div.option2').addClass('displayNone');
                    $('div.option1').removeClass('displayNone');
                }
            });

            $('#file_content').change(function(){
                $('.fileError').html('');
                messErrFile = '{{ trans('files::view.Only allow file csv, xlsx, doc, xls, pdf') }}';
                fileExtension = ['csv', 'xlsx', 'xls', 'doc', 'pdf'];
                if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    $('.fileError').append('<div class="error">'+ messErrFile +'</div>');
                    return false;
                } else {
                    $('.fileError').html('');
                    return true;
                }

            })


            $("#sendMail").click(function() {
                if($('#sendMail').is(':checked')) {
                    $('.showFormMail').removeClass('displayNone');
                    $('.submit-save').addClass('displayNone');
                    $('.select-search-employee').html('');
                } else {
                    $('.showFormMail').addClass('displayNone');
                    $('.submit-save').removeClass('displayNone');
                    $('.select-search-employee').html('');
                }
            });

            $(".group-email").select2({
                tags: true,
                placeholder: "<?php echo trans('files::view.Group email') ?>",
            });
        });

        $("#selectEmp").change(function() {
            valSelectEmp = $('#selectEmp :selected').attr('attrnew');
            if (valSelectEmp == undefined) {
                $("#tickSinger").html('');
            } else {
                $("#tickSinger").html(valSelectEmp);
            }
        })

        $('.dateOption2').change(function() {
            dateFile = $('.dateOption2').val();
            if (dateFile.trim() == '') {
                $('#date_file-error').show();
            } else {
                $('#date_file-error').hide();
            }
        });

        $('#dateOption1').change(function() {
            dateFile = $('#dateOption1').val();
            if (dateFile.trim() == '') {
                $('#date_file_send-error').show();
            } else {
                $('#date_file_send-error').hide();
            }
        });

        $('.select-search-employee').change(function() {
            employee = $('.select-search-employee').val();
            if (employee == null) {
                $('.search-employee-error').show();
            } else {
                $('.search-employee-error').hide();
            }
        });

        $('.codeText').change(function() {
            codeText = $('.codeText').val();
            if (codeText.trim() == '') {
                $('#codeText-error').show();
            } else {
                $('#codeText-error').hide();
                urlCheckCodeFileExist = '<?php echo route('file::file.check-code-file-exist');?>';
                checkExistRegister(urlCheckCodeFileExist);
            }
        });
        /*check validate form click button save*/
        function checkFileForm(urlCheckCodeFileExist) {
            var status = 1;
            $('#check_submit').val(1);

            if (checkExistRegister(urlCheckCodeFileExist)) {
                status = 0;
            }

            codeText = $('.codeText').val();
            if (codeText.trim() == '') {
                $('#codeText-error').show();
                status = 0;
            }

            date_file_send = $('#datetimepicker-date_file_send').datepicker('getDate');;
            if (date_file_send == null) {
                $('#date_file_send-error').show();
                status = 0;
            }

            date_file = $('#datetimepicker-date_file').datepicker('getDate');
            if (date_file == null) {
                $('#date_file-error').show();
                status = 0;
            }

            quote = CKEDITOR.instances["quote_text"].getData();
            if (quote.trim() == '') {
                $('#quote-error').show();
                status = 0;
            }

            checkGroupTeam = $('#groupTeam :selected').val();
            if (checkGroupTeam == 0) {
                $('#groupTeam-error').show();
                status = 0;
            }

            checkGroupTeam = $('#groupTeam :selected').val();
            if (checkGroupTeam == 0) {
                $('#groupTeam-error').show();
                status = 0;
            }

            if (status == 0) {
                return false;
            } 
            return true;
        }

        /*check validate form click button email*/
        function checkClickEmail(urlCheckCodeFileExist) {
            var status = 1;
            $('#check_submit').val(1);

            if (checkExistRegister(urlCheckCodeFileExist)) {
                status = 0;
            }

            codeText = $('.codeText').val();
            if (codeText.trim() == '') {
                $('#codeText-error').show();
                status = 0;
            }

            if ($('.select-search-employee').length) {
                var selectEmployee = $('.select-search-employee').val();
                if (selectEmployee == null) {
                    $('.search-employee-error').show();
                    status = 0;
                }
            }

            date_file_send = $('#datetimepicker-date_file_send').datepicker('getDate');;
            if (date_file_send == null) {
                $('#date_file_send-error').show();
                status = 0;
            }

            date_file = $('#datetimepicker-date_file').datepicker('getDate');;
            if (date_file == null) {
                $('#date_file-error').show();
                status = 0;
            }

            quote = CKEDITOR.instances["quote_text"].getData();
            if (quote.trim() == '') {
                $('#quote-error').show();
                status = 0;
            }

            checkGroupTeam = $('#groupTeam :selected').val();
            if (checkGroupTeam == 0) {
                $('#groupTeam-error').show();
                status = 0;
            }

            selectEmployee = $('.select-search-employee').val();
            if (selectEmployee == null) {
                $('.search-employee-error').show();
                status = 0;
            }
            
            if (status == 0) {
                return false;
            }
            return true;
        }

        function checkExistRegister(urlCheckCodeFileExist) {
            var isExist = false;
            var codeText = $(".codeText").val();
            $.ajax({
                type: "GET",
                url: urlCheckCodeFileExist,
                dataType: 'json',
                async: false,
                data: {
                    codeText: codeText,
                },
                success: function (data) {
                    $('.codeError').html('');
                    if (data.length > -1) {
                        isExist = false;
                    } else {
                        $('.codeError').append('<div class="error">'+ data.error +'</div>');
                        isExist = true;
                    }
                }
            });
            return isExist;
        }

        $(function() {
            $('.select-search-employee').selectSearchEmployee();
        });
    </script>
@endsection
