<?php
    use Rikkei\Team\View\Permission;
    use Rikkei\Project\Model\Project;
    $idCurrent = Permission::getInstance()->getEmployee()->id;
?>
<style>
    .tooltip > .tooltip-inner {
        text-align: left;
        padding: 10px;
        max-width: 500px;
    }
</style>
<div class="box box-primary">
    <div class="box-header with-border">{{trans('project::view.Basic info')}}</div>
    <form class="form-horizontal frm-create-project" id="create-project-form" method="post" action="{{route('project::project.create')}}" enctype="multipart/form-data" autocomplete="off">
    {!! csrf_field() !!}
    <div class="box-body">
        <div class="row">
            <div class="form-group col-sm-6"></div>
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Purchase Order ID')}}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="purchase_order_id" name="purchase_order_id" placeholder="{{trans('project::view.Purchase Order ID')}}" value="">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 alert-message-error hidden">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Error!</strong><span class="str-error-message"></span>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Project Name')}} <em>*</em></label>
                <div class="col-sm-8">
                <?php
                    $oldName = old('name');
                    if (isset($oldName)) {
                        $oldName = true;
                    } else {
                        $oldName = false;
                    }
                ?>
                    <input type="text" class="form-control" id="name" name="name" placeholder="{{trans('project::view.Project name')}}" value="{{$oldName ? old('name') : ''}}">
                    @if($errors->has('name'))
                    <label id="name-error" class="error" for="name">{{$errors->first('name')}}</label>
                    @endif
                </div>
            </div>

            <div class="col-sm-6 form-group">
                <label for="select_category" class="col-sm-4 control-label margin-top--10 required">{{trans('project::view.Project category')}} <em>*</em></label>
                <div class="col-sm-8" id="select_category">
                    <select class="form-control" name="category" id="category">
                        <option value=""></option>
                        @foreach($lblProjectCate as $items => $item)
                            <option value="{{$items}}">{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6" id="select-team">
                <label for="team_id" class="col-sm-4 control-label required">{{trans('project::view.Group')}} <em>*</em></label>
                <div class="col-sm-8">
                    <div class="dropdown team-dropdown">
                        <span>
                        <select id="team_id" class="form-control select-search hidden team-dev-tree" name="team_id[]" multiple="multiple"></select>
                        @if($errors->has('team_id'))
                            <label id="team_id-error" class="error" for="team_id">{{$errors->first('team_id')}}</label>
                        @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Group Leader')}} <em>*</em></label>
                <div class="col-sm-8 div-leader-id">
                    @if(!empty(old('leader_id')))
                    <?php $oldInput = session()->getOldInput(); ?>
                    <input class="form-control" type="text"  readonly="readonly" name="except[leader_id]"
                           value="{{ isset($oldInput['except']['leader_id']) ? $oldInput['except']['leader_id'] : '' }}">
                    <input type="hidden" name="leader_id" value="{{old('leader_id')}}">
                    @else
                    <input class="form-control" type="text"  readonly="readonly" name="except[leader_id]">
                    <input type="hidden" name="except[leader_id]">
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
              <label for="manager_id" class="col-sm-4 control-label required">{{trans('project::view.Project Manager')}} <em>*</em></label>
              <div class="col-sm-8">
                <select name="manager_id" class="form-control" id="manager_id">
                        @foreach($employees as $employee)
                            @if (old('manager_id') == $employee->id)
                            <option value="{{$employee->id}}" selected>{{$employee->name}} ({{preg_replace('/@.*/', '', $employee->email)}})</option>
                            @elseif($idCurrent == $employee->id)
                            <option value="{{$employee->id}}" selected>{{$employee->name}} ({{preg_replace('/@.*/', '', $employee->email)}})</option>
                            @else
                            <option value="{{$employee->id}}">{{$employee->name}} ({{preg_replace('/@.*/', '', $employee->email)}})</option>
                            @endif
                        @endforeach
                    </select>
              </div>
            </div>

            <div class="col-sm-6 form-group">
                <label for="classification" class="col-sm-4 control-label margin-top--10 required">{{trans('project::view.Classification')}} <em>*</em></label>
                <div class="col-sm-8" id="select_classification">
                    <select class="form-control" name="classification" id="classification">
                        <option value=""></option>
                        @foreach($lblClassCate as $items => $item)
                            <option value="{{$items}}">{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Contract Type')}} <em>*</em></label>
                <div class="col-sm-8">
                    <select name="type" class="form-control" id="type">
                    @foreach($labelTypeProject as $key => $value)
                        <option value="{{$key}}" {{old('state') == $key ? 'selected' : '' }}>{{$value}}</option>
                    @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Project Status')}} <em>*</em></label>
                <div class="col-sm-8">
                    <select name="state" class="form-control" id="state">
                        @foreach($status as $key => $value)
                        <option value="{{$key}}" {{old('state') == $key ? 'selected' : '' }}>{{$value}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Company')}} <em>*</em></label>
                <div class="col-sm-8" id="select_company">
                    <div>
                        <select name="company_id" class="form-control select-search" id="company_id" data-remote-url="{{ URL::route('sales::search.ajax.company') }}">
                        </select>
                        @if($errors->has('company_id'))
                            <label id="company_id-error" class="error" for="company_id">{{$errors->first('company_id')}}</label>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group col-sm-6" id="closed_date">
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6" id="select-sales">
                <label class="col-sm-4 control-label required">{{trans('project::view.Salesperson')}} <em>*</em></label>
                <div class="col-sm-8">
                    <div>
                        <select id="sale_id" class="form-control select-search hidden"
                            name="sale_id[]" multiple="multiple">
                        <select id="sale_id" class="form-control select-search hidden"
                            name="sale_id[]" multiple="multiple" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                        </select>
                        <input type="hidden" name="sale_id" id="sale-selected" value="{{ old('sale_id') }}">
                        @if($errors->has('sale_id'))
                            <label id="sale_id-error" class="error" for="sale_id">{{$errors->first('sale_id')}}</label>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Customer')}} <em>*</em></label>
                <div class="col-sm-8" id="select_customer">
                    <select name="cust_contact_id" class="select-search" id="cust_contact_id" data-remote-url="{{ URL::route('sales::search.ajax.searchCustomerAjax', ['id' => null]) }}">
                    </select>
                    @if($errors->has('cust_contact_id'))
                        <label id="cust_contact_id-error" class="error" for="cust_contact_id">{{$errors->first('cust_contact_id')}}</label>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label">{{trans('project::view.Customer Email')}}</label>
                <div class="col-sm-8" id="select_cus_email">
                    <input class="form-control popClass input-basic-info" name="cus_email" id="cus_email" placeholder="{{trans('project::view.Customer Email')}}" value="">
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label">{{trans('project::view.Customer contact')}}</label>
                <div class="col-sm-8" id="select_cus_contact">
                    <input class="form-control popClass input-basic-info" name="cus_contact" id="cus_contact" placeholder="{{trans('project::view.Customer contact')}}" value="">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6" id="input-billable-effort">
                <label class="col-sm-4 control-label required">{{trans('project::view.Billable Effort')}} <em>*</em>
                    <i class="fa fa-question-circle" data-toggle="tooltip" title="{{ trans('project::view.billable_effort_desc') }}"></i></label>
                <div class="col-sm-8">
                    <div class="input-group input-group-select">
                        <?php
                        $oldBillable = old('billable_effort');
                        if (isset($oldBillable)) {
                            $oldBillable = true;
                        } else {
                            $oldBillable = false;
                        }
                        ?>
                        <input type="text" class="form-control" id="billable_effort" name="billable_effort" placeholder="{{trans('project::view.Billable Effort')}}" value="{{$oldBillable ? old('billable_effort') : ''}}">
                        <span class="input-group-addon" id="basic-addon2" >
                            <select class="select-addon select-same" same-id="type_mm" id="billable_effort_select" name="billable_effort_select">
                                <option value="">{{trans('project::view.Unit')}}</option>
                                <option value="{{ Project::MD_TYPE }}">{{trans('project::view.MD')}}</option>
                                <option value="{{ Project::MM_TYPE }}">{{trans('project::view.MM')}}</option>
                            </select>
                        </span>
                    </div>
                    <input type="hidden" id="js-billable-detail-data" name="data_billable_detail"/>
                @if($errors->has('billable_effort'))
                    <label id="billable_effort-error" class="error" for="billable_effort">{{$errors->first('billable_effort')}}</label>
                    @endif
                    <label id="billable_effort-error-select" class="error" for="billable_effort_select">{{$errors->first('billable_effort')}}</label>
                </div>
            </div>

            <div class="form-group col-sm-6" id="input-plan-effort">
                <label class="col-sm-4 control-label required">{{trans('project::view.Plan Effort')}} <em>*</em>
                    <i class="fa fa-question-circle" data-toggle="tooltip" title="{{ trans('project::view.plan_effort_desc') }}"></i></label>
                <div class="col-sm-8">
                    <div class="input-group input-group-select">
                        <?php
                        $oldPlan = old('plan_effort');
                        if (isset($oldPlan)) {
                            $oldPlan = true;
                        } else {
                            $oldPlan = false;
                        }
                        ?>
                        <input type="text" class="form-control" id="plan_effort" name="plan_effort" placeholder="{{trans('project::view.Plan Effort')}}" value="{{$oldPlan ? old('plan_effort') : ''}}">
                        <span class="input-group-addon" id="basic-addon2">
                            <select class="select-addon select-same" same-id="type_mm" id="plan_effort_select" name="plan_effort_select">
                                <option value="">{{trans('project::view.Unit')}}</option>
                                <option value="{{ Project::MD_TYPE }}">{{trans('project::view.MD')}}</option>
                                <option value="{{ Project::MM_TYPE }}">{{trans('project::view.MM')}}</option>
                            </select>
                        </span>
                    </div>
                    @if($errors->has('plan_effort'))
                    <label id="plan_effort-error" class="error" for="plan_effort">{{$errors->first('plan_effort')}}</label>
                    @endif
                    <label id="plan_effort_select-error" class="error" for="plan_effort_select">{{ $errors->first('plan_effort_select') }}</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6" id="input-cost-approved-production">
                <label class="col-sm-4 control-label pd-md-0 required">{{ trans('project::view.Approved production cost') }} <em>*</em>
                    <i class="fa fa-question-circle" data-toggle="tooltip" title="{{ trans('project::view.approved_production_cost_desc') }}"></i></label>
                <div class="col-sm-8">
                    <div class="input-group input-group-select">
                        <?php
                        $oldApprovedCost = old('cost_approved_production');
                        ?>
                        <input type="text" class="form-control" id="cost_approved_production" name="cost_approved_production" placeholder="{{trans('project::view.Approved production cost')}}" value="{{ $oldApprovedCost ? $oldApprovedCost : '' }}">
                        <span id="basic-addon2" class="input-group-addon">
                            <select class="select-addon select-same" same-id="type_mm" id="cost_approved_production_select" name="cost_approved_production_select">
                                <option value="">{{trans('project::view.Unit')}}</option>
                                <option value="{{ Project::MD_TYPE }}">{{trans('project::view.MD')}}</option>
                                <option value="{{ Project::MM_TYPE }}">{{trans('project::view.MM')}}</option>
                            </select>
                        </span>
                        <input type="hidden" id="data-project-cost" name="data_project_cost"/>

                    </div>
                    @if($errors->has('cost_approved_production'))
                    <label id="cost_approved_production-error" class="error" for="cost_approved_production">{{ $errors->first('cost_approved_production') }}</label>
                    @endif
                    <label id="cost_approved_production_select-error" class="error" for="cost_approved_production_select"></label>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{trans('project::view.Project kind')}} <em>*</em>
                    <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{!! trans('project::view.Project kind tooltip') !!}"></i></label>
                <div class="col-sm-8" id="select_kind">
                    <select name="kind_id" class="form-control" id="kind_id">
                        <option value=""></option>
                        @foreach($lblProjectKind as $key => $value)
                            <option value="{{$key}}" {{old('kind_id') == $key ? 'selected' : '' }}>{{$value}}</option>
                        @endforeach
                    </select>
                    @if($errors->has('kind_id'))
                        <label id="kind_id-error" class="error" for="kind_id">{{$errors->first('kind_id')}}</label>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label for="start_at" class="col-sm-4 control-label required">{{trans('project::view.Start Date')}} <em>*</em></label>
                <div class="col-sm-8">
                <?php
                    $oldStartAt = old('start_at');
                    if (isset($oldStartAt)) {
                        $oldStartAt = true;
                    } else {
                        $oldStartAt = false;
                    }
                ?>
                    <input type="text" class="form-control count_day_project_work date" id="start_at" name="start_at" data-date-format="yyyy-mm-dd" data-date-week-start="1" data-provide="datepicker" placeholder="{{trans('project::view.YY-MM-DD')}}" value="{{$oldStartAt ? old('start_at') : ''}}" data-date-today-highlight="true">
                    @if($errors->has('start_at'))
                    <label id="start_at-error" class="error" for="start_at">{{$errors->first('start_at')}}</label>
                    @endif
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label for="end_at" class="col-sm-4 control-label required">{{trans('project::view.End Date')}} <em>*</em></label>
                <div class="col-sm-8">
                <?php
                    $oldEndAt = old('end_at');
                    if (isset($oldEndAt)) {
                        $oldEndAt = true;
                    } else {
                        $oldEndAt = false;
                    }
                ?>
                    <input type="text" class="form-control count_day_project_work date" id="end_at" name="end_at" data-date-format="yyyy-mm-dd" data-date-week-start="1" data-provide="datepicker" placeholder="{{trans('project::view.YY-MM-DD')}}" value="{{$oldEndAt ? old('end_at') : ''}}" data-date-today-highlight="true">
                    @if($errors->has('end_at'))
                    <label id="end_at-error" class="error" for="end_at">{{$errors->first('end_at')}}</label>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label for="start_at" class="col-sm-4 control-label">{{trans('project::view.Duration')}}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control count_day_project" id="project_date" name="project_date" disabled>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label for="start_at" class="col-sm-4 control-label">{{trans('project::view.Team size')}}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control count_member_project" id="project_member" name="project_member" disabled value="1">
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label for="start_at" class="col-sm-4 control-label">{{trans('project::view.Team size - current')}}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control count_member_project" id="project_member_current" name="project_member_current" value="1" disabled>
                </div>
            </div>
        </div>

        <div class="row">
            @if ($programsOption)
            <div class="col-sm-6 form-group" id="select-prog_langs">
                <label for="prog_langs" class="col-sm-4 control-label margin-top--10 required">{{trans('project::view.Programming language')}}</label>
                <div class="col-sm-8">
                    <div class="dropdown team-dropdown prog_langs-dropdown">
                        <select id="prog_langs" class="form-control select-search hidden multiselect2" name="prog_langs[]" multiple="multiple">
                            @foreach($programsOption as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('prog_langs'))
                        <label id="prog_langs-error" class="error" for="prog_langs">{{ $errors->first('prog_langs') }}</label>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            <div class="col-sm-6 form-group">
                <label for="is_important" class="col-sm-4 control-label margin-top--10">{{trans('project::view.Is important')}}</label>
                <div class="col-sm-8 fg-valid-custom">
                    <div class="dropdown team-dropdown prog_langs-dropdown">
                        <div class="checkbox">
                            <input type="checkbox" class="margin-left-0 input-basic-info" id="is_important" name="is_important" value="{{ Project::IS_IMPORTANT }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="business" class="col-sm-4 control-label margin-top--10 required">{{trans('project::view.Business domain')}} <em>*</em></label>
                <div class="col-sm-8" id="select_business">
                    <select class="form-control" name="business" id="business">
                        <option value=""></option>
                        @foreach($lblBusinessCate as $items => $item)
                            <option value="{{$items}}">{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-sm-6 form-group">
                <label for="sub_sector" class="col-sm-4 control-label margin-top--10 required">{{trans('project::view.Sub sector')}} <em>*</em></label>
                <div class="col-sm-8" id="select_sub_sector">
                    <select class="form-control" name="sub_sector" id="sub_sector">
                        <option value=""></option>
                        @foreach($lblSectorCate as $items => $item)
                            <option value="{{$items}}">{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <p class="text-center">
            <button class="btn btn-primary btn-create btn-lg" type="submit">
            {{trans('project::view.Create Project')}}
            </button>
        </p>
    </div>
  </form>
</div>

@section('script')
    @parent
    @include('project::tab_content.includes.js.js-create-project')
    @include('project::tab_content.includes.js-model-operation')
@endsection
