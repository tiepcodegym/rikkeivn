<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\ManageTime\View\ManageTimeConst as MTConst;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View as CoreView;
use Rikkei\ManageTime\View\WorkingTimePermission;

$listStatuses = MTConst::listWorkingTimeStatuses();
$route = request()->route()->getName();
$isMyApprove = ($route == 'manage_time::wktime.register.approve.list');
$isMyRegister = ($route == 'manage_time::wktime.register.list');
$listIds = $collectionModel->lists('id')->toArray();
$listPermiss = WorkingTimePermission::permissEditItems($listIds);
$listPermissApprove = WorkingTimePermission::permissApproveItems($listIds);
?>

@extends('layouts.default')

@section('title', trans('manage_time::view.Register working time'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/working-time.css') }}">
@stop

@section('content')

<div class="content-sidebar">

    <div class="content-col">
        <!-- Box mission list -->
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="box-title managetime-box-title">{{ $pageTitle }}</h3>
                    </div>
                    <div class="col-sm-6 text-right">
                        @if ($isMyApprove)
                        <button type="button" class="btn btn-success status-submit status-btn" data-status="{{ MTConst::STT_WK_TIME_APPROVED }}"
                                data-url="{{ route('manage_time::wktime.approve_register') }}" disabled
                                data-noti="{{ trans('manage_time::message.confirm_do_action', ['action' => trans('manage_time::view.Approve')]) }}">
                            <i class="fa fa-check"></i> {{ trans('manage_time::view.Approve') }}
                        </button>
                        <button type="button" class="btn btn-danger status-btn" id="btn_time_modal_reject" data-status="{{ MTConst::STT_WK_TIME_REJECT }}" disabled>
                            <i class="fa fa-minus-circle"></i> {{ trans('manage_time::view.Not approve') }}
                        </button>
                        @endif
                        @include('team::include.filter')
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped dataTable table-bordered working-time-tbl">
                    <thead>
                        <tr>
                            @if ($isMyApprove)
                            <th><input type="checkbox" class="check-all"></th>
                            @endif
                            <th>{{ trans('manage_time::view.No.') }}</th>
                            @if (!$isMyRegister)
                            <th class="sorting white-space-nowrap {{ Config::getDirClass('emp_email') }} col-name" data-order="emp_email" data-dir="{{ Config::getDirOrder('emp_email') }}">{{ trans('manage_time::view.Registrant') }}</th>
                            @endif
                            <th class="sorting {{ Config::getDirClass('from_month') }} col-name" data-order="from_month" data-dir="{{ Config::getDirOrder('from_month') }}">{{ trans('manage_time::view.From month') }}</th>
                            <th class="sorting {{ Config::getDirClass('to_month') }} col-name" data-order="to_month" data-dir="{{ Config::getDirOrder('to_month') }}">{{ trans('manage_time::view.To month') }}</th>
                            <th>{{ trans('manage_time::view.working_time') }}</th>
                            <th class="sorting {{ Config::getDirClass('created_at') }} col-name" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('manage_time::view.Filing date') }}</th>
                            <th class="sorting white-space-nowrap {{ Config::getDirClass('reason') }} col-name" data-order="reason" data-dir="{{ Config::getDirOrder('reason') }}">{{ trans('manage_time::view.Reason change working time') }}</th>
                            <th class="sorting {{ Config::getDirClass('status') }} col-name" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('manage_time::view.Status') }}</th>
                            <th class="sorting white-space-nowrap {{ Config::getDirClass('approver_mail') }} col-name" data-order="approver_mail" data-dir="{{ Config::getDirOrder('approver_mail') }}">{{ trans('manage_time::view.Approver') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @if ($isMyApprove)
                            <td></td>
                            @endif
                            <td></td>
                            @if (!$isMyRegister)
                            <td>
                                <input type="text" name="filter[emp.email]" class="form-control filter-grid"
                                       value="{{ CoreForm::getFilterData('emp.email') }}" placeholder="{{ trans('manage_time::view.Search') }}...">
                            </td>
                            @endif
                            <td>
                                <input type="text" name="filter[excerpt][from_month]" class="form-control filter-grid month-filter"
                                       value="{{ CoreForm::getFilterData('excerpt', 'from_month') }}" placeholder="{{ trans('manage_time::view.Search') }}...">
                            </td>
                            <td>
                                <input type="text" name="filter[excerpt][to_month]" class="form-control filter-grid month-filter"
                                       value="{{ CoreForm::getFilterData('excerpt', 'to_month') }}" placeholder="{{ trans('manage_time::view.Search') }}...">
                            </td>
                            <td></td>
                            <td>
                                <input type="text" name="filter[wkt.created_at]" class="form-control filter-grid"
                                       value="{{ CoreForm::getFilterData('wkt.created_at') }}" placeholder="{{ trans('manage_time::view.Search') }}...">
                            </td>
                            <td>
                                <input type="text" name="filter[wkt.reason]" class="form-control filter-grid"
                                       value="{{ CoreForm::getFilterData('wkt.reason') }}" placeholder="{{ trans('manage_time::view.Search') }}...">
                            </td>
                            <td>
                                @if (!$status)
                                <?php
                                $filterStatus = CoreForm::getFilterData('number', 'wkt.status');
                                ?>
                                <select class="form-control select-search select-grid filter-grid" name="filter[number][wkt.status]"
                                        style="min-width: 120px;">
                                    <option value="">&nbsp;</option>
                                    @foreach ($listStatuses as $value => $label)
                                    <option value="{{ $value }}" {{ $value == $filterStatus ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @endif
                            </td>
                            <td>
                                <input type="text" name="filter[approver.email]" class="form-control filter-grid"
                                       value="{{ CoreForm::getFilterData('approver.email') }}" placeholder="{{ trans('manage_time::view.Search') }}...">
                            </td>
                            <td></td>
                        </tr>
                        @if (!$collectionModel->isEmpty())
                            <?php
                            $currentPage = $collectionModel->currentPage();
                            $perPage = $collectionModel->perPage();
                            ?>
                            @foreach ($collectionModel as $order => $item)
                            <?php
                            $isApproveItem = WorkingTimePermission::checkApproveInList($item->id, $listPermissApprove, $item->approver_id);
                            ?>
                            <tr>
                                @if ($isMyApprove)
                                <td>
                                    @if ($isApproveItem)
                                    <input type="checkbox" class="check-item" value="{{ $item->id }}" data-status="{{ $item->status }}">
                                    @endif
                                </td>
                                @endif
                                <td>{{ $order + 1 + ($currentPage - 1) * $perPage }}</td>
                                @if (!$isMyRegister)
                                <td>{{ CoreView::getNickName($item->emp_email) }}</td>
                                @endif
                                <td>{{ $item->getFromMonth() }}</td>
                                <td>{{ $item->getToMonth() }}</td>
                                <td class="white-space-nowrap">
                                    {{ trans('manage_time::view.Morning shift') }}: &nbsp; <strong>{{ $item->start_time1 . ' - ' . $item->end_time1 }}</strong> <br />
                                    {{ trans('manage_time::view.Afternoon shift') }}: <strong>{{ $item->start_time2 . ' - ' . $item->end_time2 }}</strong>
                                </td>
                                <td class="white-space-nowrap">{{ $item->created_at }}</td>
                                <td>
                                    <div class="minw-250 ws-pre-line short-content">{{ $item->reason }}</div>
                                </td>
                                <td>{!! $item->renderStatusHtml($listStatuses, 'label') !!}</td>
                                <td>{{ CoreView::getNickName($item->approver_mail) }}</td>
                                <td class="white-space-nowrap">
                                    <a href="{{ route('manage_time::wktime.register', ['id' => $item->id]) }}" class="btn btn-success"
                                       title="{{ trans('manage_time::view.Detail') }}"><i class="fa fa-eye"></i></a>
                                    @if ($item->isDelete() && WorkingTimePermission::checkPermissInList($item->id, $listPermiss, [$item->created_by, $item->employee_id]))
                                        {!! Form::open([
                                            'method' => 'delete',
                                            'class' => 'form-inline',
                                            'route' => ['manage_time::wktime.delete', $item->id]
                                        ]) !!}
                                        <button type="submit" class="btn-delete delete-confirm" title="{{ trans('manage_time::view.Delete') }}"><i class="fa fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @else
                        <tr>
                            <td colspan="9"><h4 class="text-center">{{ trans('manage_time::view.No results found') }}</h4></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->
            <div class="box-body">
                @include('team::include.pager')
            </div>
        </div>
        <!-- /. box -->
    </div>
    
    <div class="sidebar-col">
        <div class="sidebar-inner">
            @include('manage_time::working-time.includes.sidebar')
        </div>
    </div>
    
    @include('manage_time::working-time.includes.register-modal')
    
</div>

@stop

@section('script')
<script>
    var textRequiredItem = '{{ trans('manage_time::view.None item checked') }}';
    var textItemNotValid = '{{ trans('manage_time::view.None item valid!') }}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="{{ CoreUrl::asset('asset_managetime/js/working-time.js') }}"></script>
<script>
    $('.month-filter, .date-filter').on('dp.change', function () {
        $('.btn-search-filter').click();
    });
</script>
@stop

