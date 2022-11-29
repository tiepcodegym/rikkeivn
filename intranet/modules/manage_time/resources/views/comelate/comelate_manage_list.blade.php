@extends('manage_time::layout.manage_layout')

@section('title-manage')
    {{ trans('manage_time::view.Manage late in early out') }} 
@endsection

<?php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth; 
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View;
    use Rikkei\Core\View\Form;
    use Rikkei\Team\View\Config;
    use Rikkei\ManageTime\View\ManageTimeCommon;
    use Rikkei\ManageTime\Model\ComeLateRegister;
    use Rikkei\ManageTime\View\ComeLatePermission;

    $teamsOptionAll = \Rikkei\Team\View\TeamList::toOption(null, true, false);

    $registerTable = ComeLateRegister::getTableName();
    $employeeCreateTable = 'employee_table_for_creator';
    $employeeApproveTable = 'employee_table_for_approver';
    $startDateFilter = Form::getFilterData('except', "$registerTable.date_start");
    $endDateFilter = Form::getFilterData('except', "$registerTable.date_end");
    $lateStartShiftFilter = Form::getFilterData('except', "$registerTable.late_start_shift");
    $earlyMidShiftFilter = Form::getFilterData('except', "$registerTable.early_mid_shift");
    $lateMidShiftFilter = Form::getFilterData('except', "$registerTable.late_mid_shift");
    $earlyEndShiftFilter = Form::getFilterData('except', "$registerTable.early_end_shift");

    $statusUnapprove = ComeLateRegister::STATUS_UNAPPROVE;
    $statusApproved = ComeLateRegister::STATUS_APPROVED;
    $statusDisapprove = ComeLateRegister::STATUS_DISAPPROVE;
    $statusCancel = ComeLateRegister::STATUS_CANCEL;
    $urlShowPopup = route('manage_time::profile.comelate.view-popup');
    $allowCreateEditOther = ComeLatePermission::allowCreateEditOther();
    $colspan = 14;
    if ($allowCreateEditOther) {
        $colspan = 15;
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
                                <option value="{{ URL::route('manage_time::timekeeping.manage.comelate') }}"<?php
                                        if (! $teamIdCurrent): ?> selected<?php endif; 
                                        ?><?php
                                        if ($teamIdsAvailable !== true): ?> disabled<?php endif;
                                        ?>>&nbsp;</option>
                            @endif
                            
                            @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                @foreach($teamsOptionAll as $option)
                                    @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                                        <option value="{{ URL::route('manage_time::timekeeping.manage.comelate', ['team' => $option['value']]) }}"<?php
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
                        <a class="btn btn-success managetime-margin-bottom-5" href="{{ route('manage_time::profile.comelate.admin-register') }}" target="_blank">
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

                            <th class="sorting {{ Config::getDirClass('creator_code') }} col-title col-creator-code" data-order="creator_code" data-dir="{{ Config::getDirOrder('creator_code') }}">{{ trans('manage_time::view.Employee code') }}</th>

                            <th class="sorting {{ Config::getDirClass('creator_name') }} col-title col-creator-name" data-order="creator_name" data-dir="{{ Config::getDirOrder('creator_name') }}">{{ trans('manage_time::view.Registrant') }}</th>

                            <th class="sorting {{ Config::getDirClass('role_name') }} col-title col-role-name" data-order="role_name" data-dir="{{ Config::getDirOrder('role_name') }}">{{ trans('manage_time::view.Position') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_start') }} col-title col-date-start" data-order="date_start" data-dir="{{ Config::getDirOrder('date_start') }}">{{ trans('manage_time::view.From date') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_end') }} col-title col-date-end" data-order="date_end" data-dir="{{ Config::getDirOrder('date_end') }}">{{ trans('manage_time::view.End date') }}</th>

                            <th class="sorting {{ Config::getDirClass('apply_days') }} col-title col-apply-day" data-order="apply_days" data-dir="{{ Config::getDirOrder('apply_days') }}">{{ trans('manage_time::view.Apply to') }}</th>

                            <th class="sorting {{ Config::getDirClass('late_start_shift') }} col-title col-late-start-shift" data-order="late_start_shift" data-dir="{{ Config::getDirOrder('late_start_shift') }}">{{ trans('manage_time::view.Late start shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('early_mid_shift') }} col-title col-early-mid-shift" data-order="early_mid_shift" data-dir="{{ Config::getDirOrder('early_mid_shift') }}">{{ trans('manage_time::view.Early mid shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('late_mid_shift') }} col-title col-late-mid-shift" data-order="late_mid_shift" data-dir="{{ Config::getDirOrder('late_mid_shift') }}">{{ trans('manage_time::view.Late mid shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('early_end_shift') }} col-title col-early-end-shift" data-order="early_end_shift" data-dir="{{ Config::getDirOrder('early_end_shift') }}">{{ trans('manage_time::view.Early end shift') }}</th>
 
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

                            <th class="sorting {{ Config::getDirClass('apply_days') }} col-title managetime-col-100 col-apply-day" data-order="apply_days" data-dir="{{ Config::getDirOrder('apply_days') }}" style="min-width: 100px; max-width: 120px;">{{ trans('manage_time::view.Apply to') }}</th>

                            <th class="sorting {{ Config::getDirClass('late_start_shift') }} col-title managetime-col-40 col-late-start-shift" data-order="late_start_shift" data-dir="{{ Config::getDirOrder('late_start_shift') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Late start shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('early_mid_shift') }} col-title managetime-col-40 col-early-mid-shift" data-order="early_mid_shift" data-dir="{{ Config::getDirOrder('early_mid_shift') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Early mid shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('late_mid_shift') }} col-title managetime-col-40 col-late-mid-shift" data-order="late_mid_shift" data-dir="{{ Config::getDirOrder('late_mid_shift') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Late mid shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('early_end_shift') }} col-title managetime-col-40 col-early-end-shift" data-order="early_end_shift" data-dir="{{ Config::getDirOrder('early_end_shift') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Early end shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('approver_name') }} col-title managetime-col-80 col-approver-name" data-order="approver_name" data-dir="{{ Config::getDirOrder('approver_name') }}" style="min-width: 80px; max-width: 80px;">{{ trans('manage_time::view.Approver') }}</th>

                            <th class="sorting {{ Config::getDirClass('status') }} col-title managetime-col-100 col-status" data-order="status" data-dir="{{ Config::getDirOrder('status') }}" style="min-width: 100px; max-width: 100px;">{{ trans('manage_time::view.Status') }}</th>
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
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $registerTable }}.late_start_shift]" value='{{ $lateStartShiftFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $registerTable }}.early_mid_shift]" value='{{ $earlyMidShiftFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $registerTable }}.late_mid_shift]" value='{{ $lateMidShiftFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $registerTable }}.early_end_shift]" value='{{ $earlyEndShiftFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
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
                            <td style="min-width: 120px; max-width: 120px;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $filterStatus = Form::getFilterData('number', "come_late_registers.status");
                                        ?>
                                        <select name="filter[number][come_late_registers.status]" class="form-control select-grid filter-grid select-search" autocomplete="off" style="width: 100%;">
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
                                        <td class="{{ $tdClass }}">{{ Carbon::parse($item->date_start)->format('d-m-Y') }}</td>
                                        <td class="{{ $tdClass }}">{{ Carbon::parse($item->date_end)->format('d-m-Y') }}</td>
                                        <td class="{{ $tdClass }}">{{ ManageTimeCommon::convertApplyDaysToString($item->apply_days) }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->late_start_shift }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->early_mid_shift }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->late_mid_shift }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->early_end_shift }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->approver_name }}</td>
                                        <td class="{{ $tdClass }}">{{ $status }}</td>
                                        @if ($allowCreateEditOther)
                                            <td>
                                                @if($item->status == $statusApproved || $item->status == $statusCancel)
                                                    <a class="btn btn-success" title="{{ trans('manage_time::view.View detail') }}" href="{{ route('manage_time::profile.comelate.detail', ['id' => $item->register_id]) }}" >
                                                        <i class="fa fa-info-circle"></i>
                                                    </a>
                                                @else
                                                    <a class="btn btn-success managetime-margin-bottom-5" title="{{ trans('manage_time::view.Edit') }}" href="{{ route('manage_time::profile.comelate.edit', ['id' => $item->register_id]) }}" >
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-danger button-delete managetime-margin-bottom-5" value="{{ $item->register_id }}" data-status="{{ $item->status }}" title="{{ trans('manage_time::view.Delete') }}" data-toggle="modal">
                                                        <i class="fa fa-trash-o"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        @endif
                                        </td>
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
        var urlDelete = '{{ route('manage_time::profile.comelate.delete') }}';
        var contentDisallowDelete = '<?php echo trans("manage_time::message.The register of late in early out has been approved cannot delete"); ?>';

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

                var widthColApplyDay = $('#managetime_table_primary .col-apply-day').width();
                $('#managetime_table_fixed .col-apply-day').width(widthColApplyDay);

                var widthColLateStartShift= $('#managetime_table_primary .col-late-start-shift').width();
                $('#managetime_table_fixed .col-late-start-shift').width(widthColLateStartShift);

                var widthColEarlyMidShift = $('#managetime_table_primary .col-early-mid-shift').width();
                $('#managetime_table_fixed .col-early-mid-shift').width(widthColEarlyMidShift);

                var widthColLateMidShift= $('#managetime_table_primary .col-late-mid-shift').width();
                $('#managetime_table_fixed .col-late-mid-shift').width(widthColLateMidShift);

                var widthColEarlyEndShift = $('#managetime_table_primary .col-early-end-shift').width();
                $('#managetime_table_fixed .col-early-end-shift').width(widthColEarlyEndShift);

                var widthColReason = $('#managetime_table_primary .col-reason').width();
                $('#managetime_table_fixed .col-reason').width(widthColReason);

                var widthColApproverName = $('#managetime_table_primary .col-approver-name').width();
                $('#managetime_table_fixed .col-approver-name').width(widthColApproverName);

                var widthColStatus = $('#managetime_table_primary .col-status').width();
                $('#managetime_table_fixed .col-status').width(widthColStatus);
            }
        });
    </script>
@endsection
