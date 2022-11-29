@extends('manage_time::layout.manage_layout')

@section('title-manage')
{{ trans('manage_time::view.Manage business trip report') }} 
@endsection

<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View;
use Rikkei\Core\View\Form;
use Rikkei\Team\View\Config;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\View\MissionPermission;

$parentId = null;
$colspan = 7;
?>

@section('css-manage')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
@endsection

@section('content-manage')
<div class="box box-primary" id="mission_manage_list">
    <div class="box-header">
        <div class="team-select-box col-md-12">
            <div class="col-md-6 col-sm-12">
                <label for="choose-time-filter">{{ trans('manage_time::view.Choose datetime') }}</label>
                <div class="input-group datetime-picker ">
                    <input
                        type="text" 
                        class="form-control choose-time-filter filter-grid"
                        name="choose-time-filter" 
                        id="filter-date"
                        autocomplete="off" 
                        value=""
                        >
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-sm-12">
                <label for="sel_country">{{ trans('manage_time::view.Country') }}</label>
                <div class="input-box">
                    <select class="form-control filter-grid" name="sel_country" id='sel_country'   >
                        <option  {{!in_array($selCountry,[1,2]) ? 'selected':''}} value="" >-- {{ trans('manage_time::view.Country') }} --</option>
                        <option {{$selCountry ==1 ?'selected':''}}  value="1" >{{trans('manage_time::view.Viet Nam')}}</option>
                        <option {{$selCountry ==2 ?'selected':''}} value="2">{{ trans('manage_time::view.Other countries') }}</option>
                    </select>
                </div>

            </div>

        </div>

        <div class="pull-right">
            <div class="filter-action">
                <button id="btn-confirm-export" type="button"  class="btn btn-primary  managetime-margin-bottom-5">
                    <span>{{ trans('manage_time::view.Export') }} <i class="fa fa-print hidden"></i></span>
                </button>
            </div>
        </div>
    </div>
    <!-- /.box-header -->

    <div class="box-body no-padding " id="tab-content">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="managetime_table_fixed">
                <thead class="managetime-thead" style="z-index: 1">
                    <tr>
                        <th class="col-no">{{ trans('manage_time::view.No.') }}</th>

                        <th class="col-creator-code"  >{{ trans('manage_time::view.Employee code') }}</th>

                        <th class="col-creator-name"  >{{ trans('manage_time::view.Employee') }}</th>

                        <th class="col-location"  >{{ trans('manage_time::view.Location') }}</th>

                        <th class="col-date-start" >{{ trans('manage_time::view.From date') }}</th>

                        <th class="col-date-end"  >{{ trans('manage_time::view.End date') }}</th>

                        <th class="col-total">{{ trans('manage_time::view.Number of business trip days in a month') }} </th>
                    </tr>
                </thead>
            </table>

            <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="managetime_table_primary">
                <thead class="managetime-thead">
                    <tr>
                        <th class="col-no" style="min-width: 15px;">{{ trans('manage_time::view.No.') }}</th>

                        <th class="col-creator-code"  style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.Employee code') }}</th>

                        <th class="col-creator-name"  style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.Employee') }}</th>

                        <th class="col-location"  style="min-width: 300px;max-width: 100%; ">{{ trans('manage_time::view.Location') }}</th>

                        <th class="col-date-start"  style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.From date') }}</th>

                        <th class="col-date-end"  style="min-width: 40px; max-width: 40px;">{{ trans('manage_time::view.End date') }}</th>

                        <th class="col-total" style="min-width: 130px; width: 130px;">{{ trans('manage_time::view.Number of business trip days in a month') }} </th>
                    </tr>
                </thead>
                <tbody id="position_start_header_fixed">
                    @if (isset($collectionModel) && count($collectionModel))
                        @foreach ($collectionModel as $team)
                            <tr>
                                <td colspan="{!! $colspan !!}" class="text-left">
                                    <b>{{ $team['name'] }} ({!! trans('manage_time::view.Total user business trip:') !!} {!! count($team['employees']) !!})</b>
                                </td>
                            </tr>
                            <?php $i = 0; ?>
                            @foreach ($team['employees'] as $emp)
                            <tr>
                                <td>{!! ++$i !!}</td>
                                <td>{!! $emp['code'] !!}</td>
                                <td class="managetime-show-popup">{{ $emp['name'] }}</td>
                                <td>{!! $emp['location'] !!}</td>
                                <td>{!! $emp['start_at'] !!}</td>
                                <td>{!! $emp['end_at'] !!}</td>
                                <td>{!! $emp['onsite_days'] !!}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    @else
                    <tr>
                        <td colspan="{{$colspan}}" class="text-center">
                            <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
            <!-- /.table -->
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->

</div>
<!-- /. box -->
@endsection
@include('manage_time::report.include.confirm-export-modal')
@section('script-manage')
<script type="text/javascript">
    var urlReportSearch = "{{route('manage_time::timekeeping.manage.report')}}"
</script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="{{ CoreUrl::asset('asset_managetime/js/report.list.js') }}"></script>
<script type="text/javascript">
    (function ($) {
        $('.datetime-picker').datetimepicker({
            format: 'MM-YYYY',
            showClose: true,
            date: "{{$filterDate}}"
        });
    })(jQuery);

    var fixTop = $('#position_start_header_fixed').offset().top;
    var $tableFixed = $('#managetime_table_fixed');
    var $tablePrimary = $('#managetime_table_primary');
    var $primaryHeadings = $tablePrimary.find('thead th');
    var $fixedHeadings = $tableFixed.find('thead th');
    $(window).on('scroll resize', function () {
        var scrollTop = $(window).scrollTop();
        if (scrollTop > fixTop) {
            $tableFixed.width($tablePrimary[0].scrollWidth);
            $primaryHeadings.map((i, el) => {
                $fixedHeadings[i].style.width =  getComputedStyle(el, null).width;
            });
        }
    });
</script>
@endsection
