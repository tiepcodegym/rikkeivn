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
    <div  style="padding-left: 15px; font-size: 25px" class="hove-pointer">
        <div class="tab-hidden"><i class="fa fa-angle-double-left" aria-hidden="true"></i></div>
        <div class="tab-show hidden"><i class="fa fa-angle-double-right" aria-hidden="true"></i></div>
    </div>

    <div id="top_fixed_head">
    </div>
    <div class="pdh-10 tbl_container" id="me_table_container">
        <div class="table-left">
            <table id="_me_tbl_left" class="table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle">
                <thead class="managetime-thead">
                    <tr style="height: 165px;">
                        <th class="col-title managetime-col-15"></th>
                        <th class="col-title managetime-col-50 col-hidden">{{ trans('manage_time::view.Timekeeping code') }}</th>

                        <th class="col-title managetime-col-100 col-hidden">{{ trans('manage_time::view.Employee code') }}</th>

                        <th class="col-title managetime-col-120">{{ trans('manage_time::view.Full name') }}</th>

                        <th class="col-title managetime-col-200 col-hidden">{{ trans('manage_time::view.Department') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="filter-input-grid">
                        <td>&nbsp;</td>
                        <td class="col-hidden">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                        $filterEmployeeCard = Form::getFilterData('number', "{$tblEmployee}.employee_card_id");
                                    ?>
                                    <input type="text" name="filter[number][{{ $tblEmployee }}.employee_card_id]" value='{{ $filterEmployeeCard }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </div>
                        </td>
                        <td class="col-hidden">
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
                        <td class="col-hidden">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                        $teamFilter = Form::getFilterData('except', "{$tblTeamMember}.team_id");
                                    ?>
                                    <select style="width: 100%;" name="filter[except][{{ $tblTeamMember }}.team_id]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                        <option value="">&nbsp;</option>
                                        @foreach($teamsOptionAll as $option)
                                            <option value="{{ $option['value'] }}"<?php
                                                if ($option['value'] == $teamFilter): ?> selected<?php endif; 
                                                    ?>>{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @if (isset($collectionModel) && count($collectionModel))
                        @foreach ($collectionModel as $item)
                            <tr>
                                <td><input onclick="childCheck();" type="checkbox" class="check_emp" data-emp_id="{{ $item->employee_id }}" data-emp_code="{{ $item->employee_code }}" data-emp_name="{{ $item->employee_name }}" /></td>
                                <td class="col-hidden">{{ $item->employee_card_id }}</td>
                                <td class="col-hidden">{{ $item->employee_code }}</td>
                                <td>{{ $item->employee_name }}</td>
                                <td class="col-hidden">{{ $item->role_name }}</td>
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
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.The total of trial working days') }}</th>
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
                        @if ($isTeamCodeJapan)
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.M') }}</th>
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.S') }}</th>
                        @else
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.M1') }}</th>
                        @endif

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.OTKL') }}</th>
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.number days compensation') }}</th>
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.OT of official working') }}</th>
                        @if ($timeKeepingTable->type == getOptions::WORKING_OFFICIAL)
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.OT of trial working') }}</th>
                        @endif

                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.The total of official working days to salary') }}</th>

                        @if ($timeKeepingTable->type == getOptions::WORKING_OFFICIAL)
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.The total of trial working days to salary') }}</th>
                        @endif
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.Total basic salary') }}</th>
                        @if ($permissionTimeKeeping)
                        <th class="managetime-col-50"></th>
                        @endif
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
                            <?php
                            $isWorkOff = ManagetimeView::hasOffcialText($item->offcial_date, $timeKeepingTable->end_date);
                            $isWorkTri = ManagetimeView::hasTrialText($item->offcial_date, $timeKeepingTable->start_date);
                            $isWorkOffTrain = $isWorkOff && $isWorkTri;
                            ?>
                            <tr data-emp="{{ $item->employee_id }}">
                                @if ($timeKeepingTable->type == getOptions::WORKING_OFFICIAL)
                                <td>
                                    <span class="result-field">{{ number_format($item->total_official_working_days, 2) }}</span>
                                    <input type="text" data-field="total_official_working_days" class="edit-field form-control num hidden" />
                                    <input type="hidden" data-field="total_official_working_days" class="edit-field-hidden" value="{{ $item->total_official_working_days }}" />
                                </td>
                                @endif
                                <td>
                                    <span class="result-field">{{ number_format($item->total_trial_working_days, 2) }}</span>
                                    <input type="text" data-field="total_trial_working_days" class="edit-field num form-control hidden" />
                                    <input type="hidden" data-field="total_trial_working_days" class="edit-field-hidden" value="{{ $item->total_trial_working_days }}" />
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalOTWeekdays, 2) }}</span>
                                    @if ($isWorkOffTrain)
                                    <div class="arrow-left arrow-left-top hidden"></div>
                                    <div class="arrow-left arrow-left-bottom hidden"></div>
                                    <span class="note note-top hidden" >Chính thức</span>
                                    <span class="note note-bottom hidden" >Thử việc</span>
                                    @endif
                                    @if ($isWorkOff)
                                    <input type="hidden" data-field="total_official_ot_weekdays" class="edit-field-hidden" value="{{ $item->total_official_ot_weekdays }}" />
                                    <input type="text" data-field="total_official_ot_weekdays" class="edit-field num form-control hidden" />
                                    @endif
                                    @if ($isWorkTri)
                                    <input type="hidden" data-field="total_trial_ot_weekdays" class="edit-field-hidden" value="{{ $item->total_trial_ot_weekdays }}" />
                                    <input type="text" data-field="total_trial_ot_weekdays" class="edit-field num form-control hidden" />
                                    @endif
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalOTWeekends, 2) }}</span>
                                    @if ($isWorkOffTrain)
                                    <div class="arrow-left arrow-left-top hidden"></div>
                                    <div class="arrow-left arrow-left-bottom hidden"></div>
                                    <span class="note note-top hidden" >Chính thức</span>
                                    <span class="note note-bottom hidden" >Thử việc</span>
                                    @endif
                                    @if ($isWorkOff)
                                    <input type="hidden" data-field="total_official_ot_weekends" class="edit-field-hidden" value="{{ $item->total_official_ot_weekends }}" />
                                    <input type="text" data-field="total_official_ot_weekends" class="edit-field num form-control hidden" />
                                    @endif
                                    @if ($isWorkTri)
                                    <input type="hidden" data-field="total_trial_ot_weekends" class="edit-field-hidden" value="{{ $item->total_trial_ot_weekends }}" />
                                    <input type="text" data-field="total_trial_ot_weekends" class="edit-field num form-control hidden" />
                                    @endif
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalOTHolidays, 2) }}</span>
                                    @if ($isWorkOffTrain)
                                    <div class="arrow-left arrow-left-top hidden"></div>
                                    <div class="arrow-left arrow-left-bottom hidden"></div>
                                    <span class="note note-top hidden" >Chính thức</span>
                                    <span class="note note-bottom hidden" >Thử việc</span>
                                    @endif
                                    @if ($isWorkOff)
                                    <input type="hidden" data-field="total_official_ot_holidays" class="edit-field-hidden" value="{{ $item->total_official_ot_holidays }}" />
                                    <input type="text" data-field="total_official_ot_holidays" class="edit-field num form-control hidden" />
                                    @endif
                                    @if ($isWorkTri)
                                    <input type="hidden" data-field="total_trial_ot_holidays" class="edit-field-hidden" value="{{ $item->total_trial_ot_holidays }}" />
                                    <input type="text" data-field="total_trial_ot_holidays" class="edit-field num form-control hidden" />
                                    @endif
                                </td>
                                <td>
                                    <span class="result-field is-int ">{{ $item->total_number_late_in }}</span>
                                    <input type="text" data-field="total_number_late_in" class="edit-field num int form-control hidden" value="{{ $item->total_number_late_in }}" />
                                    <input type="hidden" data-field="total_number_late_in" class="edit-field-hidden" value="{{ $item->total_number_late_in }}" />
                                </td>
                                <td>
                                    <span class="result-field is-int ">{{ $item->total_number_early_out }}</span>
                                    <input type="text" data-field="total_number_early_out" class="edit-field num int form-control hidden" value="{{ $item->total_number_early_out }}" />
                                    <input type="hidden" data-field="total_number_early_out" class="edit-field-hidden" value="{{ $item->total_number_early_out }}" />
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalRegisterBusinessTrip, 2) }}</span>
                                    @if ($isWorkOffTrain)
                                    <div class="arrow-left arrow-left-top hidden"></div>
                                    <div class="arrow-left arrow-left-bottom hidden"></div>
                                    <span class="note note-top hidden" >Chính thức</span>
                                    <span class="note note-bottom hidden" >Thử việc</span>
                                    @endif
                                    @if ($isWorkOff)
                                    <input type="hidden" data-field="total_official_business_trip" class="edit-field-hidden" value="{{ $item->total_official_business_trip }}" />
                                    <input type="text" data-field="total_official_business_trip" class="edit-field num form-control hidden" />
                                    @endif
                                    @if ($isWorkTri)
                                    <input type="hidden" data-field="total_trial_business_trip" class="edit-field-hidden" value="{{ $item->total_trial_business_trip }}" />
                                    <input type="text" data-field="total_trial_business_trip" class="edit-field num form-control hidden" />
                                    @endif
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalLeaveDayHasSalary, 2) }}</span>
                                    @if ($isWorkOffTrain)
                                    <div class="arrow-left arrow-left-top hidden"></div>
                                    <div class="arrow-left arrow-left-bottom hidden"></div>
                                    <span class="note note-top hidden" >Chính thức</span>
                                    <span class="note note-bottom hidden" >Thử việc</span>
                                    @endif
                                    @if ($isWorkOff)
                                    <input type="hidden" data-field="total_official_leave_day_has_salary" class="edit-field-hidden" value="{{ $item->total_official_leave_day_has_salary }}" />
                                    <input type="text" data-field="total_official_leave_day_has_salary" class="edit-field num form-control hidden" />
                                    @endif
                                    @if ($isWorkTri)
                                    <input type="hidden" data-field="total_trial_leave_day_has_salary" class="edit-field-hidden" value="{{ $item->total_trial_leave_day_has_salary }}" />
                                    <input type="text" data-field="total_trial_leave_day_has_salary" class="edit-field num form-control hidden" />
                                    @endif
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalLeaveDayBasic, 2) }}</span>
                                    @if ($isWorkOffTrain)
                                    <div class="arrow-left arrow-left-top hidden"></div>
                                    <div class="arrow-left arrow-left-bottom hidden"></div>
                                    <span class="note note-top hidden" >Chính thức</span>
                                    <span class="note note-bottom hidden" >Thử việc</span>
                                    @endif
                                    @if ($isWorkOff)
                                    <input type="hidden" data-field="total_official_leave_basic_salary" class="edit-field-hidden" value="{{ $item->total_official_leave_basic_salary }}" />
                                    <input type="text" data-field="total_official_leave_basic_salary" class="edit-field num form-control hidden" />
                                    @endif
                                    @if ($isWorkTri)
                                    <input type="hidden" data-field="total_trial_leave_basic_salary" class="edit-field-hidden" value="{{ $item->total_trial_leave_basic_salary }}" />
                                    <input type="text" data-field="total_trial_leave_basic_salary" class="edit-field num form-control hidden" />
                                    @endif
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->total_leave_day_no_salary, 2) }}</span>
                                    <input type="text" data-field="total_leave_day_no_salary" class="edit-field num form-control hidden" value="{{ $item->total_leave_day_no_salary }}" />
                                    <input type="hidden" data-field="total_leave_day_no_salary" class="edit-field-hidden" value="{{ $item->total_leave_day_no_salary }}" />
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalRegisterSupplement, 2) }}</span>
                                    @if ($isWorkOffTrain)
                                    <div class="arrow-left arrow-left-top hidden"></div>
                                    <div class="arrow-left arrow-left-bottom hidden"></div>
                                    <span class="note note-top hidden" >Chính thức</span>
                                    <span class="note note-bottom hidden" >Thử việc</span>
                                    @endif
                                    @if ($isWorkOff)
                                    <input type="hidden" data-field="total_official_supplement" class="edit-field-hidden" value="{{ $item->total_official_supplement }}" />
                                    <input type="text" data-field="total_official_supplement" class="edit-field num form-control hidden" />
                                    @endif
                                    @if ($isWorkTri)
                                    <input type="hidden" data-field="total_trial_supplement" class="edit-field-hidden" value="{{ $item->total_trial_supplement }}" />
                                    <input type="text" data-field="total_trial_supplement" class="edit-field num form-control hidden" />
                                    @endif
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->totalHoliday, 2) }}</span>
                                    @if ($isWorkOffTrain)
                                    <div class="arrow-left arrow-left-top hidden"></div>
                                    <div class="arrow-left arrow-left-bottom hidden"></div>
                                    <span class="note note-top hidden" >Chính thức</span>
                                    <span class="note note-bottom hidden" >Thử việc</span>
                                    @endif
                                    @if ($isWorkOff)
                                    <input type="hidden" data-field="total_official_holiay" class="edit-field-hidden" value="{{ $item->total_official_holiay }}" />
                                    <input type="text" data-field="total_official_holiay" class="edit-field num form-control hidden" />
                                    @endif
                                    @if ($isWorkTri)
                                    <input type="hidden" data-field="total_trial_holiay" class="edit-field-hidden" value="{{ $item->total_trial_holiay }}" />
                                    <input type="text" data-field="total_trial_holiay" class="edit-field num form-control hidden" />
                                    @endif
                                </td>
                                @if ($isTeamCodeJapan)
                                <td>
                                    <span class="result-field">{{ $item->total_late_shift }}</span>
                                    <input type="hidden" data-field="total_late_start_shift" class="edit-field-hidden" value="{{ $item->total_late_start_shift }}" />
                                    <input type="hidden" data-field="total_late_mid_shift" class="edit-field-hidden" value="{{ $item->total_late_mid_shift }}" />
                                    <input type="text" data-field="total_late_start_shift" title="M1" class="edit-field num form-control hidden" />
                                    <input type="text" data-field="total_late_mid_shift" title="M2" class="edit-field num form-control hidden" />
                                </td>
                                <td>
                                    <span class="result-field">{{ $item->total_early_shift }}</span>
                                    <input type="hidden" data-field="total_early_mid_shift" class="edit-field-hidden" value="{{ $item->total_early_mid_shift }}" />
                                    <input type="text" data-field="total_early_mid_shift" title="S1" class="edit-field num form-control hidden" />
                                    <input type="hidden" data-field="total_early_end_shift" class="edit-field-hidden" value="{{ $item->total_early_end_shift }}" />
                                    <input type="text" data-field="total_early_end_shift" title="S2" class="edit-field num form-control hidden" />
                                </td>
                                @else
                                <td>
                                    <span class="result-field">{{ number_format($item->total_late_start_shift, 2) }}</span>
                                    <input type="hidden" data-field="total_late_start_shift" class="edit-field-hidden" value="{{ $item->total_late_start_shift }}" />
                                    <input type="text" data-field="total_late_start_shift" class="edit-field num form-control hidden" />
                                </td>
                                @endif
                                <td>
                                    <span class="result-field">{{ number_format($item->total_ot_no_salary, 2) }}</span>
                                    <input type="hidden" data-field="total_ot_no_salary" class="edit-field-hidden" value="{{ $item->total_ot_no_salary }}" />
                                    <input type="text" data-field="total_ot_no_salary" class="edit-field num form-control hidden" />
                                </td>
                                <td>
                                    <span class="result-field">{{ number_format($item->total_num_com, 2) }}</span>
                                    @if ($isWorkOffTrain)
                                    <div class="arrow-left arrow-left-top hidden"></div>
                                    <div class="arrow-left arrow-left-bottom hidden"></div>
                                    <span class="note note-top hidden" >Chính thức</span>
                                    <span class="note note-bottom hidden" >Thử việc</span>
                                    @endif
                                    @if ($isWorkOff)
                                    <input type="hidden" data-field="number_com_off" class="edit-field-hidden" value="{{ $item->number_com_off }}" />
                                    <input type="text" data-field="number_com_off" class="edit-field num neg form-control hidden" />
                                    @endif
                                    @if ($isWorkTri)
                                    <input type="hidden" data-field="number_com_tri" class="edit-field-hidden" value="{{ $item->number_com_tri }}" />
                                    <input type="text" data-field="number_com_tri" class="edit-field num neg form-control hidden" />
                                    @endif
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

                                @if ($permissionTimeKeeping)
                                <td>
                                    <button class="btn btn-primary btn-edit-row">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-primary btn-save-row hidden" data-url="{{ route('manage_time::timekeeping.saveRowKeeping') }}">
                                        <i class="fa fa-save"></i>
                                        <i class="fa fa-refresh fa-spin hidden"></i>
                                    </button>
                                    <button class="btn btn-danger btn-cancel-row hidden">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                </td>
                                @endif
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