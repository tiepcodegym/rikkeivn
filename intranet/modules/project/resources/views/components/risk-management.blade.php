<?php 
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\Task;

$allNameTab = Task::getAllNameTabWorkorder();
$allRisks = $allRisks->sortByDesc('level_important')->values();
$permissionEdit = isset($permissionEditQA) && $permissionEditQA ? $permissionEditQA : $permissionEdit;
?>
@if(isset($detail))
@if(config('project.workorder_approved.risk'))
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_RISK]}}" id="table-risk">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="width-5-per align-center">{{trans('project::view.No')}}</th>
                <th style="width: 20px;">{{trans('project::view.ID risk')}}</th>
                <th >{{trans('project::view.Risk Type')}}</th>
                <th style="width: 80px;">{{trans('project::view.Division')}}</th>
                <th >{{trans('project::view.Summary')}}</th>
                <th >{{trans('project::view.Status')}}</th>
                <th >{{trans('project::view.Priority')}}</th>
                <th >{{trans('project::view.Owner')}}</th>
                <th >{{trans('project::view.Due Date')}}</th>
                <th >{{trans('project::view.Create date')}}</th>
                <th >{{trans('project::view.Update date')}}</th>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($allRisks as $key => $risk)
            <?php
                $hasChild = false;
                if($risk->status == Risk::STATUS_APPROVED) {
                    if (count($risk->projectRiskChild) > 0) {
                        $hasChild = true;
                    }
                }
            ?>
            <tr class="background-{{ViewProject::getColorStatusWorkOrder($risk->status)}}" data-toggle="tooltip" data-placement="top" title="{{Risk::statusLabel()[$risk->status]}}">
                <td class="align-center">{{$key + 1}}</td>
                <td>{{ $risk->id }}</td>
                 <td>
                    {{ empty(Risk::getTypeList()[$risk->type]) ? '' : Risk::getTypeList()[$risk->type] }}
                </td>
                <td>{{ $risk->team_leader_name }}</td>
                <td>
                    {!!nl2br(e($risk->content))!!}
                </td>
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
                       {{ViewHelper::getNickName($risk->owner_email)}}
                    @endif
                </td>
                <td>
                    @if ($risk->due_date)
                        {{ $risk->due_date }}
                    @endif
                </td>
                <td>{{ $risk->created_at }}</td>
                <td>{{ $risk->updated_at }}</td>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <td>
                    <span>
                        <a href="{{ route('project::report.risk.detail', [ 'id' => $risk->id ]) }}"><i class="fa fa-pencil-square-o width-38 btn-edit edit-risk edit-risk-{{$risk->id}}" data-id="{{$risk->id}}" data-status="{{$risk->status}}"><i class="fa fa-spin fa-refresh hidden "></i></i></a>
                        <i class="fa fa-plus btn-edit btn-add-task" data-id="{{$risk->id}}" title="Add task"></i>
                        @if ($risk->status != Risk::STATUS_CANCELLED)
                            <span data-id="{{$risk->id}}" style="padding-left: 5px"><a id="button_cancel_css" data-id="{{$risk->id}}" class="btn-move button_cancel_risk button_cancel_css_{{$risk->id}}" title="Cancel Risk"><i class="fa fa-times" title="Cancel Risk"></i></a></span>
                        @endif
                    </span>
                </td>
                @endif
            </tr>
            @endforeach
            @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
            <tr class="tr-add-risk">
                <td colspan="9" class="slove-risk">
                  <span href="#" class="btn-add add-risk" data-project-id="{{$project->id}}"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@else
<p>{{ trans('project::view.Note security') }} <a target="_blank" rel="noopener noreferrer" href="{{ route('project::report.common-risk')}}">{{ trans('project::view.Here') }}</a> {{ trans('project::view.view sample report') }}</p>
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_RISK]}}" id="table-risk">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="align-center">{{trans('project::view.No')}}</th>
                <th style="width: 20px;">{{trans('project::view.ID risk')}}</th>
                <th >{{trans('project::view.Risk Type')}}</th>
                <th >{{trans('project::view.Summary')}}</th>
                <th >{{trans('project::view.Status')}}</th>
                <th >{{trans('project::view.Priority')}}</th>
                <th >{{trans('project::view.Owner')}}</th>
                <th >{{trans('project::view.Due Date')}}</th>
                <th >{{trans('project::view.Create date')}}</th>
                <th >{{trans('project::view.Update date')}}</th>
                @if(isset($permissionEdit) && $permissionEdit)
                <th class="width-9-per">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($allRisks as $key => $risk)
            <tr>
                <td class="align-center">{{$key + 1}}</td>
                <td>{{ $risk->id }}</td>
                <td>
                    {{ empty(Risk::getTypeList()[$risk->type]) ? '' : Risk::getTypeList()[$risk->type] }}
                </td>
                <td>
                    {!!nl2br(e($risk->content))!!}
                </td>
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
                       {{ViewHelper::getNickName($risk->owner_email)}}
                    @endif
                </td>
                
                <td>
                    @if ($risk->due_date)
                    {{ $risk->due_date }}
                    @endif
                </td>
                <td>{{ $risk->created_at }}</td>
                <td>{{ $risk->updated_at }}</td>
                @if(isset($permissionEdit) && $permissionEdit && !$project->isClosed())
                <td>
                    <span>
                        <a href="{{ route('project::risk.detail', ['id' => $risk->id ]) }}"><i class="fa fa-pencil-square-o width-38 btn-edit edit-risk edit-risk-{{$risk->id}}" data-view="0" data-id="{{$risk->id}}"
                                                                                                       data-redirect="{{ route('project::project.edit', ['id' => $project->id]) . '#risk' }}"><i class="fa fa-spin fa-refresh hidden "></i></i></a>
                    </span>
                    <span>
                        <i class="fa fa-plus btn-edit btn-add-task" data-url-ajax="{{ URL::route('project::task.add.ajax', ['id' => $project->id, 'type' => Task::TYPE_RISK, 'risk_id' => $risk->id, 'template' => 1]) }}" data-id="{{$risk->id}}" title="Add task"></i>
                    </span>
                    @if ($risk->status != Risk::STATUS_CANCELLED)
                        <span data-id="{{$risk->id}}"><a id="button_cancel_css" data-id="{{$risk->id}}" class="btn-move button_cancel_risk button_cancel_css_{{$risk->id}}" title="Cancel Risk"><i class="fa fa-times" title="Cancel Risk"></i></a></span>
                    @endif

                </td>
                @else
                <td>
                    <span>
                        <i class="fa fa-eye width-38 btn-edit edit-risk edit-risk-{{$risk->id}}" data-view="1" data-id="{{$risk->id}}"><i class="fa fa-spin fa-refresh hidden "></i></i>
                    </span>
                </td>
                @endif
            </tr>
            @endforeach
            @if(isset($permissionEdit) && $permissionEdit && !$project->isClosed())
            <tr class="tr-add-risk">
                <td colspan="9" class="slove-risk">
                  <button href="#" class="btn-add add-risk" data-project-id="{{$project->id}}"><i class="fa fa-plus"></i></button>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif
@endif
<div class="modal fade" id="modal-task-risk" data-keyboard="false" >
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{trans('resource::view.Nhập người giới thiệu')}}</h4>
            </div>
            <div class="modal-body">
                
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade modal-danger" id="modal-cancel-risk" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ Lang::get('core::view.Are you sure cancel item(s)?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-submit">{{ Lang::get('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<script>
    var urlCancelRisk = '{{ route("project::risk.cancel") }}';
    $(document).on('click', '.button_cancel_risk', function () {
        console.log(123);
        $('#modal-cancel-risk').modal('show');
        var $riskId = $(this).attr('data-id');
        cancel_risk($riskId);
    });

    function cancel_risk($riskId) {
        console.log($riskId);
        $(document).on('click', '#modal-cancel-risk .btn-submit', function() {
            $('#modal-cancel-risk').modal('hide');
            $.ajax({
                url: urlCancelRisk,
                type: 'post',
                data: {
                    riskId: $riskId
                },
                success: function (data) {
                    $("span[data-id='" + $riskId + "']").remove();
                },
                error: function () {
                    alert('ajax fail to fetch data');
                },
            });
        });
    }
</script>