<?php
$senderName = old('exclude[email_from_name]') ? old('exclude[email_from_name]') : $eventInfo->sender_name;
$senderEmail = old('exclude[email_from]') ? old('exclude[email_from]') : $eventInfo->sender_email;
$appPassword = old('exclude[app_password]') ? old('exclude[app_password]') : $eventInfo->getAppPass();
$company = old('item[company]') ? old('item[company]') : $eventInfo->company;
$name = old('item[name]') ? old('item[name]') : $eventInfo->name;
$email = old('item[email]') ? old('item[email]') : $eventInfo->email;
$status = old('item[status]') ? old('item[status]') : $eventInfo->status;
$note = old('item[note]') ? old('item[note]') : $eventInfo->note;
$attacher = old('item[attacher]') ? old('item[attacher]') : $eventInfo->attacher;
info(old('item'));
?>
@extends('layouts.default')

@section('title')
{{ trans('event::view.Send mail event') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('content')
@include('event::eventday.customer.message-alert')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-event-create" method="post" action="{{ route('event::eventday.customer.update',['id'=>$eventInfo->id]) }}" 
                      class="form-horizontal has-valid" autocomplete="off" >
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left row">
                                <label for="email_from" class="col-sm-1 control-label required">{{ trans('event::view.Sender') }} <em>*</em></label>
                                <div class="col-sm-11">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <input name="exclude[email_from_name]" class="form-control input-field" type="text" id="email_from_name" 
                                                   value="{{$senderName}}" placeholder="{{ trans('event::view.Name') }}" />
                                        </div>
                                        <div class="col-sm-4">
                                            <input name="exclude[email_from]" class="form-control input-field" type="text" id="email_from" 
                                                   value="{{$senderEmail}}" placeholder="{{ trans('event::view.Email') }}" />
                                        </div>
                                      
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left form-group-select2 row">
                                <label for="gender" class="col-sm-1 control-label required">{{ trans('event::view.Receive') }}<em>*</em></label>
                                <div class="col-sm-11 fg-valid-custom">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <input name="item[company]" class="form-control input-field" type="text" id="company" 
                                                   value="{{$company}}" placeholder="{{ trans('event::view.Company') }}" />
                                        </div>

                                        <div class="col-sm-3">
                                            <input name="item[name]" class="form-control input-field" type="text" id="name" 
                                                   value="{{$name}}" placeholder="{{ trans('event::view.Name') }}" />
                                        </div>
                                        <div class="col-sm-3">
                                            <input name="item[email]" class="form-control input-field" type="text" id="email" 
                                                   value="{{$email}}" placeholder="{{ trans('event::view.Email') }}" />
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left form-group-select2 row">
                                <label for="gender" class="col-sm-1 control-label required">&nbsp;</label>
                                <div class="col-sm-11 fg-valid-custom">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <input name="item[attacher]" class="form-control input-field" type="text" id="company" 
                                                   value="{{$attacher}}" placeholder="{{ trans('event::view.Attacher') }}" />
                                        </div>

                                        <div class="col-sm-6">
                                            <input name="item[note]" class="form-control input-field" type="text" id="name" 
                                                   value="{{$note}}" placeholder="{{ trans('event::view.Other requirments') }}" />
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-label-left form-group-select2 row">
                                <label for="sel_status" class="col-sm-1 control-label required">{{ trans('event::view.Status') }}<em>*</em></label>
                                <div class="col-sm-11 fg-valid-custom">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <select class="form-control" name="item[status]" id="sel_status">
                                                <option  {{$status==0?'selected':''}}  value="0">{{trans('event::view.Not confirm')}}</option>
                                                <option {{$status==1?'selected':''}} value="1">{{trans('event::view.Attend')}}</option>
                                                <option {{$status==2?'selected':''}} value="2">{{trans('event::view.Refuse')}}</option>
                                            </select>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 align-center">
                            <button type="submit" class="btn-add " >{{ trans('event::view.Save') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
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
<script type="text/javascript">

jQuery(document).ready(function ($) {
var rules = {
'exclude[email_from]': {
required: true,
        email: true
        },
        'exclude[email_from_name]': {
        required: true
        },
        'item[gender]': {
        required: true
        },
        'item[company]': {
        required: true
        },
        'item[name]': {
        required: true
        },
        'item[email]': {
        required: true,
                email: true
        }
};
var messages = {
'exclude[email_from]': {
required: '{{ trans('core::view.This field is required') }}',
        email: '{{ trans('core::view.Please enter a valid email address') }}'
        },
        'exclude[email_from_name]': {
        required: '{{ trans('core::view.This field is required') }}'
        },
        'item[gender]': {
        required: '{{ trans('core::view.This field is required') }}'
        },
        'item[name]': {
        required: '{{ trans('core::view.This field is required') }}'
        },
        'item[company]': {
        required: '{{ trans('core::view.This field is required') }}'
        },
        'item[email]': {
        required: '{{ trans('core::view.This field is required') }}',
                email: '{{ trans('core::view.Please enter a valid email address') }}'
        }
};
$('#form-event-create').validate({
rules: rules,
        messages: messages
        });
});
</script>
@endsection