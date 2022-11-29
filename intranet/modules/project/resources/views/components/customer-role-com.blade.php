<?php
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\MemberCommunication;
use Illuminate\Support\Str;
use Rikkei\Project\View\View as ViewProject;

$allNameTab = Task::getAllNameTabWorkorder();
$allEmployee = Employee::getAllEmployee();
$allRole = getOptions::getAllRoles();
?>
<div id="workorder_customer_communication">
<h4>I. Organization Chart</h4>
<img height="400" width="900" src="{{ asset('project/images/organization_chart.png') }}">

<h4>II. Stakeholder Management</h4>
    <div class="row">
        <div class="col-md-9"><label class="control-label column-width-12-5-per">1. Customer</label></div>
        <div class="col-md-3 align-right margin-bottom-20">
        </div>
    </div>
    @if(isset($detail))
        <div class="table-responsive table-content-customer_communication" id="table-member_communication">
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
                @if(isset($getCustomerCommunication))
                    @foreach($getCustomerCommunication as $key => $member)
                        <tr class="tr-customer_communication-{{$member->id}} tr-customer_communication-css">
                            <td>{{$key + 1}}</td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="customer_communication" data-id="{{$member->id}}" name="contact_address" class="popover-wo-other customer-customer_communication-{{$member->id}} white-space">{{ $member->customer }}</span>
                                <textarea class="display-none form-control input-customer-customer_communication-{{$member->id}} white-space" name="customer_customer_communication" rows="2">{{ $member->customer }}</textarea>
                            </td>
                            <td class="td-member_communication-role">
                                <span data-toggle="popover" data-value="{{$member->role}}" data-type="customer_communication" data-id="{{$member->id}}" name="role" class="role-customer_communication-{{$member->id}} white-space">{{ MemberCommunication::getRoleCom($member, $allRole) }}</span>
                                <select name="role_customer_communication" class="display-none form-control input-role-customer_communication-{{$member->id}} customer_communication-role-select2" multiple="multiple" style="width: 100%">
                                    <?php
                                        $arrayRole = explode(',', $member->role);
                                    ?>
                                    @foreach($allRole as $key => $role)
                                        <option value="{{$key}}" class="form-control width-100" @if(in_array($key, $arrayRole)) selected @endif>{{$role}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="customer_communication" data-id="{{$member->id}}" name="contact_address" class="popover-wo-other contact_address-customer_communication-{{$member->id}} white-space">{!!Str::words(nl2br(e($member->contact_address)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-contact_address-customer_communication-{{$member->id}} white-space" name="contact_address_customer_communication" rows="2">{!! $member->contact_address !!}</textarea>
                            </td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="customer_communication" data-id="{{$member->id}}" name="responsibility" class="popover-wo-other responsibility-customer_communication-{{$member->id}} white-space">{!!Str::words(nl2br(e($member->responsibility)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-responsibility-customer_communication-{{$member->id}} white-space" name="responsibility_customer_communication" rows="2">{!! $member->responsibility !!}</textarea>
                            </td>
                            @if(isset($permissionEdit) && $permissionEdit)
                                <td>
                                    <span>
                                        <i class="fa fa-floppy-o display-none btn-add save-customer_communication save-customer_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-customer_communication edit-customer_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <span class="btn btn-primary display-none loading-item" id="loading-item-customer_communication-{{$member->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                        <i class="fa fa-trash-o btn-delete delete-confirm-new delete-customer_communication delete-customer_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-customer_communication refresh-customer_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endif
                @if(isset($permissionEdit) && $permissionEdit)
                    <tr class="tr-add-customer_communication">
                        <td colspan="8" class="slove-customer_communication">
                            <span href="#" class="btn-add add-customer_communication"><i class="fa fa-plus"></i></span>
                        </td>
                    </tr>
                    <tr class="display-none tr-customer_communication tr-customer_communication-hidden tr-customer_communication-css">
                        <td></td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 customer-customer_communication" name="customer_customer_communication" rows="2"></textarea>
                            </span>
                        </td>
                        <td class="td-customer_communication-role">
                            <select name="role_customer_communication" class="form-control width-100 customer_communication-role-customer_communication customer_communication-role-select2-new" multiple="multiple">
                                @foreach($allRole as $key => $role)
                                    <option value="{{$key}}" class="form-control width-100">{{$role}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 contact_address-customer_communication" name="contact_address_customer_communication" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 responsibility-customer_communication" name="responsibility_customer_communication" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <span class="btn btn-primary display-none loading-items" id="loading-item-customer_communication"><i class="fa fa-refresh fa-spin"></i></span>
                                <i class="fa fa-floppy-o btn-add add-new-customer_communication"></i>
                                <i class="fa fa-trash-o btn-delete remove-customer_communication"></i>
                            </span>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    @else
        <div class="table-responsive table-content-customer_communication" id="table-member_communication">
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
                @if(isset($getCustomerCommunication))
                    @foreach($getCustomerCommunication as $key => $member)
                        <tr class="tr-customer_communication-{{$member->id}} tr-customer_communication-css">
                            <td>{{$key + 1}}</td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="customer_communication" data-id="{{$member->id}}" name="contact_address" class="popover-wo-other customer-customer_communication-{{$member->id}} white-space">{{ $member->customer }}</span>
                                <textarea class="display-none form-control input-customer-customer_communication-{{$member->id}} white-space" name="customer_customer_communication" rows="2">{{ $member->customer }}</textarea>
                            </td>
                            <td class="td-customer_communication-role">
                                <span data-toggle="popover" data-value="{{$member->role}}" data-type="customer_communication" data-id="{{$member->id}}" class="role-customer_communication-{{$member->id}} white-space">{{ MemberCommunication::getRoleCom($member, $allRole) }}</span>
                                <select class="display-none form-control input-role-customer_communication-{{$member->id}} customer_communication-role-select2" multiple="multiple" style="width: 100%;">
                                    <?php
                                    $arrayRole = explode(',', $member->role);
                                    ?>
                                    @foreach($allRole as $key => $role)
                                        <option value="{{$key}}" class="form-control width-100" @if(in_array($key, $arrayRole)) selected @endif>{{$role}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="customer_communication" data-id="{{$member->id}}" name="contact_address" class="popover-wo-other contact_address-customer_communication-{{$member->id}} white-space">{!!Str::words(nl2br(e($member->contact_address)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-contact_address-customer_communication-{{$member->id}} white-space" name="contact_address_customer_communication" rows="2">{!! $member->contact_address !!}</textarea>
                            </td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="customer_communication" data-id="{{$member->id}}" name="responsibility" class="popover-wo-other responsibility-customer_communication-{{$member->id}} white-space">{!!Str::words(nl2br(e($member->responsibility)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-responsibility-customer_communication-{{$member->id}} white-space" name="responsibility_customer_communication" rows="2">{!! $member->responsibility !!}</textarea>
                            </td>
                            @if(isset($permissionEdit) && $permissionEdit)
                                <td>
                                    <span>
                                        <i class="fa fa-floppy-o display-none btn-add save-customer_communication save-customer_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-customer_communication edit-customer_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <span class="btn btn-primary display-none loading-item" id="loading-item-customer_communication-{{$member->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                        <i class="fa fa-trash-o btn-delete delete-confirm-new delete-customer_communication delete-customer_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                        <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-customer_communication refresh-customer_communication-{{$member->id}}" data-id="{{$member->id}}"></i>
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endif
                @if(isset($permissionEdit) && $permissionEdit)
                    <tr class="tr-add-customer_communication">
                        <td colspan="8" class="slove-customer_communication">
                            <span href="#" class="btn-add add-customer_communication"><i class="fa fa-plus"></i></span>
                        </td>
                    </tr>
                    <tr class="display-none tr-customer_communication tr-customer_communication-hidden tr-customer_communication-css">
                        <td></td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 customer-customer_communication" name="customer_customer_communication" rows="2"></textarea>
                            </span>
                        </td>
                        <td class="td-customer_communication-role">
                            <select name="role_customer_communication" class="form-control customer_communication-role-customer_communication customer_communication-role-select2-new" multiple="multiple">
                                @foreach($allRole as $key => $role)
                                    <option value="{{$key}}" class="form-control width-100">{{$role}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 contact_address-customer_communication" name="contact_address_customer_communication" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 responsibility-customer_communication" name="responsibility_customer_communication" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <span class="btn btn-primary display-none loading-items" id="loading-item-customer_communication"><i class="fa fa-refresh fa-spin"></i></span>
                                <i class="fa fa-floppy-o btn-add add-new-customer_communication"></i>
                                <i class="fa fa-trash-o btn-delete remove-customer_communication"></i>
                            </span>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    @endif
</div>