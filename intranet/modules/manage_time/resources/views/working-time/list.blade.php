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

@section('title', trans('manage_time::view.Register working time'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/working-time.css') }}">
<style>
    .m-lf-10 {
        margin-left: 10px;
        margin-right: 10px;
    }
</style>
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
                        {{-- @include('team::include.filter') --}}
                    </div>
                </div>
            </div>

            <div class="m-lf-10">
                <div class="table-responsive">
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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!$collectionModel->isEmpty())
                                <?php
                                    $currentPage = $collectionModel->currentPage();
                                    $perPage = $collectionModel->perPage();
                                ?>
                                @foreach ($collectionModel as $order => $item)
                                    <?php
                                        $arrWT = $objWorkingTime->getLabelWorkingTime($workingTimeFrame[$item->key_working_time], true);
                                        $arrWTHalf = $objWorkingTime->getLabelWorkingTimeHalf($workingTimeHalfFrame[$item->key_working_time_half], true);
                                    ?>
                                    <tr>
                                        <td>{{ $order + 1 + ($currentPage - 1) * $perPage }}</td>
                                        <td>{{ $item->employee->name }}</td>
                                        <td>{{ $item->getFromDate() }}</td>
                                        <td>{{ $item->getToDate() }}</td>
                                        <td class="white-space-nowrap">
                                            {{ $arrWT[0]}} <br>
                                            {{ $arrWT[1]}}
                                        </td>
                                        <td class="white-space-nowrap">
                                            {{ $arrWTHalf[0]}} <br>
                                            {{ $arrWTHalf[1]}}
                                        </td>
                                        <td class="white-space-nowrap">{{ $item->created_at->format("d-m-Y") }}</td>
                                        <td>
                                            <div class="minw-250 ws-pre-line short-content">{{ $item->reason }}</div>
                                        </td>
                                        <td>{!!$objWorkingTime->renderStatusHtml($listStatuses, $item->status, 'label')!!}</td>
                                        <td>{{ $item->approver->name }}</td>
                                        <td class="white-space-nowrap">
                                            <a href="{{ route('manage_time::wktime.detail', ['id' => $item->id]) }}" class="btn btn-success"
                                               title="{{ trans('manage_time::view.Detail') }}"><i class="fa fa-eye"></i></a>
                                            @if ($item->isDelete() && $permiss['edit'])
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

