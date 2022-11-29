@extends('layouts.default')
<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\Menu;

$actionOptions = Menu::toOptionState();
?>

@section('title')
@if (! Form::getData('menus.id'))
    {{ trans('core::view.Create new menu group') }}
@else
    {{ trans('core::view.Menu group') }}: {{ Form::getData('menus.name') }}
@endif
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
@endsection

@section('content')
<div class="row menu-group">
    <form action="{{ route('core::setting.menu.group.save') }}" method="post" class="form-horizontal" id="form-edit-menus">
        {!! csrf_field() !!}
        <input type="hidden" name="id" value="{{ Form::getData('menus.id') }}" />
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body">
                    
                    <div class="form-group form-label-left">
                        <label class="col-md-3 control-label required">{{ trans('team::view.Name') }}<em>*</em></label>
                        <div class="input-box col-md-9">
                            <input type="text" name="item[name]" class="form-control" placeholder="{{ trans('team::view.Name') }}" value="{{ Form::getData('menus.name') }}" />
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left form-group-select2">
                        <label class="col-md-3 control-label">{{ trans('core::view.State') }}</label>
                        <div class="input-box col-md-9">
                            <select class="select-search form-control" name="item[state]">
                                @foreach ($actionOptions as $option)
                                    <option value="{{ $option['value'] }}"<?php if ($option['value'] == Form::getData('menus.state')): ?> selected<?php endif; ?>>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            
                        </div>
                    </div>
                    
                    <div class=" col-md-12 box-action box-action-bottom">
                        <input type="submit" class="btn-add" name="submit" value="{{ trans('team::view.Save') }}" />
                        @if (Form::getData('menus.id'))
                            <input type="submit" class="btn-delete btn-action delete-confirm no-disabled" 
                               disabled name="submit_delete" value="{{ trans('team::view.Remove') }}" />
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
<script>
    jQuery(document).ready(function ($) {
        var messages = {
            'item[name]': {
                required: '<?php echo trans('core::view.This field is required'); ?>',
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 50]) ; ?>'
              }
        };
        var rules = {
            'item[name]': {
                required: true,
                rangelength: [1, 50]
            }
        };
        $('#form-edit-menus').validate({
            rules: rules,
            messages: messages
        });
        selectSearchReload();
    });
</script>
@endsection