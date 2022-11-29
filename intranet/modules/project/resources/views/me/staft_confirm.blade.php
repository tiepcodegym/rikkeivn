@extends('layouts.default')

@section('title', trans('project::me.Monthly Evaluation'))

@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
@endsection

@section('content')

<?php
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Project\Model\MeComment;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Team\View\Permission;
use Carbon\Carbon;
use Rikkei\Project\Model\Project;
use Rikkei\Project\View\MeView;

$evalTable = MeEvaluation::getTableName();
$user = Permission::getInstance()->getEmployee();
$request = request();
$filter_project_id = FormView::getFilterData('number', $evalTable.'.project_id');
$arrayTypeLabel = Project::labelTypeProject();
$timeSepMonth = Carbon::parse(config('project.me_sep_month'));
?>

<div class="box box-info _me_review_page">
    <div class="box-body">
        <div class="row">
            <div class="col-md-8">
                <div class="form-inline box-action select-media mgr-20">
                    <select class="form-control select-search select-grid filter-grid has-search" name="filter[number][{{$evalTable}}.project_id]"
                            id="filter_project">
                        <option value="">{{trans('project::me.Select project')}}</option>
                        <option value="NULL" {{ $filter_project_id == 'NULL' ? 'selected' : '' }}>&nbsp;</option>
                        @if (!$filter_projects->isEmpty())
                            @foreach($filter_projects as $proj)
                            <?php
                            if (!$proj->project_id) {
                                continue;
                            }
                            $selected = '';
                            if ($request->has('project_id') && $proj->project_id == $request->get('project_id')) {
                                $selected = 'selected';
                            } else if ($proj->project_id == $filter_project_id) {
                                $selected = 'selected';
                            }
                            ?>
                            <option value="{{$proj->project_id}}" {{ $selected }}>
                                {{$proj->name}}
                            </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-inline box-action select-media mgr-20">
                    <select class="form-control select-search select-grid filter-grid has-search" name="filter[month][eval_time]" id="filter_eval_time">
                        <?php
                        $prev_month = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();
                        ?>
                        <option value="_all_">{{trans('project::me.Select month')}}</option>
                        @if (!$filterMonths->isEmpty())
                        @foreach ($filterMonths as $month)
                        <?php
                        $selected = '';
                        if (($filter_month != '_all_') &&
                                (($request->has('time') && $month->eval_month == $request->get('time')) ||
                                ($month->eval_time == $filter_month) ||
                                ($month->eval_time == $prev_month && !$filter_month && !$request->has('time')))) {
                            $selected = 'selected';
                        }
                        ?>
                        <option value="{{$month->eval_time}}" {{ $selected }}>
                             {{ $month->eval_month }}
                        </option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-inline box-action select-media">
                    <select class="form-control select-search select-grid filter-grid" name="filter[{{ $evalTable }}.status]">
                        <option value="">{{ trans('project::me.Select status') }}</option>
                        <?php $statuses = MeEvaluation::filterStatus(); ?>1
                        @foreach ($statuses as $key => $status)
                        <?php
                        $selected = '';
                        if ($key == FormView::getFilterData($evalTable.'.status')) {
                            $selected = 'selected';
                        }
                        ?>
                        <option value="{{$key}}" {{ $selected }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <?php echo \Rikkei\Project\View\MeView::renderNewVerLink(
                        route('me::profile.confirm', ['month' => $timeSepMonth->addMonthNoOverflow()->format('Y-m')])
                    ); ?>
            </div>
            <div class="col-md-4 text-right">
                @include('team::include.filter')
                <a target="_blank" href="{{ route('project::project.eval.help') }}" class="btn btn-primary">{{ trans('project::me.Help') }}</a>
            </div>
        </div>
        <div class="text-right"><i>{{ trans('project::me.Right click to comment') }}</i></div>
    </div>

    <div class="pdh-10">
        <div class="table-responsive _me_table_responsive">
            <table id="_me_table" class="table table-striped table-bordered table-hover table-grid-data table-th-middle" style="border-left: 1px solid #ccc;">

                @include('project::me.template.thead', ['staff_view' => true, 'has_month' => true, 'action_col' => true])

                <tbody>
                    @if (!$collectionModel->isEmpty())
                    @foreach($collectionModel as $item)
                    <?php
                    
                    $projectPoint = $item->proj_point;
                    $eval_time = $item->eval_time->format('m-Y');
                    //get work dates from concat dates
                    $realTime = $item->effort;
                    
                    $projIndex = $item->proj_index;
                    if ($projIndex === null) {
                        $projIndex = $item->getFactorProjectType($item->project_type);
                    }
                    //list attr point from concat attr
                    $listPoint = MeView::getListPoint($item->point_attrs);
                    //list comment class from cancat attr
                    $listCommentClass = MeView::getCommentClass($item->cmt_attrs);
                    $commentNoteClass = isset($listCommentClass[-1]) ? 'has_comment ' . implode(' ', $listCommentClass[-1]) : '';

                    ?>

                    <tr data-eval="{{$item->id}}" data-project="{{$item->project_id}}" data-change="0">
                        <td class="_nowwrap date-tooltip">
                            {{ $eval_time }}
                            <?php
                            $monthFormat = $item->eval_time->format('Y-m');
                            ?>
                            @if (isset($listRangeMonths[$monthFormat]))
                            <i data-toggle="tooltip" data-placement="bottom" class="fa fa-question-circle"
                               title="{{ $listRangeMonths[$monthFormat]['start'] . ' : ' . $listRangeMonths[$monthFormat]['end'] }}" ></i>
                            @endif
                        </td>
                        @if ($item->project_id)
                        <td class="_break_word"><a href="{{ route('project::point.edit', ['id' => $item->project_id]) }}" target="_blank">{{ $item->proj_name }}</a></td>
                        @else
                        <td>{{ $item->team_name }}</td>
                        @endif
                        <td>{{ MeView::getProjectTypeLabel($item->project_type, $arrayTypeLabel) }}</td>
                        @if (!$normalAttrs->isEmpty())
                            @foreach($normalAttrs as $attr)
                                <?php
                                $attrPoint = $item->getAttrPoint($listPoint, $attr->id, $attr->default);
                                $comment_class = isset($listCommentClass[$attr->id]) ? 'has_comment '.implode(' ', $listCommentClass[$attr->id]) : '';
                                ?>
                                <td class="point_group {{ $comment_class }}" data-group="{{$attr->group}}" data-attr="{{$attr->id}}" title="{{ trans('project::me.Right click to comment') }}">
                                    <span class="_me_attr_point" data-attr="{{$attr->id}}" data-weight="{{$attr->weight}}">{{ $attrPoint != MeAttribute::NA ? $attrPoint : 'N/A' }}</span>
                                    @include('project::me.template.comments', ['user' => $user, 'item_id' => $item->id, 'attr_id' => $attr->id, 'project_id' => $item->project_id, 'is_staff' => true])
                                </td>
                            @endforeach
                        @endif
                        <td class="_avg_rules auto_fill _none"></td>
                        @if (!$performAttrs->isEmpty())
                            @foreach($performAttrs as $attr)
                                <?php
                                $attrPoint = isset($listPoint[$attr->id]) ? $listPoint[$attr->id] : round($attr->default);
                                $comment_class = isset($listCommentClass[$attr->id]) ? 'has_comment '.implode(' ', $listCommentClass[$attr->id]) : '';
                                ?>
                                <td class="point_group {{ $comment_class }}" data-group="{{$attr->group}}" data-attr="{{$attr->id}}" title="{{ trans('project::me.Right click to comment') }}">
                                    <span class="_me_attr_point _none" data-attr="{{$attr->id}}" data-weight="{{$attr->weight}}">{{ $attrPoint }}</span>
                                    <span>{{ $item->getLabelPerformPoint($attrPoint, $attr->has_na) }}</span>
                                    @include('project::me.template.comments', ['user' => $user, 'item_id' => $item->id, 'attr_id' => $attr->id, 'project_id' => $item->project_id, 'is_staff' => true])
                                </td>
                            @endforeach
                        @endif
                        <td class="_pf_person_avg auto_fill _none"></td>
                        <td class="_project_point auto_fill">{{$projectPoint}}</td>
                        <td class="_project_type auto_fill">{{ $item->getFactorProjectType($item->project_type) }}</td>
                        <td class="auto_fill _none"><strong class="_perform_value"></strong></td>
                        <td class="auto_fill"><strong>{{$item->avg_point}}</strong></td>
                        <td class="auto_fill">{{$realTime}}</td>
                        <td class="_break_word auto_fill">{{$item->contribute_label}}</td>
                        <td class="note_group {{ $commentNoteClass }}">
                            @include('project::me.template.comments', ['user' => $user, 'item_id' => $item->id, 'attr_id' => null, 'project_id' => $item->project_id, 'is_staff' => true, 'comment_type' => MeComment::TYPE_NOTE])
                        </td>
                        <td class="dropdown _action_btns auto_fill">
                            {{$item->status_label}}
                        </td>
                        @if ($item->status == MeEvaluation::STT_APPROVED)
                        <td class="_nowwrap">
                            {!! Form::open(['method' => 'put', 'route' => ['project::project.eval.staff_update', $item->id], 'class' => 'form-inline no-validate form_item_confirm']) !!}
                            <input type="hidden" name="status" value="{{ MeEvaluation::STT_FEEDBACK }}" />
                            <button type="submit" class="btn-delete _btn_feedback {{$item->canChangeStatus(MeEvaluation::STT_FEEDBACK) ? '' : 'is-disabled'}}" data-noti="{{trans('project::me.Are you sure you want to do this action', ['action' => trans('project::me.Feedback')])}}" data-warning="{{trans('project::me.You must comment before feedback')}}">{{trans('project::me.Feedback')}}</button>
                            {!! Form::close() !!}

                            {!! Form::open(['method' => 'put', 'route' => ['project::project.eval.staff_update', $item->id], 'class' => 'form-inline no-validate form_item_confirm']) !!}
                            <input type="hidden" name="status" value="{{ MeEvaluation::STT_CLOSED }}" />
                            <button type="submit" class="btn-add _btn_accept" {{ $item->canChangeStatus(MeEvaluation::STT_CLOSED) ? '' : 'disabled' }} data-noti="{{trans('project::me.Are you sure you want to do this action', ['action' => trans('project::me.Accept')])}}">{{trans('project::me.Accept')}}</button>
                            {!! Form::close() !!}
                        </td>
                        @else
                        <td colspan="2" style="border-right: 1px solid #f4f4f4;"></td>
                        @endif
                    </tr>
                    @endforeach

                    @if (!$collection['has_project'] && $collectionModel->count() > 1 && $filter_month != '_all_')
                    <?php
                        $sumPoint = 0;
                        $sumDay = 0;
                        $items = $collection['all']->select($evalTable.'.*')->get();
                        foreach ($items as $item) {
                            $effort = $item->effort == 0 ? 1 : $item->effort;
                            $sumPoint += $item->avg_point * $effort;
                            $sumDay += $effort;
                        }
                        $avgMonth = round($sumPoint/$sumDay, 2);
                    ?>
                    <tr class="month_sumary">
                        <td colspan="{{ $normalAttrs->count() + $performAttrs->count() + 5 }}" class="text-right">{{trans('project::me.Summary')}} </td>
                        <td colspan="2" class="auto_fill">{{ $avgMonth }}</td>
                        <td colspan="1" class="auto_fill">{{ MeEvaluation::getContributeLabel($avgMonth) }}</td>
                        <td colspan="5"></td>
                    </tr>
                    @endif

                    @else
                    <tr>
                        <td colspan="{{ $normalAttrs->count() + $performAttrs->count() + 11 }}">
                            <h4 class="text-center">{{trans('project::me.No result')}}</h4>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="cleafix"></div>
    <div class="box-body">
        @include('team::include.pager')
    </div>

</div>

<div id="overlay" class="hidden"><i class="fa fa-spin fa-refresh iloading"></i></div>
@endsection

@section('warn_confirn_class', 'modal-default')

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
@include('project::me.template.script')
<script>
    newMeUrl = "{{ route('me::profile.confirm') }}";
</script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/me_script.js') }}"></script>
<script>
    $('body').on('change', '#filter_eval_time', function () {
        if (checkNewVersion($(this).val(), $('#filter_project').val())) {
            return false;
        }
    });
    var monthVal = $('#filter_eval_time').val();
    if (monthVal) {
        checkNewVersion(monthVal, $('#filter_project').val());
    }

    @if($filter_month && $filter_month != '_all_')
        var filterMonth = new Date('{{ $filter_month }}');
        var dMonth = filterMonth.getMonth() + 1;
        var month = filterMonth.getFullYear() + '-' + (dMonth < 10 ? '0' + dMonth : dMonth);
        checkNewVersion(month, $('#filter_project').val());
    @endif
</script>
@endsection

