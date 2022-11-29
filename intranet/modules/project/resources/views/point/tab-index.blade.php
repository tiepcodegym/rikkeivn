@extends('layouts.default')

@section('title')
    <?php
    if (!$viewBaseline) {
        echo trans('project::view.Project Dashboard');
    } else {
        echo trans('project::view.Project Dashboard Baseline');
    } ?>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css"
          rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}"/>
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <style>
        .multi-select-style .multiselect-container {
            width: 100%;
            height: 250px;
            max-height: 300px;
            overflow-y: auto;
        }
        .btn-group.open .dropdown-toggle {
            width: 100%;
        }
        .btn-group{
            width: 100% !important
        }
        .btn-group .multiselect {
            width: 100% !important;
        }
    </style>
@endsection

@section('content')
    <?php
    use Rikkei\Project\Model\Project;
    use Rikkei\Project\View\View as ViewProject;
    use Rikkei\Core\View\View;
    use Rikkei\Team\Model\Employee;
    use Rikkei\Project\Model\ProjectPoint;
    use Rikkei\Team\View\Config;
    use Rikkei\Core\View\Form;
    use Rikkei\Project\Model\ProjPointBaseline;
    use Rikkei\Team\View\TeamList;
    use Rikkei\Project\Model\Task;
    use Rikkei\Core\View\OptionCore;
    use Rikkei\Project\View\GeneralProject;
    use Carbon\Carbon;
    use Rikkei\Team\Model\Team;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Sales\Model\Company;
    use Rikkei\Team\View\Permission;
    use Rikkei\Project\Model\ProjectWatch;

    $teamPathV2 = Team::getTeamPath();

    $tableProject = Project::getTableName();
    $urlSubmitFilter = GeneralProject::getUrlFilterDb();
    if ($isWatch) $urlSubmitFilter = trim($urlSubmitFilter, '/') . '?' . $isWatch . '/';
    $teamPath = Team::getTeamPathTree(false);
    $teamChargeFilter = Form::getFilterData('except', 'team_charge_id', $urlSubmitFilter);
    $teamFilter = Form::getFilterData('exception', 'team_id', $urlSubmitFilter);
    $stateFilter = Form::getFilterData('exception', "{$tableProject}.state", $urlSubmitFilter);
    $isImportantFilter = Form::getFilterData('number', "{$tableProject}.is_important", $urlSubmitFilter);
    $tableEmployee = Employee::getTableName();
    $tableCompany = Company::getTableName();
    $optionYesNo = OptionCore::yesNo(true, false);
    $viewDashboardasBaseline = false;
    $nowTime = Carbon::now();
    $listMyTracking = ProjectWatch::listMyTracking();
    if (Permission::getInstance()->isAllow('project::project.export')) {
        $buttonAction['export'] = [
            'label' => 'export employees in project',
            'class' => 'export',
            'disabled' => true,
            'url' => URL::route('project::project.export'),
            'icon_refresh' => true
        ];
    }

    if ($isAllowRaise) {
        $buttonAction['raise'] = [
            'label' => 'Raise',
            'class' => 'raise-submit',
            'disabled' => true,
            'url' => URL::route('project::dashboard.raise'),
            'icon_refresh' => true
        ];
    }

    if (Permission::getInstance()->isAllow('project::project.export.project')) {
        $buttonAction['export-project'] = [
            'label' => 'export project',
            'class' => 'export-project',
            'disabled' => false,
            'url' => URL::route('project::project.export.project'),
            'icon_refresh' => true,
            'tooltip' => trans('project::view.Export projects that are on-going up to now')
        ];
    }
    $weekSlug = $viewBaseline && isset($weekList['list'][(int)$weekList['choose']]['value']) ? $weekList['list'][(int)$weekList['choose']]['value'] : null;
    if ($viewBaseline && $isAllowRaise) {
        $buttonAction['raise']['url'] = URL::route('project::dashboard.raise.baseline');
    }
    ?>
    <div class="row">
        <div class="nav-tabs-custom nav-tabs-rikkei test-tabs">
            <div class="box box-info filter-wrapper" data-url="{{ $urlSubmitFilter }}">
                <div class="box-body filter-mobile-left">
                    @include('project::point.include.baseline_select')
                    @include('team::include.filter', ['domainTrans' => 'project', 'buttons' => $buttonAction])
                </div>
                <form action="{{ route('project::project.export') }}" method="post" id="export-member"
                      class="no-validate">
                    {!! csrf_field() !!}
                    {!! $viewBaseline ? "<input type='hidden' name='baseline' value='baseline'> " : '' !!}
                </form>
                <form action="{{ route('project::project.export.project') }}" method="post" id="export-project"
                      class="no-validate">
                    {!! csrf_field() !!}
                    {!! $viewBaseline ? "<input type='hidden' name='baseline' value='baseline'> " : '' !!}
                </form>
                <div class="tab-content">
                    <ul class="nav nav-tabs">
                        <li id="project" <?php if (!$isWatch) echo ' class="active"'; ?>>
                            <a href="javascript:void(0)">{{ trans('project::view.All') }}</a>
                        </li>
                        <li id="watch" <?php if ($isWatch) echo ' class="active"'; ?>>
                            <a href="javascript:void(0)">{{ trans('project::view.Watch list') }}</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="home" class="tab-pane fade in active">
                            @include('project::point.index')
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="task-list-popup-wraper">
            <div class="modal fade task-list-modal" role="dialog" data-id="0" data-type="0">
                <div class="modal-dialog" role="document">
                    <div class="modal-content grid-data-query"
                         data-url="{{ URL::route('project::task.list.ajax', ['id' => 0]) }}">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                            <h3 class="task-list-title">
                                <span class="task-list-title-text">{{ trans('project::view.Task list') }}</span>
                                <span class="task-list-title-text hidden"
                                      data-type="{{ Task::TYPE_ISSUE_COST }}">{{ trans('project::view.Task cost list') }}</span>
                                <span class="task-list-title-text hidden"
                                      data-type="{{ Task::TYPE_ISSUE_QUA }}">{{ trans('project::view.Task quality list') }}</span>
                                <span class="task-list-title-text hidden"
                                      data-type="{{ Task::TYPE_ISSUE_TL }}">{{ trans('project::view.Task timeliness list') }}</span>
                                <span class="task-list-title-text hidden"
                                      data-type="{{ Task::TYPE_ISSUE_PROC }}">{{ trans('project::view.Task process list') }}</span>
                                <span class="task-list-title-text hidden"
                                      data-type="{{ Task::TYPE_ISSUE_CSS }}">{{ trans('project::view.Task css list') }}</span>
                                &nbsp; <i class="fa fa-spin fa-refresh hidden"></i>
                                <a class="btn-add btn-add-task" target="_blank" href="#"
                                   data-url="{{ URL::route('project::task.add', ['id' => 0]) }}">
                                    <i class="fa fa-plus"></i>
                                </a>
                            </h3>
                        </div>
                        <div class="modal-body">
                            <div class="grid-data-query-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal-raise-note">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="#"
                          class="form-horizontal form-submit-ajax" autocomplete="off"
                          data-callback-error="workoderErrorDate">
                        {!! csrf_field() !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span></button>
                            <h4 class="modal-title">{{trans('project::view.Note')}}</h4>
                        </div>
                        <div class="modal-body">
                            <textarea rows="5" name="raise-note" class="form-control" required></textarea>
                            <label id="title-error" class="error hidden"
                                   for="title">{{ trans('project::view.This field is required.') }}</label>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left"
                                    data-dismiss="modal">{{ trans('project::view.Close') }}</button>
                            <button type="button" class="btn-add submit-raise-note">
                                {{ trans('project::view.Submit') }}
                                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            var viewBaseline = '{{ $viewBaseline }}',
                weekSlug = '{{ $weekSlug }}';

            $('#project').click(function () {
                if (!viewBaseline) {
                    window.location.href = "{{ route('project::dashboard') }}";
                }
                if (weekSlug === '') {
                    window.location.href = "{{ route('project::dashboard', ['bl' => 1]) }}";
                } else {
                    window.location.href = "{{ route('project::point.baseline', ['slug' => $weekSlug]) }}";
                }
            });
            $('#watch').click(function () {
                if (!viewBaseline) {
                    window.location.href = "{{ route('project::dashboard.isWatch', 'watch-list') }}";
                }
                window.location.href = "{{ route('project::dashboard.isWatch', ['type' => 'watch-list', 'bl' => 1]) }}";
            });
        });
    </script>
    <script>
        var globalPassModule = {
            urlTaskTitle: '{{ URL::route('project::dashboard.notes') }}',
            weekSlug: "{{ $viewBaseline && isset($weekList['list'][(int) $weekList['choose']]['value']) ? $weekList['list'][(int) $weekList['choose']]['value'] : null }}",
            noBaseline: "{{ !$viewBaseline }}",
            teamPath: JSON.parse('{!! json_encode($teamPath) !!}'),
            teamChargeSelected: JSON.parse('{!! json_encode($teamChargeFilter) !!}'),
            teamSelected: JSON.parse('{!! json_encode($teamFilter) !!}')
        };
        var globalText = {
            noComment: '{{ trans("project::view.No comment") }}',
            loadMore: '{{ trans("project::view.Load more") }}'
        };
        var globalMsg = {
            raiseTwoProject: '{{ trans("project::view.Warning note raise project") }}'
        }

    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    <script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            selectSearchReload();
            $('#type, #state').multiselect({
                numberDisplayed: 2,
                nonSelectedText: '----',
                allSelectedText: '{{ trans('project::view.All') }}',
                onDropdownHide: function(event) {
                    RKfuncion.filterGrid.filterRequest(this.$select);
                }
            });
        });

        $('#checkAll').click(function () {
            if ($(this).is(':checked')) {
                $('.export').removeAttr('disabled');
            } else {
                $('.export').attr('disabled', 'disabled');
            }
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
        $('.checkbox-item').click(function () {
            var flag = true;
            $('.checkbox-item').map(function (i, e) {
                if (!$(e).is(':checked')) {
                    flag = false;
                }
            });
            if (flag === false) {
                $('#checkAll').prop('checked', false);
            } else {
                $('#checkAll').prop('checked', true);
            }
        });
        var ajaxChangeStatus = '{{ route('project::ajaxAddOrRemoveWatcher') }}',
            isWatch = '{{ $isWatch }}';
        $(function () {
            $('.btn-change-status').change(function () {
                var id = $(this).val();
                $.ajax({
                    url: ajaxChangeStatus,
                    type: 'POST',
                    data: {
                        project_id: id,
                        _token: siteConfigGlobal.token
                    },
                    success: function (data) {
                        if (data.status === 'enabled') {
                            $(this).prop('checked', true).change()
                        } else {
                            $(this).prop('checked', false).change()
                        }
                        if (isWatch) {
                            location.reload();
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

    <script>
        var teamPathV2 = {!! json_encode($teamPathV2) !!};
        $(function () {
            selectSearchReload();
            $('.select-multi').multiselect({
                numberDisplayed: 1,
                nonSelectedText: '--------------',
                allSelectedText: '{{ trans('project::view.All') }}',
                onDropdownHide: function(event) {
                    RKfuncion.filterGrid.filterRequest(this.$select);
                }
            });
            $('.js-select-multi-role').multiselect({
                numberDisplayed: 1,
                nonSelectedText: '--------------',
                allSelectedText: '{{ trans('project::view.All') }}',
                enableCaseInsensitiveFiltering: true,
                onDropdownHide: function(event) {
                    RKfuncion.filterGrid.filterRequest(this.$select);
                }
            });
            // Limit the string length to column roles.
            $('.role-special').shortedContent({showChars: 150});
        });
    
        $(document).on('mouseup', 'li.checkbox-item', function () {
            var domInput = $(this).find('input');
            var id = domInput.val();
            var isChecked = !domInput.is(':checked');
            if (teamPathV2[id] && typeof teamPathV2[id].child !== "undefined") {
                var teamChild = teamPathV2[id].child;
                $('li.checkbox-item input').map((i, el) => {
                    if (teamChild.indexOf(parseInt($(el).val())) !== -1 && $(el).is(':checked') === !isChecked) {
                        $(el).click();
                    }
                });
            }
            setTimeout(() => {
                changeLabelSelected();
            }, 0)
        });
        $(document).ready(function () {
            changeLabelSelected();
        });
    
        function changeLabelSelected() {
            var checkedValue = $(".list-team-select-box option:selected");
            var title = '';
            if (checkedValue.length === 0) {
                $(".list-team-select-box .multiselect-selected-text").text('--------------');
            }
            if (checkedValue.length === 1) {
                $(".list-team-select-box .multiselect-selected-text").text($.trim(checkedValue.text()));
            }
            for (let i = 0; i < checkedValue.length; i++) {
                title += $.trim(checkedValue[i].label) + ', ';
            }
            $('.list-team-select-box button').prop('title', title.slice(0, -2))
        }
    </script>
@endsection
