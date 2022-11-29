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

    $teamsOptionAll = TeamList::toOption(null, true, false);
    $tblEmployee = Employee::getTableName();
    $tblTeamMember = TeamMember::getTableName();
    $totalCol = count($datesTimekeeping);
?>

<div class="box-body no-padding">
    <div id="top_fixed_head">
    </div>
    <div class="pdh-10 tbl_container" id="me_table_container">
        <div class="table-left">
            <table id="_me_tbl_left" class="table dataTable table-bordered table-grid-data table-th-middle">
                <thead class="managetime-thead">
                    <tr>
                        <th class="col-title managetime-col-15"></th>
                        <th class="col-title managetime-col-50">{{ trans('manage_time::view.Timekeeping code') }}</th>
                        <th class="col-title managetime-col-80">{{ trans('manage_time::view.Employee code acronym') }}</th>
                        <th class="col-title managetime-col-120">{{ trans('manage_time::view.Full name') }}</th>
                        <th class="col-title managetime-col-135">{{ trans('manage_time::view.Department') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="filter-input-grid">
                        <td>&nbsp;</td>
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                        $filterEmployeeCard = Form::getFilterData('number', "{$tblEmployee}.employee_card_id");
                                    ?>
                                    <input type="text" name="filter[number][{{ $tblEmployee }}.employee_card_id]" value='{{ $filterEmployeeCard }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </div>
                        </td>
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
                                <td>{{ $item->employee_card_id }}</td>
                                <td>{{ $item->employee_code }}</td>
                                <td>{{ $item->employee_name }}</td>
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
            <table class="table dataTable table-bordered table-grid-data managetime-table tbl-resize" id="_me_table">
                <thead class="managetime-thead">
                    <tr>
                        @if (isset($datesTimekeeping) && count($datesTimekeeping))
                            @foreach ($datesTimekeeping as $column => $date)
                                <th class="col-title managetime-col-35 align-center" data-col="{{ $column }}" data-weekend="{{ ManageTimeCommon::isWeekend($date, $compensationDays) ? 'true' : 'false' }}">{!! ManageTimeCommon::getDayOfWeek($date) !!}</th>
                            @endforeach
                        @endif
                    </tr>
                </thead>
                <tbody id="position_start_header_fixed">
                    <tr class="filter-input-grid">
                        @for($i = 1; $i <= $totalCol; $i++)
                        <td style="background: #f5f5f5">&nbsp;</td>
                        @endfor
                    </tr>
                    @if (isset($collectionModel) && count($collectionModel))
                        @foreach ($collectionModel as $item)
                            @php
                                $teamCodePrefix = $arrTeamPrefix[$item->employee_id];
                                $compensationDays = $arrCompensationDays[$teamCodePrefix];
                            @endphp
                            <tr>
                                @if (isset($datesTimekeeping) && count($datesTimekeeping))
                                    @foreach ($datesTimekeeping as $column => $date)
                                        <?php
                                            $dataItem = $dataKeeping[$item->employee_id][date('Y-m-d', strtotime($date))];
                                            $timekeepingSign = ManageTimeCommon::getTimekeepingSign($dataItem, $teamCodePrefix ,$compensationDays, $arrHolidays[$teamCodePrefix]);
                                        ?>
                                        <td data-col="{{ $column }}">{{ $timekeepingSign[0] }}</td>
                                    @endforeach
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr class="no-result">
                            <td colspan="4" class="text-center">
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