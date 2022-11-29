@extends('layouts.default')

@section('title', trans('event::view.title ts to fines'))

@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Project\View\ProjDbHelp;

$now = ProjDbHelp::getDateDefaultRewardFilter();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link href="{{ CoreUrl::asset('project/css/edit.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
@endsection

@section('content')

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <p>{!!trans('event::view.note head ts to fines')!!}</p>
                <p>{!!trans('event::view.Email receive')!!}: {{$userCurrent->email}}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form method="POST" action="{!!route('event::send.email.employees.ts.to.fines')!!}"
                    accept-charset="UTF-8" id="time_point_form" class="form-horizontal"
                    enctype="multipart/form-data" novalidate="novalidate" autocomplete="off">
                    {!!csrf_field()!!}
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="date" class="col-sm-1 control-label required">{{ trans('project::view.Date') }}<em>*</em></label>
                                <div class="col-sm-11">
                                    <input name="date" class="form-control input-field date-picker" type="text" id="date" 
                                        value="{{ $now->format('Y-m') }}" placeholder="YYYY-MM"  />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <label class="col-md-1 control-label" for="excel_file">{{trans('project::view.File timekeeping')}} (csv, xls, xlsx)</label>
                                <div class="col-md-11">
                                    <input class="form-control" type="file" name="excel_file">
                                </div>
                                <div class="col-md-offset-1 col-md-11">
                                    <strong>{!!trans('core::view.view guide file')!!}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 align-center">
                            <button class="btn-add" type="submit">{!!trans('event::view.Upload')!!}&nbsp; <span class="_uploading hidden"><i class="fa fa-spin fa-refresh"></i></span></button>
                        </div>
                    </div>
                </form>
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
                        {{ trans('event::view.format day-month-year') }}
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
        }, 1000);
    });
    window.onbeforeunload = function () {
      if (_uploading) {
          return true;
      }  
    };
    $('input.date-picker').datetimepicker({
        format: 'YYYY-MM'
    });
    $('#time_point_form').validate({
        rules: {
            date: {
                required: true
            },
            excel_file: {
                required: true
            }
        }
    });
});
</script>
@endsection
