@extends('layouts.default')
<?php
use Carbon\Carbon;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\Programs;
?>
@section('title')
@if(isset($programId) && !empty($programId['id']))
{{ trans('resource::view.Programminglanguages.Edit.Edit Programminglanguages edit') }}
@else
{{ trans('resource::view.Programminglanguages.Create.Create Programminglanguages') }}
@endif
@endsection
<?php
if(isset($programId) && !empty($programId['id'])) { 
    $urlSubmit = route('resource::programminglanguages.postCreate');
    $checkEdit = true;
} else {
    $urlSubmit = route('resource::programminglanguages.postCreate');
    $checkEdit = false;
}
?>

@section('css')
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('sales/css/customer_create.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form method="post" action="{{$urlSubmit}}" 
                      enctype="multipart/form-data" autocomplete="off" class="form-horizontal form-sales-module">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="name" class="col-sm-3 control-label">{{ trans('resource::view.Programminglanguages.Create.Create Programminglanguages name') }}
                                <em class="required">*</em></label>
                                <div class="col-sm-9">
                                    <input name="name" class="form-control input-field" type="text" id="company" aria-required="true" aria-invalid="true"
                                        value="{{ old('name',$programId['name']) }}" placeholder="{{ trans('resource::view.Programminglanguages.Create.Create Programminglanguages name') }}" />
                                    @if($checkEdit) 
                                        <input type="hidden" name="id" value="{{ old('id', $programId['id']) }}" />
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="name" class="col-sm-3 control-label">{{ trans('resource::view.Programminglanguages.Create.Primary char') }}
                                <em class="required">*</em></label>
                                <div class="col-sm-9">
                                    <label class="radio-inline"><input type="radio" name="primary_chart" checked value="0">&nbsp;{{ trans('resource::view.No') }}</label>
                                    @if($checkEdit && $programId['primary_chart'] == 1) 
                                        <label class="radio-inline"><input type="radio" name="primary_chart" checked value="1">&nbsp;{{ trans('resource::view.Yes') }}</label>
                                    @else
                                        <label class="radio-inline"><input type="radio" name="primary_chart" value="1"  >&nbsp;{{ trans('resource::view.Yes') }}</label>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 align-center">
                            <button class="btn-add" type="submit">@if(isset($programId) && !empty($programId['id']))
                            {{ trans('resource::view.Programminglanguages.Edit.Edit Programminglanguages update') }}
                            @else
                            {{ trans('resource::view.Programminglanguages.Create.Create Programminglanguages') }}
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
<script src="{{ asset('lib/js/jquery.validate.min.js') }}"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
<script type="text/javascript">
     $('input').iCheck({
       checkboxClass: 'icheckbox_minimal-blue',
       radioClass: 'iradio_minimal-blue'
    });
</script>
@endsection