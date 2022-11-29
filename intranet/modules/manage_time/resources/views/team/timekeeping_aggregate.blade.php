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
    use Rikkei\ManageTime\Model\TimeKeepingTable;
    use Rikkei\ManageTime\View\ManageTimeCommon;
    use Rikkei\ManageTime\View\ManageTimeConst;
    use Rikkei\ManageTime\View\TimekeepingPermission;

    $permissionTimeKeeping = TimekeepingPermission::isPermission();
    $objTKTable = new TimeKeepingTable();
    $arrLabelType = $objTKTable->getArrLabelTypeTKTable();
?>

@section('css-manage')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ asset('resource/css/candidate/list.css') }}" >
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/timekeeping.css') }}" />
    <style>
        .keeping-aggregate-page #box_search_timekeeping_table {
            width: 66%;
        }
    </style>
@endsection

@section('content-manage')
    <!-- Box register list -->
    <div class="box box-primary _me_review_page keeping-aggregate-page">
        <div class="box-header">
            <div class="row">
                <div class="col-md-8 col-sm-6">
                    <div class="team-select-box managetime-margin-bottom-5">
                        <label for="" class="control-label w-120">{{trans('project::view.Choose project:')}} </label>
                        <div class="input-box" id="">
                            <select class="form-control select-search" id="select_project" style="width: 100%;">
                                <option>&nbsp</option>
                                @if(count($projects))
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}"
                                                @if ($projFilter == $proj->id)
                                                selected
                                                @endif
                                        > {{ $proj->projName }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 pull-right text-right">
                    <a href="{{route('manage_time::division.lead-export-aggregate', ['id' => $timeKeepingTable->id]) }}?projd={{$projFilter}}" class="btn btn-primary">{{ trans('manage_time::view.Export') }}</a>
                    <div class="form-inline">
                        @include('team::include.filter')
                    </div>
                    <br/>
                </div>
                <div class="col-lg-12">
                    <div class="team-select-box managetime-margin-bottom-5">
                        <div class="col-md-6 padding-0">
                            <label for="" class="control-label w-120">{{ trans('manage_time::view.Timekeeping table:') }}</label>
                            <div class="input-box" id="box_search_timekeeping_table">
                                <input type="hidden" name="" id="timekeeping_table_id" value="{{ $timeKeepingTable->id }}">
                                <select class="form-control select-search" id="select_timekeeping_table" style="width: 100%;">
                                    @if (isset($timekeepingTablesList) && count($timekeepingTablesList))
                                        <option>&nbsp;</option>
                                        @foreach ($timekeepingTablesList as $item)
                                            <?php
                                            $dateStart = Carbon::parse($item->start_date)->format('d/m/Y');
                                            $dateEnd = Carbon::parse($item->end_date)->format('d/m/Y');
                                            $name = $item->team_name
                                                . ' {' . trans('manage_time::view.From')
                                                . ' ' . $dateStart
                                                . ' ' . trans('manage_time::view.to')
                                                . ' ' . $dateEnd
                                                . '} - {' . $arrLabelType[$item->type]
                                                .'}';
                                            ?>
                                            <option value="{{ route('manage_time::division.list-tk-aggregates', ['id' => $item->timekeeping_table_id]) }}?projd={{$projFilter}}" {{ $item->timekeeping_table_id == $timeKeepingTable->id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 managetime-margin-bottom-5 padding-0">
                           <label for="" class="control-label">{{ trans('manage_time::view.Timekeeping table type:') }}</label>
                            <div class="input-box" id="">
                                <select class="form-control select-search" id="select_type_tk" style="width: 100%;">
                                    @foreach($arrLabelType as $k => $v)
                                        <option value="{{ $k }}"
                                        @if ($typeFilter == $k)
                                            selected
                                        @endif
                                        > {{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 padding-0">
                            <label for="" class="control-label">{{ trans('manage_time::view.Year:') }}</label>
                            <div class="input-group date" id="datetimepicker-year">
                                <input type="text" class="form-control filter-grid" id="filter_year" value="{{ $yearFilter }}">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-header -->

        <div id="filter_data">
            @include('manage_time::team.timekeeping_aggregate_data')
        </div>

        <div class="box-footer no-padding">
            <div class="mailbox-controls">  
                @include('team::include.pager')
            </div>
        </div>
    </div>
    <!-- /. box -->
<div class="box box-primary">
    <div class="box-body">
        {!! trans('manage_time::view.guide timekeeping aggregate') !!}
    </div>
</div>
@endsection

@section('script-manage')
    <script>
        var chooseEmpText = '{{ trans('manage_time::view.Choose employee') }}';
        var messageExist = ' đã tồn tại trong bảng chấm công.';
        var token = '{{ csrf_token() }}';
        var tableId = {{ $timeKeepingTable->id }};
        var url = window.location.origin + window.location.pathname;
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/timekeeping.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
    <script type="text/javascript">
        var urlAjaxGetTimekeepingTable = '{{ route('manage_time::division.ajax-get-tk-table') }}';
        var meTblLeft = $('#_me_tbl_left');
        var empIdInList = <?php echo json_encode($empIdInList) ?>;
        $(function() {
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
                        type_id: $('#select_type_tk').val(),
                        proj_id: "{{$projFilter}}",
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
 
            fixHeightTr();

            // Set fillter to left table
            setFixedHeaderTimekeeping();
        });

        function fixHeightTr() {
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
        }

        $('#select_type_tk').change(function() {
            $.ajax({
                type: "GET",
                data : {
                    year: $('#filter_year').val(),
                    timekeepingTableId: $('#timekeeping_table_id').val(),
                    type_id: $('#select_type_tk').val(),
                    proj_id: "{{$projFilter}}",
                },
                url: urlAjaxGetTimekeepingTable,
                success: function (data) {
                    $('#select_timekeeping_table').html(data.html);
                },
            });  
        })
        $('#select_project').change(function() {
            if ($('#select_project').val()) {
                url = url + '?projd=' + $('#select_project').val();
            }
            window.location.href = url;
        })
    </script>
@endsection
