<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form;
use Rikkei\Resource\View\getOptions;

?>

@extends('layouts.default')

@section('title', trans('resource::view.Monthly recruitment report'))

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.1/css/bootstrap-colorpicker.min.css" />
    <link rel="stylesheet" href="{!! CoreUrl::asset('resource/css/monthly_report.css') !!}">
@endsection

@section('content')
<div class="box box-primary">
    <div class="box-body">
        <div class="channels-group form-group flex">
            <label>{!! trans('resource::view.Request.List.Channel') !!}</label>
            <div class="flex-1 ml-10">
                <select name="filter[except][channelIds][]" class="form-control column-choice filter-grid" multiple="multiple">
                    @foreach ($allChannels as $channel)
                        <option value="{!! $channel->id !!}"
                                <?php if (in_array($channel->id, $filterChannelIds)): ?> selected <?php endif; ?>>{{ $channel->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="row">
                    <div class="flex col-sm-5">
                        <label class="white-space-nowrap">{!! trans('resource::view.Time') !!}</label>
                        <input type="text" name="filter[except][month]" class="form-control filter-grid ml-5 input-datepicker" autocomplete="off"
                               data-format="YYYY-MM" placeholder="YYYY-mm" value="{{ $month }}">
                    </div>
                    <div class="col-sm-7 flex mt-7">
                        <label class="flex">
                            <input type="radio" name="display_type" value="table" class="mt-0-i" checked>
                            <span class="ml-3 white-space-nowrap">{!! trans('resource::view.Data table') !!}</span>
                        </label>
                        <label class="flex ml-10">
                            <input type="radio" name="display_type" value="chart" class="mt-0-i">
                            <span class="ml-3 white-space-nowrap">{!! trans('resource::view.Chart') !!}</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{!! route('resource::monthly_report.recruit.export') !!}" class="btn btn-primary">
                    <span>{!! trans('resource::view.Render') !!} <i class="fa fa-spin fa-refresh hidden"></i></span>
                </a>
                <button class="btn btn-primary btn-search-filter">
                    <span>{!! trans('team::view.Search') !!} <i class="fa fa-spin fa-refresh hidden"></i></span>
                </button>
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="row tab-data-table">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table dataTable table-striped table-bordered">
                        <thead>
                        <tr>
                            <th rowspan="2" class="vertical-align-middle">{!! trans('resource::view.Employee') !!}</th>
                            @foreach ($selectedChannels as $channel)
                            <th colspan="4" class="text-center vertical-align-middle bg-header-table">{{ $channel->name }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($selectedChannels as $channel)
                                @php
                                    if (!isset($groupChannels[$channel->id])) {
                                        $groupChannels[$channel->id] = [
                                            'id' => $channel->id,
                                            'name' => $channel->name,
                                            'color' => $channel->color,
                                            'recruiters' => [],
                                            'total' => 0,
                                            'fail' => 0,
                                            'pass' => 0,
                                            'offer' => 0,
                                        ];
                                    }
                                @endphp
                            <th class="text-center">
                                <span>{!! trans('resource::view.Total') !!}</span>
                                <span>({!! $groupChannels[$channel->id]['total'] !!})</span>
                            </th>
                            <th class="text-center">
                                <span>{!! trans('resource::view.Fail') !!}</span>
                                <span>({!! $groupChannels[$channel->id]['fail'] !!})</span>
                            </th>
                            <th class="text-center">
                                <span>{!! trans('resource::view.Pass') !!}</span>
                                <span>({!! $groupChannels[$channel->id]['pass'] !!})</span>
                            </th>
                            <th class="text-center">
                                <span>{!! trans('resource::view.Offer') !!}</span>
                                <span>({!! $groupChannels[$channel->id]['offer'] !!})</span>
                            </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                            @foreach ($collectionModel as $recruiter)
                            <tr>
                                <td class="min-w-150">{{ $recruiter->name }}</td>
                                @foreach ($selectedChannels as $channel)
                                    @php
                                        if (!isset($groupChannels[$channel->id]['recruiters'][$recruiter->email])) {
                                            $groupChannels[$channel->id]['recruiters'][$recruiter->email] = [
                                                'total' => 0,
                                                'fail' => 0,
                                                'pass' => 0,
                                                'offer' => 0,
                                            ];
                                        }
                                        $data = $groupChannels[$channel->id]['recruiters'][$recruiter->email];
                                    @endphp
                                    <td class="text-center text-bold">{!! $data['total'] !!}</td>
                                    <td class="text-center text-bold color-red">{!! $data['fail'] !!}</td>
                                    <td class="text-center text-bold color-blue">{!! $data['pass'] !!}</td>
                                    <td class="text-center text-bold color-green">{!! $data['offer'] !!}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-body">
                    @include('team::include.pager')
                </div>
            </div>
        </div>
        <div class="row tab-chart hidden">
            <div class="col-md-12">
                <p class="note">{!! trans('resource::view.note_change_color_channel') !!}</p>
            </div>
            <div class="col-md-12 h-500">
                <canvas id="candidateFail"></canvas>
            </div>
            <div class="col-md-12 h-500">
                <canvas id="candidatePass"></canvas>
            </div>
            <div class="col-md-12 h-500">
                <canvas id="candidateOffer"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-change-color">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <form method="POST" action="{!! route('resource::monthly_report.channel.changeColor') !!}">
                    {!! csrf_field() !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{!! trans('Change color') !!}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-md-3">{!! trans('Channel') !!}</label>
                            <div class="col-md-9">
                                <input type="text" name="channel" class="form-control" disabled>
                                <input type="hidden" name="channelId">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3" for="color">{!! trans('Color') !!}</label>
                            <div class="col-md-9">
                                <input type="text" name="color" class="form-control" id="color">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{!! trans('Close') !!}</button>
                <button type="submit" class="btn btn-success btn-save">{!! trans('Save') !!} <i class="fa fa-spin fa-refresh hidden"></i></button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.1/js/bootstrap-colorpicker.min.js"></script>
<script src="{!! CoreUrl::asset('lib/js/Chart.bundle.js')  !!}"></script>
<script>
    var recruiters = {!! collect($collectionModel->items()) !!};
    var selectedChannels = {!! $selectedChannels !!};
    var groupChannels = {!! collect($groupChannels) !!};
    var txtQuantityCandidateFailInterview = '{!! trans('resource::view.Quantity candidate fail interview') !!}';
    var txtQuantityCandidatePassInterview = '{!! trans('resource::view.Quantity candidate pass interview') !!}';
    var txtQuantityCandidatePassOffer = '{!! trans('resource::view.Quantity candidate pass offer') !!}';
</script>
<script src="{{ CoreUrl::asset('resource/js/recruit/monthly_report.js') }}"></script>
@stop
