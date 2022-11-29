@extends('layouts.default')
<?php
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\Action;

$actionOptions = Action::toOption();
?>

@section('title')
@if (! Form::getData('acl.id'))
    {{ trans('team::view.Create new') }} Acl
@else
    Acl: 
    @if (Lang::has('acl.' . Form::getData('acl.description')))
        {{ trans('acl.' . Form::getData('acl.description')) }}
    @else
        {{ Form::getData('acl.description') }}
    @endif
@endif

@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
@endsection

@section('content')
<div class="row member-profile">
    <form action="{{ route('team::setting.acl.save') }}" method="post" class="form-horizontal" id="form-acl-edit">
        {!! csrf_field() !!}
        <input type="hidden" name="id" value="{{ Form::getData('acl.id') }}" />
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body">
                    
                    <div class="form-group form-label-left">
                        <label class="col-md-3 control-label required">Code<em>*</em></label>
                        <div class="input-box col-md-9">
                            <input type="text" name="item[name]" class="form-control" placeholder="{{ trans('team::view.Name') }}" value="{{ Form::getData('acl.name') }}" />
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left">
                        <label class="col-md-3 control-label">{{ trans('team::view.Description') }}</label>
                        <div class="input-box col-md-9">
                            <textarea name="item[description]" class="form-control" placeholder="{{ trans('team::view.Description') }}">{{ Form::getData('acl.description') }}</textarea>
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left">
                        <label class="col-md-3 control-label">{{ trans('team::view.Sort order') }}</label>
                        <div class="input-box col-md-9">
                            <input type="number" name="item[sort_order]" class="form-control" placeholder="{{ trans('team::view.Sort order') }}" value="{{ Form::getData('acl.sort_order') }}" />
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left form-group-select2">
                        <label class="col-md-3 control-label">{{ trans('team::view.Parent') }}</label>
                        <div class="input-box col-md-9">
                            <select class="select-search form-control" name="item[parent_id]">
                                @foreach ($actionOptions as $option)
                                    <option value="{{ $option['value'] }}"<?php if ($option['value'] == Form::getData('acl.parent_id')): ?> selected<?php endif; ?>>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left">
                        <label class="col-md-3 control-label">Route</label>
                        <div class="input-box col-md-9">
                            <input type="text" name="item[route]" class="form-control" placeholder="Route" value="{{ Form::getData('acl.route') }}" />
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left">
                        <label class="col-md-3 control-label">{{ trans('team::view.Translate (vi) "description"') }}</label>
                        <div class="input-box col-md-9">
                            <textarea name="trans[description]" class="form-control" disabled><?php
                                if (Lang::has('acl.' . Form::getData('acl.description'))): ?>{{ trans('acl.' . Form::getData('acl.description')) }}<?php
                                else: ?>{{ Form::getData('acl.description') }}<?php endif; ?></textarea>
                        </div>
                    </div>
                    <div class=" col-md-12 box-action box-action-bottom">
                        <input type="submit" class="btn-add" name="submit" value="{{ trans('team::view.Save') }}" />
                        @if (Form::getData('acl.id'))
                            <input type="submit" class="btn-delete btn-action delete-confirm no-disabled" disabled name="submit_delete" 
                                value="{{ trans('team::view.Remove') }}" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php
//remove flash session
Form::forget();
?>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('team/js/script.js') }}"></script>
<script>
    jQuery(document).ready(function($) {
        selectSearchReload({showSearch: true});
        messages = {
            'item[name]': {
                required: '<?php echo trans('core::view.This field is required'); ?>',
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>',
              }
          };
          rules = {
            'item[name]': {
                required: true,
                rangelength: [1, 255]
            }
          };
          $('#form-acl-edit').validate({
                rules: rules,
                messages: messages
            });
    });
    
</script>
@endsection