
<?php
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config;
use Rikkei\Resource\View\FreeEffort;
use Rikkei\Core\View\CoreUrl;
use Carbon\Carbon;

$empTbl = 'employees';
$compares = FreeEffort::compareFilters();
?>
@extends('layouts.default')

@section('title', trans('resource::view.Employees available'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css">
<link href="{{ CoreUrl::asset('resource/css/general.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('content')

<div class="box box-info search-box">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('resource::view.Search conditions') }}</h3>
    </div>
    
    <div class="box-body">
        @include('resource::available.search')
        <div class="row">
            <div class="col-sm-4">
                <button type="button" id="btn_update_data" class="btn btn-default"
                        data-toggle="tooltip" title="{{ trans('resource::view.Update realtime employee data') }}"
                        data-url="{{ route('resource::available.update_data') }}">
                    {{ trans('resource::view.Update data') }} <i class="fa fa-spin fa-refresh hidden"></i>
                </button>
            </div>
            <div class="col-sm-8 text-right">
                @if ($permissExport)
                    {!! Form::open(['method' => 'post', 'route' => 'resource::available.export', 'class' => 'form-inline', 'id' => 'form_export']) !!}
                    <input type="hidden" name="employee_ids">
                    <button class="btn btn-success" type="submit" data-toggle="tooltip" title="{{ trans('resource::view.export_description') }}"
                            id="btn_export_result">
                        <i class="fa fa-download"></i> {{ trans('resource::view.Export') }}
                    </button>
                    {!! Form::close() !!}
                    <button type="button" class="btn btn-primary" id="btn_reset_checkbox">{{ trans('resource::view.Reset checkbox') }}</button>
                @endif
                @include('team::include.filter', ['domainTrans' => 'resource'])
            </div>
        </div>
    </div>
</div>

<div class="box box-info">

    <div class="pdh-10">
        <div class="table-responsive">
            <table id="table_employees" class="table dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        @if ($permissExport)
                        <th><input type="checkbox" class="check-all"></th>
                        @endif
                        <th>{{ trans('core::view.NO.') }}</th>
                        <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('resource::view.Employee name') }}</th>
                        <th width="100">{{ trans('resource::view.Foreign language') }}</th>
                        <th class="sorting {{ Config::getDirClass('team_names') }} col-name" data-order="team_names" data-dir="{{ Config::getDirOrder('team_names') }}">{{ trans('resource::view.Team') }}</th>
                        <th class="sorting {{ Config::getDirClass('exper_year') }} col-name" data-order="exper_year" data-dir="{{ Config::getDirOrder('exper_year') }}">
                            {{ trans('resource::view.Experience') }} <span>({{ trans('resource::view.year') }})</span>
                        </th>
                        <th>{{ trans('resource::view.Programing language') }}</th>
                        <th>{{ trans('resource::view.Project (in time filter)') }}</th>
                        <th>{{ trans('resource::view.Note') }}</th>
                        <th>{{ trans('resource::view.Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!$collectionModel->isEmpty())
                        <?php
                        $perPage = $collectionModel->perPage();
                        $currPage = $collectionModel->currentPage();
                        ?>
                        @foreach ($collectionModel as $order => $item)
                        <!--check project joined-->
                        <?php
                        $filterProjs = collect();
                        if ($item->str_pjms) {
                            $filterItems = FreeEffort::filterDateAttrs($item, $dataSearch);
                            $filterProjs = $filterItems['projects'];
                        }
                        ?>
                        <tr data-employee="{{ $item->id }}" class="{{ $filterProjs->isEmpty() ? 'bg-warning' : 'bg-success' }}">
                            @if ($permissExport)
                            <td><input type="checkbox" class="check-item" value="{{ $item->id }}"></td>
                            @endif
                            <td>{{ $order + 1 + ($currPage - 1) * $perPage }}</td>
                            <td class="emp-col">
                                <a target="_blank" href="{{ route('team::member.profile.index', ['employeeId' => $item->id, 'type' => 'cv']) }}">{{ $item->name }}</a><br />
                                <span>({{ CoreView::getNickName($item->email) }})</span>
                            </td>
                            <td>{{ $item->lang_level }}</td>
                            <td>{{ $item->team_names }}</td>
                            <td>{{ $item->exper_year }}</td>
                            <td>{!! FreeEffort::sepSkillLangs($item->str_langs) !!}</td>
                            <td>
                                @if (!$filterProjs->isEmpty())
                                <ul class="td-lists padding-left-15">
                                    @foreach($filterProjs as $pIdx => $pjm)
                                        @if ($pIdx < 5)
                                        <li class="white-space-nowrap">
                                            <strong title="{{ $pjm['name'] }}">{{ $pjm['name'] }}</strong>: 
                                            <span>{{ $pjm['start_at'] }} <i class="fa fa-long-arrow-right"></i> <span class="text-yellow">{{ $pjm['end_at'] }}</span></span> 
                                            <span class="text-blue">({{ $pjm['effort'] }}%)</span>
                                        </li>
                                        @else
                                        <li>... <a class="full-projs-btn" data-toggle="modal" href="#modal_projects_detail">{{ trans('resource::view.View more') }}</a></li>
                                        @endif
                                    @endforeach
                                </ul>
                                @endif
                            </td>
                            <td class="note-col">
                                <?php
                                $currHasNote = false;
                                ?>
                                @if (isset($arrayNotes[$item->id]))
                                    @foreach ($arrayNotes[$item->id] as $note)
                                        @include('resource::available.note-item', ['noteItem' => $note])
                                        <?php
                                        if (!$currHasNote && $note->email == $currentUser->email) {
                                            $currHasNote = true;
                                        }
                                        ?>
                                    @endforeach
                                @endif
                                @if (!$currHasNote)
                                    @include('resource::available.note-item')
                                @endif
                            </td>
                            <td>
                                @if (!isset($arrayTaskIds[$item->id]))
                                <button type="button" class="btn btn-primary btn-add-task">
                                    <i class="fa fa-plus"></i> {{ trans('resource::view.Add task') }}
                                </button>
                                @else
                                <button type="button" class="btn btn-primary btn-add-task" data-task="{{ $arrayTaskIds[$item->id] }}"
                                        title="{{ route('project::task.general.create.ajax', ['id' => $arrayTaskIds[$item->id]]) }}">
                                    <i class="fa fa-edit"></i> {{ trans('resource::view.Edit task') }}
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="{{ $permissExport ? 10 : 9 }}" class="text-center">
                            <h4>
                                @if ($dataSearch['has_search'])
                                    {{ trans('resource::message.Not found item') }}
                                @else
                                    <span class="text-green">{{ trans('resource::message.Please click search button') }}</span>
                                @endif
                            </h4>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="box-body">
        @include('team::include.pager', ['domainTrans' => 'resource'])
    </div>
</div>

@include('resource::available.projects-detail-modal')
@include('resource::available.add-task-modal')

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
    var saveNoteUrl = '{{ route("resource::available.save_note") }}';
    var addTaskUrl = '{{ route("project::task.general.create.ajax") }}';
    var saveTaskUrl = '{{ route("project::task.general.save") }}';
    var textNewTaskTitle = '{!! trans("resource::view.Employees available") !!}';
    var currEmpId = '{{ $currentUser->id }}';
    var currEmpAcc = '{{ CoreView::getNickName($currentUser->email)  }}';
    var textEditTask = '{{ trans("resource::view.Edit task") }}';
    var textAddTask = '{{ trans("resource::view.Add task") }}';
    var textErrorMaxLength = '<?php echo trans("resource::message.The field may not be greater characters", ["field" => "note", "max" => 500]) ?>';
</script>
<script src="{{ CoreUrl::asset('resource/js/available/script.js') }}"></script>
@stop
