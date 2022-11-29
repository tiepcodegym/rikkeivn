<?php
use Rikkei\Core\View\View;
use Rikkei\Core\Model\CoreConfigData;
?>

<div class="row">
    <div class="form-group col-sm-6">
        <label class="col-sm-3 control-label">{{ trans('project::view.Descriptions') }}</label>
        <div class="col-sm-9">
            <textarea class="form-control" rows="5" name="scope[scope_desc]"
                      >{{ old('scope.scope_desc') ? old('scope.scope_desc') : ($scopeObject ? $scopeObject->scope_desc : null) }}</textarea>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="col-sm-3 control-label">{{ trans('project::view.Customers provided') }}</label>
        <div class="col-sm-9">
            <textarea class="form-control" rows="5" name="scope[scope_customer_provide]"
                      >{{ old('scope.scope_customer_provide') ? old('scope.scope_customer_provide') : ($scopeObject ? $scopeObject->scope_customer_provide : null) }}</textarea>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="col-sm-3 control-label">{{ trans('project::view.Scope') }}</label>
        <div class="col-sm-9">
            <textarea class="form-control" rows="5" name="scope[scope_scope]"
                      >{{ old('scope.scope_scope') ? old('scope.scope_scope') : ($scopeObject ? $scopeObject->scope_scope : null) }}</textarea>
        </div>
    </div>
</div>

<div>
    <h3>Customer requirements</h3>
    <div class="row">
        <label class="col-md-3 control-label column-width-12-5-per">{{ trans('project::view.General') }}</label>
        <div class="col-md-9 column-width-87-5-per">
            <p class="form-control-static">{!! View::nl2br(CoreConfigData::getValueDb('project.required_rikkei')) !!}</p>
        </div>
    </div>
    <div class="row">
        <label class="col-md-3 control-label column-width-12-5-per">{{trans('project::view.Customer contract requirement')}}</label>
        <div class="col-md-9 column-width-87-5-per">
            @if ($customer)
            <p class="form-control-static">{!! View::nl2br($customer->contract) !!}</p>
            @endif
        </div>
    </div>
    <div class="row">
        <label class="col-md-3 control-label column-width-12-5-per">{{trans('project::view.Extra')}}</label>
        <div class="col-md-9 column-width-87-5-per">
            <textarea class="form-control" rows="5" name="scope[scope_customer_require]"
                      >{{ old('scope.scope_customer_require') ? old('scope.scope_customer_require') : ($scopeObject ? $scopeObject->scope_customer_require : null) }}</textarea>
        </div>
    </div>
</div>