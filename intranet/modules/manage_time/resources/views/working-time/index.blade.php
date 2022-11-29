<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\ManageTime\View\ManageTimeConst as MTConst;
    use Rikkei\ManageTime\View\WorkingTime;
    use Rikkei\ManageTime\View\WorkingTimePermission;
    use Rikkei\Team\View\Config;

    $objWorkingTime = new WorkingTime();
    $listStatuses = $objWorkingTime->listWorkingTimeStatuses();
    $listIds = $collectionModel->lists('id')->toArray();
    $listPermiss = WorkingTimePermission::permissEditItems($listIds);
    $listPermissApprove = WorkingTimePermission::permissApproveItems($listIds);
?>

@extends('layouts.default')

@section('title', trans('manage_time::view.Working time'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/working-time.css') }}">
@stop

@section('content')

<div class="box box-info">
    <div class="row">
        <div class="col-md-12">
            
            <div class="box-body text-right">
                @if (isset($permissRegister) && $permissRegister)    
                    <a href="{{ route('manage_time::wktime.register') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> {{ trans('manage_time::view.Register working time') }}
                    </a>
                @endif
               {{--  <div class="form-inline">
                    @include('team::include.filter')
                </div> --}}
            </div>

            <div class="tbl-lr-15 table-responsive">
                <table class="table table-hover table-striped dataTable table-bordered working-time-tbl">
                    <thead>
                        <tr>
                            <th>{{ trans('manage_time::view.No.') }}</th>
                            <th class="white-space-nowrap">{{ trans('manage_time::view.Registrant') }}</th>
                            <th>{{ trans('manage_time::view.Start date') }}</th>
                            <th>{{ trans('manage_time::view.End date') }}</th>
                            <th>{{ trans('manage_time::view.Timeframe of work') }}</th>
                            <th>{{ trans('manage_time::view.Time off 1/4') }}</th>
                            <th class="col-name" data-order="created_at">{{ trans('manage_time::view.Filing date') }}</th>
                            <th class="white-space-nowrap col-name">{{ trans('manage_time::view.Reason change working time') }}</th>
                            <th class="col-name" data-order="status">{{ trans('manage_time::view.Status') }}</th>
                            <th class="white-space-nowrap col-name">{{ trans('manage_time::view.Approver') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$collectionModel->isEmpty())
                            <?php
                                $currentPage = $collectionModel->currentPage();
                                $perPage = $collectionModel->perPage();
                            ?>
                            @foreach ($collectionModel as $order => $item)
                                <tr data-working_time_id ={{$item->working_time_id}}>
                                    <td>{{ $order + 1 + ($currentPage - 1) * $perPage }}</td>
                                    <td>{{ $item->workingTime->employee->name }}</td>
                                    <td>{{ $item->getFromDate() }}</td>
                                    <td>{{ $item->getToDate() }}</td>
                                    <td class="white-space-nowrap">
                                        @php
                                            $strMoring = 'Sáng ' . $item->start_time1 . ' - ' . $item->end_time1;
                                            $strAfter= 'Chiều ' . $item->start_time2 . ' - ' . $item->end_time2;
                                        @endphp
                                        {{ $strMoring}} <br>
                                        {{ $strAfter}}
                                    </td>
                                    <td class="white-space-nowrap">
                                        @php
                                            $strMoring = 'Sáng ' . $item->half_morning;
                                            $strAfter= 'Chiều ' . $item->half_afternoon;
                                        @endphp
                                        {{ $strMoring}} <br>
                                        {{ $strAfter}}
                                    </td>
                                    <td class="white-space-nowrap">{{ $item->created_at->format("d-m-Y") }}</td>
                                    <td>
                                        <div class="minw-250 ws-pre-line short-content">{{ $item->reason }}</div>
                                    </td>
                                    <td>{!!$objWorkingTime->renderStatusHtml($listStatuses, $item->status, 'label')!!}</td>
                                    <td>{{ $item->workingTime->approver->name }}</td>
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
            
            <div class="box-body">
                @include('team::include.pager')
            </div>
            
        </div>
    </div>
</div>

@stop

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script>
    $('.month-filter').datetimepicker({
        format: 'MM-YYYY',
        useCurrent: false,
    });
    $('.month-filter').on('dp.change', function () {
        $('.btn-search-filter').click();
    });
</script>
@stop
