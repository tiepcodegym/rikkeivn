@extends('layouts.default')
<?php
use Carbon\Carbon;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\RequestChannel;
use Rikkei\Resource\Model\Channels;
use Rikkei\Core\View\CoreUrl;

if (isset($channel) && $channel->id) {
    $checkEdit = true;
    $urlSubmit = route('resource::channel.postCreate', ['id' => $channel->id]);
} else {
    $urlSubmit = route('resource::channel.postCreate');
    $checkEdit = false;
}
$key = 0;
?>
@section('title')
@if (isset($channel) && $channel->id)
{{ trans('resource::view.Channel.Create.Update channel') }}
@else
{{ trans('resource::view.Channel.Create.Create channel') }}
@endif
@endsection

@section('css')
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('sales/css/customer_create.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<style>
    .datepicker {
        border: 1px solid #ccc !important;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-create-channel" method="post" action="{{ $urlSubmit }}" 
                      enctype="multipart/form-data" autocomplete="off" class="form-horizontal form-sales-module">
                    {!! csrf_field() !!}
                    @if($checkEdit)
                        <input type="hidden" name="channel_id" value="{{ old('id', $channel->id) }}" />
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="name" class="col-sm-3 control-label">{{ trans('resource::view.Channel.Create.Channel name') }} <em class="required">*</em></label>
                                <div class="col-sm-9">
                                    <input name="name" class="form-control input-field" type="text" id="company" 
                                        value="{{ old('name', $channel->name) }}" placeholder="{{ trans('resource::view.Channel.Create.Channel name') }}" />
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="cost" class="col-sm-3 control-label">{{ trans('resource::view.Request.Create.Status') }}</label>
                                <div class="col-sm-9">
                                    <label class="radio-inline padding-left-0">
                                        <input type="radio" name="status"
                                               value="{{Channels::ENABLED}}"
                                               checked
                                        >&nbsp;{{ trans('resource::view.Enable') }}
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio"name="status"
                                               value="{{Channels::DISABLED}}"
                                                {{ $checkEdit && $channel->status == Channels::DISABLED ? 'checked' : '' }}
                                        >&nbsp;{{ trans('resource::view.Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="cost" class="col-sm-3 control-label">{{ trans('resource::view.Channel.Presenter type') }} <em class="required">*</em></label>
                                <div class="col-sm-9">
                                    <label class="radio-inline padding-left-0">
                                        <input type="radio" name="is_presenter"
                                            value="{{Channels::PRESENTER_NO}}"
                                            checked
                                        >&nbsp;{{ trans('resource::view.No') }}
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio"name="is_presenter" 
                                            value="{{Channels::PRESENTER_YES}}"
                                            @if (old('is_presenter') && Channels::PRESENTER_YES == old('is_presenter')) 
                                                checked 
                                            @elseif ($checkEdit && $channel->is_presenter == Channels::PRESENTER_YES)
                                                checked 
                                            @endif
                                        >&nbsp;{{ trans('resource::view.Yes') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="cost"
                                       class="col-sm-3 control-label">{{ trans('resource::view.Channel.Cost type') }}</label>
                                <div class="col-sm-9">
                                    <label class="radio-inline padding-left-0">
                                        <input type="radio" name="cost_type" id="cost_change"
                                               value="{{ Channels::COST_CHANGE }}"
                                               {{ $checkEdit &&  $channel->type == Channels::COST_CHANGE ? 'checked' : '' }}
                                               @if(!$checkEdit)
                                               checked
                                                @endif
                                        >&nbsp;{{ trans('resource::view.Channel.Cost change') }}
                                    </label>
                                    <label class="radio-inline padding-left-0">
                                        <input type="radio" name="cost_type" id="cost_fixed"
                                               value="{{ Channels::COST_FIXED }}"
                                                {{ $checkEdit &&  $channel->type == Channels::COST_FIXED ? 'checked' : '' }}
                                        >&nbsp;{{ trans('resource::view.Channel.Cost fixed') }}
                                    </label>

                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($checkEdit)
                        @foreach($channel->channelFees as $key => $val)
                    <div class="list-input-fields-{{ $key }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="cost"
                                           class="col-sm-3 control-label">{{ trans('resource::view.From date') }}</label>
                                    <div class="col-md-2">
                                        <span>
                                            <input type='text' class="form-control date start_date"
                                                   name="data[{{$key}}][start_date]"
                                                   placeholder="YYYY-MM-DD" tabindex=4
                                                   value="{{ $val->start_date }}"/>
                                        </span>
                                    </div>
                                    <label for="cost"
                                           class="col-sm-1 control-label"
                                           style="white-space: nowrap">{{ trans('resource::view.To date') }}</label>
                                    <div class="col-md-2">
                                        <span>
                                            <input type='text' class="form-control date end_date"
                                                   name="data[{{$key}}][end_date]"
                                                   placeholder="YYYY-MM-DD" tabindex=4
                                                   value="{{ $val->end_date }}"/>
                                        </span>
                                    </div>
                                    <div id="show_cost" style="display: {{ $channel->type == Channels::COST_CHANGE ? 'none' : '' }}"
                                         class="show_cost">
                                        <label for="cost" style="white-space: nowrap"
                                               class="col-sm-1 control-label show_cost">{{ trans('resource::view.Request.Detail.Cost') }}
                                            <em class="required">*</em></label>
                                        <div class="col-md-2">
                                            <span>
                                                 <input name="data[{{$key}}][cost]" class="form-control input-field show_cost" value="{{ number_format($val->cost) }}"/>
                                            </span>
                                        </div>
                                    </div>
                                    <button class="btn btn-danger btn-sm btn-del-cost-change" type="button">
                                        <i class="fa fa-close"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                        @endforeach
                    @endif
                    @if(count($channel->channelFees) == 0 || !$checkEdit)
                        <div class="list-input-fields-0">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group form-label-left">
                                        <label for="cost"
                                               class="col-sm-3 control-label">{{ trans('resource::view.From date') }}</label>
                                        <div class="col-md-2">
                                <span>
                                    <input type='text' class="form-control date start_date"
                                           name="data[0][start_date]"
                                           placeholder="YYYY-MM-DD" tabindex=4
                                           value=""/>
                                </span>
                                        </div>
                                        <label for="cost"
                                               class="col-sm-1 control-label"
                                               style="white-space: nowrap">{{ trans('resource::view.To date') }}</label>
                                        <div class="col-md-2">
                                <span>
                                    <input type='text' class="form-control date end_date"
                                           name="data[0][end_date]"
                                           placeholder="YYYY-MM-DD" tabindex=4
                                           value=""/>
                                </span>
                                        </div>
                                        <div id="show_cost" style="display: none" class="show_cost">
                                            <label for="cost" style="white-space: nowrap"
                                                   class="col-sm-1 control-label">{{ trans('resource::view.Request.Detail.Cost') }}
                                                <em
                                                        class="required">*</em></label>
                                            <div class="col-md-2">
                                <span>
                                    <input name="data[0][cost]" class="form-control input-field show_cost" value = ''/>
                                </span>
                                            </div>
                                        </div>
                                        <button class="btn btn-danger btn-sm btn-del-cost-change" type="button">
                                            <i class="fa fa-close"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="upload-wrapper">
                        <button type="button" class="btn btn-primary btn-sm" id="btn_add_input"
                                data-name="attach_files[]"><i class="fa fa-plus"></i></button>
                    </div>
                    @if ($checkEdit)
<!--                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="cost" class="col-sm-3 control-label">{{ trans('resource::view.Channel.Create.Used') }} <em class="required">*</em></label>
                                <div class="col-sm-9">
                                    <span>{{ View::getInstance()->priceFormat(RequestChannel::getInstance()->getTotalCostByChannel($channel->id)) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>-->
                    @endif
                    
                    <div class="row">
                        <div class="col-md-12 align-center">
                            <button class="btn-add" type="submit">
                                @if($checkEdit)
                                    {{ trans('resource::view.Channel.Create.Update channel') }}
                                @else
                                    {{ trans('resource::view.Channel.Create.Create channel') }}
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script>
    /**
    * 
    * iCheck load
    */
    $('input').iCheck({
       checkboxClass: 'icheckbox_minimal-blue',
       radioClass: 'iradio_minimal-blue'
    }); 
    jQuery(document).ready(function ($) {
        messages = {
            name : {
                required: '<?php echo trans('resource::view.Channel.Create.Name required'); ?>',
                rangelength: '<?php echo trans('resource::view.Channel.Create.Name greater than', ['number'=> 255]); ?>'
            }
        };

        rules = {
            name : {
                required: true,
                rangelength: [1, 255]
            }
        };

        $('#form-create-channel').validate({
            rules: rules,
            messages: messages
        });
        
        $('.num').keyup(function(event) {

        // skip for arrow keys
        if(event.which >= 37 && event.which <= 40){
            event.preventDefault();
           }

           $(this).val(function(index, value) {
               value = value.replace(/,/g,''); // remove commas from existing input
               return numberWithCommas(value); // add commas back in
           });
         });
    });
    jQuery.validator.addClassRules({
        start_date: {
            required: true
        },
        end_date: {
            required: true
        },
        show_cost: {
            required: true
        }
    });

    $('#cost_change').on('ifUnchecked ifChecked', function (event) {
        if (event.type == 'ifUnchecked') {
            $('.show_cost').show();
        } else {
            $('.show_cost').hide();
        }
    });
    var i = {{ $key }};
    $('#btn_add_input').click(function (e) {
        i += 1;
        e.preventDefault();
        var dom = $('.list-input-fields-0 .row')[0].outerHTML;
        dom = dom.replace(/name=\"data\[\d+\]/g, 'name="data[' + i + ']');
        dom = dom.replace(/value=\".+?\"/g, 'value=""');
        $(".list-input-fields-{{ $key }}").append(dom);
        $('.date').datepicker({
            autoclose: true,
            todayHighlight: true,
            todayBtn: "linked",
            format: 'yyyy-mm-dd'
        });
    });

    $('body').on('click', '.btn-del-cost-change', function (e) {
        e.preventDefault();
        if ($('.btn-del-cost-change').length > 1) {
            $(this).closest('.row').remove();
        }
    });

    $('.date').datepicker({
        autoclose: true,
        todayHighlight: true,
        todayBtn: "linked",
        format: 'yyyy-mm-dd'
    });

    function addCommaInNumber(number) {
        return (number + '').replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    $(document).on("keyup", '.show_cost', function (event) {
        if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
            return;
        }
        var money = $(this);
        var input = parseInt(money.val().replace(/[\D\s\._\-]+/g, ""));
        isNaN(input) ? input = 0 : input;
        money.val(function () {
            return (input === 0) ? 0 : addCommaInNumber(input);
        });
    });
</script>
@endsection
