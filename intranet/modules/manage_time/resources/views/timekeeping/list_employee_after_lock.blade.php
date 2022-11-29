@extends('manage_time::layout.manage_layout')

@section('title-manage')
    {{ trans('manage_time::view.List employee after lock close timekeeping') }}
@endsection

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\ManageTime\Model\TimekeepingLockHistories;
    use Rikkei\Core\View\Form as CoreForm;use Rikkei\ManageTime\View\ViewTimeKeeping;
?>

@section('css-manage')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/timekeepinglock.css') }}" />
@endsection

<?php
    $objLockHistory = new TimekeepingLockHistories();
    $objVT = new ViewTimeKeeping();
    $arrLabelType = $objLockHistory->getLabelType();
    $arrLabelStatus = $objLockHistory->getlabelStatus();
    $arrLabelApp = $objVT->getLabelApp();
?>

@section('content-manage')
<div class="box box-primary lock">
    <div class="box-header table-responsive">
        <div class="col-md-8 col-sm-offset-2">
            <table class="table table-bordered table-hover table-grid-data">
                <tbody>
                    <tr class="text-center">
                        <td colspan="5"><b>{{ $infoTkTable->timekeeping_table_name }}</b></td>
                    </tr>
                    <tr>
                        <td><b>{{ trans('manage_time::view.Team') }}</b></td>
                        <td><b>{{ trans('manage_time::view.Month') }}</b></td>
                        <td><b>{{ trans('manage_time::view.Year') }}</b></td>
                        <td><b>{{ trans('manage_time::view.Close time lock up') }}</b></td>
                        <td><b>{{ trans('manage_time::view.Open time lock up') }}</b></td>
                    </tr>
                    <tr>
                        <td>{{ $infoTkTable->team_name }}</td>
                        <td>{{ $infoTkTable->month }}</td>
                        <td>{{ $infoTkTable->year }}</td>
                        @if ($infoLock)
                            <td>{{ $infoLock->time_close_lock }}</td>
                            <td>{{ $infoLock->time_open_lock }}</td>
                        @else
                            <td></td>
                            <td></td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-body">
        <div class="pull-right">
            @include('team::include.filter')
        </div>
        <ul class="nav nav-tabs">
            <?php
                $nLock = count($arrIdLock);
            ?>
            @foreach($arrIdLock as $idLock)
            <li class="@if ($idLock == $idLockActive) active @endif">
                <a href="{{ route('manage_time::timekeeping.list-employee-after-lock', ['id' => $infoTkTable->id]) }}?id-lock={{ $idLock }}">
                    {{ trans('manage_time::view.St lock') }} {{ $nLock-- }}
                </a>
            </li>
            @endforeach
        </ul>
        <br>
        <div class="box box-primary box-solid collapsed-box">
            <div class="box-header with-border">
                <div class="row ">
                    <div class="col-sm-2 col-xs-3">{{ trans('manage_time::view.Employee code') }}</div>
                    <div class="col-sm-4 col-xs-6">{{ trans('manage_time::view.Employee fullname') }}</div>
                    <div class="col-sm-5 col-xs-3 display-non-mobile">{{ trans('project::view.Team') }}</div>
                    <div class="col-sm-1 col-xs-3">{{ trans('manage_time::view.Application number') }}</div>
                </div>
            </div>
        </div>
        <div class="">
            <div class="row filter-input-grid">
                <div class="col-sm-2 col-xs-3">
                    <div style="margin-right: -30px">
                        <input type="text" name="filter[code]" value='{{ CoreForm::getFilterData("code") }}' placeholder="{{ trans('manage_time::view.Search') }} ..." class="filter-grid form-control" />
                    </div>
                </div>
                <div class="col-sm-4 col-xs-6">
                    <input type="text" name="filter[name]" value='{{ CoreForm::getFilterData("name") }}' placeholder="{{ trans('manage_time::view.Search') }} ..." class="filter-grid form-control" />
                </div>
                <div class="col-sm-5 col-xs-3 display-non-mobile">
                </div>
                <div class="col-sm-1 col-xs-3"></div>
            </div>
        </div>
        @if (count($arrInfoEmpAfterLock))
        @foreach($arrInfoEmpAfterLock as $keyEmp => $items)
            <div class="box box-default box-solid collapsed-box">
                <div class="box-header with-border">
                    <div class="row ">
                        <div class="col-sm-2 col-xs-3">{{ $items['infoEmp']['empCode'] }}</div>
                        <div class="col-sm-4 col-xs-6">{{ $items['infoEmp']['empName'] }}</div>
                        <div class="col-sm-5 col-xs-3 display-non-mobile">{{ $items['infoEmp']['empTeam'] }}</div>
                        <div class="col-sm-1 col-xs-2">{{ $items['infoEmp']['number'] }}</div>
                    </div>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body table-responsive" style="display: none;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="display-non-mobile"></th>
                                <th>#</th>
                                <th class="with-260">{{ trans('manage_time::view.Application type name') }}</th>
                                <th>{{ trans('manage_time::view.Id') }}</th>
                                <th>{{ trans('manage_time::view.From date') }}</th>
                                <th>{{ trans('manage_time::view.End date') }}</th>
                                <th>{{ trans('manage_time::view.Update') }}</th>
                                <th>{{ trans('manage_time::view.Application status') }}</th>
                                <th class="status">
                                    {{ trans('manage_time::view.Status') }}
                                    <span class="fa fa-question-circle display-non-mobile" data-toggle="tooltip" title="" data-html="true" data-original-title="{{ trans('manage_time::view.circle-status-lh') }}"></span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $i = 0;
                                $type = '';
                            ?>
                            @foreach($items['app'] as $item)
                                <tr class="@if ($item['deletedAt']) color-red @endif">
                                    <td class="display-non-mobile"></td>
                                    <td>{{ ++$i }}</td>
                                    @if ($type != $item['type'])
                                        <td class="with-260" style="color: black">{{ $arrLabelType[$item['type']] }}</td>
                                    @else
                                        <td class="with-260"></td>
                                    @endif
                                     <td>{{ $item['id'] }}</td>
                                    <td>{{ $item['startAt'] }}</td>
                                    <td>{{ $item['endAt'] }}</td>
                                    <td>{{ $item['updatedAt'] }}</td>
                                    <td>{{ $arrLabelApp[$item['statusApp']] }}</td>
                                    <td>
                                        @if ($item['status'] == TimekeepingLockHistories::STATUS_NOT_UPDATE)
                                            {{ $arrLabelStatus[$item['status']] }}
                                        @else
                                            <span class="label label-success label-status-lock-h">{{ $item['updatedStatus'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <?php
                                    $type = $item['type']
                                ?>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
        @endif
    </div>
</div>
@endsection

@section('script-manage')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
@endsection