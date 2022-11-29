@extends('layouts.default')
<?php
use Carbon\Carbon;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Resource\View\View;
use Illuminate\Http\Request;
?>
@section('title')
{{ trans('resource::view.CheckExist.checkexist') }}
@endsection
<?php 

$urlSubmit = route('resource::candidate.postCheckExist');
if (empty($data)) {
    $checkEdit = false;
} else {
    $checkEdit = true;
}
?>

@section('css')
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">    
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('resource/css/resource.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-check-exist" method="post" action="{{ $urlSubmit }}" 
                      enctype="multipart/form-data" autocomplete="off" class="form-horizontal form-sales-module">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="name" class="col-sm-3 control-label">{{ trans('resource::view.CheckExist.Name') }}</label>
                                <div class="col-sm-9">
                                    <input name="fullname"  id="fullname" class="form-control input-field" type="text" id="company" 
                                        placeholder="{{ trans('resource::view.CheckExist.Name.Candidate') }}" value="{{$checkEdit ? $data['fullname'] : ''}}" />
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="cost" class="col-sm-3 control-label">{{ trans('resource::view.CheckExist.Email') }}</label>
                                <div class="col-sm-9">
                                    <input name="email" class="form-control input-field" type="text" id="email" 
                                    placeholder="{{ trans('resource::view.CheckExist.Email') }}" value="{{$checkEdit ? $data['email'] : ''}}"/> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group position-relative form-label-left">
                                <label for="birthday" class="col-sm-3 control-label">{{ trans('resource::view.CheckExist.Birthday') }}</label>
                                <div class="col-md-9">
                                    <span>                                  
                                        <input type='text' class="form-control date" id="birthday" name="birthday" data-provide="datepicker" placeholder="YYYY-MM-DD" value="{{$checkEdit ? $data['birthday'] : ''}}"/>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div> 
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="cost" class="col-sm-3 control-label">{{ trans('resource::view.CheckExist.Phone') }}</label>
                                <div class="col-sm-9">
                                    <input name="mobile" class="form-control input-field" type="text" id="company" 
                                        placeholder="{{ trans('resource::view.CheckExist.Phone') }}" value="{{$checkEdit ? $data['mobile'] : ''}}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 align-center">
                            <button class="btn btn-primary btn-submit-confirm" type="submit">
                            {{ trans('resource::view.CheckExist.checkexist') }}
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
<script src="{{ URL::asset('common/js/methods.validate.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="{{ asset('resource/js/candidate/checkexist.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
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
            fullname : {
                required: '<?php echo trans('resource::view.CheckExist.required'); ?>',
            }
        };
        rules = {
            fullname: {
                required: function() {
                    return $('#email').val().length == 0;
                }
            }
        };

        $('#form-check-exist').validate({
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
</script>
@endsection