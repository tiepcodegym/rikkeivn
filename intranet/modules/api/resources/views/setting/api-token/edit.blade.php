<?php
use Rikkei\Api\Helper\Helper;

$routes = Helper::listRoutes();
?>

@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css">
@stop

@section('title', trans('api::view.Api access token setting'))

@section('content')

<div class="box box-rikkei">
    <div class="box-header with-border">
        <h3 class="box-title">{!! trans('core::view.Edit item') !!}</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                <form method="post" class="no-validate" action="{!! route('api-web::setting.tokens.save') !!}">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <?php
                        $curRoute = old('route') ? old('route') : ($item ? $item->route : request()->get('route'));
                        ?>
                        <label>{!! trans('api::view.Api route') !!} <em class="text-red">*</em></label>
                        <select class="form-control select-search" name="route">
                            <option value="">&nbsp;</option>
                            @if (count($routes) > 0)
                                @foreach ($routes as $routeName => $desc)
                                <option value="{!! $routeName !!}" {!! $routeName === $curRoute ? 'selected' : '' !!}>{!! $desc !!}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{!! trans('api::view.Api token') !!}</label>
                        <div class="input-group">
                            <input type="text" name="token" class="form-control" autocomplete="off"
                                   value="{{ old('token') ? old('token') : ($item ? $item->token : null) }}"/>
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-primary btn-make-random-token">{!! trans('api::view.Generate') !!}</button>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{!! trans('api::view.Expired at') !!}</label>
                        <div class="input-group date">
                            <span class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </span>
                            <input type="text" name="expired_at" class="form-control input-datetimepicker" data-format="YYYY-MM-DD HH:mm"
                                value="{{ old('expired_at') ? old('expired_at') : ($item ? $item->expired_at : null) }}"/>
                        </div>
                    </div>
                    <div class="form-group text-center">
                        <a href="{!! route('api-web::setting.tokens.list') !!}" class="btn btn-warning">
                            <i class="fa fa-long-arrow-left"></i> {!! trans('core::view.Back to list') !!}
                        </a>
                        @if ($item)
                        <input type="hidden" name="id" value="{!! $item->id !!}" />
                        @endif
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> {!! trans('core::view.Save') !!}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@stop

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script>
    (function ($) {
        RKfuncion.select2.init();
        RKfuncion.general.initDateTimePicker($('.input-datetimepicker'));

        $('.btn-make-random-token').click(function (e) {
            e.preventDefault();
            var ranToken = RKfuncion.general.strRandom(64, false);
            var elInput = $(this).closest('.input-group').find('input');
            elInput.val(ranToken);
        });
    })(jQuery);
</script>
@stop
