@extends('layouts.guest')

@section('title', trans('test::test.candidate_infor'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.3.8/css/AdminLTE.min.css" />
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
    
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            
            <div class="messages">
                @include('messages.success')
            </div>
            
            <?php 
            $fields = [
                'fullname' => trans('test::test.Full name'),
                'email' => trans('test::test.email'),
                'birthday' => trans('test::test.Date of birth'),
                'identify' => trans('test::test.Identify'),
                'issued_date' => trans('test::test.Issued date'),
                'issued_place' => trans('test::test.Issued place'),
                'home_town' => trans('test::test.Home town'),
                'mobile' => trans('test::test.Phone number'),
                'position_apply_input' => trans('test::test.Position recruitment'),
                'offer_salary_input' => trans('test::test.Desired salary'),
                'offer_start_date' => trans('test::test.If recruited, when can you start the job'),
                'had_worked' => trans('test::test.Have you worked at our company'),
                'channel_input' => trans('test::test.Where did you hear about our recruitment'),
                'relative_worked' => trans('test::test.Your name or your relatives are working for our company')
            ] 
            ?>
            @foreach($fields as $key => $label)
            <div class="form-group">
                <label>{{ $label }}</label>
                <input disabled class="form-control no-resize" value="{{ $item->{$key} }}" style="height: 45px;"></input>
            </div>
            @endforeach
        </div>
    </div>

    <div class="form-group text-center">
        <a href="{{ route('test::candidate.input_infor', ['id' => $item->email]) }}" class="btn-edit">{{ trans('test::test.edit') }}</a>
    </div>

</div>

@stop

@section('footer-class', 'container')

@section('script')

@stop
