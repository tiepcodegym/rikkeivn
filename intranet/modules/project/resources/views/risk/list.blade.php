@extends('layouts.default')
@section('title')
    {{ trans('project::view.Risk list') }}
@endsection
@section('content')
<?php
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Risk;
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\CoreUrl;

$teamsOptionAll = TeamList::toOption(null, true, false);
$urlFilter = trim(URL::route('project::report.risk'), '/') . '/';
$teamPath = Team::getTeamPath();
?>
<div class="row list-css-page">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row form-horizontal filter-input-grid">
                    <div class="col-sm-12">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-3 control-label">{{ trans('project::view.Project') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control filter-grid" name="filter[projs.name]" value="{{ Form::getFilterData('projs.name') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-3 control-label">{{ trans('project::view.Owner') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control filter-grid" name="filter[employees.email]" value="{{ Form::getFilterData('employees.email') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-3 control-label">{{ trans('project::view.Status') }}</label>
                                <div class="col-sm-9">
                                    <select class="form-control select-grid filter-grid" name="filter[proj_op_ricks.status]">
                                        <option></option>
                                        @foreach (Risk::statusLabel() as $statusKey => $statusText)
                                        <option value="{{ $statusKey }}" {{ !is_null(Form::getFilterData('proj_op_ricks.status')) && Form::getFilterData('proj_op_ricks.status') == $statusKey ? 'selected' : '' }}>{{ $statusText }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ trans('project::view.Division') }}</label>
                                <div class="col-sm-9">
                                    <div class="list-team-select-box">
                                        {{-- show team available --}}
<!--                                        --><?php //dd($teamIdCurrent); ?>
                                        @if ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                                            <div class="input-box filter-multi-select multi-select-style btn-select-team">
                                                <select name="filter[except][teams.id][]" id="select-team-member" multiple
                                                        class="form-control filter-grid multi-select-bst select-multi"
                                                        autocomplete="off">
                                                    {{-- show team available --}}
                                                    @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                                        @foreach($teamsOptionAll as $option)
                                                                <option value="{{ $option['value'] }}" class="checkbox-item"
                                                                        {{ in_array($option['value'], array_map("trim", explode(",", $teamIdCurrent))) ? 'selected' : '' }}>{{ $option['label'] }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        @endif
                                        {{-- end show team available --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-3 control-label">{{ trans('project::view.Priority') }}</label>
                                <div class="col-sm-9">
                                    <select class="form-control select-grid filter-grid" name="filter[proj_op_ricks.level_important]">
                                        <option></option>
                                        @foreach (Risk::getListLevelRisk() as $keyLevel => $valueLevel)
                                            <option value="{{ $valueLevel }}" {{ Form::getFilterData('proj_op_ricks.level_important') == $valueLevel ? 'selected' : '' }}>{{ $keyLevel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 row">
                        <div class="col-md-4">
                            <label for="deadline_from" class="col-md-3 control-label">{{ trans('project::view.Deadline') }}</label>
                            <div class="col-md-9">
                                <div class="form-inline col-md-12">
                                    <label for="deadline_from" class="col-md-3 control-label">{{ trans('project::view.From') }}</label>
                                    <input class="date-picker col-md-10 form-control filter-grid" id="deadline_from" name="filter[except][proj_op_ricks.due_date]" value="{{ Form::getFilterData('except', 'proj_op_ricks.due_date') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                                <div class="form-inline col-sm-12 margin-top-20">
                                    <label for="deadline_from" class="col-sm-3 control-label">{{ trans('project::view.To') }}</label>
                                    <input class="date-picker form-control filter-grid" id="deadline_to" name="filter[except][proj_op_ricks.finish_date]" value="{{ Form::getFilterData('except', 'proj_op_ricks.finish_date') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="created_from" class="col-sm-3 control-label">{{ trans('project::view.Created') }}</label>
                            <div class="col-md-9">
                                <div class="form-inline col-md-12">
                                    <label for="created_from" class="col-md-3 control-label">{{ trans('project::view.From') }}</label>
                                    <input class="date-picker col-md-10 form-control filter-grid" id="created_from" name="filter[except][proj_op_ricks.created_at]" value="{{ Form::getFilterData('except', 'proj_op_ricks.created_at') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                                <div class="form-inline col-sm-12 margin-top-20">
                                    <label for="created_to" class="col-sm-3 control-label">{{ trans('project::view.To') }}</label>
                                    <input class="date-picker form-control filter-grid" id="created_to" name="filter[except][proj_op_ricks.test_date]" value="{{ Form::getFilterData('except', 'proj_op_ricks.test_date') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="created_from" class="col-sm-3 control-label">{{ trans('project::view.Updated') }}</label>
                            <div class="col-md-9">
                                <div class="form-inline col-md-12">
                                    <label for="updated_form" class="col-md-3 control-label">{{ trans('project::view.From') }}</label>
                                    <input class="date-picker col-md-10 form-control filter-grid" id="updated_form" name="filter[except][proj_op_ricks.updated_at]" value="{{ Form::getFilterData('except', 'proj_op_ricks.updated_at') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                                <div class="form-inline col-sm-12 margin-top-20">
                                    <label for="updated_to" class="col-sm-3 control-label">{{ trans('project::view.To') }}</label>
                                    <input class="date-picker form-control filter-grid" id="updated_to" name="filter[except][proj_op_ricks.deleted_at]" value="{{ Form::getFilterData('except', 'proj_op_ricks.deleted_at') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="box-body">
                            <div class="col-md-7"></div>
                            <button class="btn btn-primary" style="display: inline-block;float: right;margin-right: 5px;">
                                <a style="color: #fff" href="{{asset('help/risk/riskhelp.html')}}" target="_blank">{{ trans('project::view.Help') }}</a>
                            </button>
                            <button class="btn btn-primary btn-reset-filter col-sm-1" style="display: inline-block;float: right;margin-right: 5px">
                                <span>{{ trans('team::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                            </button>
                            <button class="btn btn-primary btn-search-filter col-sm-1" style="display: inline-block;float: right;margin-right: 5px">
                                <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                            </button>
                            <form action="{{ route('project::project.export.risk') }}" method="post">
                                {!! csrf_field() !!}
                                <button class="btn btn-success col-sm-1" style="display: inline-block;float: right;margin-right: 20px;">
                                    {{ trans('project::view.Export') }}
                                </button>
                            </form>
                            <button href="#" style="margin-left: 30px;" class="btn add-risk btn-success col-sm-1"><i class="fa fa-plus"></i> {{ trans('project::view.Add risk') }}</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
                                <thead>
                                    <tr role="row">
                                        <th style="width: 10px;">{{trans('project::view.ID risk')}}</th>
                                        <th style="width: 150px;">{{trans('project::view.Risk Type')}}</th>
                                        <th style="width: 300px;">{{trans('project::view.Title')}}</th>
                                        <th style="width: 80px;">{{trans('project::view.Division')}}</th>
                                        <th style="width: 300px;">{{trans('project::view.Project')}}</th>
                                        <th style="width: 80px;">{{trans('project::view.Status')}}</th>
                                        <th >{{trans('project::view.Priority')}}</th>
                                        <th style="width: 40px;">{{trans('project::view.Owner')}}</th>
                                        <th >{{trans('project::view.Due Date')}}</th>
                                        <th >{{trans('project::view.Create date')}}</th>
                                        <th >{{trans('project::view.Update date')}}</th>
                                   </tr>
                                </thead>
                                <tbody>
                                    @if(count($collectionModel) > 0)
                                    @foreach($collectionModel as $risk)
                                    <tr role="row" >
                                        <td>{{ $risk->id }}</td>
                                        <td>{{ empty(Risk::getTypeList()[$risk->type]) ? '' : Risk::getTypeList()[$risk->type] }}</td>
                                        <td>
                                            <a href="{{ route('project::report.risk.detail', ['id' => $risk->id]) }}">{!!nl2br(e($risk->content))!!}</a>
                                        </td>
                                        <td>{{ $risk->team_name }}</td>
                                        <td><a href="{{ route('project::project.edit', ['id' => $risk->proj_id]) }}">{{ $risk->proj_name }}</a></td>
                                        <td>
                                            {{ empty(Risk::statusLabel()[$risk->status]) ? Risk::statusLabel()[Risk::STATUS_OPEN] : Risk::statusLabel()[$risk->status] }}
                                        </td>
                                        <td>
                                            {{ Risk::getKeyLevelRisk($risk->level_important) }}
                                        </td>
                                        <td>
                                            @if ($risk->team_owner)
                                                {{ $risk->team_name }}
                                            @endif
                                            @if ($risk->owner)
                                                @if ($risk->team_owner)
                                                    {{ ' - ' }}
                                                @endif
                                               {{View::getNickName($risk->owner_email)}}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($risk->due_date)
                                                {{ $risk->due_date }}
                                            @endif
                                        </td>
                                        <td>{{$risk->created_at}}</td>
                                        <td>{{$risk->updated_at}}</td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr><td colspan="11" class="text-align-center"><h2>{{trans('sales::view.No result not found')}}</h2></td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>    
                        <div class="box-body">
                            @include('team::include.pager', ['domainTrans' => 'project'])
                        </div>
                    </div>
                </div>
                
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
@endsection
<!-- Styles -->
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('resource/css/resource.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css">
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
@endsection

<!-- Script -->
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
<script type="text/javascript" src="{{ asset('lib/js/bootstrap-dialog.min.js') }}"></script>
<script type="text/javascript">
    var teamPath = {!! json_encode($teamPath) !!};
    var token = '{!! csrf_token() !!}';
    var requiredText = 'Trường bắt buộc nhập';
    var urlAddRisk = '{{ route("project::wo.editRisk") }}';
    jQuery(document).ready(function () {

        $('input.date-picker').datepicker({
            format: 'yyyy-mm-dd'
        })

        $(document).on('click', '.add-risk', function () {
            var $curElem = $(this);
            $('.add-risk').prop('disabled', true);
            $curElem.find('i.fa-plus').removeClass('fa-plus').addClass('fa-refresh').addClass('fa-spin');
            $.ajax({
                url: urlAddRisk.trim(),
                type: 'get',
                data: {},
                dataType: 'text',
                success: function (data) {
                    BootstrapDialog.show({
                        cssClass: 'risk-dialog',
                        message: $('<div></div>').html(data),
                        closable: false,
                        buttons: [{
                            id: 'btn-close',
                            icon: 'fa fa-close',
                            label: 'Close',
                            cssClass: 'btn-primary',
                            autospin: false,
                            action: function(dialogRef){
                                dialogRef.close();
                            }
                        },{
                            id: 'btn-save',
                            icon: 'glyphicon glyphicon-check',
                            label: 'Save',
                            cssClass: 'btn-primary',
                            autospin: false,
                            action: function(dialogRef){
                                $('.form-riks-detail').submit();
                            }
                        }]
                    });
                },
                error: function () {
                    alert('ajax fail to fetch data');
                },
                complete: function () {
                    $curElem.find('i.fa-refresh').addClass('fa-plus').removeClass('fa-refresh').removeClass('fa-spin');
                    $('.add-risk').prop('disabled', false);
                }
            });
        });
    });

    $(document).on('mouseup', 'li.checkbox-item', function () {
        var domInput = $(this).find('input');
        var id = domInput.val();
        var isChecked = !domInput.is(':checked');
        if (teamPath[id] && typeof teamPath[id].child !== "undefined") {
            var teamChild = teamPath[id].child;
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
        selectSearchReload();
        changeLabelSelected();
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
