<?php
use Rikkei\Core\View\Form;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Task;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\View\CoreUrl;
use Carbon\Carbon;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Project\View\View;

$tableEmployee = Employee::getTableName();
$tableProject = Project::getTableName();
$tableTask = Task::getTableName();
$priorityFilter = Form::getFilterData("{$tableTask}.priority", null);
$statusFilter = Form::getFilterData('exception', "{$tableTask}.status");
$taskPriority = Task::priorityLabel();
$permission = View::checkPermissionEditWorkorder($project);
$permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditQA'] || $permission['permissionEditPqa'];
?>
<div class="box-body">
    <div class="filter-input-grid">
        <div class="col-sm-12">
            <div class="form-group row col-sm-4">
                <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Title') }}</label>
                <div class="col-sm-9">
                    <input type="text" name="filter[{{ $tableTask }}.title]" value="{{ Form::getFilterData($tableTask.'.title') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                </div>
            </div>
            <div class="form-group row col-sm-4">
                <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Priority') }}</label>
                <div class="col-sm-9 filter-multi-select">
                    <select class="form-control select-grid filter-grid select-search" name="filter[{{ $tableTask }}.priority]">
                        <option value="">&nbsp;</option>
                        @foreach($priority as $key => $value)
                            <option value="{{ $key }}" {{ $key == $priorityFilter ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row col-sm-4">
                <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Owner') }}</label>
                <div class="col-sm-9">
                    {{-- <input type="text" name="filter[tblEmpAssign.email]" value="{{ Form::getFilterData('tblEmpAssign.email') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" /> --}}
                    @php
                        $ownerFilter = Form::getFilterData("tblEmpAssign.email", null);
                    @endphp
                    <select name="filter[tblEmpAssign.email]" id="flt_employee_owner" class="js-example-basic-single form-control select-grid filter-grid select-search">
                        <option value="">&nbsp;</option>
                        @foreach($owners as $option)
                            <option value="{{ $option->email }}" {{ $option->email == $ownerFilter ? 'selected' : '' }}>{{ $option->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row col-sm-4">
                <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Status') }}</label>
                <div class="col-sm-9 filter-multi-select">
                    <select class="form-control select-grid filter-grid select-search" name="filter[number][{{ $tableTask }}.status]">
                        <option value="">&nbsp;</option>
                        @foreach($ncStatusAll as $key => $value)
                            <option value="{{ $key }}" {{ Form::getFilterData('number', $tableTask.'.status') == $key ? 'selected' : '' }}>{{ $value }}</option>
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

    <div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_NC]}}" id="table-nc">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
            <tr>
                <th>{{ trans('project::view.No.') }}</th>
                <th>{{ trans('project::view.Title') }}</th>
                <th>{{ trans('project::view.Create date') }}</th>
                <th>{{ trans('project::view.Due date') }}</th>
                <th>{{ trans('project::view.Priority') }}</th>
                <th>{{ trans('project::view.Owner') }}</th>
                <th>{{ trans('project::view.Status') }}</th>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @if (count($listNc))
                @foreach($listNc as $key => $item)
                <tr data-id="{{ $item->id }}">
                    <td>{{ $key+1 }}</td>
                    <td>
                        {{ $item->title }}
                    </td>
                    <td>
                        {{ Carbon::parse($item->created_at)->format('Y-m-d') }}
                    </td>
                    <td>
                        @if ($item->duedate)
                            {{ Carbon::parse($item->duedate)->format('Y-m-d') }}
                        @endif
                    </td>
                    <td>
                        @if (in_array($item->priority, array_keys($taskPriority)))
                            {{ $taskPriority[$item->priority] }}
                        @endif
                    </td>
                    <td>{{ $item->assign_email }}</td>
                    <td>{{ in_array($item->status, array_keys($ncStatusAll)) ? $ncStatusAll[$item->status] : '' }}</td>
                    @if(isset($permissionEdit) && $permissionEdit && !$project->isClosed())
                        <td style="text-align: right;">
                            <a class="btn-edit" href="{{ route('project::nc.detail', ['id'=>$item->id]) }}">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button class="btn-delete btn-delete-nc" data-id="{{ $item->id }}">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </td>
                    @else
                        <td>
                            <span>
                                <i class="fa fa-eye width-38 btn-edit edit-nc edit-nc-{{$item->id}}" data-view="1" data-id="{{$item->id}}"><i class="fa fa-spin fa-refresh hidden "></i></i>
                            </span>
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
            <tr class="tr-add-nc">
                <td colspan="9" class="slove-nc">
                    <button href="#" class="btn-add add-nc" data-project-id="{{$project->id}}"><i class="fa fa-plus"></i></button>
                </td>
            </tr>
        @endif
    </div>
</div>
<div class="box-body">
    @include('team::include.pager', ['domainTrans' => 'project', 'dataModel' => $listNc])
</div>

<div class="modal fade modal-danger" id="modal-delete-confirm-nc" tabindex="-1" role="dialog">
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
            $('.js-example-basic-single').select2();
        });
    </script>
@endsection
