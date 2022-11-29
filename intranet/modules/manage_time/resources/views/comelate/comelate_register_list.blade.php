@extends('manage_time::layout.common_layout')

@section('title-common')
    {{ trans('manage_time::view.Late in early out register') }} 
@endsection

<?php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View;
    use Rikkei\Core\View\Form;
    use Rikkei\Team\View\Config;
    use Rikkei\ManageTime\Model\ComeLateRegister;
    use Rikkei\ManageTime\View\ManageTimeCommon;

    $registerTable = ComeLateRegister::getTableName();
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
    $urlDelete = route('manage_time::profile.comelate.delete');
?>

@section('css-common')
@endsection

@section('sidebar-common')
    @include('manage_time::include.sidebar_comelate')
@endsection

@section('content-common')
    <!-- Box register list -->
    <div class="box box-primary">
        <div class="box-header">
            <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.Late in early out register list') }}</h3>
            <div class="pull-right">   
                @include('team::include.filter')
            </div>
        </div>
        <!-- /.box-header -->

        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table" id="managetime_table_fixed">
                    <thead class="managetime-thead">
                        <tr>
                            <th class="col-no">{{ trans('manage_time::view.No.') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_start') }} col-title col-date-start" data-order="date_start" data-dir="{{ Config::getDirOrder('date_start') }}">{{ trans('manage_time::view.From date') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_end') }} col-title col-date-end" data-order="date_end" data-dir="{{ Config::getDirOrder('date_end') }}">{{ trans('manage_time::view.End date') }}</th>

                            <th class="sorting {{ Config::getDirClass('apply_days') }} col-title col-apply-day" data-order="apply_days" data-dir="{{ Config::getDirOrder('apply_days') }}">{{ trans('manage_time::view.Apply to') }}</th>

                            <th class="sorting {{ Config::getDirClass('late_start_shift') }} col-title col-late-start-shift" data-order="late_start_shift" data-dir="{{ Config::getDirOrder('late_start_shift') }}">{{ trans('manage_time::view.Late start shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('early_mid_shift') }} col-title col-early-mid-shift" data-order="early_mid_shift" data-dir="{{ Config::getDirOrder('early_mid_shift') }}">{{ trans('manage_time::view.Early mid shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('late_mid_shift') }} col-title col-late-mid-shift" data-order="late_mid_shift" data-dir="{{ Config::getDirOrder('late_mid_shift') }}">{{ trans('manage_time::view.Late mid shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('early_end_shift') }} col-title col-early-end-shift" data-order="early_end_shift" data-dir="{{ Config::getDirOrder('early_end_shift') }}">{{ trans('manage_time::view.Early end shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('reason') }} col-title col-reason" data-order="reason" data-dir="{{ Config::getDirOrder('reason') }}">{{ trans('manage_time::view.Register reason') }}</th>

                            @if(isset($status))
                                <th class="col-title col-status">{{ trans('manage_time::view.Status') }}</th>
                            @else
                                <th class="sorting {{ Config::getDirClass('status') }} col-title col-status" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('manage_time::view.Status') }}</th>
                            @endif
                            
                            <th class="col-action-user"></th>
                        </tr>
                    </thead>
                </table>

                <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table-control" id="managetime_table_primary">
                    <thead class="managetime-thead">
                        <tr>
                            <th class="managetime-col-25 col-no" style="min-width: 25px;">{{ trans('manage_time::view.No.') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_start') }} col-title managetime-col-60 col-date-start" data-order="date_start" data-dir="{{ Config::getDirOrder('date_start') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.From date') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_end') }} col-title managetime-col-60 col-date-end" data-order="date_end" data-dir="{{ Config::getDirOrder('date_end') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.End date') }}</th>

                            <th class="sorting {{ Config::getDirClass('apply_days') }} col-title managetime-col-60 col-apply-day" data-order="apply_days" data-dir="{{ Config::getDirOrder('apply_days') }}" style="min-width: 60px; max-width:60px;">{{ trans('manage_time::view.Apply to') }}</th>

                            <th class="sorting {{ Config::getDirClass('late_start_shift') }} col-title managetime-col-40 col-late-start-shift" data-order="late_start_shift" data-dir="{{ Config::getDirOrder('late_start_shift') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Late start shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('early_mid_shift') }} col-title managetime-col-40 col-early-mid-shift" data-order="early_mid_shift" data-dir="{{ Config::getDirOrder('early_mid_shift') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Early mid shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('late_mid_shift') }} col-title managetime-col-40 col-late-mid-shift" data-order="late_mid_shift" data-dir="{{ Config::getDirOrder('late_mid_shift') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Late mid shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('early_end_shift') }} col-title managetime-col-40 col-early-end-shift" data-order="early_end_shift" data-dir="{{ Config::getDirOrder('early_end_shift') }}" style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Early end shift') }}</th>

                            <th class="sorting {{ Config::getDirClass('reason') }} col-title managetime-col-80 col-reason" data-order="reason" data-dir="{{ Config::getDirOrder('reason') }}"  style="min-width: 80px; max-width: 100px;">{{ trans('manage_time::view.Register reason') }}</th>

                            @if(isset($status))
                                <th class="col-title managetime-col-100 col-status" style="min-width: 100px; max-width: 100px;">{{ trans('manage_time::view.Status') }}</th>
                            @else
                                <th class="sorting {{ Config::getDirClass('status') }} col-title managetime-col-100 col-status" data-order="status" data-dir="{{ Config::getDirOrder('status') }}" style="min-width: 100px; max-width: 100px;">{{ trans('manage_time::view.Status') }}</th>
                            @endif
                            
                            <th class="managetime-col-85 col-action-user" style="min-width: 85px; max-width: 85px;"></th>
                        </tr>
                    </thead>
                    <tbody id="position_start_header_fixed">
                        <tr>
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
                                        <input type="text" name="filter[{{ $registerTable }}.reason]" value='{{ Form::getFilterData("{$registerTable}.reason") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            @if(empty($status))
                                <td style="min-width: 100px; max-width: 100px;">
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
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            @else
                                <td>&nbsp;</td>
                            @endif
                            <td>&nbsp;</td>
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
                                    } else {
                                        $tdClass = 'managetime-unapprove';
                                        $status = trans('manage_time::view.Unapprove');
                                    }
                                ?>
                                    <tr>
                                        <td class="{{ $tdClass }}">{{ $i }}</td>
                                        <td class="{{ $tdClass }}">{{ Carbon::parse($item->date_start)->format('d-m-Y') }}</td>
                                        <td class="{{ $tdClass }}">{{ Carbon::parse($item->date_end)->format('d-m-Y') }}</td>
                                        <td class="{{ $tdClass }}">{{ ManageTimeCommon::convertApplyDaysToString($item->apply_days) }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->late_start_shift }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->early_mid_shift }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->late_mid_shift }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->early_end_shift }}</td>
                                        <td class="{{ $tdClass }} managetime-read-more">{!! View::nl2br($item->reason) !!}</td>
                                        <td class="{{ $tdClass }}">{{ $status }}</td>
                                        <td>
                                            @if($item->status == $statusApproved)
                                                <a class="btn btn-success" title='{{ trans("manage_time::view.View detail") }}' href="{{ route('manage_time::profile.comelate.detail', ['id' => $item->register_id]) }}" >
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
                                    </tr>
                                <?php $i++; ?>
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

                <!-- Delete modal -->
                @include('manage_time::include.modal.modal_delete')

                <!-- Allow delete modal -->
                @include('manage_time::include.modal.modal_allow_edit')
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

@section('script-common')
    <script src="{{ CoreUrl::asset('asset_managetime/js/register.list.js') }}"></script>
    <script type="text/javascript">
        var statusApproved = '{{ $statusApproved }}';
        var urlDelete = '{{ $urlDelete }}';
        var contentDisallowDelete = '<?php echo trans("manage_time::message.The register of late in early out has been approved cannot delete"); ?>';

        var fixTop = $('#position_start_header_fixed').offset().top;
        $(window).scroll(function() {
            var scrollTop = $(window).scrollTop();
            if (scrollTop > fixTop) {
                var widthColNo = $('#managetime_table_primary .col-no').width();
                $('#managetime_table_fixed .col-no').width(widthColNo);

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

                var widthColStatus = $('#managetime_table_primary .col-status').width();
                $('#managetime_table_fixed .col-status').width(widthColStatus);

                var widthColAction = $('#managetime_table_primary .col-action-user').width();
                $('#managetime_table_fixed .col-action-user').width(widthColAction);
            }
        });
    </script>
@endsection
