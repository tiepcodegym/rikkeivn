@extends('layouts.default')
<?php

    use Rikkei\Core\View\CoreUrl;

    $id = isset($relation) ? $relation->id : '';
?>
@section('title')
@if(isset($relation))
{{ trans('welfare::view.Relation Name') }} : {{ $relation->name }}
@else
{{ trans('welfare::view.Add New') }}
@endif
@endsection

@section('css')

@endsection

@section('content')
<div class="error-edit-relation hidden">
    <div class="alert alert-success">
        <ul>
            <li></li>
        </ul>
    </div>
</div>
<div class="row">
    <form action="{{ route('welfare::welfare.relation.save') }}" method="POST" class="form-horizontal" id="form-edit-relations-name">
        {!! csrf_field() !!}
        <input type="hidden" name="id" value="{{ isset($relation) ? $relation->id : null }}" />
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body form-group-select2">
                    <div class="form-group">
                        <label class="col-md-2 control-label required">{{ trans('welfare::view.Name Relations') }}<em>*</em></label>
                        <div class="input-box col-md-6">
                            <input type="text" name="name" class="form-control" placeholder="{{ trans('welfare::view.Name Relations') }}" value="{{ isset($relation) ? $relation->name : null }}" />
                            <label id="name-error" class="error hidden" style="color: red;"></label>
                        </div>
                    </div>
                    <div class=" col-md-12 box-action box-action-bottom no-disabled">
                        <input type="submit" class="btn-add" name="submit" value="{{ trans('team::view.Save') }}" />
                        @if (isset($relation))
                        <button type="button" class="btn-delete delete-relation-confirm" data-toggle="modal" data-target="#modal-delete-relation">
                                {{ trans('team::view.Remove') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade modal-danger" id="modal-delete-relation" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('welfare::view.Confirm Delete') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ trans('welfare::view.Confirm_message') }}</p>
                <p class="text-change"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok" data-url="{{ route('welfare::welfare.relation.delete') }}">{{ trans('welfare::view.Ok') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
@endsection

@section('script')
<script src="{{ URL::asset('lib/js/jquery.validate.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="{{ CoreUrl::asset('asset_welfare/js/relation.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var messages = {
            name: {
                required: '<?php echo trans('core::view.This field is required'); ?>',
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 30]); ?>',
                remote: '<?php echo trans('welfare::view.The value already exists') ?>'
            }
        };
        var rules = {
            name: {
                required: true,
                rangelength: [1, 30]
            }
        };

        $('#form-edit-relations-name').validate({
            rules: rules,
            messages: messages,
            lang: 'vi'
        });

        if ($.cookie('message') && $.cookie('message') !== 'undefined') {
            $('.error-edit-relation li').html($.cookie('message'));
            $('.error-edit-relation').removeClass('hidden');
            $('.error-edit-relation').show();
            $.removeCookie('message');
        }
    });
</script>
@endsection
