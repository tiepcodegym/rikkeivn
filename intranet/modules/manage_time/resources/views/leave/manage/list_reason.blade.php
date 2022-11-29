@extends('layouts.default')

@section('title')
    {{trans('manage_time::view.List reason')}}
@endsection
    <?php
        use Rikkei\Core\View\CoreUrl;
        use Rikkei\Team\View\Config as TeamConfig;
        use Rikkei\Core\View\View as CoreView;
        use Rikkei\Core\View\Form as CoreForm;
    ?>
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/reason_list.css') }}">
@endsection

@section('content')
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
                    <div class="col-sm-8">
                        <button id="btn_add_reason" class="btn btn-success"><i class="fa fa-plus" aria-hidden="true"></i> {{ trans('manage_time::view.Add new') }}</button>
                    </div>
                    <div class="col-sm-4">
                        @include('manage_time::include.filter')
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table-reason">
                        <thead>
                            <tr>
                                <th class="col-id width-10" style="width: 20px;">{{ trans('manage_time::view.No.') }}</th>
                                <th class="sorting {{ TeamConfig::getDirClass('name') }} col-name" data-order="name" data-dir="{{ TeamConfig::getDirOrder('name') }}">{{ trans('manage_time::view.Name') }}</th>
                                <th class="sorting {{ TeamConfig::getDirClass('sort_order') }} col-sort_order" data-order="sort_order" data-dir="{{ TeamConfig::getDirOrder('sort_order') }}">{{ trans('manage_time::view.Reason sort order') }}</th>
                                <th class="sorting {{ TeamConfig::getDirClass('salary_date') }} col-salary_date" data-order="salary_date" data-dir="{{ TeamConfig::getDirOrder('salary_date') }}">{{ trans('manage_time::view.Salary rate (%)') }}</th>
                                <th class="sorting {{ TeamConfig::getDirClass('used_leave_day') }} col-used_leave_day" data-order="used_leave_day" data-dir="{{ TeamConfig::getDirOrder('used_leave_day') }}">{{ trans('manage_time::view.Used leave day') }}</th>
                                <th class="managetime-col-85">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="filter-input-grid">
                                <td>&nbsp;</td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[name]" value="{{ CoreForm::getFilterData('name') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[sort_order]" value="{{ CoreForm::getFilterData('sort_order') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[number][salary_rate]" value="{{ CoreForm::getFilterData('number','salary_rate') }}" placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            @if(isset($collectionModel) && count($collectionModel))
                                <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                                @foreach($collectionModel as $item)
                                    <?php 
                                        $usedLeaveDay = trans('manage_time::view.No, used leave day');
                                        if ($item->used_leave_day) {
                                            $usedLeaveDay = trans('manage_time::view.Yes, used leave day');
                                        }
                                    ?>
                                    <tr reason-id="{{ $item->id }}" reason-name="{{ $item->name }}" reason-order="{{ $item->sort_order }}" reason-salary="{{ $item->salary_rate }}" reason-used-leave-day="{{ $item->used_leave_day }}" class="reason-data">
                                        <td>{{ $i }}</td>
                                        @if ($item->name)
                                            <td>{{$item->name}}</td>
                                        @else
                                            <td>&nbsp;</td>
                                        @endif
                                        <td>{{ $item->sort_order }}</td>
                                        <td>{{ $item->salary_rate }}</td>
                                        <td>{{ $usedLeaveDay }}</td>
                                        <td class="align-center td-button">
                                            <button class="btn-edit reason-edit" reason_id="{{$item->id}}">
                                                <i class="fa fa-pencil-square-o" aria-hidden="true" ></i>
                                            </button>
                                            <form class="form-delete" action="{{URL::route('manage_time::admin.manage-reason-leave.delete',$item->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                <button class="btn-delete delete-confirm" type="submit"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php $i++; ?>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">
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
        </div>

        <!-- modal form to add and edit reason -->
        <div id="modal-create-edit" class="modal fade" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ trans('manage_time::view.Add reason for taking a leave') }}</h4>
                    </div>
                    <form method="POST" action="{{URL::route('manage_time::admin.manage-reason-leave.save')}}" id="form-submit-reason">
                        {{ csrf_field() }}
                        <div class="modal-body">
                            <div class="row form-group">
                                <div class="col-md-3">
                                    <label for="name">{{ trans('manage_time::view.Name') }} <em>*</em></label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" name="name" class="form-control" id="name" check-name="{{URL::route('manage_time::admin.manage-reason-leave.check-name')}}">
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-md-3">
                                    <label for="sort_order">{{ trans('manage_time::view.Reason sort order') }}</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" name="sort_order" class="form-control" id="sort_order">
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-md-3">
                                    <label for="salary_rate">{{ trans('manage_time::view.Salary rate (%)') }}</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" name="salary_rate" class="form-control" id="salary_rate">
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-md-3">
                                    <label for="used_leave_day">{{ trans('manage_time::view.Used leave day') }}</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="form-control managetime-select-2" name="used_leave_day" id="used_leave_day">
                                        <option value="0">{{ trans('manage_time::view.No, used leave day') }}</option>
                                        <option value="1">{{ trans('manage_time::view.Yes, used leave day') }}</option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="id" id="reason_id" value="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" id="close_form">{{ trans('manage_time::view.Close') }}</button>
                            <button type="submit" class="btn btn-primary" id="add_submit">{{ trans('manage_time::view.Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var titleEditReson = '<?php echo trans('manage_time::view.Edit reason for taking a leave') ?>';
        var labelSave = '{{ trans('manage_time::view.Save') }}';
        var titleAddReason = '{{ trans('manage_time::view.Add reason for taking a leave') }}';
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/leave.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            selectSearchReload();
        });
    </script>
@endsection

