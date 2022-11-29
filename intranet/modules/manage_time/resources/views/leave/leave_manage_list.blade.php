@extends('manage_time::layout.manage_layout')

@section('title-manage')
    {{ trans('manage_time::view.Manage leave day') }} 
@endsection

<?php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth; 
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View;
    use Rikkei\Core\View\Form;
    use Rikkei\Team\View\Config;
    use Rikkei\ManageTime\View\ManageTimeCommon;
    use Rikkei\ManageTime\Model\LeaveDayRegister;
    use Rikkei\ManageTime\View\LeaveDayPermission;

    $teamsOptionAll = \Rikkei\Team\View\TeamList::toOption(null, false, true);

    $registerTable = LeaveDayRegister::getTableName();
    $employeeCreateTable = 'employee_table_for_creator';
    $employeeApproveTable = 'employee_table_for_approver';
    $employeeAubstituteTable = 'employee_table_for_substitute';
    $startDateFilter = Form::getFilterData('except', "$registerTable.date_start");
    $endDateFilter = Form::getFilterData('except', "$registerTable.date_end");
    $createdAtFilter = Form::getFilterData('except', "$registerTable.created_at");
    $approvedDateFilter = Form::getFilterData('except', "$registerTable.approved_at");

    $statusUnapprove = LeaveDayRegister::STATUS_UNAPPROVE;
    $statusApproved = LeaveDayRegister::STATUS_APPROVED;
    $statusDisapprove = LeaveDayRegister::STATUS_DISAPPROVE;
    $statusCancel = LeaveDayRegister::STATUS_CANCEL;
    $urlShowPopup = route('manage_time::profile.leave.view-popup');
    $allowCreateEditOther = LeaveDayPermission::allowCreateEditOther();
    $colspan = 13;
    if ($allowCreateEditOther) {
        $colspan = 14;
    }
?>

@section('css-manage')
@endsection

@section('content-manage')
    <!-- Box manage list -->
    <div class="box box-primary">
        <div class="box-header">
            <div class="team-select-box">
                @if (is_object($teamIdsAvailable))
                    <p>
                        <b>Team:</b>
                        <span>{{ $teamIdsAvailable->name }}</span>
                    </p>
                @elseif ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                    <label for="select-team-member">{{ trans('manage_time::view.Select team') }}</label>
                    <div class="input-box">
                        <select name="team_all" id="select-team-member"
                            class="form-control select-search input-select-team-member"
                            autocomplete="off">
                            
                            @if ($teamIdsAvailable === true)
                                <option value="{{ URL::route('manage_time::timekeeping.manage.leave') }}"<?php
                                        if (! $teamIdCurrent): ?> selected<?php endif; 
                                        ?><?php
                                        if ($teamIdsAvailable !== true): ?> disabled<?php endif;
                                        ?>>&nbsp;</option>
                            @endif
                            
                            @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                @foreach($teamsOptionAll as $option)
                                    @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                                        <option value="{{ URL::route('manage_time::timekeeping.manage.leave', ['team' => $option['value']]) }}"<?php
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
                    @if ($allowCreateEditOther)
                        <a class="btn btn-success managetime-margin-bottom-5" href="{{ route('manage_time::profile.leave.admin-register') }}" target="_blank">
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
                    <thead class="managetime-thead ">
                        <tr>
                            <th class="col-no">{{ trans('manage_time::view.No.') }}</th>

                            <th class="sorting {{ Config::getDirClass('creator_code') }} col-title col-creator-code" data-order="creator_code" data-dir="{{ Config::getDirOrder('creator_code') }}">{{ trans('manage_time::view.Employee code') }}</th>

                            <th class="sorting {{ Config::getDirClass('creator_name') }} col-title col-creator-name" data-order="creator_name" data-dir="{{ Config::getDirOrder('creator_name') }}">{{ trans('manage_time::view.Registrant') }}</th>

                            <th class="sorting {{ Config::getDirClass('role_name') }} col-title col-role-name" data-order="role_name" data-dir="{{ Config::getDirOrder('role_name') }}">{{ trans('manage_time::view.Position') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_start') }} col-title col-date-start" data-order="date_start" data-dir="{{ Config::getDirOrder('date_start') }}">{{ trans('manage_time::view.From date') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_end') }} col-title col-date-end" data-order="date_end" data-dir="{{ Config::getDirOrder('date_end') }}">{{ trans('manage_time::view.End date') }}</th>

                            <th class="sorting {{ Config::getDirClass('created_at') }} col-title col-created-at" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('manage_time::view.Filing date') }}</th>

                            <th class="sorting {{ Config::getDirClass('approved_at') }} col-title col-approved-at" data-order="approved_at" data-dir="{{ Config::getDirOrder('approved_at') }}">{{ trans('manage_time::view.Approval time') }}</th>

                            <th class="sorting {{ Config::getDirClass('number_days_off') }} col-title col-number-days-off" data-order="number_days_off" data-dir="{{ Config::getDirOrder('number_days_off') }}">{{ trans('manage_time::view.Number of days off') }}</th>

                            <th class="sorting {{ Config::getDirClass('reason') }} col-title col-reason" data-order="reason" data-dir="{{ Config::getDirOrder('reason') }}">{{ trans('manage_time::view.Leave day type') }}</th>

                            <th class="sorting {{ Config::getDirClass('substitute_name') }} col-title col-substitute-name" data-order="substitute_name" data-dir="{{ Config::getDirOrder('substitute_name') }}">{{ trans('manage_time::view.Substitute person') }}</th>

                            <th class="sorting {{ Config::getDirClass('approver_name') }} col-title col-approver-name" data-order="approver_name" data-dir="{{ Config::getDirOrder('approver_name') }}">{{ trans('manage_time::view.Approver') }}</th>

                            <th class="sorting {{ Config::getDirClass('status') }} col-title col-status" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('manage_time::view.Status') }}</th>
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

                            <th class="sorting {{ Config::getDirClass('creator_code') }} col-title managetime-col-60 col-creator-code" data-order="creator_code" data-dir="{{ Config::getDirOrder('creator_code') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.Employee code') }}</th>

                            <th class="sorting {{ Config::getDirClass('creator_name') }} col-title managetime-col-80 col-creator-name" data-order="creator_name" data-dir="{{ Config::getDirOrder('creator_name') }}" style="min-width: 80px; max-width: 80px;">{{ trans('manage_time::view.Registrant') }}</th>

                            <th class="sorting {{ Config::getDirClass('role_name') }} col-title managetime-col-120 col-role-name" data-order="role_name" data-dir="{{ Config::getDirOrder('role_name') }}" style="min-width: 120px; max-width: 180px;">{{ trans('manage_time::view.Position') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_start') }} col-title managetime-col-60 col-date-start" data-order="date_start" data-dir="{{ Config::getDirOrder('date_start') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.From date') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_end') }} col-title managetime-col-60 col-date-end" data-order="date_end" data-dir="{{ Config::getDirOrder('date_end') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.End date') }}</th>

                            <th class="sorting {{ Config::getDirClass('created_at') }} col-title managetime-col-60 col-created-at" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.Filing date') }}</th>

                            <th class="sorting {{ Config::getDirClass('approved_at') }} col-title managetime-col-60 col-approved-at" data-order="approved_at" data-dir="{{ Config::getDirOrder('approved_at') }}">{{ trans('manage_time::view.Approval time') }}</th>

                            <th class="sorting {{ Config::getDirClass('number_days_off') }} col-title managetime-col-35 col-number-days-off" data-order="number_days_off" data-dir="{{ Config::getDirOrder('number_days_off') }}" style="min-width: 35px; max-width: 35px;">{{ trans('manage_time::view.Number of days off') }}</th>

                            <th class="sorting {{ Config::getDirClass('reason') }} col-title managetime-col-60 col-reason" data-order="reason" data-dir="{{ Config::getDirOrder('reason') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.Leave day type') }}</th>

                            <th class="sorting {{ Config::getDirClass('substitute_name') }} col-title managetime-col-80 col-substitute-name" data-order="substitute_name" data-dir="{{ Config::getDirOrder('substitute_name') }}" style="min-width: 80px; max-width: 80px;">{{ trans('manage_time::view.Substitute person') }}</th>

                            <th class="sorting {{ Config::getDirClass('approver_name') }} col-title managetime-col-80 col-approver-name" data-order="approver_name" data-dir="{{ Config::getDirOrder('approver_name') }}" style="min-width: 80px; max-width: 80px;">{{ trans('manage_time::view.Approver') }}</th>

                            <th class="sorting {{ Config::getDirClass('status') }} col-title managetime-col-120 col-status" data-order="status" data-dir="{{ Config::getDirOrder('status') }}" style="min-width: 120px; max-width: 120px;">{{ trans('manage_time::view.Status') }}</th>
                            
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
                                        <input type="text" name="filter[{{ $employeeCreateTable }}.employee_code]" value='{{ Form::getFilterData("{$employeeCreateTable}.employee_code") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $employeeCreateTable }}.name]" value='{{ Form::getFilterData("{$employeeCreateTable}.name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $registerTable }}.date_start]" value='{{ $startDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $registerTable }}.date_end]" value='{{ $endDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $registerTable }}.created_at]" value='{{ $createdAtFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
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
                                            $numberDaysOffFilter = Form::getFilterData('except', "$registerTable.number_days_off");
                                        ?>
                                        <input type="text" name="filter[except][{{ $registerTable }}.number_days_off]" value='{{ $numberDaysOffFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $filterReason = Form::getFilterData('number', "leave_day_reason_table.id");
                                        ?>
                                        <select name="filter[number][leave_day_reason_table.id]" class="form-control select-grid filter-grid select-search" autocomplete="off" style="width: 100%;">
                                            <option>&nbsp;</option>
                                            @foreach($listLeaveDayReasons as $key => $value)
                                                @if($value->id)
                                                    <option value="{{ $value->id }}" <?php
                                                            if ($value->id == $filterReason): ?> selected<?php endif; 
                                                                ?>>{{ $value->reason_name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $employeeAubstituteTable }}.name]" value='{{ Form::getFilterData("{$employeeAubstituteTable}.name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $employeeApproveTable }}.name]" value='{{ Form::getFilterData("{$employeeApproveTable}.name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $filterStatus = Form::getFilterData('number', "leave_day_registers.status");
                                        ?>
                                        <select name="filter[number][leave_day_registers.status]" class="form-control select-grid filter-grid select-search" autocomplete="off" style="width: 100%;">
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
                                            <option value="{{ $statusCancel }}" <?php
                                                if ($filterStatus == $statusCancel): ?> selected<?php endif; 
                                                    ?>>{{ trans('manage_time::view.Cancelled') }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </td>
                            @if ($allowCreateEditOther)
                                <td>&nbsp;</td>
                            @endif
                        </tr>

                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
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
                                ?>
                                    <tr>
                                        <td class="{{ $tdClass }}">{{ $i }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->creator_code }}</td>
                                        <td class="{{ $tdClass }} managetime-show-popup">
                                            <a class="{{ $tdClass }}" value="{{ $item->register_id }}" style="cursor: pointer;">{{ $item->creator_name }}</a>
                                        </td>
                                        <td class="{{ $tdClass }}">{{ $item->role_name }}</td>
                                        <td class="{{ $tdClass }}">{{ Carbon::parse($item->date_start)->format('d-m-Y H:i') }}</td>
                                        <td class="{{ $tdClass }}">{{ Carbon::parse($item->date_end)->format('d-m-Y H:i') }}</td>
                                        <td class="{{ $tdClass }}">{{ Carbon::parse($item->created_at)->format('d-m-Y H:i') }}</td>
                                        @if (isset($item->approved_at))
                                            <td class="{{ $tdClass }}">{{ Carbon::parse($item->approved_at)->format('d-m-Y H:i') }}</td>
                                        @else
                                            <td class="{{ $tdClass }}"></td>
                                        @endif
                                        <td class="{{ $tdClass }}">{{ $item->number_days_off }}</td>
                                        <td class="{{ $tdClass }}">{!! View::nl2br($item->reason) !!}</td>
                                        <td class="{{ $tdClass }}">{{ $item->substitute_name }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->approver_name }}</td>
                                        <td class="{{ $tdClass }}">{{ $status }}</td>
                                        </td>
                                        @if ($allowCreateEditOther)
                                            <td>
                                                @if($item->status == $statusApproved || $item->status == $statusCancel)
                                                    <a class="btn btn-success" title="{{ trans('manage_time::view.View detail') }}" href="{{ route('manage_time::profile.leave.detail', ['id' => $item->register_id]) }}" >
                                                        <i class="fa fa-info-circle"></i>
                                                    </a>
                                                @else
                                                    <a class="btn btn-success managetime-margin-bottom-5" title="{{ trans('manage_time::view.Edit') }}" href="{{ route('manage_time::profile.leave.edit', ['id' => $item->register_id]) }}" >
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-danger button-delete managetime-margin-bottom-5" value="{{ $item->register_id }}" data-status="{{ $item->status }}" title="{{ trans('manage_time::view.Delete') }}" data-toggle="modal">
                                                        <i class="fa fa-trash-o"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="{{ $colspan }}" class="text-center">
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
        var urlDelete = '{{ route('manage_time::profile.leave.delete') }}';
        var contentDisallowDelete = '<?php echo trans("manage_time::message.The register of leave day has been approved cannot delete"); ?>';

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

                var widthColDateStart = $('#managetime_table_primary .col-date-start').width();
                $('#managetime_table_fixed .col-date-start').width(widthColDateStart);

                var widthColEndStart = $('#managetime_table_primary .col-date-end').width();
                $('#managetime_table_fixed .col-date-end').width(widthColEndStart);

                var widthColCreatedAt = $('#managetime_table_primary .col-created-at').width();
                $('#managetime_table_fixed .col-created-at').width(widthColCreatedAt);
                
                var widthColApprovedAt = $('#managetime_table_primary .col-approved-at').width();
                $('#managetime_table_fixed .col-approved-at').width(widthColApprovedAt);

                var widthColNumberDaysOff = $('#managetime_table_primary .col-number-days-off').width();
                $('#managetime_table_fixed .col-number-days-off').width(widthColNumberDaysOff);

                var widthColReason = $('#managetime_table_primary .col-reason').width();
                $('#managetime_table_fixed .col-reason').width(widthColReason);

                var widthColNote = $('#managetime_table_primary .col-note').width();
                $('#managetime_table_fixed .col-note').width(widthColNote);

                var widthColSubstituteName = $('#managetime_table_primary .col-substitute-name').width();
                $('#managetime_table_fixed .col-substitute-name').width(widthColSubstituteName);

                var widthColApproverName = $('#managetime_table_primary .col-approver-name').width();
                $('#managetime_table_fixed .col-approver-name').width(widthColApproverName);

                var widthColStatus = $('#managetime_table_primary .col-status').width();
                $('#managetime_table_fixed .col-status').width(widthColStatus);
            }
        });
    </script>
@endsection
