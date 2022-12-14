@extends('manage_time::layout.common_layout')

@section('title-common')
{{ trans('manage_time::view.Supplement register') }} 
@endsection

<?php

use Carbon\Carbon;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\Team\Model\Team;
use Rikkei\ManageTime\Model\SupplementReasons;

$annualHolidays = CoreConfigData::getAnnualHolidays(2);
$specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePreOfEmp);

$isShowFormEdit = false;
$disabled = '';
if (isset($isAllowEdit) && $isAllowEdit) {
    $isShowFormEdit = true;
} else {
    $disabled = 'disabled';
}

$statusUnapprove = SupplementRegister::STATUS_UNAPPROVE;
$statusApproved = SupplementRegister::STATUS_APPROVED;
$statusDisapprove = SupplementRegister::STATUS_DISAPPROVE;
$statusCancel = SupplementRegister::STATUS_CANCEL;
$urlApprove = route('manage_time::profile.supplement.approve');
$urlDisapprove = route('manage_time::profile.supplement.disapprove');
$urlSearchRelatedPerson = route('manage_time::profile.supplement.find-employee');
$urlCheckRegisterExist = route('manage_time::profile.supplement.check-register-exist');
$contentModalApprove = trans('manage_time::view.Do you want to approve the register of supplement?');
?>

@section('css-common')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/jquery.fileuploader.css') }}" />
<style>
    .tt p {
        display: inline-block;
        font-size: 16px;
        cursor: pointer
    }
</style>
@endsection

@section('sidebar-common')
@include('manage_time::include.sidebar_supplement')
@endsection

@section('content-common')
<div class="se-pre-con"></div>
<!-- Box edit register -->
<div class="box box-primary" id="mission_register">
    <div class="box-header with-border">
        <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.View detail supplement register') }}</h3>
    </div>
    <!-- /.box-header -->

    <div class="box-body no-padding">
        @if($isShowFormEdit)
        <form role="form" method="post" action="{{ route('manage_time::profile.supplement.update') }}" class="managetime-form-register supplement-register" id="form-edit-register" enctype="multipart/form-data" autocomplete="off">
            {!! csrf_field() !!}
            @endif
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                    <div class="box-body">
                        <input type="hidden" name="register_id" id="register_id" value="{{ $registerRecord->register_id }}">
                        <input type="hidden" name="employee_id" id="employee_id" value="{{ $registerRecord->creator_id }}">
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
                                    <select id="approver" class="form-control select-search-employee-no-pagination" name="approver" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee-can-approve', ['route' => 'manage_time::manage-time.manage.supplement.approve']) }}" {{ $disabled }}>
                                        <option value="{{ $registerRecord->approver_id }}" selected>{{ $registerRecord->approver_name . ' (' . preg_replace('/@.*/', '',$registerRecord->approver_email) . ')' }}</option>
                                    </select>
                                </div>
                                <label id="approver-error" class="managetime-error" for="approver">{{ trans('core::view.This field is required') }}</label>
                            </div>
                            <div class="col-sm-6 managetime-form-group">
                                <label class="control-label managetime-label">{{ trans('manage_time::view.Related persons need notified') }}</label>
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
                                <label class="control-label required managetime-label">{{ trans('manage_time::view.From date') }} <em>*</em> <span class="fa fa-question-circle" data-toggle="tooltip" title="{!! trans('manage_time::view.Start date tooltip supplement') !!}" data-html="true" ></span></label>
                                <div class='input-group date' id='datetimepicker-start-date'>
                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-start">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                    <input type='text' class="form-control managetime-date" name="start_date" id="start_date" data-date-format="DD-MM-YYYY HH:mm" value="" {{ $disabled }} />
                                </div>
                                <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('core::view.This field is required') }}</label>
                                <label id="register_exist_error" class="managetime-error" for="start_date">{{ trans('manage_time::message.Registration time has been identical') }}</label>
                                <label id="supplement_ot_one_day" class="managetime-error" for="end_date">{{ trans('manage_time::view.Register supplement OT must be in a day') }}</label>
                            </div>
                            <div class="col-sm-6 managetime-form-group">
                                <label class="control-label required managetime-label">{{ trans('manage_time::view.End date') }} <em>*</em> <span class="fa fa-question-circle" data-toggle="tooltip" title="{!! trans('manage_time::view.End date tooltip supplement') !!}" data-html="true" ></span></label>
                                <div class='input-group date' id='datetimepicker-end-date'>
                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-end">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                    <input type='text' class="form-control" name="end_date" id="end_date" data-date-format="DD-MM-YYYY HH:mm" value="" {{ $disabled }} />
                                </div>
                                <div class='input-group date' id='hidden-end-date' style="display: none;">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                    <input type='text' class="form-control managetime-date" name="" id="" readonly />
                                </div>
                                <label id="end_date-error" class="managetime-error" for="end_date">{{ trans('core::view.This field is required') }}</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 managetime-form-group">
                                <label class="control-label managetime-label">{{ trans('manage_time::view.Number of days supplement') }}</label>
                                <div class="input-box">
                                    <input type="text" name="number_days_off" id="number_days_off" class="form-control" value="{{ $registerRecord->number_days_supplement }}" readonly />
                                </div>
                                <label id="end_date_before_start_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.The number days supplement must be than 0') }}</label>
                            </div>
                            <div class="col-sm-6 managetime-form-group">
                                <label class="control-label">&nbsp;</label>
                                <div class="input-box">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="is_ot" id="is_ot" value="1" {{ $registerRecord->is_ot ? 'checked' : '' }} {{ $disabled }}> {{ trans('manage_time::view.Is OT') }}
                                        </label>    
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label required managetime-label">{{ trans('manage_time::view.Supplement reason') }} <em>*</em></label>
                            @if ($reasons)
                            <select class="form-control select-search col-md-6" id="reason_id" name="reason_id" {{ $disabled }}>
                                @foreach ($reasons as $reason)
                                <option value="{{ $reason->id }}"
                                    {{ $registerRecord->reason_id == $reason->id ? 'selected' : ''}}
                                    data-required="{{ $reason->is_image_required }}"
                                    data-other="{{ $reason->is_type_other }}">{{ $reason->name }}</option>
                                @endforeach
                            </select>
                            <div class="margin-bottom-15">&nbsp;</div>
                            @endif
                            <div class="input-box">
                                <textarea id="reason" name="reason" class="form-control required managetime-textarea {{ $reasons && $registerRecord->reason_id !== $reasonTypeOther->id ? 'hidden' : '' }}"
                                    onkeyup="checkInputReasonKeyup()" {{ $disabled }}
                                    placeholder="{{ trans('manage_time::view.Supplement reason') }}">{{ $registerRecord->reason }}</textarea>
                            </div>
                            <label id="reason-error" class="managetime-error" for="reason">{{ trans('core::view.This field is required') }}</label>
                        </div>

                        @if(isset($isShowFormEdit) && $isShowFormEdit)
                        <div class="managetime-upload-file">
                            <input type="file" id="image_upload" name="files" {{ $disabled }} data-fileuploader-files="{{ $appendedFiles }}">
                            <label id="image_upload-error" class="managetime-error" >{{ trans('core::view.This field is required') }}</label>
                        </div>
                        @else
                            @if(isset($attachmentsList) && count($attachmentsList))
                                @foreach($attachmentsList as $img)
                                <a class="fancybox" href="{{ URL::asset($img->path) }}" rel="group" style="cursor:zoom-in"><img src="{{ URL::asset($img->path) }}" width="180" border="0" alt=""></a>
                                @endforeach
                            @endif
                        @endif

                        @include('manage_time::supplement.include.supplement_together')

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
                        <button type="submit" class="btn btn-primary" id="submit" onclick="return checkFormSupplementRegister('{{ $urlCheckRegisterExist }}');"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Update') }}</button>
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
                    @if (isset($timekeeping))
                        <br>
                        <div class="tt">
                        <p>Th??ng tin ch???m c??ng tham kh???o</p>
                        </div>
                        @include('manage_time::supplement.include.detail_timekeeping')
                    @endif
                </div>
            </div>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<div class="box box-primary">
    <div class="box-body font-size-14">
        {!! trans('manage_time::view.Guide register supplement') !!}
    </div>
</div>
<!-- /. box -->
@endsection

@section('script-common')
<script>
    var teamCode = '{{ $teamCodePreOfEmp }}';
    var codeJp = '{{ Team::CODE_PREFIX_JP }}';
    var isEmpJp = teamCode === codeJp;
    var compensationDays = <?php echo json_encode($compensationDays); ?>;
    var empProjects = <?php echo json_encode($empProjects); ?>;
    /**
     * Store working time setting of employee
     */
    var timeSetting = <?php echo json_encode($timeSetting); ?>;
    var currentEmpId = {{ $registerRecord->creator_id }};
    var token = '{{ csrf_token() }}';
    var urlGetTimeSetting = '{{ route("manage_time::profile.leave.get-time-setting") }}';
    var typeOther = {{ SupplementReasons::TYPE_OTHER }};
    var typeImageRequired = {{ SupplementReasons::IS_IMAGE_REQUIRED }};
    var textUpload = '{{ trans('manage_time::view.Upload proofs (image file of computer logs, images of meeting with clients...)') }}';
    // Variable check is working in Japan
    var isWorkingJP = false;
    @if ($reasons)
        isWorkingJP = true;
    @endif
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/additional-methods.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
<script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_managetime/js/register.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_managetime/js/jquery.fileuploader.js') }}"></script>

<script type="text/javascript">
    const STATUS_APPROVED = '{{ $statusApproved }}';
    const STATUS_CANCEL = '{{ $statusCancel }}';
    var fileUploaderMessage = '<?php echo trans('manage_time::message.Upload proofs message'); ?>';
    var urlApprove = '{{ $urlApprove }}';
    var urlDisapprove = '{{ $urlDisapprove }}';
    var urlSearchRelatedPerson = '{{ $urlSearchRelatedPerson }}';
    var startDateDefault = '{{ $registerRecord->date_start }}';
    var endDateDefault = '{{ $registerRecord->date_end }}';
    var notificationStatusApproved = '<?php echo trans("manage_time::message.The register of supplement has been approved can not edit"); ?>';
    var notificationStatusCanceled = '<?php echo trans("manage_time::message.The register of supplement has been canceled can not edit"); ?>';
    var annualHolidays = '{{ implode(', ', $annualHolidays) }}';
    var arrAnnualHolidays = annualHolidays.split(', ');
    var specialHolidays = '{{ implode(', ', $specialHolidays) }}';
    var arrSpecialHolidays = specialHolidays.split(', ');
    $(function() {
        $('.select-search-employee').selectSearchEmployee();
        $('.select-search-employee-no-pagination').selectSearchEmployeeNoPagination();
        
        $(".tt").on('click', 'p', function(event) {
            event.preventDefault();
            $(".tk_detail table").toggleClass('hidden');
        });
    });
    var pageType = "{{ $pageType }}";
</script>
@endsection
