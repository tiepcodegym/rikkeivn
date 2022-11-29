@extends('layouts.default')

<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\Menu;
use Rikkei\Core\Model\MenuItem;
use Rikkei\Team\Model\Action;
use Rikkei\Core\View\View;

$activeOptions = MenuItem::toOptionState();
$menusOptions = Menu::toOption();
$menuItemOptions = MenuItem::toOption(Form::getData('menuitem.id'));
$actionsOptions = Action::toOption();
$routeListOption = View::routeListToOption();
?>

@section('title')
@if (! Form::getData('menuitem.id'))
    {{ trans('core::view.Create new') }} menu
@else
    Menu: {{ Form::getData('menuitem.name') }}
@endif
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
@endsection

@section('content')
<div class="row menu-group">
    <form action="{{ route('core::setting.menu.item.save') }}" method="post" class="form-horizontal" id="form-edit-menu-item">
        {!! csrf_field() !!}
        <input type="hidden" name="id" value="{{ Form::getData('menuitem.id') }}" />
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body form-group-select2">
                    
                    <div class="form-group form-label-left">
                        <label class="col-md-3 control-label required">{{ trans('team::view.Name') }}<em>*</em></label>
                        <div class="input-box col-md-9">
                            <input type="text" name="item[name]" class="form-control" placeholder="{{ trans('team::view.Name') }}" value="{{ Form::getData('menuitem.name') }}" />
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left">
                        <label class="col-md-3 control-label">{{ trans('core::view.State') }}</label>
                        <div class="input-box col-md-9">
                            <select class="form-control select-search2" name="item[state]">
                                @foreach ($activeOptions as $option)
                                    <option value="{{ $option['value'] }}"<?php if ($option['value'] == Form::getData('menuitem.state')): ?> selected<?php endif; ?>>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left">
                        <label class="col-md-3 control-label">{{ trans('core::view.Menu group') }}</label>
                        <div class="input-box col-md-9">
                            <select class="form-control select-search2" name="item[menu_id]">
                                @foreach ($menusOptions as $option)
                                    <option value="{{ $option['value'] }}"<?php if ($option['value'] == Form::getData('menuitem.menu_id')): ?> selected<?php endif; ?>>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left form-group-select2">
                        <label class="col-md-3 control-label">{{ trans('core::view.Parent Menu') }}</label>
                        <div class="input-box col-md-9">
                            <select class="form-control select-search" name="item[parent_id]">
                                @foreach ($menuItemOptions as $option)
                                    <option value="{{ $option['value'] }}"<?php if ($option['value'] == Form::getData('menuitem.parent_id')): ?> selected<?php endif; ?>>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left form-group-select2">
                        <label class="col-md-3 control-label">{{ trans('core::view.Url') }}</label>
                        <div class="input-box col-md-9">
                            <select name="item[url]" class="form-control select-search">
                                @if ($routeListOption)
                                    @foreach ($routeListOption as $option)
                                        <option value="{{ $option['value'] }}"<?php if ($option['value'] == Form::getData('menuitem.url')): ?> selected<?php endif; ?>>{{ $option['label'] }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left form-group-select2">
                        <label class="col-md-3 control-label">{{ trans('core::view.Action') }}</label>
                        <div class="input-box col-md-9">
                            <select class="select-search form-control" name="item[action_id]">
                                @foreach ($actionsOptions as $option)
                                    <option value="{{ $option['value'] }}"<?php if ($option['value'] == Form::getData('menuitem.action_id')): ?> selected<?php endif; ?>>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group form-label-left">
                        <label class="col-md-3 control-label">{{ trans('core::view.Sort') }}</label>
                        <div class="input-box col-md-9">
                            <input type="number" name="item[sort_order]" class="form-control" placeholder="{{ trans('core::view.Sort') }}" value="{{ Form::getData('menuitem.sort_order') }}" />
                        </div>
                    </div>
                    
                    <div class=" col-md-12 box-action box-action-bottom">
                        <input type="submit" class="btn-add" name="submit" value="{{ trans('team::view.Save') }}" />
                        @if (Form::getData('menuitem.id'))
                            <input type="submit" class="btn-delete btn-action delete-confirm no-disabled" disabled name="submit_delete" 
                                value="{{ trans('team::view.Remove') }}" data-noti="{{ trans('core::view.Are you sure delete this menu and all children?') }}" />
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
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 50]) ; ?>',
              }
        }
        var rules = {
            'item[name]': {
                required: true,
                rangelength: [1, 50]
            }
        };
        $('#form-edit-menu-item').validate({
            rules: rules,
            messages: messages
        });
        selectSearchReload({showSearch: true});
        $('.select-search2').select2({
            minimumResultsForSearch: Infinity
        });
    });
</script>
@endsection