<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Config;
use Rikkei\Resource\View\HrWeeklyReport;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\View\getOptions;

$route = 'resource::hr_wr.index';
$currentUser = Permission::getInstance()->getEmployee();
$filterFromDate = FormView::getFilterData('excerpt', 'date_from');
$filterToDate = FormView::getFilterData('excerpt', 'date_to');
$filterRecruiter = FormView::getFilterData('excerpt', 'recruiter');
?>

@extends('layouts.default')

@section('title', trans('resource::view.Hr weekly report'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('resource/css/hr_weekly_report.css') }}" />
@endsection

@section('content')

<div class="box box-primary">
    <div class="box-body">
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="row margin-bottom-5">
                            <label class="col-lg-2 col-md-3 margin-top-5 white-space-nowrap">{{ trans('resource::view.Hr.From') }}</label>
                            <div class="col-lg-10 col-md-9">
                                <input type="text" name="filter[excerpt][date_from]" autocomplete="off"
                                       value="{{ $filterFromDate }}" id="filter_from_date"
                                       class="form-control date-picker filter-grid form-inline">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="row margin-bottom-5">
                            <label class="col-lg-2 col-md-3 margin-top-5  white-space-nowrap">{{ trans('resource::view.Hr.To') }}</label>
                            <div class="col-lg-10 col-md-9">
                                <input type="text" name="filter[excerpt][date_to]" autocomplete="off"
                                       value="{{ $filterToDate }}" id="filter_to_date"
                                       class="form-control date-picker filter-grid form-inline">
                            </div>
                        </div>
                    </div>
                    @if (Permission::getInstance()->isScopeCompany(null, $route)
                        || Permission::getInstance()->isScopeTeam(null, $route))
                    <div class="col-sm-4">
                        <div class="row margin-bottom-5">
                            <label class="col-md-4 margin-top-5 white-space-nowrap">{{ trans('resource::view.Recruiter') }}</label>
                            <div class="col-md-8">
                                <select name="filter[excerpt][recruiter]" class="form-control select-grid filter-grid">
                                    <option value="">&nbsp;</option>
                                    @if ($hrAccounts)
                                        @foreach ($hrAccounts as $email)
                                        <option value="{{ $email }}" {{ $email == $filterRecruiter ? 'selected' : '' }}>{{ $email }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                @include('team::include.filter', ['domainTrans' => 'resource'])
            </div>
        </div>
    </div>
 
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped dataTable wr-table">
                <thead>
                    <tr class="bg-light-blue">
                        <th>{{ trans('core::view.NO.') }}</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('week') }}" data-order="week" data-dir="{{ Config::getDirOrder('week') }}">
                            {{ trans('resource::view.Hr.Week') }}
                        </th>
                        <th class="white-space-nowrap">{{ trans('resource::view.Number CV receive') }}</th>
                        <th>{{ trans('resource::view.Hr.Tested') }}</th>
                        <th>{{ trans('resource::view.Hr.Test passed') }}</th>
                        <th>{{ trans('resource::view.Hr.GM > 8') }}</th>
                        <th>{{ trans('resource::view.Hr.Interviewed') }}</th>
                        <th>{{ trans('resource::view.Interview passed') }}</th>
                        <th>{{ trans('resource::view.Hr.Offered') }}</th>
                        <th>{{ trans('resource::view.Hr.Offer passed') }}</th>
                        <th>{{ trans('resource::view.Hr.Working') }}</th>
                        <th>{{ trans('resource::view.Hr.Note') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!$collectionModel->isEmpty())
                        @foreach ($collectionModel as $order => $item)
                        <?php
                        $dateWeek = HrWeeklyReport::getArrDateByWeek($item->week);
                        $itemNumberCvs = $item->getJsonAttribute('number_cvs', $paramWiths);
                        $itemTests = $item->getJsonAttribute('tests', $paramWiths);
                        $itemTestsPass = $item->getJsonAttribute('tests_pass', $paramWiths);
                        $itemGmats = $item->getJsonAttribute('gmats_8', $paramWiths);
                        $itemInterviews = $item->getJsonAttribute('interviews', $paramWiths);
                        $itemInterviewsPass = $item->getJsonAttribute('interviews_pass', $paramWiths);
                        $itemOffers = $item->getJsonAttribute('offers', $paramWiths);
                        $itemOffersPass = $item->getJsonAttribute('offers_pass', $paramWiths);
                        $itemWorkings = $item->getJsonAttribute('workings', $paramWiths);
                        ?>
                        <tr data-week="{{ $item->week }}">
                            <td>{{ $order + 1 + ($collectionModel->currentPage() - 1) * $collectionModel->perPage() }}</td>
                            <td class="text-center">
                                <div>{{ trans('resource::view.Hr.Week') }}: {{ $dateWeek[2] }}</div>
                                <span class="white-space-nowrap">{{ $dateWeek[0] }} <i class="fa fa-long-arrow-right"></i> {{ $dateWeek[1] }}</span>
                            </td>
                            <td class="col-data" data-items="{{ $itemNumberCvs }}">{{ $itemNumberCvs->count() }}</td>
                            <td class="col-data" data-items="{{ $itemTests }}">{{ $itemTests->count() }}</td>
                            <td class="col-data" data-items="{{ $itemTestsPass }}">{{ $itemTestsPass->count() }}</td>
                            <td class="col-data" data-items="{{ $itemGmats }}">{{ $itemGmats->count() }}</td>
                            <td class="col-data" data-items="{{ $itemInterviews }}">{{ $itemInterviews->count() }}</td>
                            <td class="col-data" data-items="{{ $itemInterviewsPass }}">{{ $itemInterviewsPass->count() }}</td>
                            <td class="col-data" data-items="{{ $itemOffers }}">{{ $itemOffers->count() }}</td>
                            <td class="col-data" data-items="{{ $itemOffersPass }}">{{ $itemOffersPass->count() }}</td>
                            <td class="col-data" data-items="{{ $itemWorkings }}" data-working="1">{{ $itemWorkings->count() }}</td>
                            <td class="note-col">
                                <?php
                                $currHasNote = false;
                                ?>
                                @if (isset($arrayNotes[$item->week]))
                                    @foreach ($arrayNotes[$item->week] as $note)
                                        @include('resource::hr_weekly_report.note_item', ['noteItem' => $note])
                                        <?php
                                        if (!$currHasNote && $note->email == $currentUser->email) {
                                            $currHasNote = true;
                                        }
                                        ?>
                                    @endforeach
                                @endif
                                @if (!$currHasNote)
                                    @include('resource::hr_weekly_report.note_item')
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="12" class="text-center"><h4>{{ trans('resource::message.Not found item') }}</h4></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @include('team::include.pager', ['domainTrans' => 'resource'])
    </div>
</div>

<div class="modal fade" id="modal_report_detail">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('core::view.NO.') }}</th>
                            <th>{{ trans('resource::view.Name') }}</th>
                            <th>{{ trans('resource::view.Email') }}</th>
                            <th>{{ trans('resource::view.Request.Create.Programming languages') }} / {{ trans('resource::view.Position apply') }}</th>
                            <th>{{ trans('resource::view.Recruiter') }}</th>
                        </tr>
                    </thead>
                    <tbody class="list-cv">
                        <tr>
                            <td colspan="5">{{ trans('resource::message.Not found item') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{ trans('resource::view.Close') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@endsection

@section('script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script>
    var saveNoteUrl = '{{ route("resource::hr_wr.save_note") }}';
    var textErrorMaxLength = '<?php echo trans("resource::message.The field may not be greater characters", ["field" => "note", "max" => 500]) ?>';
    var textShowMore = '<?php echo trans("resource::view.Show more") ?>';
    var textShowLess = '<?php echo trans("resource::view.Show less") ?>';
    var listPrograms = '{!! json_encode($programs) !!}';
    var listPositions = '{!! json_encode($position) !!}';
    var routeDetail = '{{ route("resource::candidate.detail", ["id" => null]) }}';
    var textWeek = '{{ trans('resource::view.Week') }}';
    var textNotItem = '{{ trans('resource::message.Not found item') }}';
    var textDate = '{{ trans('resource::view.Date') }}';
    var textTeam = '{{ trans('manage_time::view.Work unit') }}';

    $('select[name="filter[excerpt][recruiter]"]').select2({
        minimumInputLength: 0
    });
    (function ($) {
        selectSearchReload();
        
        $('.date-picker').datepicker({
            format: 'yyyy/mm/dd',
            viewMode: 'days',
            autoclose: true,
            todayBtn: true,
        }).on('changeDate', function () {
            $('.btn-search-filter').click();
        });

        @if ($filterToDate)
            $('#filter_from_date').datepicker('setEndDate', '{{ $filterToDate }}');
        @endif
        @if ($filterFromDate)
            $('#filter_to_date').datepicker('setStartDate', '{{ $filterFromDate }}');
        @endif
    })(jQuery);
</script>
<script src="{{ CoreUrl::asset('resource/js/hr_weekly_report/index.js') }}"></script>

@endsection
