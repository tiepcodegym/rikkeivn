@extends('manage_time::layout.manage_layout')

@section('title-manage')
    {{ trans('manage_time::view.Manage business trip') }} 
@endsection

<?php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth; 
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View;
    use Rikkei\Core\View\Form;
    use Rikkei\Team\View\Config;
    use Rikkei\ManageTime\View\ManageTimeCommon;
    use Rikkei\ManageTime\Model\BusinessTripRegister;
    use Rikkei\ManageTime\View\MissionPermission;
    use Rikkei\ManageTime\View\View as ManageTimeView;

    $teamsOptionAll = \Rikkei\Team\View\TeamList::toOption(null, true, false);

    $registerTable = "bus_reg";
    $EmployeeTable = 'tblEmp';
    $employeeApproveTable = 'employee_table_for_approver';
    $roleTableAs = 'role_table';
    $startDateFilter = Form::getFilterData('except', "business_trip_employees.start_at");
    $endDateFilter = Form::getFilterData('except', "business_trip_employees.end_at");
    $approvedDateFilter = Form::getFilterData('except', "bus_reg.approved_at");
    $tblBusEmp = 'business_trip_employees';
    $tblEmpContact = 'tblEmpContact';

    $statusUnapprove = BusinessTripRegister::STATUS_UNAPPROVE;
    $statusApproved = BusinessTripRegister::STATUS_APPROVED;
    $statusDisapprove = BusinessTripRegister::STATUS_DISAPPROVE;
    $statusCancel = BusinessTripRegister::STATUS_CANCEL;
    $urlShowPopup = route('manage_time::profile.mission.view-popup');
    $allowCreateEditOther = MissionPermission::allowCreateEditOther();

    $routeName = \Request::route()->getName();
    $report = false;
    if ($routeName == "manage_time::timekeeping.manage.report-business-trip") {
        $allowCreateEditOther = false;
        $report = true;
    }

    $colspan = 11;
    if ($allowCreateEditOther) {
        $colspan = 12;
    }
?>

@section('css-manage')
@endsection

@section('content-manage')
    <!-- Box manage list -->
    <div class="box box-primary" id="mission_manage_list" >
        <div class="box-header">
            <div class="team-select-box">
                @if (is_object($teamIdsAvailable))
                    <p>
                        <b>Team:</b>
                        <span>{{ $teamIdsAvailable->name }}</span>
                    </p>
                @elseif ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                    <label for="select-team-member">{{ trans('team::view.Choose team') }}</label>
                    <div class="input-box">
                        <select name="team_all" id="select-team-member"
                            class="form-control select-search input-select-team-member"
                            autocomplete="off">
                            
                            @if ($teamIdsAvailable === true)
                                <option value="{{ URL::route($routeName) }}"<?php
                                        if (! $teamIdCurrent): ?> selected<?php endif; 
                                        ?><?php
                                        if ($teamIdsAvailable !== true): ?> disabled<?php endif;
                                        ?>>&nbsp;</option>
                            @endif
                            
                            @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                @foreach($teamsOptionAll as $option)
                                    @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                                        <option value="{{ URL::route($routeName, ['team' => $option['value']]) }}"<?php
                                            if ($option['value'] == $teamIdCurrent): ?> selected<?php endif; 
                                                ?><?php
                                            if ($teamIdsAvailable === true):
                                            elseif (! in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else:
                                            ?>{{ $option['option'] }}<?php endif; ?>>{{ $option['label'] }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                @endif
            </div>
            <div class="pull-right">
                <div class="filter-action">
                    @if($isViewReportBusinessTrip)
                    <a href="{{ route('manage_time::timekeeping.manage.report') }}" class="btn btn-primary btn-report managetime-margin-bottom-5">
                        <span>{{ trans('manage_time::view.Business trip report') }} <i class="fa fa-print hidden"></i></span>
                    </a>
                    @endif
                    @if ($report)
                        <a href="{{ route('manage_time::timekeeping.manage.report-business-trip-export', ["id" => $teamIdCurrent]) }}" class="btn btn-primary btn-report managetime-margin-bottom-5">
                            <span>{{ trans('manage_time::view.Export') }} <i class="fa fa-print hidden"></i></span>
                        </a>
                    @endif
                    @if ($allowCreateEditOther)
                        <a class="btn btn-success managetime-margin-bottom-5" href="{{ route('manage_time::profile.mission.admin-register') }}" target="_blank">
                            <span><i class="fa fa-plus"></i> {{ trans('manage_time::view.Register') }}</span>
                        </a>
                    @endif
                    <button class="btn btn-primary btn-reset-filter managetime-margin-bottom-5">
                        <span>{{ trans('manage_time::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                    </button>
                    <button class="btn btn-primary btn-search-filter managetime-margin-bottom-5">
                        <span>{{ trans('manage_time::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                    </button>
                </div>
            </div>
        </div>
        <!-- /.box-header -->

        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table rs-index" id="managetime_table_fixed">
                    <thead class="managetime-thead">
                        <tr>
                            <th class="col-no">{{ trans('manage_time::view.No.') }}</th>

                            <th class="col-title col-creator-code" data-order="employee_code">{{ trans('manage_time::view.Employee code') }}</th>

                            <th class="col-title col-creator-name" data-order="name">{{ trans('manage_time::view.Employee name') }}</th>

                            <th class="col-title col-role-name" data-order="postion_emp">{{ trans('manage_time::view.Position') }}</th>

                            <th class="col-title col-location" data-order="location">{{ trans('manage_time::view.Location') }}</th>

                            <th class="col-title col-purpose" data-order="purpose">{{ trans('manage_time::view.Purpose') }}</th>

                            <th class="col-title col-date-start" data-order="start_at">{{ trans('manage_time::view.From date') }}</th>

                            <th class="col-title col-date-end" data-order="end_at">{{ trans('manage_time::view.End date') }}</th>

                            <th class="col-title col-approved-at" data-order="approved_at">{{ trans('manage_time::view.Approval time') }}</th>

                            <th class="col-title col-number-days-business-trip" data-order="number_days_business_trip">{{ trans('manage_time::view.Number of days business trip') }}</th>
                            @if ($report)
                                <th class="col-title col-skype" data-order="skype">{{ trans('manage_time::view.Skype') }}</th>

                                <th class="col-title col-email" data-order="email">{{ trans('manage_time::view.Telephone allowance') }}</th>

                                <th class="col-title col-mobile_phone" data-order="mobile_phone">{{ trans('manage_time::view.Email') }}</th>
                            @else
                                <th class="col-title col-approver-name" data-order="name_approver">{{ trans('manage_time::view.Approver') }}</th>

                                <th class="col-title col-status" data-order="status">{{ trans('manage_time::view.Status') }}</th>
                            @endif

                            @if ($allowCreateEditOther)
                                <th class="col-action-user"></th>
                            @endif
                        </tr>
                    </thead>
                </table>

                <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table-control" id="managetime_table_primary">
                    <thead class="managetime-thead">
                        <tr>
                            <th class="managetime-col-25 col-no" style="min-width: 25px;">{{ trans('manage_time::view.No.') }}</th>

                            <th class="col-title managetime-col-60 col-creator-code" data-order="employee_code" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.Employee code') }}</th>

                            <th class="col-title managetime-col-80 col-creator-name" data-order="name" style="min-width: 80px; max-width: 80px;">{{ trans('manage_time::view.Employee name') }}</th>

                            <th class="col-title managetime-col-120 col-role-name" data-order="postion_emp" style="min-width: 120px; max-width: 180px;">{{ trans('manage_time::view.Position') }}</th>

                            <th class="col-title managetime-col-50 col-location" data-order="location" style="min-width: 50px; max-width: 50px;">{{ trans('manage_time::view.Location') }}</th>

                            <th class="col-title managetime-col-100 col-purpose" data-order="purpose" style="min-width: 100px; max-width: 120px;">{{ trans('manage_time::view.Purpose') }}</th>

                            <th class="col-title managetime-col-60 col-date-start" data-order="start_at" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.From date') }}</th>

                            <th class="col-title managetime-col-60 col-date-end" data-order="end_at" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.End date') }}</th>

                            <th class="col-title managetime-col-60 col-approved-at" data-order="approved_at" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.Approval time') }}</th>

                            <th class="col-title managetime-col-35 col-number-days-business-trip" data-order="number_days_business_trip" style="min-width: 50px; max-width: 50px;">{{ trans('manage_time::view.Number of days business trip') }}</th>

                            @if ($report)
                                <th class="col-title managetime-col-80 col-skype" data-order="skype">{{ trans('manage_time::view.Skype') }}</th>

                                <th class="col-title managetime-col-80 col-email" data-order="email">{{ trans('manage_time::view.Telephone allowance') }}</th>

                                <th class="col-title managetime-col-80 col-mobile_phone" data-order="mobile_phone">{{ trans('manage_time::view.Email') }}</th>

                            @else
                                <th class="col-title managetime-col-80 col-approver-name" style="min-width: 80px; max-width: 80px;">{{ trans('manage_time::view.Approver') }}</th>

                                <th class="col-title managetime-col-140 col-status" data-order="status">{{ trans('manage_time::view.Status') }}</th>
                            @endif
                            
                            @if ($allowCreateEditOther)
                                <th class="managetime-col-85 col-action-user"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="position_start_header_fixed">
                        <tr>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $EmployeeTable }}.employee_code]" value='{{ Form::getFilterData("{$EmployeeTable}.employee_code") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $EmployeeTable }}.name]" value='{{ Form::getFilterData("{$EmployeeTable}.name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $filterRole = Form::getFilterData('number', "role_emp.id");
                                        ?>
                                        <select name="filter[number][role_emp.id]" class="form-control select-grid filter-grid select-search" autocomplete="off" style="width: 100%;">
                                            <option value="">&nbsp;</option>
                                            @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ $filterRole == $role->id ? 'selected' : '' }}>{{ $role->role }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $registerTable }}.location]" value='{{ Form::getFilterData("{$registerTable}.location") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $registerTable }}.purpose]" value='{{ Form::getFilterData("{$registerTable}.purpose") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $tblBusEmp }}.start_at]" value='{{ $startDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $tblBusEmp }}.end_at]" value='{{ $endDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $registerTable }}.approved_at]" value='{{ $approvedDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $numberDaysBusinessFilter = Form::getFilterData('except', "$registerTable.number_days_business_trip");
                                        ?>
                                        {{-- <input type="text" name="filter[except][{{ $registerTable }}.number_days_business_trip]" value='{{ $numberDaysBusinessFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" /> --}}
                                    </div>
                                </div>
                            </td>
                            @if ($report)
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[{!! $tblEmpContact !!}.skype]" value='{{ Form::getFilterData("{$tblEmpContact}.skype") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[{!! $tblEmpContact !!}.mobile_phone]" value='{{ Form::getFilterData("{$tblEmpContact}.mobile_phone") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[tblEmp.email]" value='{{ Form::getFilterData("tblEmp.email") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                            @else
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[tblEmp_app.name]" value='{{ Form::getFilterData("tblEmp_app.name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td style="min-width: 120px; max-width: 120px;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php
                                                $filterStatus = Form::getFilterData('number', "bus_reg.status");
                                            ?>
                                            <select name="filter[number][bus_reg.status]" class="form-control select-grid filter-grid select-search" autocomplete="off" style="width: 100%;">
                                                <option value="">&nbsp;</option>
                                                <option value="{{ $statusUnapprove }}" <?php
                                                    if ($filterStatus == $statusUnapprove): ?> selected<?php endif;
                                                        ?>>{{ trans('manage_time::view.Unapprove') }}
                                                </option>
                                                <option value="{{ $statusApproved }}" <?php
                                                    if ($filterStatus == $statusApproved): ?> selected<?php endif;
                                                        ?>>{{ trans('manage_time::view.Approved') }}
                                                </option>
                                                <option value="{{ $statusDisapprove }}" <?php
                                                    if ($filterStatus == $statusDisapprove): ?> selected<?php endif;
                                                        ?>>{{ trans('manage_time::view.Disapprove') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            @endif
                            @if ($allowCreateEditOther)
                                <td>&nbsp;</td>
                            @endif
                        </tr>

                        @if(isset($collectionModel) && count($collectionModel))
                            <?php
                                $i = View::getNoStartGrid($collectionModel);
                                $idReg = 0;
                                $j = 0;
                            ?>
                            @foreach($collectionModel as $item)
                                <?php 
                                    $tdClass = '';
                                    $status = '';
                                    if ($item->status == $statusApproved) {
                                        $tdClass = 'managetime-approved';
                                        $status = trans('manage_time::view.Approved');
                                    } elseif ($item->status == $statusDisapprove) {
                                        $tdClass = 'managetime-disapprove';
                                        $status = trans('manage_time::view.Disapprove');
                                    } elseif ($item->status == $statusUnapprove) {
                                        $tdClass = 'managetime-unapprove';
                                        $status = trans('manage_time::view.Unapprove');
                                    } else {
                                        $tdClass = 'managetime-cancelled';
                                        $status = trans('manage_time::view.Cancelled');
                                    }

                                    $start = Carbon::createFromFormat('Y-m-d H:i:s', $item->start_at);
                                    $end = Carbon::createFromFormat('Y-m-d H:i:s', $item->end_at);
                                    if ($item->creator_id == $item->employee_id) {
                                        $positon = $item->postion_reg ;
                                        $number = $item->number_days_business_trip;
                                    } else {
                                        $positon = $item->postion_emp;
                                        $start_at = clone $start;
                                        $end_at = clone $end;
                                        $number = BusinessTripRegister::getNumberBusinessTrip($start_at, $end_at, $item->employee_id);
                                    }
                                ?>
                                    <tr>
                                        @if ($item->register_id == $idReg)
                                            <td class="{{ $tdClass }} text-right">{{ $i - 1 }}.{{ ++$j }}</td>
                                            <td class="{{ $tdClass }}">{{ $item->employee_code }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td class="{{ $tdClass }}">{{ $positon }}</td>
                                            <td class="{{ $tdClass }}"></td>
                                            <td class="{{ $tdClass }}"></td>
                                            <td class="{{ $tdClass }}">{{ $start->format('d-m-Y H:i') }}</td>
                                            <td class="{{ $tdClass }}">{{ $end->format('d-m-Y H:i') }}</td>
                                            @if (isset($item->approved_at))
                                                <td class="{{ $tdClass }}">{{ Carbon::parse($item->approved_at)->format('d-m-Y H:i') }}</td>
                                            @else
                                                <td class="{{ $tdClass }}"></td>
                                            @endif
                                            <td class="{{ $tdClass }}">{{ $number }}</td>
                                            @if ($report)
                                                <td class="{{ $tdClass }}">{{ $item->skype }}</td>
                                                <td class="{{ $tdClass }}">{{ $item->mobile_phone }}</td>
                                                <td class="{{ $tdClass }}">{{ $item->email }}</td>
                                            @else
                                                <td class="{{ $tdClass }}"></td>
                                                <td class="{{ $tdClass }}"></td>
                                            @endif
                                        @else
                                            <?php $j = 0 ?>
                                            <td class="{{ $tdClass }}">{{ $i }}</td>
                                            <td class="{{ $tdClass }}">{{ $item->employee_code }}</td>
                                            <td class="{{ $tdClass }} managetime-show-popup">
                                                <a class="{{ $tdClass }}" value="{{ $item->register_id }}" style="cursor: pointer; color: #0673b3 !important">{{ $item->name }}</a>
                                            </td>
                                            <td class="{{ $tdClass }}"> {{ $positon }}</td>
                                            <td class="{{ $tdClass }}">{{ $item->location }}</td>
                                            <td class="{{ $tdClass }}">{{ $item->purpose }}</td>
                                            <td class="{{ $tdClass }}">{{ $start->format('d-m-Y H:i') }}</td>
                                            <td class="{{ $tdClass }}">{{ $end->format('d-m-Y H:i') }}</td>
                                            @if (isset($item->approved_at))
                                                <td class="{{ $tdClass }}">{{ Carbon::parse($item->approved_at)->format('d-m-Y H:i') }}</td>
                                            @else
                                                <td class="{{ $tdClass }}"></td>
                                            @endif
                                            <td class="{{ $tdClass }}"> {{ $number}}</td>
                                            @if ($report)
                                                <td class="{{ $tdClass }}">{{ $item->skype }}</td>
                                                <td class="{{ $tdClass }}">{{ $item->mobile_phone }}</td>
                                                <td class="{{ $tdClass }}">{{ $item->email }}</td>
                                            @else
                                                <td class="{{ $tdClass }}">{{ $item->name_approver }}</td>
                                                <td class="{{ $tdClass }}">{{ $status }}</td>
                                            @endif
                                            @if ($allowCreateEditOther)
                                                <td>
                                                    @if($item->status == $statusApproved || $item->status == $statusCancel)
                                                        <a class="btn btn-success" title="{{ trans('manage_time::view.View detail') }}" href="{{ route('manage_time::profile.mission.detail', ['id' => $item->register_id]) }}" >
                                                            <i class="fa fa-info-circle"></i>
                                                        </a>
                                                    @else
                                                        <a class="btn btn-success managetime-margin-bottom-5" title="{{ trans('manage_time::view.Edit') }}" href="{{ route('manage_time::profile.mission.edit', ['id' => $item->register_id]) }}" >
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <button class="btn btn-danger button-delete managetime-margin-bottom-5" value="{{ $item->register_id }}" data-status="{{ $item->status }}" title="{{ trans('manage_time::view.Delete') }}" data-toggle="modal">
                                                            <i class="fa fa-trash-o"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            @endif
                                        @endif
                                    </tr>

                                <?php
                                    if ($item->register_id != $idReg) {
                                        $i++;
                                    }
                                    $idReg = $item->register_id;
                                ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="11" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <!-- /.table -->

                <!-- View modal -->
                <div class="modal fade in managetime-modal" id="modal_view">
                </div>

                <!-- Delete modal -->
                @include('manage_time::include.modal.modal_delete')
            </div>
            <!-- /.table-responsive -->
        </div>
        <!-- /.box-body -->

        <div class="box-footer no-padding">
            <div class="mailbox-controls">   
                @include('team::include.pager')
            </div>
        </div>
    </div>
    <!-- /. box -->
@endsection

@section('script-manage')
    <script src="{{ CoreUrl::asset('asset_managetime/js/manage.list.js') }}"></script>
    <script type="text/javascript">
        var urlShowPopup = '{{ $urlShowPopup }}';
        var statusApproved = '{{ $statusApproved }}';
        var urlDelete = '{{ route('manage_time::profile.mission.delete') }}';
        var contentDisallowDelete = '<?php echo trans("manage_time::message.The register of business trip has been approved cannot delete"); ?>';

        var fixTop = $('#position_start_header_fixed').offset().top;
        $(window).scroll(function() {
            var scrollTop = $(window).scrollTop();
            if (scrollTop > fixTop) {
                var widthColNo = $('#managetime_table_primary .col-no').width();
                $('#managetime_table_fixed .col-no').width(widthColNo);

                var widthColCreatorCode = $('#managetime_table_primary .col-creator-code').width();
                $('#managetime_table_fixed .col-creator-code').width(widthColCreatorCode);

                var widthColCreatorName = $('#managetime_table_primary .col-creator-name').width();
                $('#managetime_table_fixed .col-creator-name').width(widthColCreatorName);

                var widthColRoleName = $('#managetime_table_primary .col-role-name').width();
                $('#managetime_table_fixed .col-role-name').width(widthColRoleName);

                var widthColLocation = $('#managetime_table_primary .col-location').width();
                $('#managetime_table_fixed .col-location').width(widthColLocation);

                var widthColPurpose = $('#managetime_table_primary .col-purpose').width();
                $('#managetime_table_fixed .col-purpose').width(widthColPurpose);

                var widthColDateStart = $('#managetime_table_primary .col-date-start').width();
                $('#managetime_table_fixed .col-date-start').width(widthColDateStart);

                var widthColEndStart = $('#managetime_table_primary .col-date-end').width();
                $('#managetime_table_fixed .col-date-end').width(widthColEndStart);

                var widthColApprovedAt = $('#managetime_table_primary .col-approved-at').width();
                $('#managetime_table_fixed .col-approved-at').width(widthColApprovedAt);

                var widthColNumberDaysBusinessTrip = $('#managetime_table_primary .col-number-days-business-trip').width();
                $('#managetime_table_fixed .col-number-days-business-trip').width(widthColNumberDaysBusinessTrip);

                var widthColApproverName = $('#managetime_table_primary .col-approver-name').width();
                $('#managetime_table_fixed .col-approver-name').width(widthColApproverName);

                var widthColStatus = $('#managetime_table_primary .col-status').width();
                $('#managetime_table_fixed .col-status').width(widthColStatus);
            }
        });
    </script>
@endsection
