@extends('layouts.default')

@section('title')
    {{trans('manage_time::view.Salary table list')}}
@endsection

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\Team\View\TeamList;
    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Team\Model\Employee;
    use Rikkei\ManageTime\View\ManageTimeCommon;
    use Rikkei\ManageTime\Model\SalaryTable;

    $currentYear = Carbon::now()->year;
    $monthsOfYear = ManageTimeCommon::getMonths();
    $teamsOptionAll = TeamList::toOption(null, true, false);
    $tblSalaryTable = SalaryTable::getTableName();
    $tblEmployee = Employee::getTableName();
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
            <button id="btn_add_salary_table" class="btn btn-success" data-toggle="modal" data-target="#modal_add_salary_table"><i class="fa fa-plus" aria-hidden="true"></i> {{ trans('manage_time::view.Add new') }}</button>
            <div class="pull-right">   
                @include('manage_time::include.filter')
            </div>
        </div>
        <!-- /.box-header -->

        <div class="box-body no-padding"> 
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table-control">
                    <thead>
                        <tr>
                            <th class="managetime-col-25" style="min-width: 25px;">{{ trans('manage_time::view.No.') }}</th>
                            <th class="managetime-col-120 sorting {{ TeamConfig::getDirClass('salary_table_name') }}" data-order="salary_table_name" data-dir="{{ TeamConfig::getDirOrder('salary_table_name') }}" style="min-width: 120px; max-width: 120px;">{{ trans('manage_time::view.Salary table name') }}</th>
                            <th class="managetime-col-80 sorting {{ TeamConfig::getDirClass('team_name') }}" data-order="team_name" data-dir="{{ TeamConfig::getDirOrder('team_name') }}" style="min-width: 80px; max-width: 80px;">{{ trans('manage_time::view.Team') }}</th>
                            <th class="managetime-col-40 sorting {{ TeamConfig::getDirClass('month') }}" data-order="month" data-dir="{{ TeamConfig::getDirOrder('month') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Month') }}</th>
                            <th class="managetime-col-40 sorting {{ TeamConfig::getDirClass('year') }}" data-order="year" data-dir="{{ TeamConfig::getDirOrder('year') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Year') }}</th>
                            <th class="managetime-col-60 sorting {{ TeamConfig::getDirClass('start_date') }}" data-order="start_date" data-dir="{{ TeamConfig::getDirOrder('start_date') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.From date') }}</th>
                            <th class="managetime-col-60 sorting {{ TeamConfig::getDirClass('end_date') }}" data-order="end_date" data-dir="{{ TeamConfig::getDirOrder('end_date') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.End date') }}</th>
                            <th class="managetime-col-80 sorting {{ TeamConfig::getDirClass('creator_name') }}" data-order="creator_name" data-dir="{{ TeamConfig::getDirOrder('creator_name') }}" style="min-width: 80px; max-width: 80px;">{{ trans('manage_time::view.Creator') }}</th>
                            <th class="managetime-col-40" style="min-width: 40px; max-width: 40px;">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tblSalaryTable }}.salary_table_name]" value='{{ CoreForm::getFilterData("{$tblSalaryTable}.salary_table_name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $monthFilter = CoreForm::getFilterData('number', "$tblSalaryTable.month");
                                            $monthsOfYearTypeNum = ManageTimeCommon::getMonths(true);
                                        ?>
                                        <select name="filter[number][{{ $tblSalaryTable }}.month]" class="form-control select-grid filter-grid select-search" autocomplete="off" style="width: 100%;">
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
                                            $yearFilter = CoreForm::getFilterData('number', "$tblSalaryTable.year");
                                        ?>
                                        <input type="text" name="filter[number][{{ $tblSalaryTable }}.year]" value='{{ $yearFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $startDateFilter = CoreForm::getFilterData('except', "$tblSalaryTable.start_date");
                                        ?>
                                        <input type="text" name="filter[except][{{ $tblSalaryTable }}.start_date]" value='{{ $startDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $endDateFilter = CoreForm::getFilterData('except', "$tblSalaryTable.end_date");
                                        ?>
                                        <input type="text" name="filter[except][{{ $tblSalaryTable }}.end_date]" value='{{ $endDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
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
                                <tr slary-table-id="{{ $item->salary_table_id }}" slary-table-name="{{ $item->salary_table_name }}" timekeeping-table-id="{{ $item->timekeeping_table_id }}">
                                    <td>{{ $i }}</td>
                                    <td><a href="{{ route('manage_time::timekeeping.salary.salary-table-detail', ['id' => $item->salary_table_id]) }}" target="_blank">{{ $item->salary_table_name }}</a></td>
                                    <td>{{ $item->team_name }}</td>
                                    <td>{{ $item->month }}</td>
                                    <td>{{ $item->year }}</td>
                                    <td>{{ Carbon::parse($item->start_date)->format('d-m-Y') }}</td>
                                    <td>{{ Carbon::parse($item->end_date)->format('d-m-Y') }}</td>
                                    <td>{{ $item->creator_name }}</td>
                                    <td class="align-center">
                                        <button class="btn btn-primary btn-show-modal-upload" title="{{ trans('manage_time::view.Import salary table') }}">
                                            <i class="fa fa-upload"></i>
                                        </button>
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

        <!-- Modal add salary table -->
        <div id="modal_add_salary_table" class="modal fade" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" style="font-size: 24px;">{{ trans('manage_time::view.Create new salary table') }}</h4>
                    </div>
                    <form method="POST" action="{{ route('manage_time::timekeeping.salary.save-salary-table') }}" autocomplete="off" id="form_add_salary_table">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="control-label required">{{ trans('manage_time::view.Salary table name') }} <em>*</em></label>
                                <div class="input-box">
                                    <input type="text" name="salary_table_name" class="form-control" id="salary_table_name" />
                                </div>
                                <label id="salary_table_name-error" class="managetime-error" for="salary_table_name">{{ trans('manage_time::view.This field is required') }}</label>
                                <label id="salary_table_name_length-error" class="managetime-error" for="salary_table_name">{{ trans('manage_time::view.This field not be greater than 255 characters') }}</label>
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
                                        <select class="form-control select-search" name="month" id="month" style="width: 100%">
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
                                    <div class='input-group date' id='datetimepicker_start_date'>
                                        <span class="input-group-addon managetime-icon-date">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type='text' class="form-control managetime-date" name="start_date" placeholder="dd-mm-yyyy" />
                                    </div>
                                    <label id="start_date-error" class="managetime-error" for="start_date">{{ trans('manage_time::view.This field is required') }}</label>
                                    <label id="start_date_after_end_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.The start date at must be before end date') }}</label>
                                </div>
                                <div class="col-sm-6 managetime-form-group">
                                    <label class="control-label required">{{ trans('manage_time::view.End date') }} <em>*</em></label>
                                    <div class='input-group date' id='datetimepicker_end_date'>
                                        <span class="input-group-addon managetime-icon-date">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type='text' class="form-control managetime-date" name="end_date" placeholder="dd-mm-yyyy" />
                                    </div>
                                    <label id="end_date-error" class="managetime-error" for="end_date">{{ trans('core::view.This field is required') }}</label>
                                    <label id="end_date_before_start_date-error" class="managetime-error" for="end_date">{{ trans('manage_time::view.The end date at must be after start date') }}</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">{{ trans('manage_time::view.Timekeeping table') }} <em>*</em></label>
                                <div class="input-box">
                                    <select class="form-control select-search" name="timekeeping_table_id" id="timekeeping_table_id" style="width: 100%">
                                        <option value="">&nbsp;</option>
                                        @if (isset($timekeepingTablesList) && count($timekeepingTablesList))
                                            @foreach ($timekeepingTablesList as $item)
                                                <option value="{{ $item->timekeeping_table_id }}">{{ $item->timekeeping_table_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <label id="timekeeping_table_id-error" class="managetime-error" for="timekeeping_table_id">{{ trans('manage_time::view.This field is required') }}</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                            <button type="submit" class="btn btn-primary" id="btn_add_submit" onclick="return checkSubmitSalaryTable();"><i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Save') }}</button>
                            <input type="hidden" id="check_submit_add_salary_table" name="" value="0">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal upload salary table -->
        <div id="modal_upload_salary_table" class="modal fade" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" style="font-size: 24px;">{{ trans('manage_time::view.Import salary table') }}</h4>
                    </div>
                    <form method="POST" action="{{ route('manage_time::timekeeping.salary.upload-salary-table') }}" enctype="multipart/form-data" id="form_updload_salary_table">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="control-label">{{ trans('manage_time::view.Salary table name') }}</label>
                                <div class="input-box">
                                    <input type="text" id="upload_salary_table_name" name="salary_table_name" class="form-control" readonly />
                                    <input type="hidden" id="upload_salary_table_id" name="salary_table_id" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">{{ trans('manage_time::view.Salary table (excel)') }} <em>*</em></label>
                                <div class="input-box">
                                    <input class="form-control" type="file" name="file" id="file_salary_table" />
                                </div>
                                <label id="file_salary_table-error" class="managetime-error" for="file">{{ trans('manage_time::view.This field is required') }}</label>
                            </div>

                            <div class="form-group">
                                <img src="{{ URL::asset('asset_managetime/images/template/bang_luong.png') }}" class="img-responsive" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                            <button type="submit" class="btn-add" id="btn_upload_salary_table" onclick="return checkUploadSalaryTable();" data-loading-text="<i class='fa fa-spin fa-refresh'></i> {{ trans('manage_time::view.Upload') }}"><i class="fa fa-upload"></i> {{ trans('manage_time::view.Upload') }}</button>
                            <input type="hidden" id="check_upload_salary_table" name="" value="0">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            selectSearchReload();

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

            var optionDatePicker = {
                autoclose: true,
                format: 'dd-mm-yyyy',
                weekStart: 1,
                todayHighlight: true
            };

            $('#datetimepicker_start_date').datepicker(optionDatePicker).on('changeDate', function(selected) {
                $('#start_date-error').hide();
                var startDate = $('#datetimepicker_start_date').datepicker("getDate");
                var endDate = $('#datetimepicker_end_date').datepicker("getDate");

                if (startDate != null && endDate != null) {
                    var startDateMS = startDate.getTime();
                    var endDateMS = endDate.getTime();
                    checkSubmit = $('#check_submit_add_salary_table').val();
                    if (checkSubmit == 1) {
                        if (startDateMS > endDateMS) {
                            $('#start_date_after_end_date-error').show();
                        } else {
                            $('#start_date_after_end_date-error').hide();
                            $('#end_date_before_start_date-error').hide();
                        }
                    }
                }
            }).on('clearDate', function() {
                checkSubmit = $('#check_submit_add_salary_table').val();
                if (checkSubmit == 1) {
                    $('#start_date-error').show();
                    $('#start_date_after_end_date-error').hide();
                    $('#end_date_before_start_date-error').hide();
                }
            });

            $('#datetimepicker_end_date').datepicker(optionDatePicker).on('changeDate', function(selected) {
                $('#end_date-error').hide();
                var startDate = $('#datetimepicker_start_date').datepicker("getDate");
                var endDate = $('#datetimepicker_end_date').datepicker("getDate");

                if (startDate != null && endDate != null) {
                    var startDateMS = startDate.getTime();
                    var endDateMS = endDate.getTime();
                    checkSubmit = $('#check_submit_add_salary_table').val();
                    if (checkSubmit == 1) {
                        if (startDateMS > endDateMS) {
                            $('#end_date_before_start_date-error').show();
                        } else {
                            $('#end_date_before_start_date-error').hide();
                            $('#start_date_after_end_date-error').hide();
                        }
                    }
                }
            }).on('clearDate', function() {
                checkSubmit = $('#check_submit_add_salary_table').val();
                if (checkSubmit == 1) {
                    $('#end_date-error').show();
                    $('#end_date_before_start_date-error').hide();
                    $('#start_date_after_end_date-error').hide();
                }
            });

            $('#btn_add_salary_table').click(function() {
                $('#check_submit_add_salary_table').val(0);
                $('#salary_table_name-error').hide();
                $('#salary_table_name_length-error').hide();
                $('#salary_table_name').val('');
                $('#team_id-error').hide();
                $('#month-error').hide();
                $('#year-error').hide();
                $('#year_digit-error').hide();
                $('#year').val('{{ $currentYear }}');
                $('#start_date-error').hide();
                $('#start_date_after_end_date-error').hide();
                $('#datetimepicker_start_date').datepicker('setDate', null);
                $('#end_date-error').hide();
                $('#end_date_before_start_date-error').hide();
                $('#datetimepicker_end_date').datepicker('setDate', null);
                $("#month").select2("val", "1");
                $('#timekeeping_table_id-error').hide();
                $("#timekeeping_table_id").val(null).trigger('change');
            });

            $('#salary_table_name').keyup(function() {
                var checkSubmit = $('#check_submit_add_salary_table').val();
                if (checkSubmit == 1) {
                    var salaryTableName = $('#salary_table_name').val().trim();
                    if (salaryTableName == '') {
                        $('#salary_table_name_length-error').hide();
                        $('#salary_table_name-error').show();
                    } else {
                        $('#salary_table_name-error').hide();
                        if (salaryTableName.length > 255) {;
                            $('#salary_table_name_length-error').show();
                        } else {
                            $('#salary_table_name_length-error').hide();
                        }
                    }
                }
            });

            $('#year').keyup(function() {
                var checkSubmit = $('#check_submit_add_salary_table').val().trim();
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
            });

            $('#year').keypress(function (e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
            });

            $('#timekeeping_table_id').on('select2:select', function (e) {
                var checkSubmit = $('#check_submit_add_salary_table').val();
                if (checkSubmit == 1) {
                    var timekeepingTableId = $('#timekeeping_table_id').val().trim();
                    if (timekeepingTableId == '' || timekeepingTableId == null) {
                        $('#timekeeping_table_id-error').show();
                    } else {
                        $('#timekeeping_table_id-error').hide();
                    }
                }
            });

            $('.btn-show-modal-upload').click(function() {
                var data = $(this).closest('tr');
                $('#upload_salary_table_name').val(data.attr('slary-table-name'));
                $('#upload_salary_table_id').val(data.attr('slary-table-id'));
                $('#modal_upload_salary_table').modal('show');
                $('#file_salary_table-error').hide();
                $('#file_salary_table').val('');
                $('#check_upload_salary_table').val(0);
            });

            $('#file_salary_table').change(function() {
                var checkSubmit = $('#check_upload_salary_table').val();
                if (checkSubmit == 1) {
                    var fileSalaryTable = $('#file_salary_table').val().trim();
                    if (fileSalaryTable == '') {
                        $('#file_salary_table-error').show();
                    } else {
                        $('#file_salary_table-error').hide();
                    }
                }
            });
        });

        function checkSubmitSalaryTable() {
            var status = 1;
            $('#check_submit_add_salary_table').val(1);
            var salaryTableName = $('#salary_table_name').val();
            if (salaryTableName == '') {
                $('#salary_table_name-error').show();
                status = 0;
            } else {
                if (salaryTableName.length > 255) {;
                    $('#salary_table_name_length-error').show();
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
            } else {
                if (year < 0) {
                    $('#year_digit-error').show();
                    status = 0;
                }
            }
            var startDate = $('#datetimepicker_start_date').datepicker("getDate");
            if (startDate == null) {
                $('#start_date-error').show();
                status = 0;
            }
            var endDate = $('#datetimepicker_end_date').datepicker("getDate");
            if (endDate == null) {
                $('#end_date-error').show();
                status = 0;
            }
            if (startDate != null && endDate != null) {
                var startDateMS = startDate.getTime();
                var endDateMS = endDate.getTime();
                if (startDateMS > endDateMS) {
                    $('#end_date_before_start_date-error').show();
                    status = 0;
                }
            }
            var timekeepingTableId = $('#timekeeping_table_id').val();
            if (timekeepingTableId == null || timekeepingTableId == '') {
                $('#timekeeping_table_id-error').show();
                status = 0;
            }

            if (status == 0) {
                return false;
            } 
            
            $('#btn_add_submit').button('loading');
            return true;
        }

        function checkUploadSalaryTable() {
            var status = 1;
            $('#check_upload_salary_table').val(1);
            var fileSalaryTable = $('#file_salary_table').val();
            if (fileSalaryTable == '') {
                $('#file_salary_table-error').show();
                status = 0;
            }
            if (status == 0) {
                return false;
            }
            $('#btn_upload_salary_table').button('loading');
            return true;
        }
    </script>
@endsection

