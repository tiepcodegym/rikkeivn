<?php
    use Rikkei\Project\Model\MeAttribute;
    use Rikkei\Core\View\CoreUrl;
?>

<div class="row">
    <div class="col-sm-6">
        <div class="row">
            <div class="col-xs-6">
                <div class="form-group">
                    <label>{{trans('me::view.Weight')}} (%)<sup>*</sup></label>
                    {!! Form::number('weight', $item->weight, ['class' => 'form-control', 'step' => '0.1']) !!}
                </div>
            </div>
            <div class="col-xs-6">
                <div class="form-group">
                    <label>{{trans('project::me.Default')}}</label>
                    {!! Form::number('default', $item->default, ['class' => 'form-control', 'step' => '0.1']) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-4">
                <div class="form-group">
                    <label>{{trans('project::me.Min value')}}</label>
                    {!! Form::number('range_min', $item->range_min, ['class' => 'form-control', 'step' => '0.1']) !!}
                </div>
            </div>
            <div class="col-xs-4">
                <div class="form-group">
                    <label>{{trans('project::me.Max value')}}</label>
                    {!! Form::number('range_max', $item->range_max, ['class' => 'form-control', 'step' => '0.1']) !!}
                </div>
            </div>
            <div class="col-xs-4">
                <div class="form-group">
                    <label>{{trans('project::me.Step')}}</label>
                    {!! Form::number('range_step', $item->range_step, ['class' => 'form-control', 'step' => '0.1']) !!}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>{{trans('project::me.Order')}}</label>
            {!! Form::number('order', $item->order, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            <label>{{trans('project::me.Group')}}</label>
            {!! Form::select('group', MeAttribute::getGroupTypes(), $item->group, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            <label>{{trans('project::me.Fill')}} </label>
             {!! Form::checkbox('can_fill', old('can_fill'), $item->can_fill) !!}
        </div>
    </div>
</div>