@extends('layouts.default')

@section('title', trans('project::me.Monthly Evaluation'))

@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
@endsection

@section('content')

<?php
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\MeComment;
use Rikkei\Core\View\Form;
use Carbon\Carbon;
use Rikkei\Team\View\Permission;
use Rikkei\Project\View\MeView;
use Rikkei\Project\Model\Project;

$currentUser = Permission::getInstance()->getEmployee();

$request = request();
$evalTable = MeEvaluation::getTableName();
$rqProjectId = $request->get('project_id');
if (!$rqProjectId) {
    $rqProjectId = Form::getFilterData('excerpt', $evalTable.'.project_id');
}
$arrayTypeLabel = Project::labelTypeProject();
$sepMonth = Carbon::parse(config('project.me_sep_month'));
?>

<div class="box box-info _me_review_page">
    <div class="box-body">
        <div class="row">
            <div class="col-md-8 col-lg-9">
                @if ($isScopeCompany)
                <div class="form-inline box-action select-media mgr-35">
                    <select id="filter_teams" class="form-control select-search select-grid filter-grid has-search" name="filter[spec_data][team_id]">
                        <option value="">{{ trans('project::me.Select project team') }}</option>
                        @if (count($filterTeams) > 0)
                            @foreach ($filterTeams as $team)
                            <option value="{{ $team['value'] }}" {{ $team['value'] == Form::getFilterData('spec_data', 'team_id') ? 'selected' : '' }}>
                                {{ $team['label'] }}
                            </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                @endif
                <div class="form-inline select-media box-action mgr-35">
                    <select id="filter_projects" class="form-control select-search select-grid filter-grid" name="filter[excerpt][{{$evalTable}}.project_id]"
                            data-remote-url="{{ route('project::me.search.project.team.ajax') }}"
                            data-placeholder = "{{ trans('project::me.Project') }}"
                            data-options='{!! json_encode(["allowClear" => true]) !!}'>
                        @if ($projectOrTeamName)
                        <option value="{{ $rqProjectId }}">{{ $projectOrTeamName }}</option>
                        @endif
                    </select>
                </div>
                <div class="form-inline box-action select-media mgr-35">
                    <?php 
                    $filterMonthTime = '';
                    if ($request->get('time')) {
                        $filterMonthTime = Carbon::parse($request->get('time'))->format('Y-m');
                    }
                    if (!$filterMonthTime) {
                        $filterMonthTime = Form::getFilterData('except', 'month');
                    }
                    ?>
                    <input type="text" class="form-control filter-grid month-picker" name="filter[except][month]" value="{{ $filterMonthTime }}"
                           placeholder="{{ trans('project::me.Month') }}: Y-m" autocomplete="off">
                </div>
                @if ($teamName)
                <div class="form-inline box-action select-media">
                    <span class="team-of-project">{{$teamName}}</span>
                </div>
                @endif

                <?php echo MeView::renderNewVerLink(route('me::view.member.index', ['month' => $sepMonth->addMonthNoOverflow()->format('Y-m')])); ?>
            </div>
            <div class="col-md-4 col-lg-3 text-right">
                @include('team::include.filter')
                <a target="_blank" href="{{ route('project::project.eval.help') }}" class="btn btn-primary">{{ trans('project::me.Help') }}</a>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="text-right"><i>{{ trans('project::me.Right click to comment') }}</i></div>
    </div>

    <div class="pdh-10">
        <div class="table-responsive _me_table_responsive fixed-table-container">
            <table id="_me_table" require-comment="1" class="fixed-table table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle">

                @include('project::me.template.thead', ['has_month' => true, 'sort_contri' => true, 'notShowFilterStatus' => true, 'is_review_team' => true])

                <tbody>
                    @if (!$collectionModel->isEmpty())
                    @foreach($collectionModel as $item)
                    <?php
                    $projectPoint = $item->proj_point;
                    //get work dates from concat dates
                    $realTime = $item->effort;
                    $projIndex = $item->getFactorProjectType($item->project_type);
                    //list attr point from concat attr
                    $listPoint = MeView::getListPoint($item->point_attrs);
                    //list comment class from cancat attr
                    $listCommentClass = MeView::getCommentClass($item->cmt_attrs);
                    $commentNoteClass = isset($listCommentClass[-1]) ? 'has_comment ' . implode(' ', $listCommentClass[-1]) : '';
                    ?>
                    <tr data-eval="{{$item->id}}" data-project="{{$item->project_id}}" data-email="{{$item->employee_email}}" data-time="{{$item->eval_time}}">
                        <td class="_break_word fixed-col">{{$item->employee_code}}</td>
                        <td class="_nowwrap fixed-col">{{$item->eval_time->format('Y-m')}}</td>
                        <td class="_break_word fixed-col">{{ ucfirst(preg_replace('/@.*/', '', $item->employee_email)) }}</td>
                        <td class="_nowwrap">
                            @if ($item->project_id)
                            <a href="{{ route('project::point.edit', ['id' => $item->project_id]) }}" target="_blank">{{ $item->project_name }}</a>
                            @else
                            {{ $item->team_name }}
                            @endif
                        </td>
                        <td>{{ MeView::getProjectTypeLabel($item->project_type, $arrayTypeLabel) }}</td>
                        @if (!$normalAttrs->isEmpty())
                            @foreach($normalAttrs as $attr)
                                <?php
                                $attr_point = $item->getAttrPoint($listPoint, $attr->id, $attr->default);
                                $comment_class = isset($listCommentClass[$attr->id]) ? 'has_comment '.implode(' ', $listCommentClass[$attr->id]) : '';
                                ?>
                                <td class="point_group {{ $comment_class }}" data-group="{{$attr->group}}" data-attr="{{$attr->id}}" title="{{ trans('project::me.Right click to comment') }}">

                                    <span class="_me_attr_point" data-attr="{{$attr->id}}" data-weight="{{$attr->weight}}">{{ $attr_point != MeAttribute::NA ? $attr_point : 'N/A' }}</span>

                                    @include('project::me.template.comments', ['user' => $currentUser, 'item_id' => $item->id, 'attr_id' => $attr->id, 'project_id' => $item->id])
                                </td>
                            @endforeach
                        @endif
                        <td class="_avg_rules auto_fill _none"></td>
                        @if (!$performAttrs->isEmpty())
                            @foreach($performAttrs as $attr)
                                <?php
                                $attr_point = isset($listPoint[$attr->id]) ? $listPoint[$attr->id] : round($attr->default);
                                $comment_class = isset($listCommentClass[$attr->id]) ? 'has_comment '.implode(' ', $listCommentClass[$attr->id]) : '';
                                ?>
                                <td class="point_group {{ $comment_class }}" data-group="{{$attr->group}}" data-attr="{{$attr->id}}" title="{{ trans('project::me.Right click to comment') }}">
                                    <span class="_me_attr_point _none" data-attr="{{$attr->id}}" data-weight="{{$attr->weight}}">{{ $attr_point }}</span>
                                    <span>{{ $item->getLabelPerformPoint($attr_point, $attr->has_na) }}</span>

                                    @include('project::me.template.comments', ['user' => $currentUser, 'item_id' => $item->id, 'attr_id' => $attr->id, 'project_id' => $item->id])
                                </td>
                            @endforeach
                        @endif
                        <td class="_pf_person_avg auto_fill _none"></td>
                        <td class="_project_point auto_fill">{{ $projectPoint }}</td>
                        <td class="_project_type auto_fill">{{ $projIndex }}</td>
                        <td class="_perform_value auto_fill _none"></td>
                        <td class="auto_fill">{{$item->avg_point}}</td>
                        <td class="auto_fill">{{ $realTime }}</td>
                        <td class="_contribute_val _break_word auto_fill">
                            {{$item->contribute_label}}
                        </td>
                        <td class="note_group {{ $commentNoteClass }}">
                            @include('project::me.template.comments', ['user' => $currentUser, 'item_id' => $item->id, 'attr_id' => null, 'project_id' => $item->project_id, 'is_leader' => true, 'comment_type' => MeComment::TYPE_NOTE])
                        </td>
                        <td class="_break_word auto_fill">{{ $item->status_label }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="{{ $normalAttrs->count() + $performAttrs->count() + 13 }}">
                            <h3>{{trans('project::me.No result')}}</h3>
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

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
@include('project::me.template.script')
<script>
    newMeUrl = "{{ route('me::view.member.index') }}";
</script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/me_script.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="{{ asset('lib/fixed-table/tableHeadFixer.js') }}"></script>
<script>
    (function ($) {
        //date picker filter month
        $('.month-picker').datepicker({
            format: 'yyyy-mm',
            viewMode: 'months',
            minViewMode: 'months',
            autoclose: true,
            clearBtn: true,
        }).on('changeDate', function (e) {
            //check new version
            var dMonth = e.date.getMonth() + 1;
            var month = e.date.getFullYear() + '-' + (dMonth < 10 ? '0' + dMonth : dMonth);
            var currProj = $('#filter_projects').val();
            if (checkNewVersion(month, currProj)) {
                return false;
            }

            $('.btn-search-filter').click();
        });

        var filterMonth = $('.month-picker').val();
        if (filterMonth) {
            checkNewVersion(filterMonth, $('#filter_projects').val());
        }

        var meTable = $('#_me_table');
        if (meTable.length > 0) {
            var fixedCols = $('.fixed-table thead tr:first .fixed-col').length;
            $(".fixed-table").tableHeadFixer({"left" : fixedCols});
        }
    })(jQuery);
</script>
@endsection

