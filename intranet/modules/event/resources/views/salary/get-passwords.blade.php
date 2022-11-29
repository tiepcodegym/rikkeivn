<?php
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Config;
?>

@extends('layouts.default')

@section('title', trans('event::view.Get password'))

@section('content')

<div class="box box-info">
    <div class="box-body">
        
    </div>

    <div class="box-body">
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
                
            {!! Form::open([
                'method' => 'post',
                'route' => 'event::send.email.employees.show_passwords',
                'class' => 'no-validate'
            ]) !!}

            <div class="form-group">
                <label>Email <em class="text-red">*</em></label>
                <input type="text" name="email" class="form-control" value="{{ isset($email) ? $email : old('email') }}" autofocus id="email"/>
            </div>

            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">{{ trans('event::view.Show password') }}</button>
            </div>

            {!! Form::close() !!}
            
            </div>
        </div>
    </div>

    @if (isset($passwords))
        <div class="box-body">
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    
                   <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Pass</th>
                                    <th>Date</th>
                                    <th>Current</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (!$passwords->isEmpty())
                                    @foreach ($passwords as $item)
                                    <tr>
                                        <td>{{ decrypt($item->value) }}</td>
                                        <td>{{ $item->updated_at }}</td>
                                        <td>
                                            @if ($item->is_current)
                                            <i class="fa fa-check text-green"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                <tr>
                                    <td colspan="3">
                                        <h4 class="text-center">{{ trans('event::message.Not found item') }}</h4>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group text-center">
                        <button type="submit" onclick = "resetPassword();" class="btn btn-primary"> Reset Password </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
</div>
<script>
    function makeid(length) {
        var result           = '';
        var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for ( var i = 0; i < length; i++ ) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    function resetPassword() {
        var pass = makeid(8);
        var employee_email =  $('#email').val();
        $.ajax({
        method: "post",
        dataType: 'json',
        data : {
           password: pass,
           email: employee_email,
           '_token': "{{csrf_token()}}",
           
        },
        url: "{{ route('event::send.email.employees.reset_password') }}" ,
        success: function (data) {
            alert(data);
            location.reload()
        },
        }); 
    };   
</script>
@endsection
