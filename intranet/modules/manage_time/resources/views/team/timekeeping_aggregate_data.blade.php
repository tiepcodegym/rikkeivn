<?php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View;
    use Rikkei\Core\View\Form;
    use Rikkei\Team\View\Config;
    use Rikkei\Team\View\TeamList;
    use Rikkei\Team\Model\Employee;
    use Rikkei\Team\Model\TeamMember;
    use Rikkei\ManageTime\Model\Timekeeping;
    use Rikkei\ManageTime\View\ManageTimeCommon;
    use Rikkei\ManageTime\View\ManageTimeConst;
    use Rikkei\ManageTime\View\View as ManageTimeView;
    use Rikkei\ManageTime\Model\TimekeepingAggregate;
    use Rikkei\Resource\View\getOptions;
    
    $teamsOptionAll = TeamList::toOption(null, true, false);
    $tblEmployee = Employee::getTableName();
    $tblTeamMember = TeamMember::getTableName();
    $aggregateTbl = TimekeepingAggregate::getTableName();
?>

<div class="box-body no-padding">
    <div id="top_fixed_head">
    </div>
    <div class="pdh-10 tbl_container" id="me_table_container" style="padding-left: 431px">
        <div class="table-left" style="width: 430px">
            <table id="_me_tbl_left" class="table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle">
                <thead class="managetime-thead">
                    <tr style="height: 165px;">
                        <th class="col-title managetime-col-100">{{ trans('manage_time::view.Employee code') }}</th>

                        <th class="col-title managetime-col-120">{{ trans('manage_time::view.Full name') }}</th>

                        <th class="col-title managetime-col-200">{{ trans('manage_time::view.Department') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="filter-input-grid">
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[{{ $tblEmployee }}.employee_code]" value='{{ Form::getFilterData("{$tblEmployee}.employee_code") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[{{ $tblEmployee }}.name]" value='{{ Form::getFilterData("{$tblEmployee}.name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                        $teamFilter = Form::getFilterData('except', "{$tblTeamMember}.team_id");
                                    ?>
                                    <select style="width: 100%;" name="filter[except][{{ $tblTeamMember }}.team_id]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                        <option value="">&nbsp;</option>
                                        @foreach($teamsOptionAll as $option)
                                            <option value="{{ $option['value'] }}"
                                                <?php
                                                    if ($option['value'] == $teamFilter) {
                                                        echo 'selected';
                                                    }
                                                    if (!in_array($option['value'], $teamIdAllow)) {
                                                        echo 'disabled';
                                                    }
                                                ?>
                                            >{{ $option['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @if (isset($collectionModel) && count($collectionModel))
                        @foreach ($collectionModel as $item)
                            <tr>
                                <td>{{ $item->employee_code }}</td>
                                <td><a href="{{route('manage_time::division.timekeeping', ['idTable' => $item->id, 'idEmp' => $item->employee_id]) }}">{{ $item->employee_name }}</a></td>
                                <td>{{ $item->role_name }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="no-result">
                            <td colspan="4" class="text-center">
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="table-responsive _me_table_responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table" id="_me_table">
                <thead class="managetime-thead">
                    <tr style="height: 165px;">
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.The total of official working days') }}</th>
                        @if ($timeKeepingTable->type == getOptions::WORKING_OFFICIAL)
                            <th class="col-title managetime-col-50">
                                {{ trans('manage_time::view.The total of trial working days') }}
                            </th>
                        @endif
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.Overtime on weekdays') }}</th>

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.Overtime on weekends') }}</th>

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.Overtime on holidays') }}</th>

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.Total number of late in') }}</th>

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.Total number of early out') }}</th>

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.CT') }}</th>

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.P') }}</th>

                        <th class="col-title managetime-col-50">LCB</th>

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.KL') }}</th>

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.BS') }}</th>

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.L') }}</th>
                        
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.M1') }}</th>

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.OTKL') }}</th>
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.number days compensation') }}</th>
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.OT of official working') }}</th>
                        @if ($timeKeepingTable->type == getOptions::WORKING_OFFICIAL)
                            <th class="col-title managetime-col-50">
                                {{ trans('manage_time::view.OT of trial working') }}
                            </th>
                        @endif
                        <th class="col-title managetime-col-50">
                            {{ trans('manage_time::view.The total of official working days to salary') }}
                        </th>
                        @if ($timeKeepingTable->type == getOptions::WORKING_OFFICIAL)
                            <th class="col-title managetime-col-50">
                                {{ trans('manage_time::view.The total of trial working days to salary') }}
                            </th>
                        @endif
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.Total basic salary') }}</th>
                    </tr>
                </thead>
                <tbody id="position_start_header_fixed">
                    <tr class="filter-input-grid">
                        @foreach($keyFilter as $index => $key)
                            @if (in_array($key, ['total_official_salary', 'total_official_working_days', 'total_official_ot']) && $timeKeepingTable->type == getOptions::WORKING_PARTTIME)
                                @php continue; @endphp
                            @endif
                            <td>
                                <div class="row">
                                    <div class="arrow-up hidden"></div>
                                    <div class="col-md-12 filter-group {{ $index == 0 || $index == 1 ? 'left-6' : '' }}">
                                        @include('manage_time::timekeeping.include.row_filter', ['key' => $key])
                                    </div>
                                </div>
                            </td>
                        @endforeach
                        <td>&nbsp;</td>
                    </tr>
                    @if (isset($collectionModel) && count($collectionModel))
                        @foreach ($collectionModel as $item)
                            <tr data-emp="{{ $item->employee_id }}">
                                @if ($timeKeepingTable->type == getOptions::WORKING_OFFICIAL)
                                <td>
                                    <span class="result-field">{{ number_format($item->total_official_working_days, 2) }}</span>
                                </td>
                                @endif
                                <td>
                                    <span class="result-field">{{ number_format($item->total_trial_working_days, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalOTWeekdays, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalOTWeekends, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalOTHolidays, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field is-int ">{{ $item->total_number_late_in }}</span>
                                </td>
                                <td>
                                    <span class="result-field is-int ">{{ $item->total_number_early_out }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalRegisterBusinessTrip, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalLeaveDayHasSalary, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalLeaveDayBasic, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->total_leave_day_no_salary, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalRegisterSupplement, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalHoliday, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->total_late_start_shift, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->total_ot_no_salary, 2) }}</span>
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->total_num_com, 2) }}</span>
                                </td>
                                
                                <td>
                                    <span data-field="total_ot_official" class="total-ot-official">{{ number_format($item->totalOTOfficial, 2) }}</span>
                                </td>
                                
                                @if ($timeKeepingTable->type == getOptions::WORKING_OFFICIAL)
                                    <td>
                                        <span data-field="total_ot_trial" class="total-ot-trial">{{ number_format($item->totalOTTrial, 2) }}</span>
                                    </td>
                                    <td>
                                        <span data-field="total_working_official_salary" class="total-working-official-salary">{{ number_format($item->total_working_officail, 2) }}</span>
                                    </td>
                                @endif
                                <td>
                                    <span data-field="total_working_trial_salary" class="total-working-trial-salary">{{ number_format($item->total_working_trial, 2) }}</span>
                                </td>
                                <td>
                                    <span data-field="total_official_leave_basic_salary" class="total_official_leave_basic_salary">{{ number_format($item->total_official_leave_basic_salary, 2) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="no-result">
                            <td colspan="19" class="text-center">
                                <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
</div>
<!-- /.box-body -->

<script type="text/javascript">
    // Count page
    totalPage = '{{ $collectionModel->lastPage() }}';
    pagerInfo = '{{trans("manage_time::view.Total :records entries / :pages page",["records" => $collectionModel->total(), "pages" => $collectionModel->lastPage()])}}';
</script>