<?php

use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Project\Model\Security;
use Rikkei\Project\Model\Task;
use Illuminate\Support\Str;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\View\View as ViewProject;

$allEmployee = Employee::getAllEmployee();
$arrayCoo = CoreConfigData::getCOOAccount();
?>
<div id="workorder_security">
@if(isset($detail))
    <div class="table-responsive table-content-security" id="table-security">
        <table class="edit-table table table-bordered table-condensed dataTable">
            <thead>
            <tr>
                <th class="width-5-per">{{trans('project::view.No')}}</th>
                <th class="width-20-per">{{trans('project::view.Clause')}}</th>
                <th class="width-20-per">{{trans('project::view.Security requirements')}}</th>
                <th class="width-15-per">{{trans('project::view.Procedure')}}</th>
                <th class="width-15-per">{{trans('project::view.Period')}}</th>
                <th class="width-15-per">{{trans('project::view.PIC')}}</th>
                @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                    <th class="width-10-per">&nbsp;</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @if(isset($allSecurity))
                @foreach($allSecurity as $key => $security)
                    <tr class="tr-security-{{$security->id}} tr-security-css">
                        <td>{{$key + 1}}</td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="security" data-id="{{$security->id}}" name="content" class="popover-wo-other content-security-{{$security->id}} white-space">{!!Str::words(nl2br(e($security->content)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-content-security-{{$security->id}} white-space" name="content_security" rows="2">{!! $security->content !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="security" data-id="{{$security->id}}" name="description" class="popover-wo-other description-security-{{$security->id}} white-space">{!!Str::words(nl2br(e($security->description)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-description-security-{{$security->id}} white-space" name="description_security" rows="2">{!! $security->description !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="security" data-id="{{$security->id}}" name="procedure" class="popover-wo-other procedure-security-{{$security->id}} white-space">{!!Str::words(nl2br(e($security->procedure)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-procedure-security-{{$security->id}} white-space" name="procedure_security" rows="2">{!! $security->procedure !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="security" data-id="{{$security->id}}" name="period" class="popover-wo-other period-security-{{$security->id}} white-space">{!!Str::words(nl2br(e($security->period)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-period-security-{{$security->id}} white-space" name="period_security" rows="2">{!! $security->period !!}</textarea>
                        </td>
                        <td class="td-security-member">
                            <?php
                            $memberSecurity = Security::getAllMemberOfSecurity($security->id);
                            $dataValue = '';
                            if ($memberSecurity) {
                                $dataValue = implode(",",$memberSecurity);
                            }
                            ?>
                            <span class="participants-security-{{$security->id}} white-space" data-value="{{$dataValue}}">{{ViewProject::getContentSecurity($security, $allEmployee)}}</span>
                            <select class="display-none form-control width-100 input-participants-security-{{$security->id}} security-member-select2" multiple="multiple">
                                @foreach($allEmployee as $key => $employee)
                                    @if(!in_array($employee->email, $arrayCoo))
                                        <option value="{{$employee->id}}" class="form-control width-100" {{in_array($employee->id, $memberSecurity) ? 'selected' : ''}}>{{preg_replace('/@.*/', '', $employee->email)}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit)
                            <td>
                                <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-security save-security-{{$security->id}}" data-id="{{$security->id}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-security edit-security-{{$security->id}}" data-id="{{$security->id}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-security-{{$security->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-confirm-new delete-security delete-security-{{$security->id}}" data-id="{{$security->id}}"></i>
                                    <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-security refresh-security-{{$security->id}}" data-id="{{$security->id}}"></i>
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endif
            @if(isset($permissionEdit) && $permissionEdit)
                <tr class="tr-add-security">
                    <td colspan="8" class="slove-security">
                        <span href="#" class="btn-add add-security"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-security tr-security-hidden tr-security-css">
                    <td></td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 content-security" name="content_security" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 description-security" name="description_security" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 procedure-security" name="procedure_security" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 period-security" name="period_security" rows="2"></textarea>
                        </span>
                    </td>
                    <td class="td-security-member">
                        <select name="employee_id" class="form-control width-100 security-member-security security-member-select2-new" multiple="multiple">
                            @foreach($allEmployee as $key => $employee)
                                @if(!in_array($employee->email, $arrayCoo))
                                    <option value="{{$employee->id}}" class="form-control width-100">{{preg_replace('/@.*/', '', $employee->email)}}</option>
                                @endif
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-items" id="loading-item-security"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-security"></i>
                            <i class="fa fa-trash-o btn-delete remove-security"></i>
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
                <th class="width-5-per">{{trans('project::view.No')}}</th>
                <th class="width-20-per">{{trans('project::view.Clause')}}</th>
                <th class="width-20-per">{{trans('project::view.Security requirements')}}</th>
                <th class="width-15-per">{{trans('project::view.Procedure')}}</th>
                <th class="width-15-per">{{trans('project::view.Period')}}</th>
                <th class="width-15-per">{{trans('project::view.PIC')}}</th>
                @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                    <th class="width-10-per">&nbsp;</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @if(isset($getSecurity))
                @foreach($getSecurity as $key => $security)
                    <tr class="tr-security-{{$security->id}} tr-security-css">
                        <td>{{$key + 1}}</td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="security" data-id="{{$security->id}}" name="content" class="popover-wo-other content-security-{{$security->id}} white-space">{!!Str::words(nl2br(e($security->content)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-content-security-{{$security->id}} white-space" name="content_security" rows="2">{!! $security->content !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="security" data-id="{{$security->id}}" name="description" class="popover-wo-other description-security-{{$security->id}} white-space">{!!Str::words(nl2br(e($security->description)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-description-security-{{$security->id}} white-space" name="description_security" rows="2">{!! $security->description !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="security" data-id="{{$security->id}}" name="procedure" class="popover-wo-other procedure-security-{{$security->id}} white-space">{!!Str::words(nl2br(e($security->procedure)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-procedure-security-{{$security->id}} white-space" name="procedure_security" rows="2">{!! $security->procedure !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' data-type="security" data-id="{{$security->id}}" name="period" class="popover-wo-other period-security-{{$security->id}} white-space">{!!Str::words(nl2br(e($security->period)), 30, '...')!!}</span>
                            <textarea class="display-none form-control input-period-security-{{$security->id}} white-space" name="period_security" rows="2">{!! $security->period !!}</textarea>
                        </td>
                        <td class="td-security-member">
                            <?php
                            $memberSecurity = Security::getAllMemberOfSecurity($security->id);
                            $dataValue = '';
                            if ($memberSecurity) {
                                $dataValue = implode(",",$memberSecurity);
                            }
                            ?>
                            <span class="participants-security-{{$security->id}} white-space" data-value="{{$dataValue}}">{{ViewProject::getContentSecurity($security, $allEmployee)}}</span>
                            <select class="display-none form-control width-100 input-participants-security-{{$security->id}} security-member-select2" multiple="multiple">
                                @foreach($allEmployee as $key => $employee)
                                    @if(!in_array($employee->email, $arrayCoo))
                                        <option value="{{$employee->id}}" class="form-control width-100" {{in_array($employee->id, $memberSecurity) ? 'selected' : ''}}>{{preg_replace('/@.*/', '', $employee->email)}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit)
                            <td>
                                <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-security save-security-{{$security->id}}" data-id="{{$security->id}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-security edit-security-{{$security->id}}" data-id="{{$security->id}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-security-{{$security->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-confirm-new delete-security delete-security-{{$security->id}}" data-id="{{$security->id}}"></i>
                                    <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-security refresh-security-{{$security->id}}" data-id="{{$security->id}}"></i>
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endif
            @if(isset($permissionEdit) && $permissionEdit)
                <tr class="tr-add-security">
                    <td colspan="8" class="slove-security">
                        <span href="#" class="btn-add add-security"><i class="fa fa-plus"></i></span>
                    </td>
                </tr>
                <tr class="display-none tr-security tr-security-hidden tr-security-css">
                    <td></td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 content-security" name="content_security" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 description-security" name="description_security" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 procedure-security" name="procedure_security" rows="2"></textarea>
                        </span>
                    </td>
                    <td>
                        <span>
                            <textarea class="form-control width-100 period-security" name="period_security" rows="2"></textarea>
                        </span>
                    </td>
                    <td class="td-security-member">
                        <select name="employee_id" class="form-control width-100 security-member-security security-member-select2-new" multiple="multiple">
                            @foreach($allEmployee as $key => $employee)
                                @if(!in_array($employee->email, $arrayCoo))
                                    <option value="{{$employee->id}}" class="form-control width-100">{{preg_replace('/@.*/', '', $employee->email)}}</option>
                                @endif
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <span>
                            <span class="btn btn-primary display-none loading-items" id="loading-item-security"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-security"></i>
                            <i class="fa fa-trash-o btn-delete remove-security"></i>
                        </span>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
@endif
</div>
