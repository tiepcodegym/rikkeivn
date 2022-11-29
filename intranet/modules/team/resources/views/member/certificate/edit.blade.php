<?php
use Illuminate\Support\Facades;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Certificate;

$tabTitleSub = trans('team::profile.cer_title_sub');
$employeeId = Auth::user()->employee_id;
?>
@extends('team::member.profile_layout')
@section('profile_css')
    <link href="{{ asset('magazine/css/style.css') }}" rel="stylesheet" type="text/css" >
    <style>
        .imgPreviewWrap .actions {
            left: auto;
            width: 10%;
        }
        .preview_imgPreviewWrap {
            display: inline-block;
        }
    </style>
@stop
@section('content_profile')
    <div class="row">
        @if(isset($employeeItemMulti->status))
            <div class="row position-relative">
                <div class="col-sm-3">
                    <div class="callout bg-aqua">
                        <p class="text-center text-uppercase"><strong>{{ $status_certificate[$employeeItemMulti->status] }}</strong></p>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::view.Certificate') }}<em>*</em></label>
            <div class="input-box col-md-8">
                    <select id="select-cer" name="cer[certificate_id]" class="form-control select-search has-search"
                            {!!$disabledInput!!}>
                        <option value="">&nbsp;</option>
                        @foreach ($certificates as $item)
                            <option <?php
                                    if ($employeeItemMulti->certificate_id != null && $item['id'] == $employeeItemMulti->certificate_id): ?> selected<?php
                            endif; ?> value="{{ $item['id'] }}" type="{{$item['type']}}">{{ $item['name'] }}</option>
                        @endforeach
                    </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row form-group-select2">
            <label class="col-md-4 control-label">{{ trans('team::view.Level') }}</label>
            <div class="input-box col-md-8 fg-valid-custom">
                <div class="hidden" data-cer-level="1">
                    <select name="cer[level]" class="form-control select-search has-search"
                        {!!$disabledInput!!}>
                        <option value="">&nbsp;</option>
                        @foreach ($languageLevels as $label)
                            <option value="{{ $label }}"<?php
                                if ($employeeItemMulti->level != null && $label == $employeeItemMulti->level): ?> selected<?php
                                endif; ?>>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="hidden" data-cer-level="2">
                    <input type="text" name="cer[level_other]" class="form-control"
                        placeholder="{{ trans('team::view.Level') }}" value="{{ $employeeItemMulti->level }}"
                        {!!$disabledInput!!} />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Effect from') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="cer[start_at]"
                       placeholder="yyyy-mm-dd" data-flag-type="date"
                       value="{{ $employeeItemMulti->start_at }}"
                       {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans('team::profile.Effect to') }}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="cer[end_at]"
                       placeholder="yyyy-mm-dd" data-flag-type="date"
                       value="{{ $employeeItemMulti->end_at }}"
                       {!!$disabledInput!!} />
                <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
            </div>
        </div>
    </div>
</div>
<div class="hidden" data-cer-level="1">
    <div class="row">
        <div class="col-md-6 form-horizontal">
            <div class="form-group row">
                <label class="col-md-4 control-label">{{ trans('team::profile.Listen') }}</label>
                <div class="input-box col-md-8">
                    <input type="text" class="form-control" name="cer[p_listen]"
                           placeholder="{!!trans('team::profile.9 point')!!}"
                           value="{{ $employeeItemMulti->p_listen }}"
                           {!!$disabledInput!!} />
                </div>
            </div>
        </div>
        <div class="col-md-6 form-horizontal">
            <div class="form-group row">
                <label class="col-md-4 control-label">{{ trans('team::profile.Speak') }}</label>
                <div class="input-box col-md-8">
                    <input type="text" class="form-control" name="cer[p_speak]"
                           placeholder="{!!trans('team::profile.9 point')!!}"
                           value="{{ $employeeItemMulti->p_speak }}"
                           {!!$disabledInput!!} />
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 form-horizontal">
            <div class="form-group row">
                <label class="col-md-4 control-label">{{ trans('team::profile.Read') }}</label>
                <div class="input-box col-md-8">
                    <input type="text" class="form-control" name="cer[p_read]"
                           placeholder="{!!trans('team::profile.9 point')!!}"
                           value="{{ $employeeItemMulti->p_read }}"
                           {!!$disabledInput!!} />
                </div>
            </div>
        </div>
        <div class="col-md-6 form-horizontal">
            <div class="form-group row">
                <label class="col-md-4 control-label">{{ trans('team::profile.Write') }}</label>
                <div class="input-box col-md-8">
                    <input type="text" class="form-control" name="cer[p_write]"
                           placeholder="{!!trans('team::profile.9 point')!!}"
                           value="{{ $employeeItemMulti->p_write }}"
                           {!!$disabledInput!!} />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Certificate place') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" name="cer[place]" class="form-control"
                    placeholder="{{ trans('team::profile.Certificate place') }}" value="{{ $employeeItemMulti->place }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{!! trans('team::profile.Result') !!}</label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="cer[p_sum]"
                    placeholder="{!!trans('team::profile.Result')!!}"
                    value="{{ $employeeItemMulti->p_sum }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Certificate Image|pdf') }}<em>*</em></label>
            <div class="col-md-8">
                <div id="image_preview">
                    <input class="form-control" id="certificate-image" name="certificate_image[]"
                        type="file" multiple="multiple" accept="image/*,application/pdf">
                    @if(!empty($certificatesImage))
                        <input id="certificate-image-hiden" type="hidden" value="{{ count($certificatesImage) }}">
                    @endif
                    @if(!empty($certificatesImage))
                        @foreach($certificatesImage as $item)
                            <div class="imgPreviewWrap" data-href="{{ URL::asset('storage' . $item['image']) }}"
                                data-id="{{$item['id']}}" data-name="{{$item['image']}}">
                                <a href="{{ URL::asset('storage' . $item['image']) }}" target="blank"
                                    data-id="{{$item['id']}}" data-name="{{$item['image']}}"
                                >
                                @if (!empty($item['image']) && substr($item['image'], -3) == 'pdf')
                                    <img src="{{asset('team/images/pdf_error.png') }}" alt="No image" data-pdf-thumbnail-file="{{URL::asset('storage' . $item['image'])}}">
                                @else
                                    <img src="{{URL::asset('storage' . $item['image'])}}" alt="No image">
                                @endif
                                </a>
                                <div class="actions">
                                    <button type="button" class="action-delete" title="{{ trans('magazine::view.Delete image') }}"><span>x</span></button>
                                </div>
                            </div>
                            {{-- <a href="{{ URL::asset('storage' . $item['image']) }}" target="blank"
                            class="imgPreviewWrap"
                            data-id="{{$item['id']}}" data-name="{{$item['image']}}"
                            >
                                <img src="{{URL::asset('storage' . $item['image'])}}" alt="No image">
                                <div class="actions">
                                    <button type="button" class="action-delete" title="{{ trans('magazine::view.Delete image') }}"><span>x</span></button>
                                </div>
                            </a> --}}
                        @endforeach
                    @endif
                    <div class="imgPreviewWrap hidden" data-id="" id="preview_item">
                        <a href="">
                            <img src="" alt="No image">
                        </a>
                        <div class="actions">
                            <button type="button" class="action-delete" title="{{ trans('magazine::view.Delete image') }}"><span>x</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.Approver') }}</label>
            <div class="col-md-8">
                <select id="select-approver-cer" name="cer[approver]" class="form-control select-search has-search"
                        {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($listApprover as $key => $item)
                        <option <?php
                                if ($employeeItemMulti->approver != null && $key == $employeeItemMulti->approver): ?> selected<?php
                        endif; ?> value="{{ $key }}">{{ $item }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::profile.Note') }}</label>
            <div class="input-box col-md-10">
                <textarea type="text" class="form-control" name="cer[note]"
                          placeholder="{!!trans('team::profile.Note link')!!}" rows="6"
                    {!!$disabledInput!!}>{{ $employeeItemMulti->note }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div id="certificateModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="control-label required">{{ trans('team::view.Refuse to approve allowances') }}</h3>
            </div>
            <div class="modal-body">
                <label class="control-label required">{{ trans('team::profile.Input Reason') }}<em>*</em></label>
                <textarea class="form-control comment" name="description" rows="5" id="comment"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="cancel-button" >{!!trans('team::profile.Confirm')!!}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{!!trans('team::view.Close')!!}</button>
            </div>
        </div>

    </div>
    <!-- Modal -->
    <div id="certificateModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <label class="control-label required">{{ trans('team::profile.Input Reason') }}<em>*</em></label>
                    <textarea class="form-control comment" name="description" rows="5" id="comment"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="cancel-button" >{!!trans('team::profile.Confirm')!!}</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{!!trans('team::view.Close')!!}</button>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('more_btn_submit')
    @if(isset($employeeItemMulti->status) && ($employeeItemMulti->status == Certificate::STATUS_PLAN || $employeeItemMulti->status == Certificate::STATUS_CANCEL))
        <button type="button" data-id="{{$employeeItemMulti->status}}" id="request-approve" class="btn btn-primary" style="display: none">
            {!!trans('team::profile.Send request')!!}
            <i class="fa fa-spin fa-refresh loading-submit hidden"></i>
        </button>
    @endif
    @if(isset($employeeItemMulti->status) && $employeeItemMulti->status == Certificate::STATUS_PROCESSING && $checkPermission && $employeeId == $employeeItemMulti->approver)
        <button type="button" class="btn btn-primary" id="confirm-button" >{!!trans('team::view.Approve')!!}</button>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#certificateModal">{!!trans('team::view.Reject')!!}</button>
        <input type="hidden" id="status" name="cer[status]" value="">
    @endif
@endsection
@section('profile_js_custom')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.js"></script>
    <script src="{{ CoreUrl::asset('team/js/pdf/pdfThumbnails.js') }}"></script>

    <script>
        var isCer = true;
        var certificateStatus = '{{ $employeeItemMulti->status }}';
        var imgPdf = "{{asset('team/images/pdf.webp') }}";
        $(function() {
            $('#image_preview').on('click', 'a', function(e) {
                e.preventDefault();
                var strHref = $(this).attr('href');
                reloadUrl(strHref);
            })
            function reloadUrl(url) {
                if (url.slice(-3) == 'pdf' || url.indexOf('data:application/pdf') >= 0) {
                    window.open(url, '_blank');
                } else {
                    if (url.search('http') >= 0) {
                        window.open(url, '_blank');
                    } else {
                        var image = new Image();
                        image.src = url;
                        var w = window.open("");
                        w.document.write(image.outerHTML, '<style>img{width:100%}</style>');
                    }
                }
            }
        })

        $('#request-approve').click(function () {
            var formData = new FormData();
            formData.append('_token', $("#form-employee-info").find('input[name="_token"]').val());
            formData.append('approver', $("#select-approver-cer").val());
            formData.append('status', '{{$employeeItemMulti->status}}');
            formData.append('request', true);
            $.ajax({
                type: "POST",
                processData: false,
                contentType: false,
                url: '{{route('team::member.profile.save.employee.status', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $employeeItemTypeId])}}',
                data: formData,
                success: function( response ) {
                    if (response.status == 1) {
                        RKExternal.notify(response.message, 'success');
                        document.location.reload();
                    }
                    if (response.status == 0) {
                        RKExternal.notify(response.message, 'warning');
                    }
                }
            });
        });
        $("#select-approver-cer").change(function () {
            var formData = new FormData();
            formData.append('_token', $("#form-employee-info").find('input[name="_token"]').val());
            formData.append('approver', $("#select-approver-cer").val());
            formData.append('status', '{{$employeeItemMulti->status}}');
            formData.append('request', true);
            $.ajax({
                type: "POST",
                processData: false,
                contentType: false,
                url: '{{route('team::member.profile.change.employee.approver', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $employeeItemTypeId])}}',
                data: formData,
                success: function( response ) {
                    if (response.status == 1) {
                        $('#request-approve').after(response.button);
                        $('#request-approve').css('display', 'none');
                    }
                    if (response.status == 0) {
                        $('#request-approve').css('display', 'inline-block');
                        $('#confirm-button').remove();
                    }
                }
            });
        })
        $(document).on('click', '#confirm-button', function () {
            var formData = new FormData();
            formData.append('_token', $("#form-employee-info").find('input[name="_token"]').val());
            formData.append('status', '{{$employeeItemMulti->status}}');
            formData.append('approver', $("#select-approver-cer").val());
            formData.append('request', true);
            $.ajax({
                type: "POST",
                processData: false,
                contentType: false,
                url: '{{route('team::member.profile.save.employee.status', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $employeeItemTypeId])}}',
                data: formData,
                success: function( response ) {
                    if (response.status == 1) {
                        RKExternal.notify(response.message, 'success');
                        document.location.reload();
                    }
                }
            });
        });
        $('#comment').focus(function () {
            $('#certificateModal label.error').remove();
        })
        $('#cancel-button').click(function () {
            var formData = new FormData();
            formData.append('_token', $("#form-employee-info").find('input[name="_token"]').val());
            formData.append('cancel', true);
            formData.append('comment', $('#certificateModal').find('#comment').val());
            $.ajax({
                type: "POST",
                processData: false,
                contentType: false,
                url: '{{route('team::member.profile.save.employee.status', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $employeeItemTypeId])}}',
                data: formData,
                success: function( response ) {
                    if (response.status == 1) {
                        RKExternal.notify(response.message, 'success');
                        document.location.reload();
                    }
                    else {
                        $('#certificateModal textarea').after('<label class="error">' + response.message + '</label>')
                    }
                }
            });
        })
        $(document).ready(function() {
            var MAX_FILE_SIZE = 10;
            var index = -1;
            var select_index = -1;
            var tempFiles = [];
            var MAX_SIZE = "{{ substr(ini_get('post_max_size'), 0, -1) }}";
            var msg = new Array();
            msg["fileMaxSize"] = "{{ trans('magazine::message.Server allow file max size', ['max' => ini_get('post_max_size')]) }}";
            msg["validFileSize"] = "{{ trans('magazine::message.The file may not be greater than max kilobytes', ['max' => 5120]) }}";
            msg["errorOccurred"] = "{{ trans('magazine::message.Error occurred') }}";
            var modal_error = $('#modal-warning-notification');
            $("#certificate-image").change(function (e) {
                var el_this = $(this);
                var formData = new FormData();
                formData.append('_token', el_this.find('input[name="_token"]').val());
                var files = el_this[0].files;
                var size = 0;
                for (var i = 0; i < files.length; i++) {
                    formData.append('images[]', files[i]);
                    if (files[i].size > MAX_FILE_SIZE * 1024 * 1024) {
                        showModalError(msg['validFileSize'] + ' ('+ files[i].name +' - ' + Math.floor(files[i].size / 1024) + 'KB)');
                        el_this.val('');
                        return;
                    }
                    size += files[i].size;
                    index++;
                    tempFiles.push(files[i]);
                    var reader = new FileReader();
                    (function (index) {
                        reader.readAsDataURL(files[i]);
                        reader.onload = function (e) {
                            var imgItem = $('#preview_item').clone().removeAttr('id').removeClass('hidden').attr('data-index', index);
                            var img = imgItem.find('img');
                            var nameImage = el_this.val(); 
                            if (nameImage.slice(-3) == 'pdf') {
                                img.attr('src', imgPdf);
                                img.attr('data-pdf-thumbnail-file', imgPdf);
                            } else {
                                img.attr('src', e.target.result);
                            }
                            img.src = img.prop('src');
                            var aHref = imgItem.find('a')
                            aHref.attr('href', e.target.result);
                            if (typeof EXIF != "undefined") {
                                EXIF.getData(img, function () {
                                    var orient = EXIF.getTag(img, 'Orientation');
                                    if (typeof orient != "undefined") {
                                        img.addClass('rotate-' + orient);
                                    }
                                    appendImgItem(imgItem, index);
                                });
                            } else {
                                appendImgItem(imgItem, index);
                            }
                        };
                    })(index);
                }

                if (size > MAX_SIZE * 1024 * 1024) {
                    showModalError(msg['fileMaxSize']);
                    $('button').prop('disabled', false);
                    el_this.val('');
                    return;
                }
            });

            //append image by order index
            function appendImgItem(imgItem, index) {
                var hasItem = false;
                for (var i = index; i >= 0; i--) {
                    var idxItem = $('.imgPreviewWrap[data-index="'+ i +'"]');
                    if (idxItem.length > 0) {
                        idxItem.after(imgItem);
                        hasItem = true;
                        break;
                    }
                }
                if (!hasItem) {
                    $('#image_preview').append(imgItem);
                }
            }
            //show message error
            function showModalError(message) {
                if (typeof message == "undefined") {
                    message = msg['errorOccurred'];
                }
                modal_error.find('.text-default').html(message);
                modal_error.modal('show');
            }

            $('#certificate-image').click(function () {
                $(this).val('');
            });
            $('#image_preview').on("click", ".action-delete", function () {
                var wrapper = $(this).closest(".imgPreviewWrap");
                var item_index = wrapper.attr('data-index');
                tempFiles[item_index] = null;
                if (select_index == item_index) {
                    select_index = -1;
                }
                $('#certificate-image').val('');
                wrapper.remove();
            });
            $("#form-employee-info").on("click", ".action-delete", function () {
                var wrapper = $(this).closest(".imgPreviewWrap");
                var item_index = wrapper.attr('data-index');
                tempFiles[item_index] = null;
                if (select_index == item_index) {
                    select_index = -1;
                }
                wrapper.remove();
            });
            $("#form-employee-info").submit(function () {
                var size = 0;
                $('#image_preview .imgPreviewWrap').each(function (e_index) {
                    if (typeof $(this).attr('data-index') != "undefined") {
                        var item_index = $(this).attr('data-index');
                        var file = tempFiles[item_index];
                        if (file) {
                            if (typeof file == 'object') {
                                if (file.size > MAX_FILE_SIZE * 1024 * 1024) {
                                    // check each file size < 10MB
                                    showModalError(msg['validFileSize'] + ' ('+ file.name +' - ' + Math.floor(file.size / 1024) + 'KB)');
                                    $('button').prop('disabled', false);
                                    return false;
                                }
                                size += file.size;
                            }
                        }
                    }
                });
                //check total file size < server allow post file size
                if (size > MAX_SIZE * 1024 * 1024) {
                    showModalError(msg['fileMaxSize']);
                    $('button').prop('disabled', false);
                    return false;
                }

            });
        });
        // If approved, disable button edit
        if(certificateStatus == 2) {
            $('.btn-edit-profile').prop('disabled', true);
        }
    </script>
@stop
<?php /*
@extends('layouts.profile_layout')
<?php

use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Certificate;
use Rikkei\Team\Model\EmployeeCertificate;
use Rikkei\Team\View\Permission;

$employeePermission = Permission::getInstance()->isAllow('team::team.member.edit');
$type = Certificate::labelAllType();
$certificatTbl = Certificate::getTableName();
$employCertiTbl = EmployeeCertificate::getTableName();

$disabled = '';
if (! $employeePermission) {
    $disabled = 'disabled';
}
?>

@section('title-profile')
{{ trans('team::view.Profile of :employeeName', ['employeeName' => Form::getData('employee.name')]) }}
@endsection

@section('css-profile')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css" />
@endsection

@section('content')

<?php
$urlSubmit = route('team::member.profile.cetificate.save', ['employeeId' => $employeeId]);
?>
    <form action="{{ $urlSubmit }}" method="post" id="form-employee-cetificate"
          enctype="multipart/form-data" autocomplete="off">
        {!! csrf_field() !!}
        @if (Form::getData('employee.id'))
        <input type="hidden" name="employee_id" value="{{ Form::getData('employee.id') }}" />
        @endif

        @if ($isEdit)
            <input type="hidden" name="isEdit" value="1" />
        @endif
        <input type="hidden" name="certificates[id]" value="{{ Form::getData('certificates.id') }}" />

        <!-- left menu -->
        <div class="col-lg-2 col-md-3">
            @include('team::member.left_menu',['active'=>'cetificate'])
        </div>
        <!-- /. End left menu -->

        <!-- Edit form -->
        <div class="col-lg-10 col-md-9 tab-content" style="padding: 0 50px;">
            <div class="box box-info tab-pane fade in active employee-skill-modal" id="education">
                <div class="box-header with-border">
                    <i class="fa fa-certificate"></i>
                    <h2 class="box-title">{{ trans('team::profile.Cetificate Info') }}</h2>
                </div>
                <div class="box-body">
                    <div class="form-horizontal form-label-left">
                        <div class="form-group">
                            <label class="col-md-2 control-label required">{{ trans('team::profile.Cetificate type') }}<em>*</em></label>
                            <div class="input-box col-md-4">
                                <select name="{{ $certificatTbl }}[type]" class="form-control select-search input-skill-modal"
                                    id="{{ $certificatTbl }}-type"
                                    data-tbl="{{ $certificatTbl }}" data-col="type"
                                    {{ $disabled }} >
                                    @foreach ($type as $key => $name)
                                        <option value="{{ $key }}"<?php
                                            if (Form::getData("$certificatTbl.type") !== null && $key === (int)Form::getData("$certificatTbl.type")): ?> selected<?php
                                            endif; ?>>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label required" arial-required="true">{{ trans('team::profile.Cetificate name') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <input type="text"
                                    class="form-control input-skill-modal" placeholder="{{ trans('team::profile.Cetificate name') }}"
                                    value="<?php echo Form::getData("$certificatTbl.name")?>"
                                    name="{{ $certificatTbl }}[name][{{ Certificate::TYPE_LANGUAGE }}]"
                                    id="language-name"
                                    {{ $disabled }}
                                    data-tbl="language" data-col="name" data-autocomplete="true"/>

                                <input type="text" style="display: none;"
                                    class="form-control input-skill-modal" placeholder="{{ trans('team::profile.Cetificate name') }}"
                                    value="<?php echo Form::getData("$certificatTbl.name")?>"
                                    name="{{ $certificatTbl }}[name][{{ Certificate::TYPE_CETIFICATE }}]"
                                    id="cetificate-name"
                                    {{ $disabled }}
                                    data-tbl="cetificate" data-col="name" data-autocomplete="true"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label required" arial-required="true">{{ trans('team::view.Level') }}</label>
                            <div class="input-box col-md-4">
                                <select name="{{ $employCertiTbl }}[level]" class="form-control select-search input-skill-modal"
                                    id="{{ $employCertiTbl }}-level"
                                    data-tbl="{{ $employCertiTbl }}" data-col="level"
                                    {{ $disabled }} >
                                    @foreach (View::toOptionLanguageLevel() as $option)
                                        <option value="{{ $option['value'] }}"<?php
                                            if (Form::getData("$employCertiTbl.level") !== null && (int)$option['value'] === (int)Form::getData("$employCertiTbl.level")): ?> selected<?php
                                            endif; ?>>{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="{{$employCertiTbl}}-start_at">{{ trans('team::view.Start at') }}</label>
                            <div class="input-box col-md-3 input-group-table">
                                <input type="text" id="{{$employCertiTbl}}-start_at"
                                    class="form-control date-picker input-skill-modal" placeholder="yyyy-mm-dd"
                                    {{ $disabled }}
                                    value="<?php echo View::getDate(Form::getData("$employCertiTbl.start_at"))?>" name="{{ $employCertiTbl }}[start_at]" id="{{$employCertiTbl}}-start_at"
                                    data-tbl="{{$employCertiTbl}}" data-col="start_at" />
                                <div class="input-group-addon">
                                   <i class="fa fa-calendar"></i>
                                </div>
                            </div>

                            <label class="col-md-1 control-label" for="{{$employCertiTbl}}-end_at">{{ trans('team::view.End at') }}</label>
                            <div class="input-box col-md-3 input-group-table">
                                <input type="text" id="{{$employCertiTbl}}-end_at"
                                    class="form-control date-picker input-skill-modal" placeholder="yyyy-mm-dd"
                                    {{ $disabled }}
                                    value="<?php echo View::getDate(Form::getData("$employCertiTbl.end_at"))?>" name="{{ $employCertiTbl }}[end_at]" id="{{$employCertiTbl}}-end_at"
                                    data-tbl="{{$employCertiTbl}}" data-col="end_at" />
                                <div class="input-group-addon">
                                   <i class="fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label" arial-required="true">{{ trans('team::profile.Cetificate place') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text"
                                    class="form-control input-skill-modal" placeholder="{{ trans('team::profile.Cetificate place') }}"
                                    value="<?php echo Form::getData("$employCertiTbl.place")?>"
                                    name="{{ $employCertiTbl }}[place]"
                                    id="{{ $employCertiTbl }}-place"
                                    {{ $disabled }}
                                    data-tbl="{{ $employCertiTbl }}" data-col="place" />
                            </div>
                        </div>

                        <div id="language_group" hidden="">
                            <div class="form-group">
                                <label class="col-md-2 control-label" arial-required="true">{{ trans('team::profile.Listen') }}</label>
                                <div class="input-box col-md-3">
                                    <input type="text"
                                           class="form-control input-skill-modal"
                                           value="<?php echo Form::getData("$employCertiTbl.listen") ?>"
                                           name="{{ $employCertiTbl }}[listen]"
                                           id="{{ $employCertiTbl }}-listen"
                                           {{ $disabled }}
                                           data-tbl="{{ $employCertiTbl }}" data-col="listen" />
                                </div>

                                <label class="col-md-1 control-label" arial-required="true">{{ trans('team::profile.Speak') }}</label>
                                <div class="input-box col-md-3">
                                    <input type="text"
                                           class="form-control input-skill-modal"
                                           value="<?php echo Form::getData("$employCertiTbl.speak") ?>"
                                           name="{{ $employCertiTbl }}[speak]"
                                           id="{{ $employCertiTbl }}-speak"
                                           {{ $disabled }}
                                           data-tbl="{{ $employCertiTbl }}" data-col="speak" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label" arial-required="true">{{ trans('team::profile.Read') }}</label>
                                <div class="input-box col-md-3">
                                    <input type="text"
                                           class="form-control input-skill-modal"
                                           value="<?php echo Form::getData("$employCertiTbl.read") ?>"
                                           name="{{ $employCertiTbl }}[read]"
                                           id="{{ $employCertiTbl }}-read"
                                           {{ $disabled }}
                                           data-tbl="{{ $employCertiTbl }}" data-col="read" />
                                </div>

                                <label class="col-md-1 control-label" arial-required="true">{{ trans('team::profile.Write') }}</label>
                                <div class="input-box col-md-3">
                                    <input type="text"
                                           class="form-control input-skill-modal"
                                           value="<?php echo Form::getData("$employCertiTbl.write") ?>"
                                           name="{{ $employCertiTbl }}[write]"
                                           id="{{ $employCertiTbl }}-write"
                                           {{ $disabled }}
                                           data-tbl="{{ $employCertiTbl }}" data-col="write" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label" arial-required="true">{{ trans('team::profile.Sum') }}</label>
                                <div class="input-box col-md-3">
                                    <input type="number"
                                           class="form-control input-skill-modal"
                                           value="<?php echo Form::getData("$employCertiTbl.sum") ?>"
                                           name="{{ $employCertiTbl }}[sum]"
                                           id="{{ $employCertiTbl }}-sum"
                                           {{ $disabled }}
                                           data-tbl="{{ $employCertiTbl }}" data-col="sum" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label">{{ trans('team::profile.Note') }}</label>
                            <div class="input-box col-md-9">
                                <textarea class="form-control {{$employCertiTbl}}-note input-skill-modal" placeholder="{{ trans('team::profile.Note') }}"
                                    name="{{$employCertiTbl}}[note]"
                                    id="{{$employCertiTbl}}-note"
                                    rows="6"
                                    {{ $disabled }}
                                    data-tbl="{{$employCertiTbl}}" data-col="note"><?php echo Form::getData("$employCertiTbl.note")?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end edit memeber right col -->
        @if($employeePermission)
            @include('team::include.action-box', ['edit' => $isEdit])
        @endif
    </form>
<?php
//remove flash session
Form::forget();
?>
@endsection

@section('script-profile')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="{{ URL::asset('common/js/methods.validate.js') }}"></script>
<script>
    jQuery(document).ready(function($) {

        selectTypeCetificate();
        $('#certificates-type').on('change', function(e) {
            e.preventDefault();
            selectTypeCetificate();
        });


        var messages = {
            'certificates[type]': {
                required: '<?php echo trans('core::view.This field is required'); ?>',
            },
            'certificates[name][2]': {
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'certificates[name][1]': {
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'employee_certificates[place]': {
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'employee_certificates[listen]': {
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 50]) ; ?>',
            },
            'employee_certificates[speak]': {
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 50]) ; ?>',
            },
            'employee_certificates[read]': {
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 50]) ; ?>',
            },
            'employee_certificates[write]': {
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 50]) ; ?>',
            },
            'employee_certificates[note]': {
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'employee_certificates[sum]': {
                    number: '<?php echo trans('core::view.Please enter a valid number') ; ?>',
            },
        };
        var rules = {
                'certificates[type]': {
                    required: true,
                },
                'certificates[name][1]': {
                    certificatesRequired: true,
                    rangelength:[0, 255],
                },
                'certificates[name][2]': {
                    certificatesRequired: true,
                    rangelength:[0, 255],
                },
                'employee_certificates[place]': {
                    rangelength: [0,255]
                },
                'employee_certificates[listen]': {
                    rangelength: [0,50]
                },
                'employee_certificates[speak]': {
                    rangelength: [0,50]
                },
                'employee_certificates[read]': {
                    rangelength: [0,50]
                },
                'employee_certificates[write]': {
                    rangelength: [0,50]
                },
                'employee_certificates[note]': {
                    rangelength: [0,255]
                },
                'employee_certificates[sum]': {
                    number: true,
                },
        };

        var autoComplete = {};
        var urlLoadAutoComplete = '{{ URL::route('core::ajax.skills.autocomplete') }}';

        $().employeeSkillAction({
            'autoComplete' : autoComplete,
            'urlLoadAutoComplete': urlLoadAutoComplete,
        });

        $('#form-employee-cetificate').validate({
                rules: rules,
                messages: messages,
                lang: 'vi',
        });

        jQuery.validator.addMethod("certificatesRequired", function(value, element) {
            var type = $('#certificates-type').val();
            if(type && $(element).attr('name') == 'certificates[name]['+type+']') {
                return (value).length > 0;
            }
        }, '<?php echo trans('core::view.This field is required'); ?>');

        //Date picker
        var optionDatePicker = {
            autoclose: true,
            format: 'yyyy-mm-dd',
            weekStart: 1,
            todayHighlight: true
        };
        $('.date-picker').datepicker(optionDatePicker);
        $('.select-search').select2();


        function selectTypeCetificate() {
            var type = $('#certificates-type').val();

            // change table name $to
            var changeTblAutocomplete = function($element, $to) {
                if( (tbl = $element.attr('data-tbl')) != undefined) {
                    if(tbl == $to){
                        return;
                    }
                    $element.attr('data-tbl', $to);
                }
                $element.change();
            }
            if(type == '{{ Certificate::TYPE_LANGUAGE }}') {
                $('#language_group').show()
                $('#cetificate-name').hide();
                $('#language-name').show();
            } else {
                 $('#language_group').hide()
                 $('#language-name').hide();
                 $('#cetificate-name').show();
            }
        }
    });
</script>
@endsection
*/ ?>

