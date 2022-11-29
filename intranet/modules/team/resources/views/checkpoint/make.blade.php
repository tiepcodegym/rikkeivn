<?php

use Rikkei\Core\View\CookieCore;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\CoreUrl;

 ?>

@extends('layouts.guest')

@section('title')
{{ trans('team::view.Make checkpoint page') }} - Rikkeisoft
@endsection

@section('content')
<div class="se-pre-con"></div>
<div class="make-css-page checkpoint-make-page">
    <div class="row">
        <div class="col-md-12">
                <input type="hidden" name="team_id" id="team_id_result" value="{{$checkpoint->team_id}}">
                <input type="hidden" name="checkpoint_id" id="checkpoint_id" value="{{$checkpoint->id}}">
                <input type="hidden" name="emp_id" id="emp_id" value="{{$emp->id}}">

                @include('team::checkpoint.include.checkpoint_form')
            </div>
    </div>
    <div class="modal modal-warning" id="modal-confirm">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span></button>
                    <h4>{{ trans('team::messages.Checkpoint.Make.Warning') }}</h4>
                </div>
                <div class="modal-body">
                    {{ trans('team::messages.Checkpoint.Make.Modal confirm body text') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ trans('team::view.Checkpoint.Make.Close') }}</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    
    <!-- /.modal-submit -->
    <div class="modal modal-primary" id="modal-submit">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4>{{ trans('team::view.Checkpoint.Make.Modal submit title') }}</h4>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline pull-left cancel" data-dismiss="modal">{{ trans('team::view.Checkpoint.Make.Cancel') }}</button>
                    <button type="button" class="btn btn-outline submit" onclick="submit('{{ Session::token() }}',{{$checkpoint->id}});">{{ trans('team::view.Checkpoint.Make.Modal submit') }}</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

     <!-- /.modal-confirm-reload -->
    <div class="modal modal-primary" id="modal-reload">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4>{{ trans('team::view.Checkpoint.Make.Modal submit title') }}</h4>
                </div>
                <div class="modal-body">
                    <span>{{ trans('team::messages.Checkpoint.Make.Modal reload body text') }}</span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline pull-left cancel" data-dismiss="modal">{{ trans('team::view.Checkpoint.Make.Yes') }}</button>
                    <button type="button" class="btn btn-outline submit" onclick="makeNewTurn($('#checkpoint_id').val(), $('#emp_id').val());">{{ trans('team::view.Checkpoint.Make.No') }}</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <div class="modal apply-click-modal"><img class="loading-img" src="{{ asset('sales/images/loading.gif') }}" /></div>
</div>
<!-- Check value if press back button then reload page -->
@endsection

<!-- Styles -->
@section('css')
<link href="{{ CoreUrl::asset('team/css/style.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script src="{{ CoreUrl::asset('common/js/script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/external.js') }}"></script>
<script>
var urlSubmit = '{{ route("team::checkpoint.saveResult") }}';
var urlSuccessPage = '{{ route("team::checkpoint.success") }}';
</script>
<script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
<script src="{{ CoreUrl::asset('lib/js/jquery.visible.js') }}"></script>
<script src="{{ CoreUrl::asset('sales/js/css/make.js') }}"></script>
<script src="{{ CoreUrl::asset('lib/js/jquery.cookie.min.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/checkpoint/make.js') }}"></script>
<script type="text/javascript">
    onload=function(){
        if ($.cookie('totalPoint_current['+$('#checkpoint_id').val()+']['+$('#emp_id').val()+']')) {
            $('#modal-reload').modal('show');
        }
    }
    
</script>
@endsection
