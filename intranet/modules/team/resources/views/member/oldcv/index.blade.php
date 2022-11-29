@extends('layouts.profile_layout')

<?php

use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
?>
@section('css-profile')
    <link rel="stylesheet" href="{{ URL::asset('team/css/cv.css') }}" />
@endsection

@section('content-profile')
@section('left-menu')
    @include('team::member.left_menu',['active'=>'mycv'])
@endsection
<!-- Edit form -->
<div class="box-header">
    <h3 class="box-title" style="font-size: 21px;">{{ trans('team::profile.My CV') }}</h3>
    @if (Permission::getInstance()->isAllow('team::team.member.edit.downloadcv'))
    <a title="{{ trans('team::view.Download My CV') }}"  
        class="btn btn-app pull-right download-cv"
        href="{{route('team::member.profile.cv.savecv', ['employeeId' => $employeeId])}}">
        <i class="fa fa-download"></i>
        {{ trans('team::view.Download My CV') }}
    </a>
    @endif
</div>
<!--/.BEGIN CONTENT -->
{!! $content !!}
<!--/. END CONTENT -->
@endsection

@section('script-profile')
<script>
    $(document).on('click touch-start','.download-cv',function(event) {
        var that = $(this);
        that.find('.fa').removeClass('fa-download').addClass('fa-spin').addClass('fa-refresh');
        that.attr('disabled', true);
        
        setTimeout(function() {
            that.find('.fa').addClass('fa-download').removeClass('fa-spin fa-refresh');
            that.attr('disabled', false);
        }, 3000);
    });
</script>
@endsection