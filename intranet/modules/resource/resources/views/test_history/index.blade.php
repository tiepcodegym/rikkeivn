<?php
use Rikkei\Core\View\Form as FormView;
use Rikkei\Resource\Model\TestSchedule;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;

$emptyDate = ['0000-00-00 00:00:00', null, ''];
$candidateTbl = TestSchedule::getTableName();
$currTime = Carbon::now();
$filterYear = FormView::getFilterData('spec_data', 'test_year');
if (!$filterYear) {
    $filterYear = $currTime->year;
}
$filterMonth = FormView::getFilterData('spec_data', 'test_month');
if (!$filterMonth) {
    $filterMonth = $currTime->month;
}
?>

@extends('layouts.default')

@section('title', trans('resource::view.Test schedule'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('resource/css/resource.css') }}" />
@endsection

@section('content')

<div class="box box-info">
    
    <div class="box-body">
        <div class="filter-left-box row">
            <div class="col-sm-4 col-md-3">
                <div class="form-group row">
                    <label class="col-sm-4 margin-top-5">{{ trans('resource::view.Select year') }}</label>
                    <div class="col-sm-8">
                        <input type="text" name="filter[spec_data][test_year]" id="test_year" value="{{ $filterYear }}" class="form-control date filter-grid">
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-md-3">
                <div class="form-group row">
                    <label class="col-sm-4 margin-top-5">{{ trans('resource::view.Select month') }}</label>
                    <div class="col-sm-8">
                        <select name="filter[spec_data][test_month]" class="form-control select-grid filter-grid select-search">
                            <option value="NULL">&nbsp;</option>
                            @for ($month = 1; $month < 13; $month++)
                            <option value="{{ $month }}" {{ $filterMonth == $month ? 'selected' : '' }}>{{ ($month < 10 ? '0' . $month : $month) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-md-6">
                @include('team::include.filter')
            </div>
        </div>
    </div>

    <div class="table-responsive schedule-table">
        <table class="table table-striped dataTable table-bordered table-grid-data statistics-table">
            <thead>
                <tr class="bg-light-blue">
                    <th class="text-center sorting {{ Config::getDirClass('test_time') }} col-name" data-order="test_time" data-dir="{{ Config::getDirOrder('test_time') }}">{{ trans('resource::view.Date plan') }}</th>
                    <th class="text-center">{{ trans('resource::view.Test.Time') }}</th>
                    <th class="text-center">{{ trans('resource::view.Candidate.Detail.Test') }}</th>
                    <th class="text-center">{{ trans('resource::view.Candidate.Detail.Interview') }}</th>
                    <th>{{ trans('resource::view.Test.Fullname') }}</th>
                    <th>{{ trans('resource::view.Test.Phone number') }}</th>
                    <th>{{ trans('resource::view.Email') }}</th>
                    <th class="min-width-100">{{ trans('resource::view.Test.Position apply') }}</th>
                    <th class="width-150">{{ trans('resource::view.Test.Note') }}</th>
                    <th>{{ trans('resource::view.Test.Result') }}</th>
                </tr>
            </thead>
            <tbody>
                <?php
                //get filter data
                $filterHadTested = FormView::getFilterData('spec_data', 'had_test');
                $filterHadInterview = FormView::getFilterData('spec_data', 'had_inteview');
                $filterTestResult = FormView::getFilterData('spec_data', 'test_result');
                if ($filterTestResult !== null) {
                    $filterTestResult = intval($filterTestResult);
                } else {
                    $filterTestResult = -1;
                }
                ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td>
                        <select name="filter[spec_data][had_test]" class="form-control select-grid filter-grid select-search width-100">
                            <option value="">&nbsp;</option>
                            <option value="{{ TestSchedule::YES_VAL }}" {{ $filterHadTested == TestSchedule::YES_VAL ? 'selected' : '' }}>{{ trans('resource::view.Request.Create.Yes') }}</option>
                            <option value="{{ TestSchedule::NO_VAL }}" {{ $filterHadTested == TestSchedule::NO_VAL ? 'selected' : '' }}>{{ trans('resource::view.Request.Create.No') }}</option>
                        </select>
                    </td>
                    <td>
                        <select name="filter[spec_data][had_inteview]" class="form-control select-grid filter-grid select-search width-100">
                            <option value="">&nbsp;</option>
                            <option value="{{ TestSchedule::YES_VAL }}" {{ $filterHadInterview == TestSchedule::YES_VAL ? 'selected' : '' }}>{{ trans('resource::view.Request.Create.Yes') }}</option>
                            <option value="{{ TestSchedule::NO_VAL }}" {{ $filterHadInterview == TestSchedule::NO_VAL ? 'selected' : '' }}>{{ trans('resource::view.Request.Create.No') }}</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="filter[{{ $candidateTbl }}.fullname]" value="{{ FormView::getFilterData($candidateTbl.'.fullname') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[{{ $candidateTbl }}.mobile]" value="{{ FormView::getFilterData($candidateTbl.'.mobile') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[{{ $candidateTbl }}.email]" value="{{ FormView::getFilterData($candidateTbl.'.email') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <select name="filter[number][{{ $candidateTbl }}.position_apply]" class="form-control select-grid filter-grid select-search min-width-100">
                            <option value="">&nbsp;</option>
                            @foreach($positionOptions as $key => $label)
                            <option value="{{ $key }}" {{ FormView::getFilterData('number', $candidateTbl.'.position_apply') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td></td>
                    <td>
                        <select name="filter[spec_data][test_result]" class="form-control select-grid filter-grid select-search">
                            <option value="-1" {{ $filterTestResult === -1 ? 'selected' : '' }}>&nbsp;</option>
                            <option value="{{ getOptions::RESULT_DEFAULT }}" {{ $filterTestResult === getOptions::RESULT_DEFAULT ? 'selected' : '' }}>N/A</option>
                            <option value="{{ getOptions::RESULT_PASS }}" {{ $filterTestResult === getOptions::RESULT_PASS ? 'selected' : '' }}>{{ strtoupper(trans('resource::view.Candidate.Detail.Pass')) }}</option>
                            <option value="{{ getOptions::RESULT_FAIL }}" {{ $filterTestResult === getOptions::RESULT_FAIL ? 'selected' : '' }}>{{ strtoupper(trans('resource::view.Candidate.Detail.Fail')) }}</option>
                        </select>
                    </td>
                </tr>
                @if (!$collectionModel->isEmpty())
                    <?php
                    $total = $collectionModel->count();
                    $itemPrev = $collectionModel->first();
                    
                    $groupItems = [];
                    for($i = 0; $i < $total; $i++) {
                        $item = $collectionModel[$i];

                        $itemTime = !in_array($item->test_time, $emptyDate) ? Carbon::parse($item->test_time) : null;
                        $item->test_time = $itemTime;
                        $itemDate = $itemTime ? $itemTime->format('d-m-Y') : null;

                        $itemPrevTime = !in_array($itemPrev->test_time, $emptyDate) ? Carbon::parse($itemPrev->test_time) : null;
                        $itemPrev->test_time = $itemPrevTime;
                        $itemPrevDate = $itemPrevTime ? $itemPrevTime->format('d-m-Y') : null;
                        if ($itemDate && $itemPrevDate && $itemDate == $itemPrevDate) {
                            array_push($groupItems, $item);
                        } else { 
                            ?>
                            @include('resource::test_history.table_item', ['groupItems' => $groupItems])
                            <?php
                            $groupItems = [$item];
                        }
                        $itemPrev = $item;
                        if ($i == $total - 1 && $groupItems) {
                            ?>
                            @include('resource::test_history.table_item', ['groupItems' => $groupItems])
                            <?php
                        }
                    }
                    ?>
                @else
                    <tr>
                        <td colspan="10" class="text-center"><h3>{{ trans('resource::message.No data') }}</h3></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="box-body">
        @include('team::include.pager')
    </div>

</div>

@endsection

@section('script')

@include('resource::recruit.script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script>
    (function ($) {
        selectSearchReload();
        
        $('#test_year').datepicker({
            format: 'yyyy',
            viewMode: 'years',
            minViewMode: 'years',
            autoclose: true
        }).on('changeDate', function () {
            $('.btn-search-filter').click();
        });

    })(jQuery);
</script>

@endsection