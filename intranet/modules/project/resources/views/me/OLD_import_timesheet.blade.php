@extends('layouts.default')

@section('title', trans('project::me.Update monthly time sheet'))

@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Project\View\ProjDbHelp;

$now = ProjDbHelp::getDateDefaultRewardFilter();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link href="{{ CoreUrl::asset('project/css/edit.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
@endsection

@section('content')

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
            {!! Form::open([
                'method' => 'post', 
                'route' => 
                'project::timesheet.eval.post_upload', 
                'files' => true, 
                'id' => 'time_point_form',
                'class' => 'form-horizontal'
            ]) !!}
            
                <div class="row">
                    <div class="col-md-12 radio-toggle-click-wrapper">
                        <div class="form-label-left row">
                            <div class="col-sm-2 control-label">
                                <input type="checkbox" class="input-radio-inline radio-toggle-click" 
                                    id="is_send_email" name="is_send_email" value="1" checked />
                                <label for="is_send_email"><strong>{{trans('project::view.Send email')}}?</strong></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="date" class="col-sm-1 control-label required">{{ trans('project::view.Date') }}<em>*</em></label>
                            <div class="col-sm-11">
                                <input name="date" class="form-control input-field date-picker" type="text" id="date" 
                                       value="{{ $now->format('Y-m') }}" placeholder="date"  />
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="subject" class="col-sm-1 control-label required">{{ trans('event::view.Subject') }}<em>*</em></label>
                            <div class="col-sm-11">
                                <input name="cf[hr.email_subject.timekeeping]" class="form-control input-field" type="text" id="subject" 
                                    value="{{ $subjectEmail }}" placeholder="subject" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group row">
                            <label class="col-md-1 control-label" for="excel_file">{{trans('project::view.File timekeeping')}} (csv)</label>
                            <div class="col-md-11">
                                <input class="form-control" type="file" name="excel_file">
                            </div>
                            <div class="col-md-offset-1 col-md-11">
                                <strong>{!!trans('core::view.view guide file')!!}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group row">
                            <label class="col-md-1 control-label" for="timekeeping_content">{{trans('project::view.Content')}}</label>
                            <div class="col-md-11">
                                <textarea class="form-control text-editor" name="cf[hr.email_content.timekeeping]" id="timekeeping_content">{{ $emailContentTimekeeping }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 align-center">
                        <button class="btn-add" type="submit"><i class="fa fa-upload"></i> {{trans('project::me.Upload')}} <span class="_uploading hidden"><i class="fa fa-spin fa-refresh"></i></span></button>
                    </div>
                </div>
            {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <h4>{{ trans('project::view.Format csv file') }}</h4>
                <ul>
                    <li>
                        Ngày: format ngày/tháng/năm - dd/mm/yyyy
                    </li>
                </ul>
                <img src="{{ URL::asset('event/images/template/chamcong.png') }}" class="img-responsive" />
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script type="text/javascript">
jQuery(document).ready(function ($) {
    selectSearchReload();
    var _uploading = false;
    $('#time_point_form').submit(function () {
        if (!$(this).valid()) {
            return false;
        }
        $(this).find('button[type="submit"]').prop('disabled', true);
        $('._uploading').removeClass('hidden'); 
        setTimeout(function () {
            _uploading = true;
        }, 2000);
    });
    window.onbeforeunload = function () {
      if (_uploading) {
          return true;
      }  
    };
    $('input.date-picker').datetimepicker({
        format: 'YYYY-MM'
    });
    RKfuncion.CKEditor.init(['timekeeping_content']);
    $('#time_point_form').validate({
        rules: {
            date: {
                required: true
            },
            'cf[hr.email_subject.timekeeping]': {
                required: true
            },
            'excel_file': {
                required: true
            }
        }
    });
    $('#time_point_form_update').validate({
        rules: {
            excel_file: {
                required: true
            }
        }
    });
});
</script>
@endsection
