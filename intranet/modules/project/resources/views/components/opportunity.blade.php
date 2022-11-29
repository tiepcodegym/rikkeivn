<?php
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\Task;
use Carbon\Carbon;
use Rikkei\Project\View\View;

$tableTask = Task::getTableName();
$oopSourceFilter = Form::getFilterData('oop', "{$tableTask}.opportunity_source");
$priorityFilter = Form::getFilterData('oop', "{$tableTask}.priority");
$permission = View::checkPermissionEditWorkorder($project);
$permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditQA'] || $permission['permissionEditPqa'];
?>

<div class="box-body">
    <div class="filter-input-grid">
        <div class="col-sm-12">
            <div class="form-group row col-sm-4">
                <label for="" class="col-sm-3 col-form-label">Source</label>
                <div class="col-sm-9 filter-multi-select">
                    <select class="form-control select-grid filter-grid select-search js-oop-source" name="filter[oop][{{ $tableTask }}.opportunity_source]">
                        <option value="">&nbsp;</option>
                        @foreach(Task::getAllOpportunitySource() as $key => $value)
                            <option value="{{ $key }}" {{ $key == $oopSourceFilter ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row col-sm-4">
                <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Priority') }}</label>
                <div class="col-sm-9 filter-multi-select">
                    <select class="form-control select-grid filter-grid select-search" name="filter[oop][{{ $tableTask }}.priority]">
                        <option value="">&nbsp;</option>
                        @foreach($priorityV2 as $key => $value)
                            <option value="{{ $key }}" {{ $key == $priorityFilter ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row col-sm-4">
                <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Status') }}</label>
                <div class="col-sm-9 filter-multi-select">
                    <select class="form-control select-grid filter-grid select-search" name="filter[oop-number][{{ $tableTask }}.status]">
                        <option value="">&nbsp;</option>
                        @foreach($statusOpp as $key => $value)
                            <option value="{{ $key }}" {{ Form::getFilterData('oop-number', $tableTask.'.status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="box-body filter-action">
        <?php
        if (!isset($domainTrans) || !$domainTrans) {
            $domainTrans = 'team';
        }
        ?>
        <button class="btn btn-primary btn-reset-filter">
            <span>{{ trans($domainTrans . '::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
        </button>
        <button class="btn btn-primary btn-search-filter">
            <span>{{ trans($domainTrans . '::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
        </button>
    </div>

    <div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_OPPORTUNITY]}}" id="table-opportunity">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Opportunity source</th>
                <th>Priority</th>
                <th>Plan end date</th>
                <th>Actual end date</th>
                <th>Create date</th>
                <th>Update date</th>
                <th>Status</th>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @if (count($listOpportunities))
                @foreach($listOpportunities as $key => $item)
                <tr data-id="{{ $item->id }}">
                    <td>{{ $item->id }}</td>
                    <td>
                        {{ $item->getOpportunitySource() }}
                    </td>
                    <td>
                        {{ $item->getPriority() }}
                    </td>
                    <td>
                        {{ Carbon::parse($item->duedate)->format('Y-m-d') }}
                    </td>
                    <td>
                        {{ Carbon::parse($item->actual_date)->format('Y-m-d') }}
                    </td>
                    <td>
                        {{ Carbon::parse($item->created_at)->format('Y-m-d') }}
                    </td>
                    <td>
                        {{ Carbon::parse($item->updated_at)->format('Y-m-d') }}
                    </td>
                    <td>{{ $item->getStatusOpportunity() }}</td>
                    @if(isset($permissionEdit) && $permissionEdit && !$project->isClosed())
                        <td style="text-align: right;">
                            <a class="btn-edit" href="{{ route('project::report.opportunity.detail', ['id'=>$item->id]) }}">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button class="btn-delete btn-delete-opportunity" data-id="{{ $item->id }}">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </td>
                    @else
                        <td>
                            <button style="border: none;" class="edit-opportunity" data-view="1" data-project-id="{{$project->id}}" data-id="{{$item->id}}">
                                <i class="fa fa-eye width-38 btn-edit"><i class="fa fa-spin fa-refresh hidden "></i></i>
                            </button>
                        </td>
                    @endif
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="13" class="text-center">
                        <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
        @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
            <tr class="tr-add-opportunity">
                <td colspan="9" class="slove-opportunity">
                    <button href="#" class="btn-add add-opportunity" data-project-id="{{$project->id}}"><i class="fa fa-plus"></i></button>
                </td>
            </tr>
        @endif
    </div>
</div>
<div class="box-body">
    @include('team::include.pager', ['domainTrans' => 'project', 'dataModel' => $listOpportunities])
</div>

<div class="modal fade modal-danger" id="modal-delete-confirm-opportunity" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ Lang::get('core::view.Are you sure delete item(s)?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-submit">{{ Lang::get('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

@section('script')
    @parent
    <script type="text/javascript">
        $(document).ready(function () {
            $('.js-oop-source').select2();
        });
    </script>
@endsection