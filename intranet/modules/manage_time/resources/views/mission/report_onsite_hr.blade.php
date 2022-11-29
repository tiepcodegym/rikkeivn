@extends('layouts.default')

@section('title')
    {{ trans('manage_time::view.List employee onsite') }}
@endsection

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\Form;
    use Rikkei\ManageTime\View\View as ManageTimeView;

    $objView = new ManageTimeView();
    $stt = ($emloyeeOnsite['current_page'] - 1) * $emloyeeOnsite['per_page'];
    $tbl = 'report_business_onsite';
    $current_url = URL::current();
    $now = Carbon::now();
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <style>
        .select2-dropdown {
            width: max-content !important;
            border-top: 1px solid #aaa !important;
        }
        .inline-block {
            display: inline-block;
            margin-right: 20px
        }
        .footer-body {
            margin-left: 15px;
            margin-right: 15px;
        }
        .col-w-100 {
            width: 100px;
        }
        @media (min-width: 768px) {     
            #modalMessage .modal-content {
                margin-top: 80%
            }
        }
        .bg_gratefuled {
            background-color: #2574e973 !important;
        }
        .tooltip .tooltip-inner {
            max-width: 400px!important;
            text-align: left!important;
            background-color: #3C8DBC!important;
            padding-top: 15px!important;
            padding-left: 0!important;
        }
        .tooltip .tooltip-inner ul {
            padding-left: 25px
        }
    </style>
@endsection

@section('content')
    <div class="box box-primary">
        <div class="box-header">
            <div class="row">
                <div class="col-md-8">
                    <div>
                        <div class="form-group inline-block">
                            <label>{{trans('team::view.From date')}}</label>
                            <input type="text" name="filter[except][tbl.date_start]" value='{{ $startDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date search-change" autocomplete="off" />
                        </div>
                        <div class="form-group inline-block">
                            <label> {{trans('team::view.To date')}} </label>
                            <input type="text" name="filter[except][tbl.date_end]" value='{{ $endDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date search-change" autocomplete="off" />
                        </div>
                        <div class="form-group inline-block">
                            <label> {{ trans('manage_time::view.Year number') }} </label>
                            <span class="fa fa-question-circle tooltip-leave" data-toggle="tooltip" title="" data-html="true" data-original-title="<ul>
                                <li>Tìm kiếm số năm đã đi onsite</li>
                                <li>1 năm, 2 năm, 3 năm ...</li>
                            </ul>"></span>
                            <input type="text" name="filter[except][tbl.year]" value='{{ $year }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                    <div>
                        <i>Khoảng cách ngày tìm kiếm: {{ $diffDay }} ngày</i>
                        <p><strong>Chú thích:</strong></p>
                        <ul>
                            <li>
                                <span style="width: 20px; height: 20px; display: inline-block;" class="bg_gratefuled"></span>
                                Dòng nhân viên đã được tri ân
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-right member-group-btn">      
                        <div class="pull-right">
                            <a href="{{ route('manage_time::hr.export-onsite') }}" class="btn btn-primary managetime-margin-bottom-5">
                                <span>Export <i class="fa fa-spin fa-refresh hidden"></i></span>
                            </a>
                            <button type="button" class="btn btn-info" id="btn-showmodal">Tri ân</button>
                            <button type="button" class="btn btn-danger" id="btn-showmodal-remove">Bỏ tri ân</button>
                            @include('team::include.filter')
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-body">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data  managetime-table-control supplement-list-tbl" id="managetime_table_primary">
                <thead class="managetime-thead">
                    <tr class="info">
                        <th><input type="checkbox" class="check-all" id="tbl_check_all" data-list=".table-check-list"></th>

                        <th class="managetime-col-25 col-no" style="min-width: 25px;">{{ trans('manage_time::view.No.') }}</th>

                        <th class="col-title col-w-100 col-employee-code">{{ trans('manage_time::view.Employee code') }}</th>

                        <th class="col-title col-employee-name">{{ trans('manage_time::view.Employee name') }}</th>

                        <th class="col-title col-employee-email" >{{ trans('manage_time::view.Employee email')}}</th>

                        <th class="col-title col-department">{{ trans('manage_time::view.Department') }}</th>

                        <th class="col-title col-start-date">{{ trans('manage_time::view.Start date') }}</th>

                        <th class="col-title col-end-date">{{ trans('manage_time::view.End date') }}</th>

                        <th class="col-title col-address">{{ trans('asset::view.Address') }}</th>

                        <th class="col-title  col-company">{{ trans('project::view.Company') }}</th>

                        <th class="col-title  col-customer">{{ trans('project::view.Customer') }}</th>

                        <th class=" col-title col-sales-manages">{{ trans('manage_time::view.Sales manage') }}</th>

                        <th class=" col-title  col-day-onsite text-center">{{ trans('manage_time::view.Day number onsite') }}</th>

                        <th class=" col-title col-year text-center">{{ trans('manage_time::view.Year number') }}</th>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>
                            <input type="text" name="filter[{{$tbl}}.employee_code]" value='{{ Form::getFilterData("{$tbl}.employee_code") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </td>
                        <td>
                            <input type="text" name="filter[{{$tbl}}.name]" value='{{ Form::getFilterData("{$tbl}.name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </td>
                        <td>
                            <input type="text" name="filter[{{$tbl}}.email]" value='{{ Form::getFilterData("{$tbl}.email") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </td>
                        <td>
                            <?php
                                $teamFilter = Form::getFilterData("{$tbl}.team_id");
                            ?>
                            <select style="width: 100%;" name="filter[{{ $tbl }}.team_id]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                <option value="">&nbsp;</option>
                                @foreach($teamsOptionAll as $option)
                                    <option value="{{ $option['value'] }}"<?php
                                        if ($option['value'] == $teamFilter): ?> selected<?php endif; 
                                            ?>>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>
                            <input type="text" name="filter[{{$tbl}}.location]" value='{{ Form::getFilterData("{$tbl}.location") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </td>
                        <td>
                            <input type="text" name="filter[{{$tbl}}.company_name]" value='{{ Form::getFilterData("{$tbl}.company_name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </td>
                        <td>
                            <input type="text" name="filter[{{$tbl}}.contacts_name]" value='{{ Form::getFilterData("{$tbl}.contacts_name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </td>
                        <td>
                            <input type="text" name="filter[{{$tbl}}.sale_name]" value='{{ Form::getFilterData("{$tbl}.sale_name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </td>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($emloyeeOnsite['data']) && count($emloyeeOnsite['data']))
                        @foreach ($emloyeeOnsite['data'] as $key => $item)
                            <?php
                                $viewYear = $objView->getYearByDay($item->onsite_days, $arrYear);
                                $key = $item->employee_id . '_' . $viewYear;
                            ?>
                            <tr data-tr-row="{{$key}}" class="
                                @if(isset($dataEmpGrateful[$item->employee_id]) && isset($dataEmpGrateful[$item->employee_id][$viewYear]))
                                    bg_gratefuled
                                @endif
                            ">
                                <td><input type="checkbox" class="check-item" value="{{$key}}"></td>
                                <td>{{ ++$stt }}</td>
                                <td class="col-mw-100">{{ $item->employee_code }}</td>
                                <td>{{ $item->employee_name }}</td>
                                <td>{{ $item->employee_email }}</td>
                                <td>{{ $item->team_name }}</td>
                                <td>{{ $item->start_at }}</td>
                                <td>{{ $item->end_at_now }}</td>
                                <td>{{ $item->location }}</td>
                                <td data-proj-id={{$item->proj_id}}>{{ $item->company_name }}</td>
                                <td>{{ $item->contacts_name }}</td>
                                <td>{{ $item->sale_employee }}</td>
                                <td class="text-center">{{ $item->onsite_days }}</td>
                                <td class="text-center">{{ $viewYear }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <div class="footer-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="pagination">
                        Tổng {{ $emloyeeOnsite['total'] }} đối tượng / {{ $emloyeeOnsite['last_page'] }} trang
                    </div>
                </div>
                <div class="col-md-6">
                    @if (isset($emloyeeOnsite['last_page']) && $emloyeeOnsite['last_page'] > 1)
                    <?php
                        $pre = $emloyeeOnsite['current_page'] > 1 ? ($emloyeeOnsite['current_page'] - 1) : 1;
                        $next = $emloyeeOnsite['current_page'] < $emloyeeOnsite['last_page'] ? ($emloyeeOnsite['current_page'] + 1) : $emloyeeOnsite['last_page']
                    ?>
                    <div class="text-right">
                        <nav aria-label="Page navigation example">
                            <ul class="pagination">
                            <li class="page-item">
                                <a class="page-link" href="{{$pre}}" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                                <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            @for ($i = 1; $i <= $emloyeeOnsite['last_page']; $i++)
                                <li class="page-item 
                                @if ($emloyeeOnsite['current_page'] == $i)
                                    active
                                @endif
                                "><a class="page-link" href="{{$i}}">{{$i}}</a></li>
                            @endfor
                            <li class="page-item">
                                <a class="page-link" href="{{$next}}" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                                <span class="sr-only">Next</span>
                                </a>
                            </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <button type="button" class="btn btn-info hidden" id="show-modalMessage" data-toggle="modal" data-target="#modalMessage">message</button>
    <button type="button" class="btn btn-info hidden" id="show-modalGrateful" data-toggle="modal" data-target="#modalGrateful">tri an</button>
    <!-- Modal -->
    <div id="modalGrateful" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Tri ân những nhân viên đã chọn</h4>
            </div>
            <div class="modal-body">
                <div class="error text-center" >
                    
                </div>
                <br>
                <form action="/action_page.php">
                    <div class="form-group modal-date">
                        <label>Chọn ngày tri ân:</label>
                        <input type="text" name="grateful_date" value='{{ $now->format('d-m-Y') }}' class="form-control filter-date" autocomplete="off" />
                    </div>
                    <div class="form-group">
                        <label>Ghi chú:</label>
                        <textarea name="grateful_note" id=""  class="form-control" rows="5"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default float-left" data-dismiss="modal">{{ trans('doc::view.Close')}}</button>
                <button type="button" class="btn btn-primary submit-grateful">{{ trans('doc::view.Save')}} <i class="fa fa-spin fa-refresh hidden"></i> </button>
            </div>
            </div>
        </div>
    </div>
    <div id="modalMessage" class="modal fade" role="dialog">
        <div class="modal-dialog modal-sm">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header alert-success">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="text-center" style="font-size:18px">
                        <p>Lưu thành công!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var urlPage = '{{ url('/') }}/' + 'grid/filter/pager';
        var currentUrl = '{{ $current_url }}/';
        var gratefulStore = "{{ route('manage_time::hr.grateful-store') }}";
        var gratefulRemove = "{{ route('manage_time::hr.grateful-remove') }}";
        var sessionKeys = 'report_employee_onsite';
        var _token = "{{ csrf_token() }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/jquery.shorten.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
    <script src="{{ CoreUrl::asset('common/js/check_item.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/report_onsite.js') }}"></script>
    <script>
        setCheckItem(sessionKeys);
    </script>
@endsection
