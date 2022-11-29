<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\ManageTime\Model\WorkingTimeRegister;
    use Rikkei\ManageTime\View\ManageTimeConst as MTConst;
    use Rikkei\ManageTime\View\WorkingTime;
    
    $objViewWT = new WorkingTime();
    $arrEmpIdTable = [];
    $startDate = Carbon::parse($workingTime->from_date)->format('d-m-Y');
    $endDate = Carbon::parse($workingTime->to_date)->format('d-m-Y');
    $listStatuses = $objViewWT->listWorkingTimeStatuses();
    $enableInput = ($permiss['edit'] || $permiss['update_approved']) && !$isPageDetail;
?>

@extends('layouts.default')

@section('title', trans('manage_time::view.Edit working time'))

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/working-time.css') }}">
@stop

@section('content')
<div class="content-sidebar">
    <div class="content-col">
        <!-- Box mission list -->
        <div class="box box-info">
            <div class="box-body">
                @if (isset($workingTime))
                <div class="row">
                    <div class="col-sm-4 col-md-3">
                        {!! $objViewWT->renderStatusHtml($listStatuses, $workingTime->status)!!}
                    </div>
                    <div class="col-sm-8 col-md-9 text-right">
                    </div>
                </div>
                @endif
                
                <form role="form" id="working_time_form" autocomplete="off" 
                    method="POST" 
                    action="{{ route('manage_time::wktime.update', ['id' => $workingTime->id]) }}"
                >
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label>{{ trans('manage_time::view.Registrant') }}</label>
                                    <div class="input-box">
                                        <input type="text" class="form-control" value="{{ $employee->name }} ({{ $employee->email }})" disabled />
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
                                    <label class="required">{{ trans('manage_time::view.Approver') }} <em>*</em></label>
                                    <div class="input-box">
                                        <select class="form-control select-search select-tooltip" name="approver_id" 
                                            data-remote-url="{{ route('manage_time::wktime.search_approver') }}"
                                            @if (!$enableInput)
                                                disabled
                                            @endif
                                            >
                                            <option value="{{$workingTime->approver->id}}">{{ $workingTime->approver->name . ' (' . preg_replace('/@.*/', '',$workingTime->approver->email) . ')' }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label>{{ trans('manage_time::view.Related persons need notified') }}</label>
                                    <div class="input-box select2-locked">
                                        <select name="related_ids[]" class="form-control select-search select-tooltip" 
                                            multiple data-remote-url="{{ route('team::employee.list.search.ajax') }}"
                                            @if (!$enableInput)
                                                disabled
                                            @endif
                                            >
                                            @if (isset($relatedPersonsList) && count($relatedPersonsList))
                                                @foreach($relatedPersonsList as $item)
                                                    <option value="{{ $item->id }}" selected>{{ $item->name . ' (' . preg_replace('/@.*/', '',$item->email) . ')' }}</option>
                                                @endforeach
                                            @else
                                                <option value="">&nbsp;</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="required">{{ trans('manage_time::view.From date') }} <em>*</em></label>
                                        <div class='input-group date datepicker' id="dateStartPicker">
                                            <input type='text' name="startDate" class="form-control" value="{{ $startDate }}" 
                                            @if (!$enableInput)
                                                disabled
                                            @endif
                                            />
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="required">{{ trans('manage_time::view.End date') }} <em>*</em></label>
                                        <div class='input-group date datepicker' id="dateEndPicker">
                                            <input type='text' name="endDate" class="form-control" value="{{ $endDate }}" 
                                            @if (!$enableInput)
                                                disabled
                                            @endif/>
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label class="required">{{ trans('manage_time::view.Select work time frame') }}<em>*</em></label>
                                    <div class="input-box">
                                        @if (isset($workingTimeFrame) && count ($workingTimeFrame))
                                            <select name="workingTime" id="" class="form-control select2-base select-time"
                                                @if (!$enableInput)
                                                    disabled
                                                @endif
                                                >
                                                @foreach ($workingTimeFrame as $key => $item)
                                                    <option value="{{$key}}" {!! $workingTime->key_working_time == $key ? 'selected' : '' !!}>
                                                        {{ $objViewWT->getLabelWorkingTime($item) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label class="required">{{ trans('manage_time::view.Select 1/4 break time') }}<em>*</em></label>
                                    <div class="input-box">
                                        @if (isset($workingTimeHalfFrame) && count ($workingTimeHalfFrame))
                                            <input type='text' name="workingTimeHalf" class="form-control hidden" value="0" />
                                            <select name="workingTimeHalf" id="" class="form-control select2-base select-time" disabled>
                                                @foreach ($workingTimeHalfFrame as $key => $item)
                                                    <option value="{{$key}}" {!! $workingTime->key_working_time_half == $key ? 'selected' : '' !!}>
                                                        {{ $objViewWT->getLabelWorkingTimeHalf($item) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label>{{ trans('core::view.Select a project') }}</label>
                                    <div class="input-box">
                                        <select class="form-control select-tooltip select2-base"
                                                name="project_id" id="select-project" {!! !$enableInput ? 'disabled' : '' !!}>
                                            @if (isset($projects) && count($projects))
                                                <option value="">&nbsp;</option>
                                                @foreach ($projects as $item)
                                                    <option value="{{ $item->id }}"
                                                        @if ($workingTime->proj_id == $item->id)
                                                            selected
                                                        @endif
                                                    >
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <label class="required">{{ trans('manage_time::view.Reason change working time') }} <em>*</em></label>
                                    <textarea class="form-control text-resize-y" name="reason" rows="3"
                                    @if (!$enableInput)
                                        disabled
                                    @endif
                                    >{{ $workingTime->reason}}</textarea>
                                </div>
                            </div>
                            @if ($enableInput)
                                <div class="row">
                                    <div class="col-sm-11 form-group">
                                        <div class="input-box">
                                            <label class="required">{{ trans('manage_time::view.Add employees are registered change hours') }}</label>
                                            <select name="" class="form-control select-search-employee" id="search_add_employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple>
                                            </select>
                                            <i class="color-red">{!! trans('ot::view.Note add register') !!}</i></i>
                                        </div>
                                    </div>
                                    <div class="col-sm-1 request-form-group">
                                        <div class="input-box">
                                            <br>
                                            <a class="btn btn-success" id="btn_add_employee_table_generate" data-url="{{ route('manage_time::profile.mission.get-working-time-employees') }}">
                                                <i class="fa fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <input type="hidden" name="table_data_emps" id="table_data_emps">
                        </div>
                    </div>
                    <br>
                    <br>
                    <p class="required">{{ trans('manage_time::view.The list of employees who are registered changes in hours') }}</p>
                    <div class="table-responsive">
                        <table class="table edit-table table-striped table-grid-data table-hover table-bordered table-condensed" id="table_employees_generate">
                            <thead>
                                <tr class="info">
                                    <th>{{ trans('manage_time::view.Employee code') }}</th>
                                    <th>{{ trans('manage_time::view.Employee name') }}</th>
                                    <th>{{ trans('manage_time::view.Start date') }}</th>
                                    <th>{{ trans('manage_time::view.End date') }}</th>
                                    <th class="text-center">{{ trans('manage_time::view.Time in morning') }}</th>
                                    <th class="text-center">{{ trans('manage_time::view.Time out morning') }}</th>
                                    <th class="text-center">{{ trans('manage_time::view.Time in afternoon') }}</th>
                                    <th class="text-center">{{ trans('manage_time::view.Time out afternoon') }}</th>
                                    <th class="text-center">{{ trans('manage_time::view.1/4 am') }}</th>
                                    <th class="text-center">{{ trans('manage_time::view.1/4 pm') }}</th>
                                    @if ($enableInput)
                                        <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($listEmployeeDetail as $item)
                                    <?php
                                        $arrEmpIdTable[] = $item->employee_id;
                                    ?>
                                    <tr data-id_emp={{ $item->employee_id }}
                                        data-status=1 
                                        data-generate=0 
                                        data-key_working_time={{ $objViewWT->getKeyWorkingTime($item->start_time1, $item->start_time2)}}
                                        data-key_working_time_half={{ $objViewWT->getKeyWorkingTimeHalf($item->half_morning, $item->half_afternoon)}}
                                    >
                                        <td class="col-id_register hidden">{{ $item->working_time_id }}</td>
                                        <td class="col-code">{{ $item->employee->employee_code }}</td>
                                        <td class="col-name">{{ $item->employee->name }}</td>
                                        <td class="col-start_date">{{ Carbon::parse($item->from_date)->format('d-m-Y') }}</td>
                                        <td class="col-end_date">{{ Carbon::parse($item->to_date)->format('d-m-Y')}}</td>
                                        <td class="col-morning_in text-center">{{ $item->start_time1 }}</td>
                                        <td class="col-morning_out text-center">{{ $item->end_time1 }}</td>
                                        <td class="col-afternoon_in text-center">{{ $item->start_time2 }}</td>
                                        <td class="col-afternoon_out text-center">{{ $item->end_time2 }}</td>
                                        <td class="col-half_morning text-center">{{ $item->half_morning }}</td>
                                        <td class="col-half_afternoon text-center">{{ $item->half_afternoon }}</td>
                                        @if ($enableInput)
                                            <td>
                                                <button type="button" class="btn btn-primary btn-edit"><i class="fa fa-pencil-square-o"></i></button>
                                                <button type="button" class="btn btn-delete delete"><i class="fa fa-minus"></i></button>
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                        @if (isset($comments) && count($comments))
                            <div class="form-group">
                                <div class="box box-widget">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">{{ trans('manage_time::view.Disapprove reason') }}</h3>
                                    </div>
                                    <div class="box-body">
                                        <ul class="products-list product-list-in-box">
                                            @foreach($comments as $comment)
                                                <li class="item">
                                                    <div class="post">
                                                        <div class="user-block">
                                                            <img class="img-bordered-sm" src="{{ $comment->avatar_url }}" alt="{{ $comment->name }}">
                                                            <span class="username">{{ $comment->name }}</span>
                                                            <span class="description">{{ Carbon::parse($comment->created_at)->format('d-m-Y H:i:s') }}</span>
                                                        </div>
                                                        <p>
                                                            {!! CoreView::nl2br($comment->content) !!}
                                                        </p>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="text-center">
                            @if (isset($workingTime))
                                <input type="hidden" name="ids" value="{{ $workingTime->id }}"/>
                                <input type="hidden" name="status" value="{{ WorkingTimeRegister::STATUS_APPROVE }}">
                            @endif

                            @include('manage_time::working-time.includes.status-btn')
                        </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /. box -->
</div>

<div class="sidebar-col">
    <div class="sidebar-inner">
        @include('manage_time::working-time.includes.sidebar')
    </div>
</div>
</div>

@include('manage_time::working-time.includes.register-modal')

<table id="row_employees_generate_clone" class="hidden">
    <tr data-id_emp=0 data-status=1 data-generate=1 data-key_working_time=0 data-key_working_time_half=0>
        <td class="col-id_register hidden"></td>
        <td class="col-code">aa</td>
        <td class="col-name">cc</td>
        <td class="col-start_date">12</td>
        <td class="col-end_date">34</td>
        <td class="col-morning_in text-center">08:00</td>
        <td class="col-morning_out text-center">12:00</td>
        <td class="col-afternoon_in text-center">13:30</td>
        <td class="col-afternoon_out text-center">17:30</td>
        <td class="col-half_morning text-center">10:00</td>
        <td class="col-half_afternoon text-center">15:30</td>
        <td class="action">
            <button type="button" class="btn btn-primary btn-edit"><i class="fa fa-pencil-square-o"></i></button>
            <button type="button" class="btn btn-delete delete"><i class="fa fa-minus"></i></button>
        </td>
    </tr>
</table>

<!-- Trigger the modal with a button -->
<button type="button" class="btn btn-info btn-lg btn-modalError hidden" data-toggle="modal" data-target="#modalError">Open Modal</button>
<button type="button" class="btn btn-info btn-lg btn-modalEdit hidden" data-toggle="modal" data-target="#modalEdit">Open edit</button>
@include('manage_time::working-time.modal.edit_register')
@include('manage_time::working-time.modal.error_register')

@stop

@section('script')
    <script>
        var urlEmpProj = "{{route('project::project.get-json-employee-project')}}";
        var urlDetail  = "{{route('manage_time::wktime.detail', ['id' => ''])}}";
        var arrEmpIdTable = <?php echo json_encode($arrEmpIdTable) ?>;
        var statusNotUpdate = 0;
        var statusNotGenerate = 0;
        var urlSaveRegister = "{{ route('manage_time::wktime.save_register') }}";
        var workingTimeFrame = <?php echo json_encode($workingTimeFrame) ?>;
        var workingTimeHalfFrame = <?php echo json_encode($workingTimeHalfFrame) ?>;
        var _token = '{{ csrf_token() }}';
        var idRegister = "{{ $workingTime->id }}";
        var messRequired = "{{ trans('manage_time::message.Required fields cannot be left blank')}}";
        var messEndLessStart = "{{ trans('manage_time::message.Have an end time less than the start time')}}";
        var messTheReson = "{{ trans('manage_time::message.You need to enter the reason for registration.')}}";
        var messEmpApprove = "{{ trans('manage_time::message.You have not selected employee approve')}}";
        var messNotObject = "{{ trans('manage_time::message.No objects are registered')}}";
        var messEndThanStart = "{{ trans('manage_time::message.time_end_must_greater_than_time_start')}}";
        var messEmpDuplicate = "{{ trans('manage_time::message.The following staff has a duplicate registration time:')}}";
        var messEmpNotCode = "{{ trans('manage_time::message.No employee code found')}}";
        var viewChangeWT = "{{ trans('manage_time::view.Change working time')}}";
        var viewEmployee = "{{trans('manage_time::view.Employee')}}";
        var viewFromDate = "{{trans('manage_time::view.From date')}}";
        var viewToDate = "{{trans('manage_time::view.End date')}}";
        var viewAppType = "{{trans('manage_time::view.Application type')}}";
        var viewDetail = "{{trans('manage_time::view.Detail')}}";
        var txtNote = "{{trans('manage_time::view.Note')}}";
        var wKTRelation = <?php echo json_encode($wKTRelationShip) ?>;
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/working-time-register.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2-base').select2();
            $('.select-search-employee').selectSearchEmployee();
            $('#error-working-time-register').modal('show');
        });
    </script>
@stop