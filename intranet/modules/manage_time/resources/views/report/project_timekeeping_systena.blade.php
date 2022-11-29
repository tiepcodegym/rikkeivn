@extends('manage_time::layout.manage_layout')

@section('title-manage')
{{ trans('manage_time::view.Follow timekeeping system') }} 
@endsection

<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Carbon\Carbon;
    use Rikkei\Project\Model\Project;
    
    if (!$month) {
        $month = Carbon::now()->startOfMonth();
    } else {
        $month = Carbon::createFromFormat('Y-m-d', $month . '-01');
        $date = $month->format("Y-m-d");
    }
    $arrayMonths = [
        'prev' => $month->subMonthNoOverflow()->format('Y-m'),
        'current' => $month->addMonthNoOverflow()->format('Y-m'),
        'next' => $month->addMonthNoOverflow()->format('Y-m') <= $monthNow ? $month->format('Y-m') : null
    ];

    $urlIndex = route('manage_time::timekeeping.manage.report_project_timekeeping_systena');
    $urlExport = route('manage_time::timekeeping.manage.export_project_timekeeping_systena');
    $urlSearchRelatedPerson = route('manage_time::profile.leave.find-employee');
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/day_list.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}" />
@endsection

@section('content')
    @if(Session::has('flash_success'))
        <div class="alert alert-success">
            <ul>
                <li>
                    {{ Session::get('flash_success') }}
                </li>
            </ul>
        </div>
    @endif
    <div class="box box-info">
        <div class="box-body">
            <button id="btn_add_emp_proj" class="btn btn-success" data-toggle="modal" data-target="#exampleModalCenter"><i class="fa fa-plus" aria-hidden="true"></i> {{ trans('manage_time::view.Add new') }}</button>
            <div class="pull-right">
                <ul class="list-inline">
                    <li class="list-inline-item">@include('manage_time::leave.manage.leaveday-month-select')</li>
                    <li class="list-inline-item">
                       <button class="btn btn-primary btn-report btn-export" style="margin-top: -5px; color: weith">{{ trans('manage_time::view.Export') }}
                        <i class="fa fa-spin fa-refresh hidden" style="color: white"></i>
                       </button>
                    </li>
                    <li class="list-inline-item"><button class="btn btn-primary btn-reset-filter"><span>{{ trans('manage_time::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span></button></li>
                </ul>
            </div>
        </div>
         <div class="table-responsive managetime-form-group">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table_systena">
                <thead>
                    <tr>
                        <th class="width-40" rowspan="1" colspan="1">
                            <input type="checkbox" value="0" id="check-all">
                            <label class="control-label required"><span class="fa fa-question-circle" data-toggle="tooltip" title="{!! trans('manage_time::view.Tooltip checkbox projex systena') !!}" data-html="true" ></span></label>
                        </th>
                        <th class="col-id width-10" style="width: 20px;">{{ trans('manage_time::view.No.') }}</th>
                        <th>{{ trans('manage_time::view.Employee code') }}</th>
                        <th>{{ trans('manage_time::view.Employee name') }}</th>
                        <th>{{ trans('manage_time::view.Date time start employeee') }}</th>
                        <th>{{ trans('manage_time::view.Date time end employeee') }}</th>
                        <th>{{ trans('manage_time::view.Name project') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="checkbox-body">
                    @if(isset($collectionModel) && count($collectionModel))
                        <?php 
                            $i = CoreView::getNoStartGrid($collectionModel);
                        ?>
                        @foreach($collectionModel as $item)
                            @if ($item->projId != 0 && $item->projName != 1)
                                <tr class="member-data">
                                    <td>
                                        <input type="checkbox" value="{{ $item ->empId }}">
                                    </td>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $item->empCode }}</td>
                                    <td>{{ $item->empName }}</td>
                                    <td>{{ Carbon::parse($item->empStart)->format('d/m/Y') }}</td>
                                    <td>{{ Carbon::parse($item->empEnd)->format('d/m/Y') }}</td>
                                    <td>{{ $item->projName }}</td>
                                    <td></td>
                                </tr>
                                <?php
                                    $arrProjInfor = Project:: cutStringProjSystena($item->projInfor);
                                ?>
                                @if (count($arrProjInfor))
                                    @foreach($arrProjInfor as $infor)
                                        @if ($infor[0] != $item->projId)
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td>{{ Carbon::parse($infor[1])->format('d/m/Y') }}</td>
                                                <td>{{ Carbon::parse($infor[2])->format('d/m/Y') }}</td>
                                                <td>{{ $infor[3] }}</td>
                                                <td></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            @else
                                <tr>
                                    <td>
                                        <input type="checkbox" value="{{ $item ->empId }}">
                                    </td>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $item->empCode }}</td>
                                    <td>{{ $item->empName }}</td>
                                    <td>__/__/____</td>
                                    <td>__/__/____</td>
                                    <td>_________</td>
                                    <td>
                                        <form action="{{URL::route('manage_time::timekeeping.manage.delete_project_systena')}}" method="post" class="form-inline">
                                            {!! csrf_field() !!}
                                            <input type="hidden" name="id" value="{{ $item->projMemId }}" />
                                            <button href="" class="btn-delete delete-confirm" title="{{ trans('manage_time::view.Delete') }}" disabled>
                                                <span><i class="fa fa-trash"></i></span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                            @endif
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <div class="box-body">
            @include('resource::recruit.paginate')
        </div>
    </div>
     <!-- modal form to add and edit reason -->
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title">{{ trans('manage_time::view.Add employee') }}</h5>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{URL::route('manage_time::timekeeping.manage.add_project_systena')}}" id="form-systena">
                    {{ csrf_field() }}
                        <div class="row">
                            <div class="col-sm-12 managetime-form-group">
                                <label class="control-label required">{{ trans('manage_time::view.Choose employee') }} <em>*</em></label>
                                <select name="related_persons_list[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple required>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-center">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('manage_time::view.Close' )}}</button>
                        <button type="submit" class="btn btn-primary">{{ trans('manage_time::view.Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript">
        var urlIndex = '{{ $urlIndex }}';
        var urlExport = "{{ $urlExport }}";
        var urlSearchRelatedPerson = '{{ $urlSearchRelatedPerson }}';
        var page = "{{ $collectionModel->currentPage() }}";
        var maxPage = "{{ ceil($collectionModel->total() / $collectionModel->perpage()) }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.shorten.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script src="{{asset('asset_managetime/js/project_timekeeping.js')}}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
    <script type="text/javascript">
        $('.select-search-employee').select2({
            ajax: {
                url: urlSearchRelatedPerson,
                dataType: "JSON",
                data: function (params) {
                    return {
                        q: $.trim(params.term)
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
        $('.btn-export').on('click', function () {
            $(this).find('i.fa-refresh').removeClass('hidden');
            var empIds = '';
            for(var i = 1; i <= maxPage; i++) {
                empIds = empIds + ',' + accessCookie('empId' + i);
            }
            $.ajax ({
                url: "{{ $urlExport }}",
                method: "POST",
                dataType: "JSON",
                data:  {
                    "_token": "{{ csrf_token() }}",
                    "empIds": empIds,
                    "month": "{{ $monthDay }}",
                },
                success: function(data) {
                    $('.btn-export').find('i.fa-refresh').addClass('hidden');
                    if (data.success == 1) {
                            bootbox.alert({
                            message: data.message,
                            className: 'modal-success',
                        });
                    } else {
                        bootbox.alert({
                            message: data.message,
                            className: 'modal-danger',
                        });
                    }
                },
                error: function (error) {
                    $('.btn-export').find('i.fa-refresh').addClass('hidden');
                    bootbox.alert({
                        message: error.responseJSON,
                        className: 'modal-danger',
                    });
                },
            });
        });
    </script>
@endsection
