<?php

use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\MemberCommunication;
use Illuminate\Support\Str;

$allNameTab = Task::getAllNameTabWorkorder();
$allEmployee = Employee::getAllEmployee();
$allRole = getOptions::getAllRoles();
?>
<div id="workorder_member_communication">
    <div class="row">
        <div class="col-md-9"><label class="control-label column-width-12-5-per">2. Internal Interface</label></div>
        @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
            <div class="col-md-3 align-right margin-bottom-20">
                <button class="btn-add" data-id="{{$project->id}}"
                        id="sync_project_allocation" data-reload="1" type="button">{{ trans('project::view.sync team allocation') }}
                    <i class="fa fa-spin fa-refresh hidden sync-loading"></i>
                </button>
            </div>
        @endif
    </div>
    @if(isset($detail))
        <div class="table-responsive table-content-member_communication" id="table-member_communication">
            <table class="edit-table table table-bordered table-condensed dataTable">
                <thead>
                <tr>
                    <th class="width-10-per">{{trans('project::view.No of')}}</th>
                    <th class="width-15-per">{{ trans('project::view.Contact Person') }}</th>
                    <th class="width-15-per">{{ trans('project::view.Role') }}</th>
                    <th class="width-20-per">{{ trans('project::view.Contact Address') }}</th>
                    <th class="width-30-per">{{ trans('project::view.Responsibility') }}</th>
                    @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                        <th class="width-10-per">&nbsp;</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @if(isset($getMemberCommunication))
                    @foreach($getMemberCommunication as $key => $member)
                        <tr class="tr-member_communication-{{$member->id}} tr-member_communication-css">
                            <td>{{$key + 1}}</td>
                            <td class="td-member_communication-member">
                                <?php
                                $memberCom = Employee::getEmpEmailById($member->employee_id);
                                ?>
                                <span class="member-member_communication-{{$member->id}} white-space" data-value="{{$member->employee_id}}">{{preg_replace('/@.*/', '', $memberCom->email)}}</span>
                                    <select name="member_member_communication" class="display-none form-control input-member-member_communication-{{$member->id}} member_communication-member-select2" style="width: 100%">
                                        @foreach($allEmployee as $key => $employee)
                                            <option value="{{$employee->id}}" class="form-control width-100" @if($member->employee_id == $employee->id) selected @endif>{{preg_replace('/@.*/', '', $employee->email)}}</option>
                                        @endforeach
                                    </select>
                            </td>
                            <td class="td-member_communication-role">
                                <?php
                                $arrayRole = explode(',', $member->role);
                                ?>
                                <span data-toggle="popover" data-value="{{$member->role}}" data-type="member_communication" data-id="{{$member->id}}" class="role-member_communication-{{$member->id}} white-space">{{ MemberCommunication::getRoleCom($member, $allRole) }}</span>
                                <select name="role_member_communication" class="display-none form-control input-role-member_communication-{{$member->id}} member_communication-role-select2" multiple="multiple" style="width: 100%">
                                    @foreach($allRole as $key => $role)
                                        <option value="{{$key}}" class="form-control width-100" @if(in_array($key, $arrayRole)) selected @endif>{{$role}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="member_communication" data-id="{{$member->id}}" name="contact_address" class="popover-wo-other contact_address-member_communication-{{$member->id}} white-space">{!!Str::words(nl2br(e($member->contact_address)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-contact_address-member_communication-{{$member->id}} white-space" name="contact_address_member_communication" rows="2">{!! $member->contact_address !!}</textarea>
                            </td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="member_communication" data-id="{{$member->id}}" name="responsibility" class="popover-wo-other responsibility-member_communication-{{$member->id}} white-space">{!!Str::words(nl2br(e($member->responsibility)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-responsibility-member_communication-{{$member->id}} white-space" name="responsibility_member_communication" rows="2">{!! $member->responsibility !!}</textarea>
                            </td>
                            @if(isset($permissionEdit) && $permissionEdit)
                                <td>
                                    <span>
                                        <i class="fa fa-floppy-o display-none btn-add save-member_communication save-member_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-member_communication edit-member_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <span class="btn btn-primary display-none loading-item" id="loading-item-member_communication-{{$member->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                        <i class="fa fa-trash-o btn-delete delete-confirm-new delete-member_communication delete-member_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-member_communication refresh-member_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endif
                @if(isset($permissionEdit) && $permissionEdit)
                    <tr class="tr-add-member_communication">
                        <td colspan="8" class="slove-member_communication">
                            <span href="#" class="btn-add add-member_communication"><i class="fa fa-plus"></i></span>
                        </td>
                    </tr>
                    <tr class="display-none tr-member_communication tr-member_communication-hidden tr-member_communication-css">
                        <td></td>
                        <td class="td-member_communication-member">
                            <select name="member_member_communication" class="form-control width-100 member_communication-member-member_communication member_communication-member-select2-new">
                                @foreach($allEmployee as $key => $employee)
                                    <option value="{{$employee->id}}" class="form-control width-100">{{preg_replace('/@.*/', '', $employee->email)}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="td-member_communication-role">
                            <select name="role_member_communication" class="form-control width-100 member_communication-role-member_communication member_communication-role-select2-new" multiple="multiple">
                                @foreach($allRole as $key => $role)
                                    <option value="{{$key}}" class="form-control width-100">{{$role}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 contact_address-member_communication" name="contact_address_member_communication" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 responsibility-member_communication" name="responsibility_member_communication" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <span class="btn btn-primary display-none loading-items" id="loading-item-member_communication"><i class="fa fa-refresh fa-spin"></i></span>
                                <i class="fa fa-floppy-o btn-add add-new-member_communication"></i>
                                <i class="fa fa-trash-o btn-delete remove-member_communication"></i>
                            </span>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    @else
        <div class="table-responsive table-content-member_communication" id="table-member_communication">
            <table class="edit-table table table-bordered table-condensed dataTable">
                <thead>
                    <tr>
                        <th class="width-10-per">{{trans('project::view.No of')}}</th>
                        <th class="width-15-per">{{ trans('project::view.Contact Person') }}</th>
                        <th class="width-15-per">{{ trans('project::view.Role') }}</th>
                        <th class="width-20-per">{{ trans('project::view.Contact Address') }}</th>
                        <th class="width-30-per">{{ trans('project::view.Responsibility') }}</th>
                        @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                            <th class="width-10-per">&nbsp;</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @if(isset($getMemberCommunication))
                    @foreach($getMemberCommunication as $key => $member)
                        <tr class="tr-member_communication-{{$member->id}} tr-member_communication-css">
                            <td>{{$key + 1}}</td>
                            <td class="td-member_communication-member">
                                <?php
                                $memberCom = Employee::getEmpEmailById($member->employee_id);
                                ?>
                                <span class="member-member_communication-{{$member->id}} white-space" data-value="{{$member->employee_id}}">{{preg_replace('/@.*/', '', $memberCom['email'])}}</span>
                                <select name="member_member_communication" class="display-none form-control input-member-member_communication-{{$member->id}} member_communication-member-select2" style="width: 100%">
                                    @foreach($allEmployee as $key => $employee)
                                        <option value="{{$employee->id}}" class="form-control width-100" @if($member->employee_id == $employee->id) selected @endif>{{preg_replace('/@.*/', '', $employee->email)}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="td-member_communication-role">
                                <?php
                                $arrayRole = explode(',', $member->role);
                                ?>
                                <span data-toggle="popover" data-type="member_communication" data-value="{{$member->role}}" data-id="{{$member->id}}" class="role-member_communication-{{$member->id}} white-space">{{ MemberCommunication::getRoleCom($member, $allRole) }}</span>
                                <select name="role_member_communication" class="display-none form-control input-role-member_communication-{{$member->id}} member_communication-role-select2" multiple="multiple" style="width: 100%">
                                    @foreach($allRole as $key => $role)
                                        <option value="{{$key}}" class="form-control width-100" @if(in_array($key, $arrayRole)) selected @endif>{{$role}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="member_communication" data-id="{{$member->id}}" name="contact_address" class="popover-wo-other contact_address-member_communication-{{$member->id}} white-space">{!!Str::words(nl2br(e($member->contact_address)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-contact_address-member_communication-{{$member->id}} white-space" name="contact_address_member_communication" rows="2">{!! $member->contact_address !!}</textarea>
                            </td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="member_communication" data-id="{{$member->id}}" name="responsibility" class="popover-wo-other responsibility-member_communication-{{$member->id}} white-space">{!!Str::words(nl2br(e($member->responsibility)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-responsibility-member_communication-{{$member->id}} white-space" name="responsibility_member_communication" rows="2">{!! $member->responsibility !!}</textarea>
                            </td>
                            @if(isset($permissionEdit) && $permissionEdit)
                                <td>
                                    <span>
                                        <i class="fa fa-floppy-o display-none btn-add save-member_communication save-member_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-member_communication edit-member_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <span class="btn btn-primary display-none loading-item" id="loading-item-member_communication-{{$member->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                        <i class="fa fa-trash-o btn-delete delete-confirm-new delete-member_communication delete-member_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-member_communication refresh-member_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endif
                @if(isset($permissionEdit) && $permissionEdit)
                    <tr class="tr-add-member_communication">
                        <td colspan="8" class="slove-member_communication">
                            <span href="#" class="btn-add add-member_communication"><i class="fa fa-plus"></i></span>
                        </td>
                    </tr>
                    <tr class="display-none tr-member_communication tr-member_communication-hidden tr-member_communication-css">
                        <td></td>
                        <td class="td-member_communication-member">
                            <select name="member_member_communication" class="form-control width-100 member_communication-member-member_communication member_communication-member-select2-new">
                                @foreach($allEmployee as $key => $employee)
                                    <option value="{{$employee->id}}" class="form-control width-100">{{preg_replace('/@.*/', '', $employee->email)}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="td-member_communication-role">
                            <select name="role_member_communication" class="form-control width-100 member_communication-role-member_communication member_communication-role-select2-new" multiple="multiple">
                                @foreach($allRole as $key => $role)
                                    <option value="{{$key}}" class="form-control width-100">{{$role}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 contact_address-member_communication" name="contact_address_member_communication" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 responsibility-member_communication" name="responsibility_member_communication" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <span class="btn btn-primary display-none loading-items" id="loading-item-member_communication"><i class="fa fa-refresh fa-spin"></i></span>
                                <i class="fa fa-floppy-o btn-add add-new-member_communication"></i>
                                <i class="fa fa-trash-o btn-delete remove-member_communication"></i>
                            </span>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    @endif
</div>
