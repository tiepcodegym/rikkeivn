<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View as CoreView;
use Rikkei\ManageTime\View\ManageTimeConst as MTConst;
use Carbon\Carbon;

$nextMonth = Carbon::now()->addMonthNoOverflow()->format("m-Y");
$currMonth = Carbon::now()->startOfMonth()->toDateString();
$rangeTime = isset($defaultTimes['range_time']) ? $defaultTimes['range_time'] : [];
$listStatuses = MTConst::listWorkingTimeStatuses();
//$expiredFromMonth = $item && $item->from_month <= $currMonth;
$expiredFromMonth = false;
//$expiredToMonth = $item && $item->to_month <= $currMonth;
$expiredToMonth = false;
$permiss['edit'] = $hasOtherTime['status'] ? false : $permiss['edit'];
$disabled = !$permiss['edit'] ? 'disabled' : '';
$textApproved = trans('manage_time::view.Approved');
?>

@extends('layouts.default')

@section('title', trans('manage_time::view.Register working time'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/working-time.css') }}">
<style>
    .locked-tag .select2-selection__choice__remove{
        display: none!important;
    }
</style>
@stop

@section('content')

<div class="content-sidebar">
    <div class="content-col">
        <!-- Box mission list -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.Register change working time') }}</h3>
            </div>
            
            <div class="box-body">
                
                @if ($item)
                <div class="row">
                    <div class="col-sm-4 col-md-3">
                    {!! $item->renderStatusHtml($listStatuses) !!}
                    </div>
                    <div class="col-sm-8 col-md-9 text-right">
                        <span class="note-unapproved">{{ trans('manage_time::view.unapproved_value') }}</span>
                    </div>
                </div>
                @endif
                
                <form role="form" method="post" id="working_time_form" autocomplete="off"
                      action="{{ route('manage_time::wktime.post_register') }}">
                    {!! csrf_field() !!}

                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>{{ trans('manage_time::view.Registrant') }}</label>
                            <div class="input-box">
                                <input type="text" class="form-control" value="{{ $employee->name }} ({{ $employee->email }})" disabled/>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 form-group">
                            <label>{{ trans('manage_time::view.Employee code') }}</label>
                            <div class="input-box">
                                <input type="text" class="form-control" value="{{ $employee->employee_code }}" disabled />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>{{ trans('manage_time::view.Position') }}</label>
                            <div class="input-box">
                                <?php
                                $newestTeam = ($item && $item->team) ? $item->team : $employee->newestTeam(true);
                                $roleName = $newestTeam->role_name;
                                if (!$roleName) {
                                    $role = $newestTeam->getRoleByEmpId($employee->id);
                                    if ($role) {
                                        $roleName = $role->role;
                                    }
                                }
                                ?>
                                <input type="text" class="form-control" value="{{ ($roleName ?  $roleName . ' - ' : '') . $newestTeam->name }}" disabled />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label class="required">{{ trans('manage_time::view.Approver') }} <em>*</em></label>
                            <div class="input-box">
                                <?php
                                $approver = old('approver_id') ? CoreView::getOldEmployees(old('approver_id')) : ($item ? $item->approver : null);
                                ?>
                                <select class="form-control select-search select-tooltip" name="approver_id" {{ $expiredFromMonth ? 'disabled' : $disabled }}
                                        data-remote-url="{{ route('manage_time::wktime.search_approver') }}">
                                    <option value="">&nbsp;</option>
                                    @if ($approver)
                                    <option value="{{ $approver->id }}" selected>{{ CoreView::getNickName($approver->email) }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 form-group">
                            <label>{{ trans('manage_time::view.Related persons need notified') }}</label>
                            <div class="input-box select2-locked">
                                <?php
                                $relateds = old('related_ids') ? CoreView::getOldEmployees(old('related_ids'), false) : ($item ? $item->getRelated() : null);
                                ?>
                                <select name="related_ids[]" class="form-control select-search select-tooltip" multiple
                                        data-remote-url="{{ route('team::employee.list.search.ajax') }}" {{ $expiredFromMonth ? 'disabled' : $disabled }}>
                                    @if (!$item && $relator)
                                    <option value="{{ $relator->id }}" selected locked="1">{{ $relator->getNickName() }}</option>
                                    @endif
                                    @if ($relateds && !$relateds->isEmpty())
                                        @foreach ($relateds as $emp)
                                        <option value="{{ $emp->id }}" selected {!! $relator && $relator->id == $emp->id ? 'locked="1"' : '' !!}>{{ CoreView::getNickName($emp->email) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label>{{ trans('manage_time::view.Register working time') }}</label>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label class="required">{{ trans('manage_time::view.From month') }} <em>*</em></label>
                            <div class="input-group input-group-month">
                                <?php
                                $draftFromMonth = $draftItem ? $draftItem->getFromMonth() : null;
                                $fromMonth = $item ? $item->getFromMonth() : null;
                                $isChangeFromMonth = $draftFromMonth !== $fromMonth;
                                ?>
                                <input type="text" value="{{ old('from_month') ? old('from_month') : ($draftFromMonth ? $draftFromMonth : $nextMonth) }}"
                                       class="form-control {{ $isChangeFromMonth ? 'unapproved' : '' }}" data-format="MM-YYYY" name="from_month" id="from_month" {{ $expiredFromMonth ? 'disabled' : $disabled }}
                                       @if ($item && $isChangeFromMonth)
                                       data-toggle="tooltip" title="{{ $textApproved . ': '. $fromMonth }}"
                                       @endif/>
                                <span class="input-group-addon">
                                    <span class="fa fa-calendar"></span>
                                </span>
                            </div>
                        </div>

                        <div class="col-sm-6 form-group">
                            <label class="required">{{ trans('manage_time::view.To month') }} <em>*</em></label>
                            <div class="input-group input-group-month">
                                <?php
                                $draftToMonth = $draftItem ? $draftItem->getToMonth() : null;
                                $toMonth = $item ? $item->getToMonth() : null;
                                $isChangeToMonth = $draftToMonth != $toMonth;
                                ?>
                                <input type="text" value="{{ old('to_month') ? old('to_month') : ($draftToMonth ? $draftToMonth : $nextMonth) }}"
                                       class="form-control {{ $isChangeToMonth ? 'unapproved' : '' }}" data-format="MM-YYYY" name="to_month" id="to_month"
                                       {{ $expiredToMonth ? 'disabled' : $disabled }}
                                       @if ($item && $isChangeToMonth)
                                       data-toggle="tooltip" title="{{ $textApproved . ': '. $toMonth }}"
                                       @endif/>
                                <span class="input-group-addon">
                                    <span class="fa fa-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label class="required">{{ trans('manage_time::view.working_start_time1') }} <em>*</em> <i class="fa fa-question-circle"></i></label>
                                    <div class="input-group input-group-time">
                                        <?php
                                        $isChangeStartTime1 = $item && $item->start_time1 != $draftItem->start_time1;
                                        ?>
                                        <input type="text" value="{{ old('start_time1') ? old('start_time1') : ($draftItem ? $draftItem->start_time1 : $defaultTimes['start_time1']) }}"
                                               data-format="HH:mm" class="form-control {{ $isChangeStartTime1 ? 'unapproved' : '' }}"
                                               name="start_time1" {{ $expiredFromMonth ? 'disabled' : $disabled }} data-range="{{ $rangeTime ? json_encode($rangeTime['rstart1']) : null }}"
                                               @if ($item && $isChangeStartTime1)
                                               data-toggle="tooltip" title="{{ $textApproved . ': '. $item->start_time1 }}"
                                               @endif/>
                                        <span class="input-group-addon">
                                            <span class="fa fa-calendar"></span>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-sm-6 form-group">
                                    <label class="required">{{ trans('manage_time::view.working_end_time1') }} <em>*</em> <i class="fa fa-question-circle"></i></label>
                                    <div class="input-group input-group-time">
                                        <?php
                                        $isChangeEndTime1 = $item && $item->end_time1 != $draftItem->end_time1;
                                        ?>
                                        <input type="text" value="{{ old('end_time1') ? old('end_time1') : ($draftItem ? $draftItem->end_time1 : $defaultTimes['end_time1']) }}"
                                               class="form-control {{ $isChangeEndTime1 ? 'unapproved' : '' }}"
                                               data-format="HH:mm" name="end_time1" {{ $expiredFromMonth ? 'disabled' : $disabled }}
                                               data-range="{{ $rangeTime ? json_encode($rangeTime['rend1']) : null }}"
                                               @if ($item && $isChangeEndTime1)
                                               data-toggle="tooltip" title="{{ $textApproved . ': '. $item->end_time1 }}"
                                               @endif/>
                                        <span class="input-group-addon">
                                            <span class="fa fa-calendar"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label class="required">{{ trans('manage_time::view.working_start_time2') }} <em>*</em> <i class="fa fa-question-circle"></i></label>
                                    <div class="input-group input-group-time">
                                        <?php
                                        $isChangeStartTime2 = $item && $item->start_time2 != $draftItem->start_time2;
                                        ?>
                                        <input type="text" value="{{ old('start_time2') ? old('start_time2') : ($draftItem ? $draftItem->start_time2 : $defaultTimes['start_time2']) }}"
                                               class="form-control {{ $isChangeStartTime2 ? 'unapproved' : '' }}"
                                               data-format="HH:mm" name="start_time2" {{ $expiredFromMonth ? 'disabled' : $disabled }} data-range="{{ $rangeTime ? json_encode($rangeTime['rstart2']) : null }}"
                                               @if ($item && $isChangeStartTime2)
                                               data-toggle="tooltip" title="{{ $textApproved . ': '. $item->start_time2 }}"
                                               @endif/>
                                        <span class="input-group-addon">
                                            <span class="fa fa-calendar"></span>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-sm-6 form-group">
                                    <label class="required">{{ trans('manage_time::view.working_end_time2') }} <em>*</em> <i class="fa fa-question-circle"></i></label>
                                    <div class="input-group input-group-time">
                                        <?php
                                        $isChangeEndTime2 = $item && $item->end_time2 != $draftItem->end_time2;
                                        ?>
                                        <input type="text" value="{{ old('end_time2') ? old('end_time2') : ($draftItem ? $draftItem->end_time2 : $defaultTimes['end_time2']) }}"
                                               class="form-control {{ $isChangeEndTime2 ? 'unapproved' : '' }}"
                                               data-format="HH:mm" name="end_time2" {{ $expiredFromMonth ? 'disabled' : $disabled }}
                                               data-range="{{ $rangeTime ? json_encode($rangeTime['rend2']) : null }}"
                                               @if ($item && $isChangeEndTime2)
                                               data-toggle="tooltip" title="{{ $textApproved . ': '. $item->end_time2 }}"
                                               @endif/>
                                        <span class="input-group-addon">
                                            <span class="fa fa-calendar"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="required">{{ trans('manage_time::view.Reason change working time') }} <em>*</em></label>
                        <textarea class="form-control text-resize-y" 
                                  name="reason" rows="3" {{ $expiredFromMonth ? 'disabled' : $disabled }}>{{ old('reason') ? old('reason') : ($draftItem ? $draftItem->reason : null) }}</textarea>
                    </div>
                    
                    @if ($item)
                    <div class="form-group">
                        <span>{{ trans('manage_time::view.Filing date') }}: {{ $item->created_at }}</span>
                    </div>
                    @endif

                    <div class="text-center">
                        @if ($item)
                        <input type="hidden" name="id" value="{{ $item->id }}"/>
                        @endif
                        
                        @include('manage_time::working-time.includes.status-btn')
                    </div>

                </form>
            </div>
            
            @if ($hasOtherTime['status'] && $hasOtherTime['list'])
            <div class="box-header">
                @include('manage_time::working-time.includes.other-times')
            </div>
            @endif
            
            @if (!$listComments->isEmpty())
            <div class="box-body">
                @include('manage_time::working-time.includes.comments')
            </div>
            @endif
            <!-- /.box-body -->
        </div>
        <!-- /. box -->
    </div>
    <div class="sidebar-col">
        <div class="sidebar-inner">
            @include('manage_time::working-time.includes.sidebar')
        </div>
    </div>
    
    @if ($item)
        @include('manage_time::working-time.includes.register-modal')
    @endif
</div>

@stop

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
    var textRequiredField = '<?php echo trans('manage_time::message.this_field_required') ?>';
    var textGreaterThanFromMonth = '<?php echo trans('manage_time::message.to_month_must_greater_than_from_month') ?>';
    var textGreaterThanStartTime = '<?php echo trans('manage_time::message.time_end_must_greater_than_time_start') ?>';
    var notValidDateFormat = '<?php echo trans('manage_time::message.not_valid_date_format') ?>';
    var textInvalidTotalWorkingTime = '<?php echo trans('manage_time::message.total_working_time_invalid') ?>';
    var textInvalidShiftTime = '<?php echo trans('manage_time::message.invalid_shift_time', ['mor' => $defaultTimes['min_mor'], 'aft' => $defaultTimes['min_aft']]) ?>';
    var textRequiredItem = '{{ trans('manage_time::view.None item checked') }}';
    var textItemNotValid = '{{ trans('manage_time::view.None item valid!') }}';
    var TOTAL_TIME = {{ MTConst::TOTAL_WORKING_TIME }};
    var MIN_HOUR_MOR = parseFloat({{ $defaultTimes['min_mor'] }});
    var MIN_HOUR_AFT = parseFloat({{ $defaultTimes['min_aft'] }});
    var STEPING_MINUTE = {{ MTConst::STEPING_MINUTE }};

    RKfuncion.select2.__formatReponesSelection = function (response, domSpan) {
        if (typeof response.dataMore === 'object') {
            var domSelect = domSpan.closest('.select2.select2-container')
                .siblings('select').first();
            $.each(response.dataMore, function (key, value) {
                domSelect.data('select2-more-' + key, value);
            });
        }
        var option = $('.select2-locked select option[value="'+ response.id +'"]');
        if (option.attr('locked')) {
            domSpan.addClass('locked-tag');
            response.locked = true;
        }
        return  response.text;
    };

    $('.select2-locked .select-search').on('select2:unselecting', function (e) {
        if ($(e.params.args.data.element).attr('locked')) {
            e.select2.execStop();
        }
    });
</script>
<script src="{{ CoreUrl::asset('asset_managetime/js/working-time.js') }}"></script>
@stop

