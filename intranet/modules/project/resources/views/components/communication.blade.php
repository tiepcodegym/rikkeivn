<?php 
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Project\Model\CommunicationProject;
use Rikkei\Project\Model\Task;
use Illuminate\Support\Str;

$allNameTab = Task::getAllNameTabWorkorder();
?>
<div id="workorder_communication">
<div class="row">
    <h4 class="col-md-9">III. Communication & Reporting</h4>
    @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
        <div class="col-md-3 align-right margin-bottom-20">
            <button class="btn-add sync_report_example" data-id="{{$project->id}}"
                    id="sync_report_example" data-reload="1" type="button">{{ trans('project::view.sync report example') }}
                <i class="fa fa-spin fa-refresh hidden sync-loading"></i>
            </button>
        </div>
    @endif
</div>
@if(isset($detail))
    <div class="table-responsive table-content-communication" id="table-communication">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
            <tr>
                <th class="width-20-per">{{trans('project::view.Type')}}</th>
                <th class="width-15-per">{{trans('project::view.Method')}}</th>
                <th class="width-15-per">{{trans('project::view.When')}}</th>
                <th class="width-25-per">{{trans('project::view.Information')}}</th>
                <th class="width-15-per">{{trans('project::view.Stakeholder')}}</th>
                @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                    <th class="width-10-per">&nbsp;</th>
                @endif
            </tr>
            </thead>
            <tbody>
            <tr><th>Project Meeting</th></tr>
            @if(isset($communicationMeeting))
                @foreach($communicationMeeting as $key => $meeting)
                    <tr class="tr-communication_meeting-{{$meeting->id}} tr-communication_meeting-css">
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$meeting->id}}" name="type" class="popover-wo-other type-communication_meeting-{{$meeting->id}} white-space">{!!Str::words(nl2br(e($meeting->type)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-type-communication_meeting-{{$meeting->id}} white-space" rows="2">{!! $meeting->type !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$meeting->id}}" name="method" class="popover-wo-other method-communication_meeting-{{$meeting->id}} white-space">{!!Str::words(nl2br(e($meeting->method)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-method-communication_meeting-{{$meeting->id}} white-space" rows="2">{!! $meeting->method !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$meeting->id}}" name="time" class="popover-wo-other time-communication_meeting-{{$meeting->id}} white-space">{!!Str::words(nl2br(e($meeting->time)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-time-communication_meeting-{{$meeting->id}} white-space" rows="2">{!! $meeting->time !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$meeting->id}}" name="information" class="popover-wo-other information-communication_meeting-{{$meeting->id}} white-space">{!!Str::words(nl2br(e($meeting->information)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-information-communication_meeting-{{$meeting->id}} white-space" rows="2">{!! $meeting->information !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$meeting->id}}" name="stakeholder" class="popover-wo-other stakeholder-communication_meeting-{{$meeting->id}} white-space">{!!Str::words(nl2br(e($meeting->stakeholder)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-stakeholder-communication_meeting-{{$meeting->id}} white-space" rows="2">{!! $meeting->stakeholder !!}</textarea>
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit)
                            <td>
                                <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-communication_meeting save-communication_meeting-{{$meeting->id}}" data-id="{{$meeting->id}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-communication_meeting edit-communication_meeting-{{$meeting->id}}" data-id="{{$meeting->id}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-communication_meeting-{{$meeting->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-confirm-new delete-communication_meeting delete-communication_meeting-{{$meeting->id}}" data-id="{{$meeting->id}}"></i>
                                    <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-communication_meeting refresh-communication_meeting-{{$meeting->id}}" data-id="{{$meeting->id}}"></i>
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endif
            @if(isset($permissionEdit) && $permissionEdit)
                <tr class="tr-add-communication_meeting">
                    <td colspan="8" class="slove-communication_meeting">
                        <span href="#" class="btn-add add-communication_meeting"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-communication_meeting tr-communication_meeting-hidden tr-communication_meeting-css">
                    <td>
                        <span>
                            <textarea class="form-control width-100 type-communication_meeting" name="type_communication_meeting" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 method-communication_meeting" name="method_communication_meeting" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 time-communication_meeting" name="time_communication_meeting" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 information-communication_meeting" name="information_communication_meeting" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 stakeholder-communication_meeting" name="stakeholder_communication_meeting" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-items" id="loading-item-communication_meeting"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-communication_meeting"></i>
                            <i class="fa fa-trash-o btn-delete remove-communication_meeting"></i>
                        </span>
                    </td>
                </tr>
            @endif


            <tr><th>Customer Communication and Reporting</th></tr>
            @if(isset($communicationReport))
                @foreach($communicationReport as $keyReport => $report)
                    <tr class="tr-communication_report-{{$report->id}} tr-communication_report-css">
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$report->id}}" name="type" class="popover-wo-other type-communication_report-{{$report->id}} white-space">{!!Str::words(nl2br(e($report->type)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-type-communication_report-{{$report->id}} white-space" rows="2">{!! $report->type !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$report->id}}" name="method" class="popover-wo-other method-communication_report-{{$report->id}} white-space">{!!Str::words(nl2br(e($report->method)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-method-communication_report-{{$report->id}} white-space" rows="2">{!! $report->method !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$report->id}}" name="time" class="popover-wo-other time-communication_report-{{$report->id}} white-space">{!!Str::words(nl2br(e($report->time)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-time-communication_report-{{$report->id}} white-space" rows="2">{!! $report->time !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$report->id}}" name="information" class="popover-wo-other information-communication_report-{{$report->id}} white-space">{!!Str::words(nl2br(e($report->information)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-information-communication_report-{{$report->id}} white-space" rows="2">{!! $report->information !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$report->id}}" name="stakeholder" class="popover-wo-other stakeholder-communication_report-{{$report->id}} white-space">{!!Str::words(nl2br(e($report->stakeholder)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-stakeholder-communication_report-{{$report->id}} white-space" rows="2">{!! $report->stakeholder !!}</textarea>
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit)
                            <td>
                                <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-communication_report save-communication_report-{{$report->id}}" data-id="{{$report->id}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-communication_report edit-communication_report-{{$report->id}}" data-id="{{$report->id}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-communication_report-{{$report->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-confirm-new delete-communication_report delete-communication_report-{{$report->id}}" data-id="{{$report->id}}"></i>
                                    <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-communication_report refresh-communication_report-{{$report->id}}" data-id="{{$report->id}}"></i>
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endif
            @if(isset($permissionEdit) && $permissionEdit)
                <tr class="tr-add-communication_report">
                    <td colspan="8" class="slove-communication_report">
                        <span href="#" class="btn-add add-communication_report"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-communication_report tr-communication_report-hidden tr-communication_report-css">
                    <td>
                        <span>
                            <textarea class="form-control width-100 type-communication_report" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 method-communication_report"  rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 time-communication_report" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 information-communication_report" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 stakeholder-communication_report" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-items" id="loading-item-communication_report"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-communication_report"></i>
                            <i class="fa fa-trash-o btn-delete remove-communication_report"></i>
                        </span>
                    </td>
                </tr>
            @endif


            <tr><th>Other</th></tr>
            @if(isset($communicationOther))
                @foreach($communicationOther as $keyOther => $other)
                    <tr class="tr-communication_other-{{$other->id}} tr-communication_other-css">
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$other->id}}" name="type" class="popover-wo-other type-communication_other-{{$other->id}} white-space">{!!Str::words(nl2br(e($other->type)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-type-communication_other-{{$other->id}} white-space" rows="2">{!! $other->type !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$other->id}}" name="method" class="popover-wo-other method-communication_other-{{$other->id}} white-space">{!!Str::words(nl2br(e($other->method)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-method-communication_other-{{$other->id}} white-space" rows="2">{!! $other->method !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$other->id}}" name="time" class="popover-wo-other time-communication_other-{{$other->id}} white-space">{!!Str::words(nl2br(e($other->time)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-time-communication_other-{{$other->id}} white-space" rows="2">{!! $other->time !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$other->id}}" name="information" class="popover-wo-other information-communication_other-{{$other->id}} white-space">{!!Str::words(nl2br(e($other->information)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-information-communication_other-{{$other->id}} white-space" rows="2">{!! $other->information !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$other->id}}" name="stakeholder" class="popover-wo-other stakeholder-communication_other-{{$other->id}} white-space">{!!Str::words(nl2br(e($other->stakeholder)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-stakeholder-communication_other-{{$other->id}} white-space" rows="2">{!! $other->stakeholder !!}</textarea>
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit)
                            <td>
                                <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-communication_other save-communication_other-{{$other->id}}" data-id="{{$other->id}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-communication_other edit-communication_other-{{$other->id}}" data-id="{{$other->id}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-communication_other-{{$other->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-confirm-new delete-communication_other delete-communication_other-{{$other->id}}" data-id="{{$other->id}}"></i>
                                    <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-communication_other refresh-communication_other-{{$other->id}}" data-id="{{$other->id}}"></i>
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endif
            @if(isset($permissionEdit) && $permissionEdit)
                <tr class="tr-add-communication_other">
                    <td colspan="8" class="slove-communication_other">
                        <span href="#" class="btn-add add-communication_other"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-communication_other tr-communication_other-hidden tr-communication_other-css">
                    <td>
                        <span>
                            <textarea class="form-control width-100 type-communication_other" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 method-communication_other"  rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 time-communication_other" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 information-communication_other" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 stakeholder-communication_other" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-items" id="loading-item-communication_other"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-communication_other"></i>
                            <i class="fa fa-trash-o btn-delete remove-communication_other"></i>
                        </span>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
@else
    <div class="table-responsive table-content-security" id="table-security">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
            <tr>
                <th class="width-20-per">{{trans('project::view.Type')}}</th>
                <th class="width-15-per">{{trans('project::view.Method')}}</th>
                <th class="width-15-per">{{trans('project::view.When')}}</th>
                <th class="width-25-per">{{trans('project::view.Information')}}</th>
                <th class="width-15-per">{{trans('project::view.Stakeholder')}}</th>
                @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                    <th class="width-10-per">&nbsp;</th>
                @endif
            </tr>
            </thead>
            <tbody>
            <tr><th>Project Meeting</th></tr>
            @if(isset($communicationMeeting))
                @foreach($communicationMeeting as $key => $meeting)
                    <tr class="tr-communication_meeting-{{$meeting->id}} tr-communication_meeting-css">
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$meeting->id}}" name="type" class="popover-wo-other type-communication_meeting-{{$meeting->id}} white-space">{!!Str::words(nl2br(e($meeting->type)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-type-communication_meeting-{{$meeting->id}} white-space" rows="2">{!! $meeting->type !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$meeting->id}}" name="method" class="popover-wo-other method-communication_meeting-{{$meeting->id}} white-space">{!!Str::words(nl2br(e($meeting->method)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-method-communication_meeting-{{$meeting->id}} white-space" rows="2">{!! $meeting->method !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$meeting->id}}" name="time" class="popover-wo-other time-communication_meeting-{{$meeting->id}} white-space">{!!Str::words(nl2br(e($meeting->time)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-time-communication_meeting-{{$meeting->id}} white-space" rows="2">{!! $meeting->time !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$meeting->id}}" name="information" class="popover-wo-other information-communication_meeting-{{$meeting->id}} white-space">{!!Str::words(nl2br(e($meeting->information)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-information-communication_meeting-{{$meeting->id}} white-space" rows="2">{!! $meeting->information !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$meeting->id}}" name="stakeholder" class="popover-wo-other stakeholder-communication_meeting-{{$meeting->id}} white-space">{!!Str::words(nl2br(e($meeting->stakeholder)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-stakeholder-communication_meeting-{{$meeting->id}} white-space" rows="2">{!! $meeting->stakeholder !!}</textarea>
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit)
                            <td>
                                <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-communication_meeting save-communication_meeting-{{$meeting->id}}" data-id="{{$meeting->id}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-communication_meeting edit-communication_meeting-{{$meeting->id}}" data-id="{{$meeting->id}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-communication_meeting-{{$meeting->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-confirm-new delete-communication_meeting delete-communication_meeting-{{$meeting->id}}" data-id="{{$meeting->id}}"></i>
                                    <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-communication_meeting refresh-communication_meeting-{{$meeting->id}}" data-id="{{$meeting->id}}"></i>
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endif
            @if(isset($permissionEdit) && $permissionEdit)
                <tr class="tr-add-communication_meeting">
                    <td colspan="8" class="slove-communication_meeting">
                        <span href="#" class="btn-add add-communication_meeting"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-communication_meeting tr-communication_meeting-hidden tr-communication_meeting-css">
                    <td>
                        <span>
                            <textarea class="form-control width-100 type-communication_meeting" name="type_communication_meeting" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 method-communication_meeting" name="method_communication_meeting" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 time-communication_meeting" name="time_communication_meeting" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 information-communication_meeting" name="information_communication_meeting" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 stakeholder-communication_meeting" name="stakeholder_communication_meeting" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-items" id="loading-item-communication_meeting"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-communication_meeting"></i>
                            <i class="fa fa-trash-o btn-delete remove-communication_meeting"></i>
                        </span>
                    </td>
                </tr>
            @endif


            <tr><th>Customer Communication and Reporting</th></tr>
            @if(isset($communicationReport))
                @foreach($communicationReport as $keyReport => $report)
                    <tr class="tr-communication_report-{{$report->id}} tr-communication_report-css">
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$report->id}}" name="type" class="popover-wo-other type-communication_report-{{$report->id}} white-space">{!!Str::words(nl2br(e($report->type)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-type-communication_report-{{$report->id}} white-space" rows="2">{!! $report->type !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$report->id}}" name="method" class="popover-wo-other method-communication_report-{{$report->id}} white-space">{!!Str::words(nl2br(e($report->method)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-method-communication_report-{{$report->id}} white-space" rows="2">{!! $report->method !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$report->id}}" name="time" class="popover-wo-other time-communication_report-{{$report->id}} white-space">{!!Str::words(nl2br(e($report->time)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-time-communication_report-{{$report->id}} white-space" rows="2">{!! $report->time !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$report->id}}" name="information" class="popover-wo-other information-communication_report-{{$report->id}} white-space">{!!Str::words(nl2br(e($report->information)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-information-communication_report-{{$report->id}} white-space" rows="2">{!! $report->information !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$report->id}}" name="stakeholder" class="popover-wo-other stakeholder-communication_report-{{$report->id}} white-space">{!!Str::words(nl2br(e($report->stakeholder)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-stakeholder-communication_report-{{$report->id}} white-space" rows="2">{!! $report->stakeholder !!}</textarea>
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit)
                            <td>
                                <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-communication_report save-communication_report-{{$report->id}}" data-id="{{$report->id}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-communication_report edit-communication_report-{{$report->id}}" data-id="{{$report->id}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-communication_report-{{$report->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-confirm-new delete-communication_report delete-communication_report-{{$report->id}}" data-id="{{$report->id}}"></i>
                                    <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-communication_report refresh-communication_report-{{$report->id}}" data-id="{{$report->id}}"></i>
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endif
            @if(isset($permissionEdit) && $permissionEdit)
                <tr class="tr-add-communication_report">
                    <td colspan="8" class="slove-communication_report">
                        <span href="#" class="btn-add add-communication_report"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-communication_report tr-communication_report-hidden tr-communication_report-css">
                    <td>
                        <span>
                            <textarea class="form-control width-100 type-communication_report" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 method-communication_report"  rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 time-communication_report" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 information-communication_report" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 stakeholder-communication_report" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-items" id="loading-item-communication_report"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-communication_report"></i>
                            <i class="fa fa-trash-o btn-delete remove-communication_report"></i>
                        </span>
                    </td>
                </tr>
            @endif


            <tr><th>Other</th></tr>
            @if(isset($communicationOther))
                @foreach($communicationOther as $keyOther => $other)
                    <tr class="tr-communication_other-{{$other->id}} tr-communication_other-css">
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$other->id}}" name="type" class="popover-wo-other type-communication_other-{{$other->id}} white-space">{!!Str::words(nl2br(e($other->type)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-type-communication_other-{{$other->id}} white-space" rows="2">{!! $other->type !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$other->id}}" name="method" class="popover-wo-other method-communication_other-{{$other->id}} white-space">{!!Str::words(nl2br(e($other->method)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-method-communication_other-{{$other->id}} white-space" rows="2">{!! $other->method !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$other->id}}" name="time" class="popover-wo-other time-communication_other-{{$other->id}} white-space">{!!Str::words(nl2br(e($other->time)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-time-communication_other-{{$other->id}} white-space" rows="2">{!! $other->time !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$other->id}}" name="information" class="popover-wo-other information-communication_other-{{$other->id}} white-space">{!!Str::words(nl2br(e($other->information)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-information-communication_other-{{$other->id}} white-space" rows="2">{!! $other->information !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="communication" data-id="{{$other->id}}" name="stakeholder" class="popover-wo-other stakeholder-communication_other-{{$other->id}} white-space">{!!Str::words(nl2br(e($other->stakeholder)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-stakeholder-communication_other-{{$other->id}} white-space" rows="2">{!! $other->stakeholder !!}</textarea>
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit)
                            <td>
                                <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-communication_other save-communication_other-{{$other->id}}" data-id="{{$other->id}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-communication_other edit-communication_other-{{$other->id}}" data-id="{{$other->id}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-communication_other-{{$other->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-confirm-new delete-communication_other delete-communication_other-{{$other->id}}" data-id="{{$other->id}}"></i>
                                    <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-communication_other refresh-communication_other-{{$other->id}}" data-id="{{$other->id}}"></i>
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endif
            @if(isset($permissionEdit) && $permissionEdit)
                <tr class="tr-add-communication_other">
                    <td colspan="8" class="slove-communication_other">
                        <span href="#" class="btn-add add-communication_other"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-communication_other tr-communication_other-hidden tr-communication_other-css">
                    <td>
                        <span>
                            <textarea class="form-control width-100 type-communication_other" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 method-communication_other"  rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 time-communication_other" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 information-communication_other" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 stakeholder-communication_other" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-items" id="loading-item-communication_other"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-communication_other"></i>
                            <i class="fa fa-trash-o btn-delete remove-communication_other"></i>
                        </span>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
@endif
</div>