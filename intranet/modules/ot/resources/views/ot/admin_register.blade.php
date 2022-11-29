@extends('layouts.default')

@section('title')
    {{ trans('ot::view.Register overtime') }}
@endsection

@section('css')
    <?php
        use Carbon\Carbon;
        use Rikkei\Team\Model\Team;
        use Rikkei\Core\View\CoreUrl;
        use Rikkei\Core\Model\CoreConfigData;
        use Rikkei\Ot\View\OtPermission;
        use Rikkei\Ot\View\OtView;
        use Rikkei\Ot\Model\OtRegister;
        
        $isCompanyPermission = OtPermission::isScopeManageOfCompany();
        $isTeamPermission = OtPermission::isScopeManageOfTeam();
        $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($userCurrent);
        $annualHolidays = json_encode(CoreConfigData::getAnnualHolidays(2));
        $specialHolidays = json_encode(CoreConfigData::getSpecialHolidays(2));

        $isEditable = true;
        $pageType = 'create';
        $notProject = with(new OtRegister())->getNotProject();
        $startDateDefault = Carbon::createFromFormat('Y-m-d H:i:s', $timeRegisterDefault['start_date'])->format('d-m-Y H:i');
        $endDateDefault = Carbon::createFromFormat('Y-m-d H:i:s', $timeRegisterDefault['end_date'])->format('d-m-Y H:i');
    ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_ot/css/register_ot.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_ot/css/list_ot.css') }}" />
    <style type="text/css">
        .managetime-error {
            color: red;
            font-size: 14px;
            cursor: pointer;
            display: inline-block;
            max-width: 100%;
            margin-bottom: 5px;
            word-wrap: break-word;
            display: none;
        }
        .ot-error {
            display: none;
            color: red;
        }
        .set-time-break-item label {
            font-weight: normal !important;
            padding-top: 8px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Box register -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title managetime-box-title">{{ trans('ot::view.Register information of OT') }}</h3>
                </div>
                <!-- /.box-header -->

                <div class="box-body no-padding">
                    <div class="col-lg-10 col-lg-offset-1">
                        <form role="form" method="post" action="{{ route('ot::ot.save-admin-register') }}" enctype="multipart/form-data" autocomplete="off">
                            {!! csrf_field() !!}
                            <input type="hidden" name="time_breaks" id="time_breaks">
                            <div class="row">
                                <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-6 ot-form-group">
                                                <label class="control-label">{{ trans('manage_time::view.Registrant') }} <em>*</em></label>
                                                <div class="input-box">
                                                    <select id="employee_id" class="form-control select-search-employee" name="employee_id" data-remote-url="{{ URL::route('ot::ot.ajax-search-employee') }}">
                                                    </select>
                                                </div>
                                                <label id="registrant-error" class="managetime-error" for="registrant">{{ trans('core::view.This field is required') }}</label>
                                            </div>
                                            <div class="col-md-6 ot-form-group">
                                                <div>
                                                    <label class="control-label">{{ trans('ot::view.OT Project') }} <em class="input-required">*</em></label>
                                                    <select style="width: 100%;" id="project_list" name="project_list" class="project_list form-control ot-select-2" {{ $isEditable ? '' : 'disabled' }}>
                                                        <option value="">&nbsp</option>
                                                        <option value="{{ OtRegister::KEY_NOTPROJECT_OT }}">{{ data_get($notProject, OtRegister::KEY_NOTPROJECT_OT )}}</option>
                                                    </select>
                                                    <label id="project-error" class="managetime-error" for="registrant">{{ trans('core::view.This field is required') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 ot-form-group">
                                                <label class="control-label">{{ trans('ot::view.OT from') }} <em class="input-required">*</em></label>
                                                <div class='input-group date' id='datetimepicker_start'>
                                                    <input type='text' class="form-control" name="time_start" data-date-format="DD-MM-YYYY HH:mm" id="time_start" data-inputmask="'alias': 'yyyy-mm-dd'" data-mask="" value="{!! $startDateDefault !!}" />
                                                    <span class="input-group-addon">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                </div>
                                                <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('core::view.This field is required') }}</label>
                                            </div>
                                            <div class="col-md-6 ot-form-group">
                                                <label class="control-label">{{ trans('ot::view.OT to') }}<em class="input-required">*</em></label>
                                                <div class='input-group date' id='datetimepicker_end'>
                                                    <input type='text' class="form-control" name="time_end" data-date-format="DD-MM-YYYY HH:mm" id="time_end" value="{!! $endDateDefault !!}" />
                                                    <span class="input-group-addon">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                </div>
                                                <label id="end_date-error" class="managetime-error" for="end_date">{{ trans('core::view.This field is required') }}</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 ot-form-group">
                                                <div class="input-box" id="form_set_time_break" style="display: none;">
                                                    <a class="btn btn-success btn-set-time-break" style="margin-bottom: 10px;">{{ trans('ot::view.Set shift break time') }}</a>
                                                    <input type="text" class="form-control" name="total_time_break" id="total_time_break" value="0.00" readonly>
                                                    <input type="hidden" name="" id="check_change_date" value="0">
                                                    <input type="hidden" name="" id="check_set_break_time" value="0">
                                                </div>
                                            </div>
                                            <div class="col-md-6 ot-form-group hidden">
                                                <label class="control-label">{{ trans('ot::view.Register Date') }}</label>
                                                <div class='input-group date' id='datetimepicker_register'>
                                                    <input type='text' class="form-control" name="time_register"  data-date-format="DD-MM-YYYY" id="time_register"  disabled=""
                                                           value="{{ Carbon::now()->format('d-m-Y') }}" />
                                                    <span class="input-group-addon">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 ot-form-group">
                                                <label class="control-label">{{ trans('ot::view.OT reason') }}<em class="input-required">*</em></label>
                                                <div class="input-box reason">
                                                    <textarea class="form-control" rows="4" name="reason" id="reason" style="min-height: 100px; max-width: 100%; min-width: 100%;"></textarea>
                                                </div>
                                                <label id="reason-error" class="managetime-error" for="reason">{{ trans('core::view.This field is required') }}</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 ot-form-group">
                                                <input type="checkbox" class="minimal" checked name="is_paid" value="1">
                                                <label class="control-label">{{ trans('ot::view.OT paid?') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary" onclick="return checkSubmitRegister();"><i class="fa fa-floppy-o"></i> {{ trans('ot::view.Register') }}</button>
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

    <div id="has_set_time_break" style="display: none;"></div>
    <div id="duplicate_set_time_break_item" style="display: none;">
        <div class="set-time-break-item row">
            <div class="col-md-4 ot-form-group">
                <label class="control-label time-break-date">
                    <span class="time-break-date-val"></span>
                    <span class="time-break-date-day"></span>
                </label>
            </div>
            <div class="col-md-8 ot-form-group">
                <input type="text" class="form-control time-break-value" value="0.00">
                <label class="ot-error max_time_break-error">{{ trans('ot::view.Max break time is 14h') }}</label>
            </div>
        </div>
    </div>
    <!-- Modal set time break-->
    <div class="modal fade" id="modal_set_time_break" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h3 class="box-title">{{ trans('ot::view.Set shift break time') }}</h3>
                </div>
                <style type="text/css">
                    .set-time-break-item label {
                        font-weight: normal;
                    }
                </style>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 ot-form-group">
                            <label class="control-label">{{ trans('ot::view.Date') }}</label>
                        </div>
                        <div class="col-md-8 ot-form-group">
                            <label class="control-label">{{ trans('ot::view.Shift break time (h)') }}</label>
                        </div>
                    </div>
                    <div id="box_set_time_break">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-cancel pull-left" data-dismiss="modal">{{ trans('ot::view.Close') }}</button>
                    @if ($isEditable)
                        <button type="button" class="btn btn-primary btn-confirm pull-right" onclick="saveSetTimeBreak();">{{ trans('ot::view.Save') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var teamCode = '{{ $teamCodePrefix }}';
        var codeJp = '{{ Team::CODE_PREFIX_JP }}';
        var isEmpJp = teamCode === codeJp;
        var urlSearchRelatedPerson = '{{ route('manage_time::profile.supplement.find-employee') }}';
        var urlAjaxChangeRegistrant = '{{ route('ot::ot.ajax-change-registrant') }}';
        var urlAjaxChangeProject = "{{ route('ot::ot.ajax-change-project') }}";
        var annualHolidayList = {!! $annualHolidays !!};
        var specialHolidayList = {!! $specialHolidays !!};
        var mesSameDay = "{{ trans('ot::message.Register time OT same day') }}";
        var mesStartLessEnd = "{{ trans('ot::message.Time start less than time end.') }}";
        var startDateDefault = '{!! $startDateDefault !!}';
        var endDateDefault = '{!! $endDateDefault !!}';

        var token = '{{ csrf_token() }}';
        var urlGetProject = '{{ route('ot::ot.get-project-ot') }}';
        var urlGetTimeSetting = '{{ route("manage_time::profile.leave.get-time-setting") }}';
        var compensationDays = <?php echo json_encode($compensationDays); ?>;
        var timeSetting = <?php echo empty($timeSetting) ? null : json_encode($timeSetting); ?>;
        var projectAllowedOT18Key = <?php echo empty($projectAllowedOT18Key) ? [] : json_encode($projectAllowedOT18Key); ?>;
        var idCurrent = '{{ $userCurrent->id }}';
        var keyNotProject = '{{ OtRegister::KEY_NOTPROJECT_OT }}';
        var notProject = <?php echo json_encode($notProject); ?>;
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/additional-methods.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-range/3.0.3/moment-range.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.6/jquery.number.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.shorten.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_ot/js/otregister.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_ot/js/admin_register.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            selectSearchReload();
            $('.select-search-employee').selectSearchEmployee();
        });
    </script>
@endsection
