@extends('manage_time::layout.common_layout')

@section('title-common')
    {{ trans('manage_time::view.Late in early out register') }}
@endsection

<?php 
    use Rikkei\Core\View\CoreUrl;

    $urlSearchRelatedPerson = route('manage_time::profile.comelate.find-employee');
    $urlCheckRegisterExist = route('manage_time::profile.comelate.check-register-exist');
?>

@section('css-common')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/jquery.fileuploader.css') }}" />
@endsection

@section('sidebar-common')
    @include('manage_time::include.sidebar_comelate')
@endsection

@section('content-common')
    <div class="se-pre-con"></div>
    <!-- Box add register -->
    <div class="box box-primary" id="mission_register">
        <div class="box-header with-border">
            <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.Late in early out register') }}</h3>
        </div>
        <!-- /.box-header -->

        <div class="box-body no-padding">
            <form role="form" method="post" action="{{ route('manage_time::profile.comelate.save') }}" class="managetime-form-register" id="form-register-comelate" enctype="multipart/form-data" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                        <div class="box-body">
                            <input type="hidden" name="employee_id" id="employee_id" value="{{ $registrantInformation->creator_id }}">
                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label">{{ trans('manage_time::view.Registrant') }}</label>
                                    <div class="input-box">
                                        <input type="text" name="employee_name" class="form-control" value="{{ $registrantInformation->employee_name }} ({{ $registrantInformation->employee_email }})" disabled />
                                    </div>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label">{{ trans('manage_time::view.Employee code') }}</label>
                                    <div class="input-box">
                                        <input type="text" name="employee_code" class="form-control" value="{{ $registrantInformation->employee_code }}" disabled />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">{{ trans('manage_time::view.Position') }}</label>
                                <div class="input-box">
                                    <input type="text" name="role_name" class="form-control" value="{{ $registrantInformation->role_name }}" disabled />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.Approver') }} <em>*</em></label>
                                    <div class="input-box">
                                        <select id="approver" class="form-control select-search-employee-no-pagination" name="approver" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee-can-approve', ['route' => 'manage_time::manage-time.manage.comelate.approve']) }}">
                                            @if ($suggestApprover)
                                                <option value="{{ $suggestApprover->approver_id }}" selected>{{ $suggestApprover->approver_name . ' (' . preg_replace('/@.*/', '',$suggestApprover->approver_email) . ')' }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <label id="approver-error" class="managetime-error" for="approver">{{ trans('manage_time::view.This field is required') }}</label>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label">{{ trans('manage_time::view.Related persons need notified') }}</label>
                                    <div class="input-box">
                                        <select id="related_persons" name="related_persons_list[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple>
                                        </select>
                                    </div>
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
                            <div class="comelate-upload-file">
                                <input type="file" name="files" accept="image/*">
                            </div>
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary" id="submit" onclick="return checkFormComelateRegister();"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Register') }}</button>
                            <input type="hidden" id="check_submit" name="" value="0">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /. box -->
@endsection

@section('script-common')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/comelate.register.js') }}"></script>

    <script type="text/javascript">
        var text_upload_image = '<?php echo trans('manage_time::view.Upload files'); ?>';
        var textUpload = '{{ trans('manage_time::view.Upload proofs (image file of computer logs, images of meeting with clients...)') }}';
        var urlSearchRelatedPerson = '{{ $urlSearchRelatedPerson }}';
        var urlCheckRegisterExist = '{{ $urlCheckRegisterExist }}';

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

        $(function() {
            $('.select-search-employee').selectSearchEmployee();
            $('.select-search-employee-no-pagination').selectSearchEmployeeNoPagination();
        });
    </script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.fileuploader.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/comelate.fileupload.js') }}"></script>
@endsection
