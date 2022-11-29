@extends('layouts.default')

@section('title')
    {{trans('manage_time::view.List timekeeping table')}}
@endsection

<?php
    use Carbon\Carbon;
    use Rikkei\Team\Model\Employee;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Team\View\TeamList;
    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\ManageTime\View\ManageTimeCommon;
    use Rikkei\ManageTime\Model\TimekeepingTable;
    use Rikkei\Resource\View\getOptions;
    use Rikkei\ManageTime\View\TimekeepingPermission;

    $currentYear = Carbon::now()->year;
    $currentMonth = Carbon::now()->month;
    $monthsOfYear = ManageTimeCommon::getMonths();
    $teamsOptionAll = TeamList::toOption(null, true, false);
    $tblTimekeepingTable = TimekeepingTable::getTableName();
    $tblEmployee = Employee::getTableName();

    $permissionTimeKeeping = TimekeepingPermission::isPermission();
    $withColumnDel = $permissionTimeKeeping ? 220 : 70;
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}" />
@endsection

@section('content')
    <div class="box box-primary">
        <div class="box-header with-border">
            @if ($permissionTimeKeeping)
                <button id="btn_add_timekeeping_table" class="btn btn-success" ><i class="fa fa-plus" aria-hidden="true"></i> {{ trans('manage_time::view.Add new') }}</button>
            @endif
            <div class="pull-right">   
                @include('team::include.filter')
            </div>
        </div>
        <!-- /.box-header -->

        <div class="box-body no-padding"> 
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table-control" id="table-reason">
                    <thead>
                        <tr>
                            <th class="managetime-col-25" style="min-width: 25px;">{{ trans('manage_time::view.No.') }}</th>
                            <th class="managetime-col-120 sorting {{ TeamConfig::getDirClass('timekeeping_table_name') }}" data-order="timekeeping_table_name" data-dir="{{ TeamConfig::getDirOrder('timekeeping_table_name') }}" style="min-width: 120px; max-width: 120px;">{{ trans('manage_time::view.Timekeeping table name') }}</th>
                            <th class="managetime-col-80 sorting {{ TeamConfig::getDirClass('team_name') }}" data-order="team_name" data-dir="{{ TeamConfig::getDirOrder('team_name') }}" style="min-width: 80px; max-width: 80px;">{{ trans('manage_time::view.Team') }}</th>
                            <th class="managetime-col-40 sorting {{ TeamConfig::getDirClass('month') }}" data-order="month" data-dir="{{ TeamConfig::getDirOrder('month') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Month') }}</th>
                            <th class="managetime-col-40 sorting {{ TeamConfig::getDirClass('year') }}" data-order="year" data-dir="{{ TeamConfig::getDirOrder('year') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Year') }}</th>
                            <th class="managetime-col-60 sorting {{ TeamConfig::getDirClass('start_date') }}" data-order="start_date" data-dir="{{ TeamConfig::getDirOrder('start_date') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.From date') }}</th>
                            <th class="managetime-col-60 sorting {{ TeamConfig::getDirClass('end_date') }}" data-order="end_date" data-dir="{{ TeamConfig::getDirOrder('end_date') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.End date') }}</th>
                            <th class="managetime-col-80 sorting {{ TeamConfig::getDirClass('creator_name') }}" data-order="creator_name" data-dir="{{ TeamConfig::getDirOrder('creator_name') }}" style="min-width: 80px; max-width: 80px;">{{ trans('manage_time::view.Creator') }}</th>
                            <th class="managetime-col-220" style="min-width: {{ $withColumnDel }}px; max-width: {{ $withColumnDel }}px;">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tblTimekeepingTable }}.timekeeping_table_name]" value='{{ CoreForm::getFilterData("{$tblTimekeepingTable}.timekeeping_table_name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $monthFilter = CoreForm::getFilterData('number', "$tblTimekeepingTable.month");
                                            $monthsOfYearTypeNum = ManageTimeCommon::getMonths(true);
                                        ?>
                                        <select name="filter[number][{{ $tblTimekeepingTable }}.month]" class="form-control select-grid filter-grid select-search" autocomplete="off" style="width: 100%;">
                                            @if (isset($monthsOfYearTypeNum) && count($monthsOfYearTypeNum))
                                                <option value="">&nbsp;</option>
                                                @foreach ($monthsOfYearTypeNum as $key => $value)
                                                    <option value="{{ $key }}" <?php if ($key == $monthFilter): ?> selected<?php endif; ?>>{{ $value }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $yearFilter = CoreForm::getFilterData('number', "$tblTimekeepingTable.year");
                                        ?>
                                        <input type="text" name="filter[number][{{ $tblTimekeepingTable }}.year]" value='{{ $yearFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $startDateFilter = CoreForm::getFilterData('except', "$tblTimekeepingTable.start_date");
                                        ?>
                                        <input type="text" name="filter[except][{{ $tblTimekeepingTable }}.start_date]" value='{{ $startDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $endDateFilter = CoreForm::getFilterData('except', "$tblTimekeepingTable.end_date");
                                        ?>
                                        <input type="text" name="filter[except][{{ $tblTimekeepingTable }}.end_date]" value='{{ $endDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tblEmployee }}.name]" value='{{ CoreForm::getFilterData("{$tblEmployee}.name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        @if (isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr timekeeping-table-id="{{ $item->timekeeping_table_id }}" timekeeping-table-name="{{ $item->timekeeping_table_name }}">
                                    <td>{{ $i }}</td>
                                    <td class="name_table">{{ $item->timekeeping_table_name }}</td>
                                    <td class="team_table">{{ $item->team_name }}</td>
                                    <td class="month_table">{{ $item->month }}</td>
                                    <td class="year_table">{{ $item->year }}</td>
                                    <td class="dateStart_table">{{ Carbon::parse($item->start_date)->format('d-m-Y') }}</td>
                                    <td class="dateEnd_table">{{ Carbon::parse($item->end_date)->format('d-m-Y') }}</td>
                                    <td>{{ $item->creator_name }}</td>
                                    <td class="align-center white-space-nowrap">
                                        <?php
                                            $teamCodePrefix = \Rikkei\Team\Model\Team::getTeamCodePrefix($item->team_code);
                                        ?>
                                        @if ($permissionTimeKeeping)
                                        @if ($teamCodePrefix !== 'japan')
                                        <button class="btn btn-primary btn-show-modal-upload" title="{{ trans('manage_time::view.Import file timekeeping') }}">
                                            <i class="fa fa-upload"></i>
                                        </button>
                                        @else
                                        <button class="btn btn-primary btn-show-modal-import" title="{{ trans('manage_time::view.Import time in/out') }}">
                                            <i class="fa fa-upload"></i>
                                        </button>
                                        @endif
                                        <button class="btn btn-primary btn-show-modal-get-data-timekeeping" title="{{ trans('manage_time::view.Get data from related modules') }}">
                                            <i class="fa fa-exchange"></i>
                                        </button>
                                        <button class="btn btn-primary btn-update-timekeeping-aggregate" title="{{ trans('manage_time::view.Title update timekeeping aggregate') }}">
                                            <i class="fa fa-pencil-square-o"></i>
                                        </button>
                                        
                                        @if ($teamCodePrefix !== 'japan' && in_array($item->type, $typesOffcial))
                                        <button class="btn btn-primary btn-show-modal-update-day-off" title="{{ trans('manage_time::view.Update day off') }}">
                                            <i class="fa fa-calendar-check-o"></i>
                                        </button>
                                        @endif
                                        @endif
                                        <a class="btn btn-success" title="{{ trans('manage_time::view.View timekeeping detail') }}" target="_blank" 
                                            href="{{ route('manage_time::timekeeping.timekeeping-detail', ['timekeepingTableId' => $item->timekeeping_table_id]) }}" >
                                            <i class="fa fa-info-circle"></i>
                                        </a>
                                        <a class="btn btn-success" title="{{ trans('manage_time::view.View timekeeping aggregate') }}" target="_blank" 
                                            href="{{ route('manage_time::timekeeping.timekeeping-aggregate', ['timekeepingTableId' => $item->timekeeping_table_id]) }}" >
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if ($permissionTimeKeeping)
                                        {{-- <button class="btn btn-info btn-show-modal-update" title="{{ trans('manage_time::view.Update timekeeping time') }}">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </button> --}}
                                        <button class="btn btn-info btn-show-modal-lock_up" title="{{ trans('manage_time::view.Lock timekeeping') }}" value="{{ $item->lock_up }}">
                                            @if ($item->lock_up == TimekeepingTable::OPEN_LOCK_UP)
                                                <i class="fa fa-unlock" aria-hidden="true"></i>
                                            @else
                                                <i class="fa fa-lock" aria-hidden="true"></i>
                                            @endif
                                        </button>
                                        <a class="btn btn-success" title="{{trans('manage_time::view.List employee after lock')}}" target="_blank" 
                                            href="{{ route('manage_time::timekeeping.list-employee-after-lock', ['id' => $item->timekeeping_table_id]) }}" >
                                            <i class="fa fa-list"></i>
                                        </a>
                                        <form action="{{ route('manage_time::timekeeping.delete-timekeeping-table') }}" method="post" class="form-inline">
                                            {!! csrf_field() !!}
                                            {!! method_field('delete') !!}
                                            <input type="hidden" name="id" value="{{ $item->timekeeping_table_id }}" />
                                            <button href="" class="btn-delete delete-confirm" title="{{ trans('manage_time::view.Delete') }}" disabled>
                                                <span><i class="fa fa-trash"></i></span>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <!-- /.box-body -->

        <div class="box-footer no-padding">
            @include('team::include.pager')
        </div>
        <!-- /.box-footer -->

        <!-- Modal add timekeeping table -->
        <div id="modal_add_timekeeping_table" class="modal fade" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" style="font-size: 24px;">{{ trans('manage_time::view.Create new timekeeping table detail') }}</h4>
                    </div>
                    <form method="POST" action="{{ route('manage_time::timekeeping.save-timekeeping-table') }}" autocomplete="off" id="form_add_timekeeing_table">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="radio inline-block">
                                    <label>
                                        <input type="radio" name="contract_type"  value="{{ getOptions::WORKING_OFFICIAL }}" checked="">
                                        {{ trans('manage_time::view.Regular or probationary employees') }}
                                    </label>
                                </div>
                                <div class="radio inline-block margin-left-40">
                                    <label>
                                        <input type="radio" name="contract_type"  value="{{ getOptions::WORKING_PARTTIME }}">
                                        {{ trans('manage_time::view.Contract employees') }}
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label required">{{ trans('manage_time::view.Team') }} <em>*</em></label>
                                <div class="input-box">
                                    <select class="form-control select-search input-select-team-member" name="team_id" id="team_id" style="width: 100%;">
                                        @if (isset($teamsOptionAll) && count($teamsOptionAll))
                                            @foreach($teamsOptionAll as $option)
                                                <option value="{{ $option['value'] }}" <?php if (!in_array($option['value'], $teamIdAllowCreate)) { ?> disabled="disabled" <?php } ?>>{{ $option['label'] }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <label id="team_id-error" class="managetime-error" for="team_id">{{ trans('manage_time::view.This field is required') }}</label>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.Month') }} <em>*</em></label>
                                    <div class="input-box">
                                        <select class="form-control select-search" name="month" id="month">
                                            @if (isset($monthsOfYear) && count($monthsOfYear))
                                                @foreach ($monthsOfYear as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <label id="month-error" class="managetime-error" for="month">{{ trans('manage_time::view.This field is required') }}</label>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.Year') }} <em>*</em></label>
                                    <div class="input-box">
                                        <input type="number" min="0" name="year" class="form-control" id="year" value="{{ $currentYear }}" />
                                    </div>
                                    <label id="year-error" class="managetime-error" for="year">{{ trans('manage_time::view.This field is required') }}</label>
                                    <label id="year_digit-error" class="managetime-error" for="year">{{ trans('manage_time::view.Please enter only digits') }}</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.From date') }} <em>*</em></label>
                                    <div class='input-group date' >
			                            <span class="input-group-addon managetime-icon-date">
			                                <span class="glyphicon glyphicon-calendar"></span>
			                            </span>
			                            <input type='text' class="form-control managetime-date" name="start_date" placeholder="dd-mm-yyyy" />
			                        </div>
                                    <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.End date') }} <em>*</em></label>
                                    <div class='input-group date' >
			                            <span class="input-group-addon managetime-icon-date">
			                                <span class="glyphicon glyphicon-calendar"></span>
			                            </span>
			                            <input type='text' class="form-control managetime-date" name="end_date" placeholder="dd-mm-yyyy" />
			                        </div>
                                    <label id="end_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.This field is required') }}</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label required">{{ trans('manage_time::view.Timekeeping table name') }} <em>*</em></label>
                                <div class="input-box">
                                    <input type="text" name="timekeeping_table_name" class="form-control" id="timekeeping_table_name" />
                                </div>
                                <label id="timekeeping_table_name-error" class="managetime-error" for="timekeeping_table_name">{{ trans('manage_time::view.This field is required') }}</label>
                                <label id="timekeeping_table_name_length-error" class="managetime-error" for="timekeeping_table_name">{{ trans('manage_time::view.This field not be greater than 255 characters') }}</label>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-xs-12">
                                    <b>{{ trans('manage_time::view.Attention:') }}</b>
                                    {!! trans('manage_time::view.Note create timekeeping') !!}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                            <button type="submit" class="btn btn-primary" id="btn_add_submit" onclick="return checkSubmitTimekeepingTable();"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Save') }}</button>
                            <input type="hidden" id="check_submit_add_timekeeping_table" name="" value="0">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal upload file timekeeping -->
        <div id="modal_upload_file_timekeeping" class="modal fade" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" style="font-size: 24px;">{{ trans('manage_time::view.Import file timekeeping') }}</h4>
                    </div>
                    <form method="POST" action="{{ route('manage_time::timekeeping.post-upload-file') }}" enctype="multipart/form-data" id="form_updload_file_timekeeping">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="control-label">{{ trans('manage_time::view.Timekeeping table name') }}</label>
                                <div class="input-box">
                                    <input type="text" id="upload_timekeeping_table_name" name="timekeeping_table_name" class="form-control" readonly />
                                    <input type="hidden" id="upload_timekeeping_table_id" name="timekeeping_table_id" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label required">{{ trans('manage_time::view.Timekeeping file (csv)') }} <em>*</em></label>
                                <div class="input-box">
                                    <input class="form-control" type="file" name="file" id="file_timekeeping" />
                                </div>
                                <label id="file_timekeeping-error" class="managetime-error" for="file">{{ trans('manage_time::view.This field is required') }}</label>
                            </div>

                            <div class="form-group">
                                <img src="{{ URL::asset('asset_managetime/images/template/cham_cong.png') }}" class="img-responsive" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                            <button type="submit" class="btn-add" id="btn_upload_file_timekeeping" onclick="return checkUploadFileTimekeeping();" data-loading-text="<i class='fa fa-spin fa-refresh'></i> {{ trans('manage_time::view.Upload') }}"><i class="fa fa-upload"></i> {{ trans('manage_time::view.Upload') }}</button>
                            <input type="hidden" id="check_upload_file_timekeeping" name="" value="0">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Modal import time in/out -->
        <div id="modal_import_time_in_out" class="modal fade" role="dialog" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" style="font-size: 24px;">{{ trans('manage_time::view.Import time in/out') }}</h4>
                    </div>
                    <form method="POST" action="{{ route('manage_time::timekeeping.import-time-in-out') }}">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="control-label">{{ trans('manage_time::view.Would you like to implement this function?') }}</label>
                                <div class="input-box">
                                    <input type="hidden" id="timekeeping_table_id_data" name="timekeeping_table_id" class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                            <button type="submit" class="btn-add btn-active-loading"  data-loading-text="<i class='fa fa-spin fa-refresh'></i> {{ trans('manage_time::view.Yes') }}"> {{ trans('manage_time::view.Yes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Modal get data timekeeping -->
        <div id="modal_get_data_timekeeping" class="modal fade" role="dialog" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" style="font-size: 24px;">{{ trans('manage_time::view.Get data from related modules') }}</h4>
                    </div>
                    <form method="POST" action="{{ route('manage_time::timekeeping.get-data-related-module') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="timekeeping_table_id" class="form-control timekeeping_table_id_data" />
                        <input type="text" name="dateStartTable" id="dateStartTable" hidden>
                        <input type="text" name="dateEndTable" id="dateEndTable" hidden>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-sm-12 managetime-form-group">
                                    <label class="control-label">{{ trans('manage_time::view.Update related employee') }}</label>
                                    <div class="input-box">
                                        <select name="empids[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.From date') }} <em>*</em></label>
                                    <div class='input-group date' >
                                        <span class="input-group-addon managetime-icon-date">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type='text' class="form-control managetime-date" name="start_date" placeholder="dd-mm-yyyy" autocomplete="off"/>
                                    </div>
                                    <label id="start_date-error-related" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.End date') }} <em>*</em></label>
                                    <div class='input-group date' >
                                        <span class="input-group-addon managetime-icon-date">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type='text' class="form-control managetime-date" name="end_date" placeholder="dd-mm-yyyy" autocomplete="off"/>
                                    </div>
                                    <label id="end_date-error-related" class="managetime-error" for="end_date">{{ trans('manage_time::view.This field is required') }}</label>
                                    <label id="end_date_before_start_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.The end date at must be after start date') }}</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 managetime-form-group">
                                    <label id="error-related" class="managetime-error" for="start_date"></label>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-xs-12">
                                    <b>{{ trans('manage_time::view.Attention:') }}</b>
                                    {!! trans('manage_time::view.Note related timekeeping') !!}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                            <button type="submit" class="btn-add" data-loading-text="<i class='fa fa-spin fa-refresh'></i> {{ trans('manage_time::view.Yes') }}" onclick="return checkSubmitTimekeepingTableRelated();" id="modal_btn_timekeeping_related"> {{ trans('manage_time::view.Yes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal update day off -->
        <div id="modal_update_day_off" class="modal fade" role="dialog" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" style="font-size: 24px;">{{ trans('manage_time::view.Update day off') }}</h4>
                    </div>
                    <form method="POST" action="{{ route('manage_time::timekeeping.update-day-off') }}">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="control-label">{{ trans('manage_time::view.Would you like to implement this function?') }}</label>
                                <div class="input-box">
                                    <input type="hidden" name="timekeeping_table_id" class="form-control timekeeping_table_id_data"/>
                                    <input type="hidden" name="update_day_off" value="1" class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                            <button type="submit" class="btn-add btn-active-loading"  data-loading-text="<i class='fa fa-spin fa-refresh'></i> {{ trans('manage_time::view.Yes') }}"> {{ trans('manage_time::view.Yes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal update timekeeping aggregate -->
        <div id="modal_update_timekeeping_aggregate" class="modal fade" role="dialog" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" style="font-size: 24px;">{{ trans('manage_time::view.Update timekeeping aggregate') }}</h4>
                    </div>
                    <form method="POST" action="{{ route('manage_time::timekeeping.update-timekeeping-aggregate') }}">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="control-label">{{ trans('manage_time::view.Would you like to implement this function?') }}</label>
                                <div class="input-box">
                                    <input type="hidden" id="timekeeping_table_id_aggregate" name="timekeeping_table_id" class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                            <button type="submit" class="btn-add btn-active-loading" data-loading-text="<i class='fa fa-spin fa-refresh'></i> {{ trans('manage_time::view.Yes') }}"> {{ trans('manage_time::view.Yes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal add timekeeping table -->
        <div id="modal_update_timekeeping_table" class="modal fade" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-center" style="font-size: 24px;">{{ trans('manage_time::view.Update timekeeping time') }}</h4>
                    </div>
                    <form method="POST" action="{{ route('manage_time::timekeeping.update-time-timekeeping-table') }}" autocomplete="off" id="form_update_time_timekeeing_table">
                        {{ csrf_field() }}
                        <input type="text" name="id_table" value="" id="id_table_md_update" hidden>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="control-label required">{{ trans('manage_time::view.Team') }} <em>*</em></label>
                                <input type="text" id="team_table_md_update" value="" class="form-control" disabled>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.Month') }} <em>*</em></label>
                                    <input type="text" id="month_table_md_update" value="" class="form-control" disabled>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.Year') }} <em>*</em></label>
                                    <input type="text" id="year_table_md_update" value="" class="form-control" disabled>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.From date') }} <em>*</em></label>
                                    <div class='input-group date' >
                                        <span class="input-group-addon managetime-icon-date">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type='text' class="form-control managetime-date" name="start_date" placeholder="dd-mm-yyyy" id="start_table_md_update"/>
                                    </div>
                                    <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
                                    <label id="start_date_after_end_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.The start date at must be before end date') }}</label>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.End date') }} <em>*</em></label>
                                    <div class='input-group date' >
                                        <span class="input-group-addon managetime-icon-date">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type='text' class="form-control managetime-date" name="end_date" placeholder="dd-mm-yyyy" id="end_table_md_update"/>
                                    </div>
                                    <label id="end_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.This field is required') }}</label>
                                    <label id="end_date_before_start_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.The end date at must be after start date') }}</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label required">{{ trans('manage_time::view.Timekeeping table name') }} <em>*</em></label>
                                <div class="input-box">
                                    <input type="text" name="timekeeping_table_name" class="form-control" id="name_table_md_update" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                            <button type="submit" class="btn btn-primary" id="btn_update_time_submit" onclick="return checkSubmitTimekeepingTableTime();"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Save') }}</button>
                            <input type="hidden" id="check_submit_update_time_timekeeping_table" name="" value="0">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal update status lock up -->
    <div id="modal_lock_up" class="modal fade" role="dialog" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" style="font-size: 24px;">{{ trans('manage_time::view.Lock timekeeping') }}</h4>
                </div>
                <form method="POST" action="{{ route('manage_time::timekeeping.update-lock-up') }}">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ trans('manage_time::view.Would you like to implement this function?') }}</label>
                            <div class="input-box">
                                <input type="hidden" name="timekeeping_table_id" class="form-control timekeeping_table_id_data"/>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                        <button type="submit" class="btn-add btn-active-loading"  data-loading-text="<i class='fa fa-spin fa-refresh'></i> {{ trans('manage_time::view.Yes') }}"> {{ trans('manage_time::view.Yes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script type="text/javascript">
        var currentMonth = '{{ $currentMonth }}';
        var currentYear = {{ date('Y') }};

        jQuery(document).ready(function ($) {
            selectSearchReload();
            $('input[name=start_date]').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                endDate: $('input[name=end_date]').val(),
            }).on('changeDate', function () {
                $('input[name=end_date]').datepicker('setStartDate', $('input[name=start_date]').val());
            });
            
            $('input[name=end_date]').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                startDate: $('input[name=start_date]').val(),
            }).on('changeDate', function () {
                $('input[name=start_date]').datepicker('setEndDate', $('input[name=end_date]').val());
            });

            $('.btn-active-loading').click(function() {
                $(this).button('loading');
            });

            $('.btn-show-modal-upload').click(function() {
                var data = $(this).closest('tr');
                $('#upload_timekeeping_table_name').val(data.attr('timekeeping-table-name'));
                $('#upload_timekeeping_table_id').val(data.attr('timekeeping-table-id'));
                $('#modal_upload_file_timekeeping').modal('show');
                $('#file_timekeeping-error').hide();
                $('#file_timekeeping').val('');
                $('#check_upload_file_timekeeping').val(0);
            });
            
            $('.btn-show-modal-import').click(function() {
                var data = $(this).closest('tr');
                $('#modal_import_time_in_out #timekeeping_table_id_data').val(data.attr('timekeeping-table-id'));
                $('#modal_import_time_in_out').modal('show');
            });

            $('.btn-show-modal-get-data-timekeeping').click(function() {
                var data = $(this).closest('tr');
                $('#modal_get_data_timekeeping .timekeeping_table_id_data').val(data.attr('timekeeping-table-id'));
                $('#modal_get_data_timekeeping #dateStartTable').val(data.find('.dateStart_table').text());
                $('#modal_get_data_timekeeping #dateEndTable').val(data.find('.dateEnd_table').text());
                $("#modal_get_data_timekeeping input[name=start_date]").datepicker({
                        format: 'dd-mm-yyyy',
                    }).datepicker("update", data.find('.dateStart_table').text());
                $("#modal_get_data_timekeeping input[name=end_date]").datepicker({
                        format: 'dd-mm-yyyy',
                    }).datepicker("update", data.find('.dateEnd_table').text());

                $('#modal_get_data_timekeeping').modal('show');
            });
            
            $('.btn-show-modal-update-day-off').click(function() {
                var data = $(this).closest('tr');
                $('#modal_update_day_off .timekeeping_table_id_data').val(data.attr('timekeeping-table-id'));
                $('#modal_update_day_off').modal('show');
            });

            $('.btn-update-timekeeping-aggregate').click(function() {
                var data = $(this).closest('tr');
                $('#timekeeping_table_id_aggregate').val(data.attr('timekeeping-table-id'));
                $('#modal_update_timekeeping_aggregate').modal('show');
            });

            $('#btn_add_timekeeping_table').click(function() {
                $('#check_submit_add_timekeeping_table').val(0);
                $('#timekeeping_table_name-error').hide();
                $('#timekeeping_table_name_length-error').hide();
                $('#timekeeping_table_name').val('');
                $('#team_id-error').hide();
                $('#month-error').hide();
                $('#year-error').hide();
                $('#year_digit-error').hide();
                $('#year').val('{{ $currentYear }}');
                $('#start_date-error').hide();
                $('#start_date_after_end_date-error').hide();
                $('#end_date-error').hide();
                $('#end_date_before_start_date-error').hide();
                $("#month").val(currentMonth);
                $("#month").trigger('change');
            });

            $('#timekeeping_table_name').keyup(function() {
                var checkSubmit = $('#check_submit_add_timekeeping_table').val();
                if (checkSubmit == 1) {
                    var timekeepingTableName = $('#timekeeping_table_name').val().trim();
                    if (timekeepingTableName == '') {
                        $('#timekeeping_table_name_length-error').hide();
                        $('#timekeeping_table_name-error').show();
                    } else {
                        $('#timekeeping_table_name-error').hide();
                        if (timekeepingTableName.length > 255) {;
                            $('#timekeeping_table_name_length-error').show();
                        } else {
                            $('#timekeeping_table_name_length-error').hide();
                        }
                    }
                }
            });

            $('#year').keyup(function() {
                var checkSubmit = $('#check_submit_add_timekeeping_table').val();
                if (checkSubmit == 1) {
                    var year = $('#year').val().trim();
                    if (year == '') {
                        $('#year_digit-error').hide();
                        $('#year-error').show();
                    } else {
                        $('#year-error').hide();
                        if (year < 0) {
                            $('#year_digit-error').show();
                        } else {
                            $('#year_digit-error').hide();
                        }
                    }
                }
                setTimekeepingTableName();
            });

            $('#year').keypress(function (e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
            });

            $('#file_timekeeping').change(function() {
                var checkSubmit = $('#check_upload_file_timekeeping').val();
                if (checkSubmit == 1) {
                    var fileTimekeeping = $('#file_timekeeping').val().trim();
                    if (fileTimekeeping == '') {
                        $('#file_timekeeping-error').show();
                    } else {
                        $('#file_timekeeping-error').hide();
                    }
                }
            });

            $('.filter-date').datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy',
                weekStart: 1,
                todayHighlight: true
            });

            $('.filter-date').on('keyup', function(e) {
                e.stopPropagation();
                if (e.keyCode == 13) {
                    $('.btn-search-filter').trigger('click');
                }
            });

            $('#month, #year, #team_id').on('change', function() {
                setTimekeepingTableName();
            });
        });

        /**
         * Set values in form create new `bang cham cong`
         *
         * @returns {void}
         */
        function setTimekeepingTableName() {
            $('#timekeeping_table_name-error').hide();
            
            var team = $('#team_id').select2('data')[0]['text'].trim();
            var month = $('#month').val();
            if (!$('#year').val().trim()) {
                $('#year').val(currentYear);
            } 
            var year = $('#year').val().trim();

            $('input[name=end_date]').datepicker('setStartDate', new Date(1, 1));
            $('input[name=start_date]').datepicker('setEndDate', new Date(2099, 12));

            $('input[name=start_date]').datepicker('setDate', getFirstDate(month, year));
            $('input[name=end_date]').datepicker('setDate', getLastDate(month, year));

            var startDate = $('input[name=start_date]').val();
            var endDate = $('input[name=end_date]').val();

            setTilte(startDate, endDate, month, year, team);
        }

        $('input[name=start_date], input[name=end_date]').change(function() {
            var startDate = $('input[name=start_date]').val();
            var endDate = $('input[name=end_date]').val();
            var month = $('#month').val();
            if (!$('#year').val().trim()) {
                $('#year').val(currentYear);
            } 
            var year = $('#year').val().trim();
            var team = $('#team_id').select2('data')[0]['text'].trim();
            setTilte(startDate, endDate, month, year, team);
        });

        function setTilte(startDate, endDate, month, year, team) {
            var title = "<?php echo trans('manage_time::view.Timekeeping table') ?>";

            title += ' tháng ' + pad(month);
            title += '/' + year;
            
            title += ' từ ' + startDate + ' đến ' + endDate;
            
            if (team != '' && team != null) {
                title += ' - ' + team;
            }

            $('#timekeeping_table_name').val(title);
        }

        /**
         * Get first date of month
         *
         * @param {int} month
         * @param {int} year
         * @returns {String}
         */
        function getFirstDate(month, year) {
            var firstDay = new Date(year, month - 1, 1);
            return pad(firstDay.getDate()) + '-' + pad(firstDay.getMonth() + 1) + '-' + firstDay.getFullYear();
        }

        /**
         * Get last date of month
         *
         * @param {int} month
         * @param {int} year
         * @returns {String}
         */
        function getLastDate(month, year) {
            var lastDay = new Date(year, month, 0);
            return pad(lastDay.getDate()) + '/' + pad(lastDay.getMonth() + 1) + '/' + lastDay.getFullYear();
        }

        function pad(number) {
            return ("0" + number).slice(-2);
        }

        function checkSubmitTimekeepingTable() {
            var status = 1;
            $('#check_submit_add_timekeeping_table').val(1);
            var timekeepingTableName = $('#timekeeping_table_name').val();
            if (timekeepingTableName == '') {
                $('#timekeeping_table_name-error').show();
                status = 0;
            } else {
                if (timekeepingTableName.length > 255) {;
                    $('#timekeeping_table_name_length-error').show();
                    status = 0;
                }
            }
            var teamId = $('#team_id').val();
            if (teamId == null) {
                $('#team_id-error').show();
                status = 0;
            }
            var month = $('#month').val();
            if (month == null) {
                $('#month-error').show();
                status = 0;
            }
            var year = $('#year').val().trim();
            if (year == '') {
                $('#year-error').show();
                status = 0;
            }
            var startDate = $('input[name=start_date]').datepicker("getDate");
            if (startDate == null) {
                $('#start_date-error').show();
                status = 0;
            }
            var endDate = $('input[name=end_date]').datepicker("getDate");
            if (endDate == null) {
                $('#end_date-error').show();
                status = 0;
            }
            
            if (status == 0) {
                return false;
            } 
            
            $('#btn_add_submit').button('loading');
            return true;
        }

        function checkUploadFileTimekeeping() {
            var status = 1;
            $('#check_upload_file_timekeeping').val(1);
            var fileTimekeeping = $('#file_timekeeping').val();
            if (fileTimekeeping == '') {
                $('#file_timekeeping-error').show();
                status = 0;
            }
            if (status == 0) {
                return false;
            }
            $('#btn_upload_file_timekeeping').button('loading');
            return true;
        }
        
        $('#btn_add_timekeeping_table').click(function() {
            $('#modal_add_timekeeping_table').modal('show');
            //setTimekeepingTableName();
        });

        $('.btn-show-modal-update').click(function() {
            var dataRow = $(this).closest('tr');
            $('#id_table_md_update').val(dataRow.attr('timekeeping-table-id'));
            $("#team_table_md_update").val(dataRow.find('.team_table').text());
            $("#month_table_md_update").val(dataRow.find('.month_table').text());
            $("#year_table_md_update").val(dataRow.find('.year_table').text());
            $("#start_table_md_update").val(dataRow.find('.dateStart_table').text());
            $("#end_table_md_update").val(dataRow.find('.dateEnd_table').text());
            $("#name_table_md_update").val(dataRow.find('.name_table').text());
            $('#modal_update_timekeeping_table').modal('show');
        });
        $(function() {
            $('.select-search-employee').selectSearchEmployee();
        });

        function checkSubmitTimekeepingTableTime() {
            var status = 1;
            $('#check_submit_update_time_timekeeping_table').val(1);
            var timekeepingTableName = $('#timekeeping_table_name').val();
            if (timekeepingTableName == '') {
                $('#timekeeping_table_name-error').show();
                status = 0;
            } else {
                if (timekeepingTableName.length > 255) {;
                    $('#timekeeping_table_name_length-error').show();
                    status = 0;
                }
            }
            var startDate = $('input[name=start_date]').datepicker("getDate");
            if (startDate == null) {
                $('#start_date-error').show();
                status = 0;
            }
            var endDate = $('input[name=end_date]').datepicker("getDate");
            if (endDate == null) {
                $('#end_date-error').show();
                status = 0;
            }
            
            if (status == 0) {
                return false;
            } 

            $('#btn_update_time_submit').button('loading');
            return true;
        }

        function checkSubmitTimekeepingTableRelated() {
            var status = 1;
            var startDate = $('#modal_get_data_timekeeping input[name=start_date]').val();
            var endDate = $('#modal_get_data_timekeeping input[name=end_date]').val();

            $('#start_date-error-related').hide();
            $('#end_date-error-related').hide();
            $('#error-related').hide();
            if (startDate == '') {
                $('#start_date-error-related').show();
                status = 0;
            }
            if (endDate == '') {
                $('#end_date-error-related').show();
                status = 0;
            }
            if (startDate != '' && endDate != '' &&
                new Date(moment(startDate, 'DD-MM-YYYY')).getTime() > new Date(moment(endDate, 'DD-MM-YYYY')).getTime()) {
                $('#error-related').text('Ngày bắt đầu phải nhỏ hơn ngày kết thúc');
                $('#error-related').show();
                status = 0;
            }
            var startDateTbl = moment($('#modal_get_data_timekeeping input[name=dateStartTable]').val(), 'DD-MM-YYYY');
            var endDateTbl = moment($('#modal_get_data_timekeeping input[name=dateEndTable]').val(), 'DD-MM-YYYY');
            if (startDate != '' && endDate != '' &&
                ((new Date(startDateTbl).getTime() > new Date(moment(startDate, 'DD-MM-YYYY')).getTime()) ||
                    (new Date(moment(endDate, 'DD-MM-YYYY')).getTime() > new Date(endDateTbl).getTime()))) {
                $('#error-related').text('Cập nhật dữ liệu liên quan trong khoảng từ: '
                    + $('#modal_get_data_timekeeping input[name=dateStartTable]').val()
                    + ' đến: '
                    + $('#modal_get_data_timekeeping input[name=dateEndTable]').val());
                $('#error-related').show();
                status = 0;
            }

            if (status == 0) {
                return false;
            } 
            $('#modal_btn_timekeeping_related').button('loading');
            return true;
        }
    </script>
    <script>
        $('.btn-show-modal-lock_up').click(function() {
            var data = $(this).closest('tr');
            $('#modal_lock_up .timekeeping_table_id_data').val(data.attr('timekeeping-table-id'));
            if ($(this).attr('value') == 1) {
                $('#modal_lock_up .modal-title').text("<?php echo trans('manage_time::view.Close lock timekeeping') ?>");
            } else {
                $('#modal_lock_up .modal-title').text("<?php echo trans('manage_time::view.Open lock timekeeping') ?>");
            }
            $('#modal_lock_up').modal('show');
        });
    </script>
@endsection

