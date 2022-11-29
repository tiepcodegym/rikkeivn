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
    $filter = CoreForm::getFilterData();
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
                @include('team::include.filter')
            </div>
            <div class="tbl-lr-15">
                <div class="table-responsive">
                    <table class="table table-hover table-striped dataTable table-bordered working-time-tbl">
                        <thead>
                            <tr>
                                <th>{{ trans('manage_time::view.No.') }}</th>
                                <th class="white-space-nowrap">{{ trans('manage_time::view.Registrant') }}</th>
                                <th class="col-name">{{ trans('manage_time::view.From date') }}</th>
                                <th class="col-name">{{ trans('manage_time::view.End date') }}</th>
                                <th>{{ trans('manage_time::view.Timeframe of work') }}</th>
                                <th>{{ trans('manage_time::view.Time off 1/4') }}</th>
                                <th class="col-name">{{ trans('manage_time::view.Filing date') }}</th>
                                <th class="white-space-nowrap col-name">{{ trans('manage_time::view.Reason change working time') }}</th>
                                <th class="col-name">{{ trans('manage_time::view.Status') }}</th>
                                <th class="white-space-nowrap col-name">{{ trans('manage_time::view.Approver') }}</th>
                                <th></th>
                            </tr>
                            <tr class="filter-input-grid">
                                <td>&nbsp;</td>
                                <td>
                                    <input type="text" name="filter[wtr.employee_name]" value='{{ CoreForm::getFilterData("wtr.employee_name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
                                <td>
                                    <input type="text" name="filter[wtr.date_start]" value='{{ CoreForm::getFilterData("wtr.date_start") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                </td>
                                <td>
                                    <input type="text" name="filter[wtr.date_end]" value='{{ CoreForm::getFilterData("wtr.date_end") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                </td>
                                <td>
                                    @if (isset($workingTimeFrame) && count ($workingTimeFrame))
                                        <?php
                                            $keyWKTFrame = null;
                                            if (isset($filter['wtr.working_time']))
                                                $keyWKTFrame = (int)CoreForm::getFilterData("wtr.working_time");
                                        ?>
                                        <select name="filter[wtr.working_time]"class="form-control select2-base filter-grid change-search">
                                            <option value="">&nbsp;</option>
                                            @foreach ($workingTimeFrame as $key => $item)
                                                <option value="{{$key}}"
                                                    @if ($key === $keyWKTFrame)
                                                        selected
                                                    @endif
                                                >{{ $objWorkingTime->getLabelWorkingTime($item) }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                                <td>
                                    @if (isset($workingTimeHalfFrame) && count ($workingTimeHalfFrame))
                                    <?php
                                        $keyWKTHalfFrame = null;
                                        if (isset($filter['wtr.working_time_half']))
                                            $keyWKTHalfFrame = (int)CoreForm::getFilterData("wtr.working_time_half");
                                    ?>
                                    <select name="filter[wtr.working_time_half]"class="form-control select2-base filter-grid change-search">
                                        <option value="">&nbsp;</option>
                                        @foreach ($workingTimeHalfFrame as $key => $item)
                                            <option value="{{$key}}"
                                                @if ($key === $keyWKTHalfFrame)
                                                    selected
                                                @endif
                                            >{{ $objWorkingTime->getLabelWorkingTimeHalf($item) }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                </td>
                                <td>
                                    <input type="text" name="filter[wtr.created_date]" value='{{ CoreForm::getFilterData("wtr.created_date") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                </td>
                                <td>&nbsp;</td>
                                <td>
                                    @if (isset($selectStatus) && count ($selectStatus))
                                        <?php
                                            $keyStatus = CoreForm::getFilterData("wtr.status");
                                        ?>
                                        <select name="filter[wtr.status]" class="form-control change-search filter-grid">
                                            <option value="">&nbsp;</option>
                                            @foreach ($selectStatus as $key => $item)
                                                <option value="{{$key}}"
                                                    @if ($key == $keyStatus)
                                                        selected
                                                    @endif
                                                >{{ $item }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                                <td>
                                    <input type="text" name="filter[wtr.employee_name_approve]" value='{{ CoreForm::getFilterData("wtr.employee_name_approve") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </td>
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
                                    <tr title="{{count($item->workingTimeDetails)}}">
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
    $('.filter-date').datetimepicker({
        format: "DD-MM-YYYY",
        useCurrent: true,          
    });
    $('.change-search').on('change', function(e) {
        $('.btn-search-filter').trigger('click');
    })
</script>
@stop
