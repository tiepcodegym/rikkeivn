<?php
use Rikkei\Project\Model\MonthlyReport;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Permission;

$countMonth = 12;
$rowPlanRevenue = MonthlyReport::ROW_PLAN_REVENUE;
$rowApprovedCost = MonthlyReport::ROW_APPROVED_COST;
$rowBillEffort = MonthlyReport::ROW_BILLABLE_EFFORT;
$rowApprovedProdCost = MonthlyReport::ROW_APPROVED_PROD_COST;
$rowCost = MonthlyReport::ROW_COST;
$rowBusinessEffective = MonthlyReport::ROW_BUSINESS_EFFECTIVE;
$rowNa = MonthlyReport::ROW_NA;
$rowPlan = MonthlyReport::ROW_PLAN;
$rowBillActual = MonthlyReport::ROW_BILLACTUAL;
$rowBillStaffPlan = MonthlyReport::ROW_BILL_STAFF_PLAN;
$rowBillStaffActual = MonthlyReport::ROW_BILL_STAFF_ACTUAL;
$rowActual = MonthlyReport::ROW_ACTUAL;
$rowTraningPlan = MonthlyReport::ROW_TRAINING_PLAN;
$rowCompletedPlan = MonthlyReport::ROW_COMPLETED_PLAN;
$rowAlloStaffActual = MonthlyReport::ROW_ALLO_STAFF_ACTUAL;
$rowProdStaffActual = MonthlyReport::ROW_PROD_STAFF_ACTUAL;
$rowHrPlan = MonthlyReport::ROW_HR_PLAN;
$rowHrActual = MonthlyReport::ROW_HR_ACTUAL;
$updatePemission = Permission::getInstance()->isAllow('project::monthly.report.update');
?>

@extends('layouts.default')

@section('title')
{{ trans('project::view.Monthly report') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link href="{{ CoreUrl::asset('project/css/monthly_report.css') }}" rel="stylesheet" type="text/css" >
<style>
    label .required {
        color: #ff0000;
    }
</style>
@endsection

@section('content')
<div class="box box-primary">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-6 width-220 form-group">
                <label>{{ trans('project::view.Year') }}</label>&nbsp;
                <select class="form-control year-filter width-150 display-inline select-search">
                    @foreach($yearFilter as $item)
                    <option value="{{ $item->year }}" @if($year == $item->year) selected @endif>{{ $item->year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 width-270 form-group">
                <label>{{ trans('project::view.Start month') }}</label>&nbsp;
                <select class="form-control start-month width-150 display-inline select-search">
                    @for($month=1; $month<=$countMonth; $month++)
                    <option value="{{ $month }}" 
                            {{ $month == $startMonth ? 'selected' : '' }}
                            {{ $month > $endMonth ? 'disabled' : '' }}
                    >{{ $month }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-sm-6 width-270 form-group">
                <label>{{ trans('project::view.End month') }}</label>&nbsp;
                <select class="form-control end-month width-150 display-inline select-search">
                    @for($month=1; $month<=$countMonth; $month++)
                    <option value="{{ $month }}" 
                            {{ $month == $endMonth ? 'selected' : '' }}
                            {{ $month < $startMonth ? 'disabled' : '' }}
                        >{{ $month }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-sm-6 width-150 form-group">
                <button type="button" class="btn-add btn-search">
                    {{ trans('project::view.Search') }}
                    <i class="fa fa-spin fa-refresh hidden"></i>
                </button>
            </div>
            <div class="col-sm-6 width-270 form-group">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_export_billable">
                    {{ trans('project::view.Export billable') }}
                </button>
            </div>
            <div class="col-sm-6 width-150 form-group">
                <a href="{{ route('project::monthly.report.help') }}" target="_blank" class="btn btn-primary">Help</a>
            </div>
        </div>
        <div class="position-relative">
            <div class="loader-container hidden"></div>
            <div class="data">
            @include('project::monthly_report.include.data')
            </div>
        </div>
    </div>
</div>

<div id="tbl_template" class="hidden"></div>

@include('project::monthly_report.include.modal-files')
@endsection

<!-- Script -->
@section('script')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
<script type="text/javascript" src="{{ asset('lib/xlsx/jszip.js') }}"></script>
<script type="text/javascript" src="{{ asset('lib/xlsx/xlsx.js') }}"></script>
<script>
var urlUpdate = '{{ route("project::monthly.report.update") }}';
var urlSearch = '{{ route("project::monthly.report.search") }}';
var successText = '{{ trans("project::view.Submit success") }}';
var errorText = '{{ trans("project::view.An error occurred") }}';
var errorTimeoutText = '{{ trans("project::view.Request time out") }}';
var rowNa = '{{ $rowNa }}';
var rowPlanRevenue = '{{ $rowPlanRevenue }}';
var rowApprovedCost = '{{ $rowApprovedCost }}';
var rowBillEffort = '{{ $rowBillEffort }}';
var rowApprovedCost = '{{ $rowApprovedCost }}';
var rowApprovedProdCost = '{{ $rowApprovedProdCost }}';
var notAvailable = '{{ $notAvailable }}';
var isValue = '{{ MonthlyReport::IS_VALUE }}';
var rowCost = '{{ $rowCost }}';
var rowActual = '{{ $rowActual }}';
var rowPlan = '{{ $rowPlan }}';
var rowBillActual = '{{ $rowBillActual }}';
var rowBillStaffPlan = '{{ $rowBillStaffPlan }}';
var rowBillStaffActual = '{{ $rowBillStaffActual }}';
var rowBusinessEffective = '{{ $rowBusinessEffective }}';
var rowCompletedPlan = '{{ $rowCompletedPlan }}';
var rowAlloStaffActual = '{{ $rowAlloStaffActual }}';
var rowProdStaffActual = '{{ $rowProdStaffActual }}';
var rowHrPlan = '{{ $rowHrPlan }}';
var rowHrActual = '{{ $rowHrActual }}';
var mrHasPermissUpdate = {{ $updatePemission }};
var rowTraningPlan = '{{ $rowTraningPlan }}';
var maxPoint = {{ count($allTeam) - 1 }};
var requestTimeOut = {{ MonthlyReport::REQUEST_TIMEOUT }};
var startDateBefore = '{{ trans("project::message.The from month must be before to month") }}';
//store origin values when load page
var values = <?php echo json_encode($values);?>;
//store values after changed
var tempValues = <?php echo json_encode($values);?>;
//store changed values (get difference between values and tempValues)
var changeValues = {};
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('project/js/monthly_report.js') }}"></script>
<script>
/**
 * Reset point when update value
 * 
 * @param array values
 * @param int month
 * @param string row
 * @param int type
 * @return void
 */
function resetPoint(tempValues, month, row, type)
{
    point = [];
    point[month] = [];
    point[month][row] = [];
    temp = [];
    temp[month] = [];
    temp[month][row] = [];
    
    @foreach ($allTeam as $team)
        var team = {{ $team->id }};
        if(typeof tempValues[team][month][row] != 'undefined') {
            point[month][row][team] = tempValues[team][month][row]['value'];
            temp[month][row][team] = tempValues[team][month][row]['value'];
            if(typeof point[month][row][team] == 'undefined') {
                point[month][row][team] = 0;
            }
            if(typeof temp[month][row][team] == 'undefined') {
                temp[month][row][team] = 0;
            }
        } else {
            point[month][row][team] = 0;
            temp[month][row][team] = 0;
        }
           
    @endforeach
    //remove elements "N/A"
    temp[month][row] = jQuery.grep(temp[month][row], function(value) {
        return value != "N/A";
    });
    //sort desc elements
    temp[month][row].sort(function(a, b){return b-a});
    temp[month][row] = unique(temp[month][row]);
    pointSet = maxPoint;
    for (stt=0; stt<maxPoint; stt++) {
        if (stt in temp[month][row]) {
            searchArray = searchItemInArray(point[month][row], temp[month][row][stt]);
            $.each(searchArray, function (teamId, search) {
                if (typeof search != 'undefined') {
                    span = $('.tab-pane[data-team='+teamId+']').find('table.dataTable[data-type='+type+'] tr[data-row='+row+'] span[data-month='+month+'][data-value-or-point=point]');
                    if (search == 0 || search == notAvailable) {
                        span.text(0);
                    } else {
                        span.text(pointSet);
                    }
                }
            });
            pointSet--;
        } else {
            break;
        }
    }
}

jQuery(document).ready(function ($) {
    RKfuncion.select2.init();

    $('.date-picker').each(function () {
        $(this).datepicker({
            minViewMode: 1,
            format: 'mm-yyyy',
            autoclose: true,
            todayHighlight: true
        });
    });
});

</script>
@endsection
