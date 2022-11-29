@extends('layouts.default')

@section('title')
    @if($save == 'create')
        {{ trans('team::view.Checkpoint.Create.Title') }}
    @else
        {{ trans('team::view.Checkpoint.Update.Title') }}
    @endif
@endsection

@section('content')
<?php
use Rikkei\Team\View\TeamList;
use Rikkei\Sales\View\View;

$teamsOptionAll = TeamList::toOption(null, true, false);
?>
<div class="box box-primary checkpoint-create-page">
    <div class="checkpoint-create-body">
        <div id="frm_create_checkpoint" class="form-horizontal">
            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
            @if($save == 'create')
            <input type="hidden" name="create_or_update" value="create">
            @else
            <input type="hidden" name="create_or_update" value="update">
            <input type="hidden" name="checkpoint_id" value="{{$checkpoint->id}}">
            @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label">{{ trans('team::view.Checkpoint.Create.Creator') }}<em class="required">*</em></label>
                        <div class="col-sm-9">
                            <input type="hidden" id="employee_id" name="employee_id" value="{{$employee->id}}">
                            <input type="text" class="form-control" value="{{$employee->name}}" disabled="">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label" >{{ trans('team::view.Checkpoint.Create.Rikker relate') }} </label>
                        <div class="col-sm-9">
                            <div class="rikker-relate-container">
                                <div class="rikker-set <?php if(count($rikker_relate) > 0){echo 'display-inline';} ?>" data-for='rikker_relate'>
                                    @if(count($rikker_relate) > 0)
                                        @foreach($rikker_relate as $rikker)
                                            <span class="vN bfK a3q" email="{{$rikker['email']}}">
                                                <div class="vT">{{$rikker['name']}}</div>
                                                <div class="vM" data-remove="{{$rikker['email']}}" data-for="rikker_relate" onclick="removeRikker(this);"></div>
                                            </span>
                                        @endforeach
                                    @endif
                                </div>

                                <input type="text" data-name='rikker_relate' autocomplete="off" class="form-control <?php if(count($rikker_relate)>0){ echo 'rikker-relate-update';} ?>" data-length='max' id="rikker_relate"  tabindex=7  maxlength="100" placeholder="{{ trans('team::view.Checkpoint.Create.Rikker relate') }}" value="" >

                                <input id="rikker_relate_validate" name="rikker_relate_validate" type="text" value="1" style="visibility: hidden; position: absolute;">
                                <div class="rikker-result" data-for='rikker_relate'></div>

                                @if(count($rikker_relate) > 0)
                                    @foreach($rikker_relate as $rikker)
                                        <input type="hidden" data-for="rikker_relate" name="rikker_relate[]" value="{{$rikker['email']}}" />
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div> 
                </div> 
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label" >{{ trans('team::view.Checkpoint.Create.Checkpoint type') }}<em class="required">*</em></label>
                        <div class="col-sm-9">
                            @foreach($checkpointType as $item)
                            <label class="radio-inline">
                                <input type="radio" @if($item->id == $checkpoint->checkpoint_type_id) checked @endif name="checkpoint_type_id" value="{{$item->id}}">&nbsp;{{$item->name}}
                            </label>
                            @endforeach
                            <input type="text" class="form-control" style="opacity:0; width:10px" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label" for="start_date">{{ trans('team::view.Checkpoint.Create.Checkpoint date') }}<em class="required">*</em></label>
                        <div class="col-sm-9">
                            <div class="col-xs-12 col-sm-6 padding-0" >
                                <div class="input-group padding-0"> 
                                    <span class="input-group-btn"> 
                                        <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button> 
                                    </span> 
                                    <input type='text' autocomplete="off" class="form-control date start-date" id="start_date" name="start_date" data-provide="datepicker" placeholder="YYYY/MM/DD" tabindex=9 value="{{$checkpoint->start_date}}" />
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 padding-0" >
                                <div class="input-group "> 
                                    <span class="input-group-btn"> 
                                        <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>                                </i></button> 
                                    </span> 
                                    <input type='text' autocomplete="off" class="form-control date end-date" id="end_date" name="end_date" data-provide="datepicker" placeholder="YYYY/MM/DD" tabindex=10 value="{{$checkpoint->end_date}}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>  
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label" >{{ trans('team::view.Checkpoint.Create.Checkpoint time') }}<em class="required">*</em></label>
                        <div class="col-sm-9">
                            <select class="form-control" id="check_time" name="check_time">
                                <option value="0">{{trans('team::view.Checkpoint.Create.Checkpoint time choice')}}</option>
                                @if($checkpointTime && count($checkpointTime))
                                    @foreach($checkpointTime as $item)
                                        <option value="{{$item->id}}" <?php if($checkpoint->checkpoint_time_id == $item->id){ echo 'selected';} ?>>{{$item->check_time}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 {{ $save == 'create' ? 'hidden' : '' }}" id="team-checkpoint">
                    <div class="form-group form-label-left" >
                        <label class="col-sm-3 control-label">{{ trans('team::view.Checkpoint.Create.Team choice') }}<em class="required">*</em></label>
                        <div class="col-sm-9">
                            <select class="form-control select-search" id="set_team" name="set_team">
                                <option value="0">{{trans('team::view.Checkpoint.Create.Team list')}}</option>
                                {{-- show team available --}}
                                @if ($teamIdsAvailable || (count($teamsOptionAll) && $teamTreeAvailable))
                                    @foreach($teamsOptionAll as $option)
                                        @if ($teamIdsAvailable || in_array($option['value'], $teamTreeAvailable))
                                            <option value="{{ $option['value'] }}" <?php if($checkpoint->team_id == $option['value']){ echo 'selected';} ?>
                                                <?php
                                                if (is_array($teamIdsAvailable) && ! in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else:
                                                ?>{{ $option['option'] }}<?php endif; ?>>{{ $option['label'] }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>   

            @if ($save == 'update')
                <?php $countEva = count($evaluators); ?>
                @foreach ($evaluators as $no => $eva)
                <?php $no++; ?>
                <div class="row evaluator-row evaluator-row-{{$no}}" row='{{$no}}'>
                    <div class="col-md-6">
                        <div class="form-group form-label-left">
                            <label class="col-sm-3 control-label">{{ trans('team::view.Checkpoint.Create.Evaluator') }}<em class="required">*</em></label>
                            <div class="col-sm-9">
                                <select class="form-control" id="evaluator" dataname="evaluator" name="evaluator[{{$no}}]" row='{{$no}}' old='{{$eva->evaluatorId}}' >
                                    @foreach ($empAll as $item)
                                    <option value="{{$item->id}}" 
                                        <?php if ($item->id == $eva->evaluatorId) echo 'selected' ?>
                                        <?php if (in_array($item->id, $evaluatorsSelected) && $item->id != $eva->evaluatorId) echo 'disabled' ?>
                                    >{{View::getAccName($item->email)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group form-label-left">
                            <label class="col-sm-3 control-label">{{ trans('team::view.Checkpoint.Create.Person is evaluated') }}<em class="required">*</em></label>
                            <div class="col-sm-8 evaluated-container">
                                <select class="form-control" id="evaluated"  dataname="evaluated" name="evaluated[{{$no}}][]" row='{{$no}}' multiple="multiple" >
                                    @foreach ($empOfTeam as $item)
                                    <option value="{{$item->id}}" 
                                        <?php if (in_array($item->id, $eva->evaluated)) echo 'selected' ?>
                                        <?php if (in_array($item->id, $evaluatedSelected) && !in_array($item->id, $eva->evaluated)) echo 'disabled' ?>    
                                    >{{View::getAccName($item->email)}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-1">
                                @if ($countEva > 1)
                                <span href="#" class="btn-delete btn-delete-row float-right" row="{{$no}}"><i class="fa fa-trash"></i></span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif

            <!-- ADD ROW -->
            <div class="row add-evaluator-row hidden">
                <div class="col-md-6">
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label">{{ trans('team::view.Checkpoint.Create.Evaluator') }}<em class="required">*</em></label>
                        <div class="col-sm-9">
                            <select class="form-control" id="evaluator" dataname="evaluator" >
                                <option value="0" disabled="" selected>{{trans('team::view.Checkpoint.Create.Evaluator choice')}}</option>
                                @if (isset($empAll))
                                @foreach ($empAll as $item)
                                <option value="{{$item->id}}" <?php if (in_array($item->id, $evaluatorsSelected)) echo 'disabled' ?>>{{View::getAccName($item->email)}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label">{{ trans('team::view.Checkpoint.Create.Person is evaluated') }}<em class="required">*</em></label>
                        <div class="col-sm-8 evaluated-container">
                            <select class="form-control" id="evaluated"  dataname="evaluated" multiple="multiple" >
                                @if (isset($empOfTeam))
                                @foreach ($empOfTeam as $item)
                                <option value="{{$item->id}}" >{{View::getAccName($item->email)}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-1"><span href="#" class="btn-delete btn-delete-row float-right" ><i class="fa fa-trash"></i></span></div>
                    </div>
                </div>
            </div>

            <div class="row btn-container">
                <div class="col-md-12">
                    <span href="#" class="btn-add add-evaluator <?php if ($save == 'create') echo 'hidden' ?>"><i class="fa fa-plus"></i></span>
                </div>
                <div class="col-md-12 text-align-center " >
                    <button class="btn btn-primary btn-create" type="submit" >
                        @if($save == 'create')
                            {{ trans('team::view.Checkpoint.Create.Title') }}
                        @else
                            {{ trans('team::view.Checkpoint.Update.Title') }}
                        @endif
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="error_append">
        <label  class="error" style="display: block;"></label>    
    </div>
</div>
<input id="token" type="hidden" value="{{ Session::token() }}" />
<!-- Check value if press back button then reload page -->
<input type="hidden" id="refreshed" value="no">
@endsection

<!-- Styles -->
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
<link href="{{ asset('team/css/style.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="{{ asset('common/js/box.email.js') }}"></script>
<script src="{{ asset('team/js/checkpoint/create.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script>
$('#check_time').select2();
$('#set_team').select2();

var urlSetEmp = '{{route("team::checkpoint.setEmp")}}';
var token = '{{Session::token()}}';
var urlSave = '{{route("team::checkpoint.save")}}';
var requiredText = '{{trans("team::messages.Required field")}}';
var emailInvalid = '{{trans("team::messages.Email invalid")}}';
</script>
<script type="text/javascript">
    onload=function(){
        var e=document.getElementById("refreshed");
        if (e.value=="no") e.value="yes";
        else {
            e.value="no";
            $('.btn-create').prop('disabled',true);
            location.reload();}
    }
    $('#check_time, #start_date, #end_date, #set_team').on('change', function() {
        validateHtml($(this)); 
    });
</script>
@endsection
