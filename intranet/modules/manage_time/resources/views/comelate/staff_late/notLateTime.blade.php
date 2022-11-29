@extends('layouts.default')

@section('title')
    {{ trans('manage_time::view.Employee managers do not go late') }}
@endsection

<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View;
use Rikkei\Core\View\Form;

    $rowNotLateTime = view('manage_time::comelate.renderRowNotLateTime')->render();
    $replace = array(
        '/<!--[^\[](.*?)[^\]]-->/s' => '',
        "/<\?php/"                  => '<?php ',
        "/\n([\S])/"                => '$1',
        "/\r/"                      => '',
        "/\n/"                      => '',
        "/\t/"                      => '',
        "/ +/"                      => ' ',
    );
    $rowNotLateTime = preg_replace(array_keys($replace), array_values($replace), $rowNotLateTime);
    $j = 0;
?>

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<style>
    .select2 {
        width: 100% !important;
    }
    .mt-5 {
        margin-top: 5px;
    }
    .filter {
        margin: 0px 20px;
    }
</style>
@endsection

@section('content')
<div class="hidden">
    <select name="empids" class="form-control select-search-employee "
        data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
    </select>
</div>
<div class="row staff-late">
    <div class="col-md-3">
        @include('manage_time::comelate.staff_late.menu_left')
    </div>
    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-header text-center">
                <h3>{{ trans('manage_time::view.List of employees allowed to late in the period') }}</h3>
            </div>
            <!-- /.box-header -->
           <div class="filter">
            @include('team::include.filter')
           </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered table-hover table-grid-data" id="not-late-time">
                    <thead>
                        <tr class="info">
                            <th class="text-center">STT</th>
                            <th>{{ trans('manage_time::view.Employee email') }}</th>
                            <th>{{ trans('ot::view.Employee Name') }}</th>
                            <th>{{ trans('manage_time::view.From date') }}</th>
                            <th>{{ trans('manage_time::view.End date') }}</th>
                            <th>{{ trans('manage_time::view.Late minutes') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" class='form-control filter-grid' name="filter[emp.email]" value="{{ Form::getFilterData('emp.email') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" class='form-control filter-grid' name="filter[emp.name]" value="{{ Form::getFilterData('emp.name') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $startDateFilter = Form::getFilterData("timekeeping_not_late_time.start_date");
                                        ?>
                                        <input type="text" name="filter[timekeeping_not_late_time.start_date]" value='{{ $startDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $endDateFilter = Form::getFilterData("timekeeping_not_late_time.end_date");
                                        ?>
                                        <input type="text" name="filter[timekeeping_not_late_time.end_date]" value='{{ $endDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @if(isset($collectionModel) && $collectionModel)
                        <?php $j = View::getNoStartGrid($collectionModel); ?>
                        @foreach($collectionModel as $item)
                        <tr data-row-id="{{$item->id}}">
                            <td class="text-center">{{ $j++ }}</td>
                            <td>
                                <div class="text txt-emp_email">{{ $item->emp_email }}</div>
                                <div class="input email hidden"></div>
                            </td>
                            <td>
                                <div class="text txt-emp_name">{{ $item->emp_name }}</div>
                                <div class="input name hidden">
                                    <select name="empid" class="form-control select-search-employee f-input"
                                        required="required" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}
                                ">
                                        <option value="{{ $item->emp_id }}"> {{ $item->emp_name }}</option>
                                    </select>
                                    <div class="error hidden empid-error"></div>
                                </div>
                            </td>
                            <td>
                                <div class="text txt-start_date">{{ $item->start_date }}</div>
                                <div class="input start hidden">
                                    <div class="input-group date datepicker" data-provide="datepicker">
                                        <input type="text" name='startDate' class="form-control f-input"
                                            required="required" autocomplete="off" value="{{ $item->start_date }}">
                                        <div class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </div>
                                    </div>
                                    <div class="error hidden startDate-error"></div>
                                </div>
                            </td>
                            <td>
                                <div class="text txt-end_date">{{ $item->end_date }}</div>
                                <div class="input end hidden">
                                    <div class="input-group date datepicker" data-provide="datepicker">
                                        <input type="text" name='endDate' class="form-control f-input "
                                            value="{{ $item->end_date }}" required="required" autocomplete="off">
                                        <div class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </div>
                                    </div>
                                    <div class="error hidden endDate-error"></div>
                                </div>
                            </td>
                            <td>
                                <div class="text txt-minute">{{ $item->minute }}</div>
                                <div class="input minute hidden">
                                    <input type="number" min="0" name="minute" max="119" class="form-control f-input"
                                        required="required" value="{{ $item->minute }}">
                                    <div class="error hidden minute-error"></div>
                                </div>
                            </td>
                            <td>
                                <div class="text">
                                    <button class="btn btn-primary btn-ss-action" data-btn-action="edit"
                                        type="button"><i class="fa fa-pencil"></i></button>
                                    <button class="btn btn-danger btn-ss-action" data-btn-action="delete" type="button"
                                        data-url="{{ route('manage_time::admin.staff-late.delete-not-late-time') }}"><i
                                            class="fa fa-trash"></i></button>
                                </div>
                                <div class="input hidden">
                                    <button class="btn btn-success btn-ss-action" data-btn-action="update" type="button"
                                        data-url="{{ route('manage_time::admin.staff-late.update-not-late-time') }}">
                                        <i class="fa fa-floppy-o"><i class="fa fa-refresh fa-spin margin-left-10 hidden" hiddenaria-hidden="true">
                                        </i></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
                <button class="btn-add mt-5" data-table-id="not-late-time" data-table-row="notLateRow">
                    <i class="fa fa-plus"></i></button>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                @include('team::include.pager')
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script>
        var token = '{{ csrf_token() }}';
        var numberj = '{{ $j }}';
        var rowNotLateTime = '<?php echo($rowNotLateTime) ?>';
        var urCreateNotLateTime = "{{ route('manage_time::admin.staff-late.create-not-late-time') }}";
        var urUpdateNotLateTime = "{{ route('manage_time::admin.staff-late.update-not-late-time') }}";
        var messageDelete = '<?php echo trans('manage_time::message.Are you sure you want to delete') ?>';
        var mesStartLessEnd = "{{ trans('ot::message.Time start less than time end.') }}";
    </script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/admin_comelate.js') }}"></script>
    <script>
                    $('.filter-date').datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy',
                weekStart: 1,
                todayHighlight: true
            });
    </script>
@endsection
