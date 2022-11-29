<?php
use Rikkei\Team\View\TeamList;
use Rikkei\Test\Models\Result;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Team\View\Config;
use Rikkei\Test\View\ViewTest;

$pageTitle = trans('test::test.list_results');
if ($testerType == ViewTest::TESTER_PUBLISH) {
    $pageTitle .= ' (public)';
}
$notAnswers = [];
$testerTypePublish = ($testerType == ViewTest::TESTER_PUBLISH);
$listTeam = TeamList::toOption(null, false, false);
?>

@extends('layouts.default')

@section('title', $pageTitle)

@section('css')

    @include('test::template.css')

@endsection

@section('content')
    <div class="nav-tabs-custom test-tabs">

        <div class="right-barbox">
            <a href="{{route('test::admin.test.index')}}" class="btn btn-default">
                <i class="fa fa-long-arrow-left"></i> {{trans('test::test.back')}}
            </a>
        </div>

        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#lists_tab" data-toggle="tab" aria-expanded="true">{{ trans('test::test.list_results') }}</a>
            </li>
            <li class="analytic_tab">
                <a href="#analytic_tab" data-toggle="tab" aria-expanded="false">{{ trans('test::test.analytic') }}</a>
            </li>
        </ul>

        <div class="tab-content">
            <?php
            $listUrl = request()->url() . '/' . 'lists_tab';
            $analyticUrl = request()->url() . '/' . 'analytic_tab';
            ?>
            <div class="tab-pane active filter-wrapper" id="lists_tab" data-url="{{ $listUrl }}">

                <div class="table_nav margin-bottom-5 row">
                    <div class="col-sm-6">
                        <strong style="font-size: 20px;">{{ $test->name }} - {{ $test->time }}({{ trans('test::test.minute') }}) - {{ $total_questions }} {{ trans('test::test.question') }}</strong>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button type="button" id="btn_mass_del" class="btn btn-danger"
                                data-url="{{ route('test::admin.test.multi_delete') }}"
                                data-noti="{{ trans('test::validate.Are you sure want to delete') }}">
                            {{ trans('test::test.delete') }}
                        </button>
                        <button type="button" id="btn_export_result" class="btn btn-success"
                                id="btn_export_result"
                                data-url="{{ route('test::admin.test.export_results', [$test->id, 'tester_type'=>$testerType]) }}"
                                data-noti="{{ trans('test::test.Are you sure do action?') }}">{{ trans('test::test.export_excel') }}</button>
                        @include('team::include.filter')
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table dataTable table-hover table-striped table-bordered" id="tbl_test_result">
                        <thead>
                        <tr>
                            <th><input type="checkbox" class="check_all"></th>
                            <th>{{ trans('core::view.NO.') }}</th>
                            <th class="sorting {{ Config::getDirClass('employee_name', $listUrl) }} col-name" data-order="employee_name" data-dir="{{ Config::getDirOrder('employee_name', $listUrl) }}">{{ trans('test::test.full_name') }}</th>
                            <th class="sorting {{ Config::getDirClass('employee_email', $listUrl) }} col-name" data-order="employee_email" data-dir="{{ Config::getDirOrder('employee_email', $listUrl) }}">{{ trans('test::test.email') }}</th>
                            <th class="sorting {{ Config::getDirClass('team_names', $listUrl) }} col-name" data-order="team_names" data-dir="{{ Config::getDirOrder('team_names', $listUrl) }}">{{ trans('test::test.Division') }}</th>
                            @if ($testerTypePublish)
                                <th class="sorting {{ Config::getDirClass('phone', $listUrl) }} col-name" data-order="phone" data-dir="{{ Config::getDirOrder('phone', $listUrl) }}">{{ trans('test::test.phone_number') }}</th>
                            @endif
                            <th class="sorting {{ Config::getDirClass('begin_at', $listUrl) }} col-name" data-order="begin_at" data-dir="{{ Config::getDirOrder('begin_at', $listUrl) }}">{{ trans('test::test.begin_at') }}</th>
                            <th class="sorting {{ Config::getDirClass('created_at', $listUrl) }} col-name" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at', $listUrl) }}">{{ trans('test::test.end time') }}</th>
                            <th class="sorting {{ Config::getDirClass('total_finish_time', $listUrl) }} col-name" data-order="total_finish_time" data-dir="{{ Config::getDirOrder('total_finish_time', $listUrl) }}">{{ trans('test::test.total_finish_time') }}</th>
                            <th class="sorting {{ Config::getDirClass('total_answers', $listUrl) }} col-name" data-order="total_answers" data-dir="{{ Config::getDirOrder('total_answers', $listUrl) }}">{{ trans('test::test.total_answers') }}</th>
                            <th class="sorting {{ Config::getDirClass('total_corrects', $listUrl) }} col-name" data-order="total_corrects" data-dir="{{ Config::getDirOrder('total_corrects', $listUrl) }}">{{ trans('test::test.total_corrects')}}</th>
{{--                            <th>{{ trans('test::test.total written questions')}}</th>--}}
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                <input type="text" name="filter[rs.employee_name]"
                                       value="{{ FormView::getFilterData('rs.employee_name', null, $listUrl) }}"
                                       placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control"/>
                            </td>
                            <td>
                                <input type="text" name="filter[rs.employee_email]"
                                       value="{{ FormView::getFilterData('rs.employee_email', null, $listUrl) }}"
                                       placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control"/>
                            </td>
                            <?php
                            $filterTeamId = FormView::getFilterData('except', 'teams.id', $listUrl);
                            ?>
                            <td>
                                <select class="form-control select-search filter-grid select-grid"
                                        name="filter[except][teams.id]">
                                    <option value="">All</option>
                                    @foreach ($listTeam as $key => $label)
                                        <option value="{{ $label['value'] }}" {{ $filterTeamId == $label['value'] ? 'selected' : '' }}>
                                            {{ $label['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            @if ($testerTypePublish)
                                <td>
                                    <input type="text" name="filter[rs.phone]"
                                           value="{{ FormView::getFilterData('rs.phone', null, $listUrl) }}"
                                           placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control"/>
                                </td>
                            @endif
                            <td></td>
                            <td></td>
                            <td></td>
{{--                            <td></td>--}}
                        </tr>

                        @if(!$collectionModel->isEmpty())
                            <?php
                            $perPage = $collectionModel->perPage();
                            $page = $collectionModel->currentPage();
                            ?>
                            @foreach($collectionModel as $indexKey => $itemData)
                                <tr class="tr-parent" data-id="{{ $itemData->id }}" id="tr-parent-{{ $itemData->id }}">
                                    <td><input type="checkbox" class="check_item" value="{{ $itemData->id }}"></td>
                                    <td>
                                        <?php
                                        $noOrder = $indexKey + 1 + ($page - 1) * $perPage;
                                        ?>
                                        {{ $noOrder }}

                                        @if ($itemData->count_data > 1)
                                            <a href="#" class="link btn-show-more" data-has-click="0" data-current-index="{{ $noOrder }}" data-test-result-id="{{ $itemData->id }}">
                                                <i class="fa fa-chevron-circle-down"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="_break_all">{{ $itemData->employee_name }}</td>
                                    <td>{{ $itemData->employee_email }}</td>
                                    <td>{{ $itemData->team_names }}</td>
                                    @if ($testerTypePublish)
                                        <td>{{ $itemData->phone }}</td>
                                    @endif
                                    <td>{{ $itemData->begin_at }}</td>
                                    <td>{{ $itemData->created_at }}</td>
                                    <td>{{ $itemData->total_finish_time }}</td>
                                    <td>{{ $itemData->total_answers }}</td>
                                    <td>{{ $itemData->total_corrects }}</td>
{{--                                    <td>{{ isset($itemData->total_written_question) ? $itemData->total_written_question : null }}</td>--}}
                                    <td>
                                        <a data-toggle="tooltip" title="{{trans('test::test.view')}}"
                                           href="{{ route('test::result', ['id' => $itemData['id']]) }}" target="_blank" class="btn btn-primary"><i class="fa fa-eye"></i></a>
                                        {!! Form::open(['class' => 'form-inline', 'method' => 'delete', 'route' => ['test::admin.test.remove_result', $itemData->id]]) !!}
                                        <button type="submit" class="btn-delete delete-confirm" data-toggle="tooltip" title="{{trans('test::test.delete')}}">
                                            <i class="fa fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center"><h4>{{trans('test::test.no_item')}}</h4></td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="cleafix"></div>

                <div class="box-body">
                    @include('team::include.pager', ['urlSubmitFilter' => $listUrl])
                </div>
            </div>

            <!-- /.tab-pane -->
            <div class="tab-pane filter-wrapper" id="analytic_tab" data-url="{{ $analyticUrl }}">
            </div>
            <!-- /.tab-pane -->
        </div>
        <!-- /.tab-content -->

    </div>

    <div class="modal fade modal-default" id="modal_detail_question">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('test::test.question') }} <span class="q_num"></span></h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">{{ trans('test::test.close') }}</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

@stop
@section('script')
    <script>
        var testId = '{{ $test->id }}';
        var type = '{{ $testerType }}';
        var urlGetAnalytics = '{{ route('test::admin.test.analytics') }}';
        var isGetAnalytics = false;

        $('tbody .btn-show-more').click(function (e) {
            e.preventDefault();
            var testResultId = $(this).attr('data-test-result-id');
            var currentIndex = $(this).attr('data-current-index');
            var dataHasClick = parseInt($(this).attr('data-has-click'));
            var testerTypePublish = '{{ $testerTypePublish ? 1 : 0 }}';
            if (dataHasClick === 0) {
                $.ajax({
                    method: "POST",
                    url: '{{ route('test::admin.test.get_more_result') }}',
                    data: {
                        _token: _token,
                        test_result_id: testResultId
                    },
                    async: false,
                    success: function (data) {
                        var htmlResultTest = '';
                        var countIndex = 2;
                        data.forEach(function (item, index) {
                            var linkView = '{{ route('test::result', ['id' => ':id']) }}';
                            linkView = linkView.replace(':id', item.id);
                            var linkDelete = '{{ route('test::admin.test.remove_result', ['id' => ':id']) }}';
                            linkDelete = linkDelete.replace(':id', item.id);
                            htmlResultTest = htmlResultTest + `<tr class="_none tr-child" data-id="` + testResultId + `">
                            <td><input type="checkbox" class="check_item" value="` + item.id + `"></td>
                            <td>
                                ` + currentIndex + '-' + countIndex + `
                            </td>
                            <td class="_break_all"></td>
                            <td></td>
                            <td></td>`;
                            if (parseInt(testerTypePublish) === 1) {
                                htmlResultTest = htmlResultTest + `<td></td>`;
                            }
                            htmlResultTest = htmlResultTest + `
                            <td>` + item.begin_at + `</td>
                            <td>` + item.created_at + `</td>
                            <td>` + item.total_finish_time + `</td>
                            <td>` + item.total_answers + `</td>
                            <td>` + item.total_corrects + `</td>
                            <td>
                                <a data-toggle="tooltip" title="{{trans('test::test.view')}}"
                                    href="` + linkView + `" target="_blank" class="btn btn-primary"><i class="fa fa-eye"></i></a>
                                <form method="POST" action="` + linkDelete + `" accept-charset="UTF-8" class="form-inline">
                                <input name="_method" type="hidden" value="DELETE">
                                    <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                    <button type="submit" class="btn-delete delete-confirm" data-toggle="tooltip" title="{{trans('test::test.delete')}}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                            </tr>`;
                            countIndex = countIndex + 1;
                        });
                        $(htmlResultTest).insertAfter('#tr-parent-' + testResultId);
                    }
                });
            }
            $(this).attr('data-has-click', 1);
            var elChild = $('tbody .tr-child[data-id="'+ testResultId +'"]');
            var clDown = 'fa-chevron-circle-down';
            var clUp = 'fa-chevron-circle-up';
            var icon = $(this).find('i');
            if (elChild.css('display') == 'none') {
                elChild.fadeIn();
                icon.removeClass(clDown).addClass(clUp);
            } else {
                elChild.fadeOut();
                icon.removeClass(clUp).addClass(clDown);
            }
        });

        $('.analytic_tab').click(function () {
            if (isGetAnalytics === false) {
                isGetAnalytics = true;
                $.ajax({
                    method: 'GET',
                    url: urlGetAnalytics,
                    data: {
                        'test_id': testId,
                        'testerType': type,
                    },
                    success: function (res) {
                        $('#analytic_tab').html(res);
                    },
                    error: function () {
                        bootbox.alert({
                            message: 'Error system',
                            backdrop: true,
                            className: 'modal-default',
                        });
                    },
                });
            }
        });
    </script>

    @include('test::template.script')

@stop
