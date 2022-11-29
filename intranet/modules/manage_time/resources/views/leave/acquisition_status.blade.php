@extends('manage_time::layout.common_layout')

@section('title-common')
{{ trans('manage_time::view.Annual paid leave management book') }}
@endsection

<?php

    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Team\Model\Team;
    use Rikkei\Core\View\Form;
    use Rikkei\ManageTime\Model\LeaveDayRegister;

    $urlShowPopup = route('manage_time::profile.leave.view-popup-detail');
    $filterSearchDate = Form::getFilterData('search', 'search_date');
    $filterSearchYear = Form::getFilterData('search', 'search_year');
    $teamPath = Team::getTeamPath();
    $countListEmployee = (isset($listEmployee) && count($listEmployee)) ? count($listEmployee) : 0;
?>

@section('css-common')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" />
<style>
    .multi-select-style .multiselect-container {
        width: 350px;
    }
    .multi-select-style .multiselect-container .multiselect-item .input-group .multiselect-search{
        width: 100%;
    }
    .multi-select-style .multiselect-container .multiselect-item .input-group .input-group-btn{
        display: none;
    }
    .label-red {
        color: red;
    }
    #table_search {
        padding-left: 15px;
        padding-right: 15px;
    }
    #table_search thead>tr>th, td {
        border-bottom: none !important;
    }
    .radio-span {
        margin: 10px 6px 6px 0px;
    }
    .flex {
        display: flex;
    }
</style>
@endsection

@section('sidebar-common')
@include('manage_time::include.sidebar_leave')
@endsection

@section('content-common')
<!-- Box mission list -->
<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.Annual paid leave management book') }}</h3>
    </div>
    <!-- /.box-header -->

    <div class="box-body no-padding">
        <input type="hidden" name="pattern-option" value="{{ $pattern }}" />
        <div class="row">
            <div class="box-body">
                <table class="table table-striped dataTable table-hover table-grid-data" id="table_search">
                    <thead>
                        <tr>
                            <th>{{ trans('manage_time::view.Department book') }}</th>
                            <th>
                                {{-- show team available --}}
                                @if (is_object($teamIdsAvailable))
                                    <span>{{ trim($teamIdsAvailable->name) }}</span>
                                @elseif ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                                    <div class="input-box filter-multi-select multi-select-style btn-select-team">
                                        <select name="filter[search][team_ids][]" id="select-team-member" multiple
                                                class="form-control filter-grid multi-select-bst select-multi"
                                                autocomplete="off">
                                            {{-- show team available --}}
                                            @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                                @foreach($teamsOptionAll as $option)
                                                    @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                                                        <option value="{{ $option['value'] }}" class="checkbox-item"
                                                                {{ in_array($option['value'], array_map("trim", explode(",", $teamIdCurrent))) ? 'selected' : '' }}<?php
                                                                if ($teamIdsAvailable === true):
                                                                elseif (! in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else:
                                                            ?>{{ $option['option'] }}<?php endif; ?>>{{ $option['label'] }}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                @endif
                                {{-- end show team available --}}
                            </th>
                        </tr>
                        <tr>
                            <th>{{ trans('manage_time::view.Display pattern') }}</th>
                            <th class="flex">
                                <input type="radio" name="pattern" value="1" id="radio_1" class="option" {{ $pattern == '1' ? 'checked' : null }}><span class="radio-span"> {{ trans('manage_time::view.Reference day') }}</span>
                                <input type="radio" name="pattern" value="2" id="radio_2" class="option" {{ $pattern == '2' ? 'checked' : null }}}}><span class="radio-span"> {{ trans('manage_time::view.Management year') }}</span>
                                <div class="reference-day">
                                    <div class='input-group date' id='datetimepicker-search-date'>
                                        <span class="input-group-addon managetime-icon-date" id="managetime-icon-search-date">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type='text' class="filter-grid form-control date search_date" name="filter[search][search_date]" id="search_date" autocomplete="off" value="{{ $searchDate }}" />
                                    </div>
                                </div>     
                                <div class="management-year">
                                    <div class='input-group date' id='datetimepicker-search-year'>
                                        <span class="input-group-addon managetime-icon-date" id="managetime-icon-search-year">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type='text' class="filter-grid form-control date search_year" name="filter[search][search_year]" id="search_year" autocomplete="off" value="{{ $searchYear }}"/>
                                    </div>
                                </div>                               
                            </th>
                            <th>
                                {{ trans('manage_time::view.Get less than 5 days') }}
                            </th>
                            <th>
                                <input type='checkbox' class="filter-grid" name="filter[search][is_less_than_5_day]" id="is_less_than_5_day" {{ $filterSearchLessThan5Day ? 'checked' : null }} />
                            </th>
                            <th>
                                <button class="btn btn-primary btn-search-filter">
                                    {{ trans('manage_time::view.Search') }}
                                </button>
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="col-sm-5">
                </div>
                <div class="col-sm-7">
                    <table class="table table-bordered table-hover">
                        <tr>
                            <th>{{ trans('manage_time::view.Total grant for this term') }}</th>
                            <td>{{ number_format($allTotalDay, 2) }}</td>
                            <th>{{ trans('manage_time::view.Total acquisition for this term') }}</th>
                            <td>{{ number_format($allTotalNumDayOffApproved, 2) }}</td>
                            <th>{{ trans('manage_time::view.Paid leave acquisition rate') }}</th>
                            <td>{{ $allTotalDay != 0 ? number_format($allTotalNumDayOffApproved / $allTotalDay * 100, 2) : '0' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box-body table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="managetime_table_fixed">
                        <thead>
                            <tr>
                                <th class="sorting col-no">No</th>
                                <th class="sorting col-employee-code">従業者のコード</th>
                                <th class="sorting col-employee-name">{{ trans('manage_time::view.Name') }}</th>
                                <th class="sorting col-department">{{ trans('manage_time::view.Department book') }}</th>
                                <th class="sorting col-last-grant">{{ trans('manage_time::view.Last grant date') }}</th>
                                <th class="sorting col-next-grant">{{ trans('manage_time::view.Next grant date') }}</th>
                                <th class="sorting col-previous-term">{{ trans('manage_time::view.Number of days carried forward from the previous term') }}</th>
                                <th class="sorting col-granted-term">{{ trans('manage_time::view.Number of days granted this term') }}</th>
                                <th class="sorting col-acquired-term">{{ trans('manage_time::view.Number of days acquired this term') }}</th>
                                <th class="sorting col-days-left">{{ trans('manage_time::view.Number of paid vacation days left') }}</th>
                                <th class="sorting col-days-acquired">{{ trans('manage_time::view.Number of days to be acquired') }}</th>
                                <th class="sorting col-export">印刷用表示</th>
                            </tr>
                        </thead>
                    </table>
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="managetime_table_primary">
                        <thead class="managetime-thead">
                            <tr>
                                <th class="sorting col-no">No</th>
                                <th class="sorting col-employee-code">従業者のコード</th>
                                <th class="sorting col-employee-name">{{ trans('manage_time::view.Name') }}</th>
                                <th class="sorting col-department">{{ trans('manage_time::view.Department book') }}</th>
                                <th class="sorting col-last-grant">{{ trans('manage_time::view.Last grant date') }}</th>
                                <th class="sorting col-next-grant">{{ trans('manage_time::view.Next grant date') }}</th>
                                <th class="sorting col-previous-term">{{ trans('manage_time::view.Number of days carried forward from the previous term') }}</th>
                                <th class="sorting col-granted-term">{{ trans('manage_time::view.Number of days granted this term') }}</th>
                                <th class="sorting col-acquired-term">{{ trans('manage_time::view.Number of days acquired this term') }}</th>
                                <th class="sorting col-days-left">{{ trans('manage_time::view.Number of paid vacation days left') }}</th>
                                <th class="sorting col-days-acquired">{{ trans('manage_time::view.Number of days to be acquired') }}</th>
                                <th class="sorting col-export">印刷用表示</th>
                            </tr>
                        </thead>
                        <thead>
                            <tr>
                                <td>&nbsp;</td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[search][employee_code]" value="{{ $employeeCode }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[search][employee_name]" value="{{ $employeeName }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="min-width: 95px;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" class="filter-grid form-control" name="filter[search][search_grant_date]" value="{{ $searchGrantDate }}" placeholder="{{ trans('manage_time::view.Search') }}..." autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        </thead>
                        <tbody id="position_start_header_fixed">
                            @if(isset($listEmployee) && count($listEmployee))
                            <?php $i = 1; ?>
                            @foreach($listEmployee as $item)
                                <?php 
                                    $day_last_transfer = 0;
                                    $day_current_year = 0;
                                    $day_seniority = 0;
                                    $tdClass = '';
                                    if ($item->leave_day_baseline) {
                                        $day_last_transfer = $item->leave_day_baseline['day_last_transfer'];
                                        $day_current_year = $item->leave_day_baseline['day_current_year'];
                                        $day_seniority = $item->leave_day_baseline['day_seniority'];
                                    }
                                    if ($item->is_include_other_year) {
                                        $tdClass = 'label-red';
                                    }
                                ?>
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td class="show-popup">
                                        <a value="{{ $item->id }}" style="cursor: pointer;">{{ $item->employee_code }}</a>
                                    </td>
                                    <td class="show-popup">
                                        <a value="{{ $item->id }}" style="cursor: pointer;">{{ $item->name }}</a>
                                    </td>
                                    <td class="{{ $tdClass }}">{{ $item->team_name }}</td>
                                    <td class="{{ $tdClass }}">{{ $item->last_grant_date }}</td>
                                    <td class="{{ $tdClass }}">{{ ($item->leave_date && \Carbon\Carbon::parse($item->next_grant_date) >= \Carbon\Carbon::parse($item->leave_date))?  '退職' : \Carbon\Carbon::parse($item->next_grant_date)->addDays(1)->format('Y-m-d') }}</td>
                                    <td class="{{ $tdClass }}">{{ number_format($day_last_transfer, 2) }}</td>
                                    <td class="{{ $tdClass }}">{{ number_format($day_current_year + $day_seniority, 2) }}</td>
                                    <td class="{{ $tdClass }}">{{ number_format($item->total_num_day_off_approved, 2) }}</td>
                                    <td class="{{ $tdClass }}">{{ number_format($item->remain_day, 2) }}</td>
                                    <td class="{{ $tdClass }}">{{ number_format($item->total_num_day_off_unapproved, 2) }}</td>
                                    <td class="{{ $tdClass }}">
                                        <a class="btn btn-primary" href="{{ route('manage_time::profile.leave.acquisition-status-detail', ['id' => $item->id, 'date' => $pattern == 1 ? \Carbon\Carbon::parse($searchDate)->format('Y-m-d') : \Carbon\Carbon::parse($searchYear.'-12-31')->format('Y-m-d')]) }}">印刷用表示</a>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="12" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    <!-- View modal -->
                    <div class="modal fade in managetime-modal" id="modal_view">
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<!-- /. box -->
@endsection
@section('script-common')
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>

<script type="text/javascript">
    var urlShowPopup = '{{ $urlShowPopup }}';
    var teamPath = {!! json_encode($teamPath) !!};
    var countListEmployee = '{{ $countListEmployee }}';
    $(document).ready(function () {
        var pattern = $("input[name$='pattern-option']").val();
        if (pattern == 1) {
            $("#search_date").removeAttr('disabled');
            $("#search_year").attr('disabled','disabled');
            $("#radio_1").prop("checked", true);
            $(".management-year").hide();
            $(".reference-day").show();
        } else {
            $("#search_date").attr('disabled','disabled');
            $("#search_year").removeAttr('disabled');
            $("#radio_2").prop("checked", true);
            $(".management-year").show();
            $(".reference-day").hide();
        }
        $("input[name$='pattern']").click(function() {
            var pattern = $(this).val();
            if (pattern == 1) {
                $("#search_date").removeAttr('disabled');
                $("#search_year").attr('disabled','disabled');
                $(".management-year").hide();
                $(".reference-day").show();
            } else {
                $("#search_date").attr('disabled','disabled');
                $("#search_year").removeAttr('disabled');
                $(".management-year").show();
                $(".reference-day").hide();
            }
        });
        $(".show-popup a").click(function() {
            var searchDate = $("#search_date").val();
            var searchYear = $("#search_year").val();
            var pattern = $("input[name$='pattern-option']").val();
            if (pattern == 1) {
                searchYear = null;
            } else {
                searchDate = null;
            }
            $.ajax({
                type: "GET",
                data : { 
                    teamId: $("input[name$='filter[search][team_ids][]']").val(),
                    searchDate: searchDate,
                    searchYear: searchYear,
                    employeeId: $(this).attr('value'),
                },
                url: urlShowPopup,
                success: function (result) {
                    $('#modal_view').html(result);
                    $('#modal_view').modal('show');
                }
            });   
        });
        if (countListEmployee > 0) {
            $('#managetime_table_primary').DataTable({
                pagingType: 'simple_numbers',
                pageLength: 50,
                "searching": false,
                "language": {
                    "lengthMenu": "表示 _MENU_",
                    "zeroRecords": "データなし",
                    "info": "合計 _TOTAL_ 対象・ _PAGES_ ページ",
                    "infoEmpty": "データなし",
                    "infoFiltered": "",
                    "paginate": {
                        "next":       "<i class='fa fa-arrow-right'></i>",
                        "previous":   "<i class='fa fa-arrow-left'></i>"
                    },
                    "search": "探索 "
                }
            });
        }
    });
    $(function () {
        $('.select-multi').multiselect({
            numberDisplayed: 1,
            nonSelectedText: '--------------',
            allSelectedText: '{{ trans('project::view.All') }}',
            onDropdownHide: function(event) {
                RKfuncion.filterGrid.filterRequest(this.$select);
            }
        });
        $('.search_date').datepicker({
            format: 'yyyy/mm/dd',
            useCurrent: false,
            maxDate: moment().format('yyyy/mm/dd'),
        });
        $('.search_year').datetimepicker({
            format: 'YYYY',
            useCurrent: false,
            maxDate: moment().format('YYYY')
        });
    });

    $(document).on('mouseup', 'li.checkbox-item', function () {
        var domInput = $(this).find('input');
        var id = domInput.val();
        var isChecked = !domInput.is(':checked');
        if (teamPath[id] && typeof teamPath[id].child !== "undefined") {
            var teamChild = teamPath[id].child;
            $('li.checkbox-item input').map((i, el) => {
                if (teamChild.indexOf(parseInt($(el).val())) !== -1 && $(el).is(':checked') === !isChecked) {
                    $(el).click();
                }
            });
        }
        setTimeout(() => {
            changeLabelSelected();
        }, 0)
    });
    $(document).ready(function () {
        changeLabelSelected();
        $('.select-multi').multiselect({
            numberDisplayed: 1,
            nonSelectedText: '--------------',
            allSelectedText: '{{ trans('project::view.All') }}',
            onDropdownHide: function(event) {
                RKfuncion.filterGrid.filterRequest(this.$select);
            }
        });
    });

    function changeLabelSelected() {
        var checkedValue = $(".list-team-select-box option:selected");
        var title = '';
        if (checkedValue.length === 0) {
            $(".list-team-select-box .multiselect-selected-text").text('--------------');
        }
        if (checkedValue.length === 1) {
            $(".list-team-select-box .multiselect-selected-text").text($.trim(checkedValue.text()));
        }
        for (let i = 0; i < checkedValue.length; i++) {
            title += $.trim(checkedValue[i].label) + ', ';
        }
        $('.list-team-select-box button').prop('title', title.slice(0, -2))
    }
    var fixTop = $('#position_start_header_fixed').offset().top;
    $(window).scroll(function() {
        var scrollTop = $(window).scrollTop();
        if (scrollTop > fixTop) {
            $('#managetime_table_fixed').css('top', scrollTop - $('.table-responsive').offset().top + 40);
            $('#managetime_table_fixed').show();
        } else {
            $('#managetime_table_fixed').hide();
        }
        var widthColNo = $('#managetime_table_primary .col-no').width();
        $('#managetime_table_fixed .col-no').width(widthColNo);

        var widthColEmployeeCode = $('#managetime_table_primary .col-employee-code').width();
        $('#managetime_table_fixed .col-employee-code').width(widthColEmployeeCode);

        var widthColEmployeeName = $('#managetime_table_primary .col-employee-name').width();
        $('#managetime_table_fixed .col-employee-name').width(widthColEmployeeName);

        var widthColDepartment = $('#managetime_table_primary .col-department').width();
        $('#managetime_table_fixed .col-department').width(widthColDepartment);

        var widthColLastGrant = $('#managetime_table_primary .col-last-grant').width();
        $('#managetime_table_fixed .col-last-grant').width(widthColLastGrant);

        var widthColNextGrant = $('#managetime_table_primary .col-col-next-grant').width();
        $('#managetime_table_fixed .col-col-next-grant').width(widthColNextGrant);

        var widthColPreviousTerm = $('#managetime_table_primary .col-previous-term').width();
        $('#managetime_table_fixed .col-previous-term').width(widthColPreviousTerm);

        var widthColGrantedTerm = $('#managetime_table_primary .col-granted-term').width();
        $('#managetime_table_fixed .col-granted-term').width(widthColGrantedTerm);

        var widthColAcquiredTerm = $('#managetime_table_primary .col-acquired-term').width();
        $('#managetime_table_fixed .col-acquired-term').width(widthColAcquiredTerm);

        var widthColDaysLeft = $('#managetime_table_primary .col-days-left').width();
        $('#managetime_table_fixed .col-days-left').width(widthColDaysLeft);

        var widthColDaysAcquired = $('#managetime_table_primary .col-days-acquired').width();
        $('#managetime_table_fixed .col-days-acquired').width(widthColDaysAcquired);

        var widthColExport = $('#managetime_table_primary .col-export').width();
        $('#managetime_table_fixed .col-export').width(widthColExport);
    });
</script>
@endsection
