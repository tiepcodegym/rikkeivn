@extends('layouts.default')
<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\Model\Channels;
use Rikkei\Resource\Model\Candidate;
use Carbon\Carbon;

$channelTable = Channels::getTableName();
$candidateTableName = Candidate::getTableName();
$type = [
    Channels::COST_FIXED,
    Channels::COST_CHANGE
];
$total = 0;
$count = 0;
$carbon = Carbon::now();
$now = $carbon->format('Y-m-d');
foreach ($collectionModel as $value) {
    $count += $value->count;
}

$filter = Form::getFilterData();
$start_at = isset($filter['search']['candidates.created_at']) ? $filter['search']['candidates.created_at'] :  $carbon->firstOfMonth()->format('Y-m-d');
$end_at = isset($filter['search']['candidates.end_at']) ? $filter['search']['candidates.end_at'] :  $carbon->lastOfMonth()->format('Y-m-d');
?>

@section('title')
{{ trans('resource::view.Channel.List title') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link href="{{ asset('sales/css/customer_index.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cost"
                                   class="col-sm-2 control-label">{{ trans('resource::view.From date') }}</label>
                            <div class="col-md-2">
                            <span>
                                <input type='text' class="filter-grid form-control date"
                                       name="filter[search][{{ $candidateTableName }}.created_at]"
                                       value="{{ isset($filter['search']['candidates.created_at']) ? $filter['search']['candidates.created_at'] : $start_at }}"
                                       placeholder="YYYY-MM-DD" tabindex=4 autocomplete="off"
                                />
                            </span>
                            </div>
                            <label for="cost"
                                   class="col-sm-2 control-label">{{ trans('resource::view.To date') }}</label>
                            <div class="col-md-2">
                            <span>
                                <input type='text' class="filter-grid form-control date"
                                       name="filter[search][{{ $candidateTableName }}.end_at]"
                                       value="{{ isset($filter['search']['candidates.end_at']) ? $filter['search']['candidates.end_at'] : $end_at }}"
                                       placeholder="YYYY-MM-DD" tabindex=4 autocomplete="off"
                                />
                            </span>
                            </div>
                        </div>
                    </div>
                    @include('team::include.filter')
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data recommend-detail">
                        <thead>
                        <tr>
                            <th class="col-id width-5-per">{{ trans('sales::view.Numerical order') }}</th>
                            <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('resource::view.Channel.List.Channel name') }}</th>
                            <th class="sorting {{ Config::getDirClass('created_at') }} col-name" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('resource::view.Channel.List.Created at') }}</th>
                            <th class="sorting {{ Config::getDirClass('updated_at') }} col-name" data-order="updated_at" data-dir="{{ Config::getDirOrder('updated_at') }}">{{ trans('resource::view.Channel.List.Updated at') }}</th>
                            <th class="sorting {{ Config::getDirClass('type') }} col-name" data-order="type" data-dir="{{ Config::getDirOrder('type') }}">{{ trans('resource::view.Channel.Cost type') }}</th>
                            <th class="{{ Config::getDirClass('cost') }} col-name">{{ trans('resource::view.Request.Detail.Cost') }}
                                <i title="{!! trans('resource::view.cost tip') !!}" class="fa fa-question-circle i-me-tooltip" data-class="text-left"></i></th>
                            <th class="sorting {{ Config::getDirClass('count') }} col-name" data-order="count" data-dir="{{ Config::getDirOrder('count') }}">{{ trans('resource::view.Amount') }}
                                <i title="{!! trans('resource::view.count tip') !!}" data-class="text-left" class="fa fa-question-circle i-me-tooltip"></i></th>
                            <th class="col-action width-10-per">{{ trans('resource::view.Candidate.History.Action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $channelTable }}.name]" value="{{ Form::getFilterData("{$channelTable}.name") }}" placeholder="{{ trans('sales::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select class="form-control recruiter-box filter-grid" id="recruiterList" name="filter[{{ $channelTable }}.type]">
                                            <option value="">&nbsp;</option>
                                            @foreach($type as $option)
                                                <option value="{{ $option }}" {{ isset($filter['recruit_channel.type']) ? ($filter['recruit_channel.type'] == $option ? 'selected' : '') : '' }}
                                                >{{ $option == Channels::COST_FIXED ?  trans('resource::view.Channel.Cost fixed') :  trans('resource::view.Channel.Cost change') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td class="total-money">
                            </td>
                            <td>
                                <strong> {{ $count }} </strong>
                            </td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr id="{{ $item->id }}" data-quantity="{{ $item->count }}">
                                    <td>{{ $i }}</td>
                                    <td data-col-name="{{ $item->name }}" class="name {{ $item->name }}">{{ $item->name }}</td>
                                    <td>{{ strtotime($item->created_at) < 0 ? '' : $item->created_at->format('Y-m-d') }}</td>
                                    <td>{{ strtotime($item->updated_at) < 0 ? '' : $item->updated_at->format('Y-m-d') }}</td>
                                    <td>{{ $item->type == Channels::COST_FIXED ? trans('resource::view.Channel.Cost fixed') : trans('resource::view.Channel.Cost change') }}</td>
                                    <td class="cost-{{ $i }}">
                                        <?php
                                        $money = $date = $channelTotalDate = $totalMoney = 0;
                                        if ($item->type == Channels::COST_CHANGE && isset($item->channelFees)) {
                                            $money += $item->cost;
                                        } else {
                                            foreach ($item->channelFees as $val) {
                                                if (strtotime($start_at) <= strtotime($val->end_date) && strtotime($end_at) >= strtotime($val->start_date)) {
                                                    $start = strtotime($val->start_date) >= strtotime($start_at) ? strtotime($val->start_date) : strtotime($start_at);
                                                    $end = strtotime($val->end_date) <= strtotime($end_at) ? strtotime($val->end_date) : strtotime($end_at);
                                                    $diff = $end - $start;
                                                    $channelTotalDate = (strtotime($val->end_date) - strtotime($val->start_date)) / 86400 + 1;
                                                    $channelTotalDate = $channelTotalDate > 0 ? (int)$channelTotalDate : 1;
                                                    $money += ((int)$val->cost * (int)($diff / 86400 + 1)) / $channelTotalDate;
                                                }
                                            }
                                        }
                                        $money = $money >= 0 ? $money : 0;
                                        $total += (int)$money;
                                        ?>
                                        {{ number_format($money) }}
                                    </td>
                                    <td>{{ $item->count }}</td>
                                    <td class="group-button-action text-center">
                                        <input id="{{ $item->id }}" value="{{ $item->id }}" class="toggle-trigger btn-change-status" type="checkbox" data-toggle="toggle" {{ $item->status == 1 ? 'checked' : '' }}>
                                        <a href="{{ route('resource::channel.edit', ['id' => $item->id]) }}" class="btn-edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if (Permission::getInstance()->isAllow('resource::channel.delete'))
                                            <form action="{{route('resource::channel.delete')}}" method="post" class="form-inline">
                                                {!! csrf_field() !!}
                                                <input type="hidden" name="id" value="{{$item->id}}">
                                                <button class="btn-delete delete-confirm" title="{{trans('resource::view.Candidate.List.Delete')}}"
                                                        {{ $item->count == 0 ? 'disabled' : '' }}
                                                >
                                                    <span><i class="fa fa-trash"></i></span>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center">
                                    <h2 class="no-result-grid">{{trans('sales::view.No results found')}}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="box-body">
                @include('team::include.pager')
            </div>
        </div>
    </div>
</div>
@include('resource::candidate.include.modal.channel_candidate_detail')
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script>
        var start = '{{ $start_at }}';
        var end = '{{ $end_at }}';
        var total = '{{ $total }}';
        var urlListRecommendChannel = '{{ route('resource::candidate.ajaxGetListRecommendByChannel') }}';
        var ajaxChangeStatus = '{{ route('resource::channel.ajaxToggleStatus') }}';
        var enabled = {{ Channels::ENABLED }}

        $.fn.datepicker.defaults.todayHighlight = true;
        $('.date').datepicker({
            autoclose: true,
            todayHighlight: true,
            todayBtn: "linked",
            format: 'yyyy-mm-dd'
        });
        $(document).on('click', '.recommend-detail tbody tr td:not(.group-button-action)', function () {
            var id = $(this).parent().attr('id');
            var quantity = $(this).parent().attr('data-quantity');
            var dom = '';
            if (typeof id !== 'undefined' && quantity > 0) {
                $.ajax({
                    url: urlListRecommendChannel,
                    type: 'get',
                    data: {
                        channel_id: id,
                        start: start,
                        end: end,
                        _token: siteConfigGlobal.token
                    },
                    success: function (data) {
                        if (data.response.length !== 0) {
                            let checkType = parseInt(data.response[0].type) === 1 ? 'hidden' : '';
                            let className = data.response[0].name;
                            for (let i = 0; i < data.response.length; i++) {
                                let th = '<tr><td>' + (i + 1) + '</td>' + '<td>' + $("<div/>").text(data.response[i].employee_name).html() + '</td>' + '<td>' + data.response[i].employee_code + '</td>' + '<td>' + moment(new Date(data.response[i].join_date)).format('YYYY-MM-DD') + '</td>' + '<td class="' + checkType + '">' + (data.response[i].cost || '') + '</td></tr>';
                                dom += th.replace('\[(.*?)\]', '[' + i + ']');
                            }
                            if (checkType) {
                                $('#modal-candidate-detail table thead th').last().attr('class', 'hidden')
                            } else {
                                $('#modal-candidate-detail table thead th').last().attr('class', '')
                            }
                            $(".candidate-detail").html(dom);
                            $('.channel-name').text(className);
                        }
                    },
                    fail: function () {
                        alert("Ajax failed to fetch data");
                    }
                });
                $('#modal-candidate-detail').modal('show');
            }
        });

        function formatNumber(nStr, decSeperate, groupSeperate) {
            nStr += '';
            x = nStr.split(decSeperate);
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + groupSeperate + '$2');
            }
            return x1 + x2;
        }
        $(".total-money").html('<strong>' + formatNumber(total, '.', ',') + '</strong>');

        $(function () {
            $('.btn-change-status').change(function () {
                var id = $(this).val();
                $.ajax({
                    url: ajaxChangeStatus,
                    type: 'POST',
                    data: {
                        channel_id: id,
                        _token: siteConfigGlobal.token
                    },
                    success: function (data) {
                        if (data.status === enabled) {
                            $(this).prop('checked', true).change()
                        } else {
                            $(this).prop('checked', false).change()
                        }
                        return false;
                    },
                    fail: function () {
                        alert("Ajax failed to fetch data");
                    }
                });
            })
        })
    </script>
@endsection
