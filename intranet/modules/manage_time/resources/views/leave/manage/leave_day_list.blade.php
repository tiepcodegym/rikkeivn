@extends('layouts.default')

@section('title')
    {{trans('manage_time::view.Day manage')}}
@endsection

<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\ManageTime\View\LeaveDayPermission;
    use Rikkei\ManageTime\View\View;
    use Carbon\Carbon;

    $urlDelete = route('manage_time::admin.manage-day-of-leave.delete');
    if(isset($collectionModel) && count($collectionModel)) {
        $buttonAction['delete'] = [
            'label' => 'Delete data', 
            'class' => 'btn btn-primary btn-delete-data margin-bottom-5',
            'disabled' => true,
        ]; 
    }
    $buttonAction['export'] = [
        'label' => 'Export file', 
        'class' => 'btn btn-primary export_file margin-bottom-5',
        'disabled' => false, 
        'url'=> URL::route('manage_time::admin.manage-day-of-leave.export') . '?month='.request()->get('month'),
        'type' => 'link'
    ];

    if (!$month) {
        $month = Carbon::now()->startOfMonth();
    } else {
        $month = Carbon::createFromFormat('Y-m-d', $month . '-01');
    }
    $arrayMonths = [
        'prev' => $month->subMonthNoOverflow()->format('Y-m'),
        'current' => $month->addMonthNoOverflow()->format('Y-m'),
        'next' => $month->addMonthNoOverflow()->format('Y-m') <= $monthNow ? $month->format('Y-m') : null
    ];

    $urlIndex = route('manage_time::admin.manage-day-of-leave.index');
    $tblFilter = View::getFilterLeaveDayTable();
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/day_list.css') }}" />
    <style>
        .filter-action .fa-refresh {
            color: white;
        }
    </style>
@endsection

@section('content')
    @if (LeaveDayPermission::isAllowViewHistories())
    <div class="pull-right">
        <a class="text-muted" href="{{ route('manage_time::admin.manage-day-of-leave.histories') }}">
            <i class="fa  fa-history"></i>
            {{ trans('manage_time::view.Leave day histories changes') }}
        </a>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-12">
            @if(Session::has('flash_success'))
                <div class="alert alert-success">
                    <ul>
                        <li>
                            {{ Session::get('flash_success') }}
                        </li>
                    </ul>
                </div>
            @endif
            @if(Session::has('flash_error'))
                <div class="alert alert-warning not-found">
                    <ul>
                        <li>
                            {{ Session::get('flash_error') }}
                        </li>
                    </ul>
                </div>
            @endif
            <div class="box box-info">
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-4 margin-bottom-5">
                            <input type="file" id="upload" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" url="{{ route('manage_time::admin.manage-day-of-leave.import') }}" />
                            <i class="fa fa-refresh fa-spin hidden"></i>
                        </div>
                        <div class="col-sm-8 row-filters text-right">
                            @include('manage_time::leave.manage.leaveday-month-select', ['class' => 'margin-right-20'])
                            @include('team::include.filter', ['domainTrans' => 'manage_time', 'buttons' => $buttonAction])
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table-day">
                        <thead>
                            <tr>
                                <th class="checkbox-all" style="padding-right: 0px;">
                                    @if(isset($collectionModel) && count($collectionModel))
                                        <input type="checkbox" class="minimal" name="" value="">
                                    @endif
                                </th>
                                <th class="col-id width-10" style="width: 20px;">{{ trans('manage_time::view.No.') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('employee_code') }} col-employee_code" data-order="employee_code" data-dir="{{ TeamConfig::getDirOrder('employee_code') }}">{{ trans('manage_time::view.Employee code') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('name') }} col-name" data-order="name" data-dir="{{ TeamConfig::getDirOrder('name') }}" style="min-width: 80px;">{{ trans('manage_time::view.Employee fullname') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_last_year') }} col-day_last_year" data-order="day_last_year" data-dir="{{ TeamConfig::getDirOrder('day_last_year') }}">{{ trans('manage_time::view.Number day last year') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_last_transfer') }} col-day_last_transfer" data-order="day_last_transfer" data-dir="{{ TeamConfig::getDirOrder('day_last_transfer') }}">{{ trans('manage_time::view.Number day last year use') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_current_year') }} col-day_current_year" data-order="day_current_year" data-dir="{{ TeamConfig::getDirOrder('day_current_year') }}">{{ trans('manage_time::view.Number day current year') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_seniority') }} col-day_seniority" data-order="day_seniority" data-dir="{{ TeamConfig::getDirOrder('day_seniority') }}">{{ trans('manage_time::view.Number day seniority') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_OT') }} col-day_OT" data-order="day_OT" data-dir="{{ TeamConfig::getDirOrder('day_OT') }}">{{ trans('manage_time::view.Number day OT') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('total_day') }} col-total_day" data-order="total_day" data-dir="{{ TeamConfig::getDirOrder('total_day') }}">{{ trans('manage_time::view.Total number day') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('day_used') }} col-day_used" data-order="day_used" data-dir="{{ TeamConfig::getDirOrder('day_used') }}">{{ trans('manage_time::view.Number day used') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('remain_day') }} col-remain_day" data-order="remain_day" data-dir="{{ TeamConfig::getDirOrder('remain_day') }}">{{ trans('manage_time::view.Number day remain') }}</th>

                                <th class="sorting {{ TeamConfig::getDirClass('note') }} col-note" data-order="note" data-dir="{{ TeamConfig::getDirOrder('note') }}" style="min-width: 120px;">{{ trans('manage_time::view.Number day note') }}</th>

                                @if (!$isBaseline)
                                <th width="85" style="min-width: 85px;">&nbsp;</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="checkbox-body">
                            <tr class="filter-input-grid">
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[employees.employee_code]" value="{{ CoreForm::getFilterData('employees.employee_code') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[employees.name]" value="{{ CoreForm::getFilterData('employees.name') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_last_year]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_last_year') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_last_transfer]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_last_transfer') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_current_year]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_current_year') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_seniority]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_seniority') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_ot]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_ot') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[spec][total_day]" value="{{ CoreForm::getFilterData('spec','total_day') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[number][{{ $leaveDayTbl }}.day_used]" value="{{ CoreForm::getFilterData('number', $tblFilter . '.day_used') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[spec][remain_day]" value="{{ CoreForm::getFilterData('spec','remain_day') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[{{ $leaveDayTbl }}.note]" value="{{ CoreForm::getFilterData( $tblFilter . '.note') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                @if (!$isBaseline)
                                <td class="td-action">&nbsp;</td>
                                @endif
                            </tr>
                            @if(isset($collectionModel) && count($collectionModel))
                                <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                                @foreach($collectionModel as $item)
                                    <tr day-id="{{$item->id}}" class="reason-data">
                                        <td class="text-center"><input type="checkbox" class="minimal" name="" value="{{ $item->id }}"></td>
                                        <td>{{ $i }}</td>
                                        @if ($item->employee_code)
                                            <td class="employee_code">{{$item->employee_code}}</td>
                                        @else
                                            <td>&nbsp;</td>
                                        @endif
                                        @if ($item->name)
                                            <td class="full_name">{{$item->name}}</td>
                                        @else
                                            <td>&nbsp;</td>
                                        @endif
                                        <td class="day_last_year">{{$item->day_last_year}}</td>
                                        <td class="day_last_transfer">{{$item->day_last_transfer}}</td>
                                        <td class="day_current_year">{{$item->day_current_year}}</td>
                                        <td class="day_seniority">{{$item->day_seniority}}</td>
                                        <td class="day_OT">{{$item->day_ot}}</td>
                                        <td class="total_day">{{$item->total_day}}</td>
                                        <td class="day_used">{{$item->day_used}}</td>
                                        <td>{{$item->remain_day}}</td>
                                        <td class="note">{{$item->note}}</td>
                                        @if (!$isBaseline)
                                        <td class="align-center">
                                            <button class="btn-edit reason-edit" reason_id="{{$item->id}}">
                                                <i class="fa fa-pencil-square-o" aria-hidden="true" ></i>
                                            </button>
                                            <button class="btn btn-danger button-delete managetime-margin-bottom-5" value="{{ $item->id }}" title="{{ trans('manage_time::view.Delete') }}" data-toggle="modal">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        </td>
                                        @endif
                                    </tr>
                                    <?php $i++; ?>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="{{ $isBaseline ? 13 : 12 }}" class="text-center">
                                        <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="box-body">
                    @include('team::include.pager')
                </div>
            </div>
            <div class="box box-info">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>{{ trans('manage_time::view.Format upload leave day file') }}</h4>
                            <img src="{{ URL::asset('asset_managetime/images/template/ngay_phep.png') }}" class="img-responsive" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete modal -->
        @include('manage_time::include.modal.modal_delete')

        <!-- No select modal -->
        @include('manage_time::include.modal.modal_noselect')

        <!-- Modal edit infor day -->
        <div id="modal_edit_leave_day" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">{{trans('manage_time::view.Edit infor day')}}</h4>
                    </div>
                    <form method="POST" action="{{ route('manage_time::admin.manage-day-of-leave.edit') }}" id="form-submit-day">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">{{ trans('manage_time::view.Employee fullname') }}</label>
                                        <input disabled="true" value="Nghiêm Trường Giang" class="form-control" id="full_name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">{{ trans('manage_time::view.Employee code') }}</label>
                                        <input disabled="true" value="Nghiêm Trường Giang" class="form-control" id="employee_code">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="day_last_year">{{ trans('manage_time::view.Number day last year') }}</label>
                                        <input name="day_last_year" class="form-control" id="day_last_year">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="day_last_transfer">{{ trans('manage_time::view.Number day last year use') }}</label>
                                        <input name="day_last_transfer" class="form-control" id="day_last_transfer">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="day_current_year">{{ trans('manage_time::view.Number day current year') }}</label>
                                        <input name="day_current_year" class="form-control" id="day_current_year">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="day_seniority">{{ trans('manage_time::view.Number day seniority') }}</label>
                                        <input name="day_seniority" class="form-control" id="day_seniority">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="day_OT">{{ trans('manage_time::view.Number day OT') }}</label>
                                        <input name="day_OT" class="form-control" id="day_OT">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="day_used">{{ trans('manage_time::view.Number day used') }}</label>
                                        <input name="day_used" class="form-control" id="day_used">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="note">{{ trans('manage_time::view.Number day note') }}</label>
                                        <textarea class="form-control" name="note" id="note"></textarea>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="id" id="day_id" value="">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="add_submit">{{ trans('manage_time::view.Save') }}</button>
                            <button type="button" class="btn btn-default pull-left" id="close_form" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/leave_day.js') }}"></script>
    <script type="text/javascript">
        var urlDelete = '{{ $urlDelete }}';
        var urlIndex = '{{ $urlIndex }}';
        jQuery(document).ready(function ($) {
            selectSearchReload();
            RKfuncion.general.initDateTimePicker();
            $('.input-datepicker').on('dp.change', function () {
                window.location.href = urlIndex + '?month=' + $(this).val();
            });
        });

        $('.btn-reset-filter').click(function () {
            var location = window.location;
            window.history.pushState({}, document.title, location.origin + location.pathname);
        });
    </script>
@endsection

