@extends('manage_time::layout.manage_layout')

@section('title-manage')
    {{ $timeKeepingTable->timekeeping_table_name }} 
@endsection

<?php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View;
    use Rikkei\Core\View\Form;
    use Rikkei\Core\View\CookieCore;
    use Rikkei\Team\View\Config;
    use Rikkei\ManageTime\Model\Timekeeping;
    use Rikkei\ManageTime\View\ManageTimeCommon;
    use Rikkei\ManageTime\View\ManageTimeConst;
    use Rikkei\ManageTime\View\TimekeepingPermission;
?>

@section('css-manage')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <style type="text/css">
        #datetimepicker-year {
            width: 150px;
        }
        #box_search_timekeeping_table {
            width: 80%;
        }
        @media (max-width: 991px) {
            #box_search_timekeeping_table {
                width: 100%;
            }
        }
        .team-select-box {
            width: 100%;
        }
        .team-select-box label {
            margin-right: 10px;
            margin-top: 8px;
        }
        .team-select-box .select2.select2-container .select2-selection.select2-selection--single {
            height: 34px;
        }
    </style>
@endsection

@section('content-manage')
    <!-- Box register list -->
    <div class="box box-primary _me_review_page">
        <div class="box-header">
            <div class="row">
                <div class="col-lg-6">
                    @if (TimekeepingPermission::isPermission())
                    <button class="btn btn-success btn-show-modal-add-emp" >{{ trans('manage_time::view.Add employee') }}<i class="fa fa-spin fa-refresh hidden"></i></button>
                    <button class="btn btn-danger btn-remove-emp" disabled="">{{ trans('manage_time::view.Remove employee') }}<i class="fa fa-spin fa-refresh hidden"></i></button>
                    @endif
                </div>
                <div class="col-lg-6">
                    <div class="pull-right">
                        @include('manage_time::timekeeping.include.filter_aggregate', ['detail' => true])
                    </div>
                </div>
            </div>
            <div class="row margin-top-30">
                <div class="col-lg-12">
                    <div class="team-select-box managetime-margin-bottom-5">
                        <div class="col-md-2 managetime-margin-bottom-5 padding-0">
                            <label for="" class="control-label">{{ trans('manage_time::view.Year:') }}</label>
                            <div class="input-group date" id="datetimepicker-year">
                                <input type="text" class="form-control filter-grid" id="filter_year" value="{{ $yearFilter }}">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-10 managetime-margin-bottom-5 padding-0">
                            <label for="" class="control-label">{{ trans('manage_time::view.Timekeeping table:') }}</label>
                            <div class="input-box" id="box_search_timekeeping_table">
                                <input type="hidden" name="" id="timekeeping_table_id" value="{{ $timeKeepingTable->id }}">
                                <select class="form-control select-search" id="select_timekeeping_table" style="width: 100%;">
                                    @if (isset($timekeepingTablesList) && count($timekeepingTablesList))
                                        <option>&nbsp;</option>
                                        @foreach ($timekeepingTablesList as $item)
                                            <option value="{{ route('manage_time::timekeeping.timekeeping-detail', ['id' => $item->timekeeping_table_id]) }}" {{ $item->timekeeping_table_id == $timeKeepingTable->id ? 'selected' : '' }}>{{ $item->timekeeping_table_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-header -->

        <div id="filter_data">
            @include('manage_time::timekeeping.timekeeping_detail_data')
        </div>

        <div class="box-footer no-padding">
            <div class="mailbox-controls">  
                @include('team::include.pager')
            </div>
        </div>
    </div>
    <!-- /. box -->
@include('manage_time::timekeeping.include.modal_add_employee')
@endsection

@section('script-manage')
    <script>
        var chooseEmpText = '{{ trans('manage_time::view.Choose employee') }}';
        var messageExist = ' đã tồn tại trong bảng chấm công.';
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/timekeeping.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
    <script type="text/javascript">
        var empIdInList = <?php echo json_encode($empIdInList) ?>;
        var urlAddEmployee = '{{ route("manage_time::timekeeping.addEmpToTimekeeping") }}';
        var urlAjaxGetTimekeepingTable = '{{ route('manage_time::timekeeping.ajax-get-timekeeping-table') }}';
        const TYPE_AJAX_GET_TIMEKEEPING_TABLE = '{{ ManageTimeConst::TYPE_AJAX_GET_TIMEKEEPING_TABLE }}';
        var meTblLeft = $('#_me_tbl_left');
        $(function() {
            //Set bg color to weekend
            $('.managetime-table thead th[data-weekend="true"]').each(function () {
                var col = $(this).data('col');
                $('.managetime-table tbody td[data-col="'+col+'"]').css('background-color', '#f5f5f5');
            });

            $('#datetimepicker-year').datepicker({
                format: " yyyy",
                viewMode: "years",
                minViewMode: "years",
                autoclose: true,
            }).on('changeDate', function(selected) {
                $.ajax({
                    type: "GET",
                    data : {
                        year: $('#filter_year').val(),
                        timekeepingTableId: $('#timekeeping_table_id').val(),
                        type: TYPE_AJAX_GET_TIMEKEEPING_TABLE,
                    },
                    url: urlAjaxGetTimekeepingTable,
                    success: function (data) {
                        $('#select_timekeeping_table').html(data.html);
                    },
                });  
            });

            $('#datetimepicker-year input').keypress(function (e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
            });
            
            $('#datetimepicker-year input').on('keyup', function(e) {
            });
            $('#select_timekeeping_table').on('change', function(event) {
                value = $(this).val();
                window.location.href = value;
            });
            $('#_me_table tr').each(function (index, domElement) {
                $('#_me_tbl_left tr').each(function (ind, dom) {
                    if (index === ind) {
                        if ($(dom).outerHeight() > $(domElement).outerHeight()) {
                            $(domElement).outerHeight($(dom).outerHeight());
                        } else {
                            $(dom).outerHeight($(domElement).outerHeight());
                        }
                    }
                });
            });

            // Set fillter to left table
            setFixedHeaderTimekeeping();
        });

    </script>
@endsection
