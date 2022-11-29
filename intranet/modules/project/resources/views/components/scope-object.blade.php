<?php
    use Rikkei\Core\View\View;
    use Rikkei\Core\Model\CoreConfigData;
    use Rikkei\Project\Model\ProjectMetaScope;
    use Rikkei\Project\Model\ProjectScope;
    use Rikkei\Project\Model\Task;

$lblScopeProj = ProjectScope::all()->pluck('proj_scope','id');
$scope = ProjectMetaScope::getProjectMetaScope($project->projectMeta->id);
$scopes = ProjectMetaScope::getLabelScope($project->projectMeta->id);
?>
<div class="row">
    <div class="form-group col-sm-6">
        <label class="col-sm-3 control-label">{{trans('project::view.Descriptions')}}</label>
        <div class="col-sm-9">
            @if(isset($permissionEdit) && $permissionEdit) 
            <textarea class="form-control scope scope_desc input-basic-info" rows="5" name="scope_desc" id="scope_desc">{{$project->projectMeta->scope_desc}}</textarea>
            @else
            <p class="form-control-static">{!! View::nl2br($project->projectMeta->scope_desc) !!}</p>
            @endif
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="col-sm-3 control-label">{{trans('project::view.Customers provided')}}</label>
        <div class="col-sm-9">
            @if(isset($permissionEdit) && $permissionEdit) 
            <textarea class="form-control scope scope_customer_provide input-basic-info" rows="5" name="scope_customer_provide" id="scope_customer_provide">{{$project->projectMeta->scope_customer_provide}}</textarea>
            @else
            <p class="form-control-static">{!! View::nl2br($project->projectMeta->scope_customer_provide) !!}</p>
            @endif
        </div>
    </div>
    <?php /*
    <div class="form-group col-sm-6">
        <label class="col-sm-3 control-label">{{trans('project::view.Products')}}</label>
        <div class="col-sm-9">
            @if(isset($permissionEdit) && $permissionEdit) 
            <textarea class="form-control scope scope_products input-basic-info" rows="5" name="scope_products" id="scope_products">{{$project->projectMeta->scope_products}}</textarea>
            @else
            <p class="form-control-static">{!! View::nl2br($project->projectMeta->scope_products) !!}</p>
            @endif
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="col-sm-3 control-label">{{trans('project::view.Requirements')}}</label>
        <div class="col-sm-9">
            @if(isset($permissionEdit) && $permissionEdit) 
            <textarea class="form-control scope scope_require input-basic-info" rows="5" name="scope_require" id="scope_require">{{$project->projectMeta->scope_require}}</textarea>
            @else
            <p class="form-control-static">{!! View::nl2br($project->projectMeta->scope_require) !!}</p>
            @endif
        </div>
    </div>
     */ ?>
    <div class="form-group col-sm-6">
        <label class="col-sm-3 control-label">{{trans('project::view.Scope')}}</label>
        <div class="col-sm-9">
            <div class="dropdown team-dropdown prog_langs-dropdown">
                @if(isset($permissionEdit) && $permissionEdit)
                <select class="form-control scope scope_scope multiselect2 input-basic-info" name="scope_scope[]" id="scope_scope" multiple="multiple">
                    @foreach($lblScopeProj as $items => $item)
                        <option value="{{$items}}" @if(in_array($items, explode(',', $scope->scope))) selected @endif>{{ $item }}</option>
                    @endforeach
                </select>
                @else
                <p class="form-control-static">{{ $scopes ? implode(', ', $scopes) : '' }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="row">
    <h3>{{ trans('project::view.Customer requirements') }}</h3>
    <div class="col-md-12"></div>
    <div class="col-md-12">
        <label class="col-md-3 control-label column-width-12-5-per">{{trans('project::view.General')}}</label>
        <div class="col-md-9 column-width-87-5-per">
            <textarea class="form-control scope requirements input-basic-info" name="requirements" id="requirements" rows="8">{{ $project->projectMeta->requirements }}</textarea>
            <h5 class="text-danger m-3">Note: Điền thông tin output khách hàng mong muốn sản phẩm như thế nào?</h5>
        </div>
    </div>
    @if ($company)
    @if ($isNotContractConfirm)
    <div  class="col-md-12" id="contract" style="border: 1px solid red; padding-bottom: 30px;">
        <h4>The content below has not been confirmed</h4>
    @endif
        <div class="col-md-12">
            <label class="col-md-3 control-label column-width-12-5-per">{{trans('project::view.Security')}}</label>
            <div class="col-md-9 column-width-87-5-per">
                <p class="form-control-static">{!! View::nl2br($company->contract_security) !!}</p>
            </div>
        </div>
        <div class="col-md-12">
            <label class="col-md-3 control-label column-width-12-5-per">{{trans('project::view.Quality')}}</label>
            <div class="col-md-9 column-width-87-5-per">
                <p class="form-control-static">{!! View::nl2br($company->contract_quality) !!}</p>
            </div>
        </div>
        @if ($isNotContractConfirm && ($currentUser->id === (int)$project->manager_id  || $currentUser->id === (int)$project->leader_id))
        <form method="post" class="col-md-12" action="{{ route('project::task.contractConfirm', $project->id) }}">
            {!! csrf_field() !!}
            <div class="col-md-3 column-width-12-5-per"></div>
            <div class="col-md-9 column-width-87-5-per">
                <button class="btn btn-primary">Confirm Read</button>
            </div>
        </form>
        @endif
    @if ($isNotContractConfirm)
    </div>
    @endif
        <div class="col-md-12">
            <label class="col-md-3 control-label column-width-12-5-per">{{trans('project::view.Others')}}</label>
            <div class="col-md-9 column-width-87-5-per">
                <p class="form-control-static">{!! View::nl2br($company->contract_other) !!}</p>
            </div>
        </div>
    @endif
    <div class="col-md-12">
        <label class="col-md-3 control-label column-width-12-5-per">{{trans('project::view.Extra')}}</label>
        <div class="col-md-9 column-width-87-5-per">
            @if(isset($permissionEdit) && $permissionEdit)
                <textarea class="form-control scope scope_customer_require input-basic-info" rows="5" name="scope_customer_require" id="scope_customer_require">{{$project->projectMeta->scope_customer_require}}</textarea>
            @else
                <p class="form-control-static">{!! View::nl2br($project->projectMeta->scope_customer_require) !!}</p>
            @endif
        </div>
    </div>
    @if ($customer->note)
    <div class="col-md-12">
        <label class="col-md-3 control-label column-width-12-5-per">{{trans('project::view.Note')}}</label>
        <div class="col-md-9 column-width-87-5-per">
            <p class="form-control-static">{!! View::nl2br($customer->note) !!}</p>
        </div>
    </div>
    @endif
</div>
<div class="row">
        <div class="grid-data-query task-list-ajax" data-url="{{ URL::route('project::workorder.log.list.ajax', ['id' => $project->id]) }}">
            <h3>{{ trans('project::view.Assumptions and Constraints') }}</h3>
            @include('project::components.assumptions')
            @include('project::components.constraints')
        </div>
</div>
<div class="row">
    <h3>{{ trans('project::view.Critical Dependencies') }}</h3>
    @include('project::components.critical-dependencies')
</div>