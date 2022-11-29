@extends('layouts.default')

<?php
use Rikkei\Team\Model\Checkpoint;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\CoreUrl;
$emp = Permission::getInstance()->getEmployee();
?>

@section('content')
<div class="se-pre-con"></div>
<div class="make-css-page checkpoint-detail-page">
    <div class="row">
        <div class="col-md-12">
            @include('team::checkpoint.include.checkpoint_form', ['isDetail' => true])
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
                    <button type="button" class="btn btn-outline submit" onclick="submit('{{ Session::token() }}',{{$result->id}}, $('#checkpoint_id').val(), $('#emp_id').val(), $('#result_id').val());">{{ trans('team::view.Checkpoint.Make.Modal submit') }}</button>
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
                    <button type="button" class="btn btn-outline submit" onclick="makeNewTurn($('#checkpoint_id').val(), $('#emp_id').val(), $('#result_id').val());">{{ trans('team::view.Checkpoint.Make.No') }}</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <div class="modal apply-click-modal"><img class="loading-img" src="{{ asset('sales/images/loading.gif') }}" /></div>
</div>
<!-- Check value if press back button then reload page -->
<input type="hidden" id="refreshed" value="no">

@endsection

<!-- Styles -->
@section('css')
<link href="{{ CoreUrl::asset('team/css/style.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
<script src="{{ CoreUrl::asset('lib/js/jquery.visible.js') }}"></script>
<script src="{{ CoreUrl::asset('sales/js/css/make.js') }}"></script>
<script>
    var canEdit = 0;
    <?php if($canEdit): ?>
        canEdit = 1;
    <?php endif; ?>
    var isLeader = false;
    <?php if($canCmt || $isLeader): ?>
        isLeader = true;
    <?php endif; ?>
    var leaderConfirmSubmitText = '{{trans("team::view.Checkpoint.Detail.Submit comment?")}}';
    var submitText = '{{trans("team::view.Checkpoint.Detail.Submit")}}';
    var guiBai = '{{trans("team::view.Checkpoint.Detail.Gửi bài")}}';
</script>
<script src="{{ CoreUrl::asset('lib/js/jquery.cookie.min.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/checkpoint/detail.js') }}"></script>
<script type="text/javascript">
    onload=function(){
        if ($.cookie('checkpoint_current['+$('#checkpoint_id').val()+']['+$('#emp_id').val()+']['+$('#result_id').val()+']')) {
            $('#modal-reload').modal('show');
        }
        var e=document.getElementById("refreshed");
        if (e.value=="no") e.value="yes";
        else {e.value="no";location.reload();}
    }
    
</script>
@endsection
