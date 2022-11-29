@extends('layouts.guest')

@section('content')

<div class="row">
    <div class="welcome-body body-padding">
        <div class="logo-rikkei">
            <img src="{{ URL::asset('common/images/logo-rikkei.png') }}">
        </div>
        <div class="welcome-header">
            <h2 class="welcome-title <?php if($checkpoint->checkpoint_type_id == 1){ echo 'color-blue'; } ?>">{{ trans('team::view.Checkpoint.Welcome.Title') }}</h2>
        </div>
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span12">
                    <div >
                        <p class="welcome-line">{!! trans('team::view.Checkpoint.Welcome.Content') !!}</p>
                    </div>
                </div>
            </div>
            <div class="row-fluid ">
                <div class="css-make-info">
                    
                    <div>
                        <div class="customer-name-title">{{ trans('team::view.Checkpoint.Welcome.Employee name title') . $user->name}}</div>
                        <div >
                            <a href="{{$href}}" class="btn btn-default btn-to-make <?php if($checkpoint->checkpoint_type_id == 1){ echo 'bg-color-blue'; } ?>" name="submit">Next</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Styles -->
@section('css')
<link href="{{ asset('team/css/style.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script src="{{ asset('sales/js/css/customer.js') }}"></script>
<script src="{{ asset('sales/js/css/welcome.js') }}"></script>
@endsection