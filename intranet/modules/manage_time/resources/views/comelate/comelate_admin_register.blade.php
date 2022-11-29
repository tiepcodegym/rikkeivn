@extends('layouts.default')

@section('title')
    {{ trans('manage_time::view.Late in early out register') }}
@endsection

<?php 
    use Rikkei\Core\View\CoreUrl;

    $urlSearchRelatedPerson = route('manage_time::profile.comelate.find-employee');
    $urlCheckRegisterExist = route('manage_time::profile.comelate.check-register-exist');
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/jquery.fileuploader.css') }}" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Box register -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.Register information of late in early out') }}</h3>
                </div>
                <!-- /.box-header -->

                <div class="box-body no-padding">
                    <div class="col-lg-10 col-lg-offset-1">
                        <form role="form" method="post" action="{{ route('manage_time::profile.comelate.save-admin-register') }}" class="managetime-form-register" id="form-register" enctype="multipart/form-data" autocomplete="off">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-sm-12 managetime-form-group">
                                                <label class="control-label required">{{ trans('manage_time::view.Registrant') }} <em>*</em></label>
                                                <div class="input-box">
                                                    <select id="employee_id" class="form-control select-search-employee" name="employee_id">
                                                    </select>
                                                </div>
                                                <label id="registrant-error" class="managetime-error" for="registrant">{{ trans('manage_time::view.This field is required') }}</label>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label required">{{ trans('manage_time::view.From date') }} <em>*</em></label>
                                                <div class='input-group date' id='datetimepicker-start-date'>
                                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-start">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                    <input type='text' class="form-control managetime-date" name="start_date" id="start_date" placeholder="dd-mm-yyyy" />
                                                </div>
                                                <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
                                                <label id="register_exist_error" class="managetime-error" for="start_date">{{ trans('manage_time::message.Registration time has been identical') }}</label>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label required">{{ trans('manage_time::view.End date') }} <em>*</em></label>
                                                <div class='input-group date' id='datetimepicker-end-date'>
                                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-end">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                    <input type='text' class="form-control" name="end_date" id="end_date" placeholder="dd-mm-yyyy" />
                                                </div>
                                                <div class='input-group date' id='hidden-end-date' style="display: none;">
                                                    <span class="input-group-addon">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                    <input type='text' class="form-control" name="" id="" placeholder="dd-mm-yyyy" readonly />
                                                </div>
                                                <label id="end_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.This field is required') }}</label>
                                                <label id="end_date_before_start_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.The on date at must be after out date') }}</label>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label">{{ trans('manage_time::view.Late start shift') }}</label>
                                                <div class="input-box">
                                                    <input type="text" id="late_start_shift" name="late_start_shift" class="form-control managetime-text-right manage-time" value="0" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label">{{ trans('manage_time::view.Early mid shift') }}</label>
                                                <div class="input-box">
                                                    <input type="text" id="early_mid_shift" name="early_mid_shift" class="form-control managetime-text-right manage-time" value="0" />
                                                </div>
                                            </div>
                                        </div>
                                    
                                        <div class="row">
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label">{{ trans('manage_time::view.Late mid shift') }}</label>
                                                <div class="input-box">
                                                    <input type="text" id="late_mid_shift" name="late_mid_shift" class="form-control managetime-text-right manage-time" value="0" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label">{{ trans('manage_time::view.Early end shift') }}</label>
                                                <div class="input-box">
                                                    <input type="text" id="early_end_shift" name="early_end_shift" class="form-control managetime-text-right manage-time" value="0" />
                                                </div>
                                            </div>
                                            <div class="col-sm-12 managetime-form-group">
                                                <label id="error-time" class="managetime-error" style=" display: none;">{{ trans('manage_time::view.You must enter a time for at least one of the following fields: late start shift field or early mid shift field or late mid shift field or early end shift field') }}</label>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 col-all-day managetime-form-group" hidden>
                                                <label id="select_all_day">
                                                    <input type="checkbox" class="minimal" name="all_day" value="7">
                                                    {{ trans('manage_time::view.Apply to all day') }}
                                                </label>

                                                <input type="hidden" id="all_day_hidden" name="all_day_hidden" value="">
                                            </div>

                                            <div class="col-sm-6 col-monday managetime-form-group" hidden>
                                                <label id="monday">
                                                    <input type="checkbox" class="minimal" name="come_late_days[]" value="1">
                                                    {{ trans('manage_time::view.Monday') }}
                                                </label>
                                            </div>

                                            <div class="col-sm-6 col-tuesday managetime-form-group" hidden>
                                                <label id="tuesday">
                                                    <input type="checkbox" class="minimal" name="come_late_days[]" value="2">
                                                    {{ trans('manage_time::view.Tuesday') }}
                                                </label>
                                            </div>

                                            <div class="col-sm-6 col-wednesday managetime-form-group" hidden>
                                                <label id="wednesday">
                                                    <input type="checkbox" class="minimal" name="come_late_days[]" value="3">
                                                    {{ trans('manage_time::view.Wednesday') }}
                                                </label>
                                            </div>

                                            <div class="col-sm-6 col-thursday managetime-form-group" hidden>
                                                <label id="thursday">
                                                    <input type="checkbox" class="minimal" name="come_late_days[]" value="4">
                                                    {{ trans('manage_time::view.Thursday') }}
                                                </label>
                                            </div>

                                            <div class="col-sm-6 col-friday managetime-form-group" hidden>
                                                <label id="friday">
                                                    <input type="checkbox" class="minimal" name="come_late_days[]" value="5">
                                                    {{ trans('manage_time::view.Friday') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label required">{{ trans('manage_time::view.Register reason') }} <em>*</em></label>
                                            <div class="input-box">
                                                <textarea id="reason" name="reason" class="form-control required managetime-textarea"></textarea>
                                            </div>
                                            <label id="reason-error" class="managetime-error" for="reason" style="display: none;">{{ trans('manage_time::view.This field is required') }}</label>
                                        </div>
                                    </div>
                                    <!-- /.box-body -->
                                    <div class="comelate-upload-file">
                                        <input type="file" name="files" accept="image/*">
                                    </div>

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary" id="submit" onclick="return checkFormComelateRegister();"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Register') }}</button>
                                        <input type="hidden" id="check_submit" name="" value="0">
                                        <input type="hidden" name="admin" value="1">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/comelate.register.js') }}"></script>

    <script type="text/javascript">
        var text_upload_image = '<?php echo trans('manage_time::view.Upload files'); ?>';
        var urlSearchRelatedPerson = '{{ $urlSearchRelatedPerson }}';
        var urlCheckRegisterExist = '{{ $urlCheckRegisterExist }}';
        var urlAjaxGetApprover = '{{ route('manage_time::profile.supplement.ajax-get-approver') }}';
        var textUpload = '{{ trans('manage_time::view.Upload proofs (image file of computer logs, images of meeting with clients...)') }}';

        $('.select-search-employee').select2({
            ajax: {
                url: urlSearchRelatedPerson,
                dataType: "JSON",
                data: function (params) {
                    return {
                        q: $.trim(params.term)
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
        $('#employee_id').on('change', function(e) {
            $('#registrant-error').hide();
        });

        var rules = {
            'late_start_shift': {
                digits: true,
                max:120
            },
            'early_mid_shift': {
                digits: true,
                max:120
            },
            'late_mid_shift': {
                digits: true,
                max:120
            },
            'early_end_shift': {
                digits: true,
                max:120
            }
        }

        var messages = {
            'late_start_shift': {
                digits: '<?php echo trans('manage_time::view.Please enter only digits'); ?>',
                max: '<?php echo trans('manage_time::view.Please enter a time between 1 minute and 120 minutes'); ?>'
            },
            'early_mid_shift': {
                digits: '<?php echo trans('manage_time::view.Please enter only digits'); ?>',
                max: '<?php echo trans('manage_time::view.Please enter a time between 1 minute and 120 minutes'); ?>'
            },
            'late_mid_shift': {
                digits: '<?php echo trans('manage_time::view.Please enter only digits'); ?>',
                max: '<?php echo trans('manage_time::view.Please enter a time between 1 minute and 120 minutes'); ?>'
            },
            'early_end_shift': {
                digits: '<?php echo trans('manage_time::view.Please enter only digits'); ?>',
                max: '<?php echo trans('manage_time::view.Please enter a time between 1 minute and 120 minutes'); ?>'
            }
        }

        $('#form-register-comelate').validate({
            rules: rules,
            messages: messages
        });
    </script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.fileuploader.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/comelate.fileupload.js') }}"></script>
@endsection
