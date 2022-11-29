<?php
    use Rikkei\Project\Model\Project;
    use Rikkei\Project\Model\ProjectCategory;
    use Rikkei\Project\Model\ProjectClassification;
    use Rikkei\Project\Model\ProjectBusiness;
    use Rikkei\Project\Model\ProjectSector;
    use Carbon\Carbon;
    use Rikkei\Project\View\View;
    use Rikkei\Project\View\GeneralProject;
    use Rikkei\Project\Model\ProjectProgramLang;
    use Rikkei\Project\View\ProjDbHelp;
    use Rikkei\Sales\Model\Company;
    use Rikkei\Project\Model\ProjectMember;

if (!$pmActive) {
    $pmActive = new ProjectMember();
}

$urlSavePurchaseOrderId = route('project::project.save.purchase');
$urlGetPurchaseOrderId = route('project::project.get.purchase');
$urlSavePurchaseOrderIdToCRM = route('project::project.save.purchase_to_crm');
$countMemberProj = ProjectMember::countMemberProj($project->id);
$countMemberProjCurrent = ProjectMember::countMemberProj($project->id, true);
?>
<style>
    .tooltip > .tooltip-inner {
        text-align: left;
        padding: 10px;
        max-width: 500px;
    }
    .unapproved-price{
        background: #f1c5ab !important;
    }
    .btn-approve{
        margin-right: 10px;
    }
    .submit-successful{
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
        padding: 10px 0;
        margin-top: 10px;
        display: none;
    }
    .bootstrap-tagsinput {
        width: 100%;
        line-height: 28px;
    }
    .bootstrap-tagsinput .tag {
        font-size: 13px;
    }
    .bootstrap-tagsinput input {
        width: 100%;
    }
    .p-taginput {
        width: 100%;
        line-height: 28px;
    }
    .p-taginput span{
        font-size: 13px;
        margin-right: 2px;
        background-color: #00c0ef !important;
        padding: .2em .6em .3em;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: .25em;
    }
</style>
<div class="row">
    <form class="form-horizontal frm-create-project" id="form_edit_project">
        <div class="row">
            @if ($project->project_code_auto)
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label">{{trans('project::view.Project Code')}}</label>
                <div class="col-sm-8">
                    <p class="form-control-static">{{ $project->project_code_auto }}</p>
                </div>
            </div>
            @endif
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label">{{trans('project::view.Purchase Order ID')}}</label>
                <div class="col-sm-8">
                    @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <span>
                        <input type="text" class="form-control input-basic-info js-po-id-taginput" id="purchase_order_id" name="purchase_order_id" value="{{isset($project->po_id) ? $project->po_id : ''}}"  placeholder="{{trans('project::view.Purchase Order ID')}}" data-toggle="tooltip" data-container="body" data-role="tagsinput">
                    </span>
                    @else
                        @if ($project->state == Project::STATE_CLOSED && $currentUser->id == $idLeader)
                            <span>
                                <input type="text" class="form-control input-basic-info js-po-id-taginput" id="purchase_order_id" name="purchase_order_id" value="{{isset($project->po_id) ? $project->po_id : ''}}"  placeholder="{{trans('project::view.Purchase Order ID')}}" data-toggle="tooltip" data-container="body" data-role="tagsinput">
                            </span>
                        @else
                            <p class="form-control-static p-taginput" data-toggle="tooltip" data-container="body">
                                @php
                                    $poIdText = isset($project->po_id) ? $project->po_id : '';
                                    $poIdArray = explode(",", $poIdText);
                                @endphp
                                @if (count($poIdArray) > 0 && (isset($poIdArray[0]) && $poIdArray[0] != ''))
                                    @foreach ($poIdArray as $item)
                                        <span>{{ $item }}</span>
                                    @endforeach
                                @endif
                            </p>
                            <input type="hidden" id="purchase_order_id" value="{{ isset($project->po_id) ? $project->po_id : '' }}">
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <?php
        $canEditTypeMM = false;
        $labelTypeMM = $project->getLabelTypeMM();
        $labelTypeMMDraft = $projectDraft->getLabelTypeMM();
        ?>
        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Project Name')}} @if(!$checkHasVersionWO)<em>*</em>@endif</label>
                <div class="col-sm-8">
                @if(isset($permissionEdit) && $permissionEdit)
                    @if($checkEditWorkOrder)
                        <?php
                            $oldName = old('name');
                            $canEditTypeMM = true;
                        ?>
                        <span>
                            <input type="text" class="form-control input-basic-info {{($projectDraft->name != $project->name) ? 'changed' : ''}}" id="name" name="name" value="{{$oldName ? $oldName : $projectDraft->name}}"  placeholder="{{trans('project::view.Project name')}}" data-toggle="tooltip" data-container="body" data-original-title="{{trans('project::view.Approved Value')}}: {{$project->name}}">
                            @if($errors->has('name'))
                                <label id="name-error" class="error" for="name">{{$errors->first('name')}}</label>
                            @endif
                        </span>
                    @else
                        <p class="form-control-static" data-toggle="tooltip" data-container="body" data-original-title="{{trans('project::view.Approved Value')}}: {{$project->name}}">{{$projectDraft->name}}</p>
                    @endif
                @else
                    <p class="form-control-static" data-toggle="tooltip" data-container="body" data-original-title="{{trans('project::view.Approved Value')}}: {{$projectDraft->name}}">{{$projectDraft->name}}</p>
                @endif
                </div>
            </div>

            <div class="col-sm-6 form-group">
                <label for="category" class="col-sm-4 control-label margin-top--10 required">{{trans('project::view.Project category')}} <em>*</em></label>
                <div class="col-sm-8 fg-valid-custom" id="select_category">
                    <div>
                        @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                            <select class="form-control popClass input-basic-info" name="category" id="category">
                                <option value=""></option>
                                @foreach($lblProjectCate as $items => $item)
                                    <option value="{{$items}}" @if($project->category_id == $items) selected @endif>{{ $item }}</option>
                                @endforeach
                            </select>
                        @else
                            <p class="form-control-static">{{ $project->category_id ? ProjectCategory::getCateById($project->category_id) : ''}}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6" id="select-team">
                <label class="col-sm-4 control-label required">{{trans('project::view.Group')}} <em>*</em></label>
                <div class="col-sm-8">
                    @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <div class="dropdown team-dropdown">
                        <span>
                        <select id="team_id" class="form-control select-search hidden team-dev-tree {{(array_diff($allTeamDraft, $allTeam)) ? 'changed' : ''}}" name="team_id[]" multiple="multiple" data-original-title="{{trans('project::view.Approved Value')}}: {{View::getLabelTeamOfProject($allTeamName, $allTeam)}}"></select>
                        @if($errors->has('team_id'))
                        <label id="team_id-error" class="error" for="team_id">{{$errors->first('team_id')}}</label>
                        @endif
                        </span>
                    </div>
                    @else
                    <p class="form-control-static {{(array_diff($allTeamDraft, $allTeam)) ? 'is-change-value-summary' : ''}}" data-toggle="tooltip" data-container="body" data-original-title="{{trans('project::view.Approved Value')}}: {{View::getLabelTeamOfProject($allTeamName, $allTeam)}}">{{View::getLabelTeamOfProject($allTeamName, $allTeamDraft)}}</p>
                        <div class="dropdown team-dropdown hidden">
                        <select id="team_id" class="form-control select-search hidden team-dev-tree {{(array_diff($allTeamDraft, $allTeam)) ? 'changed' : ''}}" name="team_id[]" multiple="multiple" data-original-title="{{trans('project::view.Approved Value')}}: {{View::getLabelTeamOfProject($allTeamName, $allTeam)}}"></select>
                        </div>
                    @endif
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label">{{trans('project::view.Group Leader')}}</label>
                <div class="col-sm-8 div-leader-id">
                    @include('project::template.content-select-leader')
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{ trans('project::view.Project Manager') }}</label>
                <div class="col-sm-8" id="manager-project">
                    @if(isset($permissionEdit) && $permissionEdit)
                        @if(! $checkEditWorkOrder)
                            <p class="form-control-static {{($projectDraft->manager_id != $project->manager_id) ? 'is-change-value-summary' : ''}}" data-toggle="tooltip" data-container="body" data-original-title="{{trans('project::view.Approved Value')}}: {{$pmActive ? $pmActive->name : ''}} ({{GeneralProject::getNickName($pmActive ? $pmActive->email : '')}})">
                                @if ($projectDraft->manager_id && $pmDraft)
                                    {{$pmDraft->name}} ({{GeneralProject::getNickName($pmDraft->email)}})
                                @elseif ($pmActive)
                                    {{$pmActive->name}} ({{GeneralProject::getNickName($pmActive->email)}})
                                @endif
                            </p>
                        @else
                            <span>
                                @if (count($allPmActive))
                                <select name="manager_id" class="form-control input-basic-info {{($projectDraft->manager_id != $project->manager_id) ? 'changed' : ''}}" id="manager_id" data-original-title="{{trans('project::view.Approved Value')}}: {{$pmActive->name}} ({{GeneralProject::getNickName($pmActive->email)}})">
                                    <option value="">&nbsp;</option>
                                    @foreach ($allPmActive as $pm)
                                        <option value="{{$pm->id}}"{!!($pm->id == $projectDraft->manager_id) ? 'selected' : ''!!}>{{$pm->name}} ({{GeneralProject::getNickName($pm->email)}})</option>
                                    @endforeach
                                </select>
                                @endif
                            </span>
                        @endif
                    @else
                        @if ($pmDraft)
                        <p class="form-control-static {{($projectDraft->manager_id != $project->manager_id) ? 'is-change-value-summary' : ''}}" data-toggle="tooltip" data-container="body" data-original-title="{{trans('project::view.Approved Value')}}: {{$pmActive->name}} ({{GeneralProject::getNickName($pmActive->email)}})">{{$pmDraft->name}} ({{GeneralProject::getNickName($pmDraft->email)}})</p>
                        @else
                        <p class="form-control-static {{($projectDraft->manager_id != $project->manager_id) ? 'is-change-value-summary' : ''}}" data-toggle="tooltip" data-container="body" data-original-title="{{trans('project::view.Approved Value')}}: {{$pmActive->name}} ({{GeneralProject::getNickName($pmActive->email)}})">{{$pmActive->name}} ({{GeneralProject::getNickName($pmActive->email)}})</p>
                        @endif
                    @endif
                </div>
            </div>

            <div class="col-sm-6 form-group">
                <label for="classification" class="col-sm-4 control-label margin-top--10 required">{{trans('project::view.Classification')}} <em>*</em></label>
                <div class="col-sm-8 fg-valid-custom" id="select_classification">
                    <div>
                        @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                            <select class="form-control popClass input-basic-info" name="classification" id="classification">
                                <option value=""></option>
                                @foreach($lblClassCate as $items => $item)
                                    <option value="{{$items}}" @if($project->classification_id == $items) selected @endif>{{ $item }}</option>
                                @endforeach
                            </select>
                        @else
                            <p class="form-control-static">{{ $project->classification_id ? ProjectClassification::getClassById($project->classification_id) : ''}}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Contract Type')}} @if(!$checkHasVersionWO)<em>*</em>@endif</label>
                <div class="col-sm-8" id="type-project">
                @if(isset($permissionEdit) && $permissionEdit)
                    @if($checkEditWorkOrder)
                        <select name="type" class="form-control input-basic-info {{($projectDraft->type != $project->type) ? 'changed' : ''}}" id="type" data-original-title="{{trans('project::view.Approved Value')}}: {{$labelTypeProject[$project->type]}}">
                            @foreach($labelTypeProject as $key => $value)
                                @if(old('type') == $key)
                                <option value="{{$key}}" selected>{{$value}}</option>
                                @else
                                <option value="{{$key}}" {{$projectDraft->type == $key ? 'selected' : '' }}>{{$value}}</option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <p class="form-control-static {{($projectDraft->type != $project->type) ? 'is-change-value-summary' : ''}}" data-toggle="tooltip" data-container="body" data-original-title="{{trans('project::view.Approved Value')}}: {{$labelTypeProject[$project->type]}}">{{$labelTypeProject[$projectDraft->type]}}</p>
                    @endif
                @else
                    <p class="form-control-static {{($projectDraft->type != $project->type) ? 'is-change-value-summary' : ''}}" data-toggle="tooltip" data-container="body" data-original-title="{{trans('project::view.Approved Value')}}: {{$labelTypeProject[$project->type]}}">{{$labelTypeProject[$projectDraft->type]}}</p>
                @endif
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Project Status')}} <em>*</em></label>
                <div class="col-sm-8" id="status-project">
                @if(isset($permissionEdit) && $permissionEdit)
                    @if($checkEditWorkOrder)
                    <span>
                        <select name="state" class="form-control input-basic-info {{($projectDraft->state != $project->state) ? 'changed' : ''}}" id="state" data-original-title="{{trans('project::view.Approved Value')}}: {{ isset($status[$project->state]) ? $status[$project->state] : '' }}">
                            @foreach($status as $key => $value)
                                @if ($checkEdit)
                                    @if(old('state') == $key)
                                        <option value="{{$key}}" selected>{{$value}}</option>
                                    @else
                                        <option value="{{$key}}" {{$projectDraft->state == $key ? 'selected' : ''}}>{{$value}}</option>
                                    @endif
                                @else
                                    <option value="{{$key}}" {{old('state') == $key ? 'selected' : ''}}>{{$value}}</option>
                                @endif
                            @endforeach
                        </select>
                    </span>
                    @else
                        <p class="form-control-static {{($projectDraft->state != $project->state) ? 'is-change-value-summary' : ''}}"
                           data-toggle="tooltip" data-container="body"
                           data-original-title="{{trans('project::view.Approved Value')}}: {{ isset($status[$project->state]) ? $status[$project->state] : ''}}">
                            {{ isset($statusAllForDraf[$projectDraft->state]) ? $statusAllForDraf[$projectDraft->state] : ' '}}</p>
                    @endif
                @else
                    <p class="form-control-static {{($projectDraft->state != $project->state) ? 'is-change-value-summary' : ''}}"
                       data-toggle="tooltip" data-container="body"
                       data-original-title="{{trans('project::view.Approved Value')}}: {{ isset($status[$project->state]) ? $status[$project->state] : ''}}"
                       >{{ isset($statusAllForDraf[$projectDraft->state]) ? $statusAllForDraf[$projectDraft->state] : ' ' }}</p>
                @endif
              </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Company')}} <em>*</em></label>
                <div class="col-sm-8" id="select_company">
                    @if(isset($permissionEdit) && $permissionEdit)
                        <div>
                            <select name="company_id" class="form-control select-search input-basic-info" id="company_id" data-remote-url="{{ URL::route('sales::search.ajax.company') }}">
                                @if ($company)
                                    <option value="{{ $company->id }}" selected>{{ $company->company }}</option>
                                @endif
                            </select>
                            @if($errors->has('company_id'))
                                <label id="company_id-error" class="error" for="company_id">{{$errors->first('company_id')}}</label>
                            @endif
                        </div>
                    @else
                        <p class="form-control-static">{{ $company ? $company->company : ''}}</p>
                    @endif
                </div>
            </div>
            
            <div class="form-group col-sm-6" id="closed_date">
                @if ($project->state == Project::STATUS_PROJECT_CLOSE || $projectDraft->state == Project::STATUS_PROJECT_CLOSE)
                <label class="col-sm-4 control-label">{{trans('project::view.Close Date')}}</label>
                <div class="col-sm-8">
                    @if(isset($permissionEdit) && $permissionEdit)
                        @if($checkEditWorkOrder)
                            <?php
                            $oldCloseAt = old('close_date');
                            if (isset($oldCloseAt)) {
                                $oldCloseAt = true;
                            } else {
                                $oldCloseAt = false;
                            }
                            ?>
                            <input type="text" class="form-control input-basic-info date {{($projectDraft->close_date != $project->close_date) ? 'changed' : ''}} close-date" id="close_date" name="close_date" data-date-format="yyyy-mm-dd" data-provide="datepicker" placeholder="{{trans('project::view.YY-MM-DD')}}" data-old-value="{{$projectDraft->close_date}}" value="{{$oldCloseAt ? old('close_date') : $projectDraft->close_date}}" data-date-today-highlight="true" data-original-title="{{trans('project::view.Approved Value')}}: {{$project->close_date}}" data-toggle="tooltip" data-container="body">
                            <label id="close_date-error-ap_cost" class="error" for="close_date" style="display: none">{{ trans('project::view.Please enter the value Approved production cost in view detail')  }}</label>
                            @if($errors->has('close_date'))
                                <label id="close_date-error" class="error" for="close_date">{{$errors->first('close_date')}}</label>
                            @endif
                        @else
                            <p class="form-control-static {{($projectDraft->close_date != $project->close_date) ? 'is-change-value-summary' : ''}}"  data-toggle="tooltip" data-container="body">{{$projectDraft->close_date}}</p>
                        @endif
                    @else
                        <p class="form-control-static {{($projectDraft->close_date != $project->close_date) ? 'is-change-value-summary' : ''}}" data-original-title="{{trans('project::view.Approved Value')}}: {{$project->close_date}}" data-toggle="tooltip" data-container="body">{{$projectDraft->close_date}}</p>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Salesperson')}} <em>*</em></label>
                <div class="col-sm-8">

                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <div>
                        <select id="sale_id" class="form-control select-search-employee hidden not-approved input-basic-info"
                            name="sale_id[]" multiple="multiple" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                            @if ($allSaleEmployee)
                                @foreach($allSaleEmployee as $item)
                                    <option value="{{ $item['id'] }}" selected>{{ $item['label'] }}</option>
                                @endforeach
                            @endif
                        </select>
                        <input type="hidden" name="sale_id" id="sale-selected" value="{{ old('sale_id') }}">
                        @if($errors->has('sale_id'))
                            <label id="sale_id-error" class="error" for="sale_id">{{$errors->first('sale_id')}}</label>
                        @endif
                    </div>
                @elseif ($allSaleEmployee)
                    <p class="form-control-static">{{ ProjDbHelp::joinItemArray($allSaleEmployee, 'label') }}</p>
                @endif
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Customer')}} <em>*</em></label>
                <div class="col-sm-8" id="select_customer">
                    @if(isset($permissionEdit) && $permissionEdit)
                        <select name="cust_contact_id" class="form-control input-basic-info" id="cust_contact_id">
                            @if ($customers)
                                @foreach($customers as $key => $value)
                                    <option value="{{$value->id}}" {{$project->cust_contact_id == $value->id ? 'selected' : '' }}>{{$value->name}} @if(!empty($value->email)) - {{$value->email}} @endif</option>
                                @endforeach
                            @else
                                <option></option>
                            @endif
                        </select>

                        @if($errors->has('cust_contact_id'))
                            <label id="cust_contact_id-error" class="error" for="cust_contact_id">{{$errors->first('cust_contact_id')}}</label>
                        @endif
                    @else
                        <p class="form-control-static">{{ $customer ? $customer->name : ''  }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label">{{trans('project::view.Customer Email')}}</label>
                <div class="col-sm-8" id="select_cus_email">
                    <div>
                        @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                            <input class="form-control popClass input-basic-info" name="cus_email" id="cus_email" placeholder="{{trans('project::view.Customer Email')}}" value="{{$projectDraft->cus_email}}">
                            <div id="cus_email_error"></div>
                        @else
                            <p class="form-control-static">{{ $project->cus_email ? $project->cus_email : ''}}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label">{{trans('project::view.Customer contact')}}</label>
                <div class="col-sm-8" id="select_cus_contact">
                    <div id="cus_contact_append">
                        @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                            <input class="form-control popClass input-basic-info" name="cus_contact" id="cus_contact" placeholder="{{trans('project::view.Customer contact')}}" value="{{$projectDraft->cus_contact}}">
                            <div id="cus_contact_error"></div>
                        @else
                            <p class="form-control-static">{{ $project->cus_contact ? $project->cus_contact : ''}}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Billable Effort')}} <em>*</em>
                    <i class="fa fa-question-circle" data-toggle="tooltip" title="{{ trans('project::view.billable_effort_desc') }}"></i></label>
                <div class="col-sm-8">
                @if(isset($permissionEdit) && $permissionEdit)
                    @if($checkEditWorkOrder)
                        <div class="input-group {{ $canEditTypeMM ? 'input-group-select' : '' }}">
                            @if($quality)
                                @if($qualityDraftBill)
                                    <input type="text" class="form-control input-basic-info {{($qualityDraftBill->billable_effort != $quality->billable_effort) ? 'changed' : ''}}" id="billable_effort" name="billable_effort" placeholder="{{trans('project::view.Billable Effort')}}" value="{{$qualityDraftBill->billable_effort}}" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->billable_effort}}" data-toggle="tooltip" data-container="body" data-id="{{$qualityDraftBill->id}}">
                                @else
                                    <input type="text" class="form-control input-basic-info" id="billable_effort" name="billable_effort" placeholder="{{trans('project::view.Billable Effort')}}" value="{{$quality->billable_effort}}" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->billable_effort}}" data-toggle="tooltip" data-container="body">
                                @endif
                            @else
                                @if($qualityDraftBill)
                                <input type="text" class="form-control input-basic-info changed" id="billable_effort" name="billable_effort" placeholder="{{trans('project::view.Billable Effort')}}" value="{{$qualityDraftBill->billable_effort}}" data-id="{{$qualityDraftBill->id}}">
                                @else
                                <input type="text" class="form-control input-basic-info" id="billable_effort" name="billable_effort" placeholder="{{trans('project::view.Billable Effort')}}">
                                @endif
                            @endif
                            <span class="input-group-addon" id="basic-addon2">
                                @if($canEditTypeMM)
                                    <select class="select-addon select-same input-basic-info {{ $projectDraft->type_mm != $project->type_mm ? 'changed' : '' }}" same-id="type_mm"
                                            data-original-title="{{ trans('project::view.Approved Value') }}: {{ $labelTypeMM }}" name="type_mm" id="type_mm">
                                        <option value="{{ Project::MD_TYPE }}" {{ $projectDraft->type_mm == Project::MD_TYPE ? 'selected' : '' }}>{{trans('project::view.MD')}}</option>
                                        <option value="{{ Project::MM_TYPE }}" {{ $projectDraft->type_mm == Project::MM_TYPE ? 'selected' : '' }}>{{trans('project::view.MM')}}</option>
                                    </select>
                                @else
                                    {{ $labelTypeMM }}
                                @endif
                            </span>
                        </div>
                        <input type="hidden" id="js-billable-detail-data" name="data_billable_detail"/>

{{--                        <a id="billable-button-detail" class="btn btn-edit button_tracking" style="margin-top: 5px"><span>{{ trans('project::view.View detail') }}</span></a>--}}
                        @if($errors->has('billable_effort'))
                            <label id="billable_effort-error" class="error" for="billable_effort">{{$errors->first('billable_effort')}}</label>
                        @endif
                    @else
                        <div class="input-group  view-only">
                            @if($quality)
                                @if($qualityDraftBill)
                                    <p class="form-control-static {{($qualityDraftBill->billable_effort != $quality->billable_effort) ? 'is-change-value-summary' : ''}}" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->billable_effort}} {{ $labelTypeMM }}" data-toggle="tooltip" data-container="body">{{$qualityDraftBill->billable_effort}} {{ $labelTypeMMDraft }}</p>
                                @else
                                    <p class="form-control-static" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->billable_effort}} {{ $labelTypeMM }}" data-toggle="tooltip" data-container="body">{{$quality->billable_effort}} {{ $labelTypeMMDraft }}</p>
                                @endif
                            @else
                                @if($qualityDraftBill)
                                    <p class="form-control-static">{{$qualityDraftBill->billable_effort}} {{ $labelTypeMMDraft }}</p>
                                @endif
                            @endif
                            <input type="hidden" id="js-billable-detail-data" name="data_billable_detail"/>

{{--                            <a id="billable-button-detail" class="btn btn-edit button_tracking" style="margin-top: 5px"><span>{{ trans('project::view.View detail') }}</span></a>--}}
                        </div>
                    @endif
                @else
                    <div class="input-group view-only">
                        @if($quality)
                            @if($qualityDraftBill)
                                <p class="form-control-static {{($qualityDraftBill->billable_effort != $quality->billable_effort) ? 'is-change-value-summary' : ''}}" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->billable_effort}} {{ $labelTypeMM }}" data-toggle="tooltip" data-container="body">{{$qualityDraftBill->billable_effort}} {{ $labelTypeMMDraft }}</p>
                            @else
                                <p class="form-control-static" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->billable_effort}} {{ $labelTypeMM }}" data-toggle="tooltip" data-container="body">{{$quality->billable_effort}} {{ $labelTypeMMDraft }}</p>
                            @endif
                        @endif
                        <input type="hidden" id="js-billable-detail-data" name="data_billable_detail"/>

                        {{--<a id="billable-button-detail" class="btn btn-edit button_tracking" style="margin-top: 5px"><span>{{ trans('project::view.View detail') }}</span></a>--}}
                    </div>
                @endif
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Plan Effort')}} <em>*</em>
                    <i class="fa fa-question-circle" data-toggle="tooltip" title="{{ trans('project::view.plan_effort_desc') }}"></i></label>
                <div class="col-sm-8">
                @if(isset($permissionEdit) && $permissionEdit)
                    @if($checkEditWorkOrder)
                        <div class="input-group {{ $canEditTypeMM ? 'input-group-select' : '' }}">
                            @if($quality)
                                @if($qualityDraftPlan)
                                    <input type="text" class="form-control input-basic-info {{($qualityDraftPlan->plan_effort != $quality->plan_effort) ? 'changed' : ''}}" id="plan_effort" name="plan_effort" placeholder="{{trans('project::view.Plan Effort')}}" value="{{$qualityDraftPlan->plan_effort}}" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->plan_effort}}" data-toggle="tooltip" data-container="body" data-id="{{$qualityDraftPlan->id}}">
                                @else
                                    <input type="text" class="form-control input-basic-info" id="plan_effort" name="plan_effort" placeholder="{{trans('project::view.Plan Effort')}}" value="{{$quality->plan_effort}}" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->plan_effort}}" data-toggle="tooltip" data-container="body">
                                @endif
                            @else
                                @if($qualityDraftPlan)
                                    <input type="text" class="form-control input-basic-info changed" id="plan_effort" name="plan_effort" placeholder="{{trans('project::view.Plan Effort')}}" value="{{$qualityDraftPlan->plan_effort}}" data-id="{{$qualityDraftPlan->id}}">
                                @else
                                    <input type="text" class="form-control input-basic-info" id="plan_effort" name="plan_effort" placeholder="{{trans('project::view.Plan Effort')}}">
                                @endif
                            @endif
                            <span class="input-group-addon" id="basic-addon2">
                                @if($canEditTypeMM)
                                    <select class="select-addon select-same input-basic-info {{ $projectDraft->type_mm != $project->type_mm ? 'changed' : '' }}" id="type_mm" name="type_mm" same-id="type_mm"
                                            data-original-title="{{ trans('project::view.Approved Value') }}: {{ $labelTypeMM }}">
                                        <option value="{{ Project::MD_TYPE }}" {{ $projectDraft->type_mm == Project::MD_TYPE ? 'selected' : '' }}>{{trans('project::view.MD')}}</option>
                                        <option value="{{ Project::MM_TYPE }}" {{ $projectDraft->type_mm == Project::MM_TYPE ? 'selected' : '' }}>{{trans('project::view.MM')}}</option>
                                    </select>
                                @else
                                    {{ $labelTypeMM }}
                                @endif
                            </span>
                        </div>
                        @if($errors->has('plan_effort'))
                            <label id="plan_effort-error" class="error" for="plan_effort">{{$errors->first('plan_effort')}}</label>
                        @endif
                    @else
                        <div class="input-group">
                            @if($quality)
                                @if($qualityDraftPlan)
                                    <p class="form-control-static {{($qualityDraftPlan->plan_effort != $quality->plan_effort) ? 'is-change-value-summary' : ''}}" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->plan_effort}} {{ $labelTypeMM }}" data-toggle="tooltip" data-container="body">{{$qualityDraftPlan->plan_effort}} {{ $labelTypeMMDraft }}</p>
                                @else
                                    <p class="form-control-static" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->plan_effort}} {{ $labelTypeMM }}" data-toggle="tooltip" data-container="body">{{$quality->plan_effort}} {{ $labelTypeMMDraft }}</p>
                                @endif
                            @else
                                @if($qualityDraftPlan)
                                    <p class="form-control-static">{{$qualityDraftPlan->plan_effort}} {{ $labelTypeMMDraft }}</p>
                                @endif
                            @endif
                        </div>
                    @endif
                @else
                    @if($quality)
                        <div class="input-group">
                            @if($qualityDraftPlan)
                                <p class="form-control-static {{($qualityDraftPlan->plan_effort != $quality->plan_effort) ? 'is-change-value-summary' : ''}}" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->plan_effort}} {{ $labelTypeMM }}" data-toggle="tooltip" data-container="body">{{$qualityDraftPlan->plan_effort}} {{ $labelTypeMMDraft }}</p>
                            @else
                                <p class="form-control-static" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->plan_effort}} {{ $labelTypeMM }}" data-toggle="tooltip" data-container="body">{{$quality->plan_effort}} {{ $labelTypeMMDraft }}</p>
                            @endif
                        </div>
                    @endif
                @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label pd-md-0 required" for="cost_approved_production">{{ trans('project::view.Approved production cost') }} <em>*</em>
                    <i class="fa fa-question-circle" data-toggle="tooltip" title="{{ trans('project::view.approved_production_cost_desc') }}"></i></label>
                <div class="col-sm-8">
                @if(isset($permissionEdit) && $permissionEdit)
                    @if($checkEditWorkOrder)
                        <div class="input-group {{ $canEditTypeMM ? 'input-group-select' : '' }}">
                            @if($quality)
                                @if($qualityDraftProdCost)
                                    <input type="text" id="cost_approved_production" name="cost_approved_production" value="{{$qualityDraftProdCost->cost_approved_production}}"
                                           class="form-control input-basic-info {{($qualityDraftProdCost->cost_approved_production != $quality->cost_approved_production) ? 'changed' : ''}}"
                                           data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->cost_approved_production}}"
                                           data-toggle="tooltip" data-container="body" data-id="{{$qualityDraftProdCost->id}}">
                                @else
                                    <input type="text"  class="form-control input-basic-info" id="cost_approved_production"
                                           name="cost_approved_production" placeholder="{{trans('project::view.Approved production cost')}}"
                                           value="{{$quality->cost_approved_production}}" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->cost_approved_production}}"
                                           data-toggle="tooltip" data-container="body">
                                @endif
                            @else
                                @if($qualityDraftProdCost)
                                    <input type="text" class="form-control input-basic-info changed" id="cost_approved_production" name="cost_approved_production"
                                           placeholder="{{trans('project::view.Approved production cost')}}"
                                           value="{{$qualityDraftProdCost->cost_approved_production}}" data-id="{{$qualityDraftProdCost->id}}">
                                @else
                                    <input type="text" class="form-control input-basic-info" id="cost_approved_production" name="cost_approved_production"
                                           placeholder="{{trans('project::view.Approved production cost')}}">
                                @endif
                            @endif
                            <span class="input-group-addon" id="basic-addon2">
                                @if($canEditTypeMM)
                                    <select class="select-addon select-same input-basic-info {{ $projectDraft->type_mm != $project->type_mm ? 'changed' : '' }}" id="type_mm" name="type_mm" same-id="type_mm"
                                            data-original-title="{{ trans('project::view.Approved Value') }}: {{ $labelTypeMM }}">
                                        <option value="{{ Project::MD_TYPE }}" {{ $projectDraft->type_mm == Project::MD_TYPE ? 'selected' : '' }}>{{trans('project::view.MD')}}</option>
                                        <option value="{{ Project::MM_TYPE }}" {{ $projectDraft->type_mm == Project::MM_TYPE ? 'selected' : '' }}>{{trans('project::view.MM')}}</option>
                                    </select>
                                @else
                                    {{ $labelTypeMM }}
                                @endif
                            </span>
                             <input type="hidden" id="data-project-cost" name="data_project_cost"/>
                        </div>
                        @if (Project::hasPermissionViewCostDetail($currentUser->id, $project, $allTeam))
                        <a id="button-detail" class="btn btn-edit button_tracking" style="margin-top: 5px"><span>{{ trans('project::view.View detail') }}</span></a>
                        @endif
                        @if($errors->has('cost_approved_production'))
                            <label id="cost_approved_production-error" class="error" for="cost_approved_production">{{$errors->first('cost_approved_production')}}</label>
                        @endif
                    @else
                        <div class="input-group  view-only">
                            @if($quality)
                                @if($qualityDraftProdCost)
                                    <p class="form-control-static {{($qualityDraftProdCost->cost_approved_production != $quality->cost_approved_production) ? 'is-change-value-summary' : ''}}"
                                       data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->cost_approved_production}} {{ $labelTypeMM }}"
                                       data-toggle="tooltip" data-container="body">{{ $qualityDraftProdCost->cost_approved_production }} {{ $labelTypeMMDraft }}</p>
                                    <input type="hidden" id="cost_approved_production" name="cost_approved_production"
                                           value="{{$qualityDraftProdCost->cost_approved_production}}">
                                @else
                                    <p class="form-control-static" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->cost_approved_production}} {{ $labelTypeMM }}"
                                       data-toggle="tooltip" data-container="body">{{$quality->cost_approved_production}} {{ $labelTypeMMDraft }}</p>
                                    <input type="hidden" id="cost_approved_production" name="cost_approved_production"
                                           value="{{$quality->cost_approved_production}}">
                                @endif
                            @else
                                @if($qualityDraftProdCost)
                                    <input type="hidden" id="cost_approved_production" name="cost_approved_production"
                                           value="{{$qualityDraftProdCost->cost_approved_production}}">
                                    <p class="form-control-static">{{$qualityDraftProdCost->cost_approved_production}}</p>
                                @endif
                            @endif
                            @if (Project::hasPermissionViewCostDetail($currentUser->id, $project, $allTeam))
                            <a id="button-detail" class="btn btn-edit button_tracking" style="margin-top: 5px"><span>{{ trans('project::view.View detail') }}</span></a>
                            @endif
                        </div>
                        @if ($checkEditWorkOrderReview)
                            <div class="input-group {{ $canEditTypeMM ? 'input-group-select' : '' }} hidden">
                                @if($quality)
                                    @if($qualityDraftProdCost)
                                        <input type="text" id="cost_approved_production" name="cost_approved_production" value="{{$qualityDraftProdCost->cost_approved_production}}"
                                               class="form-control input-basic-info {{($qualityDraftProdCost->cost_approved_production != $quality->cost_approved_production) ? 'changed' : ''}}"
                                               data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->cost_approved_production}}"
                                               data-toggle="tooltip" data-container="body" data-id="{{$qualityDraftProdCost->id}}">
                                    @else
                                        <input type="text"  class="form-control input-basic-info" id="cost_approved_production"
                                               name="cost_approved_production" placeholder="{{trans('project::view.Approved production cost')}}"
                                               value="{{$quality->cost_approved_production}}" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->cost_approved_production}}"
                                               data-toggle="tooltip" data-container="body">
                                    @endif
                                @else
                                    @if($qualityDraftProdCost)
                                        <input type="text" class="form-control input-basic-info changed" id="cost_approved_production" name="cost_approved_production"
                                               placeholder="{{trans('project::view.Approved production cost')}}"
                                               value="{{$qualityDraftProdCost->cost_approved_production}}" data-id="{{$qualityDraftProdCost->id}}">
                                    @else
                                        <input type="text" class="form-control input-basic-info" id="cost_approved_production" name="cost_approved_production"
                                               placeholder="{{trans('project::view.Approved production cost')}}">
                                    @endif
                                @endif
                                <input type="hidden" id="data-project-cost" name="data_project_cost"/>
                                @if (Project::hasPermissionViewCostDetail($currentUser->id, $project, $allTeam))
                                <a id="button-detail" class="btn btn-edit button_tracking" style="margin-top: 5px"><span>{{ trans('project::view.View detail') }}</span></a>
                                @endif
                            </div>
                        @endif
                    @endif
                @else
                    @if($quality)
                        <div class="input-group  view-only">
                            @if($qualityDraftProdCost)
                                <p class="form-control-static {{($qualityDraftProdCost->cost_approved_production != $quality->cost_approved_production) ? 'is-change-value-summary' : ''}}"
                                   data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->cost_approved_production}} {{ $labelTypeMM }}"
                                   data-toggle="tooltip" data-container="body">{{ $qualityDraftProdCost->cost_approved_production }} {{ $labelTypeMMDraft }}</p>
                                 <input type="hidden" id="cost_approved_production" name="cost_approved_production"
                                       value="{{$qualityDraftProdCost->cost_approved_production}}">
                            @else
                                <p class="form-control-static" data-original-title="{{trans('project::view.Approved Value')}}: {{$quality->cost_approved_production}} {{ $labelTypeMM }}"
                                   data-toggle="tooltip" data-container="body">{{ $quality->cost_approved_production }} {{ $labelTypeMMDraft }}</p>
                                <input type="hidden" id="cost_approved_production" name="cost_approved_production"
                                       value="{{$quality->cost_approved_production}}">
                            @endif

                                <input type="hidden" id="data-project-cost" name="data_project_cost"/>
                                @if (Project::hasPermissionViewCostDetail($currentUser->id, $project, $allTeam))
                                <a id="button-detail" class="btn btn-edit button_tracking" style="margin-top: 5px"><span>{{ trans('project::view.View detail') }}</span></a>
                                @endif
                        </div>
                    @endif
                @endif
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Project kind')}} <em>*</em>
                    <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{!! trans('project::view.Project kind tooltip') !!}"></i></label>
                <div class="col-sm-8" id="select_kind">
                    @if(isset($permissionEdit) && $permissionEdit)
                        @if($checkEditWorkOrder || (isset($checkEditWorkOrderReview) && $checkEditWorkOrderReview))
                            <div>
                                <select name="kind_id" class="form-control input-basic-info {{($projectDraft->kind_id != $project->kind_id) ? 'changed' : ''}}" id="kind_id" data-original-title="{{trans('project::view.Approved Value')}}: {{ !is_null($project->kind_id) ? (isset($lblProjectKind[$project->kind_id]) ? $lblProjectKind[$project->kind_id] : '')  : ''}}">
                                    <option value=""></option>
                                    @foreach($lblProjectKind as $key => $value)
                                        @if(old('kind_id') == $key)
                                            <option value="{{$key}}" selected>{{$value}}</option>
                                        @else
                                            <option value="{{$key}}" {{$projectDraft->kind_id == $key ? 'selected' : '' }}>{{$value}}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @if($errors->has('kind_id'))
                                    <label id="kind_id-error" class="error" for="kind_id">{{$errors->first('kind_id')}}</label>
                                @endif
                            </div>
                        @else
                            <p class="form-control-static {{($projectDraft->kind_id != $project->kind_id) ? 'is-change-value-summary' : ''}}" data-toggle="tooltip" data-container="body" data-original-title="{{trans('project::view.Approved Value')}}: {{isset($lblProjectKind[$project->kind_id]) ? $lblProjectKind[$project->kind_id] : ''}}">{{isset($lblProjectKind[$projectDraft->kind_id]) ? $lblProjectKind[$projectDraft->kind_id] : ''}}</p>
                        @endif
                    @else
                        <p class="form-control-static {{($projectDraft->kind_id != $project->kind_id) ? 'is-change-value-summary' : ''}}" data-toggle="tooltip" data-container="body" data-original-title="{{trans('project::view.Approved Value')}}: {{isset($lblProjectKind[$project->kind_id]) ? $lblProjectKind[$project->kind_id] : ''}}">{{isset($lblProjectKind[$projectDraft->kind_id]) ? $lblProjectKind[$projectDraft->kind_id] : ''}}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label for="start_at" id="lbl_start_at" class="col-sm-4 control-label required">{{trans('project::view.Start Date')}} <em>*</em></label>
                <div class="col-sm-8">
                @if(isset($permissionEdit) && $permissionEdit)
                    @if($checkEditWorkOrder)
                    <?php
                        $oldStartAt = old('start_at');
                        if (isset($oldStartAt)) {
                            $oldStartAt = true;
                        } else {
                            $oldStartAt = false;
                        }
                    ?>
                    <input type="text" class="form-control input-basic-info date count_day_project_work {{($projectDraft->start_at != $project->start_at) ? 'changed' : ''}} start-date" id="start_at" name="start_at" data-date-format="yyyy-mm-dd" data-provide="datepicker" placeholder="{{trans('project::view.YY-MM-DD')}}" data-old-value="{{Carbon::parse($projectDraft->start_at)->format('Y-m')}}" value="{{$oldStartAt ? old('start_at') : Carbon::parse($projectDraft->start_at)->format('Y-m-d')}}" data-date-today-highlight="true" data-original-title="{{trans('project::view.Approved Value')}}: {{Carbon::parse($project->start_at)->format('Y-m-d')}}" data-toggle="tooltip" data-container="body">
                    <label id="start_at-error-ap_cost" class="error" for="end_at" style="display: none">{{ trans('project::view.Please enter the value Approved production cost in view detail')  }}</label>
                    @if($errors->has('start_at'))
                    <label id="start_at-error" class="error" for="start_at">{{$errors->first('start_at')}}</label>
                    @endif
                    @else
                    <p class="form-control-static {{($projectDraft->start_at != $project->start_at) ? 'is-change-value-summary' : ''}}" data-original-title="{{trans('project::view.Approved Value')}}: {{Carbon::parse($project->start_at)->format('Y-m-d')}}" data-toggle="tooltip" data-container="body">{{Carbon::parse($projectDraft->start_at)->format('Y-m-d')}}</p>
                    @endif
                @else
                    <p class="form-control-static {{($projectDraft->start_at != $project->start_at) ? 'is-change-value-summary' : ''}}" data-original-title="{{trans('project::view.Approved Value')}}: {{Carbon::parse($project->start_at)->format('Y-m-d')}}" data-toggle="tooltip" data-container="body">{{Carbon::parse($projectDraft->start_at)->format('Y-m-d')}}</p>
                @endif
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label for="end_at" id="lbl_end_at" class="col-sm-4 control-label required">{{trans('project::view.End Date')}} <em>*</em></label>
                <div class="col-sm-8">
                @if(isset($permissionEdit) && $permissionEdit)
                    @if($checkEditWorkOrder)
                    <?php
                        $oldEndAt = old('end_at');
                        if (isset($oldEndAt)) {
                            $oldEndAt = true;
                        } else {
                            $oldEndAt = false;
                        }
                    ?>
                    <input type="text" class="form-control input-basic-info date count_day_project_work {{($projectDraft->end_at != $project->end_at) ? 'changed' : ''}} end-date" id="end_at" name="end_at" data-date-format="yyyy-mm-dd" data-provide="datepicker" placeholder="{{trans('project::view.YY-MM-DD')}}" data-old-value="{{Carbon::parse($projectDraft->end_at)->format('Y-m')}}" value="{{$oldEndAt ? old('end_at') : Carbon::parse($projectDraft->end_at)->format('Y-m-d')}}" data-date-today-highlight="true" data-original-title="{{trans('project::view.Approved Value')}}: {{Carbon::parse($project->end_at)->format('Y-m-d')}}" data-toggle="tooltip" data-container="body">
                    <label id="end_at-error-ap_cost" class="error" for="end_at" style="display: none">{{ trans('project::view.Please enter the value Approved production cost in view detail')  }}</label>
                    @if($errors->has('end_at'))
                    <label id="end_at-error" class="error" for="end_at">{{$errors->first('end_at')}}</label>
                    @endif
                    @else
                    <p class="form-control-static {{($projectDraft->end_at != $project->end_at) ? 'is-change-value-summary' : ''}}" data-original-title="{{trans('project::view.Approved Value')}}: {{Carbon::parse($project->end_at)->format('Y-m-d')}}" data-toggle="tooltip" data-container="body">{{Carbon::parse($projectDraft->end_at)->format('Y-m-d')}}</p>
                    @endif
                @else
                    <p class="form-control-static {{($projectDraft->end_at != $project->end_at) ? 'is-change-value-summary' : ''}}" data-original-title="{{trans('project::view.Approved Value')}}: {{Carbon::parse($project->end_at)->format('Y-m-d')}}" data-toggle="tooltip" data-container="body">{{Carbon::parse($projectDraft->end_at)->format('Y-m-d')}}</p>
                @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label for="start_at" class="col-sm-4 control-label">{{trans('project::view.Duration')}}</label>
                <div class="col-sm-8">
                    <p id="project_date" class="count_day_project form-control-static" name="project_date" for="end_at">{{ Project::getDayOfProjectWork($projectDraft->start_at, $projectDraft->end_at) }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label for="project_member" class="col-sm-4 control-label">{{trans('project::view.Team size')}}</label>
                <div class="col-sm-8">
                    <p id="project_member" class="count_member_project form-control-static" name="project_member" for="project_member">{{ ($countMemberProj > 1) ? $countMemberProj : '1' }}</p>
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label for="project_member_current" class="col-sm-4 control-label">{{trans('project::view.Team size - current')}}</label>
                <div class="col-sm-8">
                    <p id="project_member_current" class="count_member_project form-control-static" name="project_member_current" for="project_member_current">{{ ($countMemberProjCurrent > 0) ? $countMemberProjCurrent : '0' }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            @if ($programsOption)
            <div class="col-sm-6 form-group form-group-select2" id="select-prog_langs">
                <label for="prog_langs" class="col-sm-4 control-label margin-top--10 required">{{trans('project::view.Programming language')}}</label>
                <div class="col-sm-8 fg-valid-custom">
                    <div class="dropdown team-dropdown prog_langs-dropdown">
                        @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                            <select id="prog_langs" class="form-control select-search hidden multiselect2 input-basic-info" name="prog_langs[]" multiple="multiple">
                            @foreach($programsOption as $key => $value)
                                    <option value="{{ $key }}"{{ key_exists($key, $projectProgramLangs) ? ' selected' : '' }}
                                            >{{ $value }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('prog_langs'))
                            <label id="prog_langs-error" class="error" for="prog_langs">{{ $errors->first('prog_langs') }}</label>
                            @endif
                        @else
                            <p class="form-control-static">{{ ProjectProgramLang::getNameProgramLangOfProject(null, $projectProgramLangs) }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="col-sm-6 form-group">
                <label for="is_important" class="col-sm-4 control-label margin-top--10">{{trans('project::view.Is important')}}</label>
                <div class="col-sm-8 fg-valid-custom">
                    <div class="dropdown team-dropdown prog_langs-dropdown">
                        @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                            <div class="checkbox">
                                <input type="checkbox" class="margin-left-0 input-basic-info {{($projectDraft->is_important != $project->is_important) ? 'changed' : ''}}" id="is_important" name="is_important" value="{{ Project::IS_IMPORTANT }}" {{ !empty($projectDraft->is_important) ? 'checked' : '' }}/>
                            </div>
                        @else
                            {{ !empty($project->is_important) ? trans('project::view.Yes') : trans('project::view.No') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="business" class="col-sm-4 control-label margin-top--10 required">{{trans('project::view.Business domain')}} <em>*</em></label>
                <div class="col-sm-8 fg-valid-custom" id="select_business">
                    <div>
                        @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                            <select class="form-control popClass input-basic-info" name="business" id="business">
                                <option value=""></option>
                                @foreach($lblBusinessCate as $items => $item)
                                    <option value="{{$items}}" @if($project->business_id == $items) selected @endif>{{ $item }}</option>
                                @endforeach
                            </select>
                        @else
                            <p class="form-control-static">{{ $project->business_id ? ProjectBusiness::getBusinessById($project->business_id) : ''}}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-sm-6 form-group">
                <label for="sub_sector" class="col-sm-4 control-label margin-top--10 required">{{trans('project::view.Sub sector')}} <em>*</em></label>
                <div class="col-sm-8 fg-valid-custom" id="select_sub_sector">
                    <div>
                        @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                            <select class="form-control popClass input-basic-info" name="sub_sector" id="sub_sector">
                                <option value=""></option>
                                @foreach($lblSectorCate as $items => $item)
                                    <option value="{{$items}}" @if($project->sub_sector == $items) selected @endif>{{ $item }}</option>
                                @endforeach
                            </select>
                        @else
                            <p class="form-control-static">{{ $project->sub_sector ? ProjectSector::getSubSectorById($project->sub_sector) : ''}}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@if (Project::hasPermissionViewCostDetail($currentUser->id, $project, $allTeam))
@include('project::tab_content.includes.modal.modal-operation-project')
@endif
@section('script')
    @parent
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.6.0/bootstrap-tagsinput.min.js"></script>
    <script>
        var globalCheckEditWorkOrder = !!{{isset($checkEditWorkOrder) && $checkEditWorkOrder ? 1 : 0 }};
        var globalCheckEditWorkOrderReview = !!{{isset($checkEditWorkOrderReview) && $checkEditWorkOrderReview ? 1 : 0 }};
        var globalPermissionEdit = !!{{isset($permissionEdit) && $permissionEdit ? 1 : 0 }};
        var globalIsAllowUpdateApproveCost = !!{{isset($isAllowUpdateApproveCostPrice) && $isAllowUpdateApproveCostPrice ? 1 : 0 }};
        var globalIsViewBtnApproveCost = !!{{isset($jsIsViewBtnApproveCost) && $jsIsViewBtnApproveCost ? 1 : 0 }};
        var globalOwnerTeamIds = JSON.parse('{!! json_encode($ownerTeamsCurrentEmployee) !!}');
        var hasPermissionViewCostPriceDetail = {{ $hasPermissionViewCostPriceDetail ? 'true' : 'false' }};
        var globalTeamIdOfEmp = JSON.parse('{!! json_encode($arrTeamIdOfEmp) !!}');
        var globalIdLeaderAndSubProject = JSON.parse('{!! json_encode($arrIdLeaderAndSubProject) !!}');
        var empId = {{ $empId }};
        var hasPermissionViewCostDetail = {{ $hasPermissionViewCostDetail ? 'true' : 'false' }};
        var urlSavePurchaseOrderId = '{{ $urlSavePurchaseOrderId }}';
        var urlSavePurchaseOrderIdToCRM = '{{ $urlSavePurchaseOrderIdToCRM }}';
        var urlGetPurchaseOrderId = '{{ $urlGetPurchaseOrderId }}';
        var showByTeam = '{{ $showByTeam }}';
        var urlSaveProjCate = '{{ route('project::project.cate.save') }}';
        var urlUpdateCloseDate = '{{ route('project::project.update.close') }}';
        var urlSaveContactCustomer = '{{ route('project::project.contact.save') }}';
        {{--var showByTeam = '{{ $showByTeam }}';--}}
        const TYPE_MM = {{Project::MM_TYPE}};
        const TYPE_MD = {{Project::MD_TYPE}};

        $(document).ready(function () {
            $('.js-po-id-taginput').tagsinput();
            var projectId = '{{ $project->id }}';
            var projectName = '{{ $project->name }}';
            var pmNameFull = $('#manager_id :selected').text();
            var pmName = '';
            pmNameItem = pmNameFull.split(" (");
            if (pmNameItem[0]) {
                pmName = pmNameItem[0];
            }
            $('.js-po-id-taginput').on('change', function() {
                var purchaseOrdId = $(this).val();
                save_item_meta(purchaseOrdId);

                //save in API
                let arrPurchasIds = purchaseOrdId.split(',');
                save_to_API_CRM(arrPurchasIds);
            });

            function save_item_meta(purchaseOrdId) {
                $.ajax({
                    url: urlSavePurchaseOrderId,
                    method: "POST",
                    data: {
                        projectId: projectId,
                        purchaseOrdId: purchaseOrdId,
                    },
                    success: function(data) {
                    }
                });
                return false; // prevent from submit
            }

            function save_to_API_CRM(arrPurchasIds) {
                $.ajax({
                    url: urlSavePurchaseOrderIdToCRM,
                    method: "POST",
                    data: {
                        projectId: projectId,
                        projectName: projectName,
                        arrPurchasIds: arrPurchasIds,
                        pmName: pmName,
                    },
                    success: function(data) {
                    }
                });
                return false;
            }
        });
    </script>
    @include('project::tab_content.includes.js.js-summary')
    @include('project::tab_content.includes.js-model-operation')
    <script>
        $(document).ready(function () {
            var modal = $('#modal-purchase');
            var btn = $('#button-purchase-order');
            var span = $('#close-modal');
            var purchaseOrdId = [];

            btn.click(async function () {
                var newId = $('#purchase_order_id').val();
                if (newId) {
                    if (purchaseOrdId !== newId) {
                        let newIds = newId.split(',');
                        purchaseOrdId = newIds;
                        var html = '<div class="loader"></div>'
                        $('#list_purchase').html(html);
                        get_item_meta(purchaseOrdId);
                    }
                } else {
                    let html = '<td colspan="13" class="text-center"><h2 class="no-result-grid">Chưa nhập Purchase Order Id</h2></td>';
                    $('#list_purchase').html(html);
                }
                modal.show();
            });

            span.click(function () {
                modal.hide();
            });

            function create_warning_ele() {
                return '<span data-toggle="tooltip" data-placement="top" title="Hợp đồng có partner nhưng chưa được nhập số tiền hoa hồng phải trả">' +
                    '<i class="fa fa-exclamation-triangle padding-left-20" style="font-size:20px;color:yellow; text-shadow: -2px 0 #000, 0 2px #000, 2px 0 #000, 0 -2px #000;"></i></span>'
            }

            $(window).on('click', function (e) {
                if ($(e.target).is('#modal-purchase')) {
                    modal.hide();
                }
            });

            function get_item_meta(purchaseOrdId) {
                $.ajax({
                    url: urlGetPurchaseOrderId,
                    method: "POST",
                    data: {
                        purchaseOrdId: purchaseOrdId,
                    },
                    success: function(data) {
                        let html = '';
                        $.each(data[0], function (index, value) {
                            if (value.data != undefined) {
                                if (value.data.length == 0 && value.po_title == null) {
                                    html += `<tr><td colspan="13" class="text-center" style="background: #cc651d; color: #fff;">${index} : Purchase Id này không tồn tại</td></tr>`;
                                } else {
                                    if (value.data.length == 0) {
                                        html += `<tr><td colspan="13" class="text-center" style="background: #58c1ef; color: #fff;">${value.po_title}</td></tr>
                                                <tr><td colspan="13" class="text-center"><h4 class="no-result-grid">Không có dữ liệu</h4></td></tr>`;
                                    } else {
                                        html += `<tr><td colspan="13" class="text-center" style="background: #58c1ef; color: #fff;">${value.po_title}</td></tr>`;
                                        $.each(value.data, function (key, val) {
                                            html += `<tr>
                                                        <td>${val.person}</td>
                                                        <td>${val.category}</td>
                                                        <td>${val.roles === null ? "" : val.roles}</td>
                                                        <td>${val.level === null ? "" : val.level}</td>
                                                        <td>${val.order_type}</td>
                                                        <td>${parseInt(val.unit_price).toLocaleString()} ${val.is_warning ? create_warning_ele() : ''}</td>
                                                        <td>${val.product_qty}</td>
                                                        <td>${val.unit}</td>
                                                        <td>${val.currency_id}</td>
                                                    </tr>`;
                                        });
                                    }
                                }
                            } else {
                                html += `<tr><td colspan="13" class="text-center" style="background: #cc651d; color: #fff;">${index} : Purchase Id này không tồn tại</td></tr>`;
                            }
                        });
                        $('#list_purchase').html(html);
                    },
                    error: function () {
                        let html = '<td colspan="13" class="text-center"><h2 class="no-result-grid">Không lấy được dữ liệu</h2></td>';
                        $('#list_purchase').html(html);
                    },
                });
                return false; // prevent from submit
            }
        });

        var urlGetDayOfProject = '{{ route('project::project.get-day-project') }}';
        $('.count_day_project_work').change(function () {
            var start_date = $('#start_at').val();
            var end_date = $('#end_at').val();
            if (start_date && end_date) {
                $.ajax({
                    url: urlGetDayOfProject,
                    method: "POST",
                    data: {
                        start_date: start_date,
                        end_date: end_date,
                    },
                    success: function(data) {
                        $('#project_date').val(data);
                    }
                });
            }
        });

        $('#cus_email').change(function () {
            var cusEmail = $(this).val();
            var cusContact = cusEmail.split('@')[0];
            var cusContactCurrent = $('#cus_contact').val();
            if (!cusContactCurrent) {
                $('#cus_contact').val(cusContact);
            }
            $.ajax({
                url: urlSaveContactCustomer,
                method: "POST",
                data: {
                    projectId: projectId,
                    cusContact: cusContact
                },
                success: function(data) {
                }
            });
        });

        $(document).ready(function () {
            $('body').on('change', '.popClass', function (event) {
               var category = $('#category').val();
               var classification = $('#classification').val();
               var market = $('#project_market').val();
               var business = $('#business').val();
               var subSector = $('#sub_sector').val();
               var cusEmail = $('#cus_email').val();
               var cusContact = $('#cus_contact').val();

               $.ajax({
                   url: urlSaveProjCate,
                   method: "POST",
                   data: {
                       projectId: projectId,
                       category: category,
                       classification: classification,
                       market: market,
                       business: business,
                       subSector: subSector,
                       cusEmail: cusEmail,
                       cusContact: cusContact
                   },
                   success: function(data) {
                       var html = '';
                       if (data.message_error) {
                           html += `<div class="text-danger">${data.message_error}</div>`;
                           $('#cus_email_error').html(html);
                       } else {
                           $('#cus_email_error').html(html);
                       }
                   }
               });
           });
        });

        var statusProjDraf = '{{$projectDraft->state}}';
        var statusCancel = '{{ Project::STATUS_PROJECT_CLOSE }}'
        if (statusProjDraf != statusCancel) {
            var html = '';
            $('#closed_date').html(html);
        }

        $(document).ready(function () {
            $('#state').on('change', function() {
                var statusProj = $(this).val();

                var html = '';
                if (statusProj == statusCancel) {
                    html += '<label for="close_date" class="col-sm-4 control-label">Close date</label>\n' +
                        '<div class="col-sm-8">\n' +
                        '<input type="text" class="form-control date input-basic-info" id="close_date" name="close_date" data-date-format="yyyy-mm-dd" data-provide="datepicker" >\n' +
                        '</div>'
                    $('#closed_date').html(html);
                } else {
                    $('#closed_date').html(html);
                }
            })
        });
    </script>
@endsection
