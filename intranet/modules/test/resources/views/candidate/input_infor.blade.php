@extends('layouts.guest')

@section('title', trans('test::test.candidate_infor'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.3.8/css/AdminLTE.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="/tests/css/candidate.css">
@stop

@section('messages')
<!--none include here-->
@stop

@section('content')
<div class="logo-rikkei">
    <img class="img-responsive" src="/common/images/logo-rikkei.png">
</div>

<div class="main-content">

    <h1 class="main-title text-center">{{ trans('test::test.candidate_infor') }}</h1>

    <div class="form-group">
        <p>{{ trans('test::test.head_introduce') }}</p>
    </div>

    {!! Form::open(['method' => 'post', 'route' => 'test::candidate.save_infor', 'id' => 'form_infor']) !!}

    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            
            <div class="messages">
                @include('messages.success')
                @include('messages.errors')
            </div>
            
            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.Full name') }} <em>*</em></label>
                <input type="text" autocomplete="new-password" name="fullname" value="{{ $item ? $item->fullname : old('fullname') }}" class="form-control" placeholder="">
            </div>
            
            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.email') }} <em>*</em></label>
                <input type="email" autocomplete="new-password" name="email" value="{{ $item ? $item->email : old('email') }}" class="form-control" placeholder="">
            </div>
            
            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.Date of birth') }} <em>*</em></label>
                <input type="text" name="birthday" value="{{ $item ? $item->birthday : old('birthday') }}" class="form-control" placeholder="YYYY-MM-DD">
            </div>
            
            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.Identify') }} <em>*</em></label>
                <input type="text" name="identify" value="{{ $item ? $item->identify : old('email') }}" class="form-control" placeholder="">
            </div>

            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.Issued date') }} <em>*</em></label>
                <input type="text" name="issued_date" value="{{ $item ? $item->issued_date : old('issued_date') }}" class="form-control" placeholder="YYYY-MM-DD">
            </div>

            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.Issued place') }} <em>*</em></label>
                <input type="text" name="issued_place" value="{{ $item ? $item->issued_place : old('issued_place') }}" class="form-control" placeholder="">
            </div>
            
            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.Home town') }} <em>*</em></label>
                <input type="text" name="home_town" autocomplete="new-password" value="{{ $item ? $item->home_town : old('home_town') }}" class="form-control" placeholder="">
            </div>
            
            <div class="form-group mgb-30 no-appear">
                <label class="required">{{ trans('test::test.Phone number') }} <em>*</em></label>
                <input type="number" min="0" name="mobile" value="{{ $item ? $item->mobile : old('mobile') }}" class="form-control" placeholder="">
            </div>
            
            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.Position recruitment') }} <em>*</em></label>
                <input type="text" name="position_apply_input" value="{{ $item ? $item->position_apply_input : old('position_apply_input') }}" class="form-control">
            </div>
            
            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.Desired salary') }} <em>*</em></label>
                <input type="text" autocomplete="new-password" name="offer_salary_input" value="{{ $item ? $item->offer_salary_input : old('offer_salary_input') }}" class="form-control" placeholder="">
            </div>
            
            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.If recruited, when can you start the job') }} <em>*</em></label>
                <input type="text" name="offer_start_date" value="{{ $item ? $item->offer_start_date : old('offer_start_date') }}" class="form-control" placeholder="YYYY-MM-DD">
            </div>
            
            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.Have you worked at our company') }} <em>*</em></label>
                <input type="text" name="had_worked" value="{{ $item ? $item->had_worked : old('had_worked') }}" class="form-control" placeholder="">
            </div>
            
            <div class="form-group mgb-30">
                <label class="required">{{ trans('test::test.Where did you hear about our recruitment') }} <em>*</em></label>
                <input name="channel_input" value="{{ $item ? $item->channel_input : old('channel_input') }}" class="form-control">
            </div>
            
            <div class="form-group mgb-30">
                <label>{{ trans('test::test.Your name or your relatives are working for our company') }}</label>
                <input type="text" name="relative_worked" value="{{ $item ? $item->relative_worked : old('relative_worked') }}" class="form-control" placeholder="">
            </div>

        </div>
    </div>

    <div class="form-group">
        <p>{{ trans('test::test.footer_confirm') }}</p>
    </div>

    <div class="form-group text-center">
        @if ($item)
        <input type="hidden" name="id" value="{{ $item->email }}" />
        @endif
        <button type="submit" class="btn-add">{{ $item ? trans('test::test.save') : trans('test::test.Submit') }}</button>
    </div>

    {!! Form::close() !!}

</div>

@stop

@section('footer-class', 'container')

@section('script')

<script src="{{ URL::asset('common/js/script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>

<script>
    
var fields = ['fullname', 'email', 'birthday', 'identify', 'issued_date', 'issued_place', 'home_town', 'mobile', 'position_apply_input', 'offer_salary_input', 'offer_start_date', 'had_worked', 'channel_input'];
var rules = {};
var messages = {};
for (var i = 0; i < fields.length; i++) {
    var field = fields[i];
    rules[field] = {
        required: true,
        maxlength: 255
    };
    messages[field] = {
        required: '<?php echo trans('test::validate.this_field_is_required') ?>'
    };
    if (field == 'mobile') {
        rules[field].maxlength = 11;
        messages[field].maxlength = '<?php echo trans('test::validate.this_field_max_character', ['max' => 11]) ?>';
    }
}
$('#form_infor').validate({
    rules: rules,
    messages: messages
});

var elBirth = $('input[name="birthday"]');
$('input[name="offer_start_date"], input[name="birthday"], input[name="issued_date"]').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    todayHighlight: true,
    todayBtn: "linked"
});
@if (!$item)
    elBirth.datepicker('setDate', new Date(1990, 00, 01));
    elBirth.datepicker('update');
    elBirth.val('');
@endif

numberFormat($('input[name="offer_salary_input"]'));

$('.select-search').select2({
    minimumResultsForSearch: -1
});

</script>

@stop
