@extends('manage_time::layout.common_layout')

@section('title-common')
    {{ trans('manage_time::view.Late in early out register') }} 
@endsection

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View;
    use Rikkei\ManageTime\Model\ComeLateRegister;

    $isShowFormEdit = false;
    $disabled = '';
    if (isset($isAllowEdit) && $isAllowEdit) {
        $isShowFormEdit = true;
    } else {
        $disabled = 'disabled';
    }

    $statusUnapprove = ComeLateRegister::STATUS_UNAPPROVE;
    $statusApproved = ComeLateRegister::STATUS_APPROVED;
    $statusDisapprove = ComeLateRegister::STATUS_DISAPPROVE;
    $statusCancel = ComeLateRegister::STATUS_CANCEL;
    $urlApprove = route('manage_time::profile.comelate.approve');
    $urlDisapprove = route('manage_time::profile.comelate.disapprove');
    $urlSearchRelatedPerson = route('manage_time::profile.comelate.find-employee');
    $urlCheckRegisterExist = route('manage_time::profile.comelate.check-register-exist');
    $contentModalApprove = trans('manage_time::view.Do you want to approve the register of late in early out?');
    $countDaysApply = count($daysApply);

    $isMonday = false;
    $isTuesday = false;
    $isWebnesday = false;
    $isThursday = false;
    $isFriday = false;
    if(isset($daysApply) && count($daysApply))
    {
        foreach ($daysApply as $item) {
            if($item->day == 1)
            {
                $isMonday = true;
            }
            if($item->day == 2)
            {
                $isTuesday = true;
            }
            if($item->day == 3)
            {
                $isWebnesday = true;
            }
            if($item->day == 4)
            {
                $isThursday = true;
            }
            if($item->day == 5)
            {
                $isFriday = true;
            }
        }
    }
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
    <!-- Box mission list -->
    <div class="box box-primary" id="mission_register">
        <div class="box-header with-border">
            <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.View detail late in early out register') }}</h3>
        </div>
        <!-- /.box-header -->

        <div class="box-body no-padding">
            @if($isShowFormEdit)
                <form role="form" method="post" action="{{ route('manage_time::profile.comelate.update') }}" class="managetime-form-register" id="form-register-comelate" enctype="multipart/form-data" autocomplete="off">
                {!! csrf_field() !!}
            @endif
                <div class="row">
                    <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                        <div class="box-body">
                            <input type="hidden" name="register_id" id="register_id" value="{{ $registerRecord->register_id }}">
                            <input type="hidden" name="" id="employee_id" value="{{ $registerRecord->creator_id }}">
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <?php
                                        if ($registerRecord->status == $statusDisapprove) {
                                            $classStatus = 'callout-disapprove';
                                            $status = trans('manage_time::view.Disapprove');
                                        } elseif ($registerRecord->status == $statusUnapprove) {
                                            $classStatus = 'callout-unapprove';
                                            $status = trans('manage_time::view.Unapprove');
                                        } elseif ($registerRecord->status == $statusApproved) {
                                            $classStatus = 'callout-approved';
                                            $status = trans('manage_time::view.Approved');
                                        } else {
                                            $classStatus = 'callout-cancelled';
                                            $status = trans('manage_time::view.Cancelled');
                                        }
                                    ?>
                                    <div class="managetime-callout {{ $classStatus }}">
                                        <p class="text-center"><strong>{{ $status }}</strong></p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label managetime-label">{{ trans('manage_time::view.Registrant') }}</label>
                                    <div class="input-box">
                                        <input type="text" name="employee_name" class="form-control" value="{{ $registerRecord->creator_name }} ({{ $registerRecord->creator_email }})" disabled />
                                    </div>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label managetime-label">{{ trans('manage_time::view.Employee code') }}</label>
                                    <div class="input-box">
                                        <input type="text" name="employee_code" class="form-control" value="{{ $registerRecord->creator_code }}" disabled />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label managetime-label">{{ trans('manage_time::view.Position') }}</label>
                                <div class="input-box">
                                    <input type="text" name="role_name" class="form-control" value="{{ $registerRecord->role_name }}" disabled />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required managetime-label">{{ trans('manage_time::view.Approver') }} <em>*</em></label>
                                    <div class="input-box">
                                        <select id="approver" class="form-control select-search-employee-no-pagination" name="approver" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee-can-approve', ['route' => 'manage_time::manage-time.manage.leave_day.approve']) }}" {{ $disabled }}>
                                            <option value="{{ $registerRecord->approver_id }}" selected>{{ $registerRecord->approver_name . ' (' . preg_replace('/@.*/', '',$registerRecord->approver_email) . ')' }}</option>
                                        </select>
                                    </div>
                                    <label id="approver-error" class="managetime-error" for="approver">{{ trans('manage_time::view.This field is required') }}</label>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label managetime-label">{{ trans('manage_time::view.Related persons') }}</label>
                                    <div class="input-box">
                                        <select name="related_persons_list[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple {{ $disabled }}>
                                            @if(isset($relatedPersonsList) && count($relatedPersonsList))
                                                @foreach($relatedPersonsList as $item)
                                                    <option value="{{ $item->relater_id }}" selected>{{ $item->relater_name . ' (' . preg_replace('/@.*/', '',$item->relater_email) . ')' }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required managetime-label">{{ trans('manage_time::view.From date') }} <em>*</em></label>
                                    <div class='input-group date' id='datetimepicker-start-date'>
                                        <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-start">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type='text' class="form-control managetime-date" name="start_date" id="start_date" value="{{ Carbon::parse($registerRecord->date_start)->format('d-m-Y') }}" placeholder="dd-mm-yyyy" {{ $disabled }} />
                                    </div>
                                    <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
                                    <label id="register_exist_error" class="managetime-error" for="start_date">{{ trans('manage_time::message.Registration time has been identical') }}</label>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required managetime-label">{{ trans('manage_time::view.End date') }} <em>*</em></label>
                                    <div class='input-group date' id='datetimepicker-end-date'>
                                        <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-end">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type='text' class="form-control" name="end_date" id="end_date" value="{{ Carbon::parse($registerRecord->date_end)->format('d-m-Y') }}" placeholder="dd-mm-yyyy" {{ $disabled }} />
                                    </div>
                                    <div class='input-group date' id='hidden-end-date' style="display: none;">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type='text' class="form-control" name="" id="" placeholder="dd-mm-yyyy" readonly />
                                    </div>
                                    <label id="end_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.This field is required') }}</label>
                                    <label id="end_date_before_start_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.The end date at must be after start date') }}</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label managetime-label">{{ trans('manage_time::view.Late start shift') }}</label>
                                    <div class="input-box">
                                        <input type="text" id="late_start_shift" name="late_start_shift" class="form-control managetime-text-right manage-time" value="{{ $registerRecord->late_start_shift > 0 ? $registerRecord->late_start_shift : 0 }}" {{ $disabled }} />
                                    </div>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label managetime-label">{{ trans('manage_time::view.Early mid shift') }}</label>
                                    <div class="input-box">
                                        <input type="text" id="early_mid_shift" name="early_mid_shift" class="form-control managetime-text-right manage-time" value="{{ $registerRecord->early_mid_shift > 0 ? $registerRecord->early_mid_shift : 0 }}" {{ $disabled }} />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label managetime-label">{{ trans('manage_time::view.Late mid shift') }}</label>
                                    <div class="input-box">
                                        <input type="text" id="late_mid_shift" name="late_mid_shift" class="form-control managetime-text-right manage-time" value="{{ $registerRecord->late_mid_shift > 0 ? $registerRecord->late_mid_shift : 0 }}" {{ $disabled }} />
                                    </div>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label managetime-label">{{ trans('manage_time::view.Early end shift') }}</label>
                                    <div class="input-box">
                                        <input type="text" id="early_end_shift" name="early_end_shift" class="form-control managetime-text-right manage-time" value="{{ $registerRecord->early_end_shift > 0 ? $registerRecord->early_end_shift : 0 }}" {{ $disabled }} />
                                    </div>
                                </div>
                                <div class="col-sm-12 managetime-form-group">
                                    <label id="error-time" class="managetime-error" style=" display: none;">{{ trans('manage_time::view.You must enter a time for at least one of the following fields: late start shift field or early mid shift field or late mid shift field or early end shift field') }}</label>
                                </div>
                            </div>

                            <div class="row">
                                @if(isset($daysApply) && count($daysApply))
                                    <div class="col-sm-6 col-all-day managetime-form-group" hidden>
                                        <label id="select_all_day" class="managetime-label">
                                            <input type="checkbox" class="minimal" name="all_day" value="7" {{ $disabled }}>
                                            {{ trans('manage_time::view.Apply to all day') }}
                                        </label>

                                        <input type="hidden" id="all_day_hidden" name="all_day_hidden" value="">
                                    </div>

                                    @if($isMonday)
                                        <div class="col-sm-6 col-monday managetime-form-group" hidden>
                                            <label id="monday" class="managetime-label">
                                                <input type="checkbox" class="minimal" name="come_late_days[]" checked value="1" {{ $disabled }}>
                                                {{ trans('manage_time::view.Monday') }}
                                            </label>
                                        </div>
                                    @else
                                        <div class="col-sm-6 col-monday managetime-form-group" hidden>
                                            <label id="monday" class="managetime-label">
                                                <input type="checkbox" class="minimal" name="come_late_days[]" value="1" {{ $disabled }}>
                                                {{ trans('manage_time::view.Monday') }}
                                            </label>
                                        </div>
                                    @endif
                                    
                                    @if($isTuesday)
                                        <div class="col-sm-6 col-tuesday managetime-form-group" hidden>
                                            <label id="tuesday" class="managetime-label">
                                                <input type="checkbox" class="minimal" name="come_late_days[]" checked value="2" {{ $disabled }}>
                                                {{ trans('manage_time::view.Tuesday') }}
                                            </label>
                                        </div>
                                    @else
                                        <div class="col-sm-6 col-tuesday managetime-form-group" hidden>
                                            <label id="tuesday" class="managetime-label">
                                                <input type="checkbox" class="minimal" name="come_late_days[]" value="2" {{ $disabled }}>
                                                {{ trans('manage_time::view.Tuesday') }}
                                            </label>
                                        </div>
                                    @endif

                                    @if($isWebnesday)
                                        <div class="col-sm-6 col-wednesday managetime-form-group" hidden>
                                            <label id="wednesday" class="managetime-label">
                                                <input type="checkbox" class="minimal" name="come_late_days[]" checked value="3" {{ $disabled }}>
                                                {{ trans('manage_time::view.Wednesday') }}
                                            </label>
                                        </div>
                                    @else
                                        <div class="col-sm-6 col-wednesday managetime-form-group" hidden>
                                            <label id="wednesday" class="managetime-label">
                                                <input type="checkbox" class="minimal" name="come_late_days[]" value="3" {{ $disabled }}>
                                                {{ trans('manage_time::view.Wednesday') }}
                                            </label>
                                        </div>
                                    @endif

                                    @if($isThursday)
                                        <div class="col-sm-6 col-thursday managetime-form-group" hidden>
                                            <label id="thursday" class="managetime-label">
                                                <input type="checkbox" class="minimal" name="come_late_days[]" checked value="4" {{ $disabled }}>
                                                {{ trans('manage_time::view.Thursday') }}
                                            </label>
                                        </div>
                                    @else
                                        <div class="col-sm-6 col-thursday managetime-form-group" hidden>
                                            <label id="thursday" class="managetime-label">
                                                <input type="checkbox" class="minimal" name="come_late_days[]" value="4" {{ $disabled }}>
                                                {{ trans('manage_time::view.Thursday') }}
                                            </label>
                                        </div>
                                    @endif

                                    @if($isFriday)
                                        <div class="col-sm-6 col-friday managetime-form-group" hidden>
                                            <label id="friday" class="managetime-label">
                                                <input type="checkbox" class="minimal" name="come_late_days[]" checked value="5" {{ $disabled }}>
                                                {{ trans('manage_time::view.Friday') }}
                                            </label>
                                        </div>
                                    @else
                                        <div class="col-sm-6 col-friday managetime-form-group" hidden>
                                            <label id="friday" class="managetime-label">
                                                <input type="checkbox" class="minimal" name="come_late_days[]" value="5" {{ $disabled }}>
                                                {{ trans('manage_time::view.Friday') }}
                                            </label>
                                        </div>
                                    @endif
                                @else 
                                    <div class="col-sm-6 col-all-day managetime-form-group" hidden>
                                        <label id="select_all_day" class="managetime-label">
                                            <input type="checkbox" class="minimal" selected name="all_day" value="7" {{ $disabled }}>
                                            {{ trans('manage_time::view.Apply to all day') }}
                                        </label>

                                        <input type="hidden" id="all_day_hidden" name="all_day_hidden" value="">
                                    </div>

                                    <div class="col-sm-6 col-monday managetime-form-group" hidden>
                                        <label id="monday" class="managetime-label">
                                            <input type="checkbox" class="minimal" name="come_late_days[]" value="1" {{ $disabled }}>
                                            {{ trans('manage_time::view.Monday') }}
                                        </label>
                                    </div>

                                    <div class="col-sm-6 col-tuesday managetime-form-group" hidden>
                                        <label id="tuesday" class="managetime-label">
                                            <input type="checkbox" class="minimal" name="come_late_days[]" value="2" {{ $disabled }}>
                                            {{ trans('manage_time::view.Tuesday') }}
                                        </label>
                                    </div>

                                    <div class="col-sm-6 col-wednesday managetime-form-group" hidden>
                                        <label id="wednesday" class="managetime-label">
                                            <input type="checkbox" class="minimal" name="come_late_days[]" value="3" {{ $disabled }}>
                                            {{ trans('manage_time::view.Wednesday') }}
                                        </label>
                                    </div>

                                    <div class="col-sm-6 col-thursday managetime-form-group" hidden>
                                        <label id="thursday" class="managetime-label">
                                            <input type="checkbox" class="minimal" name="come_late_days[]" value="4" {{ $disabled }}>
                                            {{ trans('manage_time::view.Thursday') }}
                                        </label>
                                    </div>

                                    <div class="col-sm-6 col-friday managetime-form-group" hidden>
                                        <label id="friday" class="managetime-label">
                                            <input type="checkbox" class="minimal" name="come_late_days[]" value="5" {{ $disabled }}>
                                            {{ trans('manage_time::view.Friday') }}
                                        </label>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label class="control-label required managetime-label">{{ trans('manage_time::view.Register reason') }} <em>*</em></label>
                                <div class="input-box">
                                    <textarea id="reason" name="reason" class="form-control required managetime-textarea" {{ $disabled }}>{{ $registerRecord->reason }}</textarea>
                                </div>
                                <label id="reason-error" class="managetime-error" for="reason">{{ trans('manage_time::view.This field is required') }}</label>
                            </div>

                            @if(isset($isShowFormEdit) && $isShowFormEdit)
                            <div class="comelate-upload-file">
                                <input type="file" name="files" {{ $disabled }} data-fileuploader-files="{{ $appendedFiles }}">
                            </div>
                            @else
                                @if(isset($attachmentsList) && count($attachmentsList))
                                    @foreach($attachmentsList as $img)
                                    <a class="fancybox" href="{{ URL::asset($img->path) }}" rel="group" style="cursor:zoom-in"><img src="{{ URL::asset($img->path) }}" width="180" border="0" alt=""></a>
                                    @endforeach
                                @endif
                            @endif

                            @if(isset($commentsList) && count($commentsList))
                                <div class="form-group">
                                    <div class="box box-widget">
                                        <div class="box-header with-border">
                                          <h3 class="box-title">{{ trans('manage_time::view.Disapprove reason') }}</h3>
                                        </div>
                                        <!-- /.box-header -->
                                        <div class="box-body">
                                            <ul class="products-list product-list-in-box">
                                                @foreach($commentsList as $item)
                                                    <li class="item">
                                                        <div class="post">
                                                            <div class="user-block">
                                                                <img class="img-bordered-sm" src="{{ $item->avatar_url }}" alt="{{ $item->name }}">
                                                                <span class="username">{{ $item->name }}</span>
                                                                <span class="description">{{ Carbon::parse($item->created_at)->format('d-m-Y H:i:s') }}</span>
                                                            </div>
                                                            <!-- /.user-block -->
                                                            <p>
                                                                {!! View::nl2br($item->comment) !!}
                                                            </p>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <!-- /.box-body -->
                                     </div>
                                </div>
                            @endif
                        </div>
                        <!-- /.box-body -->
                        
                        <div class="box-footer">
                            @if(isset($isShowFormEdit) && $isShowFormEdit)
                                <button type="submit" class="btn btn-primary" id="submit" onclick="return checkFormComelateRegister();"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Update') }}</button>
                                <input type="hidden" id="check_submit" name="" value="0">
                                <input type="hidden" id="check_status" name="" value="{{ $registerRecord->status }}">

                                <!-- Allow edit modal -->
                                @include('manage_time::include.modal.modal_allow_edit')
                            @endif

                            @if(isset($isAllowApprove) && $isAllowApprove)
                                <button type="button" class="btn btn-success" id="button_approve" value="{{ $registerRecord->register_id }}" data-toggle="modal" data-target="#modal_approve"><i class="fa fa-check"></i> {{ trans('manage_time::view.Approve') }}</button>
                                <button type="button" class="btn btn-danger" id="button_disapprove" value="{{ $registerRecord->register_id }}" data-toggle="modal" data-target="#modal_disapprove"><i class="fa fa-minus-circle"></i> {{ trans('manage_time::view.Not approve') }}</button>

                                <!-- Approve modal -->
                                @include('manage_time::include.modal.modal_approve')

                                <!-- Disapprove modal -->
                                @include('manage_time::include.modal.modal_disapprove')
                            @endif
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
    <script src="{{ CoreUrl::asset('asset_managetime/js/comelate.register.edit.js') }}"></script>

    <script type="text/javascript">
        var urlApprove = '{{ $urlApprove }}';
        var urlDisapprove = '{{ $urlDisapprove }}';
        var urlSearchRelatedPerson = '{{ $urlSearchRelatedPerson }}';
        var urlCheckRegisterExist = '{{ $urlCheckRegisterExist }}';
        var startDateDefault = '{{ $registerRecord->date_start }}';
        var endDateDefault = '{{ $registerRecord->date_end }}';
        var countDaysApply = '{{ $countDaysApply }}';
        var notificationStatusApproved = '<?php echo trans("manage_time::message.The register of late in early out has been approved can not edit"); ?>';
        var notificationStatusCanceled = '<?php echo trans("manage_time::message.The register of late in early out has been canceled can not edit"); ?>';

        loadEditGetDaysApply();

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
        var text_upload_image = '<?php echo trans('manage_time::view.Upload files'); ?>';
        var textUpload = '{{ trans('manage_time::view.Upload proofs (image file of computer logs, images of meeting with clients...)') }}';
    </script>

    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.fileuploader.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/comelate.fileupload.js') }}"></script>
@endsection
